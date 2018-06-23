<?php
require_once dirname(__FILE__) . '/../../conf/config.php';

switch ($act) {
    case 'registerPop' :
        Auth::getAuth('21', 'read');

        //수정시...
        if ((int) $request['idx'] > 0) {
            $develop = new Develop;
            $develop_list = $develop->getList(array('idx' => $request['idx']));
        }

        include_once _VIEW_PATH . 'develop_detail_pop.html';
        break;

    case 'register' :
        Auth::getAuth('21', 'write');

        if (strlen($request['id']) > 0) {
            $admin = new Admin;
            $admin_cnt = $admin->getCount(array('id' => $request['id']));

            if ((int) $admin_cnt == 0) {
                $message = "파트너 로그인ID로 등록된 관리자ID가 없습니다.";
                $back = true;
                include_once _VIEW_PATH . 'redirect.html';
            }
        }

        $develop = new Develop;
        if ($develop->doRegister($request)) {
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
        Auth::getAuth('21', 'write');

        if (strlen($request['id']) > 0) {
            $admin = new Admin;
            $admin_cnt = $admin->getCount(array('id' => $request['id']));

            if ((int) $admin_cnt == 0) {
                $message = "파트너 로그인ID로 등록된 관리자ID가 없습니다.";
                $back = true;
                include_once _VIEW_PATH . 'redirect.html';
            }
        }

        $develop = new Develop;
        if ($develop->doModify($request)) {
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
        Auth::getAuth('14', 'read');

        $request['scale'] = ((int)$request['scale'] > 0) ? $request['scale'] : 50;
        $params = $request;
        unset($params['scale']);
        unset($params['page']);

        $develop = new Develop;
        $total = $develop->getCount($params);

        $url = $_SERVER['PHP_SELF'];
        $page = new Page($request['page'], $total, $url, $params, $request['scale']);
        list($page_html, $offset, $limit) = $page->init();

        $list = $develop->getList($params, $offset, $limit, array('idx DESC'));

        include_once _VIEW_PATH . 'develop.html';
        break;
}