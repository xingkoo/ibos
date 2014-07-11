<?php

class ResumeAnalysis
{
    private $charset = "utf-8";
    private $content = "";
    private $fieldpos = array();
    private $fieldweights = array();
    private $valpos = array();
    private $config = array();

    public function __construct($content = "", $config = array())
    {
        $this->content = $content;
        $this->config = $config;
    }

    public function parse_content()
    {
        $result = array();
        $specialpostypes = array();

        foreach ($this->config as $configinfo) {
            $poslist = &$this->fieldpos[$configinfo["id"]];
            $weightlist = &$this->fieldweights[$configinfo["id"]];
            $poslist = $weightlist = array();

            foreach ($configinfo["field"] as $index => $field) {
                $poslist = array_merge($poslist, $this->get_pos($field));
            }

            $poslist = $this->pos_clear_include($poslist);
            $weightlist = $this->assess_weight($poslist);

            if (in_array($configinfo["type"], array("name", "gender", "email", "age", "mobile", "phone", "idcard", "maritalstatus"))) {
                list($poslist, $weightlist) = $this->restore_special_pos($poslist, $weightlist, $configinfo["type"]);
            }

            $poslist = $this->pos_sort_by_weight($poslist, $weightlist);
            $result[$configinfo["id"]] = "";
        }

        $posarray = $this->parse_pos_array($this->fieldpos);
        $i = 0;

        for ($len = count($posarray); $i < $len; $i++) {
            $id = $this->valpos[$i];
            $spos = $posarray[$i];

            if (isset($posarray[$i + 1])) {
                $nextid = $this->valpos[$i + 1];
                $max_weight_pos = $this->fieldpos[$nextid][0];
                $epos = $max_weight_pos[1] - $max_weight_pos[1] - $max_weight_pos[0];
            } else {
                $epos = 9999999999;
            }

            $limitstr = mb_substr($this->content, $spos, $epos - $spos, $this->charset);

            if (strpos($limitstr, "\n")) {
                $matches = array();
                if ((($this->fieldpos[$id][0][0] == $this->fieldpos[$id][0][1]) && (3 < preg_match_all("/[^\s\\t:：　]+/", $limitstr, $matches))) || (!preg_match("/^[\s\\t　:：]*[\\r\\n]+/", $limitstr) && (2 < preg_match_all("/[^\s\\t:：　]+[\s\\t:：]+[^\s\\t:：　]+[\s\\t　]*/", $limitstr, $matches)))) {
                    $near_field_str = $matches[0][0];
                    $strpos = mb_strpos($limitstr, $near_field_str, 0, $this->charset);
                    $limitepos = $spos + $strpos + mb_strlen($near_field_str, $this->charset);
                    $epos = ($limitepos < $epos ? $limitepos : $epos);
                }
            }

            $fieldvalue = mb_substr($this->content, $spos, $epos - $spos, $this->charset);
            $result[$id] = trim($fieldvalue, " 　:：;；\t\n\r/");
        }

        return $result;
    }

