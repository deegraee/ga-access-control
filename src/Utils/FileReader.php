<?php

namespace App\Utils;




class FileReader{



	/**
	 *	returns the  userlink data based on the list
	 * 
	 * @param  array $mainArrayList [description]
	 * @param  string $email         [description]
	 * @param  string $account_id    [description]
	 * @param  string $property_id   [description]
	 * @param  string $profile_id    [description]
	 * @return array $userlink   retrieved from the list
	 * @return false                 if $userlink not found
	 */
    public function getUserLink($mainArrayList, $email, $account_id, $property_id=null, $profile_id=null){
      

        foreach($mainArrayList as $account){                

            if($account['account_id'] == $account_id ){

                if($property_id !== null){ 

                     foreach($account['property_users'] as $property){

                        if($property['property_id'] == $property_id ){

                            //check if the profile id exists
                            if($profile_id !== null){

                                foreach($property['profile_users'] as $profile){

                                    if($profile['profile_id'] == $profile_id ){

                                        $key = array_search($email, array_column($profile['user_list'], 'email'));
                                        
                                        if($key !== false){
                                            // return $profile['user_list'][$key]['user_link_id'];
                                            return $profile['user_list'][$key];
                                        }else{
                                            return false;
                                        }//keycheck

                                    }//endif

                                }//endforeach
                               
                            }
                            else{

                                $key = array_search($email, array_column($property['user_list'], 'email'));
         
                                if($key !== false){
                                    // return $property['user_list'][$key]['user_link_id'];
                                    return $property['user_list'][$key];
                                }else{
                                    return false;
                                }//keycheck

                            }//endif - profile check

                        }//endif

                    }//end foreach


                }
                else{
                    
                    $key = array_search($email, array_column($account['user_list'], 'email'));

                    if($key !== false){
                        // return $account['user_list'][$key]['user_link_id'];
                        return $account['user_list'][$key];
                    }else{
                        return false;
                    }//keycheck
                   

                }//endif - property check      
               
            }//endif - account check

        }//endforeach      

    }//getUserLinkID


    public function nameArray($given_array){

    	$newArray = array();

    	foreach($given_array as $arr){
    		$newArray[] = array(
    			'account_id' => $arr[0],
    			'account_name' => $arr[1],
    			'property_id' => $arr[2],
    			'property_name' => $arr[3],
    			'profile_id' => $arr[4],
    			'profile_name' => $arr[5],
    			'email' => $arr[6],

    			'permissions' => self::getPermissions($arr)
    		);

    	}//endforeach

    	return $newArray;

    }//nameArray


    /**
     * Identify the permissions selected
     * @param  array $arr 
     * @return perm_array  permission array
     */
    public function getPermissions($arr){

    	$perm_array = array();

    	if($arr[7] == 'x'){
    		$perm_array[] = 'READ_AND_ANALYZE';
    	}

    	if($arr[8] == 'x'){
    		$perm_array[] = 'COLLABORATE';
    	}

    	if($arr[9] == 'x'){
    		$perm_array[] = 'EDIT';
    	}

    	if($arr[10] == 'x'){
    		$perm_array[] = 'MANAGE_USERS';
    	}

    	return $perm_array;
    }//getPermissions



    /**
     *
     * Divide the array list according to their levels
     * 
     * @param array
     * @return array
     */
    public function getLevels($entries){

        $entryList = array();

        $account_level = array();
        $property_level = array();
        $profile_level = array();

        foreach ($entries as $entry){
            if(!empty($entry['account_id'])){ //account level

                if(!empty($entry['property_id'])){ //property

                    if(!empty($entry['profile_id'])){ //profile

                       $profile_level[] = $entry; 

                    }//profile id
                    else{

                       $property_level[] = $entry; 
                    }

                }//property_id 
                else{

                    $account_level[] = $entry; 
                }

            }//account_id 

        }//foreach

        return $entryList[] = array(
                'account_level' => $account_level, 
                'property_level' => $property_level,
                'profile_level' => $profile_level,
            );

    }//getLevels



