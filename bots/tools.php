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

    static public $defaultGoal = null;

    static public $direction = array('n' ,'e' ,'s' ,'w');

    static public $directionNum = array(
//        'n' => 1,
//        'e' => 1,
//        's' => -1,
//        'w' => -1,
//        '0' => 0,
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

    /**
     *
     * @param int $row
     * @param int $col
     * @return int
     */
    static public function createNum($row, $col)
    {
        return $row * self::$cols + $col;
    }

    /**
     *
     * @param int $num
     * @return type array ['row' => 123, 'col' => 567]
     */
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

    /**
     *
     * @param int $mapNum1
     * @param int $mapNum2
     * @return array [row1, col1, row2, col2]
     */
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

// ================
// Пробуем вклинитяс сюда с сонаром
$sonar = Tools::sonar($botCoordinat, $foodCoordinat);

if ($sonar) {
    $foodCoordinat = $sonar;
}

// ================

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
     * @return array массив col:row | string w,n, e, s
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

        if (!is_array($direction)) {
            if ($direction == 'w' || $direction == 'e') {
                $direction = array('col' => $direction, 'row' => 0);
            }elseif($direction == 'n' || $direction == 's'){
                $direction = array('col' => 0, 'row' => $direction);
            }else{
                $direction = array('col' => 0, 'row' => 0);
            }
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
            Tools::logger('Если вылезли справа вернем ' .$nextCol);
        }
        // Если вылезли слева
        if($nextCol < 0){
            $nextCol = Tools::$cols - 1;
            Tools::logger('Если вылезли слева вернем ' .$nextCol);
        }

        // Если вылезли снизу
        if ($nextRow >= Tools::$rows){
            $nextRow = 0;
            Tools::logger('Если вылезли снизу вернем ' .$nextRow. ' $nextRow >= Tools::$rows' . "$nextRow >= " . Tools::$rows);
        }
        // Если вылезли сверху
        if($nextRow < 0){
            $nextRow = Tools::$rows -1;
            Tools::logger('Если вылезли сверху вернем ' .$nextRow. ' $nextRow < 0 .  ' . "$nextRow < 0 ");
        }

        if ($nextRow >= 83 || $nextCol >= 83) {
            Tools::logger("nextRow = $nextRow || nextCol = $nextCol");
        }

        return array('col' => $nextCol, 'row' => $nextRow);
    }


    public static function getSortRandomDirExcludeBadStep($direction, $prevDirection)
    {

        if ($direction['col'] === 0) {
            $direction = $direction['row'];
        }else{
            $direction = $direction['col'];
        }

        if ($prevDirection['col'] === 0) {
            $prevDirection = $prevDirection['row'];
        }else{
            $prevDirection = $prevDirection['col'];
        }

        switch ($direction) {
            case 'n':
                $result = array(
                    'e' => 1,
                    'w' => -1,
                    's' => 1, // Противоположное направление в конец
                );
                break;
            case 's':
                $result = array(
                    'e' => 1,
                    'w' => -1,
                    'n' => -1, // Противоположное направление в конец
                );
            case 'e':
                $result = array(
                    's' => 1,
                    'n' => -1,
                    'w' => -1, // Противоположное направление в конец
                );
            case 'w':
                $result = array(
                    'n' => -1,
                    's' => -1,
                    'e' => 1, // Противоположное направление в конец
                );

            default:
                break;
        }
        // w - запад col
        // e - восток col
        // n - север row
        // s - юг row

//        Tools::logger('Пытаюсь удалить  $prevDirection:' . $prevDirection . ' && $result: ' .  print_r($result, true));
        if ($prevDirection && isset($result[$prevDirection])) {
            $k = $result[$prevDirection];
//            Tools::logger('Удалил предыдущее направление : ' . $prevDirection);
            unset($result[$prevDirection]);
            $result[$prevDirection] = $k;
//            Tools::logger('Попытка удалась $prevDirection:' . $prevDirection . ' && $result: ' .  print_r($result, true));
        }

        return $result;
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

    public static function getNeighbor($pointNum)
    {
        $pointColRow = Tools::createCoordinate($pointNum);
        $neighbors = [];

        foreach (Tools::$direction as $dir) {
            $nextColRow = Tools::nextStep($pointColRow['col'], $pointColRow['row'], $dir);
            $nextPoint = Tools::createNum($nextColRow['row'], $nextColRow['col']);

            if (Steamer::$staticMap[$nextPoint] !== WATER) {
                $neighbors[] = $nextPoint;
            }
        }

        return $neighbors;
    }


    // Находим точку пересечения доступных областей бота и цели
    static public function getIntersectPoints($antNum, $golNum)
    {
        $i = 0;

        // Полные карты всех координат
        $antMap = [
            $antNum => null,
        ];
        $golMap = [
            $golNum => null,
        ];

        // Координаты по шагам
        $antMapStep = [];
        $golMapStep = [];

        while (!array_intersect_key($antMap, $golMap) && $i++ < 50) {
            $neighborsAnt = []; // Соседи мураша (на данном этапе обхода)
            $neighborsGol = []; // Соседи еды (на данном этапе обхода)

            // Ищем координаты, у которых еще нет соседей
            foreach ($antMap as $key => $value) {
                if ($value === null) {
                    $neighbors = Tools::getNeighbor($key);
                    $antMap[$key] = $neighbors;
                    $neighborsAnt = array_merge($neighborsAnt, $neighbors);
                }
            }

            // Если цель находится на соседней клетке
            // по горизонтали/вертикали - тогда можем сразу вернуть результ
            if ($i == 1 && array_search($golNum, $neighborsAnt)) {
//                dd($neighborsAnt);
                return $golNum;
            }

            foreach ($golMap as $key => $value) {
                if ($value === null) {
                    $neighbors = Tools::getNeighbor($key);
                    $golMap[$key] = $neighbors;
                    $neighborsGol = array_merge($neighborsGol, $neighbors);
                }
            }

            // Добавим новых соседей в общие массивы
            foreach ($neighborsAnt as $key => $value) {
                if (!isset($antMap[$value])) {
                    $antMap[$value] = null;
                }
            }

            foreach ($neighborsGol as $key => $value) {
                if (!isset($golMap[$value])) {
                    $golMap[$value] = null;
                }
            }
            $antMapStep[$i] = array_flip($neighborsAnt);
            $golMapStep[$i] = array_flip($neighborsGol);
        }


        // Соседние клетки по диагонали (вертикаль и горизонталь уже отрезали выше)
        // Возврящаем первую попавшуюся
        if ($i == 1) {
            $intersectPonts = array_intersect_key($antMap, $golMap);
            return key($intersectPonts);
        }

      $intersectPonts = array_intersect_key($antMap, $golMap);

      if (!$intersectPonts) {
          return null;
      }

      return array_keys($intersectPonts);
    }

    public static function sonar($antMap, $golMap)
    {
        // Пока возвращается массив - ищем куда идти.
        // Как только нашли - возврящаем.
        $i = 0;
        do {
            $i++;
            $intersectPonts = Tools::getIntersectPoints($antMap, $golMap);
//            dump($intersectPonts);
            if (is_array($intersectPonts)) {
                $golMap = reset($intersectPonts);
            }
        } while (is_array($intersectPonts) && $i < 50);

        return $intersectPonts;
    }
}
