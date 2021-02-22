<?php

namespace App\TransactionLoggerBundle\Util;


use Psr\Log\LoggerInterface;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TransactionLogger  
{

    private $transactionLogger;
 
    public function __construct(LoggerInterface $transactionLogger) {
        $this->transactionLogger = $transactionLogger;
    }


    public function transactionLog(array $logdata)
    {
         $this->customLoggerInfo($data);
    }//transactionLog



    public function transactionLogDebug(array $logdata)
    {
        $data = [
            'Transaction' => $logdata['transaction'],
            'User' => $logdata['user'],
            'Logs' => $logdata['logs']
        ];

        $this->customLoggerDebug($data);
    }

    public function transactionLogError(array $logdata)
    {
        $data = [
            'Transaction' => $logdata['transaction'],
            'User' => $logdata['user'],
            'Error' => $logdata['error'],
            'Logs' => $logdata['logs']
        ];

        $this->customLoggerError($data);
    }

    /**
     * @param array $info
     */
    private function customLoggerInfo(array $info)
    {
        $this->transactionLogger->info($info);
    }

    /**
     * @param array $data
     */
    private function customLoggerDebug(array $data)
    {
        $this->transactionLogger->debug('Transaction Log', $data);
    }

    /**
     * @param array $data
     */
    private function customLoggerError(array $data)
    {
        $this->transactionLogger->error('ERROR FOUND ', $data);
    }


}