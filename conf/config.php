<?php
error_reporting(E_ALL);

$rootPath = dirname(__FILE__) . '/../';
define('_DOCUMENT_ROOT',	$rootPath);
define('_CONFIG_PATH',		_DOCUMENT_ROOT . 'conf/');
define('_LIB_PATH',			_DOCUMENT_ROOT . 'lib/');
define('_HOME_PATH',		_DOCUMENT_ROOT . 'html/');

define('_MODEL_PATH',		_HOME_PATH . 'models/');
define('_VIEW_PATH',		_HOME_PATH . 'views/');

//define('_WEB_ROOT',		    '/controllers');
define('_WEB_ROOT',		    '');

define('_SCRIPT_PATH',		'/views/script/');
define('_CSS_PATH',		    '/views/css/');
define('_IMAGE_PATH',		'/views/images/');

$httpHost = $_SERVER['HTTP_HOST'];
define('_HTTP_HOST', 'http://' . $httpHost);
$domain = explode('.', $httpHost);
define('_COOKIE_DOMAIN', '.' . $domain[1] . '.' . $domain[2]);
define('SERVER_REMOTE_ADDR', $_SERVER['REMOTE_ADDR']);

spl_autoload_register(function ($class) {
    try {
        if (is_file(_LIB_PATH . strtolower($class) . '.php')) {
            require_once _LIB_PATH . strtolower($class) . '.php';
        } else {
            require_once _MODEL_PATH . strtolower($class) . '.php';
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
        echo $error_message; exit;
    }
});

if (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] == 'development') {
    define('_CONFIG_DB_INC', _CONFIG_PATH . 'inc/dev.database.inc');
} else {
    define('_CONFIG_DB_INC', _CONFIG_PATH . 'inc/database.inc');
}
define('DB_SECTION', 'BROWNIE_MASTER');
$db_con = DbCon::getInstance(_CONFIG_DB_INC, DB_SECTION);

$http_request = new Request($_GET, $_POST, $_FILE, $_SERVER, $_COOKIE);
$request = $http_request->getRequest();

session_start();
///echo "<pre>";print_r($_SERVER);
//echo "<pre>";print_r($_SESSION);
//exit;

if (!in_array($_SERVER['PHP_SELF'], array(_WEB_ROOT . "/login.php")) && Admin::isLogin() == false) {
    // 로그인/로그아웃 페이지 아닌경우 + 로그인 전
    $url = _WEB_ROOT . '/login.php?act=login';
    include_once _VIEW_PATH . 'redirect.html';

} else if (in_array($_SERVER['PHP_SELF'], array(_WEB_ROOT . "/login.php")) && $request['act'] == 'login' && Admin::isLogin() == true) {
    // 로그인 페이지 + 로그인중
    $url = $_SESSION['first_page'];
    include_once _VIEW_PATH . 'redirect.html';
}


$act = $request['act'];
unset($request['act']);

