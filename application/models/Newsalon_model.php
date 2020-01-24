<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Newsalon_model extends CI_Model {

        public function __construct() {
		    parent::__construct();
	    }

	    // insert
	    public function insertNewSalon($data){
	    	$this->db->insert(MILL_ALL_SDK_CONFIG_DETAILS,$data);
	    }
   
         
 }
