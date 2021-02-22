<?php 

// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
       $exception = $event->getException();
       $message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

       $response = new JsonResponse();
       $response->setContent($message);

        if ($exception instanceof \Google_Service_Exception) { //for authError credentials
         
           if($exception->getCode() == '401'){               
                $response = new RedirectResponse('/login');
                $event->setResponse($response);
           }elseif($exception->getCode() == '403'){

           }else{
              $event->setResponse($response);
           }
        }else if($exception instanceof \InvalidArgumentException) { //for setAccesstoken at initialize                   
                // $response = new RedirectResponse('/login');
                // $event->setResponse($response);         
        }//endif
       
    }//onKernelException
}



?>