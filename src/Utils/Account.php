<?php

namespace App\Utils;

use App\TransactionLoggerBundle\Util\TransactionLogger;

class Account{

   
	public static function listAccountSummaries(\Google_Service_Analytics $analytics){

		$errors = [];
		$success = [];

		try {
			
		  	$accounts = $analytics->management_accountSummaries->listManagementAccountSummaries();

			$accountList = array();

			foreach ($accounts->getItems() as $account) {
		
				$account_id = $account->getId();
				$account_name   = $account->getName();
				$account_kind = $account->getKind();
				
				$propertyList = array();

				foreach ($account->getWebProperties() as $property) {
					$property_id = $property->getId();
					$property_name   = $property->getName();
					$property_kind = $property->getKind();
					$property_level   = $property->getLevel();
					$property_url = $property->getWebsiteUrl();
					$property_internal_id = $property->getInternalWebPropertyId();


					$profileList = array();

					foreach ($property->getProfiles() as $profile) {
						$profile_id = $profile->getId();
						$profile_name   = $profile->getName();
						$profile_kind = $profile->getKind();
						$profile_type   = $profile->getType();

						$profileList[]   = array (
							'profile_id' => $profile_id, 
							'profile_name' => $profile_name,
							'profile_kind' => $profile_kind,
							'profile_type' => $profile_type
						);

					}//profileforeach

					$propertyList[]   = array (
						'property_id' => $property_id, 
						'property_name' => $property_name,
						'property_kind' => $property_kind,
						'property_level' => $property_level,
						'property_url' => $property_url,
						'property_internal_id' => $property_internal_id,
						'profiles' => $profileList
					);

				}//propertyforeach

				$accountList[]   = array (
					'account_id' => $account_id, 
					'account_name' => $account_name,
					'account_kind' => $account_kind,
					'properties'  =>  $propertyList

				);
			}//endforeach

			$success =  $accountList;

		  
		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
				$errors['Exception'] = 'There was an error.'  . " Code : "
			      . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch


		$ret = array(
			'success' => $success,
			'errors' => $errors
		);

		return $ret;

	}//listAccountSummaries



	public static function listAccessLevelSummaries(\Google_Service_Analytics $analytics){

		$errors = [];
		$success = [];

		try {
			
		  	$accounts = $analytics->management_accounts->listManagementAccounts();

			$accountList = array();

			foreach ($accounts->getItems() as $account) {
		
				$account_id = $account->getId();
				$account_name   = $account->getName();
				$account_created = $account->getCreated();
				$account_updated = $account->getUpdated();
				
				$propertyList = array();

				$properties  = $analytics->management_webproperties->listManagementWebproperties($account_id);

				foreach ($properties->getItems() as $property) {

					$property_id = $property->getId();
					$property_name   = $property->getName();
					$property_url = $property->getWebsiteUrl();
					$property_created   = $property->getCreated();
					$property_updated = $property->getUpdated();

					$profileList = array();

					$profiles  = $analytics->management_profiles->listManagementProfiles($account_id, $property_id);

					foreach ($profiles->getItems() as $profile) {

						$profile_id = $profile->getId();
						$profile_name   = $profile->getName();
						$profile_created = $profile->getCreated();
						$profile_updated = $profile->getUpdated();

						$profileList[]   = array (
							'profile_id' => $profile_id, 
							'profile_name' => $profile_name,
							'profile_created' => $profile_created,
							'profile_updated' => $profile_updated
						);

					}//profileforeach

					$propertyList[]   = array (
						'property_id' => $property_id, 
						'property_name' => $property_name,
						'property_url' => $property_url,
						'property_created' => $property_created,
						'property_updated' => $property_updated,
						'profiles' => $profileList
					);

				}//propertyforeach

				$accountList[]   = array (
					'account_id' => $account_id, 
					'account_name' => $account_name,
					'account_created' => $account_created,
					'account_updated' => $account_updated,
					'properties'  =>  $propertyList

				);
			}//endforeach

			$success =  $accountList;

		  
		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
				$errors['Exception'] = 'There was an error.'  . " Code : "
			      . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch


		$ret = array(
			'success' => $success,
			'errors' => $errors
		);

		return $ret;

	}//listAccessLevelSummaries


	
	public static function listAccount(\Google_Service_Analytics $analytics){
		
		$errors = [];
		$success = [];

		try {

			$accounts = $analytics->management_accounts->listManagementAccounts();

			$userAccounts = array();

			foreach ($accounts->getItems() as $account) {
		
				$account_id = $account->getId();
				$account_name   = $account->getName();
				$account_created = $account->getCreated();
				$account_updated = $account->getUpdated();

				$userAccounts[]   = array (
					'account_id' => $account_id, 
					'account_name' => $account_name,
					'account_created' => $account_created,
					'account_updated' => $account_updated
				);
			}//endforeach

			$success =  $userAccounts;

		  
		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
				$errors['Exception'] = 'There was an error.'  . " Code : "
			      . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch


		$ret = array(
			'success' => $success,
			'errors' => $errors
		);

		return $ret;

	}//listAccount



	public static function listAccountUserLinks(\Google_Service_Analytics $analytics, $account_id){

		$errors = [];
		$success = [];
	
	    try {
		
			
		  	$accountUserlinks  = $analytics->management_accountUserLinks
		      ->listManagementAccountUserLinks($account_id);

			$userAccountLinks = array();

			foreach ($accountUserlinks->getItems() as $accountUserLink) {
				$entity = $accountUserLink->getEntity();
  				$accountRef = $entity->getAccountRef();
  				$userRef = $accountUserLink->getUserRef();
  				$permissions = $accountUserLink->getPermissions();
		
				$account_user_link_id   = $accountUserLink->getId();
				$account_user_link_kind = $accountUserLink->getKind();

				$account_id   = $accountRef->getId();
				$account_name = $accountRef->getName();
				$account_kind = $accountRef->getKind();

				$permissions_local     = $permissions->getLocal();
				$permissions_effective = $permissions->getEffective();

				$user_id    = $userRef->getId();
				$user_kind  = $userRef->getKind();
				$user_email = $userRef->getEmail();

				$userAccountLinks[]   = array (
					'email' => $user_email, 
					'user_link_id' => $account_user_link_id,
					'permissions_local' => $permissions_local,
					'permissions_effective' => $permissions_effective
				);
			}//endforeach

			$success =  $userAccountLinks;

		  
		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
				$errors['Exception'] = 'There was an error.'  . " Code : "
			      . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch

		
		$ret = array(
			'success' => $success,
			'errors' => $errors
		);

		return $ret;

	}//listAccountUserLinks



	public static function insertAccountUserLink(\Google_Service_Analytics $analytics, $insertedAccountList, \Google_Client $client){
		
		$errors = [];
		$success = [];

		$logging_info = [];

		try {

		  	$client->setUseBatch(true);
		    $batch = new \Google_Http_Batch($client);
			 
			foreach($insertedAccountList as $key => $insertAccount){

	    		// Create the user reference.
				$userRef = new \Google_Service_Analytics_UserRef();
				$userRef->setEmail($insertAccount['email']);

	    		// Create the permissions object.
				$permissions = new \Google_Service_Analytics_EntityUserLinkPermissions();
				$permissions->setLocal($insertAccount['permissions']);

				$link = new \Google_Service_Analytics_EntityUserLink();
				$link->setPermissions($permissions);
				$link->setUserRef($userRef);

				$request = $analytics->management_accountUserLinks->insert($insertAccount['account_id'], $link);

				$batch->add($request);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $insertAccount['account_id']
											),
						'email' => $insertAccount['email'],
						'permissions' => $insertAccount['permissions'],
				);

	
			}//endforeach

			//execute batch
			$results = $batch->execute();

	    	foreach ($results as $key => $result) {

		        if ($result instanceof \Google_Service_Exception) {	          
		           $errors['Google_Service_Exception'][$key] = $result;
		        } else {
		           $success['results'][$key] = $result = $result->id;
		        }

		        $logging_info['result'][$key] = $result;

		    }//endforeach

		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on email :'. $insertAccount['email']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on email :'. $insertAccount['email']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
			$errors['Exception'] = 'There was an error on email :'. $insertAccount['email']
				. " Code : " . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch


		$ret = array(
			'success' => $success,
			'errors' => $errors, 
			'logging_info' => $logging_info
		);

		return $ret;


	}//insertAccountUserLink



	public static function deleteAccountUserLink(\Google_Service_Analytics $analytics, $deletedAccountsList, \Google_Client $client){

		$errors = [];
		$success = [];

		$logging_info = [];

		try {
    	
		    $client->setUseBatch(true);
		    $batch = new \Google_Http_Batch($client);
			
	    	foreach($deletedAccountsList as $deleteAccount){
	    		$link = $analytics->management_accountUserLinks->delete($deleteAccount['account_id'], $deleteAccount['link_id']);

	    		$batch->add($link);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $deleteAccount['account_id'],
											),
						'email' => $deleteAccount['email'],
						'link_id' => $deleteAccount['link_id']
				);


	    	}//endforeach

	    	$results = $batch->execute();

	    	foreach ($results as $key => $result) {
		        if ($result instanceof \Google_Service_Exception) {	          
		           $errors['Google_Service_Exception'][$key] = $result;
		        } else {
		           $success['results'][$key] =  $result;
		        }

		        $logging_info['result'][$key] = $result;
		    }
		  
		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on account user ID :'.  $deleteAccount['link_id']. " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on account user ID :'.  $deleteAccount['link_id']. " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
				$errors['Exception'] = 'There was an error on account user ID :'.  $deleteAccount['link_id']. " Code : "
			      . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch


		$ret = array(
			'success' => $success,
			'errors' => $errors,
			'logging_info' => $logging_info
		);

		return $ret;

		

	}//deleteAccountUserLink



	public static function updateAccountUserLink(\Google_Service_Analytics $analytics, $updatedAccountList, \Google_Client $client){

		$errors = [];
		$success = [];

		$logging_info = [];

		try {


		    $client->setUseBatch(true);
		    $batch = new \Google_Http_Batch($client);

	    	foreach($updatedAccountList as $updateAccount){

				$permissions = new \Google_Service_Analytics_EntityUserLinkPermissions();
				$permissions->setLocal($updateAccount['permissions']);
				
				$userLink = new \Google_Service_Analytics_EntityUserLink();
				$userLink->setPermissions($permissions);

				$request = $analytics->management_accountUserLinks->update($updateAccount['account_id'], $updateAccount['link_id'], $userLink);

				$batch->add($request);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $updateAccount['account_id'],
											),
						'email' => $updateAccount['email'],
						'link_id' => $updateAccount['link_id'],
						'old_permissions' => $updateAccount['old_permissions'],
						'new_permissions' => $updateAccount['permissions'],
				);

			}//endforeach

			//execute batch
			$results = $batch->execute();

	    	foreach ($results as $key => $result) {
		        if ($result instanceof \Google_Service_Exception) {	          
		           $errors['Google_Service_Exception'][$key] = $result;
		        } else {
		           $success['results'][$key] =  $result = $result->id;
		        }

		        $logging_info['result'][$key] = $result;
		    }

		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on email :'. $updateAccount['email']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on email :'. $updateAccount['email']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
			$errors['Exception'] = 'There was an error on email :'. $updateAccount['email']
				. " Code : " . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch
	

		$ret = array(
			'success' => $success,
			'errors' => $errors,
			'logging_info' => $logging_info
		);

		return $ret;


	}//updateAccountUserLink





}//endofclass