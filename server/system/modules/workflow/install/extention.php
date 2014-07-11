<?php

$creditExists = CreditRule::model()->countByAttributes(array("action" => "wfnextpost"));

if (!$creditExists) {
    $data = array("rulename" => "工作流转交", "action" => "wfnextpost", "cycletype" => "1", "rewardnum" => "3", "extcredits1" => "0", "extcredits2" => "1", "extcredits3" => "1");
    CreditRule::model()->add($data);
}
