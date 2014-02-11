<?php

require_once 'Ants.php';

function dd(){
    $args = func_get_args();
    foreach ($args as $value){
        dump($value);
    }
    die;
}

function dump($variable){
    ob_start();
    if (is_array($variable) || is_object($variable)){
        print_r($variable);
    }
    else{
        var_dump($variable);
    }
    $content = ob_get_clean();
    file_put_contents('/home/topas/ant/output.log', $content, FILE_APPEND);
}

class Ant 
{
    public $x;
    public $y;
    
    public $toX;
    public $toY;
    
    private $target;
    
    public $id;
    static private $auto = 0;
    
    /**
     * @var Ants
     */
    private $core;
    
    public function __construct($x, $y, Ants $core){
        $this->x = $x;
        $this->y = $y;
        $this->core = $core;
        $this->id = self::$auto++;
    }
    
    public function move($direction){
        $this->core->issueOrder($this->x, $this->y, $direction);
        list($toX, $toY) = $this->core->destination($this->x, $this->y, $direction);
        $this->toX = $toX;
        $this->toY = $toY;
    }
    
    public function setTarget($x, $y){
        $this->target = array($x, $y);
    }
    
    public function handle(MyBot $bot){
        if ($this->target && $this->x === $this->target[0] && $this->y === $this->target[1]){
            dump("Дошел до цели {$this->x}:{$this->y}");
            $this->target = null;
        }
        
        if ($this->hasTarget() === false){
            $this->targer = null;
            return false;
        }
        $core = $this->core;
        
        $d = $core->direction($this->x, $this->y, $this->target[0], $this->target[1]);
        if (empty($d)){
            dump(sprintf("EMPTY DIRECTION %d:%d -> %d:%d", $this->x, $this->y, $this->target[0], $this->target[1]));
            return false;
        }
        $direction = current($d);
        list($toX, $toY) = $core->destination($this->x, $this->y, $direction);
        if ($core->passable($toX, $toY) && !isset($this->core->busy[$toX][$toY])){
            $this->move($direction);
            $bot->busy[$toX][$toY] = $this;
            return true;
        }
        else {
            $reason = var_export(empty($this->core->busy[$toX][$toY]), true);
            if ($reason === 'false'){
                $b = $bot->busy[$toX][$toY];
                dump('Занято: ' .  $toX . ':' . $toY . ' муравьём ' . "{$b->x}:{$b->y}");
            }
            $reason .= '-';
            $reason .= var_export($core->passable($toX, $toY), true);
            dump(sprintf("IMPOSIBLE (%s) %d:%d -> %d:%d", $reason, $this->x, $this->y, $this->target[0], $this->target[1]));
        }
        return false;
    }
    
    public function hasTarget(){
        return !empty($this->target);
    }
}

class MyBot
{
    private $directions = array('n','e','s','w');
    
    private $foods = array();
    private $ants  = array();
    public  $busy  = array();
    
    private function getAnts(){
        $result = array();
        foreach ($this->ants as $row){
            foreach ($row as $ant){
                $result[] = $ant;
            }
        }
        return $result;
    }

    public function doTurn( Ants $core )
    {
        static $i = 0;
        //dump('Ход номер: ' . ++$i);
        
        $this->busy = array();
        //$this->foods = array();

        // Создание новых муравьёв
        foreach ($core->myAnts as $ant){
            if ( empty($this->ants[$ant[0]][$ant[1]])){
                $new = $this->ants[$ant[0]][$ant[1]] = new Ant($ant[0], $ant[1], $core);
                dump("CREATE ANT {$new->id}: {$ant[0]}:{$ant[1]}");
            }
        }
        
        // Запомим состояние мурашей
        foreach ($this->getAnts() as $ant){
            $this->busy[$ant->x][$ant->y] = $ant;
        }
        
        // Найдём новую еду
        foreach ($core->food as $food){
            if (isset($this->foods[$food[0]][$food[1]])){
                continue;
            }
            
            // Найдём ближайшего незанятого робота
            $nearest = PHP_INT_MAX;
            $nearestAnt = null;
            foreach ($this->getAnts() as $ant){
                /* @var $ant Ant */
                if ($ant->hasTarget()){
                    continue;
                }
                //dump("NO TARGET!!!");
                $distance = $core->distance($food[0], $food[1], $ant->x, $ant->y);
                if ($distance < $nearest){
                    //dump("NEAREST ANT!");
                    $nearestAnt = $ant;
                    $nearest = $distance;
                }
            }
            if ($nearestAnt){
                dump("Получение цели {$nearestAnt->id}: {$nearestAnt->x}:{$nearestAnt->y} -> {$food[0]}:{$food[1]}");
                $nearestAnt->setTarget($food[0], $food[1]);
                $this->foods[$food[0]][$food[1]] = $nearestAnt;
                //dump("SET TARGET FOR ANT {$nearestAnt->x}:{$nearestAnt->y} = [{$food[0]}:{$food[1]}]");
            }
        }
        
        // Выполнение целей
        foreach ($this->getAnts() as $ant){
            if ($ant->handle($this)){
                continue;
            }
//            foreach (array_reverse($this->directions) as $direction) {
//                list($x, $y) = $core->destination($ant->x, $ant->y, $direction);
//                if ($core->passable($x, $y)) {
//                    $ant->move('s');
//                    break;
//                }
//            }
        }
        
        // Перемещаем мурашей
        foreach ($this->getAnts() as $ant){
            //dump(sprintf("ANT[%d:%d] -> %d:%d", $ant->x, $ant->y, $ant->toX, $ant->toY));
            unset($this->ants[$ant->x][$ant->y]);
            $this->ants[$ant->toX][$ant->toY] = $ant;
            $ant->x = $ant->toX;
            $ant->y = $ant->toY;
        }
    }
    
}

/**
 * Don't run bot when unit-testing
 */
if( !defined('PHPUnit_MAIN_METHOD') ) {
    ini_set('error_log','/home/topas/ant/output.log');
    Ants::run( new MyBot() );
}
