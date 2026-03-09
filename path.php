<?php
if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', __DIR__);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    define('BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . "/");
    define('ROOT_PATH', realpath(dirname(__FILE__)));
}