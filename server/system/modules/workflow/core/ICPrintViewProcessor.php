<?php

class ICPrintViewProcessor extends ICViewProcessor
{
    protected function getValue($item)
    {
        $ename = "data_" . $item["itemid"];
        $value = (isset($this->itemData[$ename]) ? $this->itemData[$ename] : "");
        return $value;
    }

    public function textProcessor($item, $readOnly)
    {
        $hidden = $item["data-hide"];

        if ($hidden == "1") {
            $value = "";
        } else {
            $value = $this->getValue($item);
        }

        return $value;
    }

    public function dateProcessor($item, $readOnly)
    {
        return $this->getValue($item);
    }

    public function checkboxProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);

        if ($value == "on") {
            $value = "<input type=\"checkbox\" checked onclick='this.checked=1;'>";
        } else {
            $value = "<input type=\"checkbox\" onclick='this.checked=0;'>";
        }

        return $value;
    }

    public function textareaProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);
        $rich = $item["data-rich"];

        if (!$rich) {
            $search = array("<", ">", chr(10), "  ");
            $replace = array("&lt;", "&gt;", "<br>", "&nbsp;&nbsp;");
            $value = str_replace($search, $replace, $value);
        }

        return $value;
    }

    public function selectProcessor($item, $readOnly)
    {
        return $this->getValue($item);
    }

    public function radioProcessor($item, $readOnly)
    {
        $radio_field = $item["data-radio-field"];
        $radio_check = (isset($item["data-radio-check"]) ? $item["data-radio-check"] : "");
        $radioarr = explode("`", rtrim($radio_field, "`"));
        $eleout = "";
        $value = $this->getValue($item);

        if ($value != "") {
            $radio_check = $value;
        }

        $disabled = ($readOnly ? "disabled" : "");

        foreach ($radioarr as $radio) {
            $checked = "";
            if (($radio == $radio_check) && !empty($radio_check)) {
                $checked = "checked";
            }

            $eleout .= "<label><input type=\"radio\" disabled name=\"data_" . $item["itemid"] . "\" value=\"" . $radio . "\" " . $checked . " " . $disabled . ">" . $radio . "</label>";
        }

        return $eleout;
    }

    public function progressbarProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);
        if (($value == "") || ($value < 0)) {
            $value = 0;
        }

        if (100 < $value) {
            $value = 100;
        }

        $eleout = "\t\t\t\t <div data-item=\"progressbar\" data-step=\"{$item["data-step"]}\" class=\"progress progress-striped active\" style=\"width:{$item["data-width"]}px\" title=\"{$item["data-title"]}\">\r\n\t\t\t\t\t<div class=\"progress-bar progress-bar-{$item["data-progress-style"]}\" style=\"width:$value%\"></div>\r\n\t\t\t\t </div>";
        return $eleout;
    }

    public function labelProcessor($item, $readOnly)
    {
        return $item["content"];
    }

    public function userProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);

        if (!empty($value)) {
            $ids = StringUtil::getId($value, true);
            $values = "";

            foreach ($ids as $prefix => $id) {
                if ($prefix == "u") {
                    $values = User::model()->fetchRealnamesByUids($id);
                }

                if ($prefix == "d") {
                    $values = Department::model()->fetchDeptNameByDeptId($id);
                }

                if ($prefix == "p") {
                    $values = Position::model()->fetchPosNameByPosId($id);
                }
            }

            return $values;
        }

        return $value;
    }

    public function autoProcessor($item, $readOnly)
    {
        $field = $item["data-field"];
        $value = $this->getValue($item);
        $isTextAuto = substr($field, 0, 8) !== "sys_list";

        if ($isTextAuto) {
            return $this->textProcessor($item, $readOnly);
        } elseif (!empty($value)) {
            switch ($field) {
                case "sys_list_dept":
                    $value = Department::model()->fetchDeptNameByDeptId($value, ",", true);
                    break;

                case "sys_list_pos":
                    $value = Position::model()->fetchPosNameByPosId($value);
                    break;

                default:
                    $value = User::model()->fetchRealnameByUid($value);
                    break;
            }
        }

        return $value;
    }

    public function calcProcessor($item, $readOnly)
    {
        return $this->getValue($item);
    }

    public function listviewProcessor($item, $readOnly)
    {
        $sumFlag = 0;
        $titleStr = $valueStr = "";
        $titleArr = explode("`", trim($item["data-lv-title"], "`"));

        foreach ($titleArr as $title) {
            $titleStr .= "<th style='width:1%'>" . $title . "</th>\n";
        }

        $value = $this->getValue($item);
        $lvSum = $item["data-lv-sum"];
        $sumArr = explode("`", trim($item["data-lv-sum"], "`"));

        if (strstr($lvSum, "1")) {
            $sumFlag = 1;
        }

        $valArr = explode("\r\n", trim($value, "`"));
        $valArr = str_replace("&lt;br&gt;", "<br />", $valArr);
        $sumData = array();

        foreach ($valArr as $k => $v) {
            if (!empty($v)) {
                $valueStr .= "<tr>\n";
                $tr = $v;
                $trArr = explode("`", rtrim($tr, "`"));

                foreach ($trArr as $j => $i) {
                    if ($sumArr[$j] == 1) {
                        $sumData[$j] = str_replace("&nbsp;", "", $sumData[$j]) + str_replace("&nbsp;", "", $trArr[$j]);
                    }

                    $td = $trArr[$j];

                    if ($td == "") {
                        $td = "&nbsp;";
                    }

                    $valueStr .= "<td style='text-align:center;'>" . $td . "</td>\n";
                }

                $valueStr .= "</tr>\n";
            }
        }

        if (($sumFlag == 1) && (0 < count($valArr))) {
            $valueStr .= "<tr>\n";

            foreach ($titleArr as $k => $v) {
                if ($sumData[$k] == "") {
                    $sumValue = "&nbsp;";
                } else {
                    $sumValue = "合计:" . $sumData[$k];
                }

                $valueStr .= "<td style='text-align:center;'>" . $sumValue . "</td>\n";
            }

            $valueStr .= "</tr>\n";
        }

        $eleout = "\t\t\t\t<table class=\"table table-head-condensed table-condensed table-bordered\">\r\n\t\t\t\t\t<thead>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t$titleStr\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</thead>\r\n\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t$valueStr\r\n\t\t\t\t\t</tbody>\r\n\t\t\t\t</table>";
        return $eleout;
    }

    public function imguploadProcessor($item, $readOnly)
    {
        $imgWidth = $item["data-width"];
        $imgHeight = $item["data-height"];
        $value = $this->getValue($item);

        if ($value !== "") {
            $attach = AttachmentN::model()->fetch(sprintf("rid:%d", $this->run->runid), $value);
            $imgSrc = FileUtil::fileName(FileUtil::getAttachUrl() . "/" . $attach["attachment"]);
            $imgID = $value;
        } else {
            $imgSrc = "";
            $imgID = "";
        }

        $bg = Ibos::app()->assetManager->getAssetsUrl("workflow") . "/image/pic_upload.png";

        if ($readOnly) {
            $read = 1;
        } else {
            $read = 0;
        }

        $eleout = "\t\t\t\t<div data-item=\"imgupload\" data-width=\"$imgWidth\" data-height=\"$imgHeight\" data-id=\"{$item["itemid"]}\" data-bg=\"$bg\" data-read=\"$read\" data-src=\"$imgSrc\">\r\n\t\t\t\t\t<input type=\"hidden\" name=\"imgid_{$item["itemid"]}\" value=\"$imgID\" />\r\n\t\t\t\t</div>";
        return $eleout;
    }
}