    private function restore_special_pos($poslist, $weightlist, $type)
    {
        switch ($type) {
            case "name":
                $familysurnames = array("李", "王", "张", "刘", "陈", "杨", "黄", "孙", "周", "吴", "徐", "赵", "朱", "马", "胡", "郭", "林", "何", "高", "梁", "郑", "罗", "宋", "谢", "唐", "韩", "曹", "许", "邓", "萧", "冯", "曾", "程", "蔡", "彭", "潘", "袁", "于", "董", "余", "苏", "叶", "吕", "魏", "蒋", "田", "杜", "丁", "沈", "姜", "范", "江", "傅", "钟", "卢", "汪", "戴", "崔", "任", "陆", "廖", "姚", "方", "金", "邱", "夏", "谭", "韦", "贾", "邹", "石", "熊", "孟", "秦", "阎", "薛", "侯", "雷", "白", "龙", "段", "郝", "孔", "邵", "史", "毛", "常", "万", "顾", "赖", "武", "康", "贺", "严", "尹", "钱", "施", "牛", "洪", "龚", "汤", "陶", "黎", "温", "莫", "易", "樊", "乔", "文", "安", "殷", "颜", "庄", "章", "鲁", "倪", "庞", "邢", "俞", "翟", "蓝", "聂", "齐", "向", "申", "葛", "柴", "伍", "覃", "骆", "关", "焦", "柳", "欧", "祝", "纪", "尚", "毕", "耿", "芦", "左", "季", "管", "符", "辛", "苗", "詹", "曲", "欧阳", "靳", "祁", "路", "涂", "兰", "甘", "裴", "梅", "童", "翁", "霍", "游", "阮", "尤", "岳", "柯", "牟", "滕", "谷", "舒", "卜", "成", "饶", "宁", "凌", "盛", "查", "单", "冉", "鲍", "华", "包", "屈", "房", "喻", "解", "蒲", "卫", "简", "时", "连", "车", "项", "闵", "邬", "吉", "党", "阳", "司", "费", "蒙", "席", "晏", "隋", "古", "强", "穆", "姬", "宫", "景", "米", "麦", "谈", "柏", "瞿", "艾", "沙", "鄢", "桂", "窦", "郁", "缪", "畅", "巩", "卓", "褚", "栾", "戚", "全", "娄", "甄", "郎", "池", "丛", "边", "岑", "农", "苟", "迟", "保", "商", "臧", "佘", "卞", "虞", "刁", "冷", "应", "匡", "栗", "仇", "练", "楚", "揭", "师", "官", "佟", "封", "燕", "桑", "巫", "敖", "原", "植", "邝", "仲", "荆", "储", "宗", "楼", "干", "苑", "寇", "盖", "南", "屠", "鞠", "荣", "井", "乐", "银", "奚", "明", "麻", "雍", "花", "闻", "冼", "木", "郜", "廉", "衣", "蔺", "和", "冀", "占", "公", "门", "帅", "利", "满");
                $matches = array();
                $contents = preg_split("(\\r|\\n|\\r\\n)", $this->content);

                if (preg_match("/^[\\n\\t\\r\s　]*([^\\n\\t\\r\s　]+)/", $contents[0], $matches)) {
                    $name = $matches[1];
                    if (in_array(mb_substr($name, 0, 1, $this->charset), $familysurnames) || in_array(mb_substr($name, 0, 2, $this->charset), $familysurnames)) {
                        $spos = mb_stripos($this->content, $name, 0, $this->charset);
                        $poslist[] = array($spos, $spos);
                        $weightlist[] = 3;
                    }
                }

                foreach ($contents as $key => $row) {
                    if (preg_match("/^[\\t\s　]*([\\x{4e00}-\\x{9fa5}}]+)[\\t\s　]*$/u", $row, $matches)) {
                        $name = $matches[1];

                        if (in_array(mb_substr($name, 0, 1, $this->charset), $familysurnames)) {
                            $spos = mb_stripos($this->content, $name, 0, $this->charset);
                            $poslist[] = array($spos, $spos);
                            $weightlist[] = 2;
                        }
                    }
                }

                break;

            case "gender":
                $matches = array();

                if (preg_match_all("/(男|女)/u", $this->content, $matches)) {
                    $sexlist = $matches[0];

                    foreach ($sexlist as $index => $sex) {
                        $spos = mb_strpos($this->content, $sex, 0, $this->charset);
                        $poslist[] = array($spos, $spos);
                        $weightlist[] = 2 - $index;
                    }
                }

                break;

            case "maritalstatus":
                $matches = array();

                if (preg_match_all("/(已婚|未婚)/u", $this->content, $matches)) {
                    $maritalslist = $matches[0];

                    foreach ($maritalslist as $index => $maritals) {
                        $spos = mb_strpos($this->content, $maritals, 0, $this->charset);
                        $poslist[] = array($spos, $spos);
                        $weightlist[] = 2 - $index;
                    }
                }

                break;

            case "email":
                $matches = array();

                if (preg_match_all("/[a-zA-Z0-9_+.-]+\@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,4}/", $this->content, $matches)) {
                    $emaillist = $matches[0];

                    foreach ($emaillist as $index => $email) {
                        $spos = mb_strpos($this->content, $email, 0, $this->charset);
                        $poslist[] = array($spos, $spos);
                        $weightlist[] = 5 - $index;
                    }
                }

                break;

            case "age":
                $matches = array();

                if (preg_match_all("/\d+岁/u", $this->content, $matches)) {
                    $agelist = $matches[0];

                    foreach ($agelist as $index => $age) {
                        $spos = mb_strpos($this->content, $age, 0, $this->charset);
                        $poslist[] = array($spos, $spos);
                        $weightlist[] = 3 - $index;
                    }
                }

                break;

            case "mobile":
                $matches = array();

                if (preg_match_all("/\b\d{11}\b/", $this->content, $matches)) {
                    $emaillist = $matches[0];

                    foreach ($emaillist as $index => $email) {
                        $spos = mb_strpos($this->content, $email, 0, $this->charset);
                        $poslist[] = array($spos, $spos);
                        $weightlist[] = 5 - $index;
                    }
                }

                break;

            case "phone":
                $matches = array();

                if (preg_match_all("/\b\d{7-9}\b/", $this->content, $matches)) {
                    $emaillist = $matches[0];

                    foreach ($emaillist as $index => $email) {
                        $spos = mb_strpos($this->content, $email, 0, $this->charset);
                        $poslist[] = array($spos, $spos);
                        $weightlist[] = 5 - $index;
                    }
                }

                break;

            case "idcard":
                $matches = array();

                if (preg_match_all("/\b(\d{15}|\d{18})\b/", $this->content, $matches)) {
                    $emaillist = $matches[0];

                    foreach ($emaillist as $index => $email) {
                        $spos = mb_strpos($this->content, $email, 0, $this->charset);
                        $poslist[] = array($spos, $spos);
                        $weightlist[] = 5 - $index;
                    }
                }

                break;

            default:
                break;
        }

        return array($poslist, $weightlist);
    }

