<?php

class MessageBaseController extends ICController
{
    public function getSidebar($data = array())
    {
        $data["unreadMap"] = $this->getUnreadCount();
        $sidebarAlias = "application.modules.message.views.sidebar";
        $sidebarView = $this->renderPartial($sidebarAlias, $data, true);
        return $sidebarView;
    }

    private function getUnreadCount()
    {
        $unreadCount = UserData::model()->getUnreadCount(Ibos::app()->user->uid);
        $sidebarUnreadMap["mention"] = $unreadCount["unread_atme"];
        $sidebarUnreadMap["comment"] = $unreadCount["unread_comment"];
        $sidebarUnreadMap["notify"] = $unreadCount["unread_notify"];
        $sidebarUnreadMap["pm"] = $unreadCount["unread_message"];
        return $sidebarUnreadMap;
    }
}
