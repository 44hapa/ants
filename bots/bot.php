<?php

class Bot
{
    public $prevCoord;
    public $currentCoord;
    public $nextCoord;
    public $gol;

    public $stepPrior = 5;
    public $prioritiPoints = array();

    /**
     *Область, в которой ищем обьекты, для приритета
     * @var array
     */
    public $prioritiZone;

    /**
     *
     *  Возвращает массив точек, которые входят в мой приоритет
     * @return array
     */
    private function getPrioritiZone()
    {
        $coordinat = Tools::createCoordinate($this->currentCoord);

        $left = $coordinat[0] - $this->stepPrior;
        $right = $coordinat[0] + $this->stepPrior;
        $up = $coordinat[1] - $this->stepPrior;
        $down = $coordinat[1] + $this->stepPrior;

        $rows = Tools::$rows;
        $cols = Tools::$cols;

        $priorityZone = array();
        for ($moveX = $left; $moveX <= $right; $moveX++) {
            for ($moveY = $up; $moveY <= $down; $moveY++){
                $x = $moveX > 0 ? $moveX : $rows + $moveX;
                $y = $moveY > 0 ? $moveY : $cols + $moveY;
                $mapCoordinat = Tools::createNum($x, $y);
                $priorityZone[$mapCoordinat] = null; // Расстояние до этого приоритета
            }
        }


        $this->prioritiZone = $priorityZone;
        return $priorityZone;
    }

    /**
     * Возвращет список точек с едой, которые ввходят в мой приоритет
     * @return array
     */
    private function getPrioritiFood()
    {
        $prioritiPointFood = array();
        $priorityZone = $this->getPrioritiZone();

//        Tools::logger("QQQ\n");
//        Tools::logger(Tools::$food);
//        Tools::logger("WWW\n");

        $foods = Tools::$food;
        foreach ($foods as $mapKey => $coordinates) {
//            Tools::logger("$mapKey \n");
//            Tools::logger($coordinates);
            if (array_key_exists($mapKey, $priorityZone)) {
                $prioritiPointFood[$mapKey] = Tools::mapDistance($mapKey, $this->currentCoord);
            }
        }

//        Tools::logger($prioritiPointFood);

        return $prioritiPointFood;
    }


    public function fillAndReturnPrioritiPoints()
    {
        $this->prioritiPoints = $this->getPrioritiFood();
//        Tools::logger($this->prioritiPoints);
        return $this->prioritiPoints;
    }

    public function clearTmp()
    {
        $this->gol = null;
        $this->nextCoord = null;
        $this->prioritiZone = array();
        $this->prioritiPoints = array();
    }
}