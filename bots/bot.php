<?php

class Bot
{
    public $number;

    public $prevCoord;
    public $currentCoord;
    public $nextCoord;
    public $gol;
    public $golCoordinat;

    public $coordinatColRow = array();

    public $stepPrior = 7;

    /**
     * [координата цели] = расстояние до цели
     * @var array
     */
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
    public function getPrioritiZone()
    {
        $coordinat = Tools::createCoordinate($this->currentCoord);

        $left = $coordinat['col'] - $this->stepPrior;
        $right = $coordinat['col'] + $this->stepPrior;
        $up = $coordinat['row'] - $this->stepPrior;
        $down = $coordinat['row'] + $this->stepPrior;


//        echo "\n\n";
//        print_r($this->currentCoord);
//        echo "\n\n";
//        print_r($coordinat);
//        echo "\n\n";
//
//        echo "\nс горизонт от ($left) до ($right)\n";
//        echo "\nс вертикаль от ($up) до ($down)\n";

        $priorityZone = array();

        for ($moveX = $left; $moveX <= $right; $moveX++) {
            for ($moveY = $up; $moveY <= $down; $moveY++){
                // Если выехали за правый край
                if ($moveX >= Tools::$cols){
                    $x = $moveX - Tools::$cols;
                }else{
                    $x = $moveX;
                }

                if ($moveY >= Tools::$rows){
                    $y = $moveY - Tools::$rows;
                }else{
                    $y = $moveY;
                }

                $mapCoordinat = Tools::createNum($y, $x);
//                $priorityZone[$mapCoordinat] = null; // Расстояние до этого приоритета
                $priorityZone[$mapCoordinat] = "col=$x: row=$y"; // Расстояние до этого приоритета
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

        $foods = Tools::$food;
        foreach ($foods as $mapKey => $coordinates) {
            if (array_key_exists($mapKey, $priorityZone)) {
                $prioritiPointFood[$mapKey] = Tools::mapDistance($mapKey, $this->currentCoord);
            }
            else{
//                Tools::logger("Еда НЕ!!! попала в приоритет!!");
//                Tools::logger($mapKey);
            }
        }
        return $prioritiPointFood;
    }


    // TODO: ну очень мутная херня. Переработать!!!
    public function fillAndReturnPrioritiPoints()
    {
        $this->prioritiPoints = $this->getPrioritiFood();
        // Если нет приоритотов и цели по умолчанию - укажем ближайшую не открытую точку
        if (empty($this->prioritiPoints) && empty(Tools::$defaultGoal)) {
            Tools::logger('Ни еды ни дома.');
            $mapSlice = array_slice(Steamer::$staticMap, $this->currentCoord);
            foreach ($mapSlice as $key=>$value){
                if ($value == UNSEEN) {
                    $this->prioritiPoints[$key] = Tools::mapDistance($key, $this->currentCoord);
                    $gol = Tools::createCoordinate($key);
                    Tools::logger('В итоге идем в темноту в col:'.$gol['col'] . ' row:'.$gol['row']  . print_r($this->prioritiPoints, true));
                    return $this->prioritiPoints;
                }
            }
        }
        return $this->prioritiPoints;
    }


    public function setGol($gol)
    {
        // Если еще нет цели, присваивае новую.
        if (empty($this->gol))
        {
            $this->gol = $gol;
            $this->golCoordinat = Tools::createCoordinate($gol);
            return;
        }
        // Вычислим расстояние до новой цели.
        $distNew = Tools::mapDistance($this->currentCoord, $gol);
        $distOld = Tools::mapDistance($this->currentCoord, $this->gol);

        // Если новое рассояние меньше - гуд!
        if ($distNew < $distOld){
            $this->gol = $gol;
            return;
        }

        return;
    }

    public function clearTmp()
    {
        $this->gol = null;
        $this->nextCoord = null;
        $this->prioritiZone = array();
        $this->prioritiPoints = array();
    }
}