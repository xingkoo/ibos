<?php
	$call = $_GET['callback'];

	$data = array(
		"datas" => array(
			array('runid' => 1, 'runName' => '张小牛请假申请表', 'flowprocess' => 4, 'opflag' => 1, 'focus' => 1, 'begintime' =>  '1394521681', 'stepname' => '部门审批', 'isUnread' => 1),
			array('runid' => 2, 'runName' => '张小牛加薪申请表', 'flowprocess' => 3, 'opflag' => 0, 'focus' => 0, 'begintime' =>  '1394521681', 'stepname' => '总经理审批', 'isUnread' => 0),
			array('runid' => 3, 'runName' => '张小牛加薪申请表', 'flowprocess' => 3, 'opflag' => 1, 'focus' => 0, 'begintime' =>  '1394521681', 'stepname' => '总经理审批', 'isUnread' => 0),
			array('runid' => 4, 'runName' => '张小牛加薪申请表', 'flowprocess' => 3, 'opflag' => 0, 'focus' => 0, 'begintime' =>  '1394521681', 'stepname' => '总经理审批', 'isUnread' => 0)
		),
		"pages" => array(
			array('pageCount' => 1)
		)
	);

	echo $call.'('.json_encode($data).')';
?>