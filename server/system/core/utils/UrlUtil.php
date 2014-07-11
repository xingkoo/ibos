<?php

class UrlUtil
{
    public static function getUrl($url)
    {
        if ((count(explode("/", $url)) == 3) && !preg_match("/^http/iUs", $url)) {
            $url = Ibos::app()->urlManager->createUrl($url);
        } else {
            $urlInfo = parse_url($url);
            $url = (isset($urlInfo["scheme"]) ? $url : "http://" . $url);
        }

        return $url;
    }
}
