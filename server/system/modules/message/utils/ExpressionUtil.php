<?php

class ExpressionUtil
{
    public static function getAllExpression($flush = false)
    {
        $cacheId = "expression";
        if ((($res = CacheUtil::get($cacheId)) === false) || ($flush === true)) {
            $filepath = "static/image/expression/";
            $expression = new Dir($filepath);
            $expression_pkg = $expression->toArray();
            $res = array();
            $typeMap = array("df" => "默认", "bm" => "暴漫");

            foreach ($expression_pkg as $index => $value) {
                list($file) = explode(".", $value["filename"]);
                list($type) = explode("_", $file);
                $temp["value"] = $file;
                $temp["phrase"] = "[" . $file . "]";
                $temp["icon"] = $value["filename"];
                $temp["type"] = $type;
                $temp["category"] = $typeMap[$type];
                $res[$temp["phrase"]] = $temp;
            }

            CacheUtil::set($cacheId, $res);
        }

        return $res;
    }

    public static function parse($data)
    {
        $data = preg_replace("/img{data=([^}]*)}/", "<img src='\$1'  data='\$1' >", $data);
        return $data;
    }
}