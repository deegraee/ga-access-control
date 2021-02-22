<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

use App\Services\PostRequest;
use GuzzleHttp\Client;

use App\Utils\Profile;
use App\Utils\Property;
use App\Utils\Account;
use App\Utils\FileReader;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use App\TransactionLoggerBundle\Util\TransactionLogger;


class FileReaderController extends AbstractController
{

    private $params;
    private $dir_root;

    private $client;
    private $analytics;
    private $requestStack;

    private $accountSummaries;
    private $accessLevelSummaries;
    private $accountUserLinks;
    private $transactionLogger;


    public function __construct(ParameterBagInterface $params, $dir_root, RequestStack $requestStack, TransactionLogger $transactionLogger)
    {
        $this->params = $params;
        $this->dir_root = $dir_root;
        $this->requestStack = $requestStack;
        $this->transactionLogger = $transactionLogger;
    }
    
    /**
      * @Route("/upload", name="upload")
      */  
    public function fileUploadAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!'); //secure

        return $this->render('upload/upload.html.twig', array());

    }//fileUploadAction


    /**
      * @Route("/readfile", name="readfile", methods="POST")
      */ 
    public function fileReaderAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!'); //secure

         $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
       
        try{

            if(isset($_FILES['file']['name']) && in_array($_FILES['file']['type'],$file_mimes)) {

                $arr_file = explode('.', $_FILES['file']['name']);
                $extension = end($arr_file);

           
                $filename = basename($_FILES['file']['name']);
                $upload_dir =  $this->dir_root . '/public/uploads/';
                $import_file = $upload_dir . $filename;
               
                if (file_exists($import_file)) {
                    unlink($import_file);
                }
               
                move_uploaded_file($_FILES['file']['tmp_name'], $import_file);
               
                if('csv' == $extension) {
                    echo "This is an csv file. Only Excel files allowed.";
                    exit();

                } else {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();   
                }

                $spreadsheet = new Spreadsheet();
                $spreadsheet = $reader->load($import_file);
                $spreadsheet->getActiveSheet();

                $imported_record_array = array();
                $original_records_array  = array();

                $list = array();
                $result = array();



                foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {

                    $worksheetTitle     = $worksheet->getTitle();                  
                    $highestRow         = $worksheet->getHighestRow(); // e.g. 10
          
                    $read_array = array();


                    for ($row = 2; $row <= $highestRow; ++ $row) { //start after column titles

                        $account_id = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $account_name = $worksheet->getCellByColumnAndRow(2, $row)->getValue();

                        $property_id = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                        $property_name = $worksheet->getCellByColumnAndRow(4, $row)->getValue();

                        $profile_id = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                        $profile_name = $worksheet->getCellByColumnAndRow(6, $row)->getValue();

                        $email = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                        
                        $read_and_analyze_perm = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                        $collaborate_perm = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                        $edit_perm = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                        $manage_users_perm = $worksheet->getCellByColumnAndRow(11, $row)->getValue();

                        
                        $imported_record_array[] = array(
                            $account_id,
                            $account_name,
                            $property_id,
                            $property_name,
                            $profile_id,
                            $profile_name,
                            $email,
                            $read_and_analyze_perm,
                            $collaborate_perm,
                            $edit_perm,
                            $manage_users_perm
                        );
                               
                    }//outer loop   

                }//foreach
             


                //$this->generateAccountSummaries();
                $this->generateAccessLevelSummaries();
                $this->generateUserLinks();

                $original_records_array = FileReader::populateUserLists($this->accountUserLinks);

                
                $orig_vs_import_result = FileReader::array_diff_no_cast($original_records_array,$imported_record_array);  

                $orig_vs_import_result = FileReader::nameArray($orig_vs_import_result);

                $orig_vs_import_result = FileReader::getLevels($orig_vs_import_result);

                // echo "Original vs imported : Identify the deleted record";
                // var_dump($orig_vs_import_result);
             

                
                $import_vs_orig_result = FileReader::array_diff_no_cast($imported_record_array,$original_records_array);

                $import_vs_orig_result = FileReader::nameArray($import_vs_orig_result);

                $import_vs_orig_result = FileReader::getLevels($import_vs_orig_result);

                // echo "Imported vs original : Identify the inserted record";
                // var_dump($import_vs_orig_result);


                $list = $this->analyzeTableData($orig_vs_import_result, $import_vs_orig_result);

                // echo "insert";
                // var_dump($list['insert_array']);

                // echo "update";
                // var_dump($list['update_array']);

                // echo "delete";
                // var_dump($list['delete_array']);

              
                 $result['insert'] = $this->insertRecordsToWebservice($list['insert_array']);
                 $result['update'] = $this->updateRecordsToWebservice($list['update_array']);
                 $result['delete'] = $this->deleteRecordsToWebservice($list['delete_array']);


                $ret = $result;

            }else{
                if(empty($_FILES))
                    $ret = "Empty files";
            }
        }
        catch(\Exception $e){

            $ret = $e->getMessage();

        }//endtrycatch

        return new JsonResponse(array(
                'insert' => $list['insert_array'],
                'update' => $list['update_array'],
                'delete' => $list['delete_array'],
                'result' => $ret
            ));
    }//fileReaderAction



    /**
     * identify processes on two arrays
     * 
     * @param  [type] $orig_vs_import_result [description]
     * @param  [type] $import_vs_orig_result [description]
     * @return array list                       
     */
    private function analyzeTableData($orig_vs_import_result, $import_vs_orig_result){

        $insert_array = array();
        $update_array = array();
        $delete_array = array();

        $list = array();

         //--------------- ACCOUNT LEVEL -----------------------------------//
         
        $update_account = array();

        foreach($orig_vs_import_result['account_level'] as $orig_key => $orig_record){
           
            foreach($import_vs_orig_result['account_level'] as $change_key => $changed_record){

                //check if email is found on both lists --> to be updated
                if($orig_record['email'] == $changed_record['email']){
                    $update_account[] = $changed_record; //get the changes made
                    unset($import_vs_orig_result['account_level'][$change_key]); //remove from the list
                    unset($orig_vs_import_result['account_level'][$orig_key]); //remove from the list
                }

            }//innerforeach
        }//outerforeach

        $update_array['account_level'] = $update_account;

        //remaining value are to be deleted
        $delete_array['account_level'] = $orig_vs_import_result['account_level'];

        //remaining values are to be inserted
        $insert_array['account_level'] = $import_vs_orig_result['account_level'];

        //--------------- PROPERTY LEVEL -----------------------------------//
        
        $update_property = array();

        foreach($orig_vs_import_result['property_level'] as $orig_key => $orig_record){
            
            foreach($import_vs_orig_result['property_level'] as $change_key => $changed_record){

                //check if email is found on both lists --> to be updated
                if($orig_record['email'] == $changed_record['email']){
                    $update_property[] = $changed_record; //get the changes made
                    unset($import_vs_orig_result['property_level'][$change_key]); //remove from the list
                    unset($orig_vs_import_result['property_level'][$orig_key]); //remove from the list
                }

            }//innerforeach
        }//outerforeach

        $update_array['property_level'] = $update_property;

        //remaining value are to be deleted
        $delete_array['property_level'] = $orig_vs_import_result['property_level'];

        //remaining values are to be inserted
        $insert_array['property_level'] = $import_vs_orig_result['property_level'];

        //--------------- PROFILE LEVEL -----------------------------------//

        $update_profile = array();

        foreach($orig_vs_import_result['profile_level'] as $orig_key => $orig_record){
            
            foreach($import_vs_orig_result['profile_level'] as $change_key => $changed_record){

                //check if email is found on both lists --> to be updated
                if($orig_record['email'] == $changed_record['email']){
                    $update_profile[] = $changed_record; //get the changes made
                    unset($import_vs_orig_result['profile_level'][$change_key]); //remove from the list
                    unset($orig_vs_import_result['profile_level'][$orig_key]); //remove from the list
                }

            }//innerforeach
        }//outerforeach

        $update_array['profile_level'] = $update_profile;

        //remaining value are to be deleted
        $delete_array['profile_level'] = $orig_vs_import_result['profile_level'];

        //remaining values are to be inserted
        $insert_array['profile_level'] = $import_vs_orig_result['profile_level'];


        return $list[] = array(
            'insert_array' => $insert_array,
            'update_array' => $update_array,
            'delete_array' => $delete_array
        );

    }//analyzeTableData




    /**
     * Update the records as listed on the excel sheet
     * @param  array $list list of records to be updated
     * @return        succcess/error
     */
    private function insertRecordsToWebservice($list){

        //var_dump($list);

        $ret = array();

        //set the mode for insertion
        $analytics = $this->_initializeAnalytics();

        if(!empty($list['account_level'])){

            $insertAccount = Account::insertAccountUserLink($analytics, $list['account_level'], $this->client); 

            if (!empty($insertAccount['errors'])) {

                $this->logError("Insert", $insertAccount);
                $ret['account'] = $insertAccount['errors'];

            }elseif(!empty($insertAccount['success'])) {

                $this->logSuccess("Insert", $insertAccount);
                $ret['account'] = $insertAccount['success'];

            } 

        }//endif - account_level

        if(!empty($list['property_level'])){  

            $insertProperty = Property::insertPropertyUserLink($analytics, $list['property_level'], $this->client);

            if (!empty($insertProperty['errors'])) {

                $this->logError("Insert", $insertProperty);
                $ret['property'] = $insertProperty['errors'];

            }elseif(!empty($insertProperty['success'])) {

                $this->logSuccess("Insert", $insertProperty);
                $ret['property'] = $insertProperty['success'];
            } 

        }//endif - property_level      

        if(!empty($list['profile_level'])){

            $insertProfile = Profile::insertProfileUserLink($analytics, $list['profile_level'], $this->client);

            if (!empty($insertProfile['errors'])) {

                $this->logError("Insert", $insertProfile);
                $ret['profile'] = $insertProfile['errors'];

            }elseif(!empty($insertProfile['success'])) {

                $this->logSuccess("Insert", $insertProfile);
                $ret['profile'] = $insertProfile['success'];
            } 

        }//endif - profile_level      

        return $ret;

    }//insertRecordsToWebservice


    /**
     * Update the records as listed on the excel sheet
     * @param  array $list list of records to be updated
     * @return        succcess/error
     */
    private function updateRecordsToWebservice($list){

        $ret = array();

       
        $analytics = $this->_initializeAnalytics();

        if(!empty($list['account_level'])){

            $updateFlag = true;


            foreach($list['account_level'] as $key => $accountList){

               $user_list = FileReader::getUserLink($this->accountUserLinks, $accountList['email'], $accountList['account_id']);
               //var_dump($user_list);

                if($user_list !== false){  
                    $list['account_level'][$key]['old_permissions'] = $user_list['permissions_local'];
                    $list['account_level'][$key]['link_id'] = $user_list['user_link_id'];        
                }else{
                
                    echo "Email not found.";
                    $updateFlag = false;
                 
                }
            }//endforeach

            if($updateFlag){    

                $updateAccount = Account::updateAccountUserLink($analytics, $list['account_level'], $this->client);  
               
                if (!empty($updateAccount['errors'])) {

                    $this->logError("Update", $updateAccount);
                    $ret['account'] = $updateAccount['errors'];

                }elseif(!empty($updateAccount['success'])) {

                    $this->logSuccess("Update", $updateAccount);
                    $ret['account'] = $updateAccount['success'];
                }                 
               
               // $ret['account'] = $list['account_level'];
               
            }//endif
           
        }//account


        if(!empty($list['property_level'])){

            $updateFlag = true;

            foreach($list['property_level'] as $key => $propertyList){
                $user_list = FileReader::getUserLink($this->accountUserLinks, $propertyList['email'],$propertyList['account_id'], $propertyList['property_id'] );

                if($user_list !== false){
                    $list['property_level'][$key]['old_permissions'] = $user_list['permissions_local'];
                    $list['property_level'][$key]['link_id'] = $user_list['user_link_id'];
                }else{
                    echo "Email not found.";
                    $updateFlag = false;
                }
            }//endforeach

            if($updateFlag){

                $updateProperty = Property::updatePropertyUserLink($analytics, $list['property_level'], $this->client); 
                
                if (!empty($updateProperty['errors'])) {

                    $this->logError("Update", $updateProperty);
                    $ret['property'] = $updateProperty['errors'];

                }elseif(!empty($updateProperty['success'])) {

                    $this->logSuccess("Update", $updateProperty);
                    $ret['property'] = $updateProperty['success'];
                }             
                
                //$ret['property'] = $list['property_level'];
                
            }//endif

        }//property       


        if(!empty($list['profile_level'])){

            $updateFlag = true;

            foreach($list['profile_level'] as $key => $profileList){
                $user_list = FileReader::getUserLink($this->accountUserLinks,$profileList['email'],$profileList['account_id'], $profileList['property_id'], $profileList['profile_id'] );

                if($user_list !== false){
                    $list['profile_level'][$key]['old_permissions'] = $user_list['permissions_local'];
                    $list['profile_level'][$key]['link_id'] = $user_list['user_link_id'];
                }else{
                    echo "Email not found.";
                    $updateFlag = false;
                }
            }//endforeach

            if($updateFlag){

                $updateProfile = Profile::updateProfileUserLink($analytics, $list['profile_level'], $this->client); 

                if (!empty($updateProfile['errors'])) {

                    $this->logError("Update", $updateProfile);
                    $ret['profile'] = $updateProfile['errors'];

                }elseif(!empty($updateProfile['success'])) {

                    $this->logSuccess("Update", $updateProfile);
                    $ret['profile'] = $updateProfile['success'];
                }
                //$ret['profile'] = $list['profile_level'];
                
            }//endif
      
        }//profile


        return $ret;

    }//updateRecordsToWebservice


    /**
     * Delete the records as listed on the excel sheet
     * @param  array $list list of records to be deleted
     * @return        succcess/error
     */
    private function deleteRecordsToWebservice($list){

        $ret = array();
        
        $analytics = $this->_initializeAnalytics();

        if(!empty($list['account_level'])){

            $deleteFlag = true;

            foreach($list['account_level'] as $key => $accountList){
                $user_list = FileReader::getUserLink($this->accountUserLinks, $accountList['email'], $accountList['account_id']);

                if($user_list !== false){
                    $list['account_level'][$key]['link_id'] = $user_list['user_link_id'];
                }else{
                    echo "Email not found.";
                    $deleteFlag = false;
                }
            }//endforeach

            if($deleteFlag){

                $deleteAccount = Account::deleteAccountUserLink($analytics, $list['account_level'], $this->client); 

                if (!empty($deleteAccount['errors'])) {

                    $this->logError("Delete", $deleteAccount);
                    $ret['account'] = $deleteAccount['errors'];

                }elseif(!empty($deleteAccount['success'])) {

                    $this->logSuccess("Delete", $deleteAccount);
                    $ret['account'] = $deleteAccount['success'];
                } 

            }//endif

        }//account


        if(!empty($list['property_level'])){

            $deleteFlag = true;

            foreach($list['property_level'] as $key => $propertyList){
                $user_list = FileReader::getUserLink($this->accountUserLinks, $propertyList['email'],$propertyList['account_id'], $propertyList['property_id'] );

                if($user_list !== false){
                    $list['property_level'][$key]['link_id'] = $user_list['user_link_id'];
                }else{
                    echo "Email not found.";
                    $deleteFlag = false;
                }
            }//endforeach

            if($deleteFlag){

                $deleteProperty = Property::deletePropertyUserLink($analytics, $list['property_level'], $this->client); 

                if (!empty($deleteProperty['errors'])) {

                    $this->logError("Delete", $deleteProperty);
                    $ret['property'] = $deleteProperty['errors'];

                }elseif(!empty($deleteProperty['success'])) {

                    $this->logSuccess("Delete", $deleteProperty);
                    $ret['property'] = $deleteProperty['success'];
                }    

            }//endif

        }//property       


        if(!empty($list['profile_level'])){

            $deleteFlag = true;

            foreach($list['profile_level'] as $key => $profileList){
                $user_list = FileReader::getUserLink($this->accountUserLinks, $profileList['email'],$profileList['account_id'], $profileList['property_id'], $profileList['profile_id'] );

                if($user_list !== false){
                    $list['profile_level'][$key]['link_id'] = $user_list['user_link_id'];
                }else{
                    echo "Email not found.";
                    $deleteFlag = false;
                }
            }//endforeach

            if($deleteFlag){

                $deleteProfile = Profile::deleteProfileUserLink($analytics, $list['profile_level'], $this->client); 

                if (!empty($deleteProfile['errors'])) {

                    $this->logError("Delete", $deleteProfile);
                    $ret['profile'] = $deleteProfile['errors'];

                }elseif(!empty($deleteProfile['success'])) {

                    $this->logSuccess("Delete", $deleteProfile);
                    $ret['profile'] = $deleteProfile['success'];
                }  

            }//endif
      
        }//profile

        return $ret;

    }//deleteRecordsToWebservice



    /**
     * Retrieves the account summaries
     * and save them in a file
     * 
     * file : data_ga_account_summaries.json
     */
    public function generateAccountSummaries(){

        $filename = "data_ga_account_summaries_read.json";

        $jsonFile =  $this->dir_root . '/data/' . $filename;

        $analytics = $this->_initializeAnalytics();
           
        $ret = Account::listAccountSummaries($analytics);

        if(!empty($ret['success'])){

            if (file_put_contents($jsonFile, json_encode($ret['success']))){
                $this->accountSummaries =  json_decode(file_get_contents($jsonFile), true);
            }

        }
        elseif (!empty($ret['errors'])) {
            return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));
        }   

    }//generateAccountSummaries



    /**
     * Retrieves the access level summaries
     * and save them in a file
     * 
     * file : data_ga_access_level_summaries_read.json
     */
    public function generateAccessLevelSummaries(){

        $filename = "data_ga_access_level_summaries_read.json";

        $jsonFile =  $this->dir_root . '/data/' . $filename;

        $analytics = $this->_initializeAnalytics();
           
        $ret = Account::listAccessLevelSummaries($analytics);

        if(!empty($ret['success'])){

            if (file_put_contents($jsonFile, json_encode($ret['success']))){
                $this->accessLevelSummaries =  json_decode(file_get_contents($jsonFile), true);
            }

        }
        elseif (!empty($ret['errors'])) {
            return  new JsonResponse(array('status' => false, 'errors'=>$ret['errors']));
        }   

    }//generateAccessLevelSummaries



    /**
     * Retrieves all userlinks from the account summaries
     * and save them in a file
     * 
     * file : data_ga_user_links.json
     */
    public function generateUserLinks(){

        $filename = "data_ga_user_links_read.json";

        $jsonFile =  $this->dir_root . '/data/' . $filename;
        $analytics = $this->_initializeAnalytics();

        
        if(isset($this->accessLevelSummaries)){

            $account_users = array();

            foreach($this->accessLevelSummaries as $account){

                $property_users = array();

                foreach($account['properties'] as $property){

                    $profile_users = array();

                    foreach($property['profiles'] as $profile){

                        $ret =  Profile::listProfileUserLinks($analytics, $account['account_id'], $property['property_id'], $profile['profile_id']);

                        if(!empty($ret['success'])){

                            $profile_users[] = array(
                                'user_list' => $ret['success'],
                                'profile_id' => $profile['profile_id'],
                                'profile_name' => $profile['profile_name'],
                            );
                               
                        }//endif

                    }//foreach - profile

                    $ret =  Property::listPropertyUserLinks($analytics, $account['account_id'], $property['property_id']);

                    if(!empty($ret['success'])){

                        $property_users[] = array(
                             'user_list' => $ret['success'],
                             'property_id' => $property['property_id'],
                             'property_name' => $property['property_name'],
                             'profile_users' => $profile_users
                        );
                           
                    }else{

                        $property_users[] = array(
                             'property_id' => $property['property_id'],
                             'property_name' => $property['property_name'],
                             'profile_users' => $profile_users
                        );
                    }//endif
                   
                }//foreach - property

                $ret =  Account::listAccountUserLinks($analytics, $account['account_id']);

                if(!empty($ret['success'])){

                    $account_users[] = array(
                        'user_list' => $ret['success'],
                        'account_id' => $account['account_id'],
                        'account_name' => $account['account_name'],
                        'property_users' =>  $property_users
                    );                
                       
                }else{

                    $account_users[] = array(
                        'account_id' => $account['account_id'],
                        'account_name' => $account['account_name'],
                        'property_users' =>  $property_users
                    ); 

                }//endif
             

            }//foreach - account

            if (file_put_contents($jsonFile, json_encode($account_users))){
                $this->accountUserLinks =  json_decode(file_get_contents($jsonFile), true);
            }

        }//endif   


    }//generateUserLinks



    private function logSuccess($transaction, $logdata)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $this->transactionLogger->transactionLogDebug(array(
            'transaction' => $transaction,
            'user' => $user->getUsername(),
            'logs' => $logdata['logging_info']
        ));

    }//logSuccess


    private function logError($transaction, $logdata)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $this->transactionLogger->transactionLogError(array(
            'transaction' => $transaction,
            'user' => $user->getUsername(),
            'error' => $logdata['errors'],
            'logs' => $logdata['logging_info']
        ));

    }//logError


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


    private function _initializeAnalytics()
    {
        $client = new \Google_Client();
        $client->setAccessToken($this->getSession()->get('access_token'));

        $analytics = new \Google_Service_Analytics($client);
        $this->client = $client;

        return $analytics;
        
    }//initializeAnalytics


}