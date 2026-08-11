<?php
require_once __DIR__ . '/includes/config.php';
require_admin_login();
header('Location: dashboard.php');
exit;
