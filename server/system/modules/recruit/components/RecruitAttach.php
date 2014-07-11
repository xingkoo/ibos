<?php

class RecruitAttach extends ICAttach
{
    public function upload()
    {
        $uid = intval(EnvUtil::getRequest("uid"));
        $this->upload->save();
        $attach = $this->upload->getAttach();
        $attachment = $attach["type"] . "/" . $attach["attachment"];
        $data = array("dateline" => TIMESTAMP, "filename" => $attach["name"], "filesize" => $attach["size"], "attachment" => $attachment, "isimage" => $attach["isimage"], "uid" => $uid);
        $aid = Attachment::model()->add(array("uid" => $uid, "tableid" => 127), true);
        $data["aid"] = $aid;
        AttachmentUnused::model()->add($data);
        $file["aid"] = $aid;
        $file["name"] = $attach["name"];
        $file["url"] = FileUtil::fileName(FileUtil::getAttachUrl() . "/" . $attachment);
        if (!empty($file) && is_array($file)) {
            return CJSON::encode($file);
        } else {
            return CJSON::encode(array("aid" => 0, "url" => 0, "name" => 0));
        }
    }
}
