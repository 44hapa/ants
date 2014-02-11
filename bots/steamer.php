<?php

define('MY_ANT', 0);
define('ANTS', 1);
define('DEAD', -1);
define('LAND', -2);
define('FOOD', -3);
define('WATER', -4);
define('UNSEEN', -5);

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
    public $rows = 0;
    public $cols = 0;
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
    public $food = array();

    public function issueOrder($aRow, $aCol, $direction)
    {
        printf("o %s %s %s\n", $aRow, $aCol, $direction);
        flush();
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

        foreach ($this->food as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        $this->food = array();

        # update map and create new ant and food lists
        foreach ($data as $line) {
            if (strlen($line) > 0) {
                $tokens = explode(' ', $line);

                if (count($tokens) >= 3) {
                    $row = (int) $tokens[1];
                    $col = (int) $tokens[2];

                    $staticMapKey = $row * $this->cols - ($this->cols - $col);

                    if ($tokens[0] == 'a') {
                        $owner = (int) $tokens[3];
                        $this->map[$row][$col] = $owner;
                        if ($owner === 0) {
                            $this->myAnts [] = array($row, $col);
                            $this->staticMap[$staticMapKey] = MY_ANT;
                        } else {
                            $this->enemyAnts [] = array($row, $col);
                            $this->staticMap[$staticMapKey] = ANTS;
                        }
                    } elseif ($tokens[0] == 'f') {
                        $this->map[$row][$col] = FOOD;
                        $this->staticMap[$staticMapKey] = FOOD;
                        $this->food [] = array($row, $col);
                    } elseif ($tokens[0] == 'w') {
                        $this->map[$row][$col] = WATER;
                        $this->staticMap[$staticMapKey] = WATER;
                    } elseif ($tokens[0] == 'd') {
                        $this->staticMap[$staticMapKey] = DEAD;
                        $this->map[$row][$col] = DEAD;
                        $this->deadAnts [] = array($row, $col);
                    }
                }
            }
        }
        self::logger($this->staticMap);
        self::logger();
    }

    public function passable($row, $col)
    {
        return $this->map[$row][$col] > WATER;
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

    public function distance($row1, $col1, $row2, $col2)
    {
        $dRow = abs($row1 - $row2);
        $dCol = abs($col1 - $col2);

        $dRow = min($dRow, $this->rows - $dRow);
        $dCol = min($dCol, $this->cols - $dCol);

        return sqrt($dRow * $dRow + $dCol * $dCol);
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
            fwrite($handle, print_r("\n==============================\n", true));
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
