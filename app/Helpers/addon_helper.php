<?php  defined('APPPATH') || exit('No direct script access allowed');
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

/* CHECK THE ADDON STATUS */
if (! function_exists('addon_status')) {
    function addon_status($unique_identifier = '')
	{
		$db = \Config\Database::connect();

		$result = $db->table('addons')
					 ->where('unique_identifier', $unique_identifier)
					 ->get()
					 ->getRowArray();

		if ($result) {
			return $result['status'];
		}

		return 0;
	}
}

/* CHECK THE LIVE CLASS STATUS */
if (! function_exists('live_class_status')) {
    function live_class_status($course_id = "") {
        $db = \Config\Database::connect();
    $session = session();
    $request = service('request');
        
        $result = $db->table('course')->where(array('id' => $course_id)->get());
        if ($result->getNumRows() > 0) {
            $result = $result->getRowArray();
            return $result['is_live_class'];
        }else{
            return 0;
        }
    }
}

/* CHECK IF THE STUDENT IS ELIGIBLE FOR DOWNLOADING THE CERTIFICATE */
if (! function_exists('certificate_eligibility')) {
    function certificate_eligibility($course_id = "", $user_id = "") {
        $db = \Config\Database::connect();
    $session = session();
    $request = service('request');
        

        if ($user_id == "") {
            $user_id = session()->get('user_id');
        }
        $result = $db->table('certificates')->where(array('course_id' => $course_id, 'student_id' => $user_id)->get());
        if ($result->getNumRows() > 0) {
            return 1;
        }else{
            return 0;
        }
    }
}

/* GET THE SHAREABLE LINK OF CERTIFICATE */
if (! function_exists('generate_certificate')) {
    function generate_certificate($course_id = "", $user_id = "") {
        $db = \Config\Database::connect();
    $session = session();
    $request = service('request');
        

        if ($user_id == "") {
            $user_id = session()->get('user_id');
        }
        $result = $db->table('certificates')->where(array('course_id' => $course_id, 'student_id' => $user_id)->get())->getRowArray();
        return $result['shareable_url'];
    }
}

/* COUNT OFFLINE PAYMENT PENDING USER */
if (! function_exists('get_pending_offline_payment')) {
    function get_pending_offline_payment() {
        $db = \Config\Database::connect();
    $session = session();
    $request = service('request');
        

        $count_pending_payment = count($db->table('offline_payment')->where(array('status' => 0)->get())->getResultArray());
        return $count_pending_payment;
    }
}


use Aws\S3\S3Client;
if (! function_exists('get_video_url')) {
    function get_video_url($url, $course_id){
        $db = \Config\Database::connect();
    $session = session();
    $request = service('request');
        
        $course = $db->where('id', $course_id)->get('course')->getRowArray();

        $file = explode('wasabi-',$url);
        $name = slugify($course['title']).'/wasabi-'.$file[2];
        
        if($url){
            require_once APPPATH . 'libraries/s3-vendor/autoloader.php';
            $s3 = new S3Client([
                'endpoint' => 'http://s3.wasabisys.com',
                'region' => get_settings('wasabi_region'),
                'version' => 'latest',
                'credentials' => array(
                'key' => get_settings('wasabi_key'),
                'secret' =>get_settings('wasabi_secret_key'),
                )
            ]);
                
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => get_settings('wasabi_bucketname'),
                'Key' => $name,
                'ACL' => 'public-read',
            ]);
            $currentMinute = date('i')+10000;
            $request = $s3->createPresignedRequest($cmd, $currentMinute.' minutes');
            $presignedUrl = (string)$request->getUri();
            return $presignedUrl;
        }else{
            return 0;
        }
    }
}



// ------------------------------------------------------------------------
/* End of file addon_helper.php */
/* Location: ./system/helpers/common.php */


