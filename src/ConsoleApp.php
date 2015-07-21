<?php
/**
 * Created by PhpStorm.
 * User: Timur
 * Date: 21.07.2015
 * Time: 0:11
 */

namespace Ataev\KeyWest;


use Ataev\KeyWest\Handler\CSVHandler;

class ConsoleApp
{
    public static function run()
    {
        $data = [];
        while ($line = trim(fgets(STDIN))) {
            try {
                if ($line == 'exit')
                    break;
                else if ($line == 'help') {
                    echo '1. Enter path to reading file.', PHP_EOL;
                    echo '2. Enter path to writing file.', PHP_EOL;
                } else {
                    $data[] = $line;
                    if (count($data) == 2) {
                        $handler = new CSVHandler($data[0], $data[1], 1);
                        $handler->handleData();
                        break;
                    }
                }
            } catch (\Exception $exception) {
                echo $exception->getMessage(), PHP_EOL;
            }
        }
    }
}