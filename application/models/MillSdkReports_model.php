<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MillSdkReports_model extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
  }
  public function getSDKReportsNum($params) {
    $this->DB_ReadOnly->select('count(*) as tRecords');
    $this->DB_ReadOnly->from('mill_all_salons_sdk_reports');
    if (isset($params['id']) and $params['id'] != '') {
      $this->DB_ReadOnly->where('id', $params['id']);
    }
    if (isset($params['salon_id']) and $params['salon_id'] != '') {
      $this->DB_ReadOnly->where('salon_id', $params['salon_id']);
    }
    if (isset($params['salon_name']) and $params['salon_name'] != '') {
      $this->DB_ReadOnly->like('salon_name', trim($params['salon_name']));
    }
    if (isset($params['session_status']) and $params['session_status'] != '') {
       if($params['session_status']==0){
           $this->DB_ReadOnly->group_start();
           $this->DB_ReadOnly->like('session_status',0);
           $this->DB_ReadOnly->or_like('session_status',1);
           $this->DB_ReadOnly->group_end();
       }elseif($params['session_status']==1){
           $this->DB_ReadOnly->where('session_status',0);
       }elseif($params['session_status']==2){
           $this->DB_ReadOnly->where('session_status', 1);
       }else{
          $session_status = '';
       }
    }
   // pa($params['appointment_status'],'appointment_status count');
    if (isset($params['appointment_status']) and $params['appointment_status'] != '') {
      if($params['appointment_status']=='0'){
           $this->DB_ReadOnly->group_start();
           $this->DB_ReadOnly->like('appointment_status',0);
           $this->DB_ReadOnly->or_like('appointment_status',1);
           $this->DB_ReadOnly->group_end();
       }elseif($params['appointment_status']=='1'){
           $this->DB_ReadOnly->where('appointment_status',0);
       }elseif($params['appointment_status']=='2'){
           $this->DB_ReadOnly->where('appointment_status', 1);
       }else{
          $appointment_status = '';
       }
    }
    
    if (isset($params['created_date']) and $params['created_date'] != '') {
      //$this->db->where('created_date', $params['created_date']);
      $this->DB_ReadOnly->like('created_date',$params['created_date']);
    }
    /*if (isset($params['phone']) and $params['phone'] != '') {
      $this->db->group_start();
      $this->db->like('mobile1', trim($params['phone']));
      $this->db->or_like('mobile2', trim($params['phone']));
      $this->db->or_like('phone', trim($params['phone']));
      $this->db->group_end();
    }
    if (isset($params['address']) and $params['address'] != '') {
      $this->db->group_start();
      $this->db->like('address1', trim($params['address']));
      $this->db->or_like('address2', trim($params['address']));
      $this->db->or_like('city', trim($params['address']));
      $this->db->or_like('district', trim($params['address']));
      $this->db->or_like('state', trim($params['address']));
      $this->db->group_end();
    }
    if (isset($params['name']) and $params['name'] != '') {
      $this->db->group_start();
      $this->db->like('name', trim($params['name']));
      $this->db->or_like('surname', trim($params['name']));
      $this->db->group_end();
    }*/
    if (isset($params['keywords']) and trim($params['keywords']) !='') {
      $this->DB_ReadOnly->group_start();
      $this->DB_ReadOnly->like('id', $params['keywords']);
      $this->DB_ReadOnly->or_like('salon_id', $params['keywords']);
      $this->DB_ReadOnly->or_where('salon_name', $params['keywords']);
      $this->DB_ReadOnly->or_where('session_status', $params['keywords']);
      $this->DB_ReadOnly->or_like('appointment_status', trim($params['keywords']));
      $this->DB_ReadOnly->or_like('created_date', trim($params['keywords']));
      $this->DB_ReadOnly->group_end();
    }
    $qry = $this->DB_ReadOnly->get();
   // echo  $this->db->last_query();
    if($qry->num_rows() > 0){
      $result = $qry->result_array();
      return $result[0]['tRecords'];
    }
    return false;
  }
  public function getSDKReports($params, $cols='*')
  {
   
    $this->DB_ReadOnly->select($cols);
    $this->DB_ReadOnly->from('mill_all_salons_sdk_reports');
    if (isset($params['id']) and $params['id'] != '') {
      $this->DB_ReadOnly->where('id', $params['id']);
    }
    if (isset($params['salon_id']) and $params['salon_id'] != '') {
      $this->DB_ReadOnly->where('salon_id', $params['salon_id']);
    }
    if (isset($params['salon_name']) and $params['salon_name'] != '') {
       $this->DB_ReadOnly->like('salon_name', trim($params['salon_name']));
    }
    //pa($params['appointment_status']);
    if (isset($params['appointment_status']) and $params['appointment_status'] != '') {
       if($params['appointment_status']==0){
           $this->DB_ReadOnly->group_start();
           $this->DB_ReadOnly->like('appointment_status',0);
           $this->DB_ReadOnly->or_like('appointment_status',1);
           $this->DB_ReadOnly->group_end();
       }elseif($params['appointment_status']==1){
           $this->DB_ReadOnly->where('appointment_status',0);
       }elseif($params['appointment_status']==2){
           $this->DB_ReadOnly->where('appointment_status', 1);
       }else{
          $appointment_status = '';
       }
      
    }
    //pa($params['session_status'],'session_status');
    if (isset($params['session_status']) and $params['session_status'] != '') {
       if($params['session_status']==0){
           $this->DB_ReadOnly->group_start();
           $this->DB_ReadOnly->like('session_status',0);
           $this->DB_ReadOnly->or_like('session_status',1);
           $this->DB_ReadOnly->group_end();
       }elseif($params['session_status']==1){
           $this->DB_ReadOnly->where('session_status',0);
       }elseif($params['session_status']==2){
           $this->DB_ReadOnly->where('session_status', 1);
       }else{
          $session_status = '';
       }
    }
    if (isset($params['created_date']) and $params['created_date'] != '') {
      //$this->db->where('created_date', $params['created_date']);
      $this->DB_ReadOnly->like('created_date',$params['created_date']);
    }
    
    /*if (isset($params['phone']) and $params['phone'] != '') {
      $this->db->group_start();
      $this->db->like('mobile1', trim($params['phone']));
      $this->db->or_like('mobile2', trim($params['phone']));
      $this->db->or_like('phone', trim($params['phone']));
      $this->db->group_end();
    }
    if (isset($params['address']) and $params['address'] != '') {
      $this->db->group_start();
      $this->db->like('address1', trim($params['address']));
      $this->db->or_like('address2', trim($params['address']));
      $this->db->or_like('city', trim($params['address']));
      $this->db->or_like('district', trim($params['address']));
      $this->db->or_like('state', trim($params['address']));
       $this->db->group_end();
    }
    if (isset($params['name']) and $params['name'] != '') {
      $this->db->group_start();
      $this->db->like('name', trim($params['name']));
      $this->db->or_like('surname', trim($params['name']));
      $this->db->group_end();
    }*/
    if (isset($params['keywords']) and trim($params['keywords']) !='') {
      $this->DB_ReadOnly->group_start();
      $this->DB_ReadOnly->like('id', $params['keywords']);
      $this->DB_ReadOnly->or_like('salon_id', $params['keywords']);
      $this->DB_ReadOnly->or_where('salon_name', $params['keywords']);
      $this->DB_ReadOnly->or_where('session_status', trim($params['keywords']));
      $this->DB_ReadOnly->or_like('appointment_status', trim($params['keywords']));
      $this->DB_ReadOnly->or_like('created_date', trim($params['keywords']));
      $this->DB_ReadOnly->group_end();
    }
    $this->DB_ReadOnly->order_by($params['sortby'], $params['sort_order']);
    $this->DB_ReadOnly->limit($params['rows'], ($params['pageno']-1)*$params['rows']);
    $qry = $this->DB_ReadOnly->get();
    //echo  $this->db->last_query();
    if($qry->num_rows() > 0){
      $rslt = $qry->result_array();
      foreach ($rslt as $key => $value){
        $records[$value['id']] = $value;
      }
      return $records;
    }
    return FALSE;
  }

  public function getMemberDetails($value = FALSE, $by = 'id') {
    if (!$value) return FALSE;
    $qry = $this->DB_ReadOnly->select('*')->from('mill_all_salons_sdk_reports')->where($by, $value)->get();
    if($qry->num_rows() > 0){
      return $qry->row_array();
    }
    return FALSE;
  }

  


}