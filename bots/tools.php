<?php

class Tools
{

    static public $turn = 0;

    /**
     * количество клеток по вертикали
     * @var int
     */
    static public $rows = 0;

    /**
     * количество клеток по горизонтали
     * @var int
     */
    static public $cols = 0;
    static public $food = array();

    static public $defaultGoal = 0;

    static public $directionNum = array(
        'n' => -1,
        'e' => 1,
        's' => 1,
        'w' => -1,
        '0' => 0,
    );

    static public $AIM = array(
        'n' => array(-1, 0),
        'e' => array(0, 1),
        's' => array(1, 0),
        'w' => array(0, -1));
    public $RIGHT = array(
        'n' => 'e',
        'e' => 's',
        's' => 'w',
        'w' => 'n');
    public $LEFT = array(
        'n' => 'w',
        'e' => 'n',
        's' => 'e',
        'w' => 's');
    public $BEHIND = array(
        'n' => 's',
        's' => 'n',
        'e' => 'w',
        'w' => 'e'
    );

    static public function createNum($row, $col)
    {
        return $row * self::$cols + $col;
    }

    static public function createCoordinate($num)
    {
        $row = (int)($num/self::$cols);
        $col = $num - $row * self::$cols;
        return array('row' => $row, 'col' => $col);
    }

    static public function distance($row1, $col1, $row2, $col2)
    {

        $x1 = abs($row1 - $row2);
        $x = min(Tools::$rows - $x1, $x1);

        $y1 = abs($col1 - $col2);
        $y = min(Tools::$cols - $y1, $y1);

        return $x + $y;
    }

    static public function mapDistance($mapNum1, $mapNum2)
    {
        $coordinat1 = self::createCoordinate($mapNum1);
        $coordinat2 = self::createCoordinate($mapNum2);

        return self::distance($coordinat1['row'], $coordinat1['col'], $coordinat2['row'], $coordinat2['col']);
    }

    static public function destination($row, $col, $direction)
    {
        list($dRow, $dCol) = self::$AIM[$direction];
        $nRow = ($row + $dRow) % Tools::$rows;
        $nCol = ($col + $dCol) % self::$cols;
        if ($nRow < 0)
            $nRow += Tools::$rows;
        if ($nCol < 0)
            $nCol += self::$cols;
        return array($nRow, $nCol);
    }


    /**
     * @param Bot $ant
     * @param null| int $food номер точки на карте
     * @return array массив из двух направлений, куда желательно пойти боту
     * @throws Exception
     */
    static public function createDirection(Bot $ant, $food = null)
    {
        if (empty($food)){
            $food = !empty($ant->gol) ? $ant->gol : Tools::$defaultGoal;
        }
        $bot = $ant->currentCoord;

        if (empty($bot) || empty($food)){
            Tools::logger("Нету бот[$bot] или еда[$food]");
//            throw new Exception("Нету бот[$bot] или еда[$food]");
        }
        // w - запад
        // e - восток
        // n - север
        // s - юг

        $dirX = 0;
        $dirY = 0;

        $botCoordinat = Tools::createCoordinate($bot);
        $foodCoordinat = Tools::createCoordinate($food);

        // Движение по иксу
        // Выясним, в какую сторону ближе

        // Прямое расстояние
        $dirRow1 = abs($botCoordinat['row'] - $foodCoordinat['row']);
        // Зеркальное расстояние
        $dirRow2 = Tools::$rows - max($botCoordinat['row'], $foodCoordinat['row']) + min($botCoordinat['row'], $foodCoordinat['row']);

        $dirCol1 = abs($botCoordinat['col'] - $foodCoordinat['col']);
        $dirCol2 = Tools::$cols - max($botCoordinat['col'], $foodCoordinat['col']) + min($botCoordinat['col'], $foodCoordinat['col']);

        // Нормальное движение
        if ($dirRow1 < $dirRow2){
            if ($botCoordinat['row'] > $foodCoordinat['row']){
                $dirY = 'n';
            }

            if ($botCoordinat['row'] < $foodCoordinat['row']){
                $dirY = 's';
            }
        }
        // Движение через зеркало
        if ($dirRow1 > $dirRow2){
            if ($botCoordinat['row'] > $foodCoordinat['row']){
                $dirY = 's';
            }

            if ($botCoordinat['row'] < $foodCoordinat['row']){
                $dirY = 'n';
            }
        }


        // Нормальное движение
        if ($dirCol1 < $dirCol2){
            if ($botCoordinat['col'] > $foodCoordinat['col']){
                $dirX = 'w';
            }

            if ($botCoordinat['col'] < $foodCoordinat['col']){
                $dirX = 'e';
            }
        }
        // Движение через зеркало
        if ($dirCol1 > $dirCol2){
            if ($botCoordinat['col'] > $foodCoordinat['col']){
                $dirX = 'e';
            }

            if ($botCoordinat['col'] < $foodCoordinat['col']){
                $dirX = 'w';
            }
        }

        return array('row' => $dirY, 'col' => $dirX);
    }


