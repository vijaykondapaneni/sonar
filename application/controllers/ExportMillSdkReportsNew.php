<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ExportMillSdkReportsNew extends CI_Controller {
  function __construct() {
    parent::__construct();
    $this->load->helper(array('form', 'url'));
    $this->load->database();
  }

  function exportReports()
  {
    $new_reports_url = "http://ec2-34-226-9-190.compute-1.amazonaws.com/index.php/";
    //getting data from mill_all_salons_sdk_reports_server

    $query = $this->db->query("SELECT * FROM mill_all_salons_sdk_reports_server WHERE status=0");
    $recs = $query->result_array();
    /*echo "<pre>";
    print_r($recs);*/
    if($query->num_rows()>0){
      foreach ($recs as $key => $rec) {
        $id = $rec['id'];
        $salon_id = $rec['salon_id'];
        $salon_name = $rec['salon_name'];
        $session_status = $rec['session_status'];
        $session_error = $rec['session_error'];
        $appointment_status = $rec['appointment_status'];
        $appointment_error = $rec['appointment_error'];
        $created_date = $rec['created_date'];
        $end_date = $rec['end_date'];
        $status = $rec['status'];
        $edate = date("Y-m-d H:i:s");

        //insert in to mill_all_interm_salons_sdk_reports tabel

        $postarray = array('salon_id'=>$salon_id,'salon_name'=>$salon_name,'session_status'=>$session_status,'session_error'=>$session_error,'appointment_status'=>$appointment_status,'appointment_error'=>$appointment_error,'created_date'=>$created_date,'end_date'=>$end_date,'status'=>$status);
        /*print_r($postarray);
        exit();*/
        // get old server appoitnemnts count
        $ch = curl_init();
        $server = $new_reports_url."ExportMillSdkReportsNewWs/exportReports";
        curl_setopt($ch, CURLOPT_URL,$server);
        // for local server
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // close
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray); 
        $salons_result=curl_exec($ch);
        curl_close($ch);
        //print_r($salons_result);
        if($salons_result=="true"){
          //updating mill_all_interm_salons_sdk_reports table
          
          $update = $this->db->query("UPDATE mill_all_salons_sdk_reports_server SET status=1,end_date='$edate' WHERE id='$id' AND salon_id ='$salon_id' ");
          if($update){
            pa($id.$salon_id."Exported.");
          }
        }
        //exit();
      }
    }else{
      echo "no data to export.";
    }
    
  }
}