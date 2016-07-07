<?php

    /**
     * Created by PhpStorm.
     * User: Android
     * Date: 2016/4/20
     * Time: 10:44
     * host username password dbname port socket
     */
    class dao {
        private $mysqli;
        const lost = 0;
        const success = 1;
        public $all_config;
        private $num;//哪个用户


        public function __construct($num) {
            $this->all_config = require("config.php");
            $this->num=$num;
        }

        public function getMysqli() {
            return $this->mysqli;
        }

        /***
        *换用户
         */
        public function db($num) {
            if ($this->mysqli != null&&$num!=$this->num) {
                $this->num=$num;
                $this->close();
                $this->mysqli=null;
            }
            return $this->mydao($num);
        }

        public function mydao($num) {
            if ($this->mysqli == null) {
                $this->mysqli = new mysqli($this->all_config[$num]['host'],$this->all_config[$num]['username'],$this->all_config[$num]['password'],$this->all_config[$num]['dbname']) or die("数据库连接失败");
                if ($this->mysqli->connect_errno) {
                    printf("链接失败: %s\n", $this->mysqli->connect_error);
                    exit();
                }
            }
            return $this->mysqli;
        }

        public function close() {
            if ($this->mysqli == null) {
                return self::lost;
            } else {
                $this->mysqli->close();
                return self::success;
            }
        }
        public function doQuery($sql){
            if ($result=$this->mydao($this->num)->query($sql)){
                return $result;
            }else{
                return self::lost;
            }
        }
        public function doUpdata($sql){
            if($result=$this->mydao($this->num)->query($sql)){
                return $result;
            }else{
                return self::lost;
            }
        }
    }