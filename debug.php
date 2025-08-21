<?php
// Test ultra-simple pour identifier le problème

echo "🔍 DEBUG Railway - Début<br>";

// Test 1: PHP fonctionne
echo "✅ PHP Version: " . PHP_VERSION . "<br>";
echo "✅ Date: " . date('Y-m-d H:i:s') . "<br>";

// Test 2: Extensions
echo "📋 Extensions chargées:<br>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    echo "- $ext<br>";
}

// Test 3: Variables d'environnement
echo "<hr>🔧 Variables d'environnement:<br>";
echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'NON DÉFINI') . "<br>";
echo "MONGODB_HOST: " . ($_ENV['MONGODB_HOST'] ?? 'NON DÉFINI') . "<br>";
echo "AWS_BUCKET: " . ($_ENV['AWS_BUCKET'] ?? 'NON DÉFINI') . "<br>";

// Test 4: Autoloader
echo "<hr>📂 Test autoloader:<br>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ vendor/autoload.php trouvé<br>";
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "✅ Autoloader chargé avec succès<br>";
    } catch (Exception $e) {
        echo "❌ Erreur autoloader: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ vendor/autoload.php NON TROUVÉ<br>";
}

// Test 5: Classes core
echo "<hr>🏗️ Test classes core:<br>";
try {
    if (class_exists('Core\Database')) {
        echo "✅ Classe Core\Database existe<br>";
    } else {
        echo "❌ Classe Core\Database manquante<br>";
    }
    
    if (class_exists('Core\DatabaseHTTP')) {
        echo "✅ Classe Core\DatabaseHTTP existe<br>";
    } else {
        echo "❌ Classe Core\DatabaseHTTP manquante<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur test classes: " . $e->getMessage() . "<br>";
}

echo "<hr>🎯 DEBUG terminé - Si vous voyez ce message, PHP fonctionne !";
?>