<?php
class Job
{
    private  $table = 'job';

    public static $job_type = array(
        'NR'      => '네이버 연관검색어',
    );

    public static $client_minver = array(
        '1',
    );

    public static $status = array(
        '정상',
        '정지',
    );
    public static $oslang_section = '|';

    // 총 갯수
    public function getCount($params) {
        global $db_con;
        $total = 0;

        $where = array();
        foreach ($params as $k => $v) {
            if (strlen($v) == 0) {
                continue;
            }
            if (true ==in_array($k, array('jobname', 'oslang'))) {
                $where[] = $k ." like '%" . $v ."%'";
            } else {
                $where[] = $k ."='" . $v ."'";
            }
        }
        $where = join(' AND ', $where);
        if (strlen($where) > 0) {
            $where = ' WHERE ' . $where;
        }

        $query =<<<SQL
            SELECT count(1) AS cnt FROM `{$this->table}` {$where}
SQL;

        $result = $db_con->getRow($query);
        $total = $result['cnt'];

        return $total;
    }

    public function getList($params = array(), $offset = 0, $limit = 0, $order_by= array()) {
        global $db_con;

        $where = array();
        foreach ($params as $k => $v) {
            if (strlen($v) == 0) {
                continue;
            }
            if (true ==in_array($k, array('jobname', 'oslang'))) {
                $where[] = $k ." like '%" . $v ."%'";
            } else {
                $where[] = $k ."='" . $v ."'";
            }
        }
        $where = join(' AND ', $where);
        if (strlen($where) > 0) {
            $where = ' WHERE ' . $where;
        }

        $query =<<<SQL
            SELECT * FROM `{$this->table}` {$where} 
SQL;
        if (sizeof($order_by) > 0) {
            $query .= ' ORDER BY ' . join(',', $order_by);
        }

        if ((int) $limit > 0) {
            $query .= ' LIMIT ' . $offset . ', '.$limit;
        }
        //echo $query;
        $list = $db_con->getAll($query);

        return $list;
    }

    // 등록
    public function doRegister($params) {
        global $db_con;

        // Job언어
        $params['oslang'] = join(Job::$oslang_section, $params['oslang']);

        // 시작일정
        $params['startdt'] = join(' ', $params['startdt']);

        // 종료일정
        $params['enddt'] = join(' ', $params['enddt']);

        $set_field = array();
        foreach ($params as $k => $v) {
            if (strlen($v) == 0) {
                continue;
            }
            $set_field[] = $k ."='" . $v ."'";
        }
        $field = join(',', $set_field);

        $query =<<<SQL
            INSERT INTO `{$this->table}` set {$field}
SQL;

        $result = $db_con->excute_query($query);
        if ($result) {
            return true;
        }
        return false;
    }

    // 수정
    public function doModify($params) {
        global $db_con;

        $jobid = $params['jobid'];
        unset($params['jobid']);
        if ((int) $jobid == 0) {
            return false;
        }

        // Job언어
        $params['oslang'] = join(Job::$oslang_section, $params['oslang']);

        // 시작일정
        $params['startdt'] = join(' ', $params['startdt']);

        // 종료일정
        $params['enddt'] = join(' ', $params['enddt']);

        $set_field = array();
        foreach ($params as $k => $v) {
            if (strlen($v) == 0) {
                continue;
            }
            $set_field[] = $k ."='" . $v ."'";
        }
        $field = join(',', $set_field);

        $query =<<<SQL
            UPDATE `{$this->table}` SET {$field} WHERE jobid = '{$jobid}'
SQL;

        $result = $db_con->excute_query($query);
        if ($result) {
            return true;
        }
        return false;
    }
}