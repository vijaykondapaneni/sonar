<?php  
  defined('BASEPATH') OR exit('No direct script access allowed');

  /**
   * Class ColorPercentage 
   * Contains all the queries which are related Reports Module.
   */
    class ColorPercentage_model extends CI_Model {

       public function __construct() {
         parent::__construct();
         $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
         $this->color_field_array=array('color','highlight','Retouch','Hi-Lites','Lo-Lites','Minking','Foils','Virgin','Single Process','Crown Highlight','Partial Highlight','Double Process','Glaze','Base Softening','Highlights','Frosting','Balayage','Special Effects','Colors','Coloring','Chemical','Hilite','Hilites','Hilight','High','Perm','Relaxer','Color Retouch','Full Highlight','Custom Color','Permanent Wave');
      }

      CONST MIN_EMP_SERVICE_COUNT = 3;
      CONST LEADER_BOARD_STATUS = 1;
      CONST MIN_PERCETNAGE_BOOKED_COUNT = 2;
      CONST MIN_NEW_CLIENT_COUNT = 0;
      CONST MIN_EMP_SERVICE_RETAIL_COUNT = 0;

      public function getTotalServiceSalesCount($whereConditions){
          $this->DB_ReadOnly->select('SUM(nquantity) as nquantity');
          $get =  $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
          if($get===FALSE){
            $errors = $this->DB_ReadOnly->error();
            $errors['tablename'] = 'mill_service_sales';
            send_mail_database_error($errors);
          }
          //pa($this->DB_ReadOnly->last_query(),'getTotalServiceSalesCount',false);
          return $get;
      }

       public function getTotalColorServiceSalesCount($whereConditions){
            $like_conditions = $this->getColorLIkeConditionsArray($whereConditions['account_no']);
            //pa($like_conditions);
            $this->DB_ReadOnly->select('SUM(nquantity) as nquantity');
            if($like_conditions!=''){
             //$this->DB_ReadOnly->where($like_conditions);
             $this->DB_ReadOnly->where_in('cservicedescription',$like_conditions);
            }

            $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions)->row_array();
            //pa($this->DB_ReadOnly->last_query(),'getTotalColorServiceSalesCount',false);
            $total = $get['nquantity'];
            return $total;
        
      }



      public function getColorPercentage($whereConditions){
        //pa($whereConditions,'whereConditions');
        //service sales count
        $this->DB_ReadOnly->select('SUM(nquantity) as nquantity');
        $get =  $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions)->row_array();
        //pa($this->DB_ReadOnly->last_query(),'getTotalServiceSalesCount',false);
        $service_sales_count = $get['nquantity'];
        // color sales count
        $like_conditions = $this->getColorLIkeConditionsArray($whereConditions['account_no']);
        $this->DB_ReadOnly->select('SUM(nquantity) as nquantity');
        if($like_conditions!=''){
           $this->DB_ReadOnly->where_in('cservicedescription',$like_conditions);
          }
        $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions)->row_array();
        //pa($this->DB_ReadOnly->last_query(),'getTotalColorServiceSalesCount',false);
        $color_service_sales_count = $get['nquantity'];
        $colorPercentage = (!empty($service_sales_count) && !empty($color_service_sales_count) ) ? $this->Common_model->appCloudNumberFormat(($color_service_sales_count/$service_sales_count*100),2)  : '0.00';
        return $colorPercentage;
      }

      public function getColorPercentageLeaderboard($whereConditions){

        //pa($whereConditions); 

        //service sales count

        $this->DB_ReadOnly->select("count(distinct cinvoiceno) invoice_count");
        $this->DB_ReadOnly->select('SUM(nquantity) as nquantity');
        //$this->DB_ReadOnly->group_by('cinvoiceno');
        $this->DB_ReadOnly->having('invoice_count >2');
        $get =  $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions)->row_array();
        //pa($this->DB_ReadOnly->last_query(),'getTotalServiceSalesCount',false);
        $service_sales_count = $get['nquantity'];
        // color sales count
        $this->DB_ReadOnly->select("count(distinct cinvoiceno) invoice_count");
        $this->DB_ReadOnly->select('SUM(nquantity) as nquantity');
        //$this->DB_ReadOnly->group_by('cinvoiceno');
        $this->DB_ReadOnly->having('invoice_count >2');
        $like_conditions = $this->getColorLIkeConditionsArray($whereConditions['account_no']);
        $this->DB_ReadOnly->select('SUM(nquantity) as nquantity');
        if($like_conditions!=''){
           $this->DB_ReadOnly->where_in('cservicedescription',$like_conditions);
          }
        $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions)->row_array();
        //pa($this->DB_ReadOnly->last_query(),'getTotalColorServiceSalesCount',false);
        $color_service_sales_count = $get['nquantity'];
        $colorPercentage = (!empty($service_sales_count) && !empty($color_service_sales_count) ) ? $this->Common_model->appCloudNumberFormat(($color_service_sales_count/$service_sales_count*100),2)  : '0.00';
        return $colorPercentage;
      }
     

      function getColorLIkeConditionsArrayString($account_no){
        $this->db->where('salon_account_id',$account_no);
        $this->db->select('salon_id');
        $res = $this->db->get('mill_all_sdk_config_details')->row_array();
        $salon_id = $res['salon_id'];  
        $list_categories = $this->DB_ReadOnly->query("SELECT iid  FROM `ob_mill_service_class_listing` WHERE `salon_id` = $salon_id AND (`cclassname` LIKE '%Chemical%' || `cclassname` LIKE '%color%')")->result_array();
        //pa($this->DB_ReadOnly->last_query());
        //pa($list_categories);

        if(!empty($list_categories)){
            $new_list = '';
            foreach ($list_categories as $key => $value) {
               $new_list.= $value['iid'].",";
            }
            $iids  = trim($new_list,",");
            //pa($iids);
            $list_services = $this->DB_ReadOnly->query("SELECT cdescript  FROM `ob_mill_services_listing` WHERE `salon_id` = $salon_id AND `category_iid` IN ($iids)")->result_array();
            // pa($list_services);


           /* foreach ($list_services as $key => $value) {
               $color_field_array[] = str_replace("'", "", $value['cdescript']); 
            }

            $queryss = "cservicedescription";*/

              if(!empty($list_services)){
                foreach ($list_services as $key => $value) {
                   $color_field_array[] = str_replace("'", "", $value['cdescript']); 
                   }  
                 $queryss = "cservicedescription";

             }else{
            $queryss = "cservicedescription";
            $color_field_array =$this->color_field_array;
            }

         }else{
            $queryss = "cservicedescription";
            $color_field_array = $this->color_field_array;
         }

          return $this->__make_like_conditions_string($queryss, $color_field_array);
    }

      




       
      function getColorLIkeConditionsArray($account_no){
        $this->db->where('salon_account_id',$account_no);
        $this->db->select('salon_id');
        $res = $this->db->get('mill_all_sdk_config_details')->row_array();
        $salon_id = $res['salon_id'];  
        $list_categories = $this->DB_ReadOnly->query("SELECT iid  FROM `ob_mill_service_class_listing` WHERE `salon_id` = $salon_id AND (`cclassname` LIKE '%Chemical%' || `cclassname` LIKE '%color%')")->result_array();
        //pa($this->DB_ReadOnly->last_query());
        //pa($list_categories);  
        if(!empty($list_categories)){
            $new_list = '';
            foreach ($list_categories as $key => $value) {
               $new_list.= $value['iid'].",";
            }
            $iids  = trim($new_list,",");
            //pa($iids);
            $list_services = $this->DB_ReadOnly->query("SELECT cdescript  FROM `ob_mill_services_listing` WHERE `salon_id` = $salon_id AND `category_iid` IN ($iids)")->result_array();
            //pa($list_services);
             if(!empty($list_services)){
                foreach ($list_services as $key => $value) {
                   $color_field_array[] = str_replace("'", "", $value['cdescript']); 
                   }  
             $queryss = "cservicedescription";

             }else{
            $queryss = "cservicedescription";
            $color_field_array =$this->color_field_array;
            }
         
         }else{
            $queryss = "cservicedescription";
            $color_field_array = $this->color_field_array;
         }

          return $this->__make_like_conditions($queryss, $color_field_array);
    }

   function __make_like_conditions ($fields, array $query)
    {
        $likes = array();
        foreach ($query as $match) {
            $likes[] = $match;
        }
        return  $likes;
    }


    function __make_like_conditions_string ($fields, array $query)
    {
        $likes = array();
        foreach ($query as $match) {
            $likes[] = "$fields = '$match'";
        }
        return '('.implode(' || ', $likes).')';
    }


    public function leadserboarddataforColorSalesNewMethod($data){
      //pa($data);
      $account_no = $data['salonAccountNo'];
      $this->db->where('salon_account_id',$account_no);
      $this->db->select('salon_id');
      $res = $this->db->get('mill_all_sdk_config_details')->row_array();
      $salon_id = $res['salon_id'];  
      //$getAllStaff = $this->Common_model->getAllStaffMembersBySalon($salon_id);
      $this->db->select('staff_id,emp_iid,name');
      $staffWhere = array('salon_id' => $salon_id, 'emp_iid != ' => 0, 'status' => 'Active');
      $getAllStaff = $this->db->get_where(STAFF2_TABLE,$staffWhere)->result_array();
      //pa($getAllStaff,'Staff');
      $all_staff = array();
      $dataArray['highest_color_percent_value'] = '0.00';
      $dataArray['highest_color_percent_employee'] = '';
      $dataArray['highest_color_percent_employee_iid'] = '';
      if(isset($getAllStaff) && !empty($getAllStaff))
      {
          foreach($getAllStaff as $staffMembers)
          {   
              $whereConditions['iempid'] = $staffMembers['emp_iid'];
              $whereConditions['account_no'] = $account_no;
              $whereConditions['tdatetime >='] = $data['startDate'];
              $whereConditions['tdatetime <='] = $data['endDate'];
              $color_percent = $this->getColorPercentageLeaderboard($whereConditions);
              if($color_percent>0){
                $all_staff['color_percent_value'] = $color_percent;
                $all_staff['color_percent_employee'] = $staffMembers['name'];
                $all_staff['percent_employee_iid'] = $staffMembers['emp_iid'];
                $new_array[] = $all_staff;
              }
          }

        //  pa($new_array,'new_array');

          if(!empty($new_array)){
            $higest_array = array_reduce($new_array, function ($a, $b) {
            return @$a['color_percent_value'] > $b['color_percent_value'] ? $a : $b;
             });
            $dataArray['highest_color_percent_value'] = $higest_array['color_percent_value'];
            $dataArray['highest_color_percent_employee'] = $higest_array['color_percent_employee'];
            $dataArray['highest_color_percent_employee_iid'] = $higest_array['percent_employee_iid'];
          }
          return $dataArray;
      }
    }     
}        
