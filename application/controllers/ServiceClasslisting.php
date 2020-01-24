<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ServiceClasslisting extends CI_Controller {
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('ServiceClasslisting_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
        
    } 

          public function index()
          {
        }
  function getColorChemicalCount(){
     
           $data['sdata']=$this->ServiceClasslisting_model->details();
           // $sum=array();
           $idd[]= array();
           $idd1[]= array();
          foreach($data['sdata'] as $k=>$value)
            {
                    $salonid=$value['salon_id'];
                    $data1  = $this->ServiceClasslisting_model->colorview($value['salon_id']);
                   // echo "<pre>";  
                   // print_r($data1);
                     if($data1 != ''){
                         $data['sdata'][$k]['color'] = count($data1);
                             
                            $idd[] = 'NULL';
                           foreach($data1 as $k1=>$value2){
                                $iid[]=$value2['iid'];
                                  $id=implode(",",$iid);
                            } 
                            if($id!= ''){
                                $data2=$this->db->query("select * from ob_mill_services_listing WHERE  salon_id='$salonid' AND category_iid IN ($id)")->result_array();
                                 // echo $this->db->last_query();
                                unset($iid);
                                $data['sdata'][$k]['subcolor'] = count($data2);
                          }
                  } else{
                      $data['sdata'][$k]['color']=0;
                      $data['sdata'][$k]['subcolor']=0;
                    }    
            
                   $data3 = $this->ServiceClasslisting_model->chemicalview($value['salon_id']);
                   //  echo "<pre>";  
                   // print_r($data3);
                    if($data3 != ''){
                          $data['sdata'][$k]['chemical'] = count($data3);
                          $idd1 = 'NULL';
                           foreach($data3 as $c1=>$value){
                                $iid1[]=$value['iid'];
                                  $id1=implode(",",$iid1);
                            }   
                          if($id1 != ''){
                              $res=$this->db->query("select * from ob_mill_services_listing WHERE  salon_id='$salonid' AND category_iid IN ($id1)")->result_array();
                                // echo $this->db->last_query();
                              unset($iid1);
                              $data['sdata'][$k]['subchemical'] = count($res);
                            
                          }
                  } else{
                          $data['sdata'][$k]['chemical']=0;
                          $data['sdata'][$k]['subchemical']=0;
                    } 
                    
           }
                 $this->load->view('colorcount',$data);
    }

  
/*   function colorCheck(){
     $data['sdata']=$this->ServiceClasslisting_model->details();
       foreach($data['sdata'] as $k=>$value)
            {
             $data['sdata'][$k]['color'] = $this->ServiceClasslisting_model->colorview($value['salon_id']);
             $data['sdata'][$k]['chemical'] = $this->ServiceClasslisting_model->chemicalview($value['salon_id']);
             
            }
       $this->load->view('colorcount',$data);
  }*/
}
?> 