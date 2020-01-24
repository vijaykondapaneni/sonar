<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Saloncloudsplus_model extends CI_Model {

		public function __construct() {
			parent::__construct();
			$this->load->database();
			$this->load->helper('cookie');
			$this->load->helper('date');
			$this->load->library('session');
		}
		
		function allSalons() {
			$temp = array();
			$salonids = json_encode(array(216,218));
		    $temp['salonids'] = $salonids;
		      
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/salonsInfo");
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $temp);
		    $result=curl_exec($ch);
		    $res = json_decode($result,true);
		    curl_close($ch);
		    if($res['status'] == true) {
		    	return $res['salons'];
		    } else {
		    	array();
		    }

			/*$salonids = array(218);
			$this->db->where_in("salon_id", $salonids);
			$this->db->where(array("integration" => "Yes", "salonbiz_new" => "Yes", "salon_biz_code != " => '', "salonbiz_store_id != " => ""));
			$Salons = $this->db->get(SALON_TABLE)->result_array();*/
		}


		function salonInfo($salon_id='') {
			if(!empty($salon_id)) {
				$temp = array();
				$salonids = json_encode(array(218));
			    $temp['salonids'] = $salonids;
			      
			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/salonsInfo");
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			    curl_setopt($ch, CURLOPT_POST, 1);
			    curl_setopt($ch, CURLOPT_POSTFIELDS, $temp);
			    $result=curl_exec($ch);
			    $res = json_decode($result,true);
			    curl_close($ch);
			    if($res['status'] == true) {
			    	return $res['salons'];
			    } else {
			    	array();
			    }
			} else {
				array();
			}

			/*$salonids = array(218);
			$this->db->where_in("salon_id", $salonids);
			$this->db->where(array("integration" => "Yes", "salonbiz_new" => "Yes", "salon_biz_code != " => '', "salonbiz_store_id != " => ""));
			$Salons = $this->db->get(SALON_TABLE)->result_array();*/
		}

		public function relatedSalons($salon_id='')
		{
			if(!empty($salon_id)) {
				$temp = array();
			    $temp['salon_id'] = $salon_id;
			      
			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/relatedSalonsInfo");
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			    curl_setopt($ch, CURLOPT_POST, 1);
			    // for local server
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // close
			    curl_setopt($ch, CURLOPT_POSTFIELDS, $temp);
			    $result=curl_exec($ch);
			    $res = json_decode($result,true);
			    curl_close($ch);
			    if($res['status'] == true) {
			    	return $res['salons'];
			    } else {
			    	array();
			    }
			} else {
				array();
			}
		}

		public function relatedSalonsForStaff($salon_id='',$staff_id)
		{
			if(!empty($salon_id)) {
				$temp = array();
			    $temp['salon_id'] = $salon_id;
			    $temp['staff_id'] = $staff_id;
			      
			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/relatedSalonsInfoForStaff");
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			    curl_setopt($ch, CURLOPT_POST, 1);
			    curl_setopt($ch, CURLOPT_POSTFIELDS, $temp);
			    $result=curl_exec($ch);
			    $res = json_decode($result,true);
			    curl_close($ch);
			    if($res['status'] == true) {
			    	return $res['salons'];
			    } else {
			    	array();
			    }
			} else {
				array();
			}
		}

		public function nextTwoBizAppts($salon_id,$staff_id)
		{
			if(!empty($salon_id)) {
				$temp = array();
			    $temp['salon_id'] = $salon_id;
			    $temp['staff_id'] = $staff_id;
			      
			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/nextTwoBizAppointments");
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			    curl_setopt($ch, CURLOPT_POST, 1);
			    curl_setopt($ch, CURLOPT_POSTFIELDS, $temp);
			    $result=curl_exec($ch);
			    $res = json_decode($result,true);
			    curl_close($ch);
			    if($res['status'] == true) {
			    	return $res;
			    } else {
			    	array();
			    }
			} else {
				array();
			}
		}
		
	}
?>