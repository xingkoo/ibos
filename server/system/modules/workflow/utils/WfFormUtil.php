<?php

class WfFormUtil
{
    public static function getAllItemName($structure, $exceptType = array(), $exceptTitle = "")
    {
        $temp = array();

        foreach ($structure as $config) {
            if (!in_array($config["data-type"], $exceptType) && ($config["data-type"] !== "label")) {
                $temp[] = $config["data-title"];
            }
        }

        if (!StringUtil::findIn($exceptTitle, "[A@]")) {
            $temp[] = "[A@]";
        }

        if (!StringUtil::findIn($exceptTitle, "[B@]")) {
            $temp[] = "[B@]";
        }

        $allItemName = str_replace(array("+", "#"), array("＋", "＃"), $temp);
        return $allItemName;
    }

    public static function import($id, $file)
    {
        $newForm = array();
        $content = FileUtil::readFile($file);
        $printModel = str_replace("'", "\'", $content);

        if (preg_match_all("'<script[^>]*?>.*?</script>'si", $printModel, $script)) {
            $scriptStr = implode("\n\r", $script[0]);
            $scriptStr = preg_replace("'<script[^>]*?>'", "", $scriptStr);
            $scriptStr = str_ireplace("</script>", "", $scriptStr);
            $newForm["script"] = $scriptStr;
            $printModel = preg_replace("'<script[^>]*?>.*?</script>'si", "", $printModel);
        }

        if (preg_match_all("'<style[^>]*?>.*?</style>'si", $printModel, $css)) {
            $cssStr = implode("\n\r", $css[0]);
            $cssStr = preg_replace("'<style[^>]*?>'", "", $cssStr);
            $cssStr = str_ireplace("</style>", "", $cssStr);
            $newForm["css"] = $cssStr;
            $printModel = preg_replace("'<style[^>]*?>.*?</style>'si", "", $printModel);
        }

        $newForm["printmodel"] = $printModel;
        FlowFormType::model()->modify($id, $newForm);
        $form = new ICFlowForm($id);
        $form->getParser()->parse();
        FileUtil::deleteFile($file);
    }

    public static function export($idstr)
    {
        $idArr = (is_array($idstr) ? $idstr : explode(",", $idstr));

        if (1 < count($idArr)) {
            $zip = new Zip();
            $exportFileName = Ibos::lang("Form export file pack", "workflow.default", array("{date}" => date("Y-m-d")));
            $zipFileName = FileUtil::getTempPath() . "/" . TIMESTAMP . ".zip";

            foreach ($idArr as $id) {
                $form = self::handleExportSingleForm($id);
                $zip->addFile($form["content"], sprintf("%s.html", ConvertUtil::iIconv($form["title"], CHARSET, "gbk")));
            }

            $fp = fopen($zipFileName, "w");

            if (@fwrite($fp, $zip->file()) !== false) {
                header("Cache-control: private");
                header("Content-type: application/octet-stream");
                header("Accept-Ranges: bytes");
                header("Content-Length: " . sprintf("%u", FileUtil::fileSize($zipFileName)));
                header("Content-Disposition: attachment; filename=" . $exportFileName . ".zip");
                readfile($zipFileName);
                exit();
            }
        } else {
            $id = implode(",", $idArr);
            $form = self::handleExportSingleForm($id);
            ob_end_clean();
            header("Cache-control: private");
            header("Content-type: text/plain");
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . strlen($form["content"]));
            header("Content-Disposition: attachment; filename=" . $form["title"] . ".html");
            echo $form["content"];
        }
    }

    private static function handleExportSingleForm($id)
    {
        $form = new ICFlowForm($id);
        $content = self::handleExportContent($form->toArray());
        $formName = self::handleExportTitle($form->formname);
        unset($form);
        return array("content" => $content, "title" => $formName);
    }

    private static function handleExportTitle($formName)
    {
        $formName = str_replace(array("\\", "/", ":", "*", "?", "\\\"", "<", ">", "|"), "", $formName);
        return $formName;
    }

    private static function handleExportContent($form)
    {
        $pre = "\t\t\t<style>{$form["css"]}</style>\r\n\t\t\t<script type=\"text/javascript\">{$form["script"]}</script>";
        $printModel = $pre . $form["printmodel"];
        return $printModel;
    }
}
