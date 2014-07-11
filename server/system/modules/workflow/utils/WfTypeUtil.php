<?php

class WfTypeUtil
{
    public static function export($id)
    {
        $flow = FlowType::model()->fetchByPk($id);
        $rs = array();

        if ($flow) {
            $name = $flow["name"];
            $rs["base"] = $flow;
        }

        foreach (FlowProcess::model()->fetchAllByFlowId($id) as $process) {
            $rs["process_" . $process["processid"]] = $process;
        }

        $xml = XmlUtil::arrayToXml($rs);
        ob_end_clean();
        header("Cache-control: private");
        header("Content-type: text/xml");
        header("Content-Disposition: attachment; filename= $name.xml");
        exit($xml);
    }

    public static function import($id, $file, $importUser = false)
    {
        $content = FileUtil::readFile($file);
        $xml = XmlUtil::xmlToArray($content);
        unset($xml["base"]["flowid"]);
        unset($xml["base"]["name"]);
        unset($xml["base"]["formid"]);
        unset($xml["base"]["sort"]);
        $user = array("newuser", "deptid", "uid", "positionid", "autouserop", "autouser", "mailto");
        $data = array();

        foreach ($xml["base"] as $key => $value) {
            $key = strtolower($key);
            if (!$importUser && in_array($key, $user)) {
                continue;
            }

            $data[$key] = $value;
        }

        FlowType::model()->modify($id, $data);
        unset($xml["base"]);
        unset($data);
        FlowProcess::model()->deleteAllByAttributes(array("flowid" => $id));

        if (!empty($xml)) {
            foreach ($xml as $process) {
                unset($process["id"]);
                $data = array();
                $process["flowid"] = $id;

                foreach ($process as $k => $v) {
                    if (!$importUser && in_array($k, $user)) {
                        continue;
                    }

                    $data[$k] = $v;
                }

                FlowProcess::model()->add($data);
            }
        }

        FileUtil::deleteFile($file);
    }
}
