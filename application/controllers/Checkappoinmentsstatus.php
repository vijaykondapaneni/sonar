<?php
defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
class Checkappoinmentsstatus extends CI_Controller {
    
    function __construct()
    {
        parent::__construct();
         $this->load->model('Checkappoinmentsstatus_model');
        
        //$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
       
    } 

	public function index()
	{
		$data['result']=$this->Checkappoinmentsstatus_model->getconfig_details();

		foreach($data['result'] as $k=>$value){
   			$data['result'][$k]['starttime'] = $this->Checkappoinmentsstatus_model->getcheckdin($value['salon_account_id']);
   			$data['result'][$k]['oneday'] = $this->Checkappoinmentsstatus_model->getoneday($value['salon_account_id']);
   			$data['result'][$k]['oneweek'] = $this->Checkappoinmentsstatus_model->getoneweek($value['salon_account_id']);
   			$data['result'][$k]['twomonths'] = $this->Checkappoinmentsstatus_model->gettwomonths($value['salon_account_id']);
   			$data['result'][$k]['fourmonths'] = $this->Checkappoinmentsstatus_model->getfourmonths($value['salon_account_id']);
   			$data['result'][$k]['sixmonths'] = $this->Checkappoinmentsstatus_model->getsixmonths($value['salon_account_id']);
   			$data['result'][$k]['sdkerror'] = $this->Checkappoinmentsstatus_model->getsdkerror($value['salon_id']);

   			// echo "<pre>";
   			// print_r($data['result'][$k]['account']);
  		}
  		// echo "<pre>";
  		// print_r($data);











  		// foreach($data['result'] as $l=>$value){
   	// 		$data['result'][$l]['oneday'] = $this->Checkappoinmentsstatus_model->getoneday($value['salon_account_id']);
   	// 		 // echo "<pre>";
   	// 		 // print_r($data['result'][$k]['account']);
  		// }
  		//  foreach($data['result'] as $m=>$value){
   	// 		$data['result'][$m]['oneweek'] = $this->Checkappoinmentsstatus_model->getoneweek($value['salon_account_id']);
   	// 		// echo "<pre>";
   	// 		// print_r($data['result'][$k]['account']);
  		// }
  		// foreach($data['result'] as $n=>$value){
   	// 		$data['result'][$n]['twomonths'] = $this->Checkappoinmentsstatus_model->gettwomonths($value['salon_account_id']);
   	// 		// echo "<pre>";
   	// 		// print_r($data['result'][$k]['account']);
  		// }
  		// foreach($data['result'] as $o=>$value){
   	// 		$data['result'][$o]['fourmonths'] = $this->Checkappoinmentsstatus_model->getfourmonths($value['salon_account_id']);
   	// 		// echo "<pre>";
   	// 		// print_r($data['result'][$k]['account']);
  		// }
  		// foreach($data['result'] as $p=>$value){
   	// 		$data['result'][$p]['sixmonths'] = $this->Checkappoinmentsstatus_model->getsixmonths($value['salon_account_id']);
   	// 		// echo "<pre>";
   	// 		// print_r($data['result'][$k]['account']);
  		// }
  		
  		// exit;
		// echo "<pre>";
		// print_r($data);
		$this->load->view('checkappoinmentsstatus_view',$data);
	   
	}
	
}
