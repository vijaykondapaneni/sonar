<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MillClientsFromAppRegisterSync extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Owner Dashboard Calculation
    **/
    CONST INSERTED = 0;
    CONST UPDATED = 1;
    public $salon_id;
    public $startDate;
    public $endDate;
    public $lastYearStartDate;
    public $lastYearEndDate;
    public  $currentDate;
    private $salonId;
    private $salonDetails;
    private $dayRangeType;    
  
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
    }
    
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     * This function for get employee schedule hours last year data
     * @param type $account_no
     */
    function setUpdateMillCients($salon_id="")
        { 
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            //pa($getAllSalons,'getAllSalons',false);
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    $this->salon_id = $salonsData['salon_id'];
                    $this->salonDetails = $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                    //pa($this->salonDetails,'',false);
                    
                    if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"])  && $salonDetails['salon_info']["millennium_enabled"]=="Yes"){
                        // Database Log
                        $AccountNo = $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                        $salon_id = $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                        $log['StartingTime'] = date('Y-m-d H:i:s');                        
                        $log['whichCron'] = 'MillClientsFromAppRegisterSync';                        
                        $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_millsdk_appts/MillClientsFromAppRegisterSync/'.$salon_id;
                        $log['CronType'] = 1;
                        $log['id'] = 0;
                        $log_id = $this->Common_model->saveMillCronLogs($log);
                        // GET START DATE AND END DATE AS PER PARAMETERS

                        $postdata['salon_id'] = $salon_id;
                        $url = 'https://saloncloudsplus.com/wsCompareMillClients/updateIntermediateServer';
                        $clientsarrary = $this->Common_model->getCurlData($url,$postdata);

                        //pa($clientsarrary,'clientsarrary',false);
                        //pa($AccountNo,'log',false);

                        if(!empty($clientsarrary)){
                            foreach ($clientsarrary as $key => $value) {
                                $clientid  = $value['clientid'];
                                //pa($clientid);
                                $clientsWhere = array('AccountNo' => $AccountNo,'clientid' => $clientid);
                                $dbres = $this->db->get_where('mill_clients',$clientsWhere)->row_array();
             
                                $comparearray = array();
                                $comparearray['ClientId'] = $value['clientid'];
                                $comparearray['Email'] = $value['email'];
                                $comparearray['Zip'] = $value['zipcode'];
                                $comparearray['Phone'] = substr($value['phone'],3);
                                $comparearray['Mobile'] = substr($value['phone'],3);//Added By Kranthi
                                $comparearray['MobileAreaCode'] = $value['MobileAreaCode'];
                                $comparearray['Name'] = $value['name'];
                                $comparearray['Dob'] = $value['birthday'];
                                $comparearray['Sex'] = $value['sex'];
                                $comparearray['clientFirstVistedDate'] = $value['clientFirstVistedDate'];
                                $comparearray['clientLastVistedDate'] = $value['clientLastVistedDate'];
                                $comparearray['opted_in_email'] = $value['optin_email'];
                                $comparearray['opted_in_sms'] = $value['optin_sms'];
                                
                                if(!empty($dbres)){
                                    $diff_array = array_diff_assoc($comparearray, $dbres);
                                    //pa($diff_array,'diff_array',true);
                                    // update
                                    
                                    if(empty($diff_array))
                                    {
                                        pa('No Updates Clients Table');
                                        continue;
                                    }else
                                    {
                                        $diff_array['ModifiedDate'] = date("Y-m-d H:i:s");
                                        $diff_array['IsProcessed'] = 1;
                                        $diff_array['IsProcessedDM'] = 0;
                                        pa($diff_array,'update',false);
                                        // update
                                        $this->db->where('ClientId',$clientid);
                                        $this->db->where('AccountNo',$AccountNo);
                                        $this->db->update('mill_clients',$diff_array);
                                        //pa($this->db->last_query(),'Update');
                                    }
                                }else{
                                    // insert
                                    $comparearray['AccountNo'] = $AccountNo;
                                    $comparearray['CreatedDate'] = date("Y-m-d H:i:s");
                                    $comparearray['ModifiedDate'] = date("Y-m-d H:i:s");
                                    $comparearray['IsProcessed'] = 1;
                                    $comparearray['IsProcessedDM'] = 0;
                                    pa($comparearray,'Insert');
                                    $res = $this->db->insert('mill_clients',$comparearray);       
                                    //pa($this->db->last_query(),'insert_query');
                                    $clients_id = $this->db->insert_id();
                                    pa('',"Clients Inserted data ---ID=".$clients_id);
                                    
                                }

                            }
                        }else{
                            pa('','No Clients Data');
                        }
                       
                    // Database Log
                    $log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);  
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                }
            }else{
                echo "No SalonId's In Server";
            }
            
       } 
   
 }       