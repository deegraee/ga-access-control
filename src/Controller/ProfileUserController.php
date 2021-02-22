<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController ;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\TransactionLoggerBundle\Util\TransactionLogger;
use App\Utils\Profile;

class ProfileUserController extends AbstractController
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


	 /**
	  * @Route("/home_index", name="home_index")
	  */   
	public function indexAction()
    {

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!'); //secure
    	 
		$this->analytics = $this->_initializeAnalytics();

  		$profile = $this->getFirstProfileId($this->analytics);

  		$accountResults = $this->getAccounts($this->analytics);

     
         return $this->render('index.html.twig', array(
        	'account' => $accountResults[0],
        	'accounts' => $accountResults,

        ));

    }//index



	 /**
	  * @Route("/testapi", name="testapi")
	  */   
	public function testApiAction()
    {

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!'); //secure
     
     	$this->analytics = $this->_initializeAnalytics();

        return $this->render('testapi.html.twig', array());

    }//index



	/**
	 * @Route("/insertProfileUserLink", name="insertProfileUser", methods="POST")
	 * 
	 */
    public function insertProfileUserLinkAction()
    {
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	$_POST = $this->parseRequest();
    	header('Content-Type: application/json');
    	
    	$errors = [];

    	if(empty($_POST['insertedProfilesList'])){

    		$errors['empty'] = 'Empty Inserted Profiles list';
    	}else{

    		$insertedProfilesList = $_POST['insertedProfilesList'];
    	}//endif

    	if(empty($errors)){	

	    	$analytics = $this->_initializeAnalytics();
			$ret = Profile::insertProfileUserLink($analytics, $insertedProfilesList, $this->client);	
			
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

		
    }//insertProfileUserLinkAction


	/**
	 * @Route("/updateProfileUserLink", name="updateProfileUser", methods="POST")
	 * 
	 */
    public function updateProfileUserLinkAction()
    {
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	$_POST = $this->parseRequest();
    	header('Content-Type: application/json');

		$errors = [];

    	if(empty($_POST['updatedProfilesList'])){
    	
    		$errors['empty'] = 'Empty Updated Profiles list';
    	
    	}else{

    		$updatedProfilesList = $_POST['updatedProfilesList'];

    	}//endif

    	if(empty($errors)){	

    		$analytics = $this->_initializeAnalytics();
    		$ret = Profile::updateProfileUserLink($analytics, $updatedProfilesList, $this->client);

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
	
    }//updateProfileUserLinkAction


	/**
	 * @Route("/deleteProfileUserLink", name="deleteProfileUserLink", methods="POST")
	 * 
	 */
    public function deleteProfileUserLinkAction()
    {

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	$_POST = $this->parseRequest();
    	header('Content-Type: application/json');

    	$errors = [];

    	if(empty($_POST['deletedProfilesList'])){
    	
    		$errors['empty'] = 'Empty Deleted Profiles list';
    	
    	}else{

    		$deletedProfilesList = $_POST['deletedProfilesList'];
    	}//endif

    	if(empty($errors)){	

    		$analytics = $this->_initializeAnalytics(); 
    		$ret = Profile::deleteProfileUserLink($analytics, $deletedProfilesList, $this->client);
					
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

    }//deleteProfileUserLinkAction



	/**
	 * @Route("/getProfileUserLinks", name="getProfileUserLinks", methods="POST")
	 * 
	 */
	public function getProfileUserLinksAction()
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
    		$ret = Profile::listProfileUserLinks($analytics, $account_id, $property_id, $profile_id);

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

	}//getProfileUserLinks


	/**
	 * @Route("/getProfileList", name="getProfileList", methods="POST")
	 * 
	 */
	public function getProfileListAction()
	{

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

    	header('Content-Type: application/json');
    	
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

    	if(empty($errors)){	

    		$analytics = $this->_initializeAnalytics();
    		$ret = Profile::listProfile($analytics, $account_id, $property_id);

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

	}//getProfileListAction


	// temporary 
	//getting first profile id
	public function getFirstProfileId($analytics) 
	{
	  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

	  try{
	  	 // Get the list of accounts for the authorized user.
		  $accounts = $analytics->management_accounts->listManagementAccounts();

		  
		  if (count($accounts->getItems()) > 0) {
		    $items = $accounts->getItems();
		    $firstAccountId = $items[0]->getId();

		    // Get the list of properties for the authorized user.
		    $properties = $analytics->management_webproperties
		        ->listManagementWebproperties($firstAccountId);

		    if (count($properties->getItems()) > 0) {
		      $items = $properties->getItems();
		      $firstPropertyId = $items[0]->getId();

		      // Get the list of views (profiles) for the authorized user.
		      $profiles = $analytics->management_profiles
		          ->listManagementProfiles($firstAccountId, $firstPropertyId);

		      if (count($profiles->getItems()) > 0) {
		        $items = $profiles->getItems();

		        // Return the first view (profile) ID.
		        return $items[0]->getId();

		      } else {
		        throw new \Exception('No views (profiles) found for this user.');
		      }
		    } else {
		      throw new \Exception('No properties found for this user.');
		    }
		  } else {
		    throw new \Exception('No accounts found for this user.');
		  }
	  	}catch (\apiServiceException $e) {
		  print 'There was an Analytics API service error '
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  print 'There was a general API error '
		      . $e->getCode() . ':' . $e->getMessage();
		}


	}//getFirstProfileId


	//temporary, to be removed
	//getting accounts user has access
	public function getAccounts($analytics) 
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //secure

		try {
  			$accounts = $analytics->management_accountSummaries->listManagementAccountSummaries();
		} catch (\apiServiceException $e) {
		  print 'There was an Analytics API service error '
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  print 'There was a general API error '
		      . $e->getCode() . ':' . $e->getMessage();
		}

	
		$accountResults = array();

		//get accounts and its first property and profile id
		foreach ($accounts->getItems() as $account) {

			$property = $account->getWebProperties();
			$firstpropertyId = $property[0]->getId();
			$firstPropertyProfiles = $property[0]->getProfiles();
			$firstProfileId = $firstPropertyProfiles[0]->getId();

			$accountResults[]   = array (
				'id' => $account->getId(), 
				'name' => $account->getName(),
				'propertyId' => $firstpropertyId,
				'profileId' => $firstProfileId
			);
		}//endforeach

		return $accountResults;
	}//_getAccounts


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

		if (empty($_POST['profile_id'])) {
			$errors['profile_error'] = 'Profile ID not found';
			$profile_id = '';
		}
		else {
			$profile_id = $_POST['profile_id'];
		}

		$ret = [
			'account_id' => $account_id,
			'property_id' => $property_id,
			'profile_id' => $profile_id
		];

		if (isset($error)) {
			$ret['error'] = $error;
		}

		return $ret;
	}//_validate


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





