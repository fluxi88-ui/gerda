<?php
// logout.php - das k\u00fcrzeste script auf der ganzen welt
// session starten um sie dann direkt zu zerst\u00f6ren (coole logik oder?)
session_start();
session_destroy(); // ALLES WEG!! auf wiedersehen user-daten
header('Location: Index.html'); // tsch\u00fcss, bis zum n\u00e4chsten mal!
exit;
