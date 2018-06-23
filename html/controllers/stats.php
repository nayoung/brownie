<?php
require_once dirname(__FILE__) . '/../../conf/config.php';

switch ($act) {
    case 'software' :
        Auth::getAuth('7', 'read');

        include_once _VIEW_PATH . 'stats_software.html';
        break;

    case 'job' :
        Auth::getAuth('8', 'read');

        include_once _VIEW_PATH . 'stats_job.html';
        break;

    case 'normal' :
    default :
    Auth::getAuth('6', 'read');

    include_once _VIEW_PATH . 'stats_normal.html';

    break;

}