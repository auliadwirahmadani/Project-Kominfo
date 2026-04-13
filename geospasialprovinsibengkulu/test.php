<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$layers = App\Models\GeospatialLayer::with('metadata')->get();
file_put_contents('test_output.json', json_encode($layers, JSON_PRETTY_PRINT));
echo "DONE";
