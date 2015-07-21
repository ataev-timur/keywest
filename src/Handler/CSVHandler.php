<?php
/**
 * Created by PhpStorm.
 * User: Timur
 * Date: 20.07.2015
 * Time: 20:52
 */

namespace Ataev\KeyWest\Handler;

use League\Csv\Reader;
use League\Csv\Writer;

class CSVHandler
{
    private $reader;
    private $writer;
    private $offset;
    private $pathToReadFile;
    private $pathToWriteFile;
    private $readableData  = [];
    private $formattedData = [];

    /**
     * Конструктор обработчика csv файла.
     * @param string $pathToReadFile  Путь к файлу чтения
     * @param string $pathToWriteFile Путь к файлу записи
     * @param int $readingOffset Отступ от начала файла чтения
     */
    public function __construct($pathToReadFile, $pathToWriteFile, $readingOffset = 0)
    {
        $this->pathToReadFile  = $pathToReadFile;
        $this->pathToWriteFile = $pathToWriteFile;
        $this->offset = (int)$readingOffset;
    }

    /**
     * Обработка данных и создание нового файла.
     */
    public function handleData()
    {
        try {
            $this->reader = Reader::createFromPath($this->pathToReadFile);
            $this->writer = Writer::createFromPath($this->pathToWriteFile);
            $this->setReadableData();
            $this->setFormattedData();
            $this->writer->insertAll($this->generateInsertData());
            echo 'Data was handled and written to file '. $this->pathToWriteFile, PHP_EOL;
        } catch(\Exception $exception) {
            echo $exception->getMessage(), PHP_EOL;
        }
    }

    /**
     * Установить путь к файлу для чтения
     * @param string $pathToReadFile
     */
    public function setPathToReadFile($pathToReadFile)
    {
        $this->pathToReadFile = $pathToReadFile;
    }

    /**
     * Установить путь к файлу для записи
     * @param string $pathToWriteFile
     */
    public function setPathToWriteFile($pathToWriteFile)
    {
        $this->pathToWriteFile = $pathToWriteFile;
    }

    /**
     * Преобразовать данные в удобочитаемый вид.
     */
    private function setReadableData()
    {
        $this->reader->setOffset($this->offset);

        $this->readableData = $this->reader->query(function ($row) {
            preg_match("/\[(.*?)\]/", $row[0], $matches);
            if ($matches) {
                $result['words'] = explode(' ', $matches[1]);
                $result['exact'] = true;
            } else {
                $result['words'] = explode(' ', $row[0]);
                $result['exact'] = false;
            }
            $result['count'] = $row[1];
            return $result;
        });
    }

    /**
     * Получить массив данных для чтения.
     */
    public function getReadableData()
    {
        return $this->readableData;
    }

    /**
     * Отформатировать данные для чтения преобразуйте в новый вид.
     */
    private function setFormattedData()
    {
        foreach ($this->readableData as $row) {
            foreach ($row['words'] as $word) {
                if (!isset($this->formattedData[$word])) {
                    $this->formattedData[$word]['count'] = 1;
                } else {
                    $this->formattedData[$word]['count']++;
                }
                $this->formattedData[$word]['totalBroad'] += $row['exact'] ? 0 : $row['count'];
                $this->formattedData[$word]['totalExact'] += $row['exact'] ? $row['count'] : 0;
            }
        }
    }

    /**
     * Получить массив отформатированных данных.
     */
    public function getFormattedData()
    {
        return $this->formattedData;
    }

    public function generateInsertData()
    {
        yield ['Word','Count','Total Broad Searches','Total Exact Searches'];
        foreach ($this->formattedData as $word => $data) {
            yield [$word, $data['count'], $data['totalBroad'], $data['totalExact']];
        }
    }
}