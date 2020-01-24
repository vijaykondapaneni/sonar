<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class GetLastYearData extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Getting last year data
    **/
    CONST INSERTED = 0;
    CONST EXISTING_SERVICE_URL = 'http://mill4.salonintegration.com/wsgetlastyeardata/getAppointmentsData';
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Newsalon_model');
        $this->load->model('Pastappointmentsimport_model');
    }   
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     *Getting past appointments 
     * @param type $salon_id
     */
    public function getPastAppointments($salon_code){
        if($salon_code==''){
            print "Please provide salon Code";
            exit; 
        }

        $yearstartDate = date("Y-") . "01-01";
        $startDate = date('d/m/Y', strtotime($yearstartDate . " -1 year"));
        $today = date('Y-m-d');
        $endDate = date('d/m/Y', strtotime($today));
        $date = date('Y-m-d', strtotime($yearstartDate . " -1 year"));
        $end_date = date('Y-m-d', strtotime($today));
        pa($startDate);
        pa($endDate);
        while (strtotime($date) <= strtotime($end_date)) { 
            $startDate = "$date";
            $date = date("Y-m-d", strtotime("+9 day", strtotime($date)));
            $postdata['salon_code'] = $salon_code;
            $postdata['start_date'] = $startDate;
            $postdata['end_date'] = $date;
            // get last year data
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,self::EXISTING_SERVICE_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); 
            $result=curl_exec($ch);
            //echo $result;exit;
            $res = json_decode($result,true);
            $appointments = $res['appointments'];
            foreach ($appointments as $value) {
                $AppointmentDate = date('Y-m-d', strtotime($value['AppointmentDate']))." ".date('H:i:s', strtotime($value['AppointmentTime']));
                unset($value['AppointmentDate']);
                unset($value['AppointmentTime']);
                unset($value['SlcStatus']);
                unset($value['CreationDate']);
                unset($value['ModifiedDate']);
                unset($value['WinServiceTypeId']);
                unset($value['RowOrder']);
                unset($value['ServiceCategory']);
                unset($value['Price']);
                unset($value['Tax']);
                unset($value['SubTotal']);
                unset($value['Total']);
                unset($value['Tender']);
                unset($value['IsProcessed']);
                unset($value['TConfirmed']);
                unset($value['sdk_status']);
                unset($value['Id']);
                $pAppts = $value;
                $pAppts['AppointmentDate'] = $AppointmentDate;
                $pAppts['CrateatedDate'] = date("Y-m-d H:i:s");
                $pAppts['ModifiedDate'] = date("Y-m-d H:i:s");
                $pAppts['SlcStatus'] = self::INSERTED;
                pa($pAppts,'appointments');
                $res = $this->Pastappointmentsimport_model->insertMillPastAppointments($pAppts);
                $appts_id = $this->db->insert_id();
                pa('',"inserted data ---ID=".$appts_id);
             }
        }    
        
      
  }
}       