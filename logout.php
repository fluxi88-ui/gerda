<?php
session_start();
session_destroy();
header('Location: Website.html');
exit;
