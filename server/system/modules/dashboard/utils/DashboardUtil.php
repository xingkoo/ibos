<?php

class DashboardUtil
{
    public static function getFontPathlist($path)
    {
        $fonts = (array) glob($path . "*.ttf");
        $fontList = array();

        foreach ($fonts as $font) {
            if (is_file($font) && is_readable($font)) {
                $fontList[] = basename($font);
            }
        }

        return $fontList;
    }

    public static function moveTempFile($file, $path)
    {
        if (FileUtil::fileExists($file)) {
            $copySucceed = FileUtil::copyToDir($file, $path);

            if (!$copySucceed) {
                throw new EnvException(Ibos::lang("Move file failed", "error", array("file" => $file, "path" => $path)));
            }

            return basename($file);
        }
    }

    public static function arrayFlipKeys($arr)
    {
        $arr2 = array();
        $arrKeys = @array_keys($arr);
        $first = @each(array_slice($arr, 0, 1))[1];

        if ($first) {
            foreach ($first as $k => $v) {
                foreach ($arrKeys as $key) {
                    $arr2[$k][$key] = $arr[$key][$k];
                }
            }
        }

        return $arr2;
    }

    public static function checkFormulaSyntax($formula, $operators, $tokens)
    {
        $var = implode("|", $tokens);
        $operator = implode("", $operators);
        $operator = str_replace(array("+", "-", "*", "/", "(", ")", "'"), array("\+", "\-", "\*", "\/", "\(", "\)", "\'"), $operator);

        if (!empty($formula)) {
            if (!preg_match("/^([{$operator}\.\d\(\)]|(($var)([$operator\(\)]|$)+))+$/", $formula) || !is_null(@eval (preg_replace("/($var)/", "$\1", $formula) . ";"))) {
                return false;
            }
        }

        return true;
    }

    public static function checkFormulaCredits($formula)
    {
        return self::checkFormulaSyntax($formula, array("+", "-", "*", "/", " "), array("extcredits[1-5]"));
    }
}
