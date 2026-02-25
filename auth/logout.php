<?php
require_once dirname(__DIR__) . '/config/config.php';
Session::Destroy();
UrlHelper::redirect('auth/login.php');
