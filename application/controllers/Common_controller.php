<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common_controller extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
    }

    // Calculate Total Service Sales

    function calTotalServiceSales($whereConditions){

      $getTotalServiceSales = $this->Common_model
                                        ->getTotalServiceSales($whereConditions)
                                        ->row_array();
      return (!empty($getTotalServiceSales['nprice']) && $getTotalServiceSales['nprice'] > 0 ) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice'],2)  : '0.00';                                  
    }	
}
