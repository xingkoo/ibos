<?php

class DashboardDatabaseController extends DashboardBaseController
{
    public function init()
    {
        parent::init();

        if (!LOCAL) {
            exit(Ibos::lang("Not compatible service", "message"));
        }
    }

    public function actionBackup()
    {
        $formSubmit = EnvUtil::submitCheck("dbSubmit");
        $type = $msg = $url = "";
        $param = array();

        if ($formSubmit) {
            $status = DatabaseUtil::databaseBackup();
            extract($status);
            $this->{$type}($msg, $url, $param);
        } else {
            $data = array();
            $tablePrefix = Ibos::app()->setting->get("config/db/tableprefix");

            if (EnvUtil::getRequest("setup") == "1") {
                $status = DatabaseUtil::databaseBackup();
                extract($status);
                $this->{$type}($msg, $url, $param);
            }

            $data["defaultFileName"] = date("Y-m-d") . "_" . StringUtil::random(8);
            $data["tables"] = DatabaseUtil::getTablelist($tablePrefix);
            $this->render("backup", $data);
        }
    }

    public function actionRestore()
    {
        $formSubmit = EnvUtil::submitCheck("dbSubmit");

        if ($formSubmit) {
            $backupDir = DatabaseUtil::getBackupDir();

            if (is_array($_POST["key"])) {
                foreach ($_POST["key"] as $fileName) {
                    $filePath = $backupDir . "/" . str_replace(array("/", "\\"), "", $fileName);

                    if (is_file($filePath)) {
                        @unlink($filePath);
                    } else {
                        $i = 1;

                        while (1) {
                            $filePath = $backupDir . "/" . str_replace(array("/", "\\"), "", $fileName . "-" . $i . ".sql");

                            if (is_file($filePath)) {
                                @unlink($filePath);
                                $i++;
                            } else {
                                break;
                            }
                        }
                    }
                }

                $this->success(Ibos::lang("Database file delete succeed"));
            }
        } else {
            $this->render("restore", array("list" => DatabaseUtil::getBackupList()));
        }
    }

    public function actionOptimize()
    {
        $formSubmit = EnvUtil::submitCheck("dbSubmit");

        if ($formSubmit) {
            $tables = $_POST["optimizeTables"];

            if (!empty($tables)) {
                DatabaseUtil::optimize($tables);
            }

            $this->success(Ibos::lang("Operation succeed", "message"));
        } else {
            $list = DatabaseUtil::getOptimizeTable();
            $totalSize = 0;

            foreach ($list as $table) {
                $totalSize += $table["Data_length"] + $table["Index_length"];
            }

            $data["list"] = $list;
            $data["totalSize"] = $totalSize;
            $this->render("optimize", $data);
        }
    }
}
