<?php

class OrganizationBaseController extends ICController
{
    const NO_PERMISSION = 0;
    const ONLY_SELF = 1;
    const CONTAIN_SUB = 2;
    const SELF_BRANCH = 4;
    const All_PERMISSION = 8;

    public function getSidebar($data = array())
    {
        $sidebarAlias = "application.modules.organization.views.sidebar";
        $sidebarView = $this->renderPartial($sidebarAlias, $data, true);
        return $sidebarView;
    }
}
