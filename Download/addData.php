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
    $dao = new dao(0);
    $stick=new addStick();
    // $stick_num=$_POST['num'];
    $stick_num = empty($_POST['num'])?null:$_POST['num'];
    $stick_name= empty($_POST['name'])?null:$_POST['name'];

    if(empty($_POST['name'])){
        $name=$stick->getStickName($stick_num);
        if($name=="0"||$name=="1"){
            return;
        }else{
            $stick_name=$name;
        }
    }





    $sqlnum = "select whx_stick.stick_num from whx_stick where stick_num = $stick_num";
    $b = $dao->doQuery($sqlnum);
    if ($b->num_rows == 0) {
        $stick->addStickData($stick_num,$stick_name);
    } else {
        $sqlnum1="select * from `$stick_num` order by Date desc LIMIT 0,35";
        $c = $dao->doQuery($sqlnum1);
        $all=array();
        while($row=$c->fetch_assoc()){
            $all[]=$row;
        }
        echo '{"arr":'.json_encode($all).'}';
    };

    class addStick {





        public function addStickData($stick_num,$stick_name) {
            $dao = new dao(0);
            set_time_limit(0); //不限时 24 * 60 * 60
            $runtime = new runtime;
            $runtime->start();
            $destination_folder = '../Download/ALL/'; // 下载的文件保存目录。必须以斜杠结尾
            if (!is_dir($destination_folder)) {
                mkdir($destination_folder, 0777);
            } //判断目录是否存在
            //若无则创建，并给与777权限 windows忽略
            //$url=$_GET['url'];
            //$url = $_POST['url'];
            //$stick_num = $_GET['num'];

            if(preg_match("/^[6]/A",$stick_num)>0){
                $url="http://table.finance.yahoo.com/table.csv?s=$stick_num.ss";
            }else{
                $url="http://table.finance.yahoo.com/table.csv?s=$stick_num.sz";
            }
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
            $nameForData = explode(".", $name[1]);
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

            $dao->doQuery($creatData);

            $row = 1;
            $a = 0;
            $sql = "insert into `$nameForData[0]` VALUES ";
            if (($handle = fopen("F:/www/dream/Download/ALL/" . $name1, "r")) !== false) {
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
                    echo '{"msg":"添加行数","num":"' . $row . '"}';
                }

                fclose($handle);
            }
            $stickNameSql="insert into whx_stick(stick_name,stick_num) VALUES ('$stick_name','$stick_num') ";
            $dao->doQuery($stickNameSql);
        }

        public function getStickName($stick_num){

            //配置您申请的appkey
            $appkey = "9123f07be3da07bdf7b6e126f33ed608";

            if(preg_match("/^[6]/A",$stick_num)>0){
                $stick_num1="sh".$stick_num;
            }else{
                $stick_num1="sz".$stick_num;
            }
            header('Content-type:text/html;charset=utf-8');

            //************1.沪深股市************
            $url = "http://web.juhe.cn:8080/finance/stock/hs";
            $params = array(
                "gid" => $stick_num1,//股票编号，上海股市以sh开头，深圳股市以sz开头如：sh601009
                "key" => $appkey,//APP Key
            );
            $paramstring = http_build_query($params);
            $content = $this->juhecurl($url, $paramstring);
            $result = json_decode($content, true);
            if ($result) {
                if ($result['error_code'] == '0') {
                    return $result['result'][0]['dapandata']['name'];
                } else {
                    return "0";
                }
            } else {
                return "1";
            }
            //**************************************************
        }
        /**
         * 请求接口返回内容
         *
         * @param  string $url    [请求的URL地址]
         * @param  string $params [请求的参数]
         * @param  int    $ipost  [是否采用POST形式]
         *
         * @return  string
         */
        public function juhecurl($url, $params = false, $ispost = 0) {

            $httpInfo = array();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if ($ispost) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_URL, $url);
            } else {
                if ($params) {
                    curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
                } else {
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
            }
            $response = curl_exec($ch);
            if ($response === false) {
                //echo "cURL Error: " . curl_error($ch);
                return false;
            }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
            curl_close($ch);

            return $response;
        }


    }

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
