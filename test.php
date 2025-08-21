<?php
// Test simple pour vérifier Railway
echo "🎉 Railway PHP fonctionne !<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Date: " . date('Y-m-d H:i:s') . "<br>";

// Test extension MongoDB
if (extension_loaded('mongodb')) {
    echo "✅ Extension MongoDB chargée<br>";
} else {
    echo "❌ Extension MongoDB non chargée<br>";
}

// Test variables d'environnement
echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'non défini') . "<br>";
echo "MONGODB_HOST: " . ($_ENV['MONGODB_HOST'] ?? 'non défini') . "<br>";

// Test autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Autoloader trouvé<br>";
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Test MongoDB Client
    try {
        $client = new MongoDB\Client($_ENV['MONGO_URI'] ?? '');
        echo "✅ MongoDB Client créé avec succès<br>";
    } catch (Exception $e) {
        echo "❌ Erreur MongoDB Client: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Autoloader non trouvé<br>";
}

echo "<hr>";
echo "🔍 Structure fichiers:<br>";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "- $file<br>";
    }
}
?>