    /**
     * creates an array of the user lists from the account summaries
     * @param  array $mainArrayList 
     * @return array                
     */
    public function populateUserLists($mainArrayList){

        $main_array = array();

        if(isset($mainArrayList)){
            foreach($mainArrayList as $account){

                if(isset($account['user_list'])){
                    foreach($account['user_list'] as $account_users){
                       
                        $main_array[] = array(
                            $account['account_id'] ,
                            $account['account_name'] ,
                            '',
                            '',
                            '',
                            '',
                            $account_users['email'], 
                            in_array('READ_AND_ANALYZE',$account_users['permissions_effective'], true) ? 'x' : '',
                            in_array('COLLABORATE',$account_users['permissions_effective'], true) ? 'x' : '',
                            in_array('EDIT',$account_users['permissions_effective'], true) ? 'x' : '',
                            in_array('MANAGE_USERS',$account_users['permissions_effective'], true) ? 'x' : '',
                       
                        );
                        

                    }//account_users
                }//endif



                foreach($account['property_users'] as $property){

                    if(isset($property['user_list'])){
                        foreach($property['user_list'] as $property_users){
                            
                             $main_array[] = array(
                                $account['account_id'] ,
                                $account['account_name'] ,
                                $property['property_id'],
                                $property['property_name'],
                                '',
                                '',
                                $property_users['email'], 
                                in_array('READ_AND_ANALYZE',$property_users['permissions_effective'], true) ? 'x' : '',
                                in_array('COLLABORATE',$property_users['permissions_effective'], true) ? 'x' : '',
                                in_array('EDIT',$property_users['permissions_effective'], true) ? 'x' : '',
                                in_array('MANAGE_USERS',$property_users['permissions_effective'], true) ? 'x' : '',

                            );
                            
                        }//property_users

                    }//endif



                    foreach($property['profile_users'] as $profile){

                        foreach($profile['user_list'] as $profile_users){
                            
                             $main_array[] = array(
                                $account['account_id'] ,
                                $account['account_name'] ,
                                $property['property_id'],
                                $property['property_name'],
                                $profile['profile_id'],
                                $profile['profile_name'],
                                $profile_users['email'], 
                                in_array('READ_AND_ANALYZE',$profile_users['permissions_effective'], true) ? 'x' : '',
                                in_array('COLLABORATE',$profile_users['permissions_effective'], true) ? 'x' : '',
                                in_array('EDIT',$profile_users['permissions_effective'], true) ? 'x' : '',
                                in_array('MANAGE_USERS',$profile_users['permissions_effective'], true) ? 'x' : '',

                            );
                            

                        }//profile_users

                    }//profile

                }//property  

            }//endforeach - account

        }else{
            //var_dump($mainArrayList);
        }//endif
      

        return $main_array;

    }//populateUserLists



    /**
     * Identify the difference between two files
     * @param  [type] &$ar1 [description]
     * @param  [type] &$ar2 [description]
     * @return [type]       [description]
     */
	function array_diff_no_cast(&$ar1, &$ar2) {
	   $diff = Array();
	   foreach ($ar1 as $key => $val1) {
	      if (array_search($val1, $ar2) === false) {
	         $diff[$key] = $val1;
	      }
	   }
	   return $diff;

	}




    /**
     * Formats a JSON string for pretty printing
     *
     * @param string $json The JSON to make pretty
     * @param bool $html Insert nonbreaking spaces and <br />s for tabs and linebreaks
     * @return string The prettified output
     * @author Jay Roberts
     */
    function _format_json($json, $html = false) {

        $tabcount = 0; 
        $result = ''; 
        $inquote = false; 
        $ignorenext = false; 

        if ($html) { 
            $tab = "&nbsp;&nbsp;&nbsp;"; 
            $newline = "<br/>"; 
        } else { 
            $tab = "\t"; 
            $newline = "\n"; 
        } 

        for($i = 0; $i < strlen($json); $i++) { 
            $char = $json[$i]; 

            if ($ignorenext) { 
                $result .= $char; 
                $ignorenext = false; 

            } else { 
                switch($char) { 
                    case '{': 
                        $tabcount++; 
                        $result .= $char . $newline . str_repeat($tab, $tabcount); 
                        break; 
                    case '}': 
                        $tabcount--; 
                        $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char; 
                        break; 
                    case ',': 
                        $result .= $char . $newline . str_repeat($tab, $tabcount); 
                        break; 
                    case '"': 
                        $inquote = !$inquote; 
                        $result .= $char; 
                        break; 
                    case '\\': 
                        if ($inquote) $ignorenext = true; 
                        $result .= $char; 
                        break; 
                    default: 
                        $result .= $char; 
                } 
            } 
        } 

        return $result; 

    }//_format_json


}//class 




?>
