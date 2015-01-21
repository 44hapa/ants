<?php

class Bots
{
    private $list = array();

    // 'next' => 'current'
    private $nextCoordinates = array();

    private $golCoordinats = array();

    /**
     *
     * @var Bots
     */
    private static $instance;

    private function __construct()
    {

    }


    /**
     * @return Bot[]
     */
    public function getList()
    {
        return $this->list;
    }

    
    public function add(Bot $bot)
    {
        $mapCoordinat = $bot->currentCoord;
        if (!is_integer($mapCoordinat)){
            throw new Exception('Не передал координаты');
        }
        $this->list[$mapCoordinat] = $bot;
    }


    public function addNext(Bot $ant, $nextNum)
    {
        Tools::logger("addNext : $nextNum");
        $this->nextCoordinates[$nextNum] = $ant->currentCoord;
    }

    /**
     * @param Bot $ant
     * @param $direction массив направлений, куда желательно пойти боту (w|s|n|e|0)
     * @return int|string
     */
    public function selectMove(Bot $ant, $direction)
    {
        // Следующие координаты при смещении на direction
        $nextCoordinat = Tools::nextStep($ant->coordinatColRow['col'], $ant->coordinatColRow['row'], $direction);
        $nextCol = $nextCoordinat['col'];
        $nextRow = $nextCoordinat['row'];

        // Точка смещениея по COL (горизонталь)
        $nextNumCol = Tools::createNum($ant->coordinatColRow['row'], $nextCol);
        // Точка смещениея по ROW (вертикаль)
        $nextNumRow = Tools::createNum($nextRow, $ant->coordinatColRow['col']);

        Tools::logger("=========MOVE СТАРТ========\n");
        Tools::logger("БОТ " . implode(' : ' , $ant->coordinatColRow) . " хочет сожрать №{$ant->gol} = " . implode(' : ' , Tools::createCoordinate($ant->gol)));
        Tools::logger("Расчет  Для мураша " . implode(' : ' , $ant->coordinatColRow) . " nextCol $nextCol, nextRow $nextRow, nextNumCol $nextNumCol, nextNumRow $nextNumRow");

        if( $nextCoordinat['col'] != $ant->coordinatColRow['col'] && !$this->antOrWater($nextNumCol)){
            $this->addNext($ant, $nextNumCol);
            return $direction['col'];
        }

        if( $nextCoordinat['row'] != $ant->coordinatColRow['row'] && !$this->antOrWater($nextNumRow)){
            $this->addNext($ant, $nextNumRow);
            return $direction['row'];
        }

        Tools::logger("Нет смысла-______________________");

        // Осмысленное движение закончено. Переходим к рандому

        // Никакого рандома!
        return 'RANDOM';
    }




    public function antOrWater($nextNum)
    {
        $nextCoordinat = implode(':' , Tools::createCoordinate($nextNum));
        if (array_key_exists($nextNum, $this->nextCoordinates)){
//            Tools::logger("В клетке $nextNum [$nextCoordinat] будет БОТ");
            return true;
        }else{
//            Tools::logger("В клетке $nextNum [$nextCoordinat] НЕТ БОТА");
//            Tools::logger("ВОТ ДОКАЗУХА "  . print_r($this->nextCoordinates , true));
        }
        if (Steamer::$staticMap[$nextNum] == WATER){
//            Tools::logger("В клетке $nextNum [$nextCoordinat] ВОДА");
            return true;
        }

        false;
    }

    /**
     * @param int $key
     * @return Bot
     */
    public function getByKey($key)
    {
        return $this->list[$key];
    }


    public function getByPrevKey($key)
    {
        return $this->prevCoordinats[$key];
    }

    public function isNew($key)
    {
        if (!isset($this->nextCoordinates[$key])) {
            return true;
        }
        return false;
    }

    public function getNextList($asCoordinate = false)
    {
        if ($asCoordinate){
            $nextListAsCoordinat = array();
            foreach ($this->nextCoordinates as $next => $antCoordinat){
                $nextAsCoordinat = implode(':', Tools::createCoordinate($next));
                $antAsCoordinate = implode(':', Tools::createCoordinate($antCoordinat));
                   $nextListAsCoordinat[$nextAsCoordinat] = $antAsCoordinate;
            }
            return $nextListAsCoordinat;
        }

        return $this->nextCoordinates;
    }

    public function clearNext()
    {
        $this->nextCoordinates = array();
    }

    public function clear()
    {
        $this->nextCoordinates = array();
        foreach ($this->list as $key => $bot){
            unset($bot);
        }
        $this->list = array();
        $this->nextCoordinates = array();
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