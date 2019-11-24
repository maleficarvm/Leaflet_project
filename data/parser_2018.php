<?php
$dsn = "pgsql:host=192.168.43.76;dbname=msb;port=5432";
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];
$pdo = new PDO($dsn, 'pguser', '123456', $opt);

$tables = [
    'grreighten' => [
        'table' => 'grractive_2018',
        'parser' => 'default'
    ],
    'popupCentroids2018' => [
        'table' => 'grractive_centroid_2018',
        'parser' => 'default'
    ],
];

foreach ($tables as $jsVar => $pgsTable) {
    $result = $pdo->query("SELECT *, ST_AsGeoJSON(geom, 5) AS geojson FROM {$pgsTable['table']}");
    switch ($pgsTable['parser']) {
        case 'custom-parser':
            // Something custom
            break;
        default:
            $features = [];
            foreach ($result AS $row) {
                unset($row['geom']);
                $geometry = $row['geojson'] = json_decode($row['geojson']);
                unset($row['geojson']);
                $feature = ["type" => "Feature", "geometry" => $geometry, "properties" => $row];
                //echo json_encode($feature)."<br><br>";
                array_push($features, $feature);
            }
            $featureCollection = ["type" => "FeatureCollection", "features" => $features];
            break;
    }
    echo 'var ' . $jsVar . ' = ' . json_encode($featureCollection) . ';';
}

exit;