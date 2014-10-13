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
        // Осмысленная часть

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
        Tools::logger("Уже в следующих: " . print_r($this->nextCoordinates, true));
//        Tools::logger($ant->coordinatColRow);
        Tools::logger($nextCoordinat);
        Tools::logger("Расчет  Для мураша " . implode(' : ' , $ant->coordinatColRow) . " nextCol $nextCol, nextRow $nextRow, nextNumCol $nextNumCol, nextNumRow $nextNumRow");

        if( $nextCoordinat['col'] != $ant->coordinatColRow['col'] && !$this->antOrWater($nextNumCol)){
            Tools::logger("Осмыслено по COL");
            $this->addNext($ant, $nextNumCol);
            return $direction['col'];
        }

        if( $nextCoordinat['row'] != $ant->coordinatColRow['row'] && !$this->antOrWater($nextNumRow)){
            Tools::logger("Осмыслено по ROW");
            Tools::logger("{2}$nextNumRow");
            $this->addNext($ant, $nextNumRow);
            return $direction['row'];
        }

        Tools::logger("Нет смысла-______________________");

        // Осмысленное движение закончено. Переходим к рандому

        // Имеет смысл выбирать противоположное направление в последнюю очередь!
        $randomDir = Tools::getSortRandomDirExcludeBadStep($direction);

/*
        'n' => -1,
        'e' => 1,
        's' => 1,
        'w' => -1,
        '0' => 0,
        // w - запад col
        // e - восток col
        // n - север row
        // s - юг row
 */

        Tools::logger("Были такие направления: " . print_r($direction, true));
        Tools::logger("Стали рандомом направления: " . print_r($randomDir, true));

        foreach($randomDir as $dir => $dirNum){
            $dir == 'n' || $dir == 's' ? $dirArray = array('col' => 0, 'row' => $dir) : $dirArray = array('col' => $dir, 'row' => 0);

            Tools::logger("Пытаемся считать для такого направления : dir = $dir | dirNum = $dirNum");

            $nextCoordinat = Tools::nextStep($ant->coordinatColRow['col'], $ant->coordinatColRow['row'], $dirArray);
            $nextCol = $nextCoordinat['col'];
            $nextRow = $nextCoordinat['row'];

            Tools::logger("Текущие координаты бота " . print_r($ant->coordinatColRow, true));
            Tools::logger("Желательные " . print_r($nextCoordinat, true));

            $nextNumCol = Tools::createNum($ant->coordinatColRow['row'], $nextCol);
            $nextNumRow = Tools::createNum($nextRow, $ant->coordinatColRow['col']);

            if( $nextCol != $ant->coordinatColRow['col'] && !$this->antOrWater($nextNumCol)){
                Tools::logger("Бессмысленно COL");
                $this->addNext($ant, $nextNumCol);
                return $dir;
            }

            if( $nextRow != $ant->coordinatColRow['row'] && !$this->antOrWater($nextNumRow)){
                Tools::logger("Бессмысленно ROW");
                $this->addNext($ant, $nextNumRow);
                return $dir;
            }
        }

        // TODO: механизм отката бота, который будет меня давить.
        // Переработать механику Ж(


        $this->addNext($ant, $ant->currentCoord);
        return 'XUI';
    }




    public function antOrWater($nextNum)
    {
        $nextCoordinat = implode(':' , Tools::createCoordinate($nextNum));
        if (array_key_exists($nextNum, $this->nextCoordinates)){
            Tools::logger("В клетке $nextNum [$nextCoordinat] будет БОТ");
            return true;
        }else{
            Tools::logger("В клетке $nextNum [$nextCoordinat] НЕТ БОТА");
            Tools::logger("ВОТ ДОКАЗУХА "  . print_r($this->nextCoordinates , true));
        }
        if (Steamer::$staticMap[$nextNum] == WATER){
            Tools::logger("В клетке $nextNum [$nextCoordinat] ВОДА");
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
//        $this->prevCoordinats = $this->list;
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