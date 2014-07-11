<?php

class DashboardUploadController extends DashboardBaseController
{
    const TTF_FONT_PATH = "data/font/";

    public function actionIndex()
    {
        $operation = EnvUtil::getRequest("op");

        switch ($operation) {
            case "thumbpreview":
            case "waterpreview":
                $temp = Ibos::engine()->IO()->file()->getTempPath() . "/watermark_temp.jpg";

                if (LOCAL) {
                    if (is_file($temp)) {
                        @unlink($temp);
                    }
                }

                $quality = EnvUtil::getRequest("quality");
                $source = PATH_ROOT . "/static/image/watermark_preview.jpg";

                if ($operation == "waterpreview") {
                    $trans = EnvUtil::getRequest("trans");
                    $type = EnvUtil::getRequest("type");
                    $val = EnvUtil::getRequest("val");
                    $pos = EnvUtil::getRequest("pos");

                    if ($type == "image") {
                        $sInfo = ImageUtil::getImageInfo($source);
                        $wInfo = ImageUtil::getImageInfo($val);
                        if (($sInfo["width"] < $wInfo["width"]) || ($sInfo["height"] < $wInfo["height"])) {
                            Ibos::import("ext.ThinkImage.ThinkImage", true);
                            $imgObj = new ThinkImage(THINKIMAGE_GD);
                            $imgObj->open($val)->thumb(260, 77, 1)->save($val);
                        }

                        ImageUtil::water($source, $val, $temp, $pos, $trans, $quality);
                    } else {
                        $hexColor = EnvUtil::getRequest("textcolor");
                        $size = EnvUtil::getRequest("size");
                        $fontPath = EnvUtil::getRequest("fontpath");
                        $rgb = ConvertUtil::hexColorToRGB($hexColor);
                        ImageUtil::waterMarkString($val, $size, $source, $temp, $pos, $quality, $rgb, self::TTF_FONT_PATH . $fontPath);
                    }

                    $image = $temp;
                }

                if (!LOCAL) {
                    if (Ibos::engine()->IO()->file()->createFile($temp, file_get_contents($image))) {
                        $image = FileUtil::fileName($temp);
                    }
                }

                $data = array("image" => $image, "sourceSize" => ConvertUtil::sizeCount(FileUtil::fileSize($source)), "thumbSize" => ConvertUtil::sizeCount(FileUtil::fileSize($image)), "ratio" => sprintf("%2.1f", (FileUtil::fileSize($image) / FileUtil::fileSize($source)) * 100) . "%");
                $this->render("imagePreview", $data);
                exit();
                break;

            case "upload":
                return $this->imgUpload("watermark", true);
                break;
        }

        $formSubmit = EnvUtil::submitCheck("uploadSubmit");
        $uploadKeys = "attachdir,attachurl,thumbquality,attachsize,filetype";
        $waterMarkkeys = "watermarkminwidth,watermarkminheight,watermarktype,watermarkposition,watermarktrans,watermarkquality,watermarkimg,watermarkstatus,watermarktext,watermarkfontpath";

        if ($formSubmit) {
            $keys = $uploadKeys . "," . $waterMarkkeys;
            $keyField = explode(",", $keys);

            foreach ($_POST as $key => $value) {
                if (in_array($key, $keyField)) {
                    Setting::model()->updateSettingValueByKey($key, $value);
                } elseif ($key == "watermarkstatus") {
                    Setting::model()->updateSettingValueByKey("watermarkstatus", 0);
                }
            }

            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $upload = Setting::model()->fetchSettingValueByKeys($uploadKeys);
            $waterMark = Setting::model()->fetchSettingValueByKeys($waterMarkkeys);
            $fontPath = DashboardUtil::getFontPathlist(self::TTF_FONT_PATH);
            $data = array("upload" => $upload, "waterMark" => $waterMark, "fontPath" => $fontPath);
            $this->render("index", $data);
        }
    }
}
