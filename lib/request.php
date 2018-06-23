<?php
/**
 * Created by PhpStorm.
 * User: mia
 * Date: 2018-06-06
 * Time: 오후 5:16
 */

class Request
{
    protected $get;
    protected $post;
    protected $files;
    protected $server;
    protected $cookies;

    public function __construct(
        $get = array(),
        $post = array(),
        $files = array(),
        $server = array(),
        $cookies = array()
    ) {
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
        $this->server = $server;
        $this->cookies = $cookies;
    }

    public function access($array, $key, $defaultValue = null) {
        if (array_key_exists($key, $array) == false) {
            return $defaultValue;
        }
        return $array[$key];
    }
    public function get($key, $defaultValue = null){
        return $this->access($this->get, $key, $defaultValue);
    }

    public function post($key, $defaultValue = null){
        return $this->access($this->post, $key, $defaultValue);
    }

    public function server($key, $defaultValue = null){
        return $this->access($this->server, $key, $defaultValue);
    }

    public function getMethod(){
        return $this->access($this->server, 'REQUEST_METHOD');
    }

    public function getRequest(){
        return array_merge($this->get, $this->post);
    }
}