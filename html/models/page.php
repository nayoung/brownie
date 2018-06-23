<?php
class Page
{
    private $page = 0;
    private $total = 0;
    private $scale = 0;
    private $block = 0;
    private $url = '';
    private $request = array();

    public function __construct($page = 1, $total = 0, $url = '', $request = array(), $scale = 50, $block = 10) {
        $this->page = $page;
        $this->total = $total;
        $this->scale = $scale;
        $this->block = $block;
        $this->url = $url;
        $this->request = $request;
    }

    public function init() {
        if ((int) $this->total == 0) {
            return array('html' => '', 'offset' => 0, 'limit' => 0);
        }

        $paging = "";
        $this->page = ((int) $this->page == 0)? 1:$this->page;

        if (is_array($this->request)) {
            unset($this->request['page']);
        } else {
            $this->request = array();
        }
        $params = array();
        foreach ($this->request as $k => $v) {
            $params[] = $k . '=' . $v;
        }
        $query_string = join('&', $params);
        $href = $this->url . '?' . $query_string;

        $total_page =  ceil($this->total / $this->scale);
        $start_page = $this->page - ($this->page % $this->block) + 1;
        $end_page = $start_page + $this->block;

        if ($end_page > $total_page ) {
            $end_page = $total_page;
        }

        $prev_page = $start_page - 1;
        if ($prev_page > 0) {
            $href .= '&page=' . $prev_page;
            $paging .= '<a href="' . $href . '">Prev</a>';
        }

        for ($p = $start_page; $p <= $end_page; $p++) {
            $href .= '&page=' . $p;

            if ($p == $this->page) {
                $paging .= '<a href="' . $href . '" class="active">1</a>';
            } else {
                $paging .= '<a href="' . $href . '">1</a>';
            }
        }

        $next_page = $end_page + 1;
        if ($next_page <= $total_page) {
            $href .= '&page=' . $next_page;
            $paging .= '<a href="' . $href . '">Prev</a>';
        }

        $paging = '<div class="pager">' . $paging . '</div>';

        return array('html' => $paging, 'offset' => (($this->page - 1) * $this->scale), 'limit' => $this->scale);
    }
}