<?php

class Bot
{
    public $prevCoord;
    public $currentCoord;
    public $nextCoord;

    public $stepPrior = 5;
    

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
    public function getPrioritiZone()
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

        return $priorityZone;
    }

    /**
     * Возвращет список точек с едой, которые ввходят в мой приоритет
     * @return array
     */
    public function getPrioritiFood()
    {
        $prioritiPointFood = array();
        $priorityZone = $this->getPrioritiZone();

        foreach (Ants::$food as $mapKey => $coordinates) {
            if (isset($priorityZone[$mapKey])) {
                $prioritiPointFood[$mapKey] = Ants::mapDistance($coordinates, $this->currentCoord);
            }
        }

        return $prioritiPointFood;
    }
}