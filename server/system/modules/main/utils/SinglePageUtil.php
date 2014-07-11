<?php

class SinglePageUtil
{
    public static function parse($html, $replace)
    {
        Ibos::import("application.extensions.simple_html_dom", true);
        $doc = str_get_html($html);

        if (!$doc) {
            return null;
        }

        $e = $doc->find("div[id=page_content]", 0);

        if (!$e) {
            return null;
        }

        $e->innertext = $replace;
        return $doc;
    }

    public static function getTplEditorContent($file)
    {
        Ibos::import("application.extensions.simple_html_dom", true);
        $doc = file_get_html($file);

        if (!$doc) {
            return null;
        }

        $e = $doc->find("div[id=page_content]", 0);

        if (!$e) {
            return null;
        }

        return $e->innertext;
    }

    public static function getAllTpls()
    {
        $tplPath = "data/page/";
        $allowExt = array("php");
        $dir = opendir($tplPath);
        $tpls = array();

        while (($file = readdir($dir)) !== false) {
            if (($file != ".") && ($file != "..") && in_array(pathinfo($file, PATHINFO_EXTENSION), $allowExt)) {
                $tpls[] = $file;
            }
        }

        closedir($dir);
        return self::handleFileName($tpls);
    }

    public static function handleFileName($files)
    {
        $ret = array();

        if (!empty($files)) {
            foreach ($files as $file) {
                $info = pathinfo($file);
                $filename = $info["filename"];
                $ret[$filename] = Ibos::lang("Page_" . $filename);
            }
        }

        return $ret;
    }
}
