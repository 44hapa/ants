<?php

require_once 'steamer.php';
require_once 'bot.php';
require_once 'bots.php';
require_once 'prioritets.php';
require_once 'tools.php';

class MyBot
{
    private $directions = array('n','e','s','w');

    /**
     * @var Steamer
     */
    private $ants;


    public function slectPrioritis()
    {

        $prioritets = Prioritets::getInstance();
        $prioritets->clear();
        $ants = Bots::getInstance()->getList();
//        Tools::logger("Расчет координат для " . count($ants) . "ботов");
        foreach($ants as $mapKey => $ant){
            $ant->clearTmp();
            $priorityPoints = $ant->fillAndReturnPrioritiPoints();
            if (!empty($priorityPoints)){
                $prioritets->add($ant);
            }
        }
//        Tools::logger($prioritets->getList());

        $prioritets->determinateBests();

//        $ants = Bots::getInstance()->getList();
//        Tools::logger($prioritets->getList());
    }

    public function execute()
    {
//        Tools::$defaultGoal = Tools::createNum(14, 19);
        $this->slectPrioritis();

        Bots::getInstance()->clearNext();
        $ants = Bots::getInstance()->getList();
        foreach ($ants as $ant){

            $log = !empty($ant->gol) ? implode(Tools::createCoordinate($ant->gol), ':') : 'НЕТУ';
//            Tools::logger("Ant:[" . implode(Tools::createCoordinate($ant->currentCoord ), ':'). "] = EDA:$log");
            $coordinats = Tools::createCoordinate($ant->currentCoord);
            if (empty($ant->gol)){
//                Tools::logger("У этого нет цели " . $ant->currentCoord .  " Ставим ему дефолт");
//                $ant->gol = $ant->currentCoord;
//                $ant->gol = Tools::createNum(28, 19);
//                $ant->gol = Tools::createNum(10, 67);
//                $ant->gol = Tools::createNum(14, 19);
            }
//            $golCoord = Tools::createCoordinate($ant->gol);
//            $dir = $this->createDirection($coordinats, $golCoord);
//            $dir = Tools::createDirection($coordinats, $golCoord);
            $dirArray = Tools::createDirection($ant);
//            Tools::logger("GO TO ВЫБОР ИЗ : " . implode(':',$dirArray));
            $dir = Bots::getInstance()->selectMove($ant, $dirArray);
//            Bots::getInstance()->addNext($ant, $dir);
//            Tools::logger("GO TO ВЫБРАЛ $dir");
//            Tools::logger("GO TO Этот выбрал - >  " . print_r($coordinats,true));

            $nextCoordinat = Tools::nextStep($ant->coordinatColRow['col'], $ant->coordinatColRow['row'], $dir);
            $nextNum = Tools::createNum($nextCoordinat['row'], $nextCoordinat['col']);
            
            Steamer::issueOrder($coordinats['row'], $coordinats['col'], $dir);
//            break;
        }

    }

    public function doTurn(Steamer $ants )
    {
        $this->execute();
        return;
    }


    private function getPriorityZone($ant)
    {
        $step = 5;
        $rows = 43;
        $cols = 39;

        $fromX = $ant[0] - $step;
        $toX = $ant[0] + $step;
        $fromY = $ant[1] - $step;
        $toY = $ant[1] + $step;


        $priorityZone = array();
//        Tools::logger("$fromX => $toX, $fromY => $toY \n");
        for ($Q = $fromX; $Q <= $toX; $Q++) {
            for ($W = $fromY; $W <= $toY; $W++){
                $x = $Q > 0 ? $Q : $rows + $Q;
                $y = $W > 0 ? $W : $cols + $W;
                $priorityZone[$ant[0] . "." . $ant[1]][] = array($x,$y);
                foreach(Steamer::$food as $keyFood => $food){
                    if ($food == array($x, $y)){
                        // TODO:    нужно выбрать лучшего муравья!!!

                        $result = $food;
                        // Убурем заданую еду
//                        unset($this->ants->food[$keyFood]);
                        return $result;
                    }
//                    Tools::logger($food);
//                    Tools::logger("$x :: $y");
                }
            }
        }

//        return $priorityZone;
        return null;
    }


    private function createDirection($iAm, $food){
        if ($iAm[0] > $food[0]){
            return  'n';
        }
        if ($iAm[0] < $food[0]){
            return  's';
        }
        if ($iAm[1] > $food[1]){
            return  'w';
        }
        if ($iAm[1] < $food[1]){
            return  'e';
        }
    }

}

Steamer::run( new MyBot() );