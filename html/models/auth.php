<?php
class Auth
{
    private  $table = 'auth';

    // 총 갯수
    public function getCount($params) {
        global $db_con;
        $total = 0;

        $where = array();
        foreach ($params as $k => $v) {
            if (strlen($v) == 0) {
                continue;
            }

            if ($k == 'not_id') {
                $where[] = "id !='" . $v ."'";
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

    public function getAuthMenu($auth) {
        global $db_con;
        $query =<<<SQL
            SELECT * 
            FROM `menu` LEFT JOIN `auth_menu` ON `menu`.id = `auth_menu`.menu
            WHERE `auth_menu`.auth = '{$auth}'
            ORDER BY `menu`.parent_id ASC, `menu`.id ASC
SQL;

        $list = $db_con->getAll($query);

        return $list;

    }

    public static function getAuth($menu = 0, $type = '') {
        $auth = $_SESSION['auth'];

        if ($auth[$menu][$type] != 'Y') {
            $message = '권한이 없습니다. 관리자에게 문의하세요.';
            $close = true;
            $back = true;
            include_once _VIEW_PATH . 'redirect.html';
        }
    }

    // 등록
    public function doRegister($params) {
        global $db_con;

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

    // 정보수정
    public function doModify($params) {
        if (strlen($params['id']) == 0) {
            return false;
        }

        global $db_con;
        $id = $params['id'];
        unset($params['id']);

        $set_field = array();
        foreach ($params as $k => $v) {
            if ($k == 'not_id') {
                $set_field[] = $k ."!='" . $v ."'";
            } else {
                $set_field[] = $k ."='" . $v ."'";
            }
        }
        $field = join(',', $set_field);

        $query =<<<SQL
            UPDATE `{$this->table}` set {$field} WHERE id = '{$id}'
SQL;
        $result = $db_con->excute_query($query);
        if ($result) {
            return true;
        }
        return false;
    }

    public function apply($params)
    {
        global $db_con;

        $menu = new Menu;
        $menu_list = $menu->getList();

        $bool = true;
        foreach ($menu_list as $m) {
            if ((int) $m['parent_id'] == 0) {
                continue;
            }
            $am = $params['menu'][$m['id']];
            if (sizeof($am) > 0) {
                $am['read'] = ($am['read'] == 'Y') ? $am['read'] : 'N';
                $am['write'] = ($am['write'] == 'Y') ? $am['write'] : 'N';
            } else {
                $am = $params['menu'][$m['id']] = array('read' => 'N', 'write' => 'N');
            }
            $query = <<<SQL
            INSERT INTO auth_menu (`auth`, `menu`, `read`, `write`) VALUES ('{$params['auth']}', '{$m['id']}', '{$am['read']}', '{$am['write']}') 
            ON DUPLICATE KEY UPDATE `read` = '{$am['read']}', `write` = '{$am['write']}';
SQL;
            $result = $db_con->excute_query($query);
            if (!$result) {
                $bool = false;
            }

        }

        return $bool;
    }
}