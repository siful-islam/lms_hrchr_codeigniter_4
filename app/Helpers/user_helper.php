<?php defined('APPPATH') || exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */


if ( ! function_exists('get_user_role'))
{
	function get_user_role($type = "", $user_id = '') {
		$db = \Config\Database::connect();
    $session = session();
    $request = service('request');
		

        $role_id	=	$db->table('users')->where(array('id' => $user_id)->get())->getRow()->role_id;
        $user_role	=	$db->table('role')->where(array('id' => $role_id)->get())->getRow()->name;

        if ($type == "user_role") {
            return $user_role;
        }else {
            return $role_id;
        }
	}
}


if ( ! function_exists('is_purchased'))
{
	function is_purchased($course_id = "", $user_id = "") {
		$db = \Config\Database::connect();
    $session = session();
    $request = service('request');
		if (!session()->get('user_login'))
			return false;

		if($user_id == "")
			$user_id = session()->get('user_id');

		$enrolled_history = $db->table('enrol')->where(['user_id' => $user_id, 'course_id' => $course_id])->get();
		if ($enrolled_history->getNumRows() > 0) {
			$expiry_date = $enrolled_history->getRow()->expiry_date;
			if($expiry_date == null || $expiry_date >= time()){
				return true;
			}else{
				return false;
			}
		}else {
			return false;
		}
	}
}
if ( ! function_exists('enroll_status'))
{
	function enroll_status($course_id = "", $user_id = "") {
		$db = \Config\Database::connect();
    $session = session();
    $request = service('request');
		if($user_id == "")
			$user_id = session()->get('user_id');


		$enrolled_history = $db->table('enrol')->where(['user_id' => $user_id, 'course_id' => $course_id])->get();
		if ($enrolled_history->getNumRows() > 0) {
			$expiry_date = $enrolled_history->getRow()->expiry_date;
			if($expiry_date == null || $expiry_date >= time()){
				return 'valid';
			}else{
				return 'expired';
			}
		}else {
			return false;
		}
	}
}

// ------------------------------------------------------------------------
/* End of file user_helper.php */
/* Location: ./system/helpers/user_helper.php */