    public function parse_pos_array($fieldposlist)
    {
        $array = array();
        $index = 0;
        uasort($fieldposlist, "ResumeAnalysis::pos_sort_array");

        foreach ($fieldposlist as $id => $poslist) {
            $max_weight_pos = $poslist[0];

            if (!empty($max_weight_pos)) {
                $array[$index] = $max_weight_pos[1];
                $this->valpos[$index] = $id;
                $index++;
            }
        }

        return $array;
    }

    private static function pos_sort_array($poslist1, $poslist2)
    {
        error_reporting(1);

        if ($poslist1[0][0] == $poslist2[0][0]) {
            return 0;
        }

        return $poslist1[0][0] < $poslist2[0][0] ? -1 : 1;
    }

    private function pos_sort_by_weight($poslist, $weightlist)
    {
        $pos_list_sorted = array();
        arsort($weightlist);

        foreach ($weightlist as $index => $weight) {
            $pos_list_sorted[] = $poslist[$index];
        }

        return $pos_list_sorted;
    }

    private function pos_clear_include($poslist)
    {
        $unique_poslist = $poslist;
        $cover_include = array();
        $poslist1 = $poslist2 = $poslist;

        foreach ($poslist1 as $key1 => $pos1) {
            foreach ($poslist2 as $key2 => $pos2) {
                $isinclude = ($pos1[0] <= $pos2[0]) && ($pos2[1] <= $pos1[1]);
                if ($isinclude && ($key1 != $key2)) {
                    unset($unique_poslist[$key2]);
                }
            }
        }

        sort($unique_poslist);
        return $unique_poslist;
    }

    private function get_pos($field)
    {
        $fieldlist = array();
        $matches = array();
        $exp = "[\\t\s　]*";
        $strexp = str_replace(preg_quote($exp), $exp, implode($exp, preg_split("/(?<!^)(?!$)/u", preg_quote($field))));

        if (0 < preg_match_all("/" . $strexp . "/", $this->content, $matches)) {
            $fieldlist = $matches[0];
        }

        $fieldlist = array_unique($fieldlist);
        $poslist = array();
        $i = 0;

        for ($len = count($fieldlist); $i < $len; $i++) {
            $offset = 0;
            $field = $fieldlist[$i];
            $strlen = mb_strlen($field, $this->charset);
            while (($spos = mb_strpos($this->content, $field, $offset, $this->charset)) || ($spos === 0)) {
                $epos = $spos + $strlen;
                $poslist[] = array($spos, $epos);
                $offset = $epos;
            }
        }

        return $poslist;
    }

    private function assess_weight($poslist)
    {
        $weightlist = array();
        $left_assess_str_list = array(" ", "　", "\t", "\n", "\r");
        $left_assess_weight_list = array(2, 2, 2, 3, 3);
        $right_assess_str_list = array(" ", "　", "\t", ":", "：");
        $right_assess_weight_list = array(2, 2, 2, 5, 5);
        $i = 0;

        for ($len = count($poslist); $i < $len; $i++) {
            $weight = 0;
            $spos = $poslist[$i][0];
            $epos = $poslist[$i][1];
            $leftstr = mb_substr($this->content, $spos - 1, 1, $this->charset);
            $lkey = array_search($leftstr, $left_assess_str_list);
            if ($lkey || ($lkey === 0)) {
                $weight += $left_assess_weight_list[$lkey];
            }

            $rightstr = mb_substr($this->content, $epos, 1, $this->charset);
            $rkey = array_search($rightstr, $right_assess_str_list);
            if ($rkey || ($rkey === 0)) {
                $weight += $right_assess_weight_list[$rkey];
            }

            if ($weight != 0) {
                $weight += $len - $i;
            }

            $weightlist[$i] = $weight;
        }

        return $weightlist;
    }
}
