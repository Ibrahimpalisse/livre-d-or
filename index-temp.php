<?php
// index-temp.php - Version temporaire qui bypasse le .env
// Pour tester si le problème vient du .env manquant

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 TEST INDEX TEMPORAIRE</h1>";
echo "<p>✅ PHP fonctionne</p>";
echo "<p>✅ Session démarrée</p>";

try {
    echo "<p>🔍 Test autoload...</p>";
    require 'vendor/autoload.php';
    echo "<p>✅ Autoload OK</p>";
    
    echo "<p>🔍 Test classes Core...</p>";
    if (class_exists('Core\\Router')) {
        echo "<p>✅ Core\\Router existe</p>";
    } else {
        echo "<p>❌ Core\\Router manquant</p>";
    }
    
    if (class_exists('Core\\Env')) {
        echo "<p>✅ Core\\Env existe</p>";
        echo "<p>🔍 Test Env::load() (peut échouer si .env manque)...</p>";
        try {
            Core\Env::load();
            echo "<p>✅ Env::load() OK</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Env::load() échoué : " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Core\\Env manquant</p>";
    }
    
    echo "<h2>✅ DIAGNOSTIC COMPLET</h2>";
    echo "<p>L'application peut démarrer sans problème !</p>";
    
} catch (Throwable $e) {
    echo "<h2>🚨 ERREUR DÉTECTÉE</h2>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Fichier:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>