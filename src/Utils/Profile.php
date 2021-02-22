<?php

namespace App\Utils;


class Profile{
	
	public static function listProfile (\Google_Service_Analytics $analytics,  $account_id, $property_id){
		
		$errors = [];
		$success = [];

		try {
							
		  	$profiles  = $analytics->management_profiles
		      ->listManagementProfiles($account_id, $property_id);

			$profileLists = array();

			foreach ($profiles->getItems() as $profile) {
				$account_id = $profile->getAccountId();
				$property_id  = $profile->getWebPropertyId();

				$profile_id = $profile->getId();
			 	$profile_name = $profile->getName();
				$profile_type = $profile->getType();

				$profile_default_page = $profile->getDefaultPage();
				$profile_exclude_query_param  = $profile->getExcludeQueryParameters();
				$profile_site_search = $profile->getSiteSearchCategoryParameters();

				$profile_currency = $profile->getCurrency();
				$profile_timezone = $profile->getTimezone();
				$profile_created = $profile->getCreated();
				$profile_updated = $profile->getUpdated();

				$profile_ecom_track = $profile->getECommerceTracking();
				$profile_enhanced_ecom_track = $profile->getEnhancedECommerceTracking();
				
				$profileLists[]   = array (
					'account_id' => $account_id, 
					'property_id' => $property_id,
					'profile_id' => $profile_id,
					'profile_name' => $profile_name
				);
			}//endforeach

			$success = $profileLists;	
			  
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

	}//listProfile


	public static function listProfileUserLinks (\Google_Service_Analytics $analytics,  $account_id, $property_id, $profile_id){
		
		$errors = [];
		$success = [];

		try {
		
		  	$profileUserlinks = $analytics->management_profileUserLinks
		      ->listManagementProfileUserLinks($account_id, $property_id, $profile_id);

			$userProfileLinks = array();

			foreach ($profileUserlinks->getItems() as $profileUserLink) {
				$entity = $profileUserLink->getEntity();
				$profileRef = $entity->getProfileRef();
				$userRef = $profileUserLink->getUserRef();
			 	$permissions = $profileUserLink->getPermissions();

		
				$profile_user_link_id   = $profileUserLink->getId();
				$profile_user_link_kind = $profileUserLink->getKind();

				$profile_id   = $profileRef->getId();
				$profile_name = $profileRef->getName();
				$profile_kind = $profileRef->getKind();

				$permissions_local     = $permissions->getLocal();
				$permissions_effective = $permissions->getEffective();

				$user_id    = $userRef->getId();
				$user_kind  = $userRef->getKind();
				$user_email = $userRef->getEmail();

				$userProfileLinks[]   = array (
					'email' => $user_email, 
					'user_link_id' => $profile_user_link_id,
					'permissions_local' => $permissions_local,
					'permissions_effective' => $permissions_effective
				);
			}//endforeach

			$success =  $userProfileLinks;	
				  
		} catch (\apiServiceException $e) {
		  	$errors['apiServiceException'] =  'There was an Analytics API service error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error.' . " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\Google_Service_Exception $e) {
		  	$errors['Google_Service_Exception'] =  'There was a service exception.' . " Code : "
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

	}//listProfileUserLinks


	public static function insertProfileUserLink (\Google_Service_Analytics $analytics, $insertedProfilesList, \Google_Client $client){

		$errors = [];
		$success = [];

		$logging_info = [];

		try {

			$client->setUseBatch(true);
	    	$batch = new \Google_Http_Batch($client);
			 
			foreach($insertedProfilesList as $insertProfile){

	    		// Create the user reference.
				$userRef = new \Google_Service_Analytics_UserRef();
				$userRef->setEmail($insertProfile['email']);

	    		// Create the permissions object.
				$permissions = new \Google_Service_Analytics_EntityUserLinkPermissions();
				$permissions->setLocal($insertProfile['permissions']);

				// Create the view (profile) link.
				$link = new \Google_Service_Analytics_EntityUserLink();
				$link->setPermissions($permissions);
				$link->setUserRef($userRef);

				$request = $analytics->management_profileUserLinks->insert($insertProfile['account_id'], $insertProfile['property_id'], $insertProfile['profile_id'], $link);

				$batch->add($request);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $insertProfile['account_id'],
											'property_id' => $insertProfile['property_id'],
											'profile_id' => $insertProfile['profile_id']
											),
						'email' => $insertProfile['email'],
						'permissions' => $insertProfile['permissions'],
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
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on email :'. $insertProfile['email']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on email :'. $insertProfile['email']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();
					
		}catch (\Exception $e){
			$errors['Exception'] = 'There was an error on email :'. $insertProfile['email']
				. " Code : " . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch


		$ret = array(
			'success' => $success,
			'errors' => $errors,
			'logging_info' => $logging_info
		);

		return $ret;

	}//insertProfileUserLink


	public static function updateProfileUserLink (\Google_Service_Analytics $analytics, $updatedProfilesList, \Google_Client $client){

		$errors = [];
		$success = [];
		
		try {
   	
		    $client->setUseBatch(true);
		    $batch = new \Google_Http_Batch($client);

	    	foreach($updatedProfilesList as $updateProfile){

	    		// Create the permissions object.
				$permissions = new \Google_Service_Analytics_EntityUserLinkPermissions();
				$permissions->setLocal($updateProfile['permissions']);

				// Create the view (profile) link.
				$link = new \Google_Service_Analytics_EntityUserLink();
				$link->setPermissions($permissions);

				$request = $analytics->management_profileUserLinks->update( $updateProfile['account_id'], $updateProfile['property_id'], $updateProfile['profile_id'], $updateProfile['link_id'], $link);

				$batch->add($request);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $updateProfile['account_id'],
											'property_id' => $updateProfile['property_id'],
											'profile_id' => $updateProfile['profile_id'],
											),
						'email' => $updateProfile['email'],
						'link_id' => $updateProfile['link_id'],
						'old_permissions' => $updateProfile['old_permissions'],
						'new_permissions' => $updateProfile['permissions'],
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
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on profile ID :'. $updateProfile['profile_id']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on profile ID :'. $updateProfile['profile_id']
		  		. " Code : " . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
			$errors['Exception'] = 'There was an error on profile ID :'. $updateProfile['profile_id'] 
				. " Code : " . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch		


		$ret = array(
			'success' => $success,
			'errors' => $errors,
			'logging_info' => $logging_info
		);

		return $ret;

	}//updateProfileUserLink


	public static function deleteProfileUserLink (\Google_Service_Analytics $analytics, $deletedProfilesList, \Google_Client $client){

		$errors = [];
		$success = [];

		$logging_info = [];

    	try {
 		    	
		    $client->setUseBatch(true);
		    $batch = new \Google_Http_Batch($client);
			
	    	foreach($deletedProfilesList as $deleteProfile){
	    		$link = $analytics->management_profileUserLinks->delete($deleteProfile['account_id'], $deleteProfile['property_id'], $deleteProfile['profile_id'], $deleteProfile['link_id']);

	    		$batch->add($link);

				//log the values
				$logging_info[] = array(
						'access_level' => array(
											'account_id' => $deleteProfile['account_id'], 
											'property_id' => $deleteProfile['property_id'],
											'profile_id' => $deleteProfile['profile_id']
											),
						'email' => $deleteProfile['email'],
						'link_id' => $deleteProfile['link_id'],
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
		  	$errors['apiServiceException'] =  'There was an Analytics API service error on profile ID :'. $deleteProfile['link_id']. " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		} catch (\apiException $e) {
		  	$errors['apiException'] =  'There was a general API error on profile ID :'. $deleteProfile['link_id']. " Code : "
		      . $e->getCode() . ':' . $e->getMessage();

		}catch (\Exception $e){
				$errors['Exception'] = 'There was an error on profile ID :'. $deleteProfile['link_id']. " Code : "
			      . $e->getCode() . ':' . $e->getMessage();
		}//endtrycatch


		$ret = array(
			'success' => $success,
			'errors' => $errors,
			'logging_info' => $logging_info
		);

		return $ret;

	}//deleteProfileUserLink







}//endofclass