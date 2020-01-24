<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class CheckFolderExist extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Set the crons in all shell scripted files
    **/
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
    }
     CONST FILE_PATH = '/home/ec2-user/';

    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     *Getting past appointments 
     * @param type $salon_id
     */
    public function getCheckFolderExist(){

    	/*$directory = "/home/ec2-user/";
 
		//get all files in specified directory
		$files = glob($directory . "*");
		 
		//print each file name
		foreach($files as $file)
		{
		 //check to see if the file is a folder/directory
		 if(is_dir($file))
		 {
		  pa($file);
		 }
		}*/


    	// code back up check
    	$foldername = self::FILE_PATH.'codebackup'.date('Y-m-d',strtotime("-1 days"));
    	if (!file_exists($foldername) && !is_dir($foldername)) {
		    echo  $foldername." -- No";
		} else{
			echo $foldername."-- Yes";
		}

		// cron back up check
        echo "<br/>";
		$foldername1 = self::FILE_PATH.'cronsbackup/ec2-user'.date('Ymd',strtotime("-1 days"));
		if (!file_exists($foldername1) && !is_dir($foldername)) {
		    echo  $foldername1." -- No";
		} else{
			echo $foldername1."-- Yes";
		}



        
    }

              
  
}      