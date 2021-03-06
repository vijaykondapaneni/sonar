<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

/* 
*Description:
*/
class StaffDashboardWs extends REST_Controller
{
    
    public  $salonId;
    public  $staffId;
    public  $salonInfo;
    public  $startDate;
    public  $endDate;
    public  $currentDate;
    private $dayRangeType;
    private $lastOneMonthstartDate;
    private $lastTwoMonthsstartDate;   
    private $lastThreeMonthsstartDate;
    private $responseArr;
    private $resArr;
    //CONST SERVICE_URL = 'StaffDashboardWs/getStaffDashboardWs/Today';
    CONST SERVICE_URL_TODAY = 'StaffDashboardWs/getStaffDashboardWs/today';
    CONST SERVICE_URL_LASTWEEK = 'StaffDashboardWs/getStaffDashboardWs/lastweek';
    CONST SERVICE_URL_MONTHLY = 'StaffDashboardWs/getStaffDashboardWs/Monthly';
    CONST SERVICE_URL_LASTMONTH = 'StaffDashboardWs/getStaffDashboardWs/lastmonth';
    CONST SERVICE_URL_LAST90DAYS = 'StaffDashboardWs/getStaffDashboardWs/last90days';

    
    function __construct()
    { 
        parent::__construct(); 
        $this->load->model('Common_model');
        $this->load->library('webserviceaccess');
    }
    
    public function __getStartEndDate($dayRangeType, $year = 1 ,$s = '', $e = '')
    {
        $this->dayRangeType =  $dayRangeType;
        $this->currentDate = getDateFn();
        switch ($this->dayRangeType) {
            case TODAY:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
                $this->Service_url = self::SERVICE_URL_TODAY;
            break;
            case LASTWEEK:
                
                 if(isset( $this->salonInfo['salon_info']["salon_start_day_of_week"]) && !empty($this->salonInfo['salon_info']["salon_start_day_of_week"]))
                    {
                        $lastDayOfTheWeek =  $this->salonInfo['salon_info']["salon_start_day_of_week"];
                        $this->startDate = getDateFn(strtotime('last '.$lastDayOfTheWeek));
                        $this->endDate = getDateFn(strtotime($this->startDate.' +6 days'));
                    }
                    else
                    {
                        $this->startDate = getDateFn(strtotime('-7 days'));
                        $this->endDate = getDateFn(strtotime('-1 days'));
                    }
                    $this->Service_url = self::SERVICE_URL_LASTWEEK;   
                break;
            case LASTMONTH:
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->Service_url = self::SERVICE_URL_LASTMONTH;   
            break;
            case MONTHLY:
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
                $this->Service_url = self::SERVICE_URL_MONTHLY;
            break;
            case "THREEMONTHS":
            case "LAST90DAYS":                      
            case "last90days":                      
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->lastOneMonthstartDate = getDateFn(strtotime("first day of last month"));
                $this->lastTwoMonthsstartDate = getDateFn(strtotime($this->lastOneMonthstartDate. " -1 month"));
                $this->lastThreeMonthsstartDate = getDateFn(strtotime($this->lastOneMonthstartDate. " -2 months"));
                $this->Service_url = self::SERVICE_URL_LAST90DAYS; 
            break;
            case YEARLY:                                   
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = $this->currentDate;
                $this->Service_url = self::SERVICE_URL_LAST90DAYS; 
            break;
            case CUSTOMDATE:
                $this->startDate = $s;
                $this->endDate = $e;
                $this->Service_url = self::SERVICE_URL_TODAY;
            break;
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
                $this->Service_url = self::SERVICE_URL_TODAY;
           break;
        }
    }
    private function __getAccessWesbService($service,$salon_id){
               $this->WebAccessResponse = $this->webserviceaccess->validateWebAppWs($service,$salon_id); 
               /*if(isset($this->WebAccessResponse['HTTPCODE']) && $this->WebAccessResponse['HTTPCODE'] == 405 ){
                    show_error($this->WebAccessResponse['MESSAGE'], $this->WebAccessResponse['HTTPCODE'], $this->WebAccessResponse['HTTPCODE']);
                }else if((isset($this->WebAccessResponse['HTTPCODE']) && $this->WebAccessResponse['HTTPCODE'] != 200)) {
                    show_error($this->WebAccessResponse['MESSAGE'], $this->WebAccessResponse['HTTPCODE'], $this->WebAccessResponse['HTTPCODE']);
                    $this->WebAccessResponse = $this->WebAccessResponse;
                }else if((isset($this->WebAccessResponse['HTTPCODE']) && $this->WebAccessResponse['HTTPCODE'] == 200))
                {
                     $this->WebAccessResponse = $this->WebAccessResponse;
                }*/
                return $this->WebAccessResponse;
    }
    
