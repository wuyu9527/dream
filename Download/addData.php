<?php
    /**
     * Created by PhpStorm.
     * User: Android
     * Date: 2016/5/6
     * Time: 11:21
     */
    error_reporting(E_ALL ^ E_DEPRECATED);
    require(dirname(__FILE__) . '/../Base/baseDAO.php');
    require(dirname(__FILE__) . '/../Base/config.php');
    set_time_limit(0); //不限时 24 * 60 * 60
    class runtime {

        var $StartTime = 0;
        var $StopTime = 0;

        function get_microtime() {

            list($usec, $sec) = explode(' ', microtime());

            return ((float)$usec + (float)$sec);
        }

        function start() {

            $this->StartTime = $this->get_microtime();
        }

        function stop() {

            $this->StopTime = $this->get_microtime();
        }

        function spent() {

            return round(($this->StopTime - $this->StartTime) * 1000, 1);
        }
    }

    $runtime = new runtime;
    $runtime->start();

    $destination_folder = '../Download/ALL/'; // 下载的文件保存目录。必须以斜杠结尾
    if (!is_dir($destination_folder)){
        mkdir($destination_folder, 0777);
    } //判断目录是否存在
         //若无则创建，并给与777权限 windows忽略
    //$url=$_GET['url'];
    $url = $_POST['url'];
    //$url="http://table.finance.yahoo.com/table.csv?s=600000.ss";
    $headers = get_headers($url, 1); //得到文件大小
    if ((!array_key_exists("Content-Length", $headers))) {
        $filesize = 0;
    }
    $newfname = $destination_folder . basename($url);
    $filenamemy = explode("?", $newfname);
    /** 取得名字，列：$name[1] = 600000.ss */
    $name = explode("=", $filenamemy[1]);
    /** 取得代号，列：$nameForData[0]=600000 */
    $nameForData=explode(".",$name[1]);
    $name1 = $name[1] . ".csv";
    $file = fopen($url, "rb");
    if ($file) {
        $newf = fopen($destination_folder . $name1, "wb");
        if ($newf) while (!feof($file)) {
            fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
        }
    }
    if ($file) {
        fclose($file);
    }
    if ($newf) {
        fclose($newf);
    }
    $runtime->stop();
    $fCont = file_get_contents($url);
    $size = strlen($fCont) / 1024;



    $creatData = "CREATE TABLE `$nameForData[0]` (`Date` varchar(255) NOT NULL,`Open` double NOT NULL,`High` double NOT NULL,`Low` double NOT NULL,`Close` double NOT NULL,`Volume` varchar(255) NOT NULL,`AdjClose` double NOT NULL,PRIMARY KEY (`Date`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $dao = new dao(0);
    $my = $dao->mydao(0);
    $my->query($creatData);
    $row = 1;
    $a = 0;
    $sql = "insert into `$nameForData[0]` VALUES ";
    if (($handle = fopen("F:/www/dream/Download/ALL/".$name1, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($row != 1) {
                $sql .= "('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]'),";
            }
            $row++;
        }
        $a = $dao->doQuery(substr($sql, 0, strlen($sql) - 1));
        if ($a == 0) {
            echo '{"msg":"添加失败","num":"0"}';
        } else {
            echo '{"msg":"添加行数","num":"'.$row.'"}';
        }
        fclose($handle);
    }

