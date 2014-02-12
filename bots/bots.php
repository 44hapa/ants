<?php

class Bots
{
    private $list = array();

    private $nextCoordinates = array('next' => 'prev');

    /**
     *
     * @var Bots
     */
    private static $instance;

    private function __construct()
    {

    }


    public function getList()
    {
        return $this->list;
    }

    
    public function add($mapCoordinat)
    {
        // Если у бота нет "будущей координаты" - значит он новенький
        if ($this->isNew($mapCoordinat)) {
            $bot = new Bot();
            $bot->currentCoord = $mapCoordinat;
        }

        $this->list[$mapCoordinat] = $bot;
    }


    public function getByNextCoord($mapCoordinat)
    {
        foreach ($this->list as  $bot) {
            if ($bot->nextCoord == $mapCoordinat) {
                return $bot;
            }
        }
        return null;
    }

    /**
     * @param int $key
     * @return array
     */
    public function getByKey($key)
    {
        return $this->list[$key];
    }



    public function isNew($key)
    {
        if (!$this->nextCoordinates[$key]) {
            return true;
        }
        return false;
    }


    public function clear()
    {
        $this->list = array();
    }


    /**
     *
     * @return Bots
     */
    static public function getInstance()
    {
        if (!self::$instance){
            self::$instance = new self;
        }
        return self::$instance;
    }
    
}