<?php
$config = parse_ini_file("conf.properties");

if ($config === false) {
    echo "❌ Impossible de lire conf.properties";
} else {
    echo "<pre>";
    print_r($config);
    echo "</pre>";
}

