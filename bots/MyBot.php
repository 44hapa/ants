<?php

//require_once 'Ants.php';
require_once 'steamer.php';

class MyBot
{
    private $directions = array('n','e','s','w');

    /**
     * @var Ants
     */
    private $ants;


    public function doTurn(Ants $ants )
    {
        $this->ants = $ants;

//        Ants::logger($ants->food);
        foreach ( $ants->myAnts as $ant ) {
//            $this->getPriorityZone($ant);

            if ($prior = $this->getPriorityZone($ant)){
                $dir = $this->createDirection($ant, $prior);
                $ants->issueOrder($ant[0], $ant[1], $dir);
//                Ants::logger();
//                Ants::logger($prior);
//                Ants::logger($ant);
//                Ants::logger("\n$dir\n");
//                Ants::logger();

                continue;
            }

            Ants::logger($this->getPriorityZone($ant));
            list ($aRow, $aCol) = $ant;
            foreach ($this->directions as $direction) {
                list($dRow, $dCol) = $ants->destination($aRow, $aCol, $direction);
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
//        Ants::logger("$fromX => $toX, $fromY => $toY \n");
        for ($Q = $fromX; $Q <= $toX; $Q++) {
            for ($W = $fromY; $W <= $toY; $W++){
                $x = $Q > 0 ? $Q : $rows + $Q;
                $y = $W > 0 ? $W : $cols + $W;
                $priorityZone[$ant[0] . "." . $ant[1]][] = array($x,$y);
                foreach($this->ants->food as $keyFood => $food){
                    if ($food == array($x, $y)){
                        // TODO:    нужно выбрать лучшего муравья!!!

                        $result = $food;
                        // Убурем заданую еду
//                        unset($this->ants->food[$keyFood]);
                        return $result;
                    }
//                    Ants::logger($food);
//                    Ants::logger("$x :: $y");
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

Ants::run( new MyBot() );