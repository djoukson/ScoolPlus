<?php

echo "==================================\n";
echo "   ACTIVATION LICENCE SCHOOLPLUS\n";
echo "==================================\n\n";

$license = __DIR__ . "/license.lic";

if (!file_exists($license)) {
    echo "❌ ERREUR: license.lic introuvable\n";
    exit;
}

$destination = __DIR__ . "/../storage/app/license.lic";

if (!is_dir(dirname($destination))) {
    mkdir(dirname($destination), 0777, true);
}

copy($license, $destination);

echo "✅ LICENCE ACTIVÉE AVEC SUCCÈS\n";
