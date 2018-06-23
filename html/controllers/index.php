<?php
require_once dirname(__FILE__) . '/../../conf/config.php';

$url = $_SESSION['first_page'];
include_once _VIEW_PATH . 'redirect.html';
