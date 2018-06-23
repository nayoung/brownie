<?php
require_once dirname(__FILE__) . '/../../conf/config.php';

switch ($act) {
    case 'registerPop' :
        Auth::getAuth('15', 'read');

        $oslang = new OsLang;
        $oslang_list = $oslang->getList();

        if ((int) $request['jobid'] > 0) {
            $job = new Job;
            $job_list = $job->getList(array('jobid' => (int) $request['jobid']));

            $task = new Task;
            $task_list = $task->getList(array('jobid' => (int) $request['jobid']), 0, 0, array("step ASC"));
        }

        include_once _VIEW_PATH . 'job_detail_pop.html';
        break;

    case 'jobRegister' :
        Auth::getAuth('15', 'write');

        $job = new Job;
        if ($job->doRegister($request)) {
            $message = "등록되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "등록에 실패하였습니다.";
            $back = true;
        }
        include_once _VIEW_PATH . 'redirect.html';
        break;

    case 'jobModify' :
        Auth::getAuth('15', 'write');

        $job = new Job;
        if ($job->doModify($request)) {
            $message = "수정되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "수정에 실패하였습니다.";
            $back = true;
        }
        include_once _VIEW_PATH . 'redirect.html';
        break;

    case 'taskApply' :
        Auth::getAuth('15', 'write');

        $task = new Task;
        if ($task->doRegister($request)) {
            $message = "등록/수정 되었습니다.";
            $opener_reload = true;
            $close = true;
        } else {
            $message = "등록/수정에 실패하였습니다.";
            $back = true;
        }
        include_once _VIEW_PATH . 'redirect.html';
        break;

    default :
        Auth::getAuth('5', 'read');

        $request['page']    = ((int)$request['page'] > 0) ? $request['page'] : 1;
        $request['scale']   = ((int)$request['scale'] > 0) ? $request['scale'] : 50;
        $params = $request;
        unset($params['scale']);
        unset($params['page']);

        $oslang = new OsLang;
        $oslang_list = $oslang->getList();
        $oslang_array = array();
        foreach ($oslang_list as $ol) {
            $oslang_array[$ol['code']] = $ol['name'];
        }

        $job = new Job;
        $total = $job->getCount($params);

        $url = $_SERVER['PHP_SELF'];
        $page = new Page($request['page'], $total, $url, $params, $request['scale']);
        list($page_html, $offset, $limit) = $page->init();

        $list = $job->getList($params, $offset, $limit, array('jobid DESC'));

        include_once _VIEW_PATH . 'job.html';
        break;
}