    /**
     * @param $col int координата бота
     * @param $row int координата бота
     * @param $direction массив направлений  - ключи(col:row) - значения(w/e/n/s/0)
     * @return array массив col:row
     */
    public static function nextStep($col, $row, $direction)
    {
        // w - запад col
        // e - восток col
        // n - север row
        // s - юг row

        if ($col >= Tools::$cols || $row >= Tools::$rows || $col < 0 || $row < 0){
            throw new Exception("Попытка шагать ботом, который покинул пределы карты");
        }

        $dirNumX = Tools::$directionNum[$direction['col']];
        $dirNumY = Tools::$directionNum[$direction['row']];

        $nextCol = $col + $dirNumX;
        $nextRow = $row + $dirNumY;

//        echo "\n__________";
//        print_r("col $col");
//        echo "\n__________";
//        print_r("row $row");
//        echo "\n__________";
//        print_r($direction);
//        echo "\n__________";
//        print_r("nextCol " . $nextCol);
//        echo "\n__________";
//        print_r("nextRow " . $nextRow);
//        echo "\n__________";
//        die();

        // Если вылезли справа
        if ($nextCol >= Tools::$cols){
            $nextCol = 0;
        }
        // Если вылезли слева
        if($nextCol < 0){
            $nextCol = Tools::$cols - 1;
        }

        // Если вылезли снизу
        if ($nextRow >= Tools::$rows){
            $nextRow = 0;
        }
        // Если вылезли слева
        if($nextRow < 0){
            $nextRow = Tools::$rows -1;
        }


        return array('col' => $nextCol, 'row' => $nextRow);
    }


    public static function getSortRandomDirExcludeBadStep($direction)
    {

        if ($direction['col'] == 0) {
            $direction = $direction['row'];
        }else{
            $direction = $direction['col'];
        }

        if ($direction == 'n') {
            return array(
                'e' => 1,
                'w' => -1,
                's' => 1, // Противоположное направление в конец
            );
        }
        if ($direction == 's') {
            return array(
                'e' => 1,
                'w' => -1,
                'n' => -1, // Противоположное направление в конец
            );
        }
        if ($direction == 'e') {
            return array(
                's' => 1,
                'n' => -1,
                'w' => -1, // Противоположное направление в конец
            );
        }
        if ($direction == 'w') {
            return array(
                'n' => -1,
                's' => -1,
                'e' => 1, // Противоположное направление в конец
            );
        }
    }

    public static function logger($params = null)
    {
//        dump($params);
//        return;
        $trace = debug_backtrace();
        $location = "[{$trace[0]['line']}][{$trace[1]['function']}][{$trace[1]['class']}]";
//        print_r(debug_backtrace());
//        print_r($location);


        $handle = fopen('./../game_logs/antlog', "a+");
        fwrite($handle, print_r("\nturn:[" .Tools::$turn ."]", true));
//        fwrite($handle, "turn:[" .Tools::$turn ."]");
        if (!$params) {
//            \033[32mGOOD\033[0m
            fwrite($handle, "\033[31m===============$location=====================\033[0m\n");
        } else {
            fwrite($handle, print_r("$location", true));
            fwrite($handle, print_r($params, true));
        }
        fclose($handle);
    }

}