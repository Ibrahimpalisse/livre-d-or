<?php
// minimal.php - Test ultra-basique pour vérifier PHP
echo "✅ PHP fonctionne";
echo "<br>Version PHP: " . PHP_VERSION;
echo "<br>Extensions: " . implode(', ', get_loaded_extensions());
?>