<?php
/**
 * Created by PhpStorm.
 * User: guru
 * Date: 11.02.14
 * Time: 22:24
 */

class Prioritets
{

    private $list = array();

    public static $instance;

    private function __construct()
    {

    }


    public function getList()
    {
        return $this->list;
    }

    /**
     * $prioritet = array('mapKey' => 123, 'length' => '5', 'antNum' => 101);
     * mapKey - номер клетки на карте
     * length - расстояние до муравья-инициатор
     * antNum - номер муравья
     * @param array $prioritet
     * @return int
     */
    public function add(array $prioritet)
    {
        $this->list[] = $prioritet;
        return key($this->list);
    }


    /**
     * @param int $key
     * @return array
     */
    public function getByKey($key)
    {
        return $this->list[$key];
    }

    public function getInstance()
    {
        if (!self::$instance){
            self::$instance = new self;
        }
        return self::$instance;
    }
}