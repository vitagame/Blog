<?php

ob_start();

//сессия
session_start();
session_name("KeyGa.SU");

/* Устанавливаем соединение с базой данных */
require 'R.php';
require 'inc.php';
R::setup("mysql:host=$hostbd; dbname=$namebd", $nameuser, $userpass);

R::ext('xdispense', function($table_name) {
    return R::getRedBean()->dispense($table_name);
});

spl_autoload_register(function ($class) {
    $dirs = array(
        $_SERVER['DOCUMENT_ROOT'] . '/app/core/classes/',
        $_SERVER['DOCUMENT_ROOT'] . '/app/controllers/',
        $_SERVER['DOCUMENT_ROOT'] . '/app/controllers/admin/',
        $_SERVER['DOCUMENT_ROOT'] . '/app/models/',
        $_SERVER['DOCUMENT_ROOT'] . '/app/models/admin/',
    );
    foreach ($dirs as $dir) {
        if (is_file($dir . $class . '.php')) {
            require_once($dir . $class . '.php');
        }
    }
});

/* подключаем переводы */
require $_SERVER['DOCUMENT_ROOT'] . "/app/lib/locale/core.php";

if (User::auth()) {
    $skin = User::$user['skin'];
} else {
    $skin = Cms::setup('skin');
}

SmartySingleton::instance()->template_dir = $_SERVER['DOCUMENT_ROOT'] . '/style/' . $skin . '/templates/';
SmartySingleton::instance()->compile_dir = $_SERVER['DOCUMENT_ROOT'] . '/style/' . $skin . '/templates_c/';
SmartySingleton::instance()->config_dir = $_SERVER['DOCUMENT_ROOT'] . '/style/' . $skin . '/configs/';
SmartySingleton::instance()->cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/files/cache/';

define('SMARTY_TEMPLATE_LOAD', $_SERVER['DOCUMENT_ROOT'] . '/style/' . $skin);

if (empty($_SESSION['country'])) {
    //sypex geo
    require_once $_SERVER["DOCUMENT_ROOT"] . '/app/lib/sypex/SxGeo.php';
    $SxGeo = new SxGeo($_SERVER["DOCUMENT_ROOT"] . '/app/lib/sypex/SxGeoCity.dat');
    $stat = $SxGeo->getCityFull(Recipe::getClientIP());
    
    $_SESSION['country'] = $stat['country']['name_ru'];
}

if($_SESSION['country'] == 'Россия' || $_SESSION['country'] == 'Украина' || $_SESSION['country'] == 'Беларусь' || $_SESSION['country'] == 'Казахстан' || $_SESSION['country'] == 'Азербайджан' || $_SESSION['country'] == 'Армения' || $_SESSION['country'] == 'Грузия' || $_SESSION['country'] == 'Кыргызстан'){
    $sng = 1;
}

if (User::$user['country_id'] == 1 || User::$user['country_id'] == 5 || User::$user['country_id'] == 6 || User::$user['country_id'] == 3 || User::$user['country_id'] == 4 || User::$user['country_id'] == 15 || User::$user['country_id'] == 11 || User::$user['country_id'] == 16 || User::$user['country_id'] == 18 || User::$user['country_id'] == 2 || User::$user['country_id'] == 17) {
    $sng = 1;
}

SmartySingleton::instance()->assign(array(
    'setup' => Cms::setting(),
    'realtime' => Cms::realtime(),
    'message' => $_REQUEST['message'] ? Cms::Int($_REQUEST['message']) : Cms::setup('message'),
    'home' => Cms::setup('home'),
    'panel' => Cms::setup('adminpanel'),
    'sng' => $sng,
    'skin' => $skin,
    'user' => User::auth(),
    'route' => Base::route(),
    'page' => Cms::page(),
    'ip' => Recipe::getClientIP(),
    'url' => Cms::setup('home') . '' . $_SERVER['REQUEST_URI'],
    'admin' => Cms::admin($_COOKIE['login'], $_COOKIE['hash']),
    'vkontakte' => Cms::setup('vkontakte_key') ? oAuth::get_url('vkontakte') : '',
    'facebook' => Cms::setup('facebook_key') ? oAuth::get_url('facebook') : '',
    'google' => Cms::setup('google_key') ? oAuth::get_url('google') : '',
    'yandex' => Cms::setup('yandex_key') ? oAuth::get_url('yandex') : '',
    'odnoklassniki' => Cms::setup('ok_key') ? OAuthOK::goToAuth() : '',
));

//print_r($_COOKIE);