<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController ;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use App\TransactionLoggerBundle\Util\TransactionLogger;
use App\Utils\Property;



class PropertyUserController extends AbstractController
{

	private $client;
	private $analytics;
    private $requestStack;
    private $transactionLogger;

	public function __construct(RequestStack $requestStack, TransactionLogger $transactionLogger) {

        $this->requestStack = $requestStack;
        $this->transactionLogger = $transactionLogger;
    }
    
	private function _initializeAnalytics()
	{
		$client = new \Google_Client();
		$client->setAccessToken($this->getSession()->get('access_token'));

 		$analytics = new \Google_Service_Analytics($client);
		$this->client = $client;

 		return $analytics;
	 	
	}//initializeAnalytics


	private function _validate() {
		$_POST = $this->parseRequest();
		$errors = [];

		if (empty($_POST['account_id'])) {
			$errors['account_error'] = 'Account ID not found';
			$account_id = '';
		}
		else {
			$account_id = $_POST['account_id'];
		}

		if (empty($_POST['property_id'])) {
			$errors['property_error'] = 'Property ID not found';
			$property_id = '';
		}
		else {
			$property_id = $_POST['property_id'];
		}

		$ret = [
			'account_id' => $account_id,
			'property_id' => $property_id
		];

		if (isset($error)) {
			$ret['error'] = $error;
		}

		return $ret;
	}//_validate


