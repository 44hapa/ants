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
        foreach($ants as $mapKey => $ant){
            $ant->clearTmp();
            $ant->fillAndReturnPrioritiPoints();
            $prioritets->add($ant);
        }
//        Tools::logger($prioritets->getList());

        $prioritets->determinateBests();

//        $ants = Bots::getInstance()->getList();
//        Tools::logger($prioritets->getList());
    }

    public function execute()
    {
        $this->slectPrioritis();

        $ants = Bots::getInstance()->getList();
        foreach ($ants as $ant){
            $log = !empty($ant->gol) ? print_r(Tools::createCoordinate($ant->gol), true) : 'НЕТУ';
            Tools::logger("Ant:[" . $ant->currentCoord . "] = $log");
            $coordinats = Tools::createCoordinate($ant->currentCoord);
//            if (empty($ant->gol)){
//                $ant->gol = Tools::createNum(28, 19);
//            }
            $golCoord = Tools::createCoordinate($ant->gol);
            $dir = $this->createDirection($coordinats, $golCoord);
            Steamer::issueOrder($coordinats[0], $coordinats[1], $dir);
//            break;
        }

    }

    public function doTurn(Steamer $ants )
    {
        $this->execute();
        return;

        $this->ants = $ants;

//        Tools::logger($ants->food);
        foreach ( $ants->myAnts as $ant ) {
//            $this->getPriorityZone($ant);

            if ($prior = $this->getPriorityZone($ant)){
                $dir = $this->createDirection($ant, $prior);
                Steamer::issueOrder($ant[0], $ant[1], $dir);
//                Tools::logger();
//                Tools::logger($prior);
//                Tools::logger($ant);
//                Tools::logger("\n$dir\n");
//                Tools::logger();

                continue;
            }

//            Tools::logger($this->getPriorityZone($ant));
            list ($aRow, $aCol) = $ant;
            foreach ($this->directions as $direction) {
                list($dRow, $dCol) = Tools::destination($aRow, $aCol, $direction);
                if ($ants->passable($dRow, $dCol)) {
                    $ants->issueOrder($aRow, $aCol, $direction);
                    break;
                }
            }
        }
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
                foreach(Tools::$food as $keyFood => $food){
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