    public function getStaffDashboardWs($dayRangeType="Today"){
        if(isset($_POST['salon_id'])){
            $salon_id = $_POST['salon_id'];
            $staff_id = $_POST['staff_id'];
            $this->salonInfo = $this->Common_model->getSalonInfoBy($salon_id);
            $this->salon_name = $this->salonInfo['salon_info']['salon_name'];
            //$this->salonInfo = $this->Common_model->getCurlData(GETMILLAPPOINTMENTANDSALONINFO,array('salon_id' => $salon_id, 'staff_id' => $staff_id) );
            //$this->salonInfo = $this->Common_model->getMillAppointmentAndSalonInfo(array('salon_id' => $salon_id, 'staff_id' => $staff_id));
        }else{
            $salon_id = '';
        }
        $this->__getStartEndDate($dayRangeType);

        $this->__getAccessWesbService($this->Service_url,$salon_id);
        if($this->WebAccessResponse['HTTPCODE'] != 200){
               $this->responseArr = array('status' => false, 'message' => $this->WebAccessResponse['MESSAGE'], 'status_code' => 401);
                $response_code = $this->WebAccessResponse['HTTPCODE'];
                goto response;    
        }
        
        

        $this->currentDate = getDateFn();
        $this->responseArr = array();
        
        if(isset($_POST['salon_id']) && !empty($_POST['salon_id'])){
                if(!isset($_POST['staff_id']) && empty($_POST['staff_id'])) {
                    $this->responseArr = array('status' => false, 'error' => 'Invalid Staff Id', 'error_code' => 401);
                    $response_code = 401;
                    goto response; 
                }
                
                    $this->salonId = $_POST['salon_id'];
                    $this->staffId = $_POST['staff_id'];

                    $sdkdata = $this->Common_model->getMillSdkConfigDetailsBy($_POST['salon_id'])->row_array();
                    $staff_service_types = $sdkdata['staff_service_types'];
                    $staff_leaderboard_type = $sdkdata['staff_leaderboard_type'];
                    $staff_service_types_array = explode(",",$staff_service_types);
                    $staff_leaderboard_number_display = $sdkdata['staff_leaderboard_number_display'];
                     $team_commission = $sdkdata['team_commission'];
                   
                    
                    //$this->salonInfo = $this->Common_model->getCurlData(GETMILLAPPOINTMENTANDSALONINFO,array('salon_id' => $this->salonId, 'staff_id' => $this->staffId) );

                    $this->salonInfo = $this->Common_model->getMillAppointmentAndSalonInfo(array('salon_id' => $this->salonId, 'staff_id' => $this->staffId));
                   
      
                    // GET START DATE AND END DATE AS PER PARAMETERS
                    
                    $this->responseArr['start_date'] = $this->startDate; 
                    $this->responseArr['end_date'] = $this->endDate;
                    //GOAL OR SKILL SET INFO
                    $this->responseArr["skill_set"] = ((isset($this->salonInfo["skill_set"]) && !empty($this->salonInfo["skill_set"]))) ? $this->salonInfo["skill_set"] : "" ;
                    
                    $this->responseArr["dept_skill_set"] = ((isset($this->salonInfo["dept_skill_set"]) && !empty($this->salonInfo["dept_skill_set"]))) ? $this->salonInfo["dept_skill_set"] : "" ;
                        
                    $this->responseArr["skill_set_prebook"] = ((isset($this->salonInfo["skill_set_prebook"]) && !empty($this->salonInfo["skill_set_prebook"]))) ? $this->salonInfo["skill_set_prebook"] : "0.00" ;
                    
                    $this->responseArr["skill_set_rebook"] = ((isset($this->salonInfo["skill_set_rebook"]) && !empty($this->salonInfo["skill_set_rebook"]))) ? $this->salonInfo["skill_set_rebook"] : "" ;
                     
                    
                    $this->responseArr["skill_set_ruct"] = ((isset($this->salonInfo["skill_set_ruct"]) && !empty($this->salonInfo["skill_set_ruct"]))) ? $this->salonInfo["skill_set_ruct"] : "" ;
                    
                    
                    $this->responseArr["skill_set_color"] = ((isset($this->salonInfo["skill_set_color"]) && !empty($this->salonInfo["skill_set_color"]))) ?  $this->salonInfo["skill_set_color"] : "" ;
                    
                    $this->responseArr["skill_set_productivity"] = ((isset($this->salonInfo["skill_set_productivity"]) && !empty($this->salonInfo["skill_set_productivity"]))) ? $this->salonInfo["skill_set_productivity"] : "" ;
                    
                    $this->responseArr["skill_set_avg_service_ticket"] = ((isset($this->salonInfo["skill_set_avg_service_ticket"]) && !empty($this->salonInfo["skill_set_avg_service_ticket"]))) ? $this->salonInfo["skill_set_avg_service_ticket"] : "" ;
                    
                    $this->responseArr["skill_set_avg_rpct"] = ((isset($this->salonInfo["skill_set_avg_rpct"]) && !empty($this->salonInfo["skill_set_avg_rpct"]))) ? $this->salonInfo["skill_set_avg_rpct"] : "0.00" ;
              
                    $this->responseArr["RPST_goal"] = ((isset($this->salonInfo["RPST_goal"]) && !empty($this->salonInfo["RPST_goal"]))) ? $this->salonInfo["RPST_goal"] : "" ;
                    
                    $this->responseArr["clients_serviced_goal"] = ((isset($this->salonInfo["clients_serviced"]) && !empty($this->salonInfo["clients_serviced"]))) ? $this->salonInfo["clients_serviced"] : "" ;
                    
                    
                    $this->responseArr["clients_serviced_goal"] = ((isset($this->salonInfo["clients_serviced"]) && !empty($this->salonInfo["clients_serviced"]))) ? $this->salonInfo["clients_serviced"] : "" ;
                    
                    $this->responseArr["buying_retail_percentage_goal"] = ((isset($this->salonInfo["buying_retail_percentage_goal"]) && !empty($this->salonInfo["buying_retail_percentage_goal"]))) ? $this->salonInfo["buying_retail_percentage_goal"] : "" ;
                    
                    
                    $this->responseArr["checked_in_appointments"] = ((isset($this->salonInfo["checked_in_appointments"]) && !empty($this->salonInfo["checked_in_appointments"]))) ?   $this->salonInfo["checked_in_appointments"] : array() ;
                    
                    
                    $this->responseArr["next_two_appointments"] = ((isset($this->salonInfo["next_two_appointments"]) && !empty($this->salonInfo["next_two_appointments"]))) ? $this->salonInfo["next_two_appointments"] : array() ;
                    
                   
                    // Fetching staff report from table
                    $whereReports = array('salon_id' => $this->salonId, 'staff_id' => $this->staffId,'dayRangeType' => $this->dayRangeType,'start_date' => $this->startDate,'end_date' => $this->endDate);
                    $res = $this->db->select('*')->get_where(MILL_REPORT_CALCULATIONS_CRON,$whereReports)->row_array();

                    //pa($this->db->last_query());

                    
                    $this->responseArr["highest_avg_rpct_value"] = ((isset($res["highest_avg_rpct_value"]) && !empty($res["highest_avg_rpct_value"]))) ? $res["highest_avg_rpct_value"] : "0" ; 

                    $this->responseArr["highest_avg_rpct_employee"] = ((isset($res["highest_avg_rpct_employee"]) && !empty($res["highest_avg_rpct_employee"]))) ? $res["highest_avg_rpct_employee"] : "" ; 

                    $this->responseArr["highest_avg_rpct_employee_image"] = ((isset($res["highest_avg_rpct_employee_image"]) && !empty($res["highest_avg_rpct_employee_image"]))) ? $res["highest_avg_rpct_employee_image"] : "" ; 


                    $this->responseArr["highest_avg_serviceTicket_value"] = ((isset($res["highest_avg_serviceTicket_value"]) && !empty($res["highest_avg_serviceTicket_value"]))) ? $res["highest_avg_serviceTicket_value"] : "0" ; 


                    $this->responseArr["highest_avg_serviceTicket_employee"] = ((isset($res["highest_avg_serviceTicket_employee"]) && !empty($res["highest_avg_serviceTicket_employee"]))) ? $res["highest_avg_serviceTicket_employee"] : "" ; 

                    $this->responseArr["highest_avg_serviceTicket_employee_image"] = ((!empty($res["highest_avg_serviceTicket_employee_image"]))) ? $res["highest_avg_serviceTicket_employee_image"] : "" ; 


                    $this->responseArr["highest_prebook_value"] = ((!empty($res["highest_prebook_value"]))) ? $res["highest_prebook_value"] : "0" ; 

                    $this->responseArr["highest_prebook_sold_employee"] = (!empty($res["highest_prebook_sold_employee"]) && $res["highest_prebook_sold_employee"]!="" ) ? $res["highest_prebook_sold_employee"] : "" ; 

                    $this->responseArr["highest_prebook_sold_employee_image"] = (!empty($res["highest_prebook_sold_employee_image"])) ? $res["highest_prebook_sold_employee_image"] : "" ; 

                    //REBOOK LEADER BOARD
                    $this->responseArr["highest_rebook_value"] = (!empty($res["highest_rebook_value"]) && $res["highest_rebook_value"]!="0.00") ? $res["highest_rebook_value"] : "0" ;

                    $this->responseArr["highest_rebook_sold_employee"] = (!empty($res["highest_rebook_sold_employee"]) && $res["highest_rebook_sold_employee"]!="") ? $res["highest_rebook_sold_employee"] : "" ;

                    $this->responseArr["highest_rebook_sold_employee_image"] = (!empty($res["highest_rebook_sold_employee_image"])) ? $res["highest_rebook_sold_employee_image"] : "" ;

                    //HIGHEST RETAIL LEADERBOARD    
                    $this->responseArr["highest_retail_value"] = (!empty($res["highest_product_revenue_value"]) && $res["highest_product_revenue_value"]!="0.00") ? $res["highest_product_revenue_value"] : "0" ;

                    $this->responseArr["highest_retail_employee"] = (!empty($res["highest_product_revenue_employee"]) && $res["highest_product_revenue_employee"]!="") ? $res["highest_product_revenue_employee"] : "" ;

                    $this->responseArr["highest_retail_employee_image"] = (!empty($res["highest_product_revenue_employee_image"])) ? $res["highest_product_revenue_employee_image"] : "" ;
                    
                    //HIGHEST SERVICE LEADERBOARD   
                    $this->responseArr["highest_service_value"] = (!empty($res["highest_service_revenue_value"]) && $res["highest_service_revenue_value"]!="0.00") ? $res["highest_service_revenue_value"] : "0" ;

                    $this->responseArr["highest_service_employee"] = (!empty($res["highest_service_revenue_employee"]) && $res["highest_service_revenue_employee"]!="") ? $res["highest_service_revenue_employee"] : "" ;

                    $this->responseArr["highest_service_employee_image"] = (!empty($res["highest_service_revenue_employee_image"])) ? $res["highest_service_revenue_employee_image"] : "" ;


                    //HIGHEST RUCT LEADERBOARD
                    $this->responseArr["highest_ruct_value"] = (!empty($res["highest_ruct_value"]) && $res["highest_ruct_value"]!="0.00") ? $res["highest_ruct_value"] : "0" ;

                    $this->responseArr["highest_ruct_employee"] = (!empty($res["highest_ruct_employee"]) && $res["highest_ruct_employee"]!="") ? $res["highest_ruct_employee"] : "" ;

                    $this->responseArr["highest_ruct_employee_image"] = (!empty($res["highest_ruct_employee_image"])) ? $res["highest_ruct_employee_image"] : "" ;


                    //HIGHEST CLIENT BUYING RETAIL LEADERRBOARD
                    $this->responseArr["highest_percentage_retail_value"] = (!empty($res["highest_percentage_retail_value"]) && $res["highest_percentage_retail_value"]!="0.00") ? $res["highest_percentage_retail_value"] : "0" ;

                    $this->responseArr["highest_percentage_retail_employee"] = (!empty($res["highest_percentage_retail_employee"]) && $res["highest_percentage_retail_employee"]!="") ? $res["highest_percentage_retail_employee"] : "" ;

                    $this->responseArr["highest_percentage_retail_employee_image"] = (!empty($res["highest_percentage_retail_employee_image"])) ? $res["highest_percentage_retail_employee_image"] : "" ;



                    $this->responseArr["retailPerServiceValue"] = (!empty($res["retailPerServiceValue"]) && $res["retailPerServiceValue"]!="0.00") ? $res["retailPerServiceValue"] : "0" ;

                    $this->responseArr["client_name"] = (!empty($res["client_name"]) && $res["client_name"]!="") ? $res["client_name"] : "" ;
                    $this->responseArr["avgServiceTicket"] = (!empty($res["avgServiceTicket"]) && $res["avgServiceTicket"]!="0.00") ? $res["avgServiceTicket"] : "0" ;

                    $this->responseArr["avgRetailTicket"] = (!empty($res["avgRetailTicket"]) && $res["avgRetailTicket"]!="0.00") ? $res["avgRetailTicket"] : "0" ;

                    $this->responseArr["total_retail_price"] = (!empty($res["total_retail_price"]) && $res["total_retail_price"]!="0.00") ? $res["total_retail_price"] : "0" ;

                    $this->responseArr["retail_units"] = (!empty($res["retail_units"]) && $res["retail_units"]!="0.00") ? $res["retail_units"] : "0" ;

                    $this->responseArr["total_service_sales"] = (!empty($res["total_service_sales"]) && $res["total_service_sales"]!="0.00") ? $res["total_service_sales"] : "0" ;

                    $this->responseArr["RPCT"] = (!empty($res["RPCT"]) && $res["RPCT"]!="0.00") ? $res["RPCT"] : "0" ;

                    $this->responseArr["RPST"] = (!empty($res["RPST"]) && $res["RPST"]!="0.00") ? $res["RPST"] : "0" ;

                    $this->responseArr["RPRT"] = (!empty($res["RPRT"]) && $res["RPRT"]!="0.00") ? $res["RPRT"] : "0" ;

                    $this->responseArr["prebook_percentage"] = (!empty($res["prebook_percentage"]) && $res["prebook_percentage"]!="0.00") ? $res["prebook_percentage"] : "0" ;

                    $this->responseArr["rebook_percentage"] = (!empty($res["rebook_percentage"]) && $res["rebook_percentage"]!="0.00") ? $res["rebook_percentage"] : "0" ;

                    $this->responseArr["color_percentage"] = (!empty($res["color_percentage"]) && $res["color_percentage"]!="0.00") ? $res["color_percentage"] : "0" ;

                    $this->responseArr["new_guest_qty"] = (!empty($res["new_guest_qty"]) && $res["new_guest_qty"]!="") ? $res["new_guest_qty"] : "0" ;

                    $this->responseArr["repeated_guest_qty"] = (!empty($res["repeated_guest_qty"]) && $res["repeated_guest_qty"]!="") ? $res["repeated_guest_qty"] : "0" ;

                    $this->responseArr["totalClientServiceVisits"] = (!empty($res["totalClientServiceVisits"]) && $res["totalClientServiceVisits"]!="") ? $res["totalClientServiceVisits"] : "0" ;

                    $this->responseArr["totalClientRetailVisits"] = (!empty($res["totalClientRetailVisits"]) && $res["totalClientRetailVisits"]!="") ? $res["totalClientRetailVisits"] : "0" ;

                    $this->responseArr["totalClients"] = (!empty($res["totalClients"]) && $res["totalClients"]!="") ? $res["totalClients"] : "0" ;

                    $this->responseArr["totalTickets"] = (!empty($res["totalTickets"]) && $res["totalTickets"]!="") ? $res["totalTickets"] : "0" ;

                    $this->responseArr["RUCT"] = (!empty($res["RUCT"]) && $res["RUCT"]!="") ? $res["RUCT"] : "0" ;

                    $this->responseArr["client_served"] = (!empty($res["client_served"]) && $res["client_served"]!="") ? $res["client_served"] : "0" ;

                    $this->responseArr["percentage_booked"] = (!empty($res["percentage_booked"]) && $res["percentage_booked"]!="") ? $res["percentage_booked"] : "0" ;

                    $this->responseArr["total_guests_for_month"] = (!empty($res["total_guests_for_month"]) && $res["total_guests_for_month"]!="") ? $res["total_guests_for_month"] : "0" ;


                    $this->responseArr["percentage_of_clients_buying_retail"] = (!empty($res["percentage_of_clients_buying_retail"]) && $res["percentage_of_clients_buying_retail"]!="") ? $res["percentage_of_clients_buying_retail"] : "0" ;

                    
                    
                    /*********BOC: New Structure **********************************/
                    
                    $tempResponseArr['name'] = $this->salon_name;
                    $tempResponseArr['dateRange'] = date('m/d/Y', strtotime($this->startDate)) . ' - ' . date('m/d/Y', strtotime($this->endDate));
                    
                    $tempResponseArr['tiles'] =     array(
                                                        array(
                                                            'internal_graphs'=>0,
                                                            "titleText"=> "retailPerServiceValue",
                                                            'values'=> array(
                                                                 "prefix"=> "",
                                                                 "postfix"=> "",
                                                                 "current"=> $this->responseArr["retailPerServiceValue"]
                                                             )
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            "titleText"=> "client",
                                                            'values'=> array(
                                                                 "prefix"=> "",
                                                                 "postfix"=> "",
                                                                 "current"=> $this->responseArr["client_name"]
                                                             )
                                                            
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'type'=> 'tileWithIcon',
                                                            'titleText'=> 'New Guest',
                                                            'icon'=> 'https://s3.amazonaws.com/appreportsicons/users.png',
                                                            'values' => array(
                                                                'prefix' => '',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["new_guest_qty"]
                                                            )
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'type'=> 'tileWithIcon',
                                                            'titleText'=> 'Repeat Guest',
                                                            'icon'=> 'https://s3.amazonaws.com/appreportsicons/users.png',
                                                            'values' => array(
                                                                'prefix' => '',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["repeated_guest_qty"]
                                                            )
                                                        ),

                                                       
                                                        array(
                                                            'internal_graphs'=>1,
                                                            'type'=> 'tileServiceRetail',
                                                            'titleText'=> 'Service',
                                                            'icon'=> 'https://s3.amazonaws.com/appreportsicons/contract.png',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["total_service_sales"],
                                                            )
                                                        ),
                                                        array(
                                                            'internal_graphs'=>1, 
                                                            'type'=> 'tileServiceRetail',
                                                            'titleText'=> 'Retail',
                                                            'icon'=> 'https://s3.amazonaws.com/appreportsicons/shopping+bag.png',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["total_retail_price"],
                                                            )
                                                        ),
                                                         array(
                                                            'internal_graphs'=>1,
                                                            'type'=> 'tileServiceRetail',
                                                            'titleText'=> 'Service + Retail',
                                                            'icon'=> 'https://s3.amazonaws.com/appreportsicons/money+bag.png',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => ($this->responseArr["total_retail_price"] + $this->responseArr["total_service_sales"]),
                                                            )
                                                        ),    
                                                        array(
                                                            'internal_graphs'=>1,
                                                            'type'=> 'tileTickets',
                                                            'titleText'=> 'Avg Servic Tkt',
                                                            'icon'=> 'https://s3.amazonaws.com/appreportsicons/contract.png',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["avgServiceTicket"],
                                                                'goal' => $this->responseArr["skill_set_avg_service_ticket"]
                                                            )
                                                        ),
                                                        array(
                                                            'internal_graphs'=>1,
                                                            'type'=> 'tileTickets',
                                                            'titleText'=> 'AVG Retail',
                                                            'icon'=> 'https://s3.amazonaws.com/appreportsicons/shopping+bag.png',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["avgRetailTicket"],
                                                            )
                                                        ),
                                                        
                                                        
                                                       
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'titleText'=> 'Retail Units',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["retail_units"],
                                                            )
                                                           
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'type'=> 'tilesAppointments',
                                                            'titleText'=> 'Checked in Appts',
                                                            'values_appointments' => $this->responseArr["checked_in_appointments"]
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'type'=> 'tileWithIconFullWidth',
                                                            'titleText'=> 'next_two_appointments',
                                                            'values_appointments' => $this->responseArr["next_two_appointments"]
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'titleText'=> 'totalClientServiceVisits',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["totalClientServiceVisits"],
                                                            )
                                                            
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'titleText'=> 'totalClientRetailVisits',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["totalClientRetailVisits"],
                                                            )
                                                            
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'titleText'=> 'totalClients',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["totalClients"],
                                                            )
                                                            
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'titleText'=> 'totalTickets',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["totalTickets"],
                                                            )

                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'titleText'=> 'client_served',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["client_served"],
                                                            )

                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'titleText'=> 'percentage_booked',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["percentage_booked"],
                                                            )
                                                            
                                                        ),
                                                        array(
                                                            'internal_graphs'=>0,
                                                            'titleText'=> 'total_guests_for_month',
                                                            'values' => array(
                                                                'prefix' => '$',
                                                                'postfix' => '',
                                                                'current' => $this->responseArr["total_guests_for_month"],
                                                            )

                                                        ),


                                                    );
                    
                    // tiles_services
                    // prebook
                    $prebook =array('internal_graphs'=>1,'type'=> 'tileWithProgress',
                                          'titleText'=> 'Prebook%',
                                          'icon'=> '',
                                          'values' => array(
                                            'prefix' => '',
                                            'postfix' => '%',
                                            'current' => $this->responseArr["prebook_percentage"],
                                             'goal' => $this->responseArr["skill_set_prebook"]   
                                            )
                                        );
                    $metrics = array();
                    if(in_array('prebook',$staff_service_types_array)){
                        $metrics[] = $prebook;
                    }
                    //rpct
                    $rpct = array('internal_graphs'=>1,'type'=> 'tileWithProgress',
                                   'titleText'=> 'RPCT',
                                   'icon'=> '',
                                   'values' => array(
                                        'prefix' => '$',
                                        'postfix' => '',
                                        'current' => $this->responseArr["RPCT"],
                                        'goal' => $this->responseArr["skill_set_avg_rpct"] 
                                    )
                                );
                    if(in_array('rpct',$staff_service_types_array)){
                        $metrics[] = $rpct;
                    }
                    // color
                    $color = array('internal_graphs'=>1,'type'=> 'tileWithProgress',
                                   'titleText'=> 'Color',
                                   'icon'=> '',
                                   'values' => array(
                                        'prefix' => '',
                                        'postfix' => '%',
                                        'current' => $this->responseArr["color_percentage"],
                                         'goal' => $this->responseArr["skill_set_color"]
                                                     )
                                   );
                    if(in_array('color',$staff_service_types_array)){
                        $metrics[] = $color;
                    }

                    // rebook
                    $rebook = array('internal_graphs'=>1,
                                    'type'=> 'tileWithProgress',
                                    'titleText'=> 'Rebook%',
                                    'icon'=> '',
                                    'values' => array(
                                        'prefix' => '%',
                                        'postfix' => '',
                                        'current' => $this->responseArr["rebook_percentage"],
                                        'goal' => $this->responseArr["skill_set_rebook"] 
                                    )
                                   );
                    if(in_array('rebook',$staff_service_types_array)){
                        $metrics[] = $rebook;
                    }

                    // %booked
                    $booked = array('internal_graphs'=>1,
                                    'type'=> 'tileWithProgress',
                                    'titleText'=> 'Booked %',
                                    'icon'=> '',
                                    'values' => array(
                                        'prefix' => '%',
                                        'postfix' => '',
                                        'current' => $this->responseArr["percentage_booked"],
                                        'goal' => $this->responseArr["skill_set_prebook"] 
                                    )
                                   );
                    if(in_array('booked',$staff_service_types_array)){
                        $metrics[] = $booked;
                    }

                    // buyingretail
                    $buyingretail =  array( 'internal_graphs'=>1,
                                            'type'=> 'tileWithProgress',
                                            'titleText'=> '% Buying Retail',
                                            'icon'=> '',
                                            'values' => array(
                                                'prefix' => '%',
                                                'postfix' => '',
                                                'current' => $this->responseArr["percentage_of_clients_buying_retail"],
                                                'goal' => $this->responseArr["buying_retail_percentage_goal"] 
                                            )
                                        );
                    if(in_array('buyingretail',$staff_service_types_array)){
                        $metrics[] = $buyingretail;
                    }

                    // ruct
                    $ruct =   array(
                                    'internal_graphs'=>1,
                                    'type'=> 'tileWithProgress',
                                    'titleText'=> 'RUCT',
                                    'icon'=> '',
                                    'values' => array(
                                        'prefix' => '',
                                        'postfix' => '',
                                        'current' => $this->responseArr["RUCT"],
                                        'goal' => $this->responseArr["skill_set_ruct"] 
                                    )
                                );
                    if(in_array('ruct',$staff_service_types_array)){
                        $metrics[] = $ruct;
                    }
                    $tempResponseArr['tiles_metrics'] = $metrics; 
                    
                    //LEADER BOARD
                    $leaderboard = array();
                    $staff_leaderboard_type = explode(",",$staff_leaderboard_type);
                    foreach ($staff_leaderboard_type as $key => $value) {
                        $leaderboarddetails = $this->Common_model->getStaffLeaderBoardTypeBy($value);
                       /* pa($leaderboarddetails);
                        exit;*/
                        $leaderboarddata['name'] = !empty($this->responseArr) ? $this->responseArr[$leaderboarddetails['column_name']] : '';
                        $leaderboarddata['image'] = !empty($this->responseArr) ? $this->responseArr[$leaderboarddetails['image']] : '';
                        $leaderboarddata['title'] = $leaderboarddetails['title'];
                        if($staff_leaderboard_number_display==0){
                            $leaderboarddata['value'] = !empty($this->responseArr) ?  $leaderboarddetails['prefix'].$this->responseArr[$leaderboarddetails['column_value_name']].$leaderboarddetails['postfix']: $leaderboarddetails['prefix'].'0.0'.$leaderboarddetails['postfix'];
                        }else{
                           $leaderboarddata['value'] = ""; 
                        }
                        
                        $leaderboard[] = $leaderboarddata;
                    }
                    if($team_commission=='2'){
                        $tempResponseArr['leaders'] = $leaderboard;
                    }else{
                        $tempResponseArr['leaders'] = array();
                    }
                    /*$tempResponseArr['leaders'] = array(
                                                        array(  //Prebook LEADERRBOARD
                                                            'name' => 'Prebooking',
                                                            'image' => $this->responseArr["highest_prebook_sold_employee_image"],
                                                            'title' => $this->responseArr["highest_prebook_sold_employee"],
                                                            'value' => $this->responseArr["highest_prebook_value"],
                                                        ),
                                                        array(  //RPCT LEADERRBOARD
                                                            'name' => 'RPCT',
                                                            'image' => $this->responseArr["highest_avg_rpct_employee_image"],
                                                            'title' => $this->responseArr["highest_avg_rpct_employee"],
                                                            'value' => $this->responseArr["highest_avg_rpct_value"],
                                                        ),
                                                        array(  //SERVICE LEADERRBOARD
                                                            'name' => 'Service',
                                                            'image' => $this->responseArr["highest_service_employee_image"],
                                                            'title' => $this->responseArr["highest_service_employee"],
                                                            'value' => $this->responseArr["highest_service_value"],
                                                        ),
                                                        array( //REBOOKING LEADER BOARD
                                                            'name' => 'Rebook',
                                                            'image' => $this->responseArr["highest_rebook_sold_employee_image"],
                                                            'title' => $this->responseArr["highest_rebook_sold_employee"],
                                                            'value' => $this->responseArr["highest_rebook_value"],
                                                        ),
                                                        array(  //BUYING RETAIL
                                                            'name' => '% Buying Retail',
                                                            'image' => $this->responseArr["highest_percentage_retail_employee_image"],
                                                            'title' => $this->responseArr["highest_percentage_retail_employee"],
                                                            'value' => $this->responseArr["highest_percentage_retail_value"],
                                                        ),
                                                        array(  //RETAIL LEADERRBOARD
                                                            'name' => 'Retail',
                                                            'image' => $this->responseArr["highest_retail_employee_image"],
                                                            'title' => $this->responseArr["highest_retail_employee"],
                                                            'value' => $this->responseArr["highest_retail_value"],
                                                        ),
                                                        array(  //RUCT LEADERRBOARD
                                                            'name' => 'Ruct',
                                                            'image' => $this->responseArr["highest_ruct_employee_image"],
                                                            'title' => $this->responseArr["highest_ruct_employee"],
                                                            'value' => $this->responseArr["highest_ruct_value"],
                                                        ),
                                                        array(  //Service Ticket LEADERRBOARD
                                                            'name' => 'Service Ticket',
                                                            'image' => $this->responseArr["highest_avg_serviceTicket_employee_image"],
                                                            'title' => $this->responseArr["highest_avg_serviceTicket_employee"],
                                                            'value' => $this->responseArr["highest_avg_serviceTicket_value"],
                                                        ),
                                                       
                                                    );
                    */
                    
                  
                    
                    
                    
                    
                    
                    $this->resArr["status"] = true;
                    $this->resArr["data"]['type'] = $this->dayRangeType;
                    $this->resArr["data"]['location'] = $tempResponseArr;    
                    
                    
                        
                    /*********EOC: New Structure  **********************************/
                    
                    //pa($this->responseArr);
                    $this->responseArr = $this->resArr;
                    $response_code = 200;

                }else{
                    $this->responseArr = array('success' => false, 'error' => 'Invalid Salon Id', 'error_code' => 401);
                    goto response; 
                }
            response:
            $this->response($this->responseArr, $response_code);
    }
    
}
