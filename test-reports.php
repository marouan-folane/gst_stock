<?php

// Chargement de l'environnement Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Récupérer le premier rapport
$report = \App\Models\Report::first();

if ($report) {
    echo "Report ID: " . $report->id . PHP_EOL;
    echo "Type: " . $report->type . PHP_EOL;
    echo "URL: " . $report->url . PHP_EOL;
    echo "Download URL: " . $report->downloadUrl . PHP_EOL;
} else {
    echo "Aucun rapport trouvé dans la base de données." . PHP_EOL;
} 