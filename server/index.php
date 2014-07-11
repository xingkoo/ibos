<?php

define("ENGINE", "LOCAL");
define("PATH_ROOT", dirname(__FILE__));
$defines = PATH_ROOT . "/system/defines.php";
$yii = PATH_ROOT . "/library/yii.php";
$config = PATH_ROOT . "/system/config/common.php";
$ibosApplication = PATH_ROOT . "/system/core/components/ICApplication.php";
require_once ($defines);
require_once ($yii);
require_once ($ibosApplication);
$ibos = Yii::createApplication("ICApplication", $config);
$ibos->run();
exit();
