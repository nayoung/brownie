<?php
require_once dirname(__FILE__) . '/../../conf/config.php';

switch ($act) {
    case 'softwareRegisterPop' :
        Auth::getAuth('17', 'read');

        if ((int) $request['idx'] > 0) {
            $client = new Client;
            $client_list = $client->getList(array('idx' => $request['idx']));
        }

        $develop = new Develop;
        $develop_list = $develop->getList();

        include_once _VIEW_PATH . 'client_software_detail_pop.html';
        break;

    case 'softwareRegister' :
        Auth::getAuth('17', 'write');

        $client = new Client;
        $client_cnt = $client->getCount(array('name' => $request['name']));
        if ((int) $client_cnt > 0) {
            $message = "중복된 응용프로그램 이름입니다.";
            $back = true;
            include_once _VIEW_PATH . 'redirect.html';
        }

        if ($client->doRegister($request)) {
            $message = "등록되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "등록에 실패하였습니다.";
            $back = true;
        }

        include_once _VIEW_PATH . 'redirect.html';
        break;

    case 'softwareModify' :
        Auth::getAuth('17', 'write');

        if ((int) $request['idx'] == 0) {
            $message = "유효하지 않은 응용프로그램입니다.";
            $back = true;
            include_once _VIEW_PATH . 'redirect.html';
        }

        $client = new Client;
        $client_cnt = $client->getCount(array('name' => $request['name'], 'not_idx' => (int) $request['idx']));
        if ((int) $client_cnt > 0) {
            $message = "중복된 응용프로그램 이름입니다.";
            $back = true;
            include_once _VIEW_PATH . 'redirect.html';
        }

        if ($client->doModify($request)) {
            $message = "수정되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "수정에 실패하였습니다.";
            $back = true;
        }

        include_once _VIEW_PATH . 'redirect.html';
        break;
    case 'software' :
        Auth::getAuth('10', 'read');

        $request['page'] = ((int)$request['page'] > 0) ? $request['page'] : 1;
        $request['scale'] = ((int)$request['scale'] > 0) ? $request['scale'] : 50;
        $params = $request;
        unset($params['scale']);
        unset($params['page']);

        $client = new Client;
        $total = $client->getCount($params);

        $url = $_SERVER['PHP_SELF'];
        $page = new Page($request['page'], $total, $url, $params, $request['scale']);
        list($page_html, $offset, $limit) = $page->init();

        $list = $client->getList($params, $offset, $limit, array('idx DESC'));

        include_once _VIEW_PATH . 'client_software_setting.html';
        break;

    case 'commonApply' :
        Auth::getAuth('18', 'write');

        $common = new Common;
        if ($client->doRegister($request)) {
            $message = "등록되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "등록에 실패하였습니다.";
            $back = true;
        }

        include_once _VIEW_PATH . 'redirect.html';
        break;

    case 'common' :
        Auth::getAuth('11', 'read');

        $request['page'] = ((int)$request['page'] > 0) ? $request['page'] : 1;
        $request['scale'] = ((int)$request['scale'] > 0) ? $request['scale'] : 50;
        $params = $request;
        unset($params['scale']);
        unset($params['page']);

        $common = new Common;
        $total = $common->getCount($params);

        $url = $_SERVER['PHP_SELF'];
        $page = new Page($request['page'], $total, $url, $params, $request['scale']);
        list($page_html, $offset, $limit) = $page->init();

        $list = $common->getList($params, $offset, $limit, array('idx DESC'));
        include_once _VIEW_PATH . 'client_common_setting.html';
        break;

    case 'oslangRegisterPop' :
        Auth::getAuth('16', 'read');

        if (strlen($request['code']) > 0) {
            $oslang = new OsLang;
            $oslang_list = $oslang->getList(array('code' => $request['code']));
        }

        include_once _VIEW_PATH . 'client_os_language_detail_pop.html';
        break;

    case 'oslangRegister' :
        Auth::getAuth('16', 'write');

        $oslang = new OsLang;

        $oslang_cnt = $oslang->getCount(array('code' => $request['code']));
        if ((int) $oslang_cnt > 0) {
            $message = "중복된 언어코드 입니다.";
            $back = true;
            include_once _VIEW_PATH . 'redirect.html';
        }

        if ($oslang->doRegister($request)) {
            $message = "등록되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "등록에 실패하였습니다.";
            $back = true;
        }

        include_once _VIEW_PATH . 'redirect.html';
        break;
    case 'oslangModify' :
        Auth::getAuth('16', 'write');

        $oslang = new OsLang;
        if ($oslang->doModify($request)) {
            $message = "수정되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "수정에 실패하였습니다.";
            $back = true;
        }

        include_once _VIEW_PATH . 'redirect.html';
        break;

    case 'oslang' :
    default :
        Auth::getAuth('9', 'read');

        $request['page'] = ((int)$request['page'] > 0) ? $request['page'] : 1;
        $request['scale'] = ((int)$request['scale'] > 0) ? $request['scale'] : 50;
        $params = $request;
        unset($params['scale']);
        unset($params['page']);

        $oslang = new OsLang;
        $total = $oslang->getCount($params);

        $url = $_SERVER['PHP_SELF'];
        $page = new Page($request['page'], $total, $url, $params, $request['scale']);
        list($page_html, $offset, $limit) = $page->init();

        $list = $oslang->getList($params, $offset, $limit, array('code DESC'));

    include_once _VIEW_PATH . 'client_os_language_setting.html';
        break;

}