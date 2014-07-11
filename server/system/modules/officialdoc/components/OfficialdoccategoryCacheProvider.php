<?php

class OfficialdocCategoryCacheProvider extends CBehavior
{
    public function attach($owner)
    {
        $owner->attachEventHandler("onUpdateCache", array($this, "handleOfficialdocCategory"));
    }

    public function handleOfficialdocCategory($event)
    {
        $categorys = array();
        Yii::import("application.modules.officialdoc.model.OfficialdocCategory");
        $records = OfficialdocCategory::model()->findAll(array("order" => "sort ASC"));

        if (!empty($records)) {
            foreach ($records as $record) {
                $cat = $record->attributes;
                $categorys[$cat["catid"]] = $cat;
            }
        }

        Syscache::model()->modify("officialdoccategory", $categorys);
    }
}
