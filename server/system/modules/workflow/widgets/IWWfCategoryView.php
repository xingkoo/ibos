<?php

class IWWfCategoryView extends IWWfListContent
{
    /**
     * 视图变量
     * @var array 
     */
    private $_var = array();

    public function run()
    {
        $var["lang"] = Ibos::getLangSources();
        $var["type"] = $this->getType();
        $var["sort"] = $this->getSort();
        $var["op"] = $this->getOp();
        $this->render("wfwidget.categoryview", array_merge($this->_var, $var, $this->handleList($this->getRunProcess())));
    }

    protected function getOffset()
    {
        return -1;
    }

    protected function getLimit()
    {
        return -1;
    }

    private function handleList($runProcess)
    {
        $category = $flows = $list = array();

        foreach ($runProcess as $run) {
            $flowId = $run["flowid"];

            if (isset($flows[$flowId])) {
                $flow = $flows[$flowId];
            } else {
                $flows[$flowId] = $flow = FlowType::model()->fetchByPk($flowId);
            }

            $catId = $flow["catid"];

            if (!isset($list[$catId])) {
                $category[$catId] = array();
                $cat = FlowCategory::model()->fetchByPk($catId);

                if ($cat) {
                    $category[$catId] = $cat;
                }
            }

            if (isset($list[$catId][$flowId])) {
                if ($this->type == "todo") {
                    if ($run["flag"] == FlowConst::PRCS_UN_RECEIVE) {
                        $list[$catId][$flowId]["unreceive"]++;
                    }
                }

                $list[$catId][$flowId]["count"]++;
            } else {
                if ($this->type == "todo") {
                    if ($run["flag"] == FlowConst::PRCS_UN_RECEIVE) {
                        $flow["unreceive"] = 1;
                    } else {
                        $flow["unreceive"] = 0;
                    }
                }

                $flow["count"] = 1;
                $list[$catId][$flowId] = $flow;
            }
        }

        ksort($list, SORT_NUMERIC);
        return array("catSort" => $category, "list" => $list);
    }
}
