<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staffcount extends CI_Controller {
    
    function __construct()
    {
        parent::__construct();
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
       
    } 

	public function index()
	{
	    //for Staff count
	    $data['countno'] = $this->DB_ReadOnly->query('SELECT  det.salon_id,det.salon_name,det.salon_account_id,count(plus.staff_id) as mycount FROM mill_all_sdk_config_details as det 
	    left join plus_staff2 as plus on plus.account_no = det.salon_account_id  GROUP by det.salon_account_id')->result();
	    
	  
	  // //for appointment 
	  // $data['appcount'] = $this->DB_ReadOnly->query('SELECT det.salon_id,det.salon_name,det.salon_account_id,count(cli.IsProcessed) as myCount FROM mill_all_sdk_config_details as det
	  // left join mill_appointments as cli on cli.AccountNo = det.salon_account_id and cli.IsProcessed=0 GROUP by det.salon_account_id')->result();
	  
   //     // echo '<pre>';print_r($data['appcount']);exit;
		$this->load->view('stfcount',$data);
	}
	
}
