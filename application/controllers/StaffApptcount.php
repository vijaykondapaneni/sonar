<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class StaffApptcount extends CI_Controller {
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('Apptcount');

        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
       
    } 

public function index()
	{
$data['sdata']=$this->Apptcount->staff();

foreach($data['sdata'] as $k=>$value)
  {
   $data['sdata'][$k]['account'] = $this->Apptcount->countview($value['salon_account_id']);
   $data['sdata'][$k]['Account_id'] = $this->Apptcount->clientcount($value['salon_account_id']);
   $data['sdata'][$k]['accountid'] = $this->Apptcount->appcount($value['salon_account_id']);
    $data['sdata'][$k]['account_id'] = $this->Apptcount->featappcount($value['salon_account_id']);

  }

  // Print_r($data);
   $this->load->view('staffapptcount',$data);
}
	
}
?>
