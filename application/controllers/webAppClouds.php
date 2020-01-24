<?php
if(file_exists(APPPATH.'REST_Controller.php')){
        require_once(APPPATH.'REST_Controller.php');
}

class webAppClouds extends REST_Controller
{
  public function index_get()
  {
    // Display all books
    print test;
  }

  public function index_post()
  {
    // Create a new book
  }
}

?>