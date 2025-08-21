<?php
// Test ultra simple pour identifier où ça crash

echo "<h1>🔍 Test Simple Railway</h1>";

// Test 1: PHP de base
echo "✅ PHP fonctionne<br>";

// Test 2: Variables d'environnement
echo "Variables MongoDB:<br>";
echo "- MONGO_URL: " . (isset($_ENV['MONGO_URL']) ? '✅ DÉFINI' : '❌ MANQUANT') . "<br>";
echo "- MONGOHOST: " . (isset($_ENV['MONGOHOST']) ? '✅ DÉFINI' : '❌ MANQUANT') . "<br>";

// Test 3: Autoloader
echo "<br>Test autoloader:<br>";
try {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "✅ Autoloader chargé<br>";
    } else {
        echo "❌ Autoloader manquant<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur autoloader: " . $e->getMessage() . "<br>";
}

// Test 4: Classes Core sans instanciation
echo "<br>Test classes (sans instanciation):<br>";
if (class_exists('Core\Database')) {
    echo "✅ Classe Core\\Database existe<br>";
} else {
    echo "❌ Classe Core\\Database manquante<br>";
}

if (class_exists('Core\Router')) {
    echo "✅ Classe Core\\Router existe<br>";
} else {
    echo "❌ Classe Core\\Router manquante<br>";
}

// Test 5: Extensions MongoDB
echo "<br>Extension MongoDB:<br>";
if (extension_loaded('mongodb')) {
    echo "✅ Extension mongodb chargée<br>";
} else {
    echo "❌ Extension mongodb manquante<br>";
}

if (class_exists('MongoDB\Client')) {
    echo "✅ Classe MongoDB\\Client disponible<br>";
} else {
    echo "❌ Classe MongoDB\\Client manquante<br>";
}

echo "<br><strong>Si vous voyez ce message, PHP fonctionne !</strong>";
echo "<br>Le problème vient probablement de l'instanciation des classes Core ou de la connexion MongoDB.";
?>