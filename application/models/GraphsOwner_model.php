<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class GraphsOwner_model
	 * Contains all the queries which are related Reports Module.
	 */
    class GraphsOwner_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
            $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }
         /**
         Get Service Sales Details Array 
         */ 
		 public function getServiceSalesDetailsArr($whereConditions,$where){
		 	   $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
			   $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
		 }
		 /**
         Compare SERVICE OWNER REPORTS DATA  
         */ 
         public function compareServiceOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_SERVICE_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update SERVICE OWNER REPORTS DATA  
         */ 
         public function updateServiceOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
			return $this->db->update(MILL_SERVICE_OWNER_REPORTS, $data); 
         }
         /**
         Delete SERVICE OWNER REPORTS DATA  
         */ 
         public function deleteServiceOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_SERVICE_OWNER_REPORTS);
         }
         /**
         Insert SERVICE OWNER REPORTS DATA  
         */ 
         public function insertServiceOwnerReportsData($data){
            return $this->db->insert(MILL_SERVICE_OWNER_REPORTS, $data);
         }

         // Retail related methods
         /**
         Get Retail Sales Details Array 
         */ 
		 public function getRetailSalesDetailsArr($whereConditions,$where){
		 	   $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
               $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
			   $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES, $whereConditions);
		 }
		 /**
         Compare Retail OWNER REPORTS DATA  
         */ 
         public function compareRetailOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_RETAIL_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update Retail OWNER REPORTS DATA  
         */ 
         public function updateRetailOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
			$this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
			return $this->db->update(MILL_RETAIL_OWNER_REPORTS, $data); 
         }
         /**
         Delete Retail OWNER REPORTS DATA  
         */ 
         public function deleteRetailOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_RETAIL_OWNER_REPORTS);
         }
         /**
         Insert Retail OWNER REPORTS DATA  
         */ 
         public function insertRetailOwnerReportsData($data){
            return $this->db->insert(MILL_RETAIL_OWNER_REPORTS, $data);
         }

         // Gift Card related methods
         /**
         Get Retail Sales Details Array 
         */ 
		 public function getGiftCardSalesDetailsArr($whereConditions,$where){
		 	   $this->DB_ReadOnly->select('SUM(nprice) as nprice');
			   $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_GIFT_CARD_SALES_WITH_BALANCE, $whereConditions);
		 }
		 /**
         Compare Gift Card OWNER REPORTS DATA  
         */ 
         public function compareGiftCardOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_GIFT_CARDS_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update Gift Card OWNER REPORTS DATA  
         */ 
         public function updateGiftCardOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
			$this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
			return $this->db->update(MILL_GIFT_CARDS_OWNER_REPORTS, $data); 
         }
         /**
         Delete Gift Card OWNER REPORTS DATA  
         */ 
         public function deleteGiftCardOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_GIFT_CARDS_OWNER_REPORTS);
         }
         /**
         Insert Gift Card OWNER REPORTS DATA  
         */ 
         public function insertGiftCardOwnerReportsData($data){
            return $this->db->insert(MILL_GIFT_CARDS_OWNER_REPORTS, $data);
         }

       // New Guest Count Related Methods
         /**
         Get New Guest Details Array 
         */ 
		 public function getNewGuestDetailsArr($data){
	 	   $sql_get_checkedIn_clients_from_appts = "SELECT count(DISTINCT client.ClientId) as checked_in_clients_count FROM
                                ".MILL_CLIENTS_TABLE." client 
                                join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                                WHERE 
                                appts.AccountNo = '".$data['salonAccountNo']."' and 
                                appts.SlcStatus != '2' and 
                                appts.ClientId != '-999' and
                                MONTH(clientFirstVistedDate) ='".$data['month']."' AND YEAR(AppointmentDate) ='".$data['year']."' and 
                                client.AccountNo = '".$data['salonAccountNo']."' and 
                                MONTH(AppointmentDate) = '".$data['month']."' AND YEAR(AppointmentDate) ='".$data['year']."'";
                                return $this->DB_ReadOnly->query($sql_get_checkedIn_clients_from_appts);

		 }
         /**
         Get New Guest Details Array week wise
         */ 
         public function getNewGuestDetailsWeekWiseStartDayOfTheWeekArr($data){
        
           $sql_get_checkedIn_clients_from_appts = "SELECT count(DISTINCT client.ClientId) as checked_in_clients_count FROM
                                    ".MILL_CLIENTS_TABLE." client 
                                    join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                                    WHERE 
                                    appts.AccountNo = '".$data['salonAccountNo']."' and 
                                    appts.SlcStatus != '2' and 
                                    client.clientFirstVistedDate =  client.clientLastVistedDate and 
                                    client.AccountNo = '".$data['salonAccountNo']."' and 
                                    date(AppointmentDate) >= '".$data['startDayOfWeek']."' AND 
                                    date(AppointmentDate) <='".$data['endDayOfWeek']."'";
                                    return $this->db->query($sql_get_checkedIn_clients_from_appts);                     

         }
		 /**
         Get New Guest Details Array week wise
         */ 
		 public function getNewGuestDetailsWeekWiseArr($data){
	 	   $sql_get_checkedIn_clients_from_appts = "SELECT count(DISTINCT client.ClientId) as checked_in_clients_count FROM
                                ".MILL_CLIENTS_TABLE." client 
                                join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                                WHERE 
                                appts.AccountNo = '".$data['salonAccountNo']."' and 
                                appts.SlcStatus != '2' and 
                                appts.ClientId != '-999' and 
                                client.clientFirstVistedDate =  client.clientLastVistedDate and 
                                client.AccountNo = '".$data['salonAccountNo']."' and 
                                MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year']." AND WEEK(AppointmentDate) =".$data['week'];
                                return $this->DB_ReadOnly->query($sql_get_checkedIn_clients_from_appts);

		 }
		 
		 /**
         Compare New Guest OWNER REPORTS DATA  
         */ 
         public function compareNewGuestOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_NEW_GUEST_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update New Guest OWNER REPORTS DATA  
         */ 
         public function updateNewGuestOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
			$this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
			return $this->db->update(MILL_NEW_GUEST_OWNER_REPORTS, $data); 
         }
         /**
         Delete New Guest OWNER REPORTS DATA  
         */ 
         public function deleteNewGuestReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_NEW_GUEST_OWNER_REPORTS);
         }
         /**
         Insert New Guest OWNER REPORTS DATA  
         */ 
         public function insertNewGuestOwnerReportsData($data){
            return $this->db->insert(MILL_NEW_GUEST_OWNER_REPORTS, $data);
         }

         // Repeated Guest Count Related Methods
         /**
         Get Repeated Guest Details Array 
         */ 
		 public function getRepeatedGuestDetailsArr($data){
	 	   $sql_get_checkedIn_clients_from_appts = "SELECT count(DISTINCT client.ClientId) as checked_in_clients_count FROM
                                ".MILL_CLIENTS_TABLE." client 
                                join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                                WHERE 
                                appts.AccountNo = '".$data['salonAccountNo']."' and 
                                appts.SlcStatus != '2' and 
                                appts.ClientId != '-999' and 
                                client.clientFirstVistedDate!=  client.clientLastVistedDate and 
                                client.AccountNo = '".$data['salonAccountNo']."' and 
                                MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year'];
                                return $this->DB_ReadOnly->query($sql_get_checkedIn_clients_from_appts);

		 }
         /**
         Get New Guest Details Array week wise
         */ 
         public function getRepeatedGuestDetailsWeekWiseStartDayArr($data){
           $sql_get_checkedIn_clients_from_appts = "SELECT count(DISTINCT client.ClientId) as checked_in_clients_count FROM
                                ".MILL_CLIENTS_TABLE." client 
                                join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                                WHERE 
                                appts.AccountNo = '".$data['salonAccountNo']."' and 
                                client.AccountNo = '".$data['salonAccountNo']."' and 
                                date(AppointmentDate) >= '".$data['startDayOfWeek']."' and 
                                date(AppointmentDate) <= '".$data['endDayOfWeek']."' and 
                                appts.SlcStatus != '2' and 
                                appts.ClientId != '-999' and 
                                client.clientFirstVistedDate!=  client.clientLastVistedDate";
                                return $this->DB_ReadOnly->query($sql_get_checkedIn_clients_from_appts);

         }
		 /**
         Get New Guest Details Array week wise
         */ 
		 public function getRepeatedGuestDetailsWeekWiseArr($data){
	 	   $sql_get_checkedIn_clients_from_appts = "SELECT count(DISTINCT client.ClientId) as checked_in_clients_count FROM
                                ".MILL_CLIENTS_TABLE." client 
                                join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                                WHERE 
                                appts.AccountNo = '".$data['salonAccountNo']."' and 
                                appts.SlcStatus != '2' and 
                                appts.ClientId != '-999' and 
                                client.clientFirstVistedDate!=  client.clientLastVistedDate and 
                                client.AccountNo = '".$data['salonAccountNo']."' and 
                                MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year']." AND WEEK(AppointmentDate) =".$data['week'];
                                return $this->DB_ReadOnly->query($sql_get_checkedIn_clients_from_appts);

		 }
		 
		 /**
         Compare New Guest OWNER REPORTS DATA  
         */ 
         public function compareRepeatedGuestOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_REPEAT_GUEST_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update New Guest OWNER REPORTS DATA  
         */ 
         public function updateRepeatedGuestOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
			$this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
			return $this->db->update(MILL_REPEAT_GUEST_OWNER_REPORTS, $data); 
         }
         /**
         Delete New Guest OWNER REPORTS DATA  
         */ 
         public function deleteRepeatedGuestReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_REPEAT_GUEST_OWNER_REPORTS);
         }


         /**
         Insert New Guest OWNER REPORTS DATA  
         */ 
         public function insertRepeatedGuestOwnerReportsData($data){
            return $this->db->insert(MILL_REPEAT_GUEST_OWNER_REPORTS, $data);
         }
         
        // RPCT Data For Owner
         /**
         Get RPCT Data
         */ 
         public function getRPCTDetailsArr($whereConditions,$where){
		 	   $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count');
               $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
			   $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES, $whereConditions);
		 }
         /**
         Get RPCT Sales Data
         */ 
         public function getRPCTServiceDetailsArr($whereConditions,$where){
               $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count');
               $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
               $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
         }

         /**
         Get RPCT Sales Unique clients
         */ 
         public function getRPCTServiceSalesClientIdsForClientIds($whereConditions,$where){
               $this->DB_ReadOnly->select('DISTINCT(iclientid) as service_client_ids');
               $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
         }
         /**
         Get RPCT Retails Unique clients
         */ 
         public function getRPCTProductSalesClientIdsForClientIds($whereConditions,$where){
                $this->DB_ReadOnly->select('DISTINCT(iclientid) as retail_client_ids');
               $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES, $whereConditions);
         }

		 /**
         Compare RPCT OWNER REPORTS DATA  
         */ 
         public function compareRPCTOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_RPCT_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update RPCT OWNER REPORTS DATA  
         */ 
         public function updateRPCTOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
			$this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
            return $this->db->update(MILL_RPCT_OWNER_REPORTS, $data); 
         }
         /**
         Delete RPCT OWNER REPORTS DATA  
         */ 
         public function deleteRPCTOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_RPCT_OWNER_REPORTS);
         }
         /**
         Insert RPCT OWNER REPORTS DATA  
         */ 
         public function insertRPCTOwnerReportsData($data){
            return $this->db->insert(MILL_RPCT_OWNER_REPORTS, $data);
         }
         
         // Prebook Data For Owner
         /**
         Get Prebook Data
         */ 
         public function getPrebookDetailsArr($whereConditions,$where){
		 	   $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count');
			   $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
		 }

		 /**
         Compare Prebook OWNER REPORTS DATA  
         */ 
         public function comparePrebookOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_PERCENT_PREBOOKED_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update Prebook OWNER REPORTS DATA  
         */ 
         public function updatePrebookOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
			$this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
            return $this->db->update(MILL_PERCENT_PREBOOKED_OWNER_REPORTS, $data); 
         }
         /**
         Delete Prebook OWNER REPORTS DATA  
         */ 
         public function deletePrebookOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_PERCENT_PREBOOKED_OWNER_REPORTS);
         }
         /**
         Insert Prebook OWNER REPORTS DATA  
         */ 
         public function insertPrebookOwnerReportsData($data){
            return $this->db->insert(MILL_PERCENT_PREBOOKED_OWNER_REPORTS, $data);
         }


         // Percentage booked data For Owner
         /**
         Get Percentage booked
         */ 
         public function getPercentBookedDetailsArr($whereConditions,$where){
		 	   $this->DB_ReadOnly->select_sum('nhours','nhours');
			   $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, $whereConditions);
		 }
		 /**
         GET Total Hours Percentage Booked
        */
        public function getPercentBookedTotalHours($data){

        	    return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != '2' and 
                ClientId !=  '-999' and 
                MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year']);
        }

         /**
         GET Total Hours Percentage Booked
        */
        /*public function getStaffPercentBookedTotalHours($data){

            return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Ngaplen` ) AS ngaplen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and
                EmployeeName != '".$data['EmployeeName']."' and  
                SlcStatus != '2' and 
                ClientId !=  '-999' and 
                MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year']);
        }*/
        public function getStaffPercentBookedTotalHours($data){

            return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Ngaplen` ) AS ngaplen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and
                iempid != '".$data['iempid']."' and  
                SlcStatus != '2' and 
                ClientId !=  '-999' and 
                MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year']);
        }

        /**
         GET Total Hours Percentage Booked Week Wise
        */
        public function getPercentBookedTotalHoursWeekWiseStartWithDay($data){

            return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Ngaplen` ) AS ngaplen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != '2' and 
                date(AppointmentDate) >= '".$data['startDayOfWeek']."' AND date(AppointmentDate) <='".$data['endDayOfWeek']."' and  ClientId !=  '-999'");
        }

        /**
         GET Total Hours Percentage Booked Week Wise For Staff
        */
        /*public function getStaffPercentBookedTotalHoursWeekWiseStartWithDay($data){

            return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Ngaplen` ) AS ngaplen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != '2' and 
                EmployeeName != '".$data['EmployeeName']."' and 
                ClientId !=  '-999' and 
                date(AppointmentDate) >= '".$data['startDayOfWeek']."' AND date(AppointmentDate) <= '".$data['endDayOfWeek']."'"
                );
        }*/
         public function getStaffPercentBookedTotalHoursWeekWiseStartWithDay($data){

            return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Ngaplen` ) AS ngaplen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != '2' and 
                iempid != '".$data['iempid']."' and 
                ClientId !=  '-999' and 
                date(AppointmentDate) >= '".$data['startDayOfWeek']."' AND date(AppointmentDate) <= '".$data['endDayOfWeek']."'"
                );
        }

         /**
         GET Total Hours Percentage Booked Week Wise
        */
        public function getPercentBookedTotalHoursWeekWise($data){

        	return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Ngaplen` ) AS ngaplen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
				".MILL_PAST_APPTS_TABLE." 
				WHERE 
				AccountNo = '".$data['salonAccountNo']."' and 
				SlcStatus != '2' and 
				ClientId !=  '-999' and 
				MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year']." AND WEEK(AppointmentDate) =".$data['week']);
        }

         /**
         GET Total Hours Percentage Booked Week Wise
        */
        /*public function getStaffPercentBookedTotalHoursWeekWise($data){
            //print_r($data);
            return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Ngaplen` ) AS ngaplen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != '2' and 
                EmployeeName != '".$data['EmployeeName']."' and 
                ClientId !=  '-999' and 
                MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year']." AND WEEK(AppointmentDate) =".$data['week']);
        }*/
        public function getStaffPercentBookedTotalHoursWeekWise($data){
            //print_r($data);
            return $this->DB_ReadOnly->query("SELECT SUM( `Nstartlen` ) AS nstartlen, SUM( `Ngaplen` ) AS ngaplen, SUM( `Nfinishlen` ) AS nfinishlen FROM 
                ".MILL_PAST_APPTS_TABLE." 
                WHERE 
                AccountNo = '".$data['salonAccountNo']."' and 
                SlcStatus != '2' and 
                iempid != '".$data['iempid']."' and 
                ClientId !=  '-999' and 
                MONTH(AppointmentDate) = ".$data['month']." AND YEAR(AppointmentDate) =".$data['year']." AND WEEK(AppointmentDate) =".$data['week']);
        }

		 /**
         Compare Percentage Booked OWNER REPORTS DATA  
         */ 
         public function comparePercentBookedOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_PERCENT_BOOKED_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update Percentage Booked OWNER REPORTS DATA  
         */ 
         public function updatePercentBookedOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
			$this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
			return $this->db->update(MILL_PERCENT_BOOKED_OWNER_REPORTS, $data); 
         }
         /**
         Delete Percentage Booked OWNER REPORTS DATA  
         */ 
         public function deletePercentBookedOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_PERCENT_BOOKED_OWNER_REPORTS);
         }
         /**
         Insert Percentage Booked OWNER REPORTS DATA  
         */ 
         public function insertPercentBookedOwnerReportsData($data){
            return $this->db->insert(MILL_PERCENT_BOOKED_OWNER_REPORTS, $data);
         }


         // Color Percentage data For Owner
         /**
         Get Color Percentage booked
         */ 
         public function getColorPercentageDetailsArr($whereConditions,$where,$wherelikeConditions){
		 	   $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
			   $this->DB_ReadOnly->where($where);
			   $this->DB_ReadOnly->where($wherelikeConditions);
               return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
		 }

		  /**
         Get Color Percentage booked all services total price and invoice count
         */ 
         public function getColorPercentageDetailsArrTotalPrice($whereConditions,$where){
		 	   $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
			   $this->DB_ReadOnly->where($where);
			   return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
		 }
        
		 /**
         Compare Color Percentage OWNER REPORTS DATA  
         */ 
         public function compareColorPercentageOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_PERCENT_COLOR_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update Color Percentage OWNER REPORTS DATA  
         */ 
         public function updateColorPercentageOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
			$this->db->where('key', $whereConditions['key']);
			$this->db->where('dayRangeType', $whereConditions['dayRangeType']);
			$this->db->where('id', $whereConditions['id']);
			return $this->db->update(MILL_PERCENT_COLOR_OWNER_REPORTS, $data); 
         }
         /**
         Delete Color Percentage OWNER REPORTS DATA  
         */ 
         public function deleteColorPercentageOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_PERCENT_COLOR_OWNER_REPORTS);
         }
         /**
         Insert Color Percentage OWNER REPORTS DATA  
         */ 
         public function insertColorPercentageOwnerReportsData($data){
            return $this->db->insert(MILL_PERCENT_COLOR_OWNER_REPORTS, $data);
         }

         /**
          Get Client Service Data
         */
          public function getUnqiueClients($salonAccountNo,$month,$year){

             return $this->DB_ReadOnly->query("SELECT cinvoiceno FROM  ".MILL_SERVICE_SALES." WHERE  `account_no` =".$salonAccountNo." AND  MONTH(`tdatetime`) = ".$month." 
                  AND YEAR(`tdatetime`) =".$year."  AND  `lrefund` =  'false' UNION 
                  SELECT cinvoiceno FROM  ".MILL_PRODUCT_SALES." WHERE  `account_no` =".$salonAccountNo." AND  MONTH(`tdatetime`) = ".$month." AND YEAR(`tdatetime`) =".$year." AND  `lrefund` =  'false'");
          }
          
          // Get Client Service data
          public function getServiceSalesData($whereConditions,$where){
                   $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count,count(DISTINCT tdatetime) as tdatetime');
                   $this->DB_ReadOnly->select_sum('nquantity');
                   $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
                   $this->DB_ReadOnly->where($where);
                   return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, $whereConditions);
          }
          // Get Client Service data weekwise
          public function getUnqiueClientsWeekWise($salonAccountNo,$startDayOfWeek,$endDayOfWeek){
             return $this->DB_ReadOnly->query("SELECT cinvoiceno FROM  ".MILL_SERVICE_SALES." WHERE  `account_no` ='".$salonAccountNo."' AND  `tdatetime` >= '".$startDayOfWeek."' 
                  AND tdatetime <='".$endDayOfWeek."' AND `lrefund` =  'false' UNION 
                  SELECT cinvoiceno FROM  ".MILL_PRODUCT_SALES." WHERE  `account_no` ='".$salonAccountNo."' AND  `tdatetime` >= '".$startDayOfWeek."' AND tdatetime <='".$endDayOfWeek."' AND  `lrefund` =  'false'");
          }
          
         /**
         Compare Client Service OWNER REPORTS DATA  
         */ 
         public function compareClientServiceOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_CLIENTS_SERVED_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update Client Service OWNER REPORTS DATA  
         */ 
         public function updateClientServiceOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
            $this->db->where('key', $whereConditions['key']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('id', $whereConditions['id']);
            return $this->db->update(MILL_CLIENTS_SERVED_OWNER_REPORTS, $data); 
         }
         /**
         Delete Client Service OWNER REPORTS DATA  
         */ 
         public function deleteClientServiceOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_CLIENTS_SERVED_OWNER_REPORTS);
         }
         /**
         Insert Client Service OWNER REPORTS DATA  
         */ 
         public function insertClientServiceOwnerReportsData($data){
            return $this->db->insert(MILL_CLIENTS_SERVED_OWNER_REPORTS, $data);
         }

         // Get Percentage Retail To Service Sales OWNER

         public function getProductSalesData($whereConditions,$where){
               $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
               $this->DB_ReadOnly->where($where);
               return $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES, $whereConditions);
         }

         /**
         Compare Percentage Retail To Service Sales OWNER REPORTS DATA  
         */ 
         public function comparePercentageRetailToServiceSalesOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_PERCENTAGE_RETAIL_TO_SERVICE_SALES_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update Percentage Retail To Service Sales OWNER REPORTS DATA  
         */ 
         public function updatePercentageRetailToServiceSalesOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
            $this->db->where('key', $whereConditions['key']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('id', $whereConditions['id']);
            return $this->db->update(MILL_PERCENTAGE_RETAIL_TO_SERVICE_SALES_OWNER_REPORTS, $data); 
         }
         /**
         Delete Percentage Retail To Service Sales OWNER REPORTS DATA  
         */ 
         public function deletePercentageRetailToServiceSalesOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_PERCENTAGE_RETAIL_TO_SERVICE_SALES_OWNER_REPORTS);
         }
         /**
         Insert Percentage Retail To Service Sales OWNER REPORTS DATA  
         */ 
         public function insertPercentageRetailToServiceSalesOwnerReportsData($data){
            return $this->db->insert(MILL_PERCENTAGE_RETAIL_TO_SERVICE_SALES_OWNER_REPORTS, $data);
         }

         // For RUCT Calculatons
         /**
          Get Service Clientids RUCT OWNER REPORTS DATA  
         */
         public function getServiceSalesClientIds($whereConditions,$where){
            $this->DB_ReadOnly->select('DISTINCT(cinvoiceno) as service_client_ids');
            $this->DB_ReadOnly->where($where);
            return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
         }
         /**
          Get Product Clientids RUCT OWNER REPORTS DATA  
         */
         public function getProductSalesClientIds($whereConditions,$where){
            $this->DB_ReadOnly->select('DISTINCT(cinvoiceno) as retail_client_ids');
            $this->DB_ReadOnly->where($where);
            return $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES,$whereConditions);
         }
         /**
          Get Product sales for retail units
         */
          public function getProductRetailUnits($whereConditions,$where){
            $this->DB_ReadOnly->select_sum('nquantity');
            $this->DB_ReadOnly->where($where);
            return $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES,$whereConditions);
          }

         /**
         Compare RUCT OWNER REPORTS DATA  
         */ 
         public function compareRUCTOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_RUCT_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update RUCT OWNER REPORTS DATA  
         */ 
         public function updateRUCTOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
            $this->db->where('key', $whereConditions['key']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('id', $whereConditions['id']);
            return $this->db->update(MILL_RUCT_OWNER_REPORTS, $data); 
         }
         /**
         Delete RUCT OWNER REPORTS DATA  
         */ 
         public function deleteRUCTOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_RUCT_OWNER_REPORTS);
         }
         /**
         Insert RUCT OWNER REPORTS DATA  
         */ 
         public function insertRUCTOwnerReportsData($data){
            return $this->db->insert(MILL_RUCT_OWNER_REPORTS, $data);
         }

         // Get Total Sales
         /**
         Get Retail for Total Sales Owner Reports
         */
         public function getRetailDataforTotalSales($whereConditions,$where){
            $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,Year( `tdatetime` ) as year , Month( `tdatetime` ) as month,MONTHNAME(`tdatetime`) as month_name,count(DISTINCT iclientid) as unique_client_count');
            $this->DB_ReadOnly->select_sum('nquantity');
            $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
            $this->DB_ReadOnly->where($where);
            return $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES,$whereConditions);
         }
         /**
         Get Sales for Total Sales Owner Reports
         */
         public function getSalesDataforTotalSales($whereConditions,$where){
            $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,Year( `tdatetime` ) as year , Month( `tdatetime` ) as month,MONTHNAME(`tdatetime`) as month_name,count(DISTINCT iclientid) as unique_client_count');
            $this->DB_ReadOnly->select_sum('nquantity');
            $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
            $this->DB_ReadOnly->where($where);
            return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
         }
         /**
         Get Gift Card Total for Total Sales Owner Reports
         */
         public function getGiftDataforTotalSales($whereConditions,$where){

            $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,Year( `tdatetime` ) as year , Month( `tdatetime` ) as month,MONTHNAME(`tdatetime`) as month_name,count(DISTINCT iclientid) as unique_client_count');
            $this->DB_ReadOnly->select_sum('nprice','nprice');
            $this->DB_ReadOnly->where($where);
            return $this->DB_ReadOnly->get_where(MILL_GIFT_CARD_SALES_WITH_BALANCE,$whereConditions);
         }

         /**
         Compare Total Sales OWNER REPORTS DATA  
         */ 
         public function compareTotalSalesOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_TOTAL_REVENUE_REPORTS,$whereConditions);
         }
         /**
         Update Total Sales OWNER REPORTS DATA  
         */ 
         public function updateTotalSalesOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
            //$this->db->where('key', $whereConditions['key']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
           // $this->db->where('start_date', $whereConditions['start_date']);
           // $this->db->where('end_date', $whereConditions['end_date']);
            return $this->db->update(MILL_TOTAL_REVENUE_REPORTS, $data); 
         }
         /**
         Insert Total Sales OWNER REPORTS DATA  
         */ 
         public function insertTotalSalesOwnerReportsData($data){
            return $this->db->insert(MILL_TOTAL_REVENUE_REPORTS, $data);
         }

         // Get Rebook Percentage Data

         /**
         Get Unique Client ids per day - Rebook percentage 
         */
         public function getRebookPercentageUniqueClientIds($whereConditions,$where){
            $this->DB_ReadOnly->select('tdatetime');
            $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
            $this->DB_ReadOnly->group_by('tdatetime'); 
            $this->DB_ReadOnly->where($where);
            return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
         }
         /**
          Get Service Unique Ids -- Rebook Percentage
         */
          public function getRebookPercentageServiceSalesUniqueClientIds($whereConditions){
            $this->DB_ReadOnly->select('iclientid');
            $this->DB_ReadOnly->group_by('iclientid');
            return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
          }
         /**
          Get All Client Count -- Rebook Percentage
         */
          public function getRebookPercentageAllClientCount($salonAccountNo,$dateformat,$plusFourMonthsDate,$uniqueClientIdsJoined){

            return $this->DB_ReadOnly->query("SELECT count(DISTINCT ClientId) as client_count FROM 
                    ".MILL_APPTS_TABLE."  
                    WHERE 
                    AccountNo = '".$salonAccountNo."' and 
                    SlcStatus != 'Deleted' and 
                    str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$dateformat."' and 
                    str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$plusFourMonthsDate."' and 
                    DATE(  `MillCreatedDate` ) <=  '".$dateformat."' and 
                    LPrebook =  'true' and 
                    ClientId IN ($uniqueClientIdsJoined)");
          } 

         /**
         Compare Rebook Percentage OWNER REPORTS DATA  
         */ 
         public function compareRebookPercentageOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_REBOOK_PERCENTAGE_OWNER_REPORTS,$whereConditions);
         }
         /**
         Update Rebook Percentage OWNER REPORTS DATA  
         */ 
         public function updateRebookPercentageOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
            $this->db->where('key', $whereConditions['key']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('id', $whereConditions['id']);
            return $this->db->update(MILL_REBOOK_PERCENTAGE_OWNER_REPORTS, $data); 
         }
         /**
         Delete Rebook Percentage OWNER REPORTS DATA  
         */ 
         public function deleteRebookPercentageOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_REBOOK_PERCENTAGE_OWNER_REPORTS);
         }
         /**
         Insert Rebook Percentage OWNER REPORTS DATA  
         */ 
         public function insertRebookPercentageOwnerReportsData($data){
            return $this->db->insert(MILL_REBOOK_PERCENTAGE_OWNER_REPORTS, $data);
         }

          /**
         Compare Percentage Booked OWNER REPORTS DATA  For Staff
         */ 
         public function compareStaffPercentBookedOwnerReportsData($whereConditions){
            return  $this->DB_ReadOnly->get_where(MILL_PERCENT_BOOKED_STAFF_REPORTS,$whereConditions);
         }
         /**
         Update Percentage Booked OWNER REPORTS DATA  For Staff
         */ 
         public function updateStaffPercentBookedOwnerReportsData($whereConditions,$data){
            $this->db->where('salon_id', $whereConditions['salon_id']);
            $this->db->where('key', $whereConditions['key']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('id', $whereConditions['id']);
            return $this->db->update(MILL_PERCENT_BOOKED_STAFF_REPORTS, $data); 
         }
         /**
         Delete Percentage Booked OWNER REPORTS DATA  For Staff
         */ 
         public function deleteStaffPercentBookedOwnerReportsData($whereConditions){
            $this->db->where('salon_id', $whereConditions['salonId']);
            $this->db->where('dayRangeType', $whereConditions['dayRangeType']);
            $this->db->where('key', $whereConditions['key']);
            return $this->db->delete(MILL_PERCENT_BOOKED_STAFF_REPORTS);
         }
         /**
         Insert Percentage Booked OWNER REPORTS DATA  
         */ 
         public function insertStaffPercentBookedOwnerReportsData($data){
            return $this->db->insert(MILL_PERCENT_BOOKED_STAFF_REPORTS, $data);
         }




   }		  