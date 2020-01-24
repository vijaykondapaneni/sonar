<?php
class Ws_check_config_detail extends CI_Controller {
	function __construct() {
			parent::__construct();
			$this->load->model('Common_model');
			$this->load->model('Appointmentsimport_model');
			$this->load->model('Twoyearclientsimport_model');
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
	}


	function getConfigDetails(){
		$getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no="");
		echo "<pre>"; print_r($getConfigDetails->result_array());	echo "</pre>";
			
	}
}


?>