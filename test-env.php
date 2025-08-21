<?php
// Test si le problème vient du .env
echo "🔍 Test 1: PHP OK<br>";

try {
    echo "🔍 Test 2: Avant autoload<br>";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "🔍 Test 3: Autoload OK<br>";
    
    echo "🔍 Test 4: Avant Core\\Env<br>";
    if (class_exists('Core\\Env')) {
        echo "🔍 Test 5: Core\\Env existe<br>";
        Core\Env::load();
        echo "🔍 Test 6: Env::load() OK<br>";
    } else {
        echo "❌ Core\\Env n'existe pas !<br>";
    }
    
    echo "✅ TOUS LES TESTS PASSÉS !";
    
} catch (Throwable $e) {
    echo "<h1>🚨 CRASH DÉTECTÉ !</h1>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Fichier:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>