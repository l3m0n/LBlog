<?php
session_start();
//error_reporting(0);

header("Content-type:text/html;charset=utf-8");
!defined('ROOT_PATH') ? define('ROOT_PATH', str_replace('\\', '/', dirname(__FILE__))) : "";
!defined('APP_PATH') ? define('APP_PATH','/blog/b') : "";

require ROOT_PATH . '/common/config.php';
require ROOT_PATH . '/config/db_config.php';

require ROOT_PATH . '/common/route.php';
require ROOT_PATH . '/common/controller.php';
require ROOT_PATH . '/common/view.php';
require ROOT_PATH . '/common/model.php';
