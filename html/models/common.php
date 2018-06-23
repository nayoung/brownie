<?php
class Common
{
    private  $table = 'setting';

    public function getList($params = array(), $offset = 0, $limit = 0, $order_by= array()) {
        global $db_con;
        $list = array();

        $where = array();
        foreach ($params as $k => $v) {
            if (strlen($v) == 0) {
                continue;
            }
            $where[] = $k ."='" . $v ."'";
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

        $list = $db_con->getAll($query);

        return $list;
    }

    // 등록
    public function doRegister($request) {
        global $db_con;
        $params = $request;

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

}