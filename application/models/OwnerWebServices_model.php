<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class GraphsOwner_model
	 * Contains all the queries which are related Reports Module.
	 */
    class OwnerWebServices_model extends CI_Model {
        public function __construct() {
            $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
        }
    	/**
    	 Get Owner Reports Data
    	*/
    	 public function getOwnerReports($whereCondition){
    	 	$this->DB_ReadOnly->select('*');
			return $this->DB_ReadOnly->get_where(MILL_OWNER_REPORT_CALCULATIONS_CRON,$whereCondition);
    	 }
         /**
         Get Owner Retail Sales Data
         */
         public function getRetailSalesWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_RETAIL_OWNER_REPORTS,$whereCondition);
         }

         /**
         Get Owner Service Sales Data
         */
         public function getServiceSalesWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_SERVICE_OWNER_REPORTS,$whereCondition);
         }

         /**
         Get Owner Gift Card Sales Data
         */
         public function getGiftCardWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_GIFT_CARDS_OWNER_REPORTS,$whereCondition);
         }

        /**
         Get Owner New Guest Data
        */
        public function getNewGuestWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_NEW_GUEST_OWNER_REPORTS,$whereCondition);
        }
        /**
         Get Owner Repeated Guest Data
        */
        public function getRepeatedGuestWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_REPEAT_GUEST_OWNER_REPORTS,$whereCondition);
        }
        /**
         Get Owner RPCT Data
        */
        public function getRPCTWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_RPCT_OWNER_REPORTS,$whereCondition);
        }
        /**
         Get Owner Prebook Data
        */
        public function getPrebookWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_PERCENT_PREBOOKED_OWNER_REPORTS,$whereCondition);
        }
        /**
         Get Owner Color Percentage Data
        */
        public function getColorPercentageWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_PERCENT_COLOR_OWNER_REPORTS,$whereCondition);
        }

        /**
         Get Owner Percentage Booked Data
        */
        public function getPercentageBookedWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_PERCENT_BOOKED_OWNER_REPORTS,$whereCondition);
        }
        /**
         Get Owner RUCT Data
        */
        public function getRUCTWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_RUCT_OWNER_REPORTS,$whereCondition);
        }
        /**
         Get Owner RUCT Data
        */
        public function getRebookWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_REBOOK_PERCENTAGE_OWNER_REPORTS,$whereCondition);
        }
        /**
         Get Owner Client Service Data
        */
        public function getClientServicedWebService($whereCondition,$where,$groupby){
            if($where!=''){
                $this->DB_ReadOnly->where($where);
            }
            if($groupby!=''){
               $this->DB_ReadOnly->group_by($groupby);
            }
            return $this->DB_ReadOnly->get_where(MILL_CLIENTS_SERVED_OWNER_REPORTS,$whereCondition);
        }

        /**
         Get Owner Total Sales Data
        */
        public function getTotalSalesWebService($whereCondition){
            return $this->DB_ReadOnly->get_where(MILL_TOTAL_REVENUE_REPORTS,$whereCondition);
        }
    }
  