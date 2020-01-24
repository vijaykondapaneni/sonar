<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @Author : Mohd Anas Khan (MAK)
 * Soap Libaray for specially Millennium SDK services.
 */
class Common_library {
    // Calculate Total Service Sales
    function calcTotalServiceSales($whereConditions){
         $CI =& get_instance();
         $CI->load->model('Common_model');
         $getTotalServiceSales = $CI->Common_model
                                        ->getTotalServiceSales($whereConditions)
                                        ->row_array();
        return (!empty($getTotalServiceSales['nprice']) && $getTotalServiceSales['nprice'] > 0 ) ? $CI->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice'],2)  : '0.00';
    }
    // Calculate Total Retail Sales 
    function calcTotalRetailSales($whereConditions){
         $CI =& get_instance();
         $CI->load->model('Common_model');
         $getTotalProductSales = $CI->Common_model
                                        ->getTotalProductSales($whereConditions)
                                        ->row_array();
        return (!empty($getTotalProductSales['nprice']) && $getTotalProductSales['nprice'] > 0 ) ? $CI->Common_model->appCloudNumberFormat($getTotalProductSales['nprice'],2)  : '0.00';
    }
    // Calculate Prebook Percentage
    function calcPrebookPercentage($whereConditions){
      
        $CI =& get_instance();
        $CI->load->model('Common_model');
        $whereConditionsLRefundFalse = $whereConditions;
        $whereConditionsLRefundFalse['lrefund'] = 'false';
        $whereConditionsLPrebookTrue = $whereConditions;
        $whereConditionsLPrebookTrue['lprebook'] = 'true';

        $getServiceInvoicesClientIdsCount = $CI->Common_model
                                        ->getServiceInvoicesClientIdsCount($whereConditionsLRefundFalse)
                                        ->row_array();
 
        $getServiceInvoiceCountWithPrebookTrue =$CI->Common_model
                                        ->getServiceInvoiceCountWithPrebookTrue($whereConditionsLPrebookTrue)
                                        ->row_array();   
        return  (!empty($getServiceInvoicesClientIdsCount['invoice_count']) && !empty($getServiceInvoiceCountWithPrebookTrue['invoice_count'] )) ? $CI->Common_model->appCludRoundCalc($getServiceInvoiceCountWithPrebookTrue['invoice_count'],$getServiceInvoicesClientIdsCount['invoice_count'],100,2)  : '0.00';
    }
    // Calculate RPCT
    function calcRPCT($whereConditions){

         $CI =& get_instance();
         $CI->load->model('Common_model');
         $getTotalProductSales = $CI->Common_model
                                        ->getTotalProductSales($whereConditions)
                                        ->row_array();

         $whereConditions['lrefund'] = 'false';
         $whereConditionsLRefundFalse = $whereConditions;
         $getProductInvoicesClientIdsCount = $CI->Common_model
                                        ->getProductInvoicesClientIdsCount($whereConditionsLRefundFalse)
                                        ->row_array();
                                        
        return ($getTotalProductSales['nprice'] > 0 && !empty($getProductInvoicesClientIdsCount['invoice_count']) ) ? $CI->Common_model->appCludRoundCalcWithOutMultiplication($getTotalProductSales['nprice'],$getProductInvoicesClientIdsCount['invoice_count'],2)  : '0.00';
    } 

    // Calculate RPST

    function calcRPST($whereConditions){
         $CI =& get_instance();
         $CI->load->model('Common_model');
         $getTotalProductSales = $CI->Common_model
                                        ->getTotalProductSales($whereConditions)
                                        ->row_array();
         $whereConditionsLRefundFalse['lrefund'] = 'false';                               
         $getServiceInvoicesClientIdsCount = $CI->Common_model
                                        ->getServiceInvoicesClientIdsCount($whereConditionsLRefundFalse)
                                        ->row_array(); 
         return ($getTotalProductSales['nprice'] > 0 && !empty($getServiceInvoicesClientIdsCount['invoice_count']) ) ? $CI->Common_model->appCludRoundCalcWithOutMultiplication($getTotalProductSales['nprice'],$getServiceInvoicesClientIdsCount['invoice_count'],2)  : '0.00';                                                              
    }

    // Calculate Avg Service Ticket
   
    function calcAvgServiceTicket($whereConditions){
      $CI =& get_instance();
      $CI->load->model('Common_model');
      
      $getTotalServiceSales = $CI->Common_model
                                        ->getTotalServiceSales($whereConditions)
                                        ->row_array();  
    }



}
