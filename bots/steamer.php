<?php

define('HOME', 'HOME');
define('MY_ANT', 'MY_ANT');
define('ANTS', 'ANTS');
define('DEAD', 'DEAD');
define('LAND', 'LAND');
define('FOOD', 'FOOD');
define('WATER', 'WATER');
define('UNSEEN', 'UNSEEN');

/*
 * w - вода
 * h - мураейник (0 - мой)
 * a - муравей
 * f - еда
 * d - мертвый муравей
 * 
 */

class Ants
{

    public $turns = 0;
    static public $rows = 0;
    static public $cols = 0;
    public $loadtime = 0;
    public $turntime = 0;
    public $viewradius2 = 0;
    public $attackradius2 = 0;
    public $spawnradius2 = 0;


    // Карта рельефа
    public $staticMap;

    public $map;
    public $myAnts = array();
    public $enemyAnts = array();
    public $deadAnts = array();
    static public $food = array();
    public $AIM = array(
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

    static public function issueOrder($aRow, $aCol, $direction)
    {
        printf("o %s %s %s\n", $aRow, $aCol, $direction);
        flush();
    }

    static public function createNum($row, $col)
    {
        return $row * self::$cols - (self::$cols - $col);
    }

    static public function createCoordinate($num)
    {
        $row = $num / self::$cols;
        if (is_int($row)){
            $col = self::$cols;
            return array($row, $col);
        }
        $col = $num - (int) $row * self::$cols;
        $row = (int) $row + 1;
        return array($row, $col);
    }

    public function finishTurn()
    {
        echo("go\n");
        flush();
    }

    public function setup($data)
    {
        foreach ($data as $line) {
            if (strlen($line) > 0) {
                $tokens = explode(' ', $line);
                $key = $tokens[0];
                if (property_exists($this, $key)) {
                    $this->{$key} = (int) $tokens[1];
                }
                if ($key === 'rows') {
                    self::$rows = (int) $tokens[1];
                }
                if ($key === 'cols') {
                    self::$cols = (int) $tokens[1];
                }
            }
        }

        $maxCel = $this->rows * $this->cols;
        $this->staticMap = array_pad(array(1), $maxCel -1, UNSEEN);

        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                // Закрашивает все клетки карты землей
                $this->map[$row][$col] = LAND;
            }
        }
    }

    /** not tested */
    public function update($data)
    {
        // Почистим лист ботов
        Bots::getInstance()->clear();
//        Ants::logger($this->rows);
//        Ants::logger($this->cols);

        // clear ant and food data
        foreach ($this->myAnts as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        $this->myAnts = array();

        foreach ($this->enemyAnts as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        $this->enemyAnts = array();

        foreach ($this->deadAnts as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        $this->deadAnts = array();

        foreach (self::$food as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        self::$food = array();

        # update map and create new ant and food lists
        foreach ($data as $line) {
            if (strlen($line) > 0) {
                $tokens = explode(' ', $line);

                if (count($tokens) >= 3) {
                    $row = (int) $tokens[1];
                    $col = (int) $tokens[2];

                    $staticMapKey = self::createNum($row, $col);

//                    self::logger($this->viewradius2 . "\n");
                    // Нашли муравья
                    if ($tokens[0] == 'a') {
                        $owner = (int) $tokens[3];
                        $this->map[$row][$col] = $owner;
                        $this->staticMap[$staticMapKey] = LAND;;
                        if ($owner === 0) {
                            // Засунем бота в лист
                            Bots::getInstance()->add($staticMapKey);
                            $this->myAnts [] = array($row, $col);
                        } else {
                            $this->enemyAnts [] = array($row, $col);
                        }
                    // Нашли еду
                    } elseif ($tokens[0] == 'f') {
                        $this->map[$row][$col] = FOOD;
                        $this->staticMap[$staticMapKey] = LAND;
                        self::$food [$staticMapKey] = array($row, $col);
                     // Нашли воду
                    } elseif ($tokens[0] == 'w') {
                        $this->map[$row][$col] = WATER;
                        $this->staticMap[$staticMapKey] = WATER;
                    // Нашли смерть
                    } elseif ($tokens[0] == 'd') {
                        $this->staticMap[$staticMapKey] = LAND;
                        $this->map[$row][$col] = DEAD;
                        $this->deadAnts [] = array($row, $col);
                    } elseif ($tokens[0] == 'h') {
                        $this->staticMap[$staticMapKey] = HOME;
                    }
                }
            }
        }
//        self::logger($this->staticMap);
//        self::logger();
    }

    public function passable($row, $col)
    {
        return $this->map[$row][$col] != WATER && $this->map[$row][$col] != MY_ANTS;
    }

    public function unoccupied($row, $col)
    {
        return in_array($this->map[$row][$col], array(LAND, DEAD, FOOD));
    }

    public function destination($row, $col, $direction)
    {
        list($dRow, $dCol) = $this->AIM[$direction];
        $nRow = ($row + $dRow) % $this->rows;
        $nCol = ($col + $dCol) % $this->cols;
        if ($nRow < 0)
            $nRow += $this->rows;
        if ($nCol < 0)
            $nCol += $this->cols;
        return array($nRow, $nCol);
    }

    static public function distance($row1, $col1, $row2, $col2)
    {
        $dRow = abs($row1 - $row2);
        $dCol = abs($col1 - $col2);

        $dRow = min($dRow, self::rows - $dRow);
        $dCol = min($dCol, self::cols - $dCol);

        return sqrt($dRow * $dRow + $dCol * $dCol);
    }


    static public function mapDistance($mapNum1, $mapNum2)
    {
        return round(abs($mapNum1 - $mapNum2) / self::$rows);
    }

    public function direction($row1, $col1, $row2, $col2)
    {
        $d = array();
        $row1 = $row1 % $this->rows;
        $row2 = $row2 % $this->rows;
        $col1 = $col1 % $this->cols;
        $col2 = $col2 % $this->cols;

        if ($row1 < $row2) {
            if ($row2 - $row1 >= $this->rows / 2) {
                $d [] = 'n';
            }
            if ($row2 - $row1 <= $this->rows / 2) {
                $d [] = 's';
            }
        } elseif ($row2 < $row1) {
            if ($row1 - $row2 >= $this->rows / 2) {
                $d [] = 's';
            }
            if ($row1 - $row2 <= $this->rows / 2) {
                $d [] = 'n';
            }
        }
        if ($col1 < $col2) {
            if ($col2 - $col1 >= $this->cols / 2) {
                $d [] = 'w';
            }
            if ($col2 - $col1 <= $this->cols / 2) {
                $d [] = 'e';
            }
        } elseif ($col2 < $col1) {
            if ($col1 - $col2 >= $this->cols / 2) {
                $d [] = 'e';
            }
            if ($col1 - $col2 <= $this->cols / 2) {
                $d [] = 'w';
            }
        }
        return $d;
    }

    public static function logger($params = null)
    {
        $handle = fopen('./../game_logs/antlog', "a+");
        if (!$params) {
            fwrite($handle, print_r("==============================\n", true));
        } else {
            fwrite($handle, print_r($params, true));
        }
        fclose($handle);
    }

    public static function run($bot)
    {
        $ants = new Ants();
        $inputData = array();

        while (true) {
            $current_line = fgets(STDIN, 1024);
            $current_line = trim($current_line);
            if ($current_line === 'ready') {
                // Выполняется единожды, при первой загрузке карты
                $ants->setup($inputData);
                $ants->finishTurn();
                $inputData = array();
            } elseif ($current_line === 'go') {
                // Выполняется каждый раз, после окончания передачи порции параметров текущего шага.
                $ants->update($inputData);

                $bot->doTurn($ants);
                $ants->finishTurn();
                $inputData = array();
            } else {
                // Массив всех входных данных
                $inputData [] = $current_line;
            }
        }
    }

}
