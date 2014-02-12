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
        $coordinat = Ants::createCoordinate($this->currentCoord);

        $left = $coordinat[0] - $this->stepPrior;
        $right = $coordinat[0] + $this->stepPrior;
        $up = $coordinat[1] - $this->stepPrior;
        $down = $coordinat[1] + $this->stepPrior;

        $rows = Ants::$rows;
        $cols = Ants::$cols;

        $priorityZone = array();
        for ($moveX = $left; $moveX <= $right; $moveX++) {
            for ($moveY = $up; $moveY <= $down; $moveY++){
                $x = $moveX > 0 ? $moveX : $rows + $moveX;
                $y = $moveY > 0 ? $moveY : $cols + $moveY;
                $mapCoordinat = Ants::createNum($x, $y);
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

//        Ants::logger("QQQ\n");
//        Ants::logger(Ants::$food);
//        Ants::logger("WWW\n");

        $foods = Ants::$food;
        foreach ($foods as $mapKey => $coordinates) {
//            Ants::logger("$mapKey \n");
//            Ants::logger($coordinates);
            if (array_key_exists($mapKey, $priorityZone)) {
                $prioritiPointFood[$mapKey] = Ants::mapDistance($mapKey, $this->currentCoord);
            }
        }

//        Ants::logger($prioritiPointFood);

        return $prioritiPointFood;
    }


    public function fillAndReturnPrioritiPoints()
    {
        $this->prioritiPoints = $this->getPrioritiFood();
//        Ants::logger($this->prioritiPoints);
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