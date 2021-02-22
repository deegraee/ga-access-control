<?php

namespace App\Utils;


class Property{
	
	public static function listProperty (\Google_Service_Analytics $analytics,  $account_id){
		
		$errors = [];
		$success = [];

  		try {
	
		  	$properties  = $analytics->management_webproperties
		      ->listManagementWebproperties($account_id);

			$propertyLists = array();

			foreach ($properties->getItems() as $property) {
				$account_id = $property->getAccountId();
				$property_id = $property->getId();
				$property_name = $property->getName();
				$property_url = $property->getWebsiteUrl();
	
				$property_created   = $property->getCreated();
				$property_updated = $property->getUpdated();


				$propertyLists[]   = array (
					'account_id' => $account_id, 
					'property_id' => $property_id,
					'property_name' => $property_name,
					'property_url' => $property_url,
					'property_created' => $property_created,
					'property_updated' => $property_updated
				);
			}//endforeach

			$success =  $propertyLists;	
	
		  
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

	}//listProperty


	public static function listPropertyUserLinks (\Google_Service_Analytics $analytics, $account_id, $property_id){
		
		$errors = [];
		$success = [];

   		try {
			
			
		  	$propertyUserlinks = $analytics->management_webpropertyUserLinks
		      ->listManagementwebpropertyUserLinks($account_id, $property_id);

			$userPropertyLinks = array();

			foreach ($propertyUserlinks->getItems() as $propertyUserLink) {
				 $entity = $propertyUserLink->getEntity();
				 $propertyRef = $entity->getWebPropertyRef();
				 $userRef = $propertyUserLink->getUserRef();
				 $permissions = $propertyUserLink->getPermissions();

		
				$property_user_link_id   = $propertyUserLink->getId();
				$property_user_link_kind = $propertyUserLink->getKind();

				$property_id   = $propertyRef->getId();
				$property_name = $propertyRef->getName();
				$property_kind = $propertyRef->getKind();

				$permissions_local     = $permissions->getLocal();
				$permissions_effective = $permissions->getEffective();

				$user_id    = $userRef->getId();
				$user_kind  = $userRef->getKind();
				$user_email = $userRef->getEmail();

				$userPropertyLinks[]   = array (
					'email' => $user_email, 
					'user_link_id' => $property_user_link_id,
					'permissions_local' => $permissions_local,
					'permissions_effective' => $permissions_effective
				);
			}//endforeach

			$success =  $userPropertyLinks;	
	
		  
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

	}//listPropertyUserLinks


	public static function insertPropertyUserLink(\Google_Service_Analytics $analytics, $insertedPropertiesList, \Google_Client $client){

		$errors = [];
		$success = [];

		$logging_info = [];

		try {

		    $client->setUseBatch(true);
		    $batch = new \Google_Http_Batch($client);
			 
			foreach($insertedPropertiesList as $insertProperty){

	    		// Create the user reference.
				$userRef = new \Google_Service_Analytics_UserRef();
				$userRef->setEmail($insertProperty['email']);

	    		// Create the permissions object.
				$permissions = new \Google_Service_Analytics_EntityUserLinkPermissions();
				$permissions->setLocal($insertProperty['permissions']);

				// Create the view (Property) link.
				$link = new \Google_Service_Analytics_EntityUserLink();
				$link->setPermissions($permissions);
				$link->setUserRef($userRef);

				$request = $analytics->management_webpropertyUserLinks->insert($insertProperty['account_id'], $insertProperty['property_id'], $link);

				$batch->add($request);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $insertProperty['account_id'],
											'property_id' => $insertProperty['property_id']
											),
						'email' => $insertProperty['email'],
						'permissions' => $insertProperty['permissions'],
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
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on email :'. $insertProperty['email']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on email :'. $insertProperty['email']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
			$errors['Exception'] = 'There was an error on email :'. $insertProperty['email']
				. " Code : " . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch



		$ret = array(
			'success' => $success,
			'errors' => $errors,
			'logging_info' => $logging_info
		);

		return $ret;

	}//insertPropertyUserLink


	public static function updatePropertyUserLink(\Google_Service_Analytics $analytics, $updatedPropertiesList, \Google_Client $client){

		$errors = [];
		$success = [];

		$logging_info = [];

		try {

		    $client->setUseBatch(true);
		    $batch = new \Google_Http_Batch($client);

	    	foreach($updatedPropertiesList as $updateProperty){

	    		// Create the permissions object.
				$permissions = new \Google_Service_Analytics_EntityUserLinkPermissions();
				$permissions->setLocal($updateProperty['permissions']);

				// Create the view (Property) link.
				$link = new \Google_Service_Analytics_EntityUserLink();
				$link->setPermissions($permissions);

				$request = $analytics->management_webpropertyUserLinks->update($updateProperty['account_id'], $updateProperty['property_id'], $updateProperty['link_id'], $link);

				$batch->add($request);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $updateProperty['account_id'],
											'property_id' => $updateProperty['property_id'],
											),
						'email' => $updateProperty['email'],
						'link_id' => $updateProperty['link_id'],
						'old_permissions' => $updateProperty['old_permissions'],
						'new_permissions' => $updateProperty['permissions'],
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
		    }

		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on property ID :'. $updateProperty['link_id']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on property ID :'. $updateProperty['link_id']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
			$errors['Exception'] = 'There was an error on property ID :'. $updateProperty['link_id'] 
				. " Code : " . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch

		$ret = array(
			'success' => $success,
			'errors' => $errors,
			'logging_info' => $logging_info
		);

		return $ret;	

	}//updatePropertyUserLink


	public static function deletePropertyUserLink(\Google_Service_Analytics $analytics, $deletedPropertiesList, \Google_Client $client){

		$errors = [];
		$success = [];

		$logging_info = [];

    	try {
    	
		    $client->setUseBatch(true);
		    $batch = new \Google_Http_Batch($client);
			
	    	foreach($deletedPropertiesList as $deleteProperty){
	    		$link = $analytics->management_webpropertyUserLinks->delete($deleteProperty['account_id'], $deleteProperty['property_id'], $deleteProperty['link_id']);

	    		$batch->add($link);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $deleteProperty['account_id'],
											'property_id' => $deleteProperty['property_id']
											),
						'email' => $deleteProperty['email'],
						'link_id' => $deleteProperty['link_id'],
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
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on Property ID :'. $deleteProperty['link_id']. " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on Property ID :'. $deleteProperty['link_id']. " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
				$errors['Exception'] = 'There was an error on Property ID :'. $deleteProperty['link_id']. " Code : "
			      . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch

		$ret = array(
			'success' => $success,
			'errors' => $errors,
			'logging_info' => $logging_info
		);

		return $ret;

	}//deletePropertyUserLink


}//endofclass