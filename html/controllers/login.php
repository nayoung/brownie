<?php
require_once dirname(__FILE__) . '/../../conf/config.php';

switch ($act) {
    case 'login' :
        include_once _VIEW_PATH . 'login.html';
        break;

    case 'loginPrc' :
        $admin = new Admin;
        if ($admin->doLogin($request) == true) {

            $url = $_SESSION['first_page'];
            include_once _VIEW_PATH . 'redirect.html';
        }

        $message = '회원정보가 없거나 권한이 없습니다.';
        $back = true;
        include_once _VIEW_PATH . 'redirect.html';

        break;

    case 'logout' :
    default :
    $admin = new Admin;
        $admin->doLogout();

        $url = _HTTP_HOST . _WEB_ROOT;
        include_once _VIEW_PATH . 'redirect.html';

        break;
}

