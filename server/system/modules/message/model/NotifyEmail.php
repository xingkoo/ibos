<?php

class NotifyEmail extends ICModel
{
    public static function model($className = "NotifyEmail")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{notify_email}}";
    }

    public function sendEmail($data, $hasContent = false)
    {
        if (empty($data["email"])) {
            return false;
        }

        $s["uid"] = intval($data["uid"]);
        $s["node"] = StringUtil::filterCleanHtml($data["node"]);
        $s["email"] = StringUtil::filterCleanHtml($data["email"]);
        $s["module"] = StringUtil::filterCleanHtml($data["module"]);
        $s["issend"] = $s["sendtime"] = 0;
        $s["title"] = StringUtil::filterCleanHtml($data["title"]);
        $baseUrl = Ibos::app()->setting->get("siteurl");
        $fullName = Ibos::app()->setting->get("setting/unit/fullname");
        $user = User::model()->fetchByUid($s["uid"]);
        $named = $user["realname"] . ($user["gender"] == 1 ? " 先生" : " 女士");
        $body = html_entity_decode($data["body"]);

        if ($hasContent) {
            //$bodystr = "        <tr>\r\n\t\t\t<td colspan=\"2\">\r\n\t\t\t\t<div style=\"width:493px; padding:25px; margin:0 auto; background:#FFF; border:1px solid #ededed\">\r\n\t\t\t\t\t$body\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>   ";
            $bodystr = '
        <tr>
            <td colspan="2">
                <div style="width:493px; padding:25px; margin:0 auto; background:#FFF; border:1px solid #ededed">
                    $body
                </div>
            </td>
        </tr>   ';
        } else {
            $bodystr = "";
        }

        $s["body"] = "<!DOCTYPE HTML>\r\n<html lang=\"en-US\">\r\n<head>\r\n\t<meta charset=\"UTF-8\">\r\n\t<title>邮件提醒</title>\r\n</head>\r\n<body>\r\n\t<style type=\"text/css\">\r\n\t\ta{ text-decoration:none; }\r\n\t\ta:hover{ text-decoration:underline; }\r\n\t</style>\r\n\t<table style=\"width:598px; border:1px solid #e8e8e8;  background:#fcfcfc; margin:0 auto;\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\r\n\t\t<tr>\r\n\t\t\t<!-- 公司名称 -->\r\n\t\t\t<td style=\"width:425px; height:49px; line-height:49px; overflow:hidden; background:#1180c6; font-size:18px; font-weight:bold; color:#FFF; font-family:'Microsoft YaHei';\">&#12288;$fullName</td>\r\n\t\t\t<td style=\"width:173px; height:49px; line-height:49px; overflow:hidden; background:#1180c6; font-size:12px; color:#FFF\">IBOS云服务中心·邮件提醒</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td colspan=\"2\" style=\"width:598px; height:30px; overflow:hidden;\">&nbsp;</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<!-- 收件人姓名 -->\r\n\t\t\t<td colspan=\"2\" style=\"width:548px; height:40px; line-height:40px; overflow:hidden;font-size:16px; font-family:'\005b8b\004f53';\"><div style=\"width:543px; margin:0 auto; font-size:16px;\">HELLO！$named:</div></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td colspan=\"2\" style=\"width:598px; height:80px; overflow:hidden; \">\r\n\t\t\t\t<div style=\"width:543px; margin:0 auto;\">\r\n\t\t\t\t\t<!-- 通知标题 -->\r\n\t\t\t\t\t<p align=\"center\" style=\"width:493px; margin:0 auto; font-size:14px; line-height:20px; font-family:'\005b8b\004f53';color:#50545f;\">{$s["title"]}</p>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n        $bodystr\r\n\t\t<tr>\r\n\t\t\t<td colspan=\"2\" style=\"width:598px; height:50px; overflow:hidden;\">&nbsp;</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td colspan=\"2\" style=\"width:598px; height:40px; overflow:hidden;\">\r\n\t\t\t\t<!-- 登录按钮 -->\r\n\t\t\t\t<div style=\"width:380px; height:40px; line-height:40px; background:#1180c6; margin:0 auto; color:#fff; text-align:center\">\r\n                    <a href=\"$baseUrl{$data["url"]}\" target=\"_blank\" style=\" color:#fff;font-size:16px;\">现在就登录 IBOS协同办公平台，处理相关事宜！</a>\r\n                </div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td colspan=\"2\" style=\"width:598px; height:40px; overflow:hidden;\">&nbsp;</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<!-- 提示 -->\r\n\t\t\t<td colspan=\"2\" align=\"center\" style=\"width:598px; height:80px; overflow:hidden; font-size:12px;\">\r\n                <span style=\"color:#1180c6\">■&nbsp;</span>您可以在<span style=\"color:#1180c6\">&#12288;\r\n                    <a style=\"color:#1180c6;\" href=\"$baseUrl?r=user/home/index\">个人中心</a>&#12288;->&#12288;\r\n                    <a style=\"color:#1180c6;\" href=\"$baseUrl?r=user/home/personal\">个人资料</a>&#12288;->&#12288;\r\n                    <a style=\"color:#1180c6;\" href=\"$baseUrl?r=user/home/personal&op=remind\">提醒设置</a>&#12288;\r\n                 </span>中管理来自IBOS协同办公平台的邮件提醒\r\n            </td>\r\n\t\t</tr>\r\n\t</table>\r\n\t<table style=\"width:600px; margin:0 auto;\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\r\n\t\t<tr>\r\n\t\t\t<td style=\"width:600px; height:30px; font-size:12px; font-family:'\005b8b\004f53';color:#50545f;\">\r\n                <div style=\"line-height:30px; padding-top:5px;\">2014 ©  IBOS协同办公平台</div>\r\n            </td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<!-- 其他链接 -->\r\n\t\t\t<td style=\"width:600px; height:30px; font-size:12px; font-family:'\005b8b\004f53';color:#50545f; line-height:30px;\">\r\n                <a href=\"http://www.ibos.com.cn\" style=\"color:#50545f;\" target=\"_blank\">开发者平台</a>&#12288;/&#12288;\r\n                <a href=\"http://bbs.ibos.com.cn\" style=\"color:#50545f;\" target=\"_blank\">问答社区</a>&#12288;/&#12288;\r\n                <a href=\"http://www.ibos.com.cn/wiki\" style=\"color:#50545f;\" target=\"_blank\">文档中心</a>&#12288;&#12288;客户支持: 400-838-1185&#12288;&#12288;&#12288;support@ibos.com.cn\r\n            </td>\r\n\t\t</tr>\r\n\t</table>\r\n</body>\r\n</html>";
        $s["ctime"] = time();
        if (CloudApi::getInstance()->isOpen() && CloudApi::getInstance()->exists("mail_send")) {
            MailUtil::sendCloudMail($s["email"], $s["title"], $s["body"]);
        } else {
            MailUtil::sendMail($s["email"], $s["title"], $s["body"]);
        }

        return $this->add($s, true);
    }
}