	/**
	 * @Route("/insertPropertyUserLink", name="insertPropertyUser", methods="POST")
	 * 
	 */
    public function insertPropertyUserLinkAction()
    {
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	header('Content-Type: application/json');
    	$_POST = $this->parseRequest();
    	
    	$errors = [];

    	if(empty($_POST['insertedPropertiesList'])){

    		$errors['empty'] = 'Empty Added Properties list';
    	}else{

    		$insertedPropertiesList = $_POST['insertedPropertiesList'];
    	}//endif

    	if(empty($errors)){	

    		$analytics = $this->_initializeAnalytics();
    		$ret = Property::insertPropertyUserLink($analytics, $insertedPropertiesList, $this->client);

			
		}//endif

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if (!empty($errors)) {

			$this->transactionLogger->transactionLogError(array(
				'transaction' => "Insert",
				'user' => $user->getUsername(),
				'error' => $errors,
				'logs' => $ret['logging_info']
			));

			return  new JsonResponse(array('status' => false, 'errors'=>$errors));

		}
		elseif (!empty($ret['errors'])) {

			$this->transactionLogger->transactionLogError(array(
				'transaction' => "Insert",
				'user' => $user->getUsername(),
				'error' => $ret['errors'],
				'logs' => $ret['logging_info']
			));

			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));

		}	
		elseif(!empty($ret['success'])) {		

			$this->transactionLogger->transactionLogDebug(array(
				'transaction' => "Insert",
				'user' => $user->getUsername(),
				'logs' => $ret['logging_info']
			));

			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

		
    }//insertPropertyUserLinkAction


	/**
	 * @Route("/updatePropertyUserLink", name="updatePropertyUser", methods="POST")
	 * 
	 */
    public function updatePropertyUserLinkAction()
    {
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	$_POST = $this->parseRequest();
    	header('Content-Type: application/json');

    	$errors = [];

    	if(empty($_POST['updatedPropertiesList'])){
    	
    		$errors['empty'] = 'Empty Updated Properties list';
    	
    	}else{

    		$updatedPropertiesList = $_POST['updatedPropertiesList'];
    	}//endif

    	if(empty($errors)){	

	    	$analytics = $this->_initializeAnalytics();
	    	$ret = Property::updatePropertyUserLink($analytics, $updatedPropertiesList, $this->client);

		}//endif

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if (!empty($errors)) {

			$this->transactionLogger->transactionLogError(array(
				'transaction' => "Update",
				'user' => $user->getUsername(),
				'error' => $errors,
				'logs' => $ret['logging_info']
			));

			return  new JsonResponse(array('status' => false, 'errors'=>$errors));

		}
		elseif (!empty($ret['errors'])) {

			$this->transactionLogger->transactionLogError(array(
				'transaction' => "Update",
				'user' => $user->getUsername(),
				'error' => $ret['errors'],
				'logs' => $ret['logging_info']
			));

			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));

		}	
		elseif(!empty($ret['success'])) {

			$this->transactionLogger->transactionLogDebug(array(
				'transaction' => "Update",
				'user' => $user->getUsername(),
				'logs' => $ret['logging_info']
			));

			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

	
    }//updatePropertyUserLinkAction


	/**
	 * @Route("/deletePropertyUserLink", name="deletePropertyUserLink", methods="POST")
	 * 
	 */
    public function deletePropertyUserLinkAction()
    {

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	$_POST = $this->parseRequest();
    	header('Content-Type: application/json');

    	$errors = [];

    	if(empty($_POST['deletedPropertiesList'])){
    	
    		$errors['empty'] = 'Empty Deleted Properties list';
    	
    	}else{

    		$deletedPropertiesList = $_POST['deletedPropertiesList'];
    	}//endif

    	if(empty($errors)){	

    		$analytics = $this->_initializeAnalytics(); 
			$ret = Property::deletePropertyUserLink($analytics, $deletedPropertiesList, $this->client);
			
		}//endif
			

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if (!empty($errors)) {

			$this->transactionLogger->transactionLogError(array(
				'transaction' => "Delete",
				'user' => $user->getUsername(),
				'error' => $errors,
				'logs' => $ret['logging_info']
			));

			return  new JsonResponse(array('status' => false, 'errors'=>$errors));
		}
		elseif (!empty($ret['errors'])) {
			
			$this->transactionLogger->transactionLogError(array(
				'transaction' => "Delete",
				'user' => $user->getUsername(),
				'error' => $ret['errors'],
				'logs' => $ret['logging_info']
			));

			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));
		}	
		elseif(!empty($ret['success'])) {

			$this->transactionLogger->transactionLogDebug(array(
				'transaction' => "Delete",
				'user' => $user->getUsername(),
				'logs' => $ret['logging_info']
			));

			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

    }//deletePropertyUserLinkAction



	/**
	 * @Route("/getPropertyUserLinks", name="getPropertyUserLinks", methods="POST")
	 * 
	 */
	public function getPropertyUserLinksAction()
	{

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	header('Content-Type: application/json');

    	//get the post values
    	foreach ($this->_validate() as $k => $v) {
			${$k} = $v;
		}

		if(!isset($error)){
			$errors = [];
		}

    	if(empty($errors)){	

			$analytics = $this->_initializeAnalytics();
			$ret = Property::listPropertyUserLinks($analytics, $account_id, $property_id);

    	}//endif

	   
		if (!empty($errors)) {
			return  new JsonResponse(array('status' => false, 'errors'=>$errors));
		}	
		elseif (!empty($ret['errors'])) {
			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));
		}	
		elseif(!empty($ret['success'])) {
			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

	}//getPropertyUserLinks


	/**
	 * @Route("/getPropertyList", name="getPropertyList", methods="POST")
	 * 
	 */
	public function getPropertyListAction()
	{

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	header('Content-Type: application/json');

    	$_POST = $this->parseRequest();

		$errors = [];
		

    	if(empty($_POST['account_id'])){
    	
    		$errors['account_error'] = 'Account ID not found';

    	}else{

    		$account_id = $_POST['account_id'];

    	}//endif

    	if(empty($errors)){	

   			$analytics = $this->_initializeAnalytics();
   			$ret = Property::listProperty($analytics, $account_id);
			
    	}//endif

	   
		if (!empty($errors)) {
			return  new JsonResponse(array('status' => false, 'errors'=>$errors));
		}	
		elseif (!empty($ret['errors'])) {
			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));
		}	
		elseif(!empty($ret['success'])) {
			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

	}//getPropertyListAction



    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function getCurrentRequest()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new \LogicException('There is no "current request", and it is needed to perform this action');
        }

        return $request;
    }


    /**
     * @return null|\Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private function getSession()
    {
        $session = $this->getCurrentRequest()->getSession();

        if (!$session) {
            throw new \LogicException('In order to use "state", you must have a session. Set the OAuth2Client to stateless to avoid state');
        }

        return $session;
    }

}//endofclass





