<?php  
  defined('BASEPATH') OR exit('No direct script access allowed');

  /**
   * Class DashboardOwner_model
   * Contains all the queries which are related Reports Module.
   */
    class DashboardOwner_model extends CI_Model {
       public function __construct() {
       parent::__construct();
       $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
      }
          CONST MIN_EMP_SERVICE_COUNT = 3;
          CONST LEADER_BOARD_STATUS = 1;
          CONST MIN_PERCETNAGE_BOOKED_COUNT = 2;
          CONST MIN_NEW_CLIENT_COUNT = 0;
          CONST MIN_EMP_SERVICE_RETAIL_COUNT = 0;
        /**
         Get Total SERVICE SALES  
        */
        public function getTotalServiceSales($whereConditions){
            
            $this->DB_ReadOnly->select_sum('nquantity');
            $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
            $get =  $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
            //pa($this->DB_ReadOnly->last_query(),'getTotalServiceSales');
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_service_sales';
              send_mail_database_error($errors);
            }
            return $get;
            
        } 
        /**
        GET Service InvoicesClientIds Count
        */ 
        public function getServiceInvoicesClientIdsCount($whereConditions){
            $this->DB_ReadOnly->select_sum('nquantity');
            $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
            $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count');
            $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
            $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_service_sales';
              send_mail_database_error($errors);
            }
            return $get;
        }
        /**
        Get Service Invoice Count With Prebook True
        */
        public function getServiceInvoiceCountWithPrebookTrue($whereConditions){
            $this->DB_ReadOnly->select_sum('nquantity');
            $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
            $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count');
            $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
            $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_service_sales';
              send_mail_database_error($errors);
            }
            return $get;
        }
        /**
        Get TOTAL RETAIL SALES DETAILS
        */
        public function getTotalProductSales($whereConditions){
            $this->DB_ReadOnly->select_sum('nquantity');
            $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
            $get = $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES,$whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_product_sales';
              send_mail_database_error($errors);
            }
            return $get;
        }
        /**
        GET Product Invoices ClientIds Count
        */
        public function getProductInvoicesClientIdsCount($whereConditions){
               $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count');
               $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
               $get =  $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES,$whereConditions);
               if($get===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_product_sales';
                  send_mail_database_error($errors);
                }
                return $get;
        }
        /**
        GET Gift Card SALES
        */
        public function getTotalGiftCardSales($whereConditions){
            $this->DB_ReadOnly->select_sum('nprice');
            $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count');
            $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
            $this->DB_ReadOnly->where('igifttypeid!=','-99');
            $get = $this->DB_ReadOnly->get_where(MILL_GIFT_CARD_SALES_WITH_BALANCE,$whereConditions);
            /*pa($this->DB_ReadOnly->last_query());
            exit;*/
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_gift_card_sales_with_balance';
              send_mail_database_error($errors);
            }
            return $get;
        }
        
        /**
        Get Employee schedule hours booked
        */
        public function getTotalScheduledHours($whereConditions){
            $this->DB_ReadOnly->select_sum('nhours');
            $get = $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS,$whereConditions);
            //pa($this->DB_ReadOnly->last_query(),'getTotalScheduledHours');
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_employee_schedule_hours';
              send_mail_database_error($errors);
            }
            return $get;
        }

        /**
        Get Employee schedule hours booked
        */
        public function getTotalScheduledHoursJoesph($whereConditions){
            $this->DB_ReadOnly->select_sum('nhours');
            $get = $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS,$whereConditions);
            //pa($this->DB_ReadOnly->last_query(),'getTotalScheduledHours');
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_employee_schedule_hours';
              send_mail_database_error($errors);
            }
            return $get;
        }


        /**
        GET Total Hours Booked
        */
        public function getTotalHoursBooked($data){

            $get = $this->DB_ReadOnly->query("SELECT (SUM( Nstartlen ) + SUM( Nfinishlen )) AS totalhours FROM 
                ".MILL_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != 'Deleted' and 
                ClientId !=  '-999' and
                str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$data['startDate']."' and 
                str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$data['endDate']."'");
            

            /*$get = $this->DB_ReadOnly->query("SELECT (SUM( Nstartlen ) + SUM( Nfinishlen )) AS totalhours FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != '2' and 
                ClientId !=  '-999' and 
                date(AppointmentDate) >= '".$data['startDate']."' and 
                date(AppointmentDate) <= '".$data['endDate']."'");*/
            
            //pa($this->DB_ReadOnly->last_query(),'getTotalHoursBooked');
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_past_appointments';
              send_mail_database_error($errors);
            }
            return $get;
        }

        public function getTotalHoursBookedStaff($data){

            $get = $this->DB_ReadOnly->query("SELECT (SUM( Nstartlen ) + SUM( Nfinishlen )) AS totalhours FROM 
                ".MILL_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != 'Deleted' and 
                ClientId !=  '-999' and
                iempid =  '".$data['iempid']."' and
                str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$data['startDate']."' and 
                str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$data['endDate']."'");
            

            /*$get = $this->DB_ReadOnly->query("SELECT (SUM( Nstartlen ) + SUM( Nfinishlen )) AS totalhours FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != '2' and 
                ClientId !=  '-999' and 
                date(AppointmentDate) >= '".$data['startDate']."' and 
                date(AppointmentDate) <= '".$data['endDate']."'");*/
            
            pa($this->DB_ReadOnly->last_query(),'getTotalHoursBooked');
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_past_appointments';
              send_mail_database_error($errors);
            }
            return $get;
        }


        /**
        GET Service Sales ClientIds
        */
        public function getServiceSalesClientIds($whereConditions){
            $this->DB_ReadOnly->select('DISTINCT(cinvoiceno) as service_client_ids');
            $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_service_sales';
              send_mail_database_error($errors);
            }
            return $get;
        }
        /**
        GET Product Sales ClientIds
        */
        public function getPoductSalesClientIds($whereConditions){
            $this->DB_ReadOnly->select('DISTINCT(cinvoiceno) as retail_client_ids');
            $get = $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES,$whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_product_sales';
              send_mail_database_error($errors);
            }
            return $get;
           
        } 
        /**
        GET Staff Unique Worked Dates 
        */
        public function getStaffUniqueWorkedDates($whereConditions){
                $this->DB_ReadOnly->select('tdatetime');
                $this->DB_ReadOnly->group_by('tdatetime');
                $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
                if($get===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales';
                  send_mail_database_error($errors);
                }
                return $get;
        }

        /**
        GET PRODUCT SALES Details Array
        */
        public function getProductSalesDetailsArrr($whereConditions){
            $this->DB_ReadOnly->select_sum('nquantity');
            $get = $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES,$whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_product_sales';
              send_mail_database_error($errors);
            }
            return $get;
        }
        /**
        GET Service Sales Unique ClientIds Count
        */
        public function getServiceSalesUniqueClientIdsCount($whereConditions,$where=""){
                $this->DB_ReadOnly->select('tdatetime');
                $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
                $this->DB_ReadOnly->group_by('tdatetime');
                if($where!=''){
                $this->DB_ReadOnly->where($where); 
                }  
                $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
                if($get===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales';
                  send_mail_database_error($errors);
                }
                return $get;

        }
        /**
         GET Service Sales Unique ClientIds
        */
        public function getServiceSalesUniqueClientIds($whereConditions){
                $this->DB_ReadOnly->select('iclientid');
                $this->DB_ReadOnly->group_by('iclientid');
                $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
                if($get===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales';
                  send_mail_database_error($errors);
                }
                return $get;

        }
        /**
         GET Service Sales DetailsArrNew Lastyear
        */ 
        public function getServiceSalesDetailsArrNewLastyear($whereConditions,$where=""){

                $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count');
                $this->DB_ReadOnly->select_sum('nquantity');
                $this->DB_ReadOnly->select_sum('nprice * nquantity','nprice');
                if($where!=''){
                $this->DB_ReadOnly->where($where);
                }
                $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
                if($get===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales';
                  send_mail_database_error($errors);
                }
                return $get;

        } 
        /**
        GET Service Sales DetailsArr Repeat LastYear
        */
        public function getServiceSalesDetailsArrRepeatLastYear($whereConditions,$where=""){
                $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count');
                $this->DB_ReadOnly->select_sum('nquantity');
                $this->DB_ReadOnly->select_sum('nprice * nquantity','nprice');
                  if($where!=''){
                $this->DB_ReadOnly->where($where);
                }
                $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
                if($get===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales';
                  send_mail_database_error($errors);
                }
                return $get;

        }

        /**
        GET getTotalColorServiceSales
        */
        public function getTotalColorServiceSales($whereConditions,$likeConditions=""){
            $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
            if($likeConditions!=''){
             $this->DB_ReadOnly->where($likeConditions);
            }
            $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_service_sales';
              send_mail_database_error($errors);
            }
            return $get;

        }
        /**
        GET SERVICED CLIENT COUNT
        */
        public function getClientServicedCount($data){
                $get = $this->DB_ReadOnly->query("SELECT count(DISTINCT ClientId) as client_count FROM 
                        ".MILL_APPTS_TABLE."  
                        WHERE 
                        AccountNo = '".$data['salonAccountNo']."' and 
                        SlcStatus != 'Deleted' and 
                        str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$data['datessformat']."' and 
                        str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$data['plusFourMonthsDate']."' and 
                        DATE(  `MillCreatedDate` ) <=  '".$data['datessformat']."' and 
                        LPrebook =  'true' and 
                        ClientId IN (".$data['uniqueClientIdsJoined'].")");
                if($get===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales';
                  send_mail_database_error($errors);
                }
                return $get;
                

        }
        /**
        GET Service Sales ClientIds
        */
        public function getServiceSalesClientIdsForClientIds($whereConditions){
            $this->DB_ReadOnly->select('DISTINCT(iclientid) as service_client_ids');
            $get =  $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_service_sales';
              send_mail_database_error($errors);
            }
            return $get;
        }

        /**
        GET Product Sales ClientIds
        */
        public function getProductSalesClientIdsForClientIds($whereConditions){
            $this->DB_ReadOnly->select('DISTINCT(iclientid) as retail_client_ids');
            $get =  $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES, $whereConditions);
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_product_sales';
              send_mail_database_error($errors);
            }
            return $get;

        }
         /**
        GET SERVICED Client Id's from appointments
        */
        public function getServicedClientIdsFromAppointments($data){
           
            $get = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as new_client_count FROM 
                    ".MILL_CLIENTS_TABLE." client 
                    join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                    WHERE 
                    appts.AccountNo = '".$data['salonAccountNo']."' and 
                    appts.SlcStatus != '2' and 
                    client.AccountNo = '".$data['salonAccountNo']."' and 
                    date(AppointmentDate) >= '".$data['startDate']."' and 
                    date(AppointmentDate) <= '".$data['endDate']."' and 
                    date(client.clientFirstVistedDate) >= '".$data['startDate']."' and 
                    date(client.clientFirstVistedDate) <= '".$data['endDate']."' and appts.ClientId !='-999'");
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_past_appointments/mill_clients';
              send_mail_database_error($errors);
            }
            return $get;
                
        }

         /**
         GET Repeated SERVICED Client Id's from appointments
        */
        public function getRepeatedServicedClientIdsFromAppointments($data){
                    $get = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as repeated_client_count FROM 
                    ".MILL_CLIENTS_TABLE." client 
                    join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                    WHERE 
                    appts.AccountNo = '".$data['salonAccountNo']."' and 
                    appts.SlcStatus != '2' and 
                    client.AccountNo = '".$data['salonAccountNo']."' and 
                    date(AppointmentDate) >= '".$data['startDate']."' and 
                    date(AppointmentDate) <= '".$data['endDate']."' and 
                    client.clientFirstVistedDate != client.clientLastVistedDate");
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_past_appointments/mill_clients';
                      send_mail_database_error($errors);
                    }

                    return $get;

        }
         /**
         GET Total SERVICED Client Id's from appointments
        */
        public function getTotalServicedClientIdsFromAppointments_bkp($data){

                    $get = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as total_client_count FROM 
                    ".MILL_CLIENTS_TABLE." client 
                    join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                    WHERE 
                    appts.AccountNo = '".$data['salonAccountNo']."' and 
                    appts.SlcStatus != '2' and 
                    client.AccountNo = '".$data['salonAccountNo']."' and 
                    date(AppointmentDate) >= '".$data['startDate']."' and 
                    date(AppointmentDate) <= '".$data['endDate']."'");
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_past_appointments/mill_clients';
                      send_mail_database_error($errors);
                    }
                    return $get;
  
        }

         public function getTotalServicedClientIdsFromAppointments($data){

                    $get = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as total_client_count FROM 
                    ".MILL_CLIENTS_TABLE." client 
                    join ".MILL_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                    WHERE 
                    appts.AccountNo = '".$data['salonAccountNo']."' and 
                    appts.SlcStatus != 'Deleted' and 
                    client.AccountNo = '".$data['salonAccountNo']."' and 
                    str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$data['startDate']."' and 
                    str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$data['endDate']."'");
                   //pa($this->DB_ReadOnly->last_query(),'getTotalServicedClientIdsFromAppointments');
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_past_appointments/mill_clients';
                      send_mail_database_error($errors);
                    }
                    return $get;
  
        }

        /**
         GET Total SERVICED Client Id's from appointments
        */
        public function getHighestBookedAppointments($data){

                     $get =  $this->DB_ReadOnly->query("SELECT COUNT(*) AS total_count,SUM(appts.Nstartlen) as nstartlen,SUM(appts.Nfinishlen) as nfinishlen,emp.name,emp.emp_iid FROM ".MILL_PAST_APPTS_TABLE." appts 
                    join ".STAFF2_TABLE." emp on emp.emp_iid = appts.iempid  
                    WHERE 
                    appts.AccountNo = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS." and
                    appts.ClientId != '-999' and 
                    date(AppointmentDate) >= '".$data['startDate']."' and 
                    date(AppointmentDate) <= '".$data['endDate']."' GROUP BY appts.EmployeeName HAVING total_count > ".self::MIN_PERCETNAGE_BOOKED_COUNT." ORDER BY emp.emp_iid ASC");
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_service_sales';
                      send_mail_database_error($errors);
                    }
                   // pa($this->db->last_query());
                    return $get;

        
        }

         /**
         GET Highest Employee Schedule Hours
        */
        public function getHighestEmployeeScheduledHours($data){
                    $get =  $this->DB_ReadOnly->query("SELECT SUM(empschedule.nhours) as nhours,emp.name,emp.emp_iid FROM ".MILL_EMPLOYEE_SCHEDULE_HOURS." empschedule 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=empschedule.iempid 
                    WHERE 
                    empschedule.account_no = '".$data['salonAccountNo']."' and 
                    empschedule.cworktype = 'Work Time' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    empschedule.dayRangeType = '".$data['dayRangeType']."' and 
                    empschedule.start_date >= '".$data['startDate']."' and 
                    empschedule.end_date <= '".$data['endDate']."' GROUP BY empschedule.iempid ORDER BY emp.emp_iid ASC");
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_service_sales';
                      send_mail_database_error($errors);
                    }
                    return $get;

        
        }

        /**
        GET Leader Board Data
        */
        public function getLeaderBoardData($wherearray,$wherearrayDayRangeType){

                 $percentBookedCount["booked_hours_count"] = $this
                                                  ->getHighestBookedAppointments($wherearray)
                                                  ->result_array();
                 $percentBookedCount["scheduled_hours_count"] = $this
                                                               ->getHighestEmployeeScheduledHours($wherearrayDayRangeType)
                                                               ->result_array();

                if(!empty($percentBookedCount["booked_hours_count"]) && !empty($percentBookedCount["scheduled_hours_count"]))
                {
                    if(COUNT($percentBookedCount['booked_hours_count']) > COUNT($percentBookedCount["scheduled_hours_count"]))
                    {
                        foreach($percentBookedCount['booked_hours_count'] as $key => $booked_hours_count_value)
                        {
                            
                            $search_key = array_search($booked_hours_count_value['emp_iid'], array_column($percentBookedCount['scheduled_hours_count'], 'emp_iid'));
                            //echo $search_key;exit;

                            if($search_key !== false)
                            {
                                if(isset($percentBookedCount['scheduled_hours_count'][$search_key]['nhours']) && !empty($percentBookedCount['scheduled_hours_count'][$search_key]["nhours"]))
                                {
                                    
                                    $totalHoursBookedForSalon = ($booked_hours_count_value['nstartlen'] + $booked_hours_count_value['nfinishlen']);

                                    $highestPercentageBooked['percent_booked'][] = ($totalHoursBookedForSalon/$percentBookedCount['scheduled_hours_count'][$search_key]['nhours'])*100;

                                    $highestPercentageBooked['iempname'][] = trim($booked_hours_count_value["name"]);
                                    $highestPercentageBooked['iid'][] = $booked_hours_count_value['emp_iid'];
                                }
                            }
                        }
                    }
                    else if(COUNT($percentBookedCount['scheduled_hours_count']) > COUNT($percentBookedCount['booked_hours_count']))
                    {
                        foreach($percentBookedCount['scheduled_hours_count'] as $key => $scheduled_hours_count_value)
                        {
                            
                            $search_key = array_search($scheduled_hours_count_value["emp_iid"], array_column($percentBookedCount['booked_hours_count'], 'emp_iid'));
                            //echo $search_key;exit;
                            
                            if($search_key !== false)
                            {
                                if(isset($scheduled_hours_count_value['nhours']) && !empty($scheduled_hours_count_value['nhours']))
                                {
                                    
                                    $totalHoursBookedForSalon = ($percentBookedCount['booked_hours_count'][$search_key]['nstartlen']  + $percentBookedCount["booked_hours_count"][$search_key]['nfinishlen']);

                                    $highestPercentageBooked['percent_booked'][] = ($totalHoursBookedForSalon/$scheduled_hours_count_value["nhours"])*100;

                                    $highestPercentageBooked['iempname'][] = trim($scheduled_hours_count_value["name"]);
                                    $highestPercentageBooked['iid'][] = $scheduled_hours_count_value['emp_iid'];
                                }
                            }
                        }
                    }
                    else
                    {
                        foreach($percentBookedCount["scheduled_hours_count"] as $key => $scheduled_hours_count_value)
                          {
                           
                           $search_key = array_search($scheduled_hours_count_value["emp_iid"], array_column($percentBookedCount["booked_hours_count"], 'emp_iid'));
                           //echo $search_key;exit;
                           
                           if($search_key !== false)
                           {
                            if(isset($scheduled_hours_count_value["nhours"]) && !empty($scheduled_hours_count_value["nhours"]))
                            {
                             
                             //$totalHoursBookedForSalon = ($percentBookedCount["booked_hours_count"][$search_key]["nstartlen"] + $percentBookedCount["booked_hours_count"][$search_key]["ngaplen"] + $percentBookedCount["booked_hours_count"][$search_key]["nfinishlen"])*60;
                    
                             $totalHoursBookedForSalon = ($percentBookedCount["booked_hours_count"][$search_key]["nstartlen"] + $percentBookedCount["booked_hours_count"][$search_key]["nfinishlen"]);
                    
                             $highestPercentageBooked['percent_booked'][] = ($totalHoursBookedForSalon/$scheduled_hours_count_value["nhours"])*100;
                    
                             $highestPercentageBooked['iempname'][] = trim($scheduled_hours_count_value["name"]);
                             $highestPercentageBooked['iid'][] = $scheduled_hours_count_value['emp_iid'];
                            }
                           }
                          }
      
                    }
                    
                    if(!empty($highestPercentageBooked['percent_booked']))
                    {
                        $high_key_value = array_search(max($highestPercentageBooked['percent_booked']), $highestPercentageBooked['percent_booked']);

                        //pa($high_key_value);
                        
                        $returnResult['highest_percent_booked_value'] = $this->Common_model->appCloudNumberFormat($highestPercentageBooked['percent_booked'][$high_key_value], 2);

                        $returnResult['highest_percent_booked_employee'] = $highestPercentageBooked['iempname'][$high_key_value];
                        
                        $returnResult['highest_percent_booked_employee_iid'] = $highestPercentageBooked['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult['highest_percent_booked_value'] = "0.00";
                        $returnResult['highest_percent_booked_employee'] = "";
                        $returnResult['highest_percent_booked_employee_iid'] = "";
                    }
                }
                else
                {
                    $returnResult['highest_percent_booked_value'] = "0.00";
                    $returnResult['highest_percent_booked_employee'] = "";
                    $returnResult['highest_percent_booked_employee_iid'] = "";
                }

             return $returnResult;
        }
        /**
        GET Leader Board Data Retail Sales
        */
        public function leadserboarddataforRetailSales($data){

              $get_highest_retail = $this->DB_ReadOnly->query("SELECT count( retail.cinvoiceno) as invoice_count, sum(retail.nprice*retail.nquantity) as total_retail,emp.name,emp.emp_iid FROM ".MILL_PRODUCT_SALES." retail 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=retail.iempid 
                    WHERE 
                    retail.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    retail.tdatetime >= '".$data['startDate']."' and 
                    retail.tdatetime <= '".$data['endDate']."' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY retail.iempid
                    HAVING invoice_count > ".self::MIN_EMP_SERVICE_RETAIL_COUNT."")->result_array();
                if($get_highest_retail===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_product_sales/staff2_table';
                  send_mail_database_error($errors);
                }

                
                $highestRetailRow = array();
                if(!empty($get_highest_retail)){
                    foreach($get_highest_retail as $result){

                        $highestRetailRow['retail_total'][] = number_format($result['total_retail'], 2, '.', '');
                        $highestRetailRow['iempname'][] = trim($result['name']);
                        $highestRetailRow['iid'][] = $result['emp_iid'];

                    }
                    //print_r($highestRetailRow);exit;

                    if(!empty($highestRetailRow['retail_total']) && ($highestRetailRow['retail_total'] > 0))
                    {

                        $high_key_value = array_search(max($highestRetailRow['retail_total']), $highestRetailRow['retail_total']);
                        $returnResult["highest_retail_total_value"] = $this->Common_model->appCloudNumberFormat($highestRetailRow['retail_total'][$high_key_value], 2);
                        $returnResult["highest_retail_total_employee"] = $highestRetailRow['iempname'][$high_key_value];
                        $returnResult["highest_avg_retail_total_employee_iid"] = $highestRetailRow['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult["highest_retail_total_value"] = "0.00";
                        $returnResult["highest_retail_total_employee"] = "";
                        $returnResult["highest_avg_retail_total_employee_iid"] = "";
                    }
                }else{
                        $returnResult["highest_retail_total_value"] = "0.00";
                        $returnResult["highest_retail_total_employee"] = "";
                        $returnResult["highest_avg_retail_total_employee_iid"] = "";

                }
             return $returnResult; 
        }
        /**
        GET Leader Board Data Service Sales
        */
        public function leadserboarddataforServiceSales($data){
            $get_highest_service = $this->DB_ReadOnly->query("SELECT count(service.cinvoiceno) as invoice_count, sum(service.nprice*service.nquantity) as total_service,emp.name,emp.emp_iid FROM ".MILL_SERVICE_SALES." service 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
                    WHERE 
                    service.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    service.tdatetime >= '".$data['startDate']."' and
                    service.tdatetime <= '".$data['endDate']."' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS." GROUP BY service.iempid HAVING invoice_count > ".self::MIN_EMP_SERVICE_RETAIL_COUNT."")->result_array();
                if($get_highest_service===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_service_sales/staff2_table';
                      send_mail_database_error($errors);
                 }
                
                $highestServiceRow = array();
                if(!empty($get_highest_service)){

                    foreach($get_highest_service as $result){
                        
                        $highestServiceRow['service_total'][] = number_format($result['total_service'], 2, '.', '');
                        $highestServiceRow['iempname'][] = trim($result['name']);
                        $highestServiceRow['iid'][] = $result['emp_iid'];

                    }

                    if(!empty($highestServiceRow['service_total']) && ($highestServiceRow['service_total'] > 0))
                    { 
                        $high_key_value = array_search(max($highestServiceRow['service_total']), $highestServiceRow['service_total']);
                        $returnResult["highest_service_total_value"] = number_format($highestServiceRow['service_total'][$high_key_value], 2, '.', '');
                        $returnResult["highest_service_total_employee"] = $highestServiceRow['iempname'][$high_key_value];
                        $returnResult["highest_service_total_employee_iid"] = $highestServiceRow['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult["highest_service_total_value"] = "0.00";
                        $returnResult["highest_service_total_employee"] = "";
                        $returnResult["highest_service_total_employee_iid"] = "";
                    }
                }else{
                        $returnResult["highest_service_total_value"] = "0.00";
                        $returnResult["highest_service_total_employee"] = "";
                        $returnResult["highest_service_total_employee_iid"] = "";

                }

                return $returnResult;
        }
       /**
        GET Leader Board Data Color Sales
        */
        public function leadserboarddataforColorSales($data){
            $sql_get_color_service_total_sales = $this->DB_ReadOnly->query("SELECT count(DISTINCT service.cinvoiceno) as invoice_count, sum(service.nprice*service.nquantity) as total_service,emp.name,emp.emp_iid FROM ".MILL_SERVICE_SALES." service 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
                    WHERE 
                    service.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    service.tdatetime >= '".$data['startDate']."' and 
                    service.tdatetime <= '".$data['endDate']."' and 
                    (".$data['like_conditions'].")  and 
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY service.iempid HAVING invoice_count > ".self::MIN_EMP_SERVICE_COUNT." ORDER BY service.iempid ASC");
                $colorPercentage["color_service_sales"] = $sql_get_color_service_total_sales->result_array();

                if($sql_get_color_service_total_sales===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales/staff2_table';
                  send_mail_database_error($errors);
                }


                
                $sql_get_all_service_total_sales = $this->DB_ReadOnly->query("SELECT count(DISTINCT service.cinvoiceno) as invoice_count, sum(service.nprice*service.nquantity) as total_service,emp.name,emp.emp_iid FROM ".MILL_SERVICE_SALES." service 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
                    WHERE 
                    service.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    service.tdatetime >= '".$data['startDate']."' and 
                    service.tdatetime <= '".$data['endDate']."'  and 
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY service.iempid HAVING invoice_count > ".self::MIN_EMP_SERVICE_COUNT." ORDER BY service.iempid ASC");
                    $colorPercentage["all_service_sales"] = $sql_get_all_service_total_sales->result_array();
                
                if(!empty($colorPercentage["color_service_sales"]) && !empty($colorPercentage["all_service_sales"]))
                {
                    if(COUNT($colorPercentage["color_service_sales"]) > COUNT($colorPercentage["all_service_sales"]))
                    {
                        foreach($colorPercentage["color_service_sales"] as $key => $color_service_sales_value)
                        {
                            
                            $search_key = array_search($color_service_sales_value["emp_iid"], array_column($colorPercentage["all_service_sales"], 'emp_iid'));
                            //echo $search_key;exit;

                            if($search_key !== false)
                            {
                                if(isset($colorPercentage["all_service_sales"][$search_key]["total_service"]) && !empty($colorPercentage["all_service_sales"][$search_key]["total_service"]) && isset($color_service_sales_value["total_service"]) && !empty($color_service_sales_value["total_service"]))
                                {
                                    
                                    $highestColorPercent['prebook_percent'][] = ($color_service_sales_value["total_service"]/$colorPercentage["all_service_sales"][$search_key]["total_service"])*100;

                                    $highestColorPercent['iempname'][] = trim($color_service_sales_value["name"]);
                                    $highestColorPercent['iid'][] = $color_service_sales_value['emp_iid'];
                                }
                            }
                        }
                    }
                    elseif(COUNT($colorPercentage["all_service_sales"]) > COUNT($colorPercentage["color_service_sales"]))
                    {
                        foreach($colorPercentage["all_service_sales"] as $key => $all_service_sales_value)
                        {
                            
                            $search_key = array_search($all_service_sales_value["emp_iid"], array_column($colorPercentage["color_service_sales"], 'emp_iid'));
                            //echo $search_key;exit;
                            
                            if($search_key !== false)
                            {
                                if(isset($all_service_sales_value["total_service"]) && !empty($all_service_sales_value["total_service"]) && isset($colorPercentage["color_service_sales"][$search_key]["total_service"]) && !empty($colorPercentage["color_service_sales"][$search_key]["total_service"]))
                                {
                                    
                                    $highestColorPercent['color_percent'][] = ($colorPercentage["color_service_sales"][$search_key]["total_service"]/$all_service_sales_value["total_service"])*100;

                                    $highestColorPercent['iempname'][] = trim($all_service_sales_value["name"]);
                                    $highestColorPercent['iid'][] = $all_service_sales_value['emp_iid'];
                                }
                            }
                        }
                    }
                    else
                    {
                        for($i=0;$i<count($colorPercentage["color_service_sales"]);$i++)
                        {
                            //echo $i."<br>";
                            //echo $colorPercentage["prebook_all_count"][$i]["unique_client_count"];exit;
                            if(isset($colorPercentage["all_service_sales"][$i]) && $colorPercentage["color_service_sales"][$i] && !empty($colorPercentage["all_service_sales"][$i]["total_service"]) && !empty($colorPercentage["color_service_sales"][$i]["total_service"]))
                            {

                                $highestColorPercent['color_percent'][] = ($colorPercentage["color_service_sales"][$i]["total_service"]/$colorPercentage["all_service_sales"][$i]["total_service"])*100;
                                $highestColorPercent['iempname'][] = trim($colorPercentage["color_service_sales"][$i]["name"]);
                                $highestColorPercent['iid'][] = $colorPercentage["color_service_sales"][$i]['emp_iid'];

                            }
                            
                            //++$i; 
                        }
                    }
                    //print_r($highestColorPercent);exit;
                    if(!empty($highestColorPercent['color_percent']))
                    {
                        $high_key_value = array_search(max($highestColorPercent['color_percent']), $highestColorPercent['color_percent']);
                        
                        $returnResult["highest_color_percent_value"] = number_format($highestColorPercent['color_percent'][$high_key_value], 2, '.', '');
                        $returnResult["highest_color_percent_employee"] = $highestColorPercent['iempname'][$high_key_value];
                        $returnResult["highest_color_percent_employee_iid"] = $highestColorPercent['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult["highest_color_percent_value"] = "0.00";
                        $returnResult["highest_color_percent_employee"] = "";
                        $returnResult["highest_color_percent_employee_iid"] = "";
                    }
                }
                else
                {
                    $returnResult["highest_color_percent_value"] = "0.00";
                    $returnResult["highest_color_percent_employee"] = "";
                    $returnResult["highest_color_percent_employee_iid"] = "";
                }
         return $returnResult;
        }

        /**
        GET Leader Board Data Avg Retail Ticket
        */
        public function leadserboarddataforRetailAvgTicket($data){
             $rpct_leader_board = array();
                $sql_get_service_invoice = $this->DB_ReadOnly->query("SELECT count(DISTINCT service.cinvoiceno) as invoice_count,emp.name,emp.emp_iid FROM ".MILL_SERVICE_SALES." service 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
                    WHERE 
                    service.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    service.tdatetime >= '".$data['startDate']."' and 
                    service.tdatetime <= '".$data['endDate']."' and 
                    service.lrefund = 'false' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY service.iempid HAVING invoice_count > ".self::MIN_EMP_SERVICE_COUNT."");
                if($sql_get_service_invoice===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales/staff2_table';
                  send_mail_database_error($errors);
                }
                $rpct_leader_board["service_invoice_count"] = $sql_get_service_invoice->result_array();

                $sql_get_highest_retail = $this->DB_ReadOnly->query("SELECT count(DISTINCT retail.cinvoiceno) as invoice_count, sum(retail.nprice*retail.nquantity) as total_retail,emp.name,emp.emp_iid FROM ".MILL_PRODUCT_SALES." retail 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=retail.iempid 
                    WHERE 
                    retail.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    retail.tdatetime >= '".$data['startDate']."' and 
                    retail.tdatetime <= '".$data['endDate']."' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY retail.iempid HAVING invoice_count > ".self::MIN_EMP_SERVICE_COUNT."");
                $rpct_leader_board["total_retail_sales"] = $sql_get_highest_retail->result_array();

                //pa($rpct_leader_board["service_invoice_count"],'subbu');
              
            
                if(!empty($rpct_leader_board["service_invoice_count"]) && !empty($rpct_leader_board["total_retail_sales"]))
                {
                    if(COUNT($rpct_leader_board["service_invoice_count"]) > COUNT($rpct_leader_board["total_retail_sales"]))
                    {
                        foreach($rpct_leader_board["service_invoice_count"] as $key => $service_invoice_value)
                        {
                            
                            $search_key = array_search($service_invoice_value["emp_iid"], array_column($rpct_leader_board["total_retail_sales"], 'emp_iid'));
                            //echo $search_key;exit;

                            if($search_key !== false)
                            {
                                if(isset($service_invoice_value["invoice_count"]) && isset($rpct_leader_board["total_retail_sales"][$search_key]["total_retail"]) && !empty($service_invoice_value["invoice_count"]) && !empty($rpct_leader_board["total_retail_sales"][$search_key]["total_retail"]))
                                {
                                    

                                    $highestRPCT['RPCT_percent'][] = $rpct_leader_board["total_retail_sales"][$search_key]["total_retail"]/$service_invoice_value["invoice_count"];
                                    $highestRPCT['iempname'][] = trim($service_invoice_value["name"]);
                                    $highestRPCT['iid'][] = $service_invoice_value['emp_iid'];
                                }
                            }
                        }
                    }
                    elseif(COUNT($rpct_leader_board["total_retail_sales"]) > COUNT($rpct_leader_board["service_invoice_count"]))
                    {
                        foreach($rpct_leader_board["total_retail_sales"] as $key => $retail_invoice_value)
                        {
                            
                            $search_key = array_search($retail_invoice_value["emp_iid"], array_column($rpct_leader_board["service_invoice_count"], 'emp_iid'));
                            //echo $search_key;exit;

                            if($search_key !== false)
                            {
                                if(isset($rpct_leader_board["service_invoice_count"][$search_key]["invoice_count"]) && isset($retail_invoice_value["total_retail"]) && !empty($rpct_leader_board["service_invoice_count"][$search_key]["invoice_count"]) && !empty($rpct_leader_board["total_retail"]))
                                {
                                    $highestRPCT['RPCT_percent'][] = $retail_invoice_value["total_retail"]/$rpct_leader_board["service_invoice_count"][$search_key]["invoice_count"];
                                    $highestRPCT['iempname'][] = trim($retail_invoice_value["name"]);
                                    $highestRPCT['iid'][] = $retail_invoice_value['emp_iid'];
                                }
                            }
                        }
                    }
                    else
                    {

                        for($i=0;$i<count($rpct_leader_board["service_invoice_count"]);$i++)
                        {
                            //echo $i."<br>";
                            //echo $prebookTrueCount["prebook_all_count"][$i]["unique_client_count"];exit;
                            if(isset($rpct_leader_board["service_invoice_count"][$i]["invoice_count"]) && isset($rpct_leader_board["total_retail_sales"][$i]["total_retail"]) && !empty($rpct_leader_board["service_invoice_count"][$i]["invoice_count"]) && !empty($rpct_leader_board["total_retail_sales"][$i]["total_retail"]))
                            {
                                $highestRPCT['RPCT_percent'][] = $rpct_leader_board["total_retail_sales"][$i]["total_retail"]/$rpct_leader_board["service_invoice_count"][$i]["invoice_count"];
                                $highestRPCT['iempname'][] = trim($rpct_leader_board["total_retail_sales"][$i]["name"]);
                                $highestRPCT['iid'][] = $rpct_leader_board["total_retail_sales"][$i]['emp_iid'];
                            }
                        }
                    }
                    if(!empty($highestRPCT['RPCT_percent']))
                    {
                        $high_key_value = array_search(max($highestRPCT['RPCT_percent']), $highestRPCT['RPCT_percent']);
                        $returnResult["highest_avg_rpct_value"] = $this->Common_model->appCloudNumberFormat($highestRPCT['RPCT_percent'][$high_key_value], 2);
                        $returnResult["highest_avg_rpct_employee"] = $highestRPCT['iempname'][$high_key_value];
                        $returnResult["highest_avg_rpct_employee_iid"] = $highestRPCT['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult["highest_avg_rpct_value"] = "0.00";
                        $returnResult["highest_avg_rpct_employee"] = "";
                        $returnResult["highest_avg_rpct_employee_iid"] = "";
                    }
                }
                else
                {
                    $returnResult["highest_avg_rpct_value"] = "0.00";
                    $returnResult["highest_avg_rpct_employee"] = "";
                    $returnResult["highest_avg_rpct_employee_iid"] = "";
                }
         return $returnResult;
        }
       
       /**
        GET Leader Board Data Avg Service Ticket
        */
        public function leadserboarddataforServiceAvgTicket($data){
           $sql_get_highest_service = $this->DB_ReadOnly->query("SELECT count(DISTINCT retail.cinvoiceno) as invoice_count, sum(retail.nprice*retail.nquantity) as total_service,emp.name,emp.emp_iid FROM ".MILL_SERVICE_SALES." retail 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=retail.iempid 
                    WHERE 
                    retail.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    retail.tdatetime >= '".$data['startDate']."' and 
                    retail.tdatetime <= '".$data['endDate']."' and 
                    retail.lrefund = 'false'  and 
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY retail.iempid HAVING invoice_count > ".self::MIN_EMP_SERVICE_COUNT."")->result_array();
            if($sql_get_highest_service===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales/staff2_table';
                  send_mail_database_error($errors);
            }

                $highestRetailRow = array();
                if(!empty($sql_get_highest_service)){
                    foreach($sql_get_highest_service as $result)
                    {
                        
                        $highestRetailRow['avgServiceTicket'][] = number_format($result['total_service']/$result['invoice_count'], 2, '.', ' ');
                        $highestRetailRow['iempname'][] = trim($result['name']);
                        $highestRetailRow['iid'][] = $result['emp_iid'];
                    }
                    //print_r($highestRetailRow);
                    
                    if(!empty($highestRetailRow['avgServiceTicket']))
                    {
                        $high_key_value = array_search(max($highestRetailRow['avgServiceTicket']), $highestRetailRow['avgServiceTicket']);

                        $returnResult["highest_avg_serviceTicket_value"] = number_format($highestRetailRow['avgServiceTicket'][$high_key_value], 2, '.', '');
                        $returnResult["highest_avg_serviceTicket_employee"] = $highestRetailRow['iempname'][$high_key_value];
                        $returnResult["highest_avg_serviceTicket_employee_iid"] = $highestRetailRow['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult["highest_avg_serviceTicket_value"] = "0.00";
                        $returnResult["highest_avg_serviceTicket_employee"] = "";
                        $returnResult["highest_avg_serviceTicket_employee_iid"] = "";
                    }
                    //print_r($highestRetailRow);exit;
                }
                else
                {
                    $returnResult["highest_avg_serviceTicket_value"] = "0.00";
                    $returnResult["highest_avg_serviceTicket_employee"] = "";
                    $returnResult["highest_avg_serviceTicket_employee_iid"] = "";
                }

                return $returnResult;

       }

        /**
        GET Leader Board Data for  %Prebooked
        */
        public function leadserboarddataforPrebooked($data){
                $prebookTrueCount = array();
                $sql_get_highest_prebook_service_true = $this->DB_ReadOnly->query("SELECT count(DISTINCT service.iclientid) as unique_client_count,emp.name,emp.emp_iid,count(service.id) as service_count FROM ".MILL_SERVICE_SALES." service 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
                    WHERE 
                    service.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    service.lprebook = 'true' and
                    service.tdatetime >= '".$data['startDate']."' and 
                    service.tdatetime <= '".$data['endDate']."' and 
                    service.lrefund = 'false' and 
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY service.iempid HAVING service_count > ".self::MIN_EMP_SERVICE_COUNT."");
                if($sql_get_highest_prebook_service_true===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales/staff2_table';
                  send_mail_database_error($errors);
                }
                $prebookTrueCount["prebook_true_count"] = $sql_get_highest_prebook_service_true->result_array();
               
                $sql_get_highest_prebook_service_all = $this->DB_ReadOnly->query("SELECT count(DISTINCT service.iclientid) as unique_client_count,emp.name,emp.emp_iid,count(service.id) as service_count FROM ".MILL_SERVICE_SALES." service 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
                    WHERE 
                    service.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    service.tdatetime >= '".$data['startDate']."' and 
                    service.tdatetime <= '".$data['endDate']."' and 
                    service.lrefund = 'false'  and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY service.iempid HAVING service_count > ".self::MIN_EMP_SERVICE_COUNT."");
                $prebookTrueCount["prebook_all_count"] = $sql_get_highest_prebook_service_all->result_array();

                if(!empty($prebookTrueCount["prebook_all_count"]) && !empty($prebookTrueCount["prebook_true_count"]))
                {
                    if(COUNT($prebookTrueCount["prebook_all_count"]) > COUNT($prebookTrueCount["prebook_true_count"]))
                    {
                        foreach($prebookTrueCount["prebook_all_count"] as $key => $prebook_value)
                        {
                            $search_key = array_search($prebook_value["emp_iid"], array_column($prebookTrueCount["prebook_true_count"], 'emp_iid'));
                            if($search_key !== false)
                            {
                                if(isset($prebook_value["unique_client_count"]) && isset($prebookTrueCount["prebook_true_count"][$search_key]["unique_client_count"]) && !empty($prebook_value["unique_client_count"]) && !empty($prebookTrueCount["prebook_true_count"][$search_key]["unique_client_count"]))
                                {
                                    $highestPrebook['prebook_percent'][] = ($prebookTrueCount["prebook_true_count"][$search_key]["unique_client_count"]/$prebook_value["unique_client_count"])*100;
                                    $highestPrebook['iempname'][] = trim($prebook_value["name"]);
                                    $highestPrebook['iid'][] = $prebook_value['emp_iid'];
                                }
                            }
                        }
                    }
                    elseif(COUNT($prebookTrueCount["prebook_true_count"]) > COUNT($prebookTrueCount["prebook_all_count"]))
                    {
                        foreach($prebookTrueCount["prebook_true_count"] as $key => $prebookTrue_value)
                        {
                            $search_key = array_search($prebookTrue_value["iid"], array_column($prebookTrueCount["prebook_all_count"], 'iid'));
                         
                            if($search_key !== false)
                            {
                                if(isset($prebookTrueCount["prebook_all_count"][$search_key]["unique_client_count"]) && isset($prebookTrue_value["unique_client_count"]) && !empty($prebookTrueCount["prebook_all_count"][$search_key]["unique_client_count"]) && !empty($prebookTrueCount["unique_client_count"]))
                                {
                                    

                                    $highestPrebook['prebook_percent'][] = ($prebookTrue_value["unique_client_count"]/$prebookTrueCount["prebook_all_count"][$search_key]["unique_client_count"])*100;
                                    $highestPrebook['iempname'][] = trim($prebookTrue_value["name"]);
                                    $highestPrebook['iid'][] = $prebookTrue_value['emp_iid'];
                                }
                            }
                        }
                    }
                    else
                    {
                        for($i=0;$i<count($prebookTrueCount["prebook_true_count"]);$i++)
                        {
                            //echo $i."<br>";
                            //echo $prebookTrueCount["prebook_all_count"][$i]["unique_client_count"];exit;
                            if(isset($prebookTrueCount["prebook_true_count"][$i]["unique_client_count"]) && isset($prebookTrueCount["prebook_all_count"][$i]["unique_client_count"]) && !empty($prebookTrueCount["prebook_true_count"][$i]["unique_client_count"]) && !empty($prebookTrueCount["prebook_all_count"][$i]["unique_client_count"]))
                            {
                                $highestPrebook['prebook_percent'][] = ($prebookTrueCount["prebook_true_count"][$i]["unique_client_count"]/$prebookTrueCount["prebook_all_count"][$i]["unique_client_count"])*100;
                                $highestPrebook['iempname'][] = trim($prebookTrueCount["prebook_true_count"][$i]["name"]);
                                $highestPrebook['iid'][] = $prebookTrueCount["prebook_true_count"][$i]['emp_iid'];
                            }
                        }
                    }
                    if(!empty($highestPrebook['prebook_percent']))
                    {
                        $high_key_value = array_search(max($highestPrebook['prebook_percent']), $highestPrebook['prebook_percent']);
                        $returnResult["highest_prebook_value"] = $this->Common_model->appCloudNumberFormat($highestPrebook['prebook_percent'][$high_key_value], 2);
                        $returnResult["highest_prebook_sold_employee"] = $highestPrebook['iempname'][$high_key_value];
                        $returnResult["highest_prebook_sold_employee_iid"] = $highestPrebook['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult["highest_prebook_value"] = "0.00";
                        $returnResult["highest_prebook_sold_employee"] = "";
                        $returnResult["highest_prebook_sold_employee_iid"] = "";
                    }
                }
                else
                {
                    $returnResult["highest_prebook_value"] = "0.00";
                    $returnResult["highest_prebook_sold_employee"] = "";
                    $returnResult["highest_prebook_sold_employee_iid"] = "";
                }

                return $returnResult;
       }
       
       
       /**
       Get Leader board data fro new clients
       */

       public function getLeaderBoardDataNewClients_bkp($data,$dayRangeType){

       $get_highest_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT service.iclientid) as new_client_count,emp.name,emp.emp_iid,count(service.id) as service_count FROM ".MILL_SERVICE_SALES." service
           join ".MILL_CLIENTS_TABLE." client on service.iclientid=client.ClientId AND service.account_no=client.AccountNo
           join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid
           WHERE
           service.account_no = '".$data['salonAccountNo']."' and
           emp.account_no = '".$data['salonAccountNo']."' and
           service.tdatetime >= '".$data['startDate']."' and
           service.tdatetime <= '".$data['endDate']."' and
           service.lrefund = 'false' and
           date(client.clientFirstVistedDate) >= '".$data['startDate']."' and
           date(client.clientFirstVistedDate) <= '".$data['endDate']."' and
           emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
           GROUP BY service.iempid HAVING service_count > ".self::MIN_NEW_CLIENT_COUNT."")->result_array();
       pa($this->DB_ReadOnly->last_query());
       //print_r($get_highest_new_clients_from_appts);exit;
       if($get_highest_new_clients_from_appts===FALSE){
           $errors = $this->DB_ReadOnly->error();
           $errors['tablename'] = 'mill_past_appointments/mill_clients';
           send_mail_database_error($errors);
       }

       $highestNewClientsRow = array();

       if(!empty($get_highest_new_clients_from_appts)){

           foreach($get_highest_new_clients_from_appts as $result){
               $highestNewClientsRow['new_client_count'][] = $result['new_client_count'];
               $highestNewClientsRow['iempname'][] = trim($result['name']);
               $highestNewClientsRow['iid'][] = $result['emp_iid'];

               //$i++;
           }
           //print_r($highestNewClientsRow);exit;

           if(!empty($highestNewClientsRow['new_client_count']))
           {
               $high_key_value = array_search(max($highestNewClientsRow['new_client_count']), $highestNewClientsRow['new_client_count']);

               $returnResult["highest_new_client_value"] = $highestNewClientsRow['new_client_count'][$high_key_value];
               $returnResult["highest_new_client_employee"] = $highestNewClientsRow['iempname'][$high_key_value];
               $returnResult["highest_new_client_employee_iid"] = $highestNewClientsRow['iid'][$high_key_value];
           }
           else
           {
               $returnResult["highest_new_client_value"] = "0";
               $returnResult["highest_new_client_employee"] = "";
               $returnResult["highest_new_client_employee_iid"] = "";
           }
           return $returnResult;
       }
   }
    
    public function getLeaderBoardDataNewClients($data,$dayRangeType){
            
                     if($dayRangeType=='today'){

                        $get_highest_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as new_client_count,staff.name,count(appts.id) as appointments_count, emp_iid FROM 
                            ".MILL_APPTS_TABLE." appts 
                            join ".MILL_CLIENTS_TABLE." client on appts.ClientId=client.ClientId 
                            join ".STAFF2_TABLE." staff on appts.iempid=staff.emp_iid 
                            WHERE 
                            appts.AccountNo = '".$data['salonAccountNo']."' and 
                            appts.SlcStatus != 'Deleted' and 
                            client.AccountNo = '".$data['salonAccountNo']."' and 
                            staff.account_no = '".$data['salonAccountNo']."' and 
                            str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$data['startDate']."' and 
                            str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$data['endDate']."' and 
                            ((date(client.clientFirstVistedDate)= '".$data['startDate']."') 
                            or (client.clientFirstVistedDate='0001-01-01 00:00:00')) 
                             and appts.ClientId !='-999'
                            GROUP BY staff.emp_iid HAVING appointments_count > ".self::MIN_NEW_CLIENT_COUNT."")->result_array();
                       /*$get_highest_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as new_client_count,staff.name,count(appts.id) as appointments_count,emp_iid  FROM 
                            ".MILL_PAST_APPTS_TABLE." appts 
                            join ".MILL_CLIENTS_TABLE." client on appts.ClientId=client.ClientId 
                            join ".STAFF2_TABLE." staff on appts.iempid=staff.emp_iid 
                            WHERE 
                            appts.AccountNo = '".$data['salonAccountNo']."' and 
                            appts.SlcStatus != '2' and 
                            client.AccountNo = '".$data['salonAccountNo']."' and 
                            staff.account_no = '".$data['salonAccountNo']."' and 
                            date(AppointmentDate) >= '".$data['startDate']."' and 
                            date(AppointmentDate) <= '".$data['endDate']."' and 
                            ((date(client.clientFirstVistedDate)=date(client.clientLastVistedDate)) 
                            or (client.clientFirstVistedDate='0001-01-01 00:00:00')) 
                             and appts.ClientId !='-999'
                            GROUP BY staff.emp_iid HAVING appointments_count > ".self::MIN_NEW_CLIENT_COUNT."")->result_array();*/
                     }else{
                        $get_highest_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT service.iclientid) as new_client_count,emp.name,emp.emp_iid,count(service.id) as service_count FROM ".MILL_SERVICE_SALES." service
                           join ".MILL_CLIENTS_TABLE." client on service.iclientid=client.ClientId AND service.account_no=client.AccountNo
                           join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid
                           WHERE
                           service.account_no = '".$data['salonAccountNo']."' and
                           emp.account_no = '".$data['salonAccountNo']."' and
                           service.tdatetime >= '".$data['startDate']."' and
                           service.tdatetime <= '".$data['endDate']."' and
                           service.lrefund = 'false' and
                           date(client.clientFirstVistedDate) >= '".$data['startDate']."' and
                           date(client.clientFirstVistedDate) <= '".$data['endDate']."' and
                           emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                           GROUP BY service.iempid HAVING service_count > ".self::MIN_NEW_CLIENT_COUNT."")->result_array();
                      /* $get_highest_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as new_client_count,staff.name,count(appts.id) as appointments_count,emp_iid FROM 
                            ".MILL_PAST_APPTS_TABLE." appts 
                            join ".MILL_CLIENTS_TABLE." client on appts.ClientId=client.ClientId 
                            join ".STAFF2_TABLE." staff on appts.iempid=staff.emp_iid 
                            WHERE 
                            appts.AccountNo = '".$data['salonAccountNo']."' and 
                            appts.SlcStatus != '2' and 
                            client.AccountNo = '".$data['salonAccountNo']."' and 
                            staff.account_no = '".$data['salonAccountNo']."' and 
                            date(AppointmentDate) >= '".$data['startDate']."' and 
                            date(AppointmentDate) <= '".$data['endDate']."' and 
                            date(client.clientFirstVistedDate) >= '".$data['startDate']."' and 
                            date(client.clientFirstVistedDate) <= '".$data['endDate']."' and appts.ClientId !='-999'
                            GROUP BY staff.emp_iid HAVING appointments_count > ".self::MIN_NEW_CLIENT_COUNT."")->result_array();*/
                     }
                     //print_r($get_highest_new_clients_from_appts);exit;
                if($get_highest_new_clients_from_appts===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_past_appointments/mill_clients';
                  send_mail_database_error($errors);
                }
                
                $highestNewClientsRow = array();

                if(!empty($get_highest_new_clients_from_appts)){

                    foreach($get_highest_new_clients_from_appts as $result){
                        $highestNewClientsRow['new_client_count'][] = $result['new_client_count'];
                        $highestNewClientsRow['iempname'][] = trim($result['name']);
                        $highestNewClientsRow['iid'][] = $result['emp_iid'];
                                                
                        //$i++;
                    }
                    //print_r($highestNewClientsRow);exit;

                    if(!empty($highestNewClientsRow['new_client_count']))
                    {
                        $high_key_value = array_search(max($highestNewClientsRow['new_client_count']), $highestNewClientsRow['new_client_count']);

                        $returnResult["highest_new_client_value"] = $highestNewClientsRow['new_client_count'][$high_key_value];
                        $returnResult["highest_new_client_employee"] = $highestNewClientsRow['iempname'][$high_key_value];
                        $returnResult["highest_new_client_employee_iid"] = $highestNewClientsRow['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult["highest_new_client_value"] = "0";
                        $returnResult["highest_new_client_employee"] = "";
                        $returnResult["highest_new_client_employee_iid"] = "";
                    }
                   return $returnResult; 
                }
       }

       /**
       Get Leader board data fro Percentage Retail To Service Sales
       */
       public function leadserboarddataforPercentageRetailToServiceSales($data){
                $sql_get_total_service_sales = $this->DB_ReadOnly->query("SELECT sum(service.nprice*service.nquantity) as total_service,emp.name,emp.emp_iid,count(service.id) as service_count FROM ".MILL_SERVICE_SALES." service 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
                    WHERE 
                    service.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    service.tdatetime >= '".$data['startDate']."' and 
                    service.tdatetime <= '".$data["endDate"]."' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY service.iempid  HAVING service_count > ".self::MIN_EMP_SERVICE_COUNT."");
                if($sql_get_total_service_sales===FALSE){
                  $errors = $this->DB_ReadOnly->error();
                  $errors['tablename'] = 'mill_service_sales/staff2_table';
                  send_mail_database_error($errors);
                }
                $percentageRetailToServiceSales_leader_board["total_service_sales"] = $sql_get_total_service_sales->result_array();
               // pa($this->db->last_query());
                $sql_get_total_retail_sales = $this->DB_ReadOnly->query("SELECT sum(retail.nprice*retail.nquantity) as total_retail,emp.name,emp.emp_iid,count(retail.id) as retail_count FROM ".MILL_PRODUCT_SALES." retail 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=retail.iempid 
                    WHERE 
                    retail.account_no = '".$data['salonAccountNo']."' and 
                    emp.account_no = '".$data['salonAccountNo']."' and 
                    retail.tdatetime >= '".$data["startDate"]."' and 
                    retail.tdatetime <= '".$data["endDate"]."' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                    GROUP BY retail.iempid HAVING retail_count > ".self::MIN_EMP_SERVICE_COUNT."");
                $percentageRetailToServiceSales_leader_board["total_retail_sales"] = $sql_get_total_retail_sales->result_array();

               // pa($this->db->last_query());

                if(!empty($percentageRetailToServiceSales_leader_board["total_service_sales"]) && !empty($percentageRetailToServiceSales_leader_board["total_retail_sales"]))
                {
                    //pa(COUNT($percentageRetailToServiceSales_leader_board["total_service_sales"]),'servicesales');
                   // pa(COUNT($percentageRetailToServiceSales_leader_board["total_retail_sales"]),'retailsales');    
                    if(COUNT($percentageRetailToServiceSales_leader_board["total_service_sales"]) > COUNT($percentageRetailToServiceSales_leader_board["total_retail_sales"]))
                    {
                        foreach($percentageRetailToServiceSales_leader_board["total_service_sales"] as $key => $total_service_value)
                        {
                            
                            $search_key = array_search($total_service_value["emp_iid"], array_column($percentageRetailToServiceSales_leader_board["total_retail_sales"], 'emp_iid'));
                            //echo $search_key;exit;

                            if($search_key !== false)
                            {
                                if(isset($total_service_value["total_service"]) && isset($percentageRetailToServiceSales_leader_board["total_retail_sales"][$search_key]["total_retail"]) && !empty($total_service_value["total_service"]) && !empty($percentageRetailToServiceSales_leader_board["total_retail_sales"][$search_key]["total_retail"]))
                                {
                                    $highest_percentage_retail_to_service_sales['percentage_retail_to_service_sales'][] = ($percentageRetailToServiceSales_leader_board["total_retail_sales"][$search_key]["total_retail"]/$total_service_value["total_service"])*100;
                                    $highest_percentage_retail_to_service_sales['iempname'][] = trim($total_service_value["name"]);
                                    $highest_percentage_retail_to_service_sales['iid'][] = $total_service_value['emp_iid'];
                                }
                            }
                        }
                    }
                    elseif(COUNT($percentageRetailToServiceSales_leader_board["total_retail_sales"]) > COUNT($percentageRetailToServiceSales_leader_board["total_service_sales"]))
                    {
                        foreach($percentageRetailToServiceSales_leader_board["total_retail_sales"] as $key => $total_retail_value)
                        {
                           
                            $search_key = array_search($total_retail_value["emp_iid"], array_column($percentageRetailToServiceSales_leader_board["total_service_sales"], 'emp_iid'));
                            //echo $search_key;exit;

                            if($search_key !== false)
                            {
                                if(isset($percentageRetailToServiceSales_leader_board["total_service_sales"][$search_key]["total_service"]) && isset($total_retail_value["total_retail"]) && !empty($percentageRetailToServiceSales_leader_board["total_service_sales"][$search_key]["total_service"]) && !empty($total_retail_value["total_retail"]))
                                {
                                    $highest_percentage_retail_to_service_sales['percentage_retail_to_service_sales'][] = ($total_retail_value["total_retail"]/$percentageRetailToServiceSales_leader_board["total_service_sales"][$search_key]["total_service"])*100;
                                    $highest_percentage_retail_to_service_sales['iempname'][] = trim($total_retail_value["name"]);
                                    $highest_percentage_retail_to_service_sales['iid'][] = $total_retail_value['emp_iid'];
                                }
                            }
                        }
                    }
                    else
                    {
                        for($i=0;$i<count($percentageRetailToServiceSales_leader_board["total_service_sales"]);$i++)
                        {
                            if(isset($percentageRetailToServiceSales_leader_board["total_service_sales"][$i]["total_service"]) && isset($percentageRetailToServiceSales_leader_board["total_retail_sales"][$i]["total_retail"]) && !empty($percentageRetailToServiceSales_leader_board["total_service_sales"][$i]["total_service"]) && !empty($percentageRetailToServiceSales_leader_board["total_retail_sales"][$i]["total_retail"]))
                            {
                                $highest_percentage_retail_to_service_sales['percentage_retail_to_service_sales'][] = ($percentageRetailToServiceSales_leader_board["total_retail_sales"][$i]["total_retail"]/$percentageRetailToServiceSales_leader_board["total_service_sales"][$i]["total_service"])*100;
                                $highest_percentage_retail_to_service_sales['iempname'][] = trim($percentageRetailToServiceSales_leader_board["total_retail_sales"][$i]["name"]);
                                $highest_percentage_retail_to_service_sales['iid'][] = $percentageRetailToServiceSales_leader_board["total_retail_sales"][$i]['emp_iid'];
                            }
                        }
                    }

                   
                    if(!empty($highest_percentage_retail_to_service_sales['percentage_retail_to_service_sales']))
                    {
                        $high_key_value = array_search(max($highest_percentage_retail_to_service_sales['percentage_retail_to_service_sales']), $highest_percentage_retail_to_service_sales['percentage_retail_to_service_sales']);
                        //pa($high_key_value,'high_key_value');
                        $returnResult["highest_percentage_retail_to_service_sales_value"] = number_format($highest_percentage_retail_to_service_sales['percentage_retail_to_service_sales'][$high_key_value], 2, '.', '');
                        $returnResult["highest_percentage_retail_to_service_sales_employee"] = $highest_percentage_retail_to_service_sales['iempname'][$high_key_value];
                        $returnResult["highest_percentage_retail_to_service_sales_employee_iid"] = $highest_percentage_retail_to_service_sales['iid'][$high_key_value];
                    }
                    else
                    {
                        $returnResult["highest_percentage_retail_to_service_sales_value"] = "0.00";
                        $returnResult["highest_percentage_retail_to_service_sales_employee"] = "";
                        $returnResult["highest_percentage_retail_to_service_sales_employee_iid"] = "";
                    }
                }
                else
                {
                    $returnResult["highest_percentage_retail_to_service_sales_value"] = "0.00";
                    $returnResult["highest_percentage_retail_to_service_sales_employee"] = "";
                    $returnResult["highest_percentage_retail_to_service_sales_employee_iid"] = "";
                }
                   return $returnResult; 
         } 

        /**
            Get Top Five Service Sales 
        */
        public function topFiveServiceSales($data){
           
            $get = $this->DB_ReadOnly->query("SELECT service.cservicedescription as service_description,
            service.cservicecode as service_code,
            ROUND(SUM(nprice * nquantity),2) as TOTAL_PRICE 
            FROM ".MILL_SERVICE_SALES."   as service 
            WHERE service.account_no = '".$data['salonAccountNo']."'
            AND   service.tdatetime >= '".$data['startDate']."'
            AND   service.tdatetime <= '".$data['endDate']."'
            GROUP BY service.cservicecode ORDER BY TOTAL_PRICE DESC LIMIT 0,5");
            if($get===FALSE){
              $errors = $this->DB_ReadOnly->error();
              $errors['tablename'] = 'mill_service_sales';
              send_mail_database_error($errors);
            }
            return $get;

          } 

        /**
            Get Top Five Product Sales 
        */
        public function topFiveProductSales($data){
           
                    $get = $this->DB_ReadOnly->query("SELECT product.cproductdescription  as service_description,
                    product.cproductcode as service_code,
                    ROUND(SUM(nprice * nquantity),2) as TOTAL_PRICE 
                    FROM ".MILL_PRODUCT_SALES."   as product 
                    WHERE product.account_no = '".$data['salonAccountNo']."'
                    AND   product.tdatetime >= '".$data['startDate']."'
                    AND   product.tdatetime <= '".$data['endDate']."'
                    GROUP BY product.cproductcode
                    ORDER BY TOTAL_PRICE DESC LIMIT 0,5"); 
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_product_sales';
                      send_mail_database_error($errors);
                    }
                    return $get;

   
          }

        /**
            Get Top Five Product Sales 
        */
        public function getTopFiveOwnerReport($whereConditions,$selectColumns){
                   
                   $this->DB_ReadOnly->select($selectColumns)->where($whereConditions);
                   $get = $this->DB_ReadOnly->get(MILL_TOPFIVE_SERVICES_OWNER_REPORT);
                   if($get===FALSE){
                    $errors = $this->DB_ReadOnly->error();
                    $errors['tablename'] = 'mill_topfive_services_owner_report';
                    send_mail_database_error($errors);
                   }
                   return $get;

          }
        /**
            Get Top Five Product Sales 
        */
        public function updateTopFiveOwnerReport($whereConditions,$data){
                    $this->db->where($whereConditions);
                    $update = $this->db->update(MILL_TOPFIVE_SERVICES_OWNER_REPORT, $data);
                    if($update===FALSE){
                      $errors = $this->db->error();
                      $errors['tablename'] = 'mill_topfive_services_owner_report';
                      send_mail_database_error($errors);
                    }


          }
        /**
            Get Top Five Product Sales 
        */
        public function insertTopFiveOwnerReport($data){
                  $ins = $this->db->insert(MILL_TOPFIVE_SERVICES_OWNER_REPORT, $data);
                  if($ins===FALSE){
                      $errors = $this->db->error();
                      $errors['tablename'] = 'mill_topfive_services_owner_report';
                      send_mail_database_error($errors);
                    }

        }
          /**
            Get Owner Report CalcCron
          */
        public function getOwnerReportCalcCron($whereConditions){
                 
                    $get = $this->DB_ReadOnly->get_where(MILL_OWNER_REPORT_CALCULATIONS_CRON,$whereConditions);
                    //pa($this->DB_ReadOnly->last_query());
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_owner_report_calculations_cron';
                      send_mail_database_error($errors);
                    }
                    return $get;

        }

        /**
            UPDATE Owner Report Cron
        */
        public function updateOwnerReportCron($whereConditions,$data){
               
                $this->db->where('salon_id',$whereConditions['salon_id']);
                $this->db->where('report_type',$whereConditions['report_type']);
                $this->db->where('dayRangeType',$whereConditions['dayRangeType']);
                $this->db->where('start_date',$whereConditions['start_date']);
                $this->db->where('end_date',$whereConditions['end_date']);
                $this->db->query('LOCK TABLE '.MILL_OWNER_REPORT_CALCULATIONS_CRON.' WRITE');
                $this->db->trans_begin();
                $this->db->update(MILL_OWNER_REPORT_CALCULATIONS_CRON, $data);
                //pa($this->db->last_query(),'update');
                if ($this->db->trans_status() === FALSE)
                {
                     $this->db->trans_rollback();
                     $errors = $this->db->error();
                     $errors['tablename'] = 'mill_owner_report_calculations_cron';
                     send_mail_database_error($errors);
                }
                else
                {
                   $this->db->trans_commit();
                }
                $this->db->query('UNLOCK TABLES');
        }

           /**
            Insert Owner Report Cron
          */
        public function insertOwnerReportCron($data){
                $this->db->query('LOCK TABLE '.MILL_OWNER_REPORT_CALCULATIONS_CRON.' WRITE');
                $this->db->trans_begin();
                $this->db->insert(MILL_OWNER_REPORT_CALCULATIONS_CRON, $data);
                //pa($this->db->last_query(),'insert');
                if ($this->db->trans_status() === FALSE)
                {
                    $this->db->trans_rollback();
                    $errors = $this->db->error();
                    $errors['tablename'] = 'mill_owner_report_calculations_cron';
                    send_mail_database_error($errors);
                }
                else
                {
                    $this->db->trans_commit();
                }
                
                $this->db->query('UNLOCK TABLES');
        }
        
        
        
        public function getNewGuestCount($data){
                    $salonAccountNo = $data['salonAccountNo'];
                    $start_date = $data['start_date'];
                    $end_date = $data['end_date'];
                    
                    $this->DB_ReadOnly->select('iclientid');
                    $this->DB_ReadOnly->where('lrefund','false');
                    $this->DB_ReadOnly->where('account_no',$salonAccountNo);
                    $this->DB_ReadOnly->where('tdatetime >=',$start_date);
                    $this->DB_ReadOnly->where('tdatetime <=', $end_date);
                    $list_data = $this->DB_ReadOnly->get(MILL_SERVICE_SALES)->result_array();
                    $new_client_count = 0; 
                    if(!empty($list_data)){
                    foreach($list_data as $list=>$value){
                        $new_list[] = $value['iclientid'];
                    }
                    $client_list = implode(",",$new_list);
                  
                    if(!empty($client_list)){
                       //$client_list = trim(" ",$client_list);
                       $clients = explode(",", $client_list);
                       $this->DB_ReadOnly->select('count(DISTINCT ClientId) as new_client_count');
                       $this->DB_ReadOnly->where_in('ClientId',$clients);
                       $this->DB_ReadOnly->where('AccountNo',$salonAccountNo);
                       $this->DB_ReadOnly->where('DATE(clientFirstVistedDate)>=',$start_date);
                       $this->DB_ReadOnly->where('DATE(clientFirstVistedDate)<=',$end_date);
                       $query = $this->DB_ReadOnly->get(MILL_CLIENTS_TABLE)->row_array();
                       //pa($this->DB_ReadOnly->last_query());
                       $new_client_count = $query['new_client_count'];
                    }  
                 }
                    return $new_client_count;
        }

        public function getTotalClientsCount($data){
                    $salonAccountNo = $data['salonAccountNo'];
                    $start_date = $data['start_date'];
                    $end_date = $data['end_date'];
                    $this->DB_ReadOnly->where('account_no',$salonAccountNo);
                    $this->DB_ReadOnly->where('tdatetime >=',$start_date);
                    $this->DB_ReadOnly->where('tdatetime <=', $end_date);
                    $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
                    $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES)->row_array();
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_service_sales';
                      send_mail_database_error($errors);
                      exit();
                    }
                    return $get['unique_client_count'];

              }
      // for staff new guest count 
       public function getNewGuestCountStaff($data){
                    $salonAccountNo = $data['salonAccountNo'];
                    $start_date = $data['start_date'];
                    $end_date = $data['end_date'];
                    $iempid = $data['iempid'];
                    
                    $this->DB_ReadOnly->select('iclientid');
                    $this->DB_ReadOnly->where('lrefund','false');
                    $this->DB_ReadOnly->where('account_no',$salonAccountNo);
                    $this->DB_ReadOnly->where('tdatetime >=',$start_date);
                    $this->DB_ReadOnly->where('tdatetime <=', $end_date);
                    $this->DB_ReadOnly->where('iempid',$iempid);
                    $list_data = $this->DB_ReadOnly->get(MILL_SERVICE_SALES)->result_array();
                    $new_client_count = 0; 
                    if(!empty($list_data)){
                      foreach($list_data as $list=>$value){
                        $new_list[] = $value['iclientid'];
                    }
                    $client_list = '';
                    if($new_list!=''){
                    $client_list = implode(",",$new_list);
                    }
                    if(!empty($client_list)){
                       //$client_list = trim(" ",$client_list);
                       $clients = explode(",", $client_list);
                       $this->DB_ReadOnly->select('count(DISTINCT ClientId) as new_client_count');
                       $this->DB_ReadOnly->where_in('ClientId',$clients);
                       $this->DB_ReadOnly->where('AccountNo',$salonAccountNo);
                       $this->DB_ReadOnly->where('DATE(clientFirstVistedDate)>=',$start_date);
                       $this->DB_ReadOnly->where('DATE(clientFirstVistedDate)<=',$end_date);
                       $query = $this->DB_ReadOnly->get(MILL_CLIENTS_TABLE)->row_array();
                       //pa($this->db->last_query());
                       $new_client_count = $query['new_client_count'];
                    }
                }
             return $new_client_count;
        }

        // for staff repeat guest count

        public function getTotalClientsCountStaff($data){

                    $salonAccountNo = $data['salonAccountNo'];
                    $start_date = $data['start_date'];
                    $end_date = $data['end_date'];
                    $iempid = $data['iempid'];
                    $this->DB_ReadOnly->where('account_no',$salonAccountNo);
                    $this->DB_ReadOnly->where('tdatetime >=',$start_date);
                    $this->DB_ReadOnly->where('tdatetime <=', $end_date);
                    $this->DB_ReadOnly->where('iempid',$iempid);
                    $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
                    $get = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES)->row_array();
                    if($get===FALSE){
                      $errors = $this->DB_ReadOnly->error();
                      $errors['tablename'] = 'mill_service_sales';
                      send_mail_database_error($errors);
                      exit();
                    }
                    return $get['unique_client_count'];

              }         
        
   }      