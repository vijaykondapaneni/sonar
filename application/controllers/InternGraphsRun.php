<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class InternGraphsRun extends CI_Controller
{

      
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Run all internal graphs
    **/
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Newsalon_model');
    }   
    
    /**
     * Default index Fn
     */
    public function index(){print phpinfo();}
    /**
     * 
     * @param type $dayRangeType
     * @param type $s
     * @param type $e
     */
 
    /**
    For Intenal Graphs Run
    */
    function setCalculationsNewSalon($salon_id='')
        {
            if($salon_id==''){
            print "Please provide salon id";
            exit; 
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Previousmonths';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Previousmonths';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Previousmonths';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Previousmonths';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Yearly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Yearly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Yearly';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Previousmonths';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Yearly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Previousmonths';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Yearly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Previousmonths';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Yearly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Monthly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Previousmonths';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Yearly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Previousmonths';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Yearly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Yearly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Monthly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Yearly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/threemonths';

            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            } 
            
            // staff related graphs

            if($salon_id!=''){
                $salon_details = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                $salon_code = $salon_details['salon_account_id'];
                if($salon_code!=''){
                    $salon_encoded_code = salonWebappCloudEn($salon_code);
                }
            }

            $loop_staff[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Currentmonth';
            $loop_staff[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Currentmonth';
            $loop_staff[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Currentmonth';
            $loop_staff[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Currentmonth';
            $loop_staff[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Monthly';
            $loop_staff[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Monthly';
            $loop_staff[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Monthly';
            $loop_staff[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Monthly';            
            $loop_staff[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Previousmonths';
            $loop_staff[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Previousmonths';
            $loop_staff[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Previousmonths';
            $loop_staff[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Previousmonths';

            $k=1;
            foreach ($loop_staff as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_encoded_code;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$k);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $k++;
            }
        }


    /**
     *Getting past appointments 
     * @param type $salon_id
     */
    public function getCustomAppointments($salon_id,$start_date,$end_date){

        $mainurl = MAIN_SERVER_URL;
        if($salon_id==''){
            print "Please provide salon id";
            exit; 
        }
        if($salon_id!=''){
                $salon_details = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                $salon_code = $salon_details['salon_account_id'];
                if($salon_code!=''){
                    $salon_encoded_code = salonWebappCloudEn($salon_code);
                }
        }else{
            print "Please provide salon id";
            exit; 
        }
        $startDate = !empty($start_date) ? $start_date : date('Y-m-d');
        $endDate = !empty($end_date) ? $end_date : date('Y-m-d');
        /*pa($startDate);
        pa($endDate);*/
        while (strtotime($startDate) <= strtotime($endDate)) { 
            $NewStartDate = $startDate;
            $date = date("Y-m-d", strtotime("+4 day", strtotime($startDate)));
            $NewEndDate = $date;
            $startDate = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            pa($NewStartDate.'--'.$NewEndDate,'Dates');
            //pa($NewEndDate,'end_date');
            $controller_method = 'Wsimport_millsdk_appts/getAppointments/Custom/'.$salon_encoded_code.'/'.$NewStartDate.'/'.$NewEndDate;
            $url = $mainurl.$controller_method;
            pa($url);  
            //file_get_contents($url);
            $op = $this->Common_model->insertDataByUsingCUrl($url);
        }
      
  }    

    
    
   
 
 }       