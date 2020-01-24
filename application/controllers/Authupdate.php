<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authupdate extends CI_Controller {
	function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
    }   
	/**
	Updating ouatu session and token
	*/
	public function authcronupdate(){
		$all = $this->Common_model->getAuthData()->result_array();
		//pa($all,'all',false);
		foreach ($all as $key => $value) {
			$updatearray = array();
			$updatearray['auth_session'] = '';
			$updatearray['auth_access_token'] = '';
			$updatearray['session_reset_at'] = '0000-00-00 00:00:00';
			$wherearray = array('auth_id'=>$value['auth_id']);
			// update 
			$update = $this->Common_model->updateAuthData($updatearray,$wherearray);
			pa('Updates Done');
		}
	}
}