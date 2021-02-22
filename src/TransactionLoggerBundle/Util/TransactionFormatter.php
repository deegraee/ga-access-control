<?php


namespace App\TransactionLoggerBundle\Util;
 
use Monolog\Formatter\FormatterInterface;
 
class TransactionFormatter implements FormatterInterface
{
    public function format(array $record)
    {
 
        return json_encode([
            'message' => $record['message'],
            'timestamp' => date(DATE_ISO8601),
            'test_data' => $record['context']['test_data'],
            'log_data' => $record['context']['log_data']
        ]).PHP_EOL;

    }
 
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }
 
        return $records;
    }

}


?>