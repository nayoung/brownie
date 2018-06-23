<?php
if (!defined("MYSQLI_OBJECT"))
    define("MYSQLI_OBJECT", "MYSQLI_OBJECT");

require_once("setting.php");

Class DbCon {
    private static $instances = array();
    private $db_section;
    private $db_info = NULL;

    private $db_conn;
    private $db_slave = NULL;
    private $db_slave_used = false;

    private $is_slave = false;

    private $lastQueryTime = 0;

    // 생성자
    private function __construct($db_info, $section, $slaveLoad, $errorDie)
    {
        $this->lastQueryTime = time();
        $this->db_section = $section;
        $this->init($db_info, $section, $slaveLoad, $errorDie);
    }

    // 파괴자
    function __destruct()
    {
        if ($this->db_slave != null) {
            $this->db_slave->__destruct();
        }
        @$this->db_conn->close();
        if (isset(self::$instances[$this->db_section])) {
            unset(self::$instances[$this->db_section]);
        }
    }

    public static function &getInstance($db_info, $section = DB_SECTION, $slaveLoad = true, $errorDie = true)
    {
        if (!isset(self::$instances[$section])) {
            self::$instances[$section] = new self($db_info, $section, $slaveLoad, $errorDie);
        }
        return self::$instances[$section];
    }

    function init($db_info, $section, $slaveLoad, $errorDie)
    {
        if (!isset($GLOBALS['class_settings']))
            $settings = $GLOBALS['class_settings'] = @Setting::getInstance($db_info);
        else
            $settings = $GLOBALS['class_settings'];

        if ($settings != null) {
            if (!is_array($settings->{$section})) {
                $err = "$section 섹션이 올바르지 않습니다.";
                echo $err;
                exit();
            }
            $this->db_info = $settings->{$section};
            if (!isset($this->db_info['port'])) {
                $this->db_info['port'] = "3306";
            }
            if (isset($this->db_info["is_slave"]) && $this->db_info["is_slave"] == "true") {
                $this->setSlave(true);
            } else {
                $this->setSlave(false);
            }

            $err_msg = NULL;
            if (!$this->getConnected(true)) {
                $this->db_conn = new mysqli($this->db_info['host'], $this->db_info['userid'], $this->db_info['password'], $this->db_info['db'], $this->db_info['port']);

                $this->db_conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);

                $this->db_conn->query("set names utf8");
            }


            if ($this->db_conn->connect_error || !$this->getConnected()) {
                if (empty($err_msg)) {
                    $err_msg = $this->db_conn->error;
                }
                $detect_char = array();
                $detect_char[] = "EUC-KR";
                $detect_char[] = "ASCII";
                $detect_char[] = "UTF-8";
                $detect_encoding = mb_detect_encoding($err_msg, $detect_char);
                if ($detect_encoding != "UTF-8") {
                    $err_msg = iconv($detect_encoding, "UTF-8", $err_msg);
                }

                if ($errorDie) {
                    die("{$section} DB 연결에 문제가 있습니다.\n" . $err_msg);
                }

                $this->db_conn = null;
                return;
            }

            if (isset($this->db_info['initQuery']) && $this->db_info['initQuery'] != "") {
                @$this->db_conn->query($this->db_info['initQuery']);
            }

            $slave_must_key = array(
                'slave',
                'slavePrefix'
            );
            $slave_myst_key_check = count(array_intersect(array_keys($this->db_info), $slave_must_key)) == count($slave_must_key);

            if ($slaveLoad == true && $slave_myst_key_check && $this->db_info['slave'] == "true" && strlen($this->db_info['slavePrefix']) > 0) {
                $this->db_slave = null;
                $key = $this->getSlaveInfo($this->db_info['slavePrefix']);

                if ($key != null) {
                    $db_slave = self::getInstance($db_info, $key, false, false);
                    if ($db_slave != null && $db_slave->getConnected(true)) {
                        $this->db_slave_used = true;
                        $this->db_slave = $db_slave;
                    }
                }
            }

            $this->lastQueryTime = time();
        } else {
            $err = "DB 파일이 존재하지 않습니다.";
            echo $err;
            exit();
        }
    }

    function changeDB($db_name, $errorDie = false)
    {
        if ($db_name === null || $db_name == "") {
            die();
        }

        $this->db_info['db'] = $db_name;

        @$this->db_conn->select_db($this->db_info['db']);

        if ($this->db_conn->errno != 0 && $errorDie) {
            die("{$this->db_section} DB changeDB 문제가 있습니다.\n" . $this->db_conn->error);
        }

        if ($this->db_slave_used == true) {
            $this->db_slave->changeDB($db_name);
        }
    }

    function getConnected($ping_check = false)
    {
        return ($this->db_conn !== null && is_object($this->db_conn) && get_class($this->db_conn) === 'mysqli' && ($ping_check == false || ($ping_check && @$this->db_conn->ping())));
    }

    function getSlaveInfo($dbPrefix)
    {
        if (!isset($GLOBALS['class_settings']))
            $settings = $GLOBALS['class_settings'] = @Setting::getInstance(DB_INFO);
        else
            $settings = $GLOBALS['class_settings'];

        if (!isset($GLOBALS[$dbPrefix . 'allSlaveInfo'])) {
            $allSlaveInfo = $settings->getAllByPrefix($dbPrefix);
            if (count($allSlaveInfo) <= 0)
                return null;

            $slave_must_key = array(
                'host',
                'userid',
                'password',
                'db',
                'is_slave'
            );

            $removeKey = array();

            foreach ($allSlaveInfo as $key => $value) {
                $slave_myst_key_check = count(array_intersect(array_keys($value), $slave_must_key)) == count($slave_must_key);
                if ($slave_myst_key_check == false || !(isset($value["is_slave"]) && $value["is_slave"] == "true")) {
                    $removeKey[] = $key;
                }
            }

            foreach ($removeKey as $key) {
                unset($allSlaveInfo[$key]);
            }

            $slave_load = rand(1, 9);
            $tempSlaveInfo = array();

            foreach ($allSlaveInfo as $key => $db_info) {
                if (!isset($db_info['slave_load']) || $db_info['slave_load'] >= $slave_load)
                    $tempSlaveInfo[$key] = $db_info;
            }

            if (count($tempSlaveInfo) <= 0) {
                $tempSlaveInfo = $allSlaveInfo;
            }

            $GLOBALS[$dbPrefix . 'allSlaveInfo'] = $tempSlaveInfo;
        } else {
            $allSlaveInfo = $GLOBALS[$dbPrefix . 'allSlaveInfo'];
        }

        if (count($allSlaveInfo) <= 0)
            return null;

        $slaveKeys = array_keys($allSlaveInfo);
        $db_choice = rand(0, 1000 * count($allSlaveInfo)) % count($allSlaveInfo);

        return $slaveKeys[$db_choice];
    }

    function isSlave()
    {
        return $this->is_slave;
    }

    protected function setSlave($is_slave)
    {
        $this->is_slave = $is_slave;
    }

    function getSlave()
    {
        if ($this->db_slave_used == true && $this->is_slave == false) {
            return $this->db_slave;
        }
        return null;
    }

    function excute_query($sql, $select_slave = true, $errorDie = true) // 쿼리 처리
    {
        //$this->db_slave_used
        if ($select_slave == true && $this->db_slave_used == true && $this->is_slave == false && (@strtolower(substr(trim($sql), 0, 6)) == 'select' || @strtolower(substr(trim($sql), 0, 6)) == '/*sl*/')) {
            return $this->db_slave->excute_query($sql, false, $errorDie);
        } else if (@strtolower(substr(trim($sql), 0, 6)) != 'select' && @strtolower(substr(trim($sql), 0, 6)) != '/*sl*/' && $this->is_slave) {
            die("슬레이브 DB");
            exit;
        }

        if (time() - $this->lastQueryTime > 15) {
            $selectCheck = @$this->db_conn->query("SHOW TABLES;");
            if (($selectCheck === null || $selectCheck === false) && ($this->db_conn->errno === 1046 || $this->db_conn->errno === 2006)) {
                $this->db_conn->close();
                unset($this->db_conn);

                $this->db_conn = new mysqli($this->db_info['host'], $this->db_info['userid'], $this->db_info['password'], $this->db_info['db'], $this->db_info['port']);
                $this->db_conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);

                if ($this->db_conn->errno != 0 && $errorDie) {
                    die("{$this->db_section} DB changeDB 문제가 있습니다.\n" . $this->db_conn->error);
                }

                if (isset($this->db_info['initQuery']) && $this->db_info['initQuery'] != "") {
                    @$this->db_conn->query($this->db_info['initQuery']);
                }
            }
        }
        $result = @$this->db_conn->query($sql);

        if ($result && $this->db_conn->errno == 0) {
            $this->lastQueryTime = time();
            return $result;
        } else {
            if ($errorDie == true) {
                $err = "QUERY ERROT : " . $this->db_conn->error;
                if (defined("MYSQL_ERROR_QUERY_PRINT")) {
                    $err = "\n<br />" . $sql;
                }
                echo $err;
                exit();
            }
        }
        return false;
    }

    function unbuffered_excute_query($sql, $select_slave = true, $errorDie = true) // 쿼리 처리
    {
        //$this->db_slave_used
        if ($select_slave == true && $this->db_slave_used == true && $this->is_slave == false && (@strtolower(substr(trim($sql), 0, 6)) == 'select' || @strtolower(substr(trim($sql), 0, 6)) == '/*sl*/')) {
            return $this->db_slave->excute_query($sql, false, $errorDie);
        } else if (@strtolower(substr(trim($sql), 0, 6)) != 'select' && @strtolower(substr(trim($sql), 0, 6)) != '/*sl*/' && $this->is_slave) {
            die("슬레이브 DB");
            exit;
        }

        if (time() - $this->lastQueryTime > 15) {
            $selectCheck = @$this->db_conn->query("SHOW TABLES;");
            if (($selectCheck === null || $selectCheck === false) && ($this->db_conn->errno === 1046 || $this->db_conn->errno === 2006)) {
                @$this->db_conn->close();
                unset($this->db_conn);

                $this->db_conn = new mysqli($this->db_info['host'], $this->db_info['userid'], $this->db_info['password'], $this->db_info['db'], $this->db_info['port']);
                $this->db_conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);

                if ($this->db_conn->errno != 0 && $errorDie) {
                    die("{$this->db_section} DB changeDB 문제가 있습니다.\n" . $this->db_conn->error);
                }

                if (isset($this->db_info['initQuery']) && $this->db_info['initQuery'] != "") {
                    @$this->db_conn->query($this->db_info['initQuery']);
                }
            }
        }
        $result = @$this->db_conn->query($sql, MYSQLI_USE_RESULT);

        if ($result && $this->db_conn->errno == 0) {
            $this->lastQueryTime = time();
            return $result;
        } else {
            if ($errorDie == true) {
                $err = "QUERY ERROT : " . $this->db_conn->error;
                if (defined("MYSQL_ERROR_QUERY_PRINT")) {
                    $err = "\n<br />" . $sql;
                }
                echo $err;
                exit();
            }
        }
        return false;
    }

    // 쿼리 실행과 동시에 패치 실행
    function fetch_query($sql, $resource_key = MYSQLI_ASSOC)
    {
        $result = $this->excute_query($sql);
        $return_data = $this->next_fetch($result);
        $this->free_result($result);
        return $return_data;
    }

    // 쿼리 실행과 동시에 패치 실행
    function selectOne($table, $field = "*", $where = "", $debug = "0")
    {
        if (is_array($field)) {
            $field = implode(" , ", $field);
        }
        $query = "SELECT " . $field . " FROM " . $table . " " . $where;
        //if($debug=='1') print $query;
        return $this->fetch_query($query);
    }

    function selectQuery($sql, $firstkey = false)
    {
        $ret = array();
        $result = $this->excute_query($sql);
        while (($row = $this->next_fetch($result)) != false) {
            if ($firstkey) {
                foreach ($row as $key => $value) {
                    $ret[$key][] = $value;
                }
            } else {
                $ret[] = $row;
            }
        }
        $this->free_result($result);

        if (count($ret) > 0) {
            return $ret;
        }

        return false;
    }

    function getRow($sQuery)
    {
        $result = $this->fetch_query($sQuery);
        if (!$result) return NULL;
        return $result;
    }

    function getAll($sQuery, $firstkey = false)
    {
        $ret = array();
        $result = $this->excute_query($sQuery);
        while (($row = $this->next_fetch($result)) != false) {
            if ($firstkey) {
                foreach ($row as $key => $value) {
                    $ret[$key][] = $value;
                }
            } else {
                $ret[] = $row;
            }
        }
        $this->free_result($result);

        if (count($ret) > 0) {
            return $ret;
        }

        return NULL;
    }

    function next_fetch($result, $resource_key = MYSQLI_ASSOC)
    {
        $return_data = NULL;
        if (!$this->checkResult($result))
            return false;
        if ($resource_key == "MYSQLI_OBJECT") {
            $return_data = @$result->fetch_object();
        } else if ($resource_key == MYSQLI_ASSOC) {
            $return_data = @$result->fetch_array(MYSQLI_ASSOC);
        } else if ($resource_key == MYSQLI_NUM) {
            $return_data = @$result->fetch_array(MYSQLI_NUM);
        } else if ($resource_key == MYSQLI_BOTH) {
            $return_data = @$result->fetch_array(MYSQLI_BOTH);
        } else {
            $return_data = @$result->fetch_array($resource_key);
        }

        if ($return_data === NULL)
            return false;

        return $return_data;
    }

    function next_fetch_row($result)
    {
        $return_data = NULL;
        if (!$this->checkResult($result))
            return false;
        $return_data = @$result->fetch_row();
        if ($return_data === NULL)
            return false;

        return $return_data;
    }

    function num_rows($result)
    {
        if (!$this->checkResult($result))
            return 0;
        return $result->num_rows;
    }

    function num_rows_two($sql)
    {
        $result = $this->excute_query($sql);
        $count = $this->num_rows($result);
        $this->free_result($result);
        return $count;
    }

    function escape_string($value)
    {
        return @$this->db_conn->real_escape_string($value);
    }

    function insertID()
    {
        return $this->db_conn->insert_id;
    }

    function getDBConn()
    {
        return $this->db_conn;
    }

    function free_result($result)
    {
        if ($this->checkResult($result))
            @$result->free();
    }

    function checkResult($result)
    {
        if ($result != null && is_object($result) && get_class($result) == "mysqli_result")
            return true;

        return false;
    }
}

?>
