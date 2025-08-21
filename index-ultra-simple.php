<?php
// index-ultra-simple.php - Version absolument minimale
echo "<h1>✅ PHP fonctionne</h1>";
echo "<p>Version PHP: " . PHP_VERSION . "</p>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";

// Test basic des variables d'environnement
echo "<h2>Variables d'environnement:</h2>";
echo "<p>APP_NAME: " . (getenv('APP_NAME') ?: 'Non définie') . "</p>";
echo "<p>MONGO_URL: " . (getenv('MONGO_URL') ? 'Définie' : 'Non définie') . "</p>";
echo "<p>AWS_BUCKET: " . (getenv('AWS_BUCKET') ?: 'Non définie') . "</p>";

echo "<h2>✅ Application de base fonctionnelle</h2>";
?>