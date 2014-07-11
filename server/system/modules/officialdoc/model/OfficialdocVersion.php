<?php

class OfficialdocVersion extends ICModel
{
    public static function model($className = "OfficialdocVersion")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{doc_version}}";
    }

    public function fetchAllByDocid($docid)
    {
        $versionData = $this->fetchAll("docid=:docid ORDER BY version DESC", array(":docid" => $docid));

        if (!empty($versionData)) {
            $users = Yii::app()->setting->get("cache/users");

            foreach ($versionData as $key => $version) {
                $versionData[$key]["uptime"] = ConvertUtil::formatDate($version["uptime"], "u");
                $versionData[$key]["editor"] = (isset($users[$version["editor"]]) ? $users[$version["editor"]]["realname"] : "--");
                $versionData[$key]["showVersion"] = OfficialdocUtil::changeVersion($version["version"]);
            }
        }

        return $versionData;
    }

    public function insertVersion($docid, $uid, $nextVersion)
    {
        $version = array("docid" => $docid, "author" => $uid, "addtime" => TIMESTAMP, "version" => $nextVersion);
        return $this->add($version);
    }

    public function deleteAllByDocids($ids)
    {
        return $this->deleteAll("docid IN ($ids)");
    }
}
