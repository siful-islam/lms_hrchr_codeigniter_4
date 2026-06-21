<?php

namespace App\Controllers;

/*
 *  @author   : Creativeitem
 *  date    : 14 september, 2017
 *  Ekattor School Management System Pro
 *  http://codecanyon.net/user/Creativeitem
 *  http://support.creativeitem.com
 */
class Modal extends BaseController {


	function __construct()
  {
    parent::__construct();
// Temporary error display for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
	
    $this->load->database();
    $this->load->library('session');

	$timezone = get_settings('timezone');
    if (!empty($timezone)) {
        date_default_timezone_set($timezone);
    } else {
        date_default_timezone_set('UTC');
    }		
  } 
// Temporary debug function - remove after fixing
function test()
{
  echo "Modal controller working<br>";
  echo "Session role: " . $this->session->get('role') . "<br>";
  echo "User ID: " . $this->session->get('user_id') . "<br>";
  echo "All session: <pre>" . print_r($this->session->userdata(), true) . "</pre>";
}
	function popup($page_name = '' , $param2 = '' , $param3 = '', $param4 = '', $param5 = '', $param6 = '', $param7 = '')
	{
		$logged_in_user_role 		= strtolower($this->session->get('role'));
		// Redirect to login if session expired
		if (empty($logged_in_user_role)) {
			echo '<div class="alert alert-warning">Session expired. Please <a href="'.site_url('login').'" target="_top">login again</a>.</div>';
			return;
		}
   
		$page_data['param2']		=	$param2;
		$page_data['param3']		=	$param3;
		$page_data['param4']		=	$param4;
		$page_data['param5']		=	$param5;
		$page_data['param6']		=	$param6;
		$page_data['param7']		=	$param7;
		$this->load->view( 'backend/'.$logged_in_user_role.'/'.$page_name.'.php' ,$page_data);
	}
}


