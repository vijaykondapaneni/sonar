<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MillAppointmentsByStaff extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->database();
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
        


    }  

    public function index(){
    
    }  

    function exporting_mill_appointments() {
        ini_set('memory_limit',-1);
        //ini_set('max_execution_time',600);
                    //set_time_limit(0); 
        ini_set('MAX_EXECUTION_TIME', -1);
        $this->DB_ReadOnly->select("AccountNo");
        $this->DB_ReadOnly->group_by("AccountNo");
        //$this->db->where("AccountNo!=",1069669406);
        //$this->db->where("AccountNo",1606999570);
        $get_account_nos = $this->DB_ReadOnly->get(MILL_APPTS_TABLE);
        foreach($get_account_nos->result_array() as $account_nos) {

            $data = array();
            $AppointmentIIDs = array();
            $account_no = $account_nos['AccountNo'];
            $this->DB_ReadOnly->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
            $this->DB_ReadOnly->limit(500);
            $get_appointments = $this->DB_ReadOnly->get(MILL_APPTS_TABLE);
            if($get_appointments->num_rows() > 0) {
                foreach($get_appointments->result_array() as $appointment) {
                    //echo "dfsdfsdf sdfsdf";exit;
                    $temp = array();
                    $AppointmentIIDs[] = $appointment['AppointmentIID'];
                    //if(!in_array($appointment['AppointmentIID'],$data)) {
                        $temp['AppointmentId'] = $appointment['AppointmentIID'];
                        $temp['ApptId'] = $appointment['ApptId'];
                        $temp['SlcStatus'] = $appointment['SlcStatus'];
                        $temp['ClientId'] = $appointment['ClientId'];
                        $temp['AppointmentDate'] = $appointment['AppointmentDate'];
                        $temp['AppointmentTime'] = $appointment['AppointmentTime'];
                        $temp['CreationDate'] = $appointment['CreationDate'];
                        $temp['EmployeeName'] = $appointment['EmployeeName'];
                        $temp['Status'] = $appointment['Status'];
                        $temp['Price'] = $appointment['Price'];
                        $temp['RowOrder'] = $appointment['RowOrder'];
                        $temp['Service'] = $appointment['Service'];
                        $temp['ServiceCategory'] = $appointment['ServiceCategory'];
                        $temp['Tax'] = $appointment['Tax'];
                        $temp['SubTotal'] = $appointment['SubTotal'];
                        $temp['Total'] = $appointment['Total'];
                        $temp['Tender'] = $appointment['Tender'];
                        $temp['SdkStatus'] = $appointment['sdk_status'];
                        $temp['MillCreatedDate'] = $appointment['MillCreatedDate'];
                        $temp['MillLastModifiedDate'] = $appointment['MillLastModifiedDate'];
                        $temp['MillLastChangeDate'] = $appointment['MillLastChangeDate'];
                        $temp['Lprebook'] = $appointment['Lprebook'];
                        $temp['Nstartlen'] = $appointment['Nstartlen'];
                        $temp['Ngaplen'] = $appointment['Ngaplen'];
                        $temp['Nfinishlen'] = $appointment['Nfinishlen'];
                        $temp['CheckedIn'] = $appointment['CheckedIn'];
                        $temp['CheckInTime'] = $appointment['CheckInTime'];
                        $temp['CheckoutTime'] = $appointment['CheckoutTime'];
                        $temp['Noshow'] = $appointment['Noshow'];
                        $temp['iempid'] = $appointment['iempid'];
                        $temp['iservid'] = $appointment['iservid'];
                        $temp['BlockId'] = $appointment['BlockId'];
                        $temp['BlockDescription'] = $appointment['BlockDescription'];
                        $temp['MapNotes'] = $appointment['MapNotes'];
                        $temp['is_checked_in_push_sent'] = $appointment['is_checked_in_push_sent'];
                        $temp['checked_in_push_send_date'] = $appointment['checked_in_push_send_date'];
                        $temp['appointment_log'] = $appointment['appointment_log'];
                        $data[] = $temp;
                    //}
                }
                $post_body = json_encode($data);
                //echo $post_body;exit;
                
                //$input_array = json_decode($post_body,true);
                //print_r($input_array);exit;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_new/importing_mill_appointments_sdk/".$account_no);
                //curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data/importing_mill_appointments/1744665785");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
                $result=curl_exec($ch);
                
                $response = json_decode($result,true);
                //print_r($response);
                if($response['IsTotalSuccess'] == true) {
                    //mark all records as processed
                    $this->db->where_in('AppointmentIID', $AppointmentIIDs);
                    $this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
                    $update_array = array(
                         "IsProcessed" => 1
                    );
                    $processed = $this->db->update(MILL_APPTS_TABLE,$update_array);
                    //echo $this->db->last_query();exit;
                } else if($response['SystemError'] == "") {
                    if(!empty($response['FailedIdList'])){
                        foreach ($response['FailedIdList'] as $FailedIdList) {
                            $failure_array[] = $FailedIdList['id'];
                        }
                        
                        //mark only successful records as processed
                        $this->db->where_in('AppointmentIID', $AppointmentIIDs);
                        $this->db->where_not_in('AppointmentIID', $failure_array);
                        $this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
                        $update_array = array(
                            "IsProcessed" => 1
                        );
                        $processed = $this->db->update(MILL_APPTS_TABLE,$update_array);
                        //echo "Successfully Updated for AccountNo: ".$account_no."\n";
                    }   
                    //echo $this->db->last_query();exit;
                }
            } else {
                //echo "No Data for AccountNo: ".$account_no."\n";
            }
        }
    }
    
    //It exports only updated clients like appointments
    function exporting_mill_clients() {
        ini_set('memory_limit',-1);
        ini_set('max_execution_time',600);
        $this->DB_ReadOnly->select("AccountNo");
        $this->DB_ReadOnly->group_by("AccountNo");
        //$this->db->where("AccountNo!=",1069669406);
        //$this->db->where("AccountNo",1974787526);
        //$this->db->where("AccountNo",1606999570);
        $get_account_nos = $this->DB_ReadOnly->get(MILL_CLIENTS_TABLE);
        foreach($get_account_nos->result_array() as $account_nos) {
            $data = array();
            $ClientIds = array();
            $account_no = $account_nos['AccountNo'];
            $this->DB_ReadOnly->limit(500);
            $this->DB_ReadOnly->where(array("AccountNo" => $account_no, "IsProcessed" => 0, "ClientId!=" => ''));
            $get_appointments = $this->DB_ReadOnly->get(MILL_CLIENTS_TABLE);
            if($get_appointments->num_rows() > 0) {
                foreach($get_appointments->result_array() as $appointment) {
                    $temp = array();
                    $ClientIds[] = $appointment['ClientId']; 
                    $temp['ClientId'] = $appointment['ClientId'];
                    $temp['Email'] = $appointment['Email'];
                    $temp['Zip'] = $appointment['Zip'];
                    $temp['Phone'] = $appointment['Phone'];
                    $temp['Name'] = $appointment['Name'];
                    $temp['Dob'] = $appointment['Dob'];
                    $temp['GiftCardBalance'] = $appointment['GiftCardBalance'];
                    $temp['LoyaltyPoints'] = $appointment['LoyaltyPoints'];
                    $temp['LastReceiptAmount'] = $appointment['LastReceiptAmount'];
                    $temp['Sex'] = $appointment['Sex'];
                    $temp['Mobile'] = $appointment['Mobile'];
                    $temp['MobileAreaCode'] = $appointment['MobileAreaCode'];
                    $temp['BusinessPhoneNumber'] = $appointment['BusinessPhoneNumber'];
                    $temp['BusinessAreaCode'] = $appointment['BusinessAreaCode'];
                    $temp['clientFirstVistedDate'] = $appointment['clientFirstVistedDate'];
                    $temp['clientLastVistedDate'] = $appointment['clientLastVistedDate'];
                    $temp['optin_email'] = $appointment['opted_in_email'];
                    $temp['optin_sms'] = $appointment['opted_in_sms'];
                    $temp['reward_points'] = $appointment['reward_points'];
                    $data[] = $temp;
                }
                $post_body = json_encode($data);
                
                //$input_array = json_decode($post_body,true);
                //print_r($input_array);exit;
                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_new/importing_mill_clients_sdk/".$account_no);
                //curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data/importing_mill_clients/1744665785");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
                $result=curl_exec($ch);
                $response = json_decode($result,true);
                //print_r($response);
                if($response['IsTotalSuccess'] == true) {
                    //mark all records as processed
                    $this->db->where_in('ClientId', $ClientIds);
                    $this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
                    $update_array = array(
                         "IsProcessed" => 1
                    );
                    $processed = $this->db->update(MILL_CLIENTS_TABLE,$update_array);
                } else if($response['SystemError'] == "") {
                    if(!empty($response['FailedIdList'])){
                        foreach ($response['FailedIdList'] as $FailedIdList) {
                            $failure_array[] = $FailedIdList['id'];
                        }
                        //mark only successful records as processed
                        $this->db->where_in('ClientId', $ClientIds);
                        $this->db->where_not_in('ClientId', $failure_array);
                        $this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
                        $update_array = array(
                            "IsProcessed" => 1
                        );
                        $processed = $this->db->update(MILL_CLIENTS_TABLE,$update_array);
                        //echo $this->db->last_query()."\n";
                        //echo "Successfully Updated for AccountNo: ".$account_no."\n";
                    }   
                }
                
            } else {
                //echo "No Data for AccountNo: ".$account_no."\n";
            }
        }
    }

    function getAppointments($account_no=0,$empid=0){
        //echo "dfsdf";exit;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('memory_limit', '-1');
        require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
        require_once('xml2arr.php');
        //require_once('Wsexport_mill_data.php');
        //require_once('salonevolve.pem');
        //TO GET CONFIG DETAILS FROM DB

        $data = array();
        $dayRangeType = "Oneday";

        /*$startDate = "2018-04-28";//date("Y-m-d");
        $endDate = "2018-04-28";//date("Y-m-d");*/

        $startDate = date("Y-m-d");//date("Y-m-d");
        $endDate = date("Y-m-d");

        if(!empty($account_no) && !empty($empid)){
            $this->db->where('salon_account_id', $account_no);
            $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
        } else {
            $dataArr["msg"] = "Account Number and Employee Id is empty";
            $dataArr["status"] = false;
            $dataArray["data"] = $dataArr;
            echo json_encode($dataArray);
            exit;
        }
        
        
        //print_r($getConfigDetails->result_array());exit;

        //if($getConfigDetails->num_rows>0)
        if(!empty($getConfigDetails->row_array()))
        {
            $configDetails = $getConfigDetails->row_array();
            
            //SALON ACCOUNT NO
            //$account_no = 1501738222;
            $account_no = $configDetails['salon_account_id'];
            //echo $account_no.'<br>';
            //LOG IN DETAILS FOR MILLENIUM SDK  
            //$path_to_pem    =   base_url()."salonevolve.pem";

            $siteIp         =   $configDetails['mill_ip_address'];
            $MillenniumGuid =   $configDetails['mill_guid'];
            $musername      =   $configDetails['mill_username'];
            $mpassword      =   $configDetails['mill_password'];
            $url = $configDetails['mill_url'];
            $xmlIds = '';

            /*$siteIp           =   "50.192.249.193";
            $MillenniumGuid =   "01017B56-IB42-7058-4459-5066B7FA1663";
            $musername      =   "SDKTEST";
            $mpassword      =   "sdk1234";*/

            //MILLENIUM SDK URL AND HEADERS AND GUID    
            //$client = new nusoap_client('http://'.$siteIp.'/sdkadvance/MillenniumSDK.asmx?WSDL','wsdl','','','','');

            try{
                $client = new nusoap_client($url.'?WSDL','wsdl','','','','');
            } catch (Exception $e) {
                $dataArr["msg"] = $e->getMessage();
                $dataArr["status"] = false;
                $dataArray["data"] = $dataArr;
                echo json_encode($dataArray);
                exit;
            }
            


            $headers = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
              <MillenniumGuid>".$MillenniumGuid."</MillenniumGuid>
            </MillenniumInfo>";

            $client->setHeaders($headers);
            $err = $client->getError();
            if ($err) {
                //echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            }
            // LOGIN TO MILLENIUM SDK TO GET SESSION ID----->FIRST STEP
            $param = array('User' => $musername,'Password' => $mpassword);
            try{
                $result = $client->call('Logon', array('parameters' => $param), '', '', false, true);
            }
            catch (Exception $e) {
                //echo 'Caught exception1: ',  $e->getMessage(), "\n";
                $dataArr["msg"] = $e->getMessage();
                $dataArr["status"] = false;
                $dataArray["data"] = $dataArr;
                echo json_encode($dataArray);
                exit;
            }
            

            if ($client->fault) {
                /*echo '<h2>Fault</h2><pre>';
                print_r($result);
                echo '</pre>';*/
                $dataArr["msg"] = "There is issue in the sdk";
                $dataArr["status"] = false;
                $dataArray["data"] = $dataArr;
                echo json_encode($dataArray);
                exit;

            } else {
                
                $err = $client->getError();
                if ($err) {
                    //$errorType = '<h2>Error</h2><pre>' . $err . '</pre>';  //logToFile('errorFile.log', $errorType);
                    $dataArr["msg"] = $err;
                    $dataArr["status"] = false;
                    $dataArray["data"] = $dataArr;
                    echo json_encode($dataArray);
                    exit;

                } else {
                    $data = $client->response;
                    preg_match('/<SessionId>(.*?)<\/SessionId>/s', $data, $matches);
                    $SessionId = $matches[0]; //SESSION ID AFTER LOGIN
                    
                    //WE AGAIN CALLING ANOTHER SERVICE, FOR CALLING EVERY SERVICE OTHER THAN LOGON WE NEED THE SESSION ID 
                    

                    try{
                        $client2 = new nusoap_client($url.'?WSDL', 'wsdl','','','','');
                    } catch (Exception $e) {
                        $dataArr["msg"] = $e->getMessage();
                        $dataArr["status"] = false;
                        $dataArray["data"] = $dataArr;
                        echo json_encode($dataArray);
                        exit;
                    }


                    $headers2 = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
                      <MillenniumGuid>".$MillenniumGuid."</MillenniumGuid>
                    </MillenniumInfo>
                    <SessionInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
                      <SessionId>".$SessionId."</SessionId>
                    </SessionInfo>";
                    $client2->setHeaders($headers2);
                    $err2 = $client2->getError();
                    if ($err2) {
                        //$errorType = '<h2>Constructor error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
                        $dataArr["msg"] = $err2;
                        $dataArr["status"] = false;
                        $dataArray["data"] = $dataArr;
                        echo json_encode($dataArray);
                        exit;
                    }
                    //GETTING ALL THE APPOINTMENTS FROM SDK------------> STEP 2
                    /*if(!empty($startDate) && !empty($endDate))
                    {
                        $param2 = array('StartDate' => $startDate,'EndDate' => $endDate); //parameters
                    }
                    else
                    {
                        $currentDate = date("Y-m-d");
                        $param2 = array('StartDate' => $currentDate,'EndDate' => $currentDate); //parameters
                        
                    }*/
                    
                    $param2 = array('XmlIds' => '<NewDataSet><Ids><Id>'.$empid.'</Id></Ids></NewDataSet>','StartDate' => $startDate,'EndDate' => $endDate); //parameters

                    try{
                        $result2 = $client2->call('GetAppointmentsByDate', array('parameters' => $param2), '', '', false, true);//METHOD WITH PARAMETERS
                    }
                    catch (Exception $e) {
                        //echo 'Caught exception2: ',  $e->getMessage(), "\n";
                        $dataArr["msg"] = $e->getMessage();
                        $dataArr["status"] = false;
                        $dataArray["data"] = $dataArr;
                        echo json_encode($dataArray);
                        exit;
                    }
                    
                    if ($client2->fault) {
                        /*echo '<h2>Fault in second query</h2><pre>';
                        print_r($result2);
                        echo '</pre>';*/
                        $dataArr["msg"] = "There is a issue in the sdk";
                        $dataArr["status"] = false;
                        $dataArray["data"] = $dataArr;
                        echo json_encode($dataArray);
                        exit;
                    } else {
                        $err = $client2->getError();
                        if ($err) {
                            //$errorType = '<h2>Error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
                            $dataArr["msg"] = $err;
                            $dataArr["status"] = false;
                            $dataArray["data"] = $dataArr;
                            echo json_encode($dataArray);
                            exit;
                        } else {
                            //echo $result2['GetAppointmentsByDateResult']; exit;
                            //RESULT OF APPOINTMENTS IN XML FORMAT
                            try{
                                $xml = new simpleXml2Array(utf8_encode($result2['GetAppointmentsByDateResult']),null);
                                //$xml = new simpleXml2Array($result2['GetClientsByLastVisitResult']);
                            }
                            catch (Exception $e) {
                                //echo 'Caught exception2: ',  $e->getMessage(), "\n";
                                $dataArr["msg"] = $e->getMessage();
                                $dataArr["status"] = false;
                                $dataArray["data"] = $dataArr;
                                echo json_encode($dataArray);
                                exit;
                            }
                            
                            $clientsIds = array();
                            $dataArr = array();
                            $allapptIds = array();

                            //echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
                            if(isset($xml->arr['Apptointments']) && !empty($xml->arr['Apptointments']))
                            {
                                
                                //echo "<pre>";print_r($xml->arr['Apptointments']);exit;
                                if(!empty($xml->arr['Apptointments'])){
                                    foreach ($xml->arr['Apptointments'] as $key => $appts) {
                                        /*echo "<pre>";
                                        print_r($millAppts);*/

                                        $date = strtotime($appts['ddate'][0]); 
                                        $appointmentDate = date("n/j/Y",$date); 
                                        $sdk_status = trim($appts['cconfirmation'][0]);
                                        if(!empty($appts['torigdatetime'][0])){
                                            $salonCreatedDate = explode("T",$appts['torigdatetime'][0]);
                                            $salonCreatedDateConcat = $salonCreatedDate[0]." ".$salonCreatedDate[1];
                                        } else {
                                            $salonCreatedDateConcat = "";
                                        }

                                        if(!empty($appts['tlastmodified'][0])){
                                            $salonModifiedDate = explode("T",$appts['tlastmodified'][0]);
                                            $salonModifiedDateConcat = $salonModifiedDate[0]." ".$salonModifiedDate[1];
                                        } else {
                                            $salonModifiedDateConcat = "";
                                        }

                                        if(!empty($appts['tlastchg'][0])){
                                            $salonLastChangeDate = explode("T",$appts['tlastchg'][0]);
                                            $salonLastChangeDateConcat = $salonLastChangeDate[0]." ".$salonLastChangeDate[1];
                                        } else {
                                            $salonLastChangeDateConcat = "";
                                        }
                                        
                                       /* if($appts['ccheckintime'][0]){
                                         $ccheckintime = $appts['ccheckintime'][0];
                                        }else{
                                         $ccheckintime = $appts['ccheckintime'];    
                                        }
                                        
                                        if(is_array($appts['ccheckouttime'][0])){
                                         $ccheckouttime = $appts['ccheckouttime'][0];
                                        }else{
                                         $ccheckouttime = $appts['ccheckouttime'];  
                                        }
                                        
                                        if(is_array($appts['cblockdescr'][0])){
                                         $cblockdescr = $appts['cblockdescr'][0];
                                        }else{
                                         $cblockdescr = $appts['cblockdescr'];  
                                        }

                                        if(is_array($appts['ctimeofday'][0])){
                                         $ctimeofday = $appts['ctimeofday'][0];
                                        }else{
                                         $ctimeofday = $appts['ctimeofday'];    
                                        }
                                        $appts['ctimeofday'] = $ctimeofday;*/

                                        $ccheckintime = !empty($appts['ccheckintime'][0]) ? $appts['ccheckintime'][0]:"";

                                        $ccheckouttime = !empty($appts['ccheckouttime'][0]) ? $appts['ccheckouttime'][0]:"";

                                        $cblockdescr = !empty($appts['cblockdescr'][0]) ? $appts['cblockdescr'][0]:"";

                                        $mappnotes = !empty($appts['mappnotes'][0]) ? $appts['mappnotes'][0]:"";                                               
                                        // GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
                                        $query = $this->db->get_where(MILL_APPTS_TABLE, array('AppointmentIID' => $appts['iid'][0],'AccountNo' => $account_no));
                                        $apptsArray = $query->row_array();
                                        if(!empty($apptsArray))
                                        {
                                            if($apptsArray['ApptId']==$appts['iapptid'][0]  
                                                && $apptsArray['AccountNo']==$account_no 
                                                && $apptsArray['AppointmentTime']==$appts['ctimeofday'][0] 
                                                && $apptsArray['EmployeeName']==$appts['cempname'][0] 
                                                && $apptsArray['ClientId']==$appts['iclientid'][0] 
                                                && $apptsArray['Service']==$appts['cservice'][0]
                                                && $apptsArray['AppointmentDate']==$appointmentDate 
                                                && $apptsArray['TConfirmed']==$appts['tconfirmed'][0]
                                                && $apptsArray['sdk_status']==$sdk_status 
                                                && $apptsArray['MillCreatedDate']==$salonCreatedDateConcat
                                                && $apptsArray['MillLastModifiedDate']==$salonModifiedDateConcat 
                                                && $apptsArray['MillLastChangeDate']==$salonLastChangeDateConcat 
                                                && $apptsArray['Lprebook']==$appts['lprebook'][0]
                                                && $apptsArray['Nstartlen']==$appts['nstartlen'][0]
                                                && $apptsArray['Ngaplen']==$appts['ngaplen'][0]
                                                && $apptsArray['Nfinishlen']==$appts['nfinishlen'][0]
                                                && $apptsArray['CheckedIn']==$appts['lcheckedin'][0]
                                                && $apptsArray['CheckInTime']==$ccheckintime
                                                && $apptsArray['CheckoutTime']==$ccheckouttime
                                                && $apptsArray['Noshow']==$appts['lnoshow'][0]
                                                && $apptsArray['iempid']==$appts['iempid'][0]
                                                && $apptsArray['iservid']==$appts['iservid'][0]
                                                && $apptsArray['BlockId']==$appts['iblockid'][0]
                                                && $apptsArray['BlockDescription']==$cblockdescr
                                                && $apptsArray['MapNotes']==$mappnotes
                                                )
                                            {
                                                //continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                            }
                                            else
                                                {
                                                    //UPDATE DATA IN DB 
                                                    $date = strtotime($appts['ddate'][0]); 
                                                    $appointmentDate = date("n/j/Y",$date); 
                                                    $sdk_status = trim($appts['cconfirmation'][0]); //trim spaces in cconfirmation
                                                    $appointments_data = array(
                                                        'ApptId' => $appts['iapptid'][0],
                                                        'SlcStatus' => 'Updated',
                                                        'AppointmentDate' => $appointmentDate, //appointment Date
                                                        'dayRange' => $dayRangeType,
                                                        'AppointmentTime' => $appts['ctimeofday'][0],
                                                        'EmployeeName' => $appts['cempname'][0],
                                                        'ClientId' => $appts['iclientid'][0],
                                                        'TConfirmed' => $appts['tconfirmed'][0],
                                                        'Service' => $appts['cservice'][0],
                                                        'ModifiedDate' => date("Y-m-d H:i:s"),
                                                        'IsProcessed' => 0,
                                                        'IsProcessedDM' => 0,
                                                        'sdk_status' => $sdk_status,
                                                        'MillCreatedDate' => $salonCreatedDateConcat,
                                                        'MillLastModifiedDate' => $salonModifiedDateConcat,
                                                        'MillLastChangeDate' => $salonLastChangeDateConcat,
                                                        'Lprebook' => $appts['lprebook'][0],
                                                        'Nstartlen' => $appts['nstartlen'][0],
                                                        'Ngaplen' => $appts['ngaplen'][0],
                                                        'Nfinishlen' => $appts['nfinishlen'][0],
                                                        'CheckedIn' => $appts['lcheckedin'][0],
                                                        'CheckInTime' => $ccheckintime,
                                                        'CheckoutTime' => $ccheckouttime,
                                                        'Noshow' => $appts['lnoshow'][0],
                                                        'iempid' => $appts['iempid'][0],
                                                        'iservid' => $appts['iservid'][0],
                                                        'BlockId' => $appts['iblockid'][0],
                                                        'BlockDescription' => $cblockdescr,
                                                        'MapNotes' => $mappnotes,
                                                    );
                                                    $this->db->where('AppointmentIID',$appts['iid'][0]);
                                                    $this->db->where('AccountNo',$account_no);
                                                    $res = $this->db->update(MILL_APPTS_TABLE, $appointments_data);
                                                }
                                        }
                                        else{
                                            $date = strtotime($appts['ddate'][0]); 
                                            $appointmentDate = date("n/j/Y",$date); 
                                            $sdk_status = trim($appts['cconfirmation'][0]); //trim spaces in cconfirmation
                                            $appointments_data = array(
                                                'AppointmentIID' => $appts['iid'][0],
                                                'SalonId' => 0,
                                                'AccountNo' => $account_no,
                                                'ApptId' => $appts['iapptid'][0],
                                                'SlcStatus' => 'Inserted',
                                                'AppointmentDate' => $appointmentDate, //appointment Date
                                                'dayRange' => $dayRangeType,
                                                'AppointmentTime' => $appts['ctimeofday'][0],
                                                'EmployeeName' => $appts['cempname'][0],
                                                'WinServiceTypeId' => 0,
                                                'ClientId' => $appts['iclientid'][0],
                                                'TConfirmed' => $appts['tconfirmed'][0],
                                                'Service' => $appts['cservice'][0],
                                                'Status' => 1,
                                                'IsProcessed' => 0,
                                                'IsProcessedDM' => 0,
                                                'CrateatedDate' => date("Y-m-d H:i:s"),
                                                'ModifiedDate' => date("Y-m-d H:i:s"),
                                                'sdk_status' => $sdk_status,
                                                'MillCreatedDate' => $salonCreatedDateConcat,
                                                'MillLastModifiedDate' => $salonModifiedDateConcat,
                                                'MillLastChangeDate' => $salonLastChangeDateConcat,
                                                'Lprebook' => $appts['lprebook'][0],
                                                'Nstartlen' => $appts['nstartlen'][0],
                                                'Ngaplen' => $appts['ngaplen'][0],
                                                'Nfinishlen' => $appts['nfinishlen'][0],
                                                'CheckedIn' => $appts['lcheckedin'][0],
                                                'CheckInTime' => $ccheckintime,
                                                'CheckoutTime' => $ccheckouttime,
                                                'Noshow' => $appts['lnoshow'][0],
                                                'iempid' => $appts['iempid'][0],
                                                'iservid' => $appts['iservid'][0],
                                                'BlockId' => $appts['iblockid'][0],
                                                'BlockDescription' => $cblockdescr,
                                                'MapNotes' => $mappnotes,
                                            );
                                            $res = $this->db->insert(MILL_APPTS_TABLE, $appointments_data);
                                            //$appts_id = $this->db->insert_id();
                                        }
                                        $clientsId = $appts['iclientid'][0];
                                        
                                        $param3 = array('ClientId' => $clientsId); //parameters
                                        if(!in_array($clientsId,$clientsIds) && $clientsId != "-999"){
                                            try{
                                                $result3 = $client2->call('GetClient', array('parameters' => $param3), '', '', false, true);//METHOD WITH PARAMETERS
                                            }
                                            catch (Exception $e) {
                                                //echo 'Caught exception2: ',  $e->getMessage(), "\n";
                                                $dataArr["msg"] = $e->getMessage();
                                                $dataArr["status"] = false;
                                                $dataArray["data"] = $dataArr;
                                                echo json_encode($dataArray);
                                                exit;
                                            }
                                            /*echo "<pre>";
                                                        print_r($result3);
                                                        exit();*/
                                            if ($client2->fault) {
                                                /*echo '<h2>Fault in second method</h2><pre>';
                                                print_r($result3);
                                                echo '</pre>';*/
                                                $dataArr["msg"] = "There is issue in the sdk";
                                                $dataArr["status"] = false;
                                                $dataArray["data"] = $dataArr;
                                                echo json_encode($dataArray);
                                                exit;
                                            }else{
                                                $err3 = $client2->getError();
                                                if ($err3) {
                                                    //$errorType = '<h2>Error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
                                                    $dataArr["msg"] = $err3;
                                                    $dataArr["status"] = false;
                                                    $dataArray["data"] = $dataArr;
                                                    echo json_encode($dataArray);
                                                    exit;
                                                } else {
                                                    $query = $this->db->get_where(MILL_CLIENTS_TABLE, array('ClientId' => $result3['GetClientResult']['Id'],'AccountNo' => $account_no));
                                                    $array = $query->row_array();
                                                    if(!empty($result3['GetClientResult']['FirstVisitDate']))
                                                    {
                                                        $explodeFirstVisitDate = explode("T",$result3['GetClientResult']['FirstVisitDate']);
                                                        $firstVisitDate = $explodeFirstVisitDate[0]." ".$explodeFirstVisitDate[1];
                                                    }
                                                    else
                                                    {
                                                        $firstVisitDate = "";
                                                    }

                                                    if(!empty($result3['GetClientResult']['LastVisitDate']))
                                                    {
                                                        $explodeLastVisitDate = explode("T",$result3['GetClientResult']['LastVisitDate']);
                                                        $lastVisitDate = $explodeLastVisitDate[0]." ".$explodeLastVisitDate[1];
                                                    }
                                                    else
                                                    {
                                                        $lastVisitDate = "";
                                                    }

                                                    if(!empty($result3['GetClientResult']['ConfirmViaSMS'])&& $result3['GetClientResult']['ConfirmViaSMS']=='true')
                                                    {
                                                        $optedInSms = 1;
                                                    }
                                                    else
                                                    {
                                                        $optedInSms = 2;
                                                    }

                                                    if(!empty($result3['GetClientResult']['ConfirmViaEmail']) && $result3['GetClientResult']['ConfirmViaEmail']=='true')
                                                    {
                                                        $optedInEMail = 1;
                                                    }
                                                    else
                                                    {
                                                        $optedInEMail = 2;
                                                    }

                                                    if(!empty($array))
                                                    {
                                                        if($array['Email']==$result3['GetClientResult']['EmailAddress'] && $array['AccountNo']== $account_no 
                                                            && $array['Zip']==$result3['GetClientResult']['ZipCode'] && $array['Phone']==$result3['GetClientResult']['HomeAreaCode'].$result3['GetClientResult']['HomePhoneNumber']
                                                            && $array['Name']==$result3['GetClientResult']['FirstName'].' '.$result3['GetClientResult']['LastName'] && $array['Dob']==$result3['GetClientResult']['Birthday']
                                                            && $array['Sex']==$result3['GetClientResult']['Sex'] && $array['Mobile']==$result3['GetClientResult']['CellPhoneNumber']
                                                            && $array['MobileAreaCode']==$result3['GetClientResult']['CellAreaCode'] && $array['BusinessPhoneNumber']==$result3['GetClientResult']['BusinessPhoneNumber']
                                                            && $array['BusinessAreaCode']==$result3['GetClientResult']['BusinessAreaCode'] 
                                                            && $array['clientFirstVistedDate']==$firstVisitDate 
                                                            && $array['clientLastVistedDate']==$lastVisitDate 
                                                            && $array['opted_in_email']==$optedInEMail
                                                            && $array['opted_in_sms']==$optedInSms
                                                            )
                                                        {
                                                            //continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                                        } 
                                                        else
                                                        {
                                                            $clients_data = array(
                                                                    
                                                                'Email' => $result3['GetClientResult']['EmailAddress'],
                                                                'Name' => $result3['GetClientResult']['FirstName'].' '.$result3['GetClientResult']['LastName'],
                                                                'Phone' => $result3['GetClientResult']['HomeAreaCode'].$result3['GetClientResult']['HomePhoneNumber'],
                                                                'Dob' => $result3['GetClientResult']['Birthday'],
                                                                'Zip' => $result3['GetClientResult']['ZipCode'],
                                                                'ModifiedDate' => date("Y-m-d H:i:s"),
                                                                'IsProcessed' => 0,
                                                                'IsProcessedDM' => 0,
                                                                'Sex' => $result3['GetClientResult']['Sex'],
                                                                'Mobile' => $result3['GetClientResult']['CellPhoneNumber'],
                                                                'MobileAreaCode' => $result3['GetClientResult']['CellAreaCode'],
                                                                'BusinessPhoneNumber' => $result3['GetClientResult']['BusinessPhoneNumber'],
                                                                'BusinessAreaCode' => $result3['GetClientResult']['BusinessAreaCode'],
                                                                'clientFirstVistedDate' => $firstVisitDate,
                                                                'clientLastVistedDate' => $lastVisitDate,
                                                                'opted_in_email' => $optedInEMail,
                                                                'opted_in_sms' => $optedInSms
                                                            );
                                                            $this->db->where('ClientId',$result3['GetClientResult']['Id']);
                                                            $this->db->where('AccountNo',$account_no);
                                                            $this->db->update(MILL_CLIENTS_TABLE, $clients_data);
                                                            
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $clients_data = array(
                                                            'ClientId' => $result3['GetClientResult']['Id'],
                                                            'AccountNo' => $account_no,
                                                            'SalonId' => 0,
                                                            'Email' => $result3['GetClientResult']['EmailAddress'],
                                                            'Name' => $result3['GetClientResult']['FirstName'].' '.$result3['GetClientResult']['LastName'],
                                                            'Phone' => $result3['GetClientResult']['HomeAreaCode'].$result3['GetClientResult']['HomePhoneNumber'],
                                                            'Zip' => $result3['GetClientResult']['ZipCode'],
                                                            'Dob' => $result3['GetClientResult']['Birthday'],
                                                            'CreatedDate' => date("Y-m-d H:i:s"),
                                                            'ModifiedDate' => date("Y-m-d H:i:s"),
                                                            'IsProcessed' => 0,
                                                            'IsProcessedDM' => 0,
                                                            'Sex' => $result3['GetClientResult']['Sex'],
                                                            'Mobile' => $result3['GetClientResult']['CellPhoneNumber'],
                                                            'MobileAreaCode' => $result3['GetClientResult']['CellAreaCode'],
                                                            'BusinessPhoneNumber' => $result3['GetClientResult']['BusinessPhoneNumber'],
                                                            'BusinessAreaCode' => $result3['GetClientResult']['BusinessAreaCode'],
                                                            'clientFirstVistedDate' => $firstVisitDate,
                                                            'clientLastVistedDate' => $lastVisitDate,
                                                            'opted_in_email' => $optedInEMail,
                                                            'opted_in_sms' => $optedInSms
                                                        );
                                                        $res = $this->db->insert(MILL_CLIENTS_TABLE, $clients_data);
                                                        $clients_id = $this->db->insert_id();
                                                    }
                                                }
                                            }
                                        }                                    }
                                }

                                //base_url()."Wsexport_mill_data/exporting_mill_appointments";
                                //echo "<br>";
                                //base_url()."Wsexport_mill_data/exporting_mill_clients";

                                $this->exporting_mill_appointments();
                                $this->exporting_mill_clients();


                                //$Wsexport_mill_data = new Wsexport_mill_data();
                                //$products->_Wsexport_mill_data_exporting_mill_appointments();
                                //$products->_Wsexport_mill_data_exporting_mill_clients();
                                //Wsexport_mill_data::exporting_mill_appointments();
                                //sexport_mill_data::exporting_mill_clients();
                                
                                $dataArr["msg"] = "Data refreshed successfully";
                                $dataArr["status"] = true;
                                $dataArray["data"] = $dataArr;
                                echo json_encode($dataArray);
                                exit;
                                
                                //EMAIL SENDING ENDS
                            } //if condition, if clients are not found in MILL SDK
                            else
                            {
                                //echo "No data found in MILL SDK";
                                $dataArr["msg"] = "Data not found in the sdk.";
                                $dataArr["status"] = false;
                                $dataArray["data"] = $dataArr;
                                echo json_encode($dataArray);
                                exit;
                            }
                        }
                    }
                    //$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
                }
            }
        }
   
    }
}

