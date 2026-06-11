<?php
// logout.php — Logout handler
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';
logoutUser();
setFlash('success', 'Vous avez été déconnecté(e).');
redirect('/index.php');
