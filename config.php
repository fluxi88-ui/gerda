<?php
// Konfigurationsdatei
// Lokal: Werte sind hardcodiert
// Azure: Werte kommen aus App Service > Configuration > Application Settings

define('DB_HOST',   getenv('DB_HOST')   ?: 'moneyboykonvoltic.mysql.database.azure.com');
define('DB_NAME',   getenv('DB_NAME')   ?: 'konvolticdatenbank');
define('DB_USER',   getenv('DB_USER')   ?: 'Einhorn');
define('DB_PASS',   getenv('DB_PASS')   ?: 'H3l3N4!!!!');
define('PEPPER',    getenv('PEPPER')    ?: 'K0nv0lt!c#P3pp3r_2026');
