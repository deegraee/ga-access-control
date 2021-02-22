<?php

namespace App\Controller;


use App\Utils\Account;

use Symfony\Component\HttpFoundation\Request;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController ;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use App\TransactionLoggerBundle\Util\TransactionLogger;

class AccountUserController extends AbstractController
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

		$ret = [
			'account_id' => $account_id
		];

		if (isset($error)) {
			$ret['error'] = $error;
		}

		return $ret;

	}//_validate


	/**
	 * @Route("/insertAccountUserLink", name="insertAccountUser", methods="POST")
	 * 
	 */
    public function insertAccountUserLinkAction()
    {
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	$_POST = $this->parseRequest();
    	header('Content-Type: application/json');
    	
    	$errors = [];

    	if(empty($_POST['insertedAccountList'])){

    		$errors['empty'] = 'Empty Inserted Accounts list';
    	}else{

    		$insertedAccountList = $_POST['insertedAccountList'];
    	}//endif

    	if(empty($errors)){	

	    	$analytics = $this->_initializeAnalytics();
	    	$ret = Account::insertAccountUserLink($analytics, $insertedAccountList, $this->client);	

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
		
    }//insertAccountUserLinkAction


	/**
	 * @Route("/updateAccountUserLink", name="updateAccountUser", methods="POST")
	 * 
	 */
    public function updateAccountUserLinkAction()
    {
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	$_POST = $this->parseRequest();
    	header('Content-Type: application/json');

		$errors = [];

    	if(empty($_POST['updatedAccountList'])){
    	
    		$errors['empty'] = 'Empty Updated Accounts list';
    	
    	}else{

    		$updatedAccountList = $_POST['updatedAccountList'];
    	}//endif


    	if(empty($errors)){	

	    	$analytics = $this->_initializeAnalytics(); 
    		$ret = Account::updateAccountUserLink($analytics, $updatedAccountList, $this->client);	

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
	
    }//updateAccountUserLinkAction


	/**
	 * @Route("/deleteAccountUserLink", name="deleteAccountUserLink", methods="POST")
	 * 
	 */
    public function deleteAccountUserLinkAction()
    {

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	$_POST = $this->parseRequest();
    	header('Content-Type: application/json');
		
		$errors = []; 
		 
	
    	if(empty($_POST['deletedAccountsList'])){
    	
    		$errors['empty'] = 'Empty Deleted Accounts list';
    	
    	}else{

    		$deletedAccountsList = $_POST['deletedAccountsList'];
    	}//endif

    	if(empty($errors)){	
	
    		$analytics = $this->_initializeAnalytics(); 
    		$ret = Account::deleteAccountUserLink($analytics, $deletedAccountsList, $this->client);
			
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

    }//deleteAccountUserLinkAction



	/**
	 * @Route("/getAccountUserLinks", name="getAccountUserLinks", methods="POST")
	 * 
	 */
	public function getAccountUserLinksAction()
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

    		$ret = Account::listAccountUserLinks($analytics, $account_id);

    	}//endif

	   
		if (!empty($ret['errors'])) {
			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));

		}elseif(!empty($errors)){
			return  new JsonResponse(array('status' => false, 'errors'=>$errors));

		}elseif(!empty($ret['success'])) {
			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

	}//getAccountUserLinksAction


	/**
	 * @Route("/getAccountList", name="getAccountList", methods="POST")
	 * 
	 */
	public function getAccountListAction()
	{

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	header('Content-Type: application/json');
  
		$analytics = $this->_initializeAnalytics();

		$ret = Account::listAccount($analytics);
			
	   
		if (!empty($ret['errors'])) {
			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));
		}elseif(!empty($ret['success'])) {
			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

	}//getAccountListAction


	/**
	 * @Route("/getAccountSummaries", name="getAccountSummaries", methods={"GET","POST"})
	 * 
	 */
	public function getAccountSummariesAction()
	{

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	header('Content-Type: application/json');
  
		$analytics = $this->_initializeAnalytics();
			
		$ret = Account::listAccountSummaries($analytics);

		
		if (!empty($ret['errors'])) {
			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));
		}	
		elseif(!empty($ret['success'])) {
			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

	}//getAccountSummariesAction



	/**
	 * @Route("/getAccessLevelSummaries", name="getAccessLevelSummaries", methods={"GET","POST"})
	 * 
	 */
	public function getAccessLevelSummariesAction()
	{

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	header('Content-Type: application/json');
  
		$analytics = $this->_initializeAnalytics();
			
		$ret = Account::listAccessLevelSummaries($analytics);

		
		if (!empty($ret['errors'])) {
			return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));
		}	
		elseif(!empty($ret['success'])) {
			return new JsonResponse(array('status' => true, 'success'=>$ret['success']));	
		}//endif

	}//getAccountSummariesAction



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





