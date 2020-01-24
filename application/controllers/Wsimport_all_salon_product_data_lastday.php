<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}

class Wsimport_all_salon_product_data_lastday extends CI_Controller {

    // Define constant as per value;
    CONST INSERTED = 0;
    CONST UPDATED = 1;
    
    public $salonAccountId;
    public $pemFilePath;
    public $salonMillIp;
    public $salonMillGuid;
    public $salonMillUsername;
    public $salonMillPassword;
    public $salonMillSdkUrl;
    public $startDate;
    public $endDate;
    public $millResponseXml;
    
    public $transactionIds;
	/**
       AUTHOR: Subbu
       DESCRIPTION: THIS CLASS IS FOR IMPORT SALON PRODUCT DATA
	**/
    function __construct() {
			parent::__construct();
			$this->load->model('Common_model');
			$this->load->model('Productsalesimport_model');          
		}
    
    /**
     * Default Index Fn
     */    
	public function index() {print "Test";}
    
    /**
     * @author MAK<anas-php@webappclouds.com>
     * @param type $startDate
     * @param type $endDate
     * @param type $account_no
     */   
    function getProductSales($salon_code_encode="")
    {
        if($salon_code_encode!=''){
                $salon_code = salonWebappCloudDe($salon_code_encode);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                $account_no = $getConfigDetails[0]['salon_account_id'];
            }
        $dates = date('Y-m-d');
        $startDate= $endDate = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $dates) ) ));
        // GET SALON CONFIG DETAILS
        $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
        
        if($getConfigDetails->num_rows()>0)
        {
           foreach($getConfigDetails->result_array() as $configDetails)
            {
                pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
               
                //LOG IN DETAILS FOR MILLENIUM SDK	
                $this->salonAccountId = $configDetails['salon_account_id'];
                $this->pemFilePath	 =	base_url()."salonevolve.pem";
                $this->salonMillIp	 =	$configDetails['mill_ip_address'];
                $this->salonMillGuid =	$configDetails['mill_guid'];
                $this->salonMillUsername =	$configDetails['mill_username'];
                $this->salonMillPassword =	$configDetails['mill_password'];
                $this->salonMillSdkUrl = $configDetails['mill_url'];
                $this->startDate = (!empty($startDate)) ? $startDate : date("Y-m-d");
                $this->endDate   = (!empty($endDate)) ? $endDate : date("Y-m-d");

                // Database Log
                $log['AccountNo'] = $configDetails['salon_account_id'];
                $log['salon_id'] = $configDetails['salon_id'];
                $log['StartingTime'] = date('Y-m-d H:i:s');
                $log['whichCron'] = 'getProductSales';
                //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_salon_product_data_lastday/getProductSales/'.$salon_code_encode;
                $log['CronType'] = 0;
                $log['id'] = 0;
                $log_id = $this->Common_model->saveMillCronLogs($log);
                
                //MILLENIUM SDK REQUEST FOR SOAP CALL	
                $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                
                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                
                pa($this->millResponseSessionId,'Session');
                if($this->millResponseSessionId){
                $millMethodParams = array('StartDate' => $this->startDate,'EndDate' => $this->endDate,'IncludeVoided' => 0);
                $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetProductSales',$millMethodParams);
                //pa($this->millResponseXml['ProductSales'],'productSales');                     
                if(isset($this->millResponseXml['ProductSales']))
                {
                    
                    $productSalesCount = count($this->millResponseXml['ProductSales']);
                    pa($this->millResponseXml['ProductSales'],'ProductSales'); 
                    if(!isset($this->millResponseXml['ProductSales'][0]))  
                        {
                            $tempArr = $this->millResponseXml['ProductSales'];
                            unset($this->millResponseXml['ProductSales']);
                            $this->millResponseXml['ProductSales'][0] = $tempArr;
                        }

                    foreach($this->millResponseXml['ProductSales'] as $pSales) 
                    {
                        pa($pSales,'pSales');
                        pa('',"Product Sales--".$pSales["cinvoiceno"],false); //DEBUGMSG
                        array_walk($pSales, function (&$value,&$key) { 
                                    $value = trim($value);
                                    if($key == 'nprice' || $key == 'nquantity')
                                     $value = number_format($value, 4, '.', '');
                         });
                        $pSales['tdatetime'] = (!empty($pSales['tdatetime'])) ? explode("T",$pSales['tdatetime'])[0] : '';
                        $this->transactionIds[] = $pSales["itransdetailid"]; //COLLECT TRANS ID TO PERFORM DELETE OPERATION BELOW  

                        pa('',"GetProductSales");
                        // GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID                                        
                        $whereCondition = array('iempid' => $pSales['iempid'],'itransdetailid' => $pSales['itransdetailid'],'cinvoiceno' => $pSales['cinvoiceno'],'iclientid' => $pSales['iclientid'],'account_no' => $this->salonAccountId);
                        $arrProductSales = $this->Productsalesimport_model
                                           ->compareMillProductsSales($whereCondition)
                                           ->row_array();
                        pa('',"DBdata");

                        if(!empty($arrProductSales))
                        {

                            $diff_array = array_diff_assoc($pSales, $arrProductSales);
                            pa($diff_array,"Diff Array");

                            if(empty($diff_array))
                            {
                                continue; //SAME DATA FOUND, SO CONTINUe with the loop
                            }	
                            else
                            {
                              //  $pSales['insertedDate'] = date("Y-m-d H:i:s");
                                $pSales['updatedDate'] = date("Y-m-d H:i:s");
                                $pSales['insert_status'] = self::UPDATED;

                                $whereconditions = array();
                                $whereconditions['iempid'] = $pSales['iempid'];
                                $whereconditions['itransdetailid'] = $pSales['itransdetailid'];
                                $whereconditions['cinvoiceno'] = $pSales['cinvoiceno'];
                                $whereconditions['iclientid'] = $pSales['iclientid'];
                                $whereconditions['account_no'] = $this->salonAccountId;

                                $res = $this->Productsalesimport_model->updateMillProductsSales($whereconditions,$pSales);
                                 pa('',"Updated data ---ID=".$arrProductSales['id']);
                            }
                        }
                        else // INSERT APPOINTMENT DATA IN DB 
                        {
                            $pSales['account_no'] = $this->salonAccountId;
                            $pSales['insertedDate'] = date("Y-m-d H:i:s");
                            $pSales['updatedDate'] = date("Y-m-d H:i:s");
                            $pSales['insert_status'] = self::INSERTED;
                            $res = $this->Productsalesimport_model->insertMillProductsSales($pSales);
                            pa('',"inserted data ---ID=".$res);
                        }
                    } //foreach ends of Package

                    if(!empty($this->transactionIds))
                    {
                        //TO SEARCH EXISTING APPOINTMENTS WITH NEW APPOINTMENTS
                        $allDBtransactionIds = array();
                        // GETS APPOINTMENTS DATA FROM DB COMPARING APPOINTMENT IID
                         $whereCondition = array('account_no' => $this->salonAccountId , 'tdatetime >=' => $this->startDate, 'tdatetime <=' => $this->endDate);
                        $allApptsqueryArray = $this->Productsalesimport_model
                                           ->compareMillProductsSales($whereCondition)
                                           ->result_array();
                        foreach($allApptsqueryArray as $allApptId)
                        {
                            $allDBtransactionIds[$allApptId['id']] =  $allApptId['itransdetailid'];
                        }
                        if($allDBtransactionIds){
                            $result = array_diff($allDBtransactionIds,$this->transactionIds);
                        
                            if(!empty($result))
                            {
                                foreach($result as $recordID =>$resultss)
                                {
                                    $whereconditions = array(
                                        'id' => $recordID,
                                        'account_no'=> $this->salonAccountId,
                                        );
                                    $this->Productsalesimport_model->deleteMillProductSales($whereconditions); 

                                }
                                pa($result,"Deleted data ---=".$result);
                            }
                        }
                    }
                }
                else
                {
                    echo "No Product Sales Data found in Millennium.";
                }
                // Database Log
                $log['id'] = $log_id;
                $log_id = $this->Common_model->saveMillCronLogs($log);    
                $logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);
               }else{
                   echo "SESSION ID IS NOT SET";
               } 
            }
                            $errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
        }
        else{
            exit("PLASE CHECK SALON ID");
        }
    }
}
			

