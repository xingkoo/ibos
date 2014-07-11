<?php

class DashboardStatusController extends DashboardBaseController
{
    public function actionIndex()
    {
        exit(Ibos::app()->performance->endClockAndGet());
    }
}
