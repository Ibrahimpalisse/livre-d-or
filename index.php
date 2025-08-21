<?php
echo "Hello World from PHP!";
echo "<br>PHP Version: " . PHP_VERSION;
echo "<br>Server: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
echo "<br>Date: " . date('Y-m-d H:i:s');
?>