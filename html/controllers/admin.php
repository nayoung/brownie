<?php
require_once dirname(__FILE__) . '/../../conf/config.php';

switch ($act) {
    case 'registerPop' :
        Auth::getAuth('19', 'read');

        $auth = new Auth;
        $auth_list = $auth->getList();

        if (strlen($request['id']) > 0) {
            $admin = new Admin;
            $admin_list = $admin->getList(array('id' => $request['id']));
        }

        include_once _VIEW_PATH . 'admin_detail_pop.html';
        break;

    case 'register' :
        Auth::getAuth('19', 'write');

        $admin = new Admin;
        $admin_cnt = $admin->getCount(array('id' => $request['id']));
        if ((int) $admin_cnt > 0) {
            $message = "중복된 ID 입니다.";
            $back = true;
            include_once _VIEW_PATH . 'redirect.html';
        }

        if ($admin->doRegister($request)) {
            $message = "등록되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "등록에 실패하였습니다.";
            $back = true;
        }

        include_once _VIEW_PATH . 'redirect.html';
        break;

    case 'modify' :
        Auth::getAuth('19', 'write');

        $admin = new Admin;
        if ($admin->doModify($request)) {
            $message = "수정되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "수정에 실패하였습니다.";
            $back = true;
        }

        include_once _VIEW_PATH . 'redirect.html';
        break;

    default :
        Auth::getAuth('12', 'read');

        $request['scale'] = ((int) $request['scale'] > 0)? $request['scale']:50;
        $params = $request;
        unset($params['scale']);
        unset($params['page']);

        $admin = new Admin;
        $total = $admin->getCount($params);

        $url = $_SERVER['PHP_SELF'] ;
        $page = new Page($request['page'], $total, $url, $params, $request['scale']);
        list($page_html, $offset, $limit) = $page->init();

        $list = $admin->getList($params, $offset, $limit, array('sort asc'));

        include_once _VIEW_PATH . 'admin.html';
        break;
}

