<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
    class Template 
    {
        var $ci;
         
        function __construct() 
        {
            $this->ci =& get_instance();
        }
        function load($tpl_view, $data = null) 
				{

				    $this->ci->load->view('template/'.$tpl_view, $data);
				}
    }