<?php

class MainPageController extends ICController
{
    private $_tplPath = "data/page/";
    private $_suffix = ".php";

    public function actionIndex()
    {
        $pageid = intval(EnvUtil::getRequest("pageid"));

        if (empty($pageid)) {
            $this->error(Ibos::lang("Parameters error"));
        }

        $name = EnvUtil::getRequest("name");
        $pageTitle = (empty($name) ? Ibos::lang("Single page") : $name);
        $page = Page::model()->fetchByPk($pageid);

        if (empty($page)) {
            $this->error(Ibos::lang("Page not fount"));
        }

        $this->checkTplIsExist($page["template"]);
        $params = array("page" => $page, "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("main"), "pageTitle" => $pageTitle, "breadCrumbs" => $this->getBreadCrumbs());
        $view = "data.page." . $page["template"];
        $html = $this->renderPartial($view, $params, true);
        $jsLoaded = $this->getLoadedHtlm();
        $replace = $jsLoaded . $page["content"];
        $ret = SinglePageUtil::parse($html, $replace);

        if (!$ret) {
            $this->error(Ibos::lang("Template illegal"));
        }

        echo $ret;
    }

    public function actionEdit()
    {
        $pageid = intval(EnvUtil::getRequest("pageid"));

        if (empty($pageid)) {
            $this->error(Ibos::lang("Parameters error"));
        }

        if (EnvUtil::getRequest("op") == "switchTpl") {
            $tpl = EnvUtil::getRequest("tpl");
            $file = FileUtil::fileName($this->_tplPath . $tpl . $this->_suffix);
            $content = SinglePageUtil::getTplEditorContent($file);
            $page = array("id" => $pageid, "template" => $tpl, "content" => $content);
        } else {
            $page = Page::model()->fetchByPk($pageid);
        }

        if (empty($page)) {
            $this->error(Ibos::lang("Page not fount"));
        }

        $this->checkTplIsExist($page["template"]);
        $name = EnvUtil::getRequest("name");
        $pageTitle = (empty($name) ? Ibos::lang("Single page") : $name);
        $params = array("page" => $page, "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("main"), "pageTitle" => $pageTitle, "breadCrumbs" => $this->getBreadCrumbs());
        $view = "data.page." . $page["template"];
        $html = $this->renderPartial($view, $params, true);
        $jsLoaded = $this->getLoadedHtlm();
        $editor = $this->getEditorHtml($page);
        $replace = $jsLoaded . $editor;
        $ret = SinglePageUtil::parse($html, $replace);

        if (!$ret) {
            $this->error(Ibos::lang("Template illegal"));
        }

        echo $ret;
    }

    public function actionSave()
    {
        if (EnvUtil::submitCheck("saveSubmit")) {
            $pageid = intval(EnvUtil::getRequest("pageid"));
            $attributes = array("template" => EnvUtil::getRequest("tpl"), "content" => EnvUtil::getRequest("content"));

            if (!empty($pageid)) {
                Page::model()->modify($pageid, $attributes);
            }

            $this->ajaxReturn(array("isSuccess" => true, "pageid" => $pageid));
        }
    }

    public function actionPreview()
    {
        $name = EnvUtil::getRequest("name");
        $tpl = EnvUtil::getRequest("tpl");
        $content = EnvUtil::getRequest("content");
        $pageTitle = (empty($name) ? Ibos::lang("Single page") : $name);
        $params = array("content" => $content, "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("main"), "pageTitle" => $pageTitle, "breadCrumbs" => $this->getBreadCrumbs());
        $this->checkTplIsExist($tpl);
        $view = "data.page." . $tpl;
        $html = $this->renderPartial($view, $params, true);
        $jsLoaded = $this->getLoadedHtlm();
        $replace = $jsLoaded . $content;
        $ret = SinglePageUtil::parse($html, $replace);

        if (!$ret) {
            $this->error(Ibos::lang("Template illegal"));
        }

        echo $ret;
    }

    private function getEditorHtml($page)
    {
        $tpls = SinglePageUtil::getAllTpls();
        $params = array("page" => $page, "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("main"), "tpls" => $tpls);
        $alias = "application.modules.main.views.page.editor";
        $viewHtml = $this->renderPartial($alias, $params, true);
        return $viewHtml;
    }

    private function getLoadedHtlm()
    {
        $alias = "application.modules.main.views.page.loaded";
        $params = array("assetUrl" => Ibos::app()->assetManager->getAssetsUrl("main"));
        $viewHtml = $this->renderPartial($alias, $params, true);
        return $viewHtml;
    }

    private function getBreadCrumbs()
    {
        $breadCrumbs = array(
            array("name" => Ibos::lang("Single page"))
        );
        return $breadCrumbs;
    }

    protected function checkTplIsExist($tpl)
    {
        $file = FileUtil::fileName($this->_tplPath . $tpl . $this->_suffix);
        $ret = FileUtil::fileExists($file);

        if (!$ret) {
            $this->error(Ibos::lang("Template not fount", "", array("{file}" => $tpl . $this->_suffix)));
        }
    }
}
