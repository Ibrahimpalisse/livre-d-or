<?php
// Test complet de la connexion MongoDB Railway

echo "<h1>🔍 Test Connexion MongoDB Railway</h1>";

// Test 1: Variables d'environnement
echo "<h2>📋 Variables d'environnement Railway :</h2>";
echo "<strong>MONGO_URL:</strong> " . ($_ENV['MONGO_URL'] ?? '❌ NON DÉFINI') . "<br>";
echo "<strong>MONGOHOST:</strong> " . ($_ENV['MONGOHOST'] ?? '❌ NON DÉFINI') . "<br>";
echo "<strong>MONGOPORT:</strong> " . ($_ENV['MONGOPORT'] ?? '❌ NON DÉFINI') . "<br>";
echo "<strong>MONGOUSER:</strong> " . ($_ENV['MONGOUSER'] ?? '❌ NON DÉFINI') . "<br>";
echo "<strong>MONGOPASSWORD:</strong> " . (empty($_ENV['MONGOPASSWORD']) ? '❌ NON DÉFINI' : '✅ DÉFINI (masqué)') . "<br>";

// Test 2: Extension MongoDB
echo "<h2>🔌 Extension PHP MongoDB :</h2>";
if (extension_loaded('mongodb')) {
    echo "✅ Extension 'mongodb' chargée<br>";
} else {
    echo "❌ Extension 'mongodb' NON chargée<br>";
}

if (class_exists('MongoDB\Client')) {
    echo "✅ Classe MongoDB\\Client disponible<br>";
} else {
    echo "❌ Classe MongoDB\\Client NON disponible<br>";
}

// Test 3: Autoloader
echo "<h2>📂 Test Autoloader :</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ vendor/autoload.php trouvé<br>";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoloader chargé<br>";
} else {
    echo "❌ vendor/autoload.php NON TROUVÉ<br>";
}

// Test 4: Classes Core
echo "<h2>🏗️ Classes Core :</h2>";
try {
    if (class_exists('Core\DatabaseMongoDB')) {
        echo "✅ Classe Core\\DatabaseMongoDB disponible<br>";
        
        // Test de connexion
        echo "<h2>🔗 Test de connexion MongoDB :</h2>";
        $mongoHandler = Core\DatabaseMongoDB::getInstance();
        
        if ($mongoHandler->isAvailable()) {
            echo "✅ <strong>CONNEXION MONGODB RÉUSSIE !</strong><br>";
            
            // Test de base de données
            $db = $mongoHandler->getDatabase();
            if ($db) {
                echo "✅ Base de données accessible<br>";
                
                // Test d'écriture/lecture
                try {
                    $collection = $db->selectCollection('test_connection');
                    
                    // Insérer un document test
                    $testDoc = [
                        'test' => true,
                        'timestamp' => new \MongoDB\BSON\UTCDateTime(),
                        'message' => 'Test de connexion Railway MongoDB'
                    ];
                    
                    $result = $collection->insertOne($testDoc);
                    echo "✅ <strong>ÉCRITURE RÉUSSIE !</strong> ID: " . $result->getInsertedId() . "<br>";
                    
                    // Lire le document
                    $found = $collection->findOne(['test' => true]);
                    if ($found) {
                        echo "✅ <strong>LECTURE RÉUSSIE !</strong> Message: " . $found['message'] . "<br>";
                        
                        // Nettoyer le document test
                        $collection->deleteOne(['_id' => $result->getInsertedId()]);
                        echo "✅ Nettoyage effectué<br>";
                        
                        echo "<h2>🎉 MONGODB FONCTIONNE PARFAITEMENT !</h2>";
                        echo "<p style='color: green; font-weight: bold;'>✅ Connexion établie<br>";
                        echo "✅ Écriture fonctionnelle<br>";
                        echo "✅ Lecture fonctionnelle<br>";
                        echo "✅ Votre application peut utiliser MongoDB !</p>";
                    } else {
                        echo "❌ Erreur lecture document<br>";
                    }
                    
                } catch (Exception $e) {
                    echo "❌ Erreur test écriture/lecture: " . $e->getMessage() . "<br>";
                }
                
            } else {
                echo "❌ Base de données inaccessible<br>";
            }
        } else {
            echo "❌ <strong>CONNEXION MONGODB ÉCHOUÉE</strong><br>";
        }
        
    } else {
        echo "❌ Classe Core\\DatabaseMongoDB NON disponible<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur test classes: " . $e->getMessage() . "<br>";
}

// Test 5: Fallback HTTP si nécessaire
echo "<h2>🔄 Test Fallback HTTP (si MongoDB natif échoue) :</h2>";
try {
    if (class_exists('Core\DatabaseHTTP')) {
        echo "✅ Classe Core\\DatabaseHTTP disponible<br>";
        $httpHandler = Core\DatabaseHTTP::getInstance();
        echo "✅ Instance HTTP créée (fallback disponible)<br>";
    } else {
        echo "❌ Classe Core\\DatabaseHTTP NON disponible<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur fallback HTTP: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>📊 Résumé :</h2>";
echo "<p>Si vous voyez '🎉 MONGODB FONCTIONNE PARFAITEMENT !' ci-dessus, votre application est prête !</p>";
echo "<p>Sinon, vérifiez les erreurs détaillées ci-dessus.</p>";
?>