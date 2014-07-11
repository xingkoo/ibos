<?php

class MainEditorController extends ICController
{
    public function actionImageUp()
    {
        $config = array(
            "savePath"   => "data/editor/image/" . Ibos::app()->user->uid . "/",
            "maxSize"    => 2000,
            "allowFiles" => array(".gif", ".png", ".jpg", ".jpeg", ".bmp")
            );
        $title = htmlspecialchars(EnvUtil::getRequest("pictitle"), ENT_QUOTES);
        $up = new EditorUploader("upfile", $config);
        $info = $up->getFileInfo();
        echo "{'url':'" . $info["url"] . "','title':'" . $title . "','original':'" . $info["originalName"] . "','state':'" . $info["state"] . "'}";
    }

    public function actionImageManager()
    {
        $path = "data/editor/image/" . Ibos::app()->user->uid;
        $action = EnvUtil::getRequest("action");

        if ($action == "get") {
            if (!defined("SAE_TMP_PATH")) {
                $files = $this->getfiles($path);

                if (!$files) {
                    return null;
                }

                rsort($files, SORT_STRING);
                $str = "";

                foreach ($files as $file) {
                    $str .= "../../../../../../" . $file . "ue_separate_ue";
                }

                echo $str;
            } else {
                $st = new SaeStorage();
                $num = 0;

                while ($ret = $st->getList("data", $path, 100, $num)) {
                    foreach ($ret as $file) {
                        if (preg_match("/\.(gif|jpeg|jpg|png|bmp)$/i", $file)) {
                            echo $st->getUrl("data", $file) . "ue_separate_ue";
                        }

                        $num++;
                    }
                }
            }
        }
    }

    private function getfiles($path, &$files = array())
    {
        if (!is_dir($path)) {
            return null;
        }

        $handle = opendir($path);

        while (false !== $file = readdir($handle)) {
            if (($file != ".") && ($file != "..")) {
                $path2 = $path . "/" . $file;

                if (is_dir($path2)) {
                    $this->getfiles($path2, $files);
                } elseif (preg_match("/\.(gif|jpeg|jpg|png|bmp)$/i", $file)) {
                    $files[] = $path2;
                }
            }
        }

        return $files;
    }

    public function actionFileUp()
    {
        $config = array(
            "savePath"   => "data/editor/file/" . Ibos::app()->user->uid . "/",
            "allowFiles" => array(".rar", ".doc", ".docx", ".zip", ".pdf", ".txt", ".swf", ".wmv"),
            "maxSize"    => 5000
            );
        $up = new EditorUploader("upfile", $config);
        $info = $up->getFileInfo();
        echo "{\"url\":\"" . $info["url"] . "\",\"fileType\":\"" . $info["type"] . "\",\"original\":\"" . $info["originalName"] . "\",\"state\":\"" . $info["state"] . "\"}";
    }
}
