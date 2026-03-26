<?php
session_start();
header('Content-Type: application/json');
echo json_encode(['eingeloggt' => !empty($_SESSION['acc_id'])]);
