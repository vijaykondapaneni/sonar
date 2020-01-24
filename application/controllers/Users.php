<?php
/**
 * Description of Users
 *
 * @author anaswac
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* User Management class created by CodexWorld
*/
class Users extends CI_Controller {
    
    public $data = array();
    function __construct() {
        parent::__construct();
        $this->load->library('template');
        $this->load->library('table');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->model('user');
    }
    
    /*
     * User account information
     */
    public function account(){
        if($this->session->userdata('isUserLoggedIn')){
            $this->data['title'] = 'User Account';
            $this->data['user'] = $this->user->getRows(array('id'=>$this->session->userdata('userId')));
            //load the view
            
              $this->data['body'] = $this->load->view('users/account', $this->data , TRUE);
              $this->template->load('default',$this->data);
        }else{
            redirect('users/login');
        }
    }

    public function __redircturlsaloncloudplus($segment){
        switch ($segment) {
            case "1":
                $this->redirect_url_plus = 'index.php/Salonstatus/getAllServersStaffApptcount';
            break;
            case "2":
                $this->redirect_url_plus = 'index.php/Salonstatus/getAllServersSaloncount';
            break;
            case "3":
                $this->redirect_url_plus = 'index.php/Salonstatus/getAllServersCheckappoinmentsstatus';
            break;
            case "4":
                $this->redirect_url_plus = 'index.php/CheckNumbersFromSdk/getCheckNumbersFromSdk/lastweek';
            break;
            default:
             $this->redirect_url_plus = 'index.php/MillSdkReportsNew/getMillSdkReports';
            break ;
        }    
    }
    
    /*
     * User login
     */
    public function login(){
        if($this->session->userdata('isUserLoggedIn')){
            redirect(site_url('users/account'));
        }       
        $this->data['title'] = 'Login';
        if($this->session->userdata('success_msg')){
            $this->data['success_msg'] = $this->session->userdata('success_msg');
            $this->session->unset_userdata('success_msg');
        }
        if($this->session->userdata('error_msg')){
            $this->data['error_msg'] = $this->session->userdata('error_msg');
            $this->session->unset_userdata('error_msg');
        }
        if($this->input->post('loginSubmit')){
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'password', 'required');
            if ($this->form_validation->run() == true) {
                $con['returnType'] = 'single';
                $con['conditions'] = array(
                    'email'=>$this->input->post('email'),
                    'password' => md5($this->input->post('password')),
                    'status' => '1'
                );
                $checkLogin = $this->user->getRows($con);
                if($checkLogin){
                    $this->session->set_userdata('isUserLoggedIn',TRUE);
                    $this->session->set_userdata('userId',$checkLogin['id']);
                    $this->__redircturlsaloncloudplus($this->uri->segment(3));
                    redirect($this->redirect_url_plus);
                    //redirect('salonConfigrations/Config');
                    //redirect('Salon554Reports');
                }else{
                    $this->data['error_msg'] = 'Wrong email or password, please try again.';
                }
            }
        }
        //load the view
        
        $this->data['body'] = $this->load->view('users/login', $this->data , TRUE);
        $this->template->load('default',$this->data);
    }
    
    /*
     * User registration
     */
    public function notallowed(){
        $userData = array();
        
        if($this->session->userdata('isUserLoggedIn')){
            redirect(site_url('users/account'));
        }
        
        
        $this->data['title'] = 'User Registration';
        if($this->input->post('regisSubmit')){
            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|callback_email_check');
            $this->form_validation->set_rules('password', 'password', 'required');
            $this->form_validation->set_rules('conf_password', 'confirm password', 'required|matches[password]');

            $userData = array(
                'name' => strip_tags($this->input->post('name')),
                'email' => strip_tags($this->input->post('email')),
                'password' => md5($this->input->post('password')),
                'gender' => $this->input->post('gender'),
                'phone' => strip_tags($this->input->post('phone'))
            );

            if($this->form_validation->run() == true){
                $insert = $this->user->insert($userData);
                if($insert){
                    $this->session->set_userdata('success_msg', 'Your registration was successfully. Please login to your account.');
                    redirect('users/login');
                }else{
                    $this->data['error_msg'] = 'Some problems occured, please try again.';
                }
            }
        }
        $data['user'] = $userData;
        //load the view
       
        $this->data['body'] = $this->load->view('users/registration', $this->data , TRUE);
        $this->template->load('default',$this->data);
    }
    
    /*
     * User logout
     */
    public function logout(){
        $this->session->unset_userdata('isUserLoggedIn');
        $this->session->unset_userdata('userId');
        $this->session->sess_destroy();
        redirect('users/login/');
    }
    
    /*
     * Existing email check during validation
     */
    public function email_check($str){
        $con['returnType'] = 'count';
        $con['conditions'] = array('email'=>$str);
        $checkEmail = $this->user->getRows($con);
        if($checkEmail > 0){
            $this->form_validation->set_message('email_check', 'The given email already exists.');
            return FALSE;
        } else {
            return TRUE;
        }
    }
}
