<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Utils\Profile;
use App\Utils\Property;
use App\Utils\Account;
use App\Utils\FileReader;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class GenerateExcelController extends AbstractController
{

    private $params;
    private $dir_root;

    private $client;
    private $analytics;
    private $requestStack;

    private $accountSummaries;
    private $accessLevelSummaries;
    private $accountUserLinks;

    private $generate_filename = "data_ga_account_list.xlsx";


    public function __construct(ParameterBagInterface $params, $dir_root, RequestStack $requestStack)
    {
        $this->params = $params;
        $this->dir_root = $dir_root;
        $this->requestStack = $requestStack;
    }
    

    /**
      * @Route("/generatefile", name="generatefile", methods="POST")
      */ 
    public function generateFileAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!'); //secure

       
        try{

                // $this->generateAccountSummaries();
                $this->generateAccessLevelSummaries();
                // $jsonFile =  $this->dir_root . '/data/' . "data_ga_account_summaries.json";
                // $this->accountSummaries =  json_decode(file_get_contents($jsonFile), true);
                
                $this->generateUserLinks();
                // $jsonFile =  $this->dir_root . '/data/' . "data_ga_user_links.json";
                // $this->accountUserLinks =  json_decode(file_get_contents($jsonFile), true);               
                
                $spreadsheet = new Spreadsheet();
                
                $sheet = $spreadsheet->getActiveSheet();

                $sheet->setCellValue('A1', 'ACCOUNT ID');
                $sheet->setCellValue('B1', 'ACCOUNT NAME');

                $sheet->setCellValue('C1', 'PROPERTY ID');
                $sheet->setCellValue('D1', 'PROPERTY NAME');

                $sheet->setCellValue('E1', 'VIEW ID');
                $sheet->setCellValue('F1', 'VIEW NAME');

                $sheet->setCellValue('G1', 'EMAIL');

                $sheet->setCellValue('H1', 'READ_AND_ANALYZE');
                $sheet->setCellValue('I1', 'COLLABORATE');
                $sheet->setCellValue('J1', 'EDIT');
                $sheet->setCellValue('K1', 'MANAGE_USERS');
                
                
                $data_array = FileReader::populateUserLists($this->accountUserLinks);
                
                $sheet->fromArray(
                        $data_array,  // The data to set
                        NULL,        // Array values with this value will not be set
                        'A2'         // Top left coordinate of the worksheet range where
                                     //    we want to set these values (default is A1)
                    );

                $writer = new Xlsx($spreadsheet);
                
                $temp_file = tempnam(sys_get_temp_dir(), $this->generate_filename);
                $writer->save($temp_file);


               return $this->file($temp_file, $this->generate_filename, ResponseHeaderBag::DISPOSITION_INLINE);

        }
        catch(\Exception $e){

            $ret = $e->getMessage();
             return  new JsonResponse($ret);

        }//endtrycatch

       
    }//generateFileAction




    /**
     * Retrieves the account summaries
     * and save them in a file
     * 
     * file : data_ga_account_summaries.json
     */
    public function generateAccountSummaries(){

        $filename = "data_ga_account_summaries_generate.json";

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
     * file : data_ga_account_summaries.json
     */
    public function generateAccessLevelSummaries(){

        $filename = "data_ga_access_level_summaries_generate.json";

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

        $filename = "data_ga_user_links_generate.json";

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