<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Wsimport_getservice_classlisting extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('pagination');
        $this->load->helper(array('form', 'url'));
        $this->load->database();
    }

    function index() {
        // wont do anything
    }
    function GetServiceClassListing($account_no="") {
        //echo date("Y-m-d H:i:s");exit;
        error_reporting(E_ALL);
        
        ini_set('display_errors', 1);
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        ini_set("default_socket_timeout",30);
        require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
        require_once('xml2arr.php');
        

        //require_once('salonevolve.pem');
        //TO GET CONFIG DETAILS FROM DB

        //$account_no = 133898042;
        if($account_no!=''){
        $this->db->where('salon_account_id', $account_no);
        }
        $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS)->result_array();
        //print_r($getConfigDetails);exit;

        //if ($getConfigDetails->num_rows > 0) {
        if(!empty($getConfigDetails)){
            foreach ($getConfigDetails as $configDetails) {
                echo "===> Cron Running For Salon Account Number: <b>" . $configDetails['salon_account_id'] . "</b>";
                echo "<br>";
                //SALON ACCOUNT NO
                //$account_no = 1501738222;
                $account_no = $configDetails['salon_account_id'];
                //echo $account_no.'<br>';
                //LOG IN DETAILS FOR MILLENIUM SDK  
                //$path_to_pem = base_url() . "salonevolve.pem";

                $siteIp = $configDetails['mill_ip_address'];
                $MillenniumGuid = $configDetails['mill_guid'];
                $musername = $configDetails['mill_username'];
                $mpassword = $configDetails['mill_password'];
                $url = $configDetails['mill_url'];
                $salon_id = $configDetails['salon_id'];
                //echo $url;exit;
                //MILLENIUM SDK URL AND HEADERS AND GUID    
                //$client = new nusoap_client('http://'.$siteIp.'/sdkadvance/MillenniumSDK.asmx?WSDL','wsdl','','','','');
                
                try {
                    $client = new nusoap_client($url . '?WSDL', 'wsdl', '', '', '', '');
                } catch (Exception $e) {
                    echo 'Caught exception1: ', $e->getMessage(), "\n";
                }

                $client->timeout = 0;
                $client->response_timeout = 30;

                $headers = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
                      <MillenniumGuid>" . $MillenniumGuid . "</MillenniumGuid>
                    </MillenniumInfo>";

                $client->setHeaders($headers);
                $err = $client->getError();
                if ($err) {
                    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
                }
                // LOGIN TO MILLENIUM SDK TO GET SESSION ID----->FIRST STEP
                $param = array('User' => $musername, 'Password' => $mpassword);
                try {
                    $result = $client->call('Logon', array('parameters' => $param), '', '', false, true);
                } catch (Exception $e) {
                    echo 'Caught exception1: ', $e->getMessage(), "\n";
                }


                if ($client->fault) {
                    echo '<h2>Fault</h2><pre>';
                    print_r($result);
                    echo '</pre>';
                } else {

                    $err = $client->getError();
                    if ($err) {
                        $errorType = '<h2>Error</h2><pre>' . $err . '</pre>';  //logToFile('errorFile.log', $errorType);
                    } else {
                        $data = $client->response;
                        preg_match('/<SessionId>(.*?)<\/SessionId>/s', $data, $matches);
                        $SessionId = $matches[0]; //SESSION ID AFTER LOGIN
                        // echo $SessionId;exit;
                        //WE AGAIN CALLING ANOTHER SERVICE, FOR CALLING EVERY SERVICE OTHER THAN LOGON WE NEED THE SESSION ID 
                        $client2 = new nusoap_client($url . '?WSDL', 'wsdl', '', '', '', '');
                        $headers2 = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
                              <MillenniumGuid>" . $MillenniumGuid . "</MillenniumGuid>
                            </MillenniumInfo>
                            <SessionInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
                              <SessionId>" . $SessionId . "</SessionId>
                            </SessionInfo>";
                        $client2->setHeaders($headers2);
                        $err2 = $client2->getError();
                        if ($err2) {
                            $errorType = '<h2>Constructor error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
                        }
                        //GETTING ALL THE APPOINTMENTS FROM SDK------------> STEP 2

                        if (!empty($account_no)) {
                            $param2 = array('IncludeDeleted' => 0); //parameters
                        } 
                        //print_r($param2);exit;    
                        try {
                            $result2 = $client2->call('GetServiceClassListing', array('parameters' => $param2), '', '', false, true); //METHOD WITH PARAMETERS
                        } catch (Exception $e) {
                            echo 'Caught exception2: ', $e->getMessage(), "\n";
                        }

                        if ($client2->fault) {
                            echo '<h2>Fault in second query</h2><pre>';
                            print_r($result2);
                            echo '</pre>';
                        } else {
                            $err = $client2->getError();
                            if ($err2) {
                                $errorType = '<h2>Error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
                            } else {
                                //echo $result2['GetMembershipSalesByDateResult']; exit;
                                //RESULT OF APPOINTMENTS IN XML FORMAT
                                try {
                                    $xml = new simpleXml2Array(utf8_encode($result2['GetServiceClassListingResult']), null);
                                    //$xml = new simpleXml2Array($result2['GetAllAppointmentsByDateResult']);
                                } catch (Exception $e) {
                                    echo 'Caught exception2: ', $e->getMessage(), "\n";
                                }

                                $clientsIds = array();
                                $dataArr = array();
                                $allapptIds = array();
                                $allIIds = array();
                                $productQty = array();
                                $transactionIds = array();
                                // echo "<pre>";
                                // print_r($xml->arr);exit;
                                //echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
                                if (isset($xml->arr['ServiceClass'])) {
                                    $productSalesCount = count($xml->arr['ServiceClass']);
                                    foreach ($xml->arr['ServiceClass'] as $appts) { //CONVERTED APPOINTMENTS XML TO ARRAY
                                        echo 'Service Class Listing ' . $appts["iid"][0] . '<br>';

                                        // GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
                                        $query = $this->db->get_where('ob_mill_service_class_listing', array('iid' => $appts['iid'][0],'account_no' => $account_no,'salon_id'=>$salon_id));
                                        $apptsArray = $query->row_array();

                                        $allIIds[] = $appts['iid'][0];

                                       

                                        if (!empty($apptsArray)) {
                                            if (
                                                $apptsArray['iid'] == $appts['iid'][0] &&
                                                $apptsArray['cclassname'] == $appts['cclassname'][0] &&
                                                $apptsArray['cabbreviation'] == $appts['cabbreviation'][0] &&
                                                $apptsArray['ddatedel'] == $appts['ddatedel'][0]
                                                
                                            ) {
                                                continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                            } else {
                                                //UPDATE DATA IN DB 
                                                $employee_data = array(
                                                   
                                                    'iid' => $appts['iid'][0],
                                                    'cclassname' => trim($appts['cclassname'][0]),
                                                    'cabbreviation' => $appts['cabbreviation'][0],
                                                    'ddatedel' => $appts['ddatedel'][0],
                                                    
                                                    'updatedDate' => date("Y-m-d H:i:s"),
                                                    'insert_status' => 'Updated',
                                                );
                                                
                                                $this->db->where('iid', $appts['iid'][0]);
                                                $this->db->where('salon_id', $salon_id);
                                                
                                                // $this->db->where('iclientid', $appts['iclientid'][0]);
                                                $this->db->where('account_no', $account_no);
                                                $res = $this->db->update('ob_mill_service_class_listing', $employee_data);
                                            }
                                        } else { // INSERT APPOINTMENT DATA IN DB 
                                            $employee_data = array(
                                                   'account_no' => $account_no,
                                                   'salon_id' => $salon_id,
                                                    
                                                    'iid' => $appts['iid'][0],
                                                    
                                                    'cclassname' => trim($appts['cclassname'][0]),
                                                    'cabbreviation' => $appts['cabbreviation'][0],
                                                    'ddatedel' => $appts['ddatedel'][0],
                                                'insertedDate' => date("Y-m-d H:i:s"),
                                                'updatedDate' => date("Y-m-d H:i:s"),
                                                'insert_status' => 'Inserted',
                                            );
                                            $res = $this->db->insert('ob_mill_service_class_listing', $employee_data);
                                            $appts_id = $this->db->insert_id();
                                        }
                                    }
                                } if(!empty($allIIds)){
                                    // echo "<pre>";
                                    // print_r($allIIds);
                                    // // var_dump($allIIds);
                                    // echo "<br>";
                                    $whereconditions = array('account_no'=>$account_no,'salon_id'=>$salon_id);
                                    $this->db->select('iid');
                                    $this->db->from('ob_mill_service_class_listing');
                                    $this->db->where('account_no',$account_no);
                                    $this->db->where('salon_id',$salon_id);

                                    $query = $this->db->get();
                                    $allApptsqueryArray = $query->result_array();
                                    //echo "<pre>";
                                    // print_r($allApptsqueryArray);
                                    // exit;
                                    // $allDBIIds = array(); 
                                    foreach($allApptsqueryArray as $allIId)
                                            {
                                                $allDBIIds[] =  $allIId['iid'];
                                            }
                                            //pa($allDBIIds,'db appointments');
                                            error_reporting('0');
                                            $diff_array_appointments = array();
                                            $diff_array_appointments = array_diff($allDBIIds,$allIIds);
                                            //echo 'hello';
                                            // echo "<pre>";
                                            // print_r($diff_array_appointments);
                                            //pa($result,'result',true);
                                           
                                 if(!empty($diff_array_appointments))
                                            {
                                                //echo "hi";
                                                // print_r($diff_array_appointments);
                                                // exit;
                                                foreach($diff_array_appointments as $aptId)
                                                {
                                                    // echo "<pre>";
                                                    // print_r($aptId);
                                                    $this->db->where('iid',$aptId);
                                                    $this->db->where('account_no',$account_no);
                                                    $this->db->where('salon_id',$salon_id);
                                                    $this->db->delete('ob_mill_service_class_listing');
                                                    // echo $this->db->last_query();

                                                    $this->db->where('category_iid',$aptId);
                                                    $this->db->where('account_no',$account_no);
                                                    $this->db->where('salon_id',$salon_id);
                                                    $this->db->delete('ob_mill_services_listing');
                                                    // exit;
                                                    
                                                    pa($aptId,"Update Cancel Apptointment Id -$aptId");
                                                }
                                            }
                                    
                                } else {
                                    echo "No Service Class Listing Data found in Millennium.";
                                }
                            }
                        }
                        $errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
                    }
                }
            }
        }
    }

    function GetServiceListingByClass($account_no="") {
        //echo date("Y-m-d H:i:s");exit;
        error_reporting(E_ALL);
        
        ini_set('display_errors', 1);
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        ini_set("default_socket_timeout",30);
        require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
        require_once('xml2arr.php');
        

        //require_once('salonevolve.pem');
        //TO GET CONFIG DETAILS FROM DB

        //$account_no = 133898042;
        if($account_no!=''){
        $this->db->where('salon_account_id', $account_no);
        }
        $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS)->result_array();
        //print_r($getConfigDetails);exit;

        //if ($getConfigDetails->num_rows > 0) {
        if(!empty($getConfigDetails)){
            foreach ($getConfigDetails as $configDetails) {
                echo "===> Cron Running For Salon Account Number: <b>" . $configDetails['salon_account_id'] . "</b>";
                echo "<br>";
                //SALON ACCOUNT NO
                //$account_no = 1501738222;
                $account_no = $configDetails['salon_account_id'];
                //echo $account_no.'<br>';
                //LOG IN DETAILS FOR MILLENIUM SDK  
                //$path_to_pem = base_url() . "salonevolve.pem";

                $siteIp = $configDetails['mill_ip_address'];
                $MillenniumGuid = $configDetails['mill_guid'];
                $musername = $configDetails['mill_username'];
                $mpassword = $configDetails['mill_password'];
                $url = $configDetails['mill_url'];
                $salon_id = $configDetails['salon_id'];
                //echo $url;exit;
                //MILLENIUM SDK URL AND HEADERS AND GUID    
                //$client = new nusoap_client('http://'.$siteIp.'/sdkadvance/MillenniumSDK.asmx?WSDL','wsdl','','','','');
               // $client = new nusoap_client($url . '?WSDL', 'wsdl', '', '', '', '');

                try {
                    $client = new nusoap_client($url . '?WSDL', 'wsdl', '', '', '', '');
                } catch (Exception $e) {
                    echo 'Caught exception1: ', $e->getMessage(), "\n";
                }

                $client->timeout = 0;
                $client->response_timeout = 30;

                $headers = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
                      <MillenniumGuid>" . $MillenniumGuid . "</MillenniumGuid>
                    </MillenniumInfo>";

                $client->setHeaders($headers);
                $err = $client->getError();
                if ($err) {
                    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
                }
                // LOGIN TO MILLENIUM SDK TO GET SESSION ID----->FIRST STEP
                $param = array('User' => $musername, 'Password' => $mpassword);


                try {
                    $result = $client->call('Logon', array('parameters' => $param), '', '', false, true);
                } catch (Exception $e) {
                    echo 'Caught exception1: ', $e->getMessage(), "\n";
                }


                if ($client->fault) {
                    echo '<h2>Fault</h2><pre>';
                    print_r($result);
                    echo '</pre>';
                } else {

                    $err = $client->getError();
                    if ($err) {
                        $errorType = '<h2>Error</h2><pre>' . $err . '</pre>';  //logToFile('errorFile.log', $errorType);
                    } else {
                        $data = $client->response;
                        preg_match('/<SessionId>(.*?)<\/SessionId>/s', $data, $matches);
                        $SessionId = $matches[0]; //SESSION ID AFTER LOGIN
                        // echo $SessionId;exit;
                        //WE AGAIN CALLING ANOTHER SERVICE, FOR CALLING EVERY SERVICE OTHER THAN LOGON WE NEED THE SESSION ID 
                        $client2 = new nusoap_client($url . '?WSDL', 'wsdl', '', '', '', '');
                        $headers2 = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
                              <MillenniumGuid>" . $MillenniumGuid . "</MillenniumGuid>
                            </MillenniumInfo>
                            <SessionInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
                              <SessionId>" . $SessionId . "</SessionId>
                            </SessionInfo>";
                        $client2->setHeaders($headers2);
                        $err2 = $client2->getError();
                        if ($err2) {
                            $errorType = '<h2>Constructor error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
                        }
                        //GETTING ALL THE APPOINTMENTS FROM SDK------------> STEP 2
                         $whereconditions = array('account_no' => $account_no);
                          $this->db->select('iid,account_no');
                          $this->db->from('ob_mill_service_class_listing');
                          $this->db->where('account_no',$account_no);
                          $query = $this->db->get();
                          $EmployeeListArr = $query->result_array();
                          // echo "<pre>";
                          // print_r($EmployeeListArr);
                          //exit;
                         
                        


                        foreach($EmployeeListArr as $employeeList)
                                {
                                     $employeeId = $employeeList["iid"];
                                     $millMethodParams['XmlIds'] = '<NewDataSet><Ids><Id>'.$employeeId.'</Id></Ids></NewDataSet>';
                                     $millMethodParams['IncludeDeleted'] = 0;
                                     $millMethodParams['IncludeInActive'] = 0;
                                     echo "<pre>";
                                     pa($millMethodParams,'millMethodParams');
                                  



                       try {
                            $result2 = $client2->call('GetServiceListingByClass', array('parameters' => $millMethodParams), '', '', false, true); //METHOD WITH PARAMETERS
                        } catch (Exception $e) {
                            echo 'Caught exception2: ', $e->getMessage(), "\n";
                        }
                        
                        if ($client2->fault) {
                            echo '<h2>Fault in second query</h2><pre>';
                            // print_r($result2);
                            // echo '</pre>';
                        } else {
                            $err = $client2->getError();
                            if ($err2) {
                                $errorType = '<h2>Error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
                            } else {
                                //echo $result2['GetMembershipSalesByDateResult']; exit;
                                //RESULT OF APPOINTMENTS IN XML FORMAT
                                try {
                                    $xml = new simpleXml2Array(utf8_encode($result2['GetServiceListingByClassResult']), null);
                                    //$xml = new simpleXml2Array($result2['GetAllAppointmentsByDateResult']);
                                } catch (Exception $e) {
                                    echo 'Caught exception2: ', $e->getMessage(), "\n";
                                }

                                $clientsIds = array();
                                $dataArr = array();
                                $allapptIds = array();
                                $productQty = array();
                                $transactionIds = array();
                                // echo "<pre>";
                                // echo "hi";
                                // print_r($xml->arr);
                                //exit;
                                //echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
                                if (isset($xml->arr['Services'])) {
                                    //echo "hello";
                                    $productSalesCount = count($xml->arr['Services']);
                                    foreach ($xml->arr['Services'] as $appts) { //CONVERTED APPOINTMENTS XML TO ARRAY
                                        echo 'Service Class Listing ' . $appts["iid"][0] . '<br>';

                                        // GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
                                        $query = $this->db->get_where('ob_mill_services_listing', array('iid' => $appts['iid'][0],'account_no' => $account_no,'salon_id' => $salon_id,'category_iid'=>$employeeId));
                                        $apptsArray = $query->row_array();

                                        

                                       

                                        if (!empty($apptsArray)) {
                                            if (
                                                $apptsArray['iid'] == $appts['iid'][0] &&
                                                $apptsArray['ccode'] == $appts['ccode'][0] &&
                                                $apptsArray['cdescript'] == $appts['cdescript'][0] &&
                                                $apptsArray['ddatedel'] == $appts['ddatedel'][0] &&
                                                $apptsArray['linactive'] == $appts['linactive'][0]
                                                
                                            ) 
                                            {
                                                continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                            } else {
                                                //UPDATE DATA IN DB 
                                                $employee_data = array(
                                                   
                                                    'iid' => $appts['iid'][0],

                                                    'ccode' => trim($appts['ccode'][0]),
                                                    'cdescript' => trim($appts['cdescript'][0]),
                                                    'ddatedel' => $appts['ddatedel'][0],
                                                    'linactive' => $appts['linactive'][0],
                                                    
                                                    'updatedDate' => date("Y-m-d H:i:s"),
                                                    'insert_status' => 'Updated',
                                                );
                                                
                                                $this->db->where('iid', $appts['iid'][0]);
                                                
                                                // $this->db->where('iclientid', $appts['iclientid'][0]);
                                                $this->db->where('account_no', $account_no);
                                                $this->db->where('salon_id', $salon_id);
                                                $this->db->where('category_iid', $employeeId);

                                                $res = $this->db->update('ob_mill_services_listing', $employee_data);
                                            }
                                        } else { // INSERT APPOINTMENT DATA IN DB 
                                            $employee_data = array(
                                                   'account_no' => $account_no,
                                                   'salon_id' => $salon_id,
                                                   'category_iid' => $employeeId,
                                                    
                                                    'iid' => $appts['iid'][0],
                                                    'ccode' => trim($appts['ccode'][0]),
                                                    'cdescript' => trim($appts['cdescript'][0]),
                                                    'ddatedel' => $appts['ddatedel'][0],
                                                    'linactive' => $appts['linactive'][0],
                                                'insertedDate' => date("Y-m-d H:i:s"),
                                                'updatedDate' => date("Y-m-d H:i:s"),
                                                'insert_status' => 'Inserted',
                                            );
                                            $res = $this->db->insert('ob_mill_services_listing', $employee_data);
                                            $appts_id = $this->db->insert_id();
                                     }


                        }

                        

                                    
                                     //foreach ends of Package
                               
                               } else {
                                    echo "No Service Class Listing Data found in Millennium.";
                                }
                            
                            }
                        
                    }
                        $errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
                    }
                }
            }
        }
    }
    
        
    }






    


}