<?php

$settingFields = "statmodules";
Setting::model()->deleteAll("FIND_IN_SET(skey,'$settingFields')");
Menu::model()->deleteAllByAttributes(array("m" => "statistics"));
