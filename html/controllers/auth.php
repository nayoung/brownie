<?php
require_once dirname(__FILE__) . '/../../conf/config.php';

switch ($act) {
    case 'registerPop' :
        Auth::getAuth('20', 'read');

        if ((int) $request['id'] > 0) {
            $auth = new Auth;
            $auth_list = $auth->getList(array('id' => $request['id']));
        }
        include_once _VIEW_PATH . 'auth_detail_pop.html';
        break;

    case 'register' :
        Auth::getAuth('20', 'write');

        $auth = new Auth;
        if ((int) $request['id'] > 0) {
            $auth_cnt = $auth->getCount(array('name' => $request['name'], 'not_id' => (int) $request['id']));
            if ((int) $auth_cnt > 0) {
                $message = "중복된 권한그룹명 입니다.";
                $back = true;
                include_once _VIEW_PATH . 'redirect.html';
            }

            if ($auth->doModify($request)) {
                $message = "수정되었습니다.";
                $opener_reload = true;
                $close = true;
            } else {
                $message = "수정에 실패하였습니다.";
                $back = true;
            }
        } else {
            $auth_cnt = $auth->getCount(array('name' => $request['name']));
            if ((int) $auth_cnt > 0) {
                $message = "중복된 권한그룹명 입니다.";
                $back = true;
                include_once _VIEW_PATH . 'redirect.html';
            }

            if ($auth->doRegister($request)) {
                $message = "등록되었습니다.";
                $opener_reload = true;
                $close = true;
            } else {
                $message = "등록에 실패하였습니다.";
                $back = true;
            }
        }

        include_once _VIEW_PATH . 'redirect.html';
        break;

    case 'apply' :
        Auth::getAuth('13', 'write');

        $auth = new Auth;
        if ($auth->apply($request) == true) {
            $message = "적용되었습니다.";
        } else {
            $message = "적용에 실패하였습니다.";
        }
        $back = true;

        include_once _VIEW_PATH . 'redirect.html';
        break;

    default :
        Auth::getAuth('13', 'read');

        $params = $request;

        $auth = new Auth;
        $list = $auth->getList(array(), 0,0, array("id ASC"));

        $request['auth'] = ((int) $request['auth'] == 0 )? $list[0]['id']: $request['auth'];
        $auth_menu = $auth->getAuthMenu($request['auth']);

        $auth_menu_array = array();
        foreach ($auth_menu as $am) {
            $auth_menu_array[$am['menu']] = $am;
        }

        $menu = new Menu;
        $menu_list = $menu->getList();

        $menu_array = array();
        foreach ($menu_list as $m) {
            $m['rowspan'] = 1;
            if ($m['parent_id'] == 0) {
                // top
                $menu_array[$m['id']] = $m;
            } else if (sizeof($menu_array[$m['parent_id']]) > 0) {
                // 대분류
                $menu_array[$m['parent_id']]['child'][$m['id']] = $m;
                if (sizeof($menu_array[$m['parent_id']]['child']) > 1) {
                    $menu_array[$m['parent_id']]['rowspan']++;
                }
            } else {
                // 중분류
                foreach ($menu_array as $idx => $ma) {
                    if (array_key_exists($m['parent_id'], $ma['child'])) {
                        $menu_array[$idx]['child'][$m['parent_id']]['child'][] = $m;
                        if (sizeof($menu_array[$idx]['child'][$m['parent_id']]['child']) > 1) {
                            $menu_array[$idx]['rowspan']++;
                            $menu_array[$idx]['child'][$m['parent_id']]['rowspan']++;
                        }
                        break;
                    }
                }
            }
        }

        include_once _VIEW_PATH . 'auth.html';
        break;
}

