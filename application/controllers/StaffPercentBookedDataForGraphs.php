<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class StaffPercentBookedDataForGraphs extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Internal Graphs Calculation For Owner
    **/
    CONST INSERTED = 0;
    CONST UPDATED = 1;
    public $salon_id;
    public $startDate;
    public $endDate;
    public  $currentDate;
    private $salonId;
    private $salonDetails;
    private $dayRangeType;    
  
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Saloncloudsplus_model');
        $this->load->model('GraphsOwner_model');

    }
    // Get Date Range Types
    function __getStartEndDate($dayRangeType,$year=1)
    {
        $this->dayRangeType =  $dayRangeType;
        $currentDate = date('Y-m-d');

         switch ($this->dayRangeType) {
            case "today":
                $this->startDate = $this->currentDate;
                $this->endDate = $this->currentDate;
            break;                          
            case "lastMonth":
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;
            case "Monthly":
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
            break;
            case "threemonths":
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;
            case "Yearly":                                   
                   /* $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));*/
                $this->startDate = date("Y-m-")."01";
                $this->endDate = $this->currentDate;
            break;
            case "Previousmonths":                                   
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;          
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
           break;
        }
    }

   
    function __getSalonReportForOwnerGraphs($data){
        pa($data);
       if(!empty($data["salon_id"]) && !empty($data["start_date"]) && !empty($data["end_date"])&& !empty($data["dayRangeType"]))
            {
                $salon_id = $data['salon_id'];
                $startDate = $data['start_date'];
                $endDate = $data['end_date'];
                $dayRangeType = $data['dayRangeType'];
                $startDayOfTheWeek = $data['startDayOfTheWeek'];
                $staff_iid = $data['staff_iid'];
                $staff_id = $data['staff_id'];
                //$staff_name = $data['staff_name'];
                if(($dayRangeType=="Yearly")||($dayRangeType=="Previousmonths") ){
                    $response['values'] = array();
                    $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                    $salon_name = $salonDetails['salon_name'];
                    $salonAccountNo = $salonDetails['salon_account_id'];
                    $dataArray['location_id'] = $salon_id;
                    $dataArray['location_name'] = $salon_name;
                    $begin = new DateTime($startDate);
                    $end = new DateTime($endDate);
                    $interval = new DateInterval('P1M');
                    $daterange = new DatePeriod($begin, $interval ,$end);
                    $salesArray = array();
                    foreach ($daterange as $key => $date) {
                        $month = $date->format("m");
                        $month = ltrim($month, '0');
                        $monthName = $date->format("F");
                        $year = date("Y");
                        $firstDayOfMonth = $date->format("Y-m-d");
                        $lastdayOfMonth = date('Y-m-t',strtotime($date->format("Y-m-d")));
                        $last_year_start_date = strtotime($firstDayOfMonth);
                        $firstDayOfMonth = date('Y-m-d', $last_year_start_date);
                        $last_year_end_date = strtotime($lastdayOfMonth);
                        $lastdayOfMonth = date('Y-m-d', $last_year_end_date);
                        $prebookArray[$monthName] = array();
                        $whereConditions =  array('account_no' =>$salonAccountNo,'dayRangeType' => 'IndividualMonth','cworktype' => 'Work Time','iempid'=>$staff_iid);
                        $where = "MONTH(start_date) = $month AND YEAR(start_date) =$year";
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getPercentBookedDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $bookedArray[$monthName]['booked']['hours_scheduled'] = !empty($resultAssocArr)? $resultAssocArr : array();
                                                 

                        //$whereConditions =  array('salonAccountNo' =>$salonAccountNo,'month' =>$month,'year' => $year,'EmployeeName'=>$staff_name);
                        $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'month' =>$month,'year' => $year,'iempid'=>$staff_iid);

                        $resultAssocArrForBooked = $this->GraphsOwner_model
                                                  ->getStaffPercentBookedTotalHours($whereConditions)
                                                  ->row_array();

                        $bookedArray[$monthName]['booked']['hours_booked'] = !empty($resultAssocArrForBooked)? $resultAssocArrForBooked : array();

                        
                        //LAST YEAR DATA
                        $month = $date->format("m");
                        $month = ltrim($month, '0');
                        $monthName = $date->format("F");
                        $year = date("Y",strtotime("-1 year"));
                        $last_year_start_date = strtotime($startDate.' -1 year');
                        $lastYearStartDate = date('Y-m-d', $last_year_start_date);
                        $last_year_end_date = strtotime($endDate.' -1 year');
                        $lastYearEndDate = date('Y-m-d', $last_year_end_date);
                        $whereConditions =  array('account_no' =>$salonAccountNo,'dayRangeType' => 'IndividualMonth','cworktype' => 'Work Time','iempid'=>$staff_iid);
                        $where = "MONTH(start_date) = $month AND YEAR(start_date) =$year";
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getPercentBookedDetailsArr($whereConditions,$where)
                                                  ->row_array();
                        $bookedArray[$monthName]['last_year_booked']['hours_scheduled'] = !empty($resultAssocArr)? $resultAssocArr : array();

                    
                        
                        //$whereConditions =  array('salonAccountNo' =>$salonAccountNo,'month' =>$month,'year' => $year,'EmployeeName'=>$staff_name);
                        $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'month' =>$month,'year' => $year,'iempid'=>$staff_iid);
                        $resultAssocArrForBooked = $this->GraphsOwner_model
                                                  ->getStaffPercentBookedTotalHours($whereConditions)
                                                  ->row_array();
                        $bookedArray[$monthName]['last_year_booked']['hours_booked'] = !empty($resultAssocArrForBooked)? $resultAssocArrForBooked : array();

                        $bookedArray[$monthName]['dates']['hours_scheduled']['start_date'] = $firstDayOfMonth;
                        $bookedArray[$monthName]['dates']['hours_scheduled']['end_date'] = $lastdayOfMonth; 
                    }
                    if(!empty($bookedArray))
                    {
                        $tempArr = array();
                        foreach($bookedArray as $month_week_name => $dayRanges)
                        {
                            $datesArray = $dayRanges['dates'];
                            $percentBookedArray = $dayRanges['booked'];
                            $lastYearpercentBookedArray = $dayRanges['last_year_booked'];
                            //$productsArray = $reportsData['Products'];
                            if((isset($percentBookedArray['hours_booked']['nstartlen']) || isset($percentBookedArray['hours_booked']['ngaplen']) || isset($percentBookedArray['hours_booked']['nfinishlen'])) && isset($percentBookedArray['hours_scheduled']['nhours']) && !empty($percentBookedArray['hours_scheduled']['nhours']))
                            {
                                $totalHoursBooked = $percentBookedArray['hours_booked']['nstartlen']+$percentBookedArray['hours_booked']['ngaplen']+$percentBookedArray['hours_booked']['nfinishlen'];

                                $prebookPercentage["current_value"] = number_format(($totalHoursBooked/$percentBookedArray['hours_scheduled']['nhours'])*100, 2, '.', '');

                                $prebookPercentage["key"] = substr($month_week_name, 0, 3);
                            }
                            else
                            {
                                $prebookPercentage["current_value"] = "0";
                                $prebookPercentage["key"] = substr($month_week_name, 0, 3);
                            }
                            if((isset($lastYearpercentBookedArray['hours_booked']['nstartlen']) || isset($lastYearpercentBookedArray['hours_booked']['ngaplen']) || isset($lastYearpercentBookedArray['hours_booked']['nfinishlen'])) && isset($lastYearpercentBookedArray['hours_scheduled']['nhours']) && !empty($lastYearpercentBookedArray['hours_scheduled']['nhours']))
                            {
                                $totalHoursBooked = $lastYearpercentBookedArray['hours_booked']['nstartlen']+$lastYearpercentBookedArray['hours_booked']['ngaplen']+$lastYearpercentBookedArray['hours_booked']['nfinishlen'];

                                $prebookPercentage["last_year_value"] = number_format(($totalHoursBooked/$lastYearpercentBookedArray['hours_scheduled']['nhours'])*100, 2, '.', '');

                                $prebookPercentage["key"] = substr($month_week_name, 0, 3);
                            }
                            else
                            {
                                $prebookPercentage["last_year_value"] = "0";
                                $prebookPercentage["key"] = substr($month_week_name, 0, 3);
                            }

                            $prebookPercentage["start_date"] = $datesArray['hours_scheduled']['start_date'];
                            $prebookPercentage["end_date"] = $datesArray['hours_scheduled']['end_date'];
                            $tempArr[] = $prebookPercentage;
                            //$dataArray["prebook_percentage"]["value"][] = $prebookPercentage;
                            //$dataArray["prebook_percentage"]["key"][] = $month_week_name;
                            
                            //}
                        }
                        $dataArray["graph_data"] = $tempArr; 
                    }
                    $response['values'][] = $dataArray;
                    $response['start_date'] = $startDate;
                    $response['end_date'] = $endDate;
                    $response["status"] = true;
                }elseif($dayRangeType=="Monthly"){
                    $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                    $salon_name = $salonDetails['salon_name'];
                    $salonAccountNo = $salonDetails['salon_account_id'];
                    $dataArray['location_id'] = $salon_id;
                    $dataArray['location_name'] = $salon_name;
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                        $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else{
                        $fourWeeksArr = getLast4WeekRanges(date('Y'));
                    }

                    $bookedArray = array();
                    $week_number=0;
                    $week_number_db=1;
                    foreach ($fourWeeksArr as $dates) {
                        $startDayOfWeek = $dates['start_date'];
                        $endDayOfWeek = $dates['end_date'];
                        $current_week = $dates['current_week'];
                        pa($startDayOfWeek.' To '.$endDayOfWeek,'Start and End DayOfWeek',false);

                        $whereConditions =  array('account_no' =>$salonAccountNo,'dayRangeType' => 'IndividualWeek','cworktype' => 'Work Time','iempid'=>$staff_iid);
                        $where = "start_date >='".$startDayOfWeek."' AND end_date<='".$endDayOfWeek."'";
                        
                        pa($where,'currentYear');
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getPercentBookedDetailsArr($whereConditions,$where)
                                                  ->row_array();
                   
                        $bookedArray[$week_number_db]['booked']['hours_scheduled'] = !empty($resultAssocArr)? $resultAssocArr : array();

                        pa($this->db->last_query());  

                       // $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'startDayOfWeek'=>$startDayOfWeek,'endDayOfWeek'=>$endDayOfWeek,'EmployeeName'=>$staff_name);
                        $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'startDayOfWeek'=>$startDayOfWeek,'endDayOfWeek'=>$endDayOfWeek,'iempid'=>$staff_iid);
                        pa($whereConditions,'currentYear');
                        $resultAssocArrForBooked = $this->GraphsOwner_model
                                                  ->getStaffPercentBookedTotalHoursWeekWiseStartWithDay($whereConditions)
                                                  ->row_array();
                        //pa($this->db->last_query());          
                        
                        $bookedArray[$week_number_db]['booked']['hours_booked'] = !empty($resultAssocArrForBooked)? $resultAssocArrForBooked : array();
                        $bookedArray[$week_number_db]['Dates']['start_date'] = $startDayOfWeek;
                        $bookedArray[$week_number_db]['Dates']['end_date'] = $endDayOfWeek;
                        $bookedArray[$week_number_db]['Dates']['current_week'] = $current_week;   

                       //LAST Year Same MONTH DATA
                        if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                        {   
                            $fourWeeksArrLastYear = getLast4BusinessWeeksRanges($startDayOfTheWeek,date("Y",strtotime("-1 year")));
                        }
                        else
                        {
                           $fourWeeksArrLastYear = getLast4WeekRanges(date("Y",strtotime("-1 year")));
                        }

                        // pa($fourWeeksArrLastYear,'fourWeeksArrLastYear',false);
                        $startDayOfWeekLastYear = $fourWeeksArrLastYear[$week_number]['start_date'];
                        $endDayOfWeekLastYear = $fourWeeksArrLastYear[$week_number]['end_date'];
                        pa($startDayOfWeekLastYear." To ". $endDayOfWeekLastYear,'Start and End DayOfWeekLastYear',false);
                        $whereConditions =  array('account_no' =>$salonAccountNo,'dayRangeType' => 'IndividualWeek','cworktype' => 'Work Time','iempid'=>$staff_iid);
                       /* $where = "MONTH(start_date) = $month AND YEAR(start_date) =$year";*/
                        $where = "start_date >='".$startDayOfWeekLastYear."' AND end_date<='".$endDayOfWeekLastYear."'";
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getPercentBookedDetailsArr($whereConditions,$where)
                                                  ->row_array();
                        
                        $bookedArray[$week_number_db]['last_year_booked']['hours_scheduled'] = !empty($resultAssocArr)? $resultAssocArr : array();
                       
                      /*  $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'month' =>$month,'year' => $year,'week'=>$week);*/
                      // $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'startDayOfWeek'=>$startDayOfWeekLastYear,'endDayOfWeek'=>$endDayOfWeekLastYear,'EmployeeName'=>$staff_name);
                      $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'startDayOfWeek'=>$startDayOfWeekLastYear,'endDayOfWeek'=>$endDayOfWeekLastYear,'iempid'=>$staff_iid);
                        pa($whereConditions,'LastYear');

                       
                        $resultAssocArrForBooked = $this->GraphsOwner_model
                                                  ->getStaffPercentBookedTotalHoursWeekWiseStartWithDay($whereConditions)
                                                  ->row_array();
                        //pa($this->db->last_query());   
                        $bookedArray[$week_number_db]['last_year_booked']['hours_booked'] = !empty($resultAssocArrForBooked)? $resultAssocArrForBooked : array();                          
                        
                        $week_number++;
                        $week_number_db++;
                    }
                    pa($bookedArray,'bookedArray');
                    if(!empty($bookedArray))
                    {
                        $tempArr = array();
                        foreach($bookedArray as $month_week_name => $dayRanges)
                        {
                            $percentBookedArray = $dayRanges['booked'];
                            $datesArray = $dayRanges['Dates'];
                            $lastYearpercentBookedArray = $dayRanges['last_year_booked'];
                            //$productsArray = $reportsData['Products'];
                            if((isset($percentBookedArray['hours_booked']['nstartlen']) || isset($percentBookedArray['hours_booked']['ngaplen']) || isset($percentBookedArray['hours_booked']['nfinishlen'])) && isset($percentBookedArray['hours_scheduled']['nhours']) && !empty($percentBookedArray['hours_scheduled']['nhours']))
                            {
                                $totalHoursBooked = $percentBookedArray['hours_booked']['nstartlen']+$percentBookedArray['hours_booked']['ngaplen']+$percentBookedArray['hours_booked']['nfinishlen'];

                                $prebookPercentage["current_value"] = number_format(($totalHoursBooked/$percentBookedArray['hours_scheduled']['nhours'])*100, 2, '.', '');

                                $prebookPercentage["key"] = "Week ".$month_week_name;
                            }
                            else
                            {
                                $prebookPercentage["current_value"] = "0";
                                $prebookPercentage["key"] = "Week ".$month_week_name;
                            }

                            if((isset($lastYearpercentBookedArray['hours_booked']['nstartlen']) || isset($lastYearpercentBookedArray['hours_booked']['ngaplen']) || isset($lastYearpercentBookedArray['hours_booked']['nfinishlen'])) && isset($lastYearpercentBookedArray['hours_scheduled']['nhours']) && !empty($lastYearpercentBookedArray['hours_scheduled']['nhours']))
                            {
                                $totalHoursBooked = $lastYearpercentBookedArray['hours_booked']['nstartlen']+$lastYearpercentBookedArray['hours_booked']['ngaplen']+$lastYearpercentBookedArray['hours_booked']['nfinishlen'];

                                $prebookPercentage["last_year_value"] = number_format(($totalHoursBooked/$lastYearpercentBookedArray['hours_scheduled']['nhours'])*100, 2, '.', '');

                                $prebookPercentage["key"] = "Week ".$month_week_name;
                            }
                            else
                            {
                                $prebookPercentage["last_year_value"] = "0";
                                $prebookPercentage["key"] = "Week ".$month_week_name;
                            }
                            $prebookPercentage['start_date'] = $datesArray['start_date'];
                            $prebookPercentage['end_date'] = $datesArray['end_date'];
                            $prebookPercentage['current_week'] = $datesArray['current_week'];
                            $tempArr[] = $prebookPercentage;
                           
                        }
                        $dataArray["graph_data"] = $tempArr;
                    }
                    $response['values'][] = $dataArray;
                    $response["start_date"] = $startDate;
                    $response["end_date"] = $endDate;
                    $response["status"] = true;  
                }
                                         
            }else{
                $response["status"] = false;
            }
            return $response;
    }
    
    
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     * This function for get employee schedule hours last year data
     * @param type $account_no
     */
    function setStaffPercentBookedDataForGraphs($dayRangeType="today",$account_no="")
        { 
            
            if($account_no!=''){
             $account_no = salonWebappCloudDe($account_no);
             $salon_config_details = $this->Common_model->getMillSdkConfigDetails($account_no)->row_array();
             $salon_id = $salon_config_details['salon_id'];
            }else{
                $salon_id='';
            }
            //pa($salon_id,'',true);
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            $processedSalons = array();
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    $salon_id = $salonsData["salon_id"];
                    if(!in_array($salon_id, $processedSalons))
                    {
                        pa('',"Reports Cron Running For--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                        $this->salon_id = $salonsData['salon_id'];
                        $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                        pa('','salon Info','');
                    
                       if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes"){
                        // Database Log
                        $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                        $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                        $log['StartingTime'] = date('Y-m-d H:i:s');
                        $log['whichCron'] = 'setStaffPercentBookedDataForGraphs';
                        //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $log['CronUrl'] = MAIN_SERVER_URL.'StaffPercentBookedDataForGraphs/setStaffPercentBookedDataForGraphs/'.$dayRangeType;
                        $log['CronType'] = 1;
                        $log['id'] = 0;
                        $log_id = $this->Common_model->saveMillCronLogs($log);

                        // GET START DATE AND END DATE AS PER PARAMETERS
                        $this->__getStartEndDate($dayRangeType);
                        $getAllStaff = $this->Common_model->getAllStaffMembersBySalon($this->salon_id);
                        //pa($getAllStaff,'staff',false);

                        if(isset($getAllStaff["getAllStaff"]) && !empty($getAllStaff["getAllStaff"]))
                        {
                            foreach($getAllStaff["getAllStaff"] as $staffMembers)
                            {
                                
                                 /*$salonStaffIds = array();
                                $salonStaffIds["salon_id"] = $this->salon_id;
                                $salonStaffIds["staff_id"] = $staff_id;
                                //$arrSalonDetails['staff_name'] = $staffMembers["staff_id"];
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/millAppointmentAndSalonInfoForCron");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                // for local server
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                // close
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $salonStaffIds);
                                $salonStaffResult=curl_exec($ch);
                                $salonStaffResultArray = json_decode($salonStaffResult,true);
                              
                                if(isset($salonStaffResultArray["staff_iid"]) && !empty($salonStaffResultArray["staff_iid"]))
                                {
                                    $staff_iid = $salonStaffResultArray["staff_iid"];
                                }
                                else
                                {
                                    $staff_iid = 0;
                                }

                                if(isset($salonStaffResultArray["staff_name"]) && !empty($salonStaffResultArray["staff_name"]))
                                {
                                    $staff_name = $salonStaffResultArray["staff_name"];
                                }
                                else
                                {
                                    $staff_name = "";
                                }*/
                                $staff_id = $staffMembers["staff_id"];

                                $arrSalonDetails['staff_id'] = $staff_id;
                                $arrSalonDetails['staff_iid'] = $staffMembers["emp_iid"];;
                                //$arrSalonDetails['staff_name'] = $staff_name;
                                $arrSalonDetails['salon_id'] = $salon_id;
                                $arrSalonDetails['dayRangeType'] = $dayRangeType;
                                $arrSalonDetails['start_date'] = $this->startDate;
                                $arrSalonDetails['end_date'] = $this->endDate;
                                $arrSalonDetails['status'] = true;
                                $arrSalonDetails['startDayOfTheWeek'] =  isset($salonsData["salon_start_day_of_week"]) && !empty($salonsData["salon_start_day_of_week"]) ? $salonsData["salon_start_day_of_week"] : ''; 

                               $getAllRetailData = $this->__getSalonReportForOwnerGraphs($arrSalonDetails);
                                pa($getAllRetailData,'getAllRetailData',false);
                                if(isset($getAllRetailData) && !empty($getAllRetailData['values'])){
                                    foreach($getAllRetailData['values'] as $resValue){
                                        $salonId = $resValue["location_id"];
                                        $processedSalons[] = $salonId;
                                        if(isset($resValue["graph_data"]) && !empty($resValue["graph_data"]))
                                        {
                                            foreach($resValue['graph_data'] as $resValuess){
                                                array_walk($resValuess, function (&$value,&$key) { 
                                                    if(($key == 'current_value') || ($key == 'last_year_value'))
                                                        $value = number_format($value, 2, '.', '');
                                                });
                                                if($dayRangeType=='Previousmonths'){
                                                    $this->startDate = $resValuess['start_date'];
                                                    $this->endDate = $resValuess['end_date'];
                                                    $insertdayRangeType = 'Yearly';
                                                }elseif($dayRangeType=='Monthly'){
                                                    $this->startDate = $resValuess['start_date'];
                                                    $this->endDate = $resValuess['end_date'];
                                                    $insertdayRangeType = $dayRangeType;
                                                }else{
                                                    $insertdayRangeType = $dayRangeType;
                                                }
                                                $dataArray['salon_id'] = $salonId;
                                                $dataArray['dayRangeType'] = $insertdayRangeType;
                                                $dataArray['start_date'] = $this->startDate;
                                                $dataArray['end_date'] = $this->endDate;
                                                $dataArray['current_value'] = $resValuess['current_value'];
                                                $dataArray['key'] = $resValuess['key'];
                                                $dataArray['last_year_value'] = $resValuess['last_year_value'];
                                                $dataArray['staff_id'] = $staff_id;
                                                $yearRes = (new DateTime($this->startDate))->format("Y");
                                                $monthRes = (new DateTime($this->endDate))->format("m");
                                                // compare database
                                                $reportsWhere = array('salon_id' => $salonId,'dayRangeType' => $insertdayRangeType,'key' => $resValuess['key'],"MONTH(`start_date`)" => $monthRes, 'YEAR(`start_date`)' => $yearRes,'staff_id' => $staff_id);
                                                if($dayRangeType=='Monthly'){
                                                    $reportsWhere = array('salon_id' => $salonId,'dayRangeType' => $insertdayRangeType,'key' => $resValuess['key'],'staff_id' => $staff_id);
                                                }

                                                $reportsDataForSalon = $this->GraphsOwner_model
                                                                       ->compareStaffPercentBookedOwnerReportsData($reportsWhere)
                                                                       ->row_array();
                                                if(!empty($reportsDataForSalon)){
                                                     $diff_array = array_diff_assoc($dataArray, $reportsDataForSalon);
                                                     if(empty($diff_array)){
                                                        pa('No Updates');
                                                     }else{
                                                        // update
                                                        $diff_array['insert_status'] = self::UPDATED;
                                                        $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                                        try {
                                                            $reportsWhere = array('salon_id' => $salonId,'key' => $resValuess['key'],'dayRangeType' => $insertdayRangeType,'id'=>$reportsDataForSalon['id']);
                                                            $update = $this->GraphsOwner_model->updateStaffPercentBookedOwnerReportsData($reportsWhere,$diff_array);
                                                            pa($diff_array,'Reports Data updated Successfully');
                                                            pa($reportsDataForSalon['id'],'DB ID');

                                                        } catch (Exception $e) {
                                                            echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                                        }
                                                     }

                                                }else{
                                                   
                                                      // Insert Data
                                                        $dataArray["insert_status"] = self::INSERTED;
                                                        $dataArray["insertedDate"] = date("Y-m-d H:i:s");
                                                        $dataArray["updatedDate"] = date("Y-m-d H:i:s");
                                                        try {
                                                            //pa($dataArray,'data',true);
                                                            $insert = $this->GraphsOwner_model->insertStaffPercentBookedOwnerReportsData($dataArray);
                                                            pa($this->db->insert_id(),'Reports Data Inserted
                                                                ');
                                                        } catch (Exception $e) {
                                                            echo 'Reports Insert failed: ' . $e->getMessage()."<br>";
                                                        }
                                                }                        
                                                                    
                                            }
                                          }
                                        }    
         
                                        }else{
                                            echo "No Graph Data";
                                        }

                                    }
         
                                }else{
                                    echo "No Data";
                        }
                    // Database Log
                    $log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);     
                    
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                  } // if close   
                } // for each close
            }else{
                echo "No SalonId's In Server";
            }
            
       } 
   
 }       