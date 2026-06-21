<?php

namespace App\Models;

use App\Models\NativeBaseModel;
class User_model extends NativeBaseModel
{

    function __construct()
    {
        parent::__construct();
        /*cache control*/

    }
// get_userId_by_email
    public function get_userId_by_email($email = "")
    {
        $this->db->where('email', $email);
        return $this->db->table('users')->get()->getRow()->id;
    }
    public function get_admin_details()
    {
        return $this->db->table('users')->where(array('role_id' => 1))->get();
    }

    public function get_user($user_id = 0)
    {
        if ($user_id > 0) {
            $this->db->where('id', $user_id);
        }
        $this->db->where('role_id', 2);
        return $this->db->table('users')->get();
    }

   public function get_all_user($user_id = 0)
	{
		$builder = $this->db->table('users');

		if ($user_id > 0) {
			$builder->where('id', $user_id);
		}

		return $builder->get();
	}

    public function add_user($is_instructor = false, $is_admin = false)
    {
        $validity = $this->check_duplication('on_create', $this->request->getPost('email'));
        if ($validity == false) {
            $this->session->setFlashdata('error_message', get_phrase('email_duplication'));
        } else {
          //  $data['unique_identifier'] = 0;
            $data['first_name'] = html_escape($this->request->getPost('first_name'));
            $data['last_name'] = html_escape($this->request->getPost('last_name'));
            $data['email'] = html_escape($this->request->getPost('email'));
            $data['password'] = sha1(html_escape($this->request->getPost('password')));
            $social_link['facebook'] = html_escape($this->request->getPost('facebook_link'));
            $social_link['twitter'] = html_escape($this->request->getPost('twitter_link'));
            $social_link['linkedin'] = html_escape($this->request->getPost('linkedin_link'));
            $data['social_links'] = json_encode($social_link);
            $data['biography'] = $this->request->getPost('biography');
            $data['phone'] = html_escape($this->request->getPost('phone'));
            $data['address'] = html_escape($this->request->getPost('address'));

            if ($is_admin) {
                $data['role_id'] = 1;
                $data['is_instructor'] = 1;
            } else {
                $data['role_id'] = 2;
            }

            $data['date_added'] = strtotime(date("Y-m-d H:i:s"));
            $data['wishlist'] = json_encode(array());
            $data['status'] = 1;
            $data['image'] = md5(rand(10000, 10000000));

            //All payment keys
            if(isset($_POST['gateways'])){
                $data['payment_keys'] = json_encode($_POST['gateways']);
            }

            if ($is_instructor) {
                $data['is_instructor'] = 1;
            }

            $this->db->table('users')->insert($data);
            $user_id = $this->db->insertID();
         //   $this->user_model->update_unique_identifier($user_id);

            // IF THIS IS A USER THEN INSERT BLANK VALUE IN PERMISSION TABLE AS WELL
            if ($is_admin) {
                $permission_data['admin_id'] = $user_id;
                $permission_data['permissions'] = json_encode(array());
                $this->db->table('permissions')->insert($permission_data);
            }

            $this->upload_user_image($data['image']);
            $this->session->setFlashdata('flash_message', get_phrase('user_added_successfully'));
        }
    }

    public function add_shortcut_user($is_instructor = false)
    {
        $validity = $this->check_duplication('on_create', $this->request->getPost('email'));
        if ($validity == false) {
            $response['status'] = 0;
            $response['message'] = get_phrase('this_email_already_exits') . '. ' . get_phrase('please_use_another_email');
            return json_encode($response);
        } else {
          //  $data['unique_identifier'] = 0;
            $data['first_name'] = html_escape($this->request->getPost('first_name'));
            $data['last_name'] = html_escape($this->request->getPost('last_name'));
            $data['email'] = html_escape($this->request->getPost('email'));
            $data['password'] = sha1(html_escape($this->request->getPost('password')));
            $social_link['facebook'] = '';
            $social_link['twitter'] = '';
            $social_link['linkedin'] = '';
            $data['social_links'] = json_encode($social_link);
            $data['role_id'] = 2;
            $data['date_added'] = strtotime(date("Y-m-d H:i:s"));
            $data['wishlist'] = json_encode(array());
            $data['status'] = 1;
            $data['image'] = md5(rand(10000, 10000000));

            // Add paypal keys
            $payment_keys = array();

            $paypal['production_client_id']  = '';
            $paypal['production_secret_key'] = '';
            $payment_keys['paypal'] = $paypal;

            // Add Stripe keys
            $stripe['public_live_key'] = '';
            $stripe['secret_live_key'] = '';
            $payment_keys['stripe'] = $stripe;

            // Add razorpay keys
            $razorpay['key_id'] = '';
            $razorpay['secret_key'] = '';
            $payment_keys['razorpay'] = $razorpay;

            //All payment keys
            $data['payment_keys'] = json_encode(array());

            if ($is_instructor) {
                $data['is_instructor'] = 1;
            }
            $this->db->table('users')->insert($data);

            $user_id = $this->db->insertID();
           // $this->user_model->update_unique_identifier($user_id);

            $this->session->setFlashdata('flash_message', get_phrase('user_added_successfully'));
            $response['status'] = 1;
            return json_encode($response);
        }
    }

    public function check_duplication($action = "", $email = "", $user_id = "")
    {
        $duplicate_email_check = $this->db->table('users')->where(array('email' => $email))->get();

        if ($action == 'on_create') {
            if ($duplicate_email_check->getNumRows() > 0) {
                if ($duplicate_email_check->getRow()->status == 1) {
                    return false;
                } else {
                    return 'unverified_user';
                }
            } else {
                return true;
            }
        } elseif ($action == 'on_update') {
            if ($duplicate_email_check->getNumRows() > 0) {
                if ($duplicate_email_check->getRow()->id == $user_id) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }

    public function edit_user($user_id = "")
    { // Admin does this editing
        $validity = $this->check_duplication('on_update', $this->request->getPost('email'), $user_id);
        if ($validity) {
            $data['first_name'] = html_escape($this->request->getPost('first_name'));
            $data['last_name'] = html_escape($this->request->getPost('last_name'));
			 $data['company_name'] = html_escape($this->request->getPost('company_name'));
            if (isset($_POST['email'])) {
                $data['email'] = html_escape($this->request->getPost('email'));
            }
            $social_link['facebook'] = html_escape($this->request->getPost('facebook_link'));
            $social_link['twitter'] = html_escape($this->request->getPost('twitter_link'));
            $social_link['linkedin'] = html_escape($this->request->getPost('linkedin_link'));
            $data['social_links'] = json_encode($social_link);
            $data['biography'] = $this->request->getPost('biography');
            $data['title'] = html_escape($this->request->getPost('title'));
            $data['skills'] = html_escape($this->request->getPost('skills'));
            $data['last_modified'] = strtotime(date("Y-m-d H:i:s"));

            $data['phone'] = html_escape($this->request->getPost('phone'));
            $data['address'] = html_escape($this->request->getPost('address'));

            if (isset($_FILES['user_image']) && $_FILES['user_image']['name'] != "") {
                unlink('uploads/user_image/' . $this->db->table('users')->where(array('id' => $user_id))->get()->getRow()->image . '.jpg');
                $data['image'] = md5(rand(10000, 10000000));
                $this->upload_user_image($data['image']);
            }

            //All payment keys
            if(isset($_POST['gateways'])){
                $data['payment_keys'] = json_encode($_POST['gateways']);
            }

            $this->db->where('id', $user_id);
            $this->db->table('users')->update($data);
            $this->session->setFlashdata('flash_message', get_phrase('user_update_successfully'));
        } else {
            $this->session->setFlashdata('error_message', get_phrase('email_duplication'));
        }
    }
    public function delete_user($user_id = "")
    {
        $this->db->where('id', $user_id);
        $this->db->table('users')->delete();
        $this->session->setFlashdata('flash_message', get_phrase('user_deleted_successfully'));
    }

    public function unlock_screen_by_password($password = "")
    {
        $password = sha1($password);
        return $this->db->table('users')->where(array('id' => $this->session->get('user_id')->get(), 'password' => $password))->getNumRows();
    }

    public function register_user($data)
    {
        $this->db->table('users')->insert($data);
        $user_id = $this->db->insertID();
       // $this->user_model->update_unique_identifier($user_id);
        return $user_id;
    }

    public function register_user_update_code($data, $status = "")
    {

        //If get back disabled user and again signup
        $update_code['status'] = $status;

        $update_code['verification_code'] = $data['verification_code'];
        $update_code['password'] = $data['password'];
        $this->db->where('email', $data['email']);
        $this->db->table('users')->update($update_code);
    }

    public function my_courses($user_id = "")
    {
        if ($user_id == "") {
            $user_id = $this->session->get('user_id');
        }
        return $this->db->table('enrol')->where(array('user_id' => $user_id))->get();
    }

    public function upload_user_image($image_code)
    {
        if (isset($_FILES['user_image']) && $_FILES['user_image']['name'] != "") {
            move_uploaded_file($_FILES['user_image']['tmp_name'], 'uploads/user_image/' . $image_code . '.jpg');
            $this->session->setFlashdata('flash_message', get_phrase('user_update_successfully'));
        }
    }

    public function update_account_settings($user_id)
    {
        $validity = $this->check_duplication('on_update', $this->request->getPost('email'), $user_id);
        if ($validity) {
            if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
                $user_details = $this->get_user($user_id)->getRowArray();
                $current_password = $this->request->getPost('current_password');
                $new_password = $this->request->getPost('new_password');
                $confirm_password = $this->request->getPost('confirm_password');
                if ($user_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
                    $data['password'] = sha1($new_password);
                } else {
                    $this->session->setFlashdata('error_message', get_phrase('mismatch_password'));
                    return;
                }
            }
            $this->db->where('id', $user_id);
            $this->db->table('users')->update($data);
            $this->session->setFlashdata('flash_message', get_phrase('updated_successfully'));
        } else {
            $this->session->setFlashdata('error_message', get_phrase('email_duplication'));
        }
    }

    public function change_password($user_id)
    {
        $data = array();
        if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $user_details = $this->get_all_user($user_id)->getRowArray();
            $current_password = $this->request->getPost('current_password');
            $new_password = $this->request->getPost('new_password');
            $confirm_password = $this->request->getPost('confirm_password');

            if ($user_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
                $data['password'] = sha1($new_password);
            } else {
                $this->session->setFlashdata('error_message', get_phrase('mismatch_password'));
                return;
            }
        }

        $this->db->where('id', $user_id);
        $this->db->table('users')->update($data);
        $this->session->setFlashdata('flash_message', get_phrase('password_updated'));
    }


    public function get_instructor($id = 0)
    {
        if ($id > 0) {
            return $this->db->table('users')->where(array('id' => $id, 'is_instructor' => 1))->get();
        } else {
            return $this->db->table('users')->where(array('is_instructor' => 1))->get();
        }
    }

    public function get_instructor_by_email($email = null)
    {
        return $this->db->table('users')->where(array('email' => $email, 'is_instructor' => 1))->get();
    }

    public function get_admins($id = 0)
    {
        if ($id > 0) {
            return $this->db->table('users')->where(array('id' => $id, 'role_id' => 1))->get();
        } else {
            return $this->db->table('users')->where(array('role_id' => 1))->get();
        }
    }

    public function get_number_of_active_courses_of_instructor($instructor_id)
    {
        $result = $this->crud_model->get_courses_by_instructor_id($instructor_id, 'active');
        return $result->getNumRows();
    }

    public function get_user_image_url($user_id)
    {
        $user_profile_image = $this->db->table('users')->where(array('id' => $user_id))->get()->getRow()->image;
        if (file_exists('uploads/user_image/optimized/' . $user_profile_image . '.jpg')){
            return base_url() . 'uploads/user_image/optimized/' . $user_profile_image . '.jpg';
        }elseif(file_exists('uploads/user_image/' . $user_profile_image . '.jpg')){
            //resizeImage
            resizeImage('uploads/user_image/' . $user_profile_image . '.jpg', 'uploads/user_image/optimized/', 220);
            return base_url() . 'uploads/user_image/' . $user_profile_image . '.jpg';
        }else{
            return base_url() . 'uploads/user_image/placeholder.png';
        }
    }

    public function get_instructor_list()
    {
        return $this->db->table('users')->where(array('status' => '1', 'is_instructor' => '1'))->get();
        // $query1 = $this->db->table('course')->where(array('status' => 'active'))->get()->getResultArray();
        // $instructor_ids = array();
        // $query_result = array();
        // foreach ($query1 as $row1) {
        //     if (!in_array($row1['user_id'], $instructor_ids) && $row1['user_id'] != "") {
        //         array_push($instructor_ids, $row1['user_id']);
        //     }
        // }
        // if (count($instructor_ids) > 0) {
        //     $this->db->whereIn('id', $instructor_ids);
        //     $query_result = $this->db->table('users')->get();
        // } else {
        //     $query_result = $this->get_admin_details();
        // }

        // return $query_result;
    }

    public function update_instructor_paypal_settings($user_id = '')
    {
        $user_details = $this->get_all_user($user_id)->getRowArray();
        $payment_keys = json_decode($user_details['payment_keys'], true);
        // Update paypal keys
        $paypal['production_client_id'] = html_escape($this->request->getPost('paypal_client_id'));
        $paypal['production_secret_key'] = html_escape($this->request->getPost('paypal_secret_key'));
        $payment_keys['paypal'] = $paypal;

        //All payment keys
        $data['payment_keys'] = json_encode($payment_keys);

        $this->db->where('id', $user_id);
        $this->db->table('users')->update($data);
    }
    public function update_instructor_stripe_settings($user_id = '')
    {
        $user_details = $this->get_all_user($user_id)->getRowArray();
        $payment_keys = json_decode($user_details['payment_keys'], true);
        // Update stripe keys
        $stripe['public_live_key'] = html_escape($this->request->getPost('stripe_public_key'));
        $stripe['secret_live_key'] = html_escape($this->request->getPost('stripe_secret_key'));
        $payment_keys['stripe'] = $stripe;

        //All payment keys
        $data['payment_keys'] = json_encode($payment_keys);

        $this->db->where('id', $user_id);
        $this->db->table('users')->update($data);
    }

    public function update_instructor_razorpay_settings($user_id = ''){
        $user_details = $this->get_all_user($user_id)->getRowArray();
        $payment_keys = json_decode($user_details['payment_keys'], true);
        // Update razorpay keys
        $razorpay['key_id'] = html_escape($this->request->getPost('key_id'));
        $razorpay['secret_key'] = html_escape($this->request->getPost('secret_key'));
        $payment_keys['razorpay'] = $razorpay;

        //All payment keys
        $data['payment_keys'] = json_encode($payment_keys);

        $this->db->where('id', $user_id);
        $this->db->table('users')->update($data);
    }

    // POST INSTRUCTOR APPLICATION FORM AND INSERT INTO DATABASE IF EVERYTHING IS OKAY
    public function post_instructor_application($user_id = "")
    {
        if($user_id == ""){
            $user_id = $this->request->getPost('id');
        }
        $user_details = $this->get_all_user($user_id)->getRowArray();

        if($this->request->getPost('email')){
            $email = $this->request->getPost('email');
        }else{
            $email = $user_details['email'];
        }

        // CHECK IF THE PROVIDED ID AND EMAIL ARE COMING FROM VALID USER
        if ($user_details['email'] == $email) {

            // GET PREVIOUS DATA FROM APPLICATION TABLE
            $previous_data = $this->get_applications($user_details['id'], 'user')->getNumRows();
            // CHECK IF THE USER HAS SUBMITTED FORM BEFORE
            if ($previous_data > 0) {
                $this->session->setFlashdata('error_message', get_phrase('already_submitted'));
                redirect(site_url('user/become_an_instructor'), 'refresh');
            }
            $data['user_id'] = $user_id;
            $data['address'] = $this->request->getPost('address');
            $data['phone'] = $this->request->getPost('phone');
            $data['message'] = $this->request->getPost('message');
            if (isset($_FILES['document']) && $_FILES['document']['name'] != "") {
                if (!file_exists('uploads/document')) {
                    mkdir('uploads/document', 0777, true);
                }
                $accepted_ext = array('doc', 'docs', 'pdf', 'txt', 'png', 'jpg', 'jpeg');
                $path = $_FILES['document']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if (in_array(strtolower($ext), $accepted_ext)) {
                    $document_custom_name = random(15) . '.' . $ext;
                    $data['document'] = $document_custom_name;
                    move_uploaded_file($_FILES['document']['tmp_name'], 'uploads/document/' . $document_custom_name);
                } else {
                    $this->session->setFlashdata('error_message', get_phrase('invalide_file'));
                    redirect(site_url('user/become_an_instructor'), 'refresh');
                }
            }
            $this->db->table('applications')->insert($data);
            $this->session->setFlashdata('flash_message', site_phrase('You have successfully submitted your application.').' '.get_phrase('We will review it and notify you via email notification'));
            redirect(site_url('user/become_an_instructor'), 'refresh');
        } else {
            $this->session->setFlashdata('error_message', get_phrase('user_not_found'));
            redirect(site_url('user/become_an_instructor'), 'refresh');
        }
    }

    function instructor_application(){
        // FIRST GET THE USER DETAILS
        $user = $this->db->table('users')->where(['email' => $this->request->getPost('email')])->get();
        if($user->getNumRows() > 0){
            $user_details = $user->getRowArray();
            $previous_data = $this->get_applications($user_details['id'], 'user')->getNumRows();
            if ($previous_data == 0) {
                if (!file_exists('uploads/document')) {
                    mkdir('uploads/document', 0777, true);
                }
                $data['user_id'] = $user_details['id'];
                $data['address'] = $user_details['address'];
                $data['phone'] = $this->request->getPost('phone');
                $data['message'] = $this->request->getPost('message');

                $document_custom_name =random(15).'.'.pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
                $data['document'] = $document_custom_name;
                move_uploaded_file($_FILES['document']['tmp_name'], 'uploads/document/' . $document_custom_name);
                $this->db->table('applications')->insert($data);
            }
        }
    }


    // GET INSTRUCTOR APPLICATIONS
    public function get_applications($id = "", $type = "")
    {
        if ($id > 0 && !empty($type)) {
            if ($type == 'user') {
                $applications = $this->db->table('applications')->where(array('user_id' => $id))->get();
                return $applications;
            } else {
                $applications = $this->db->table('applications')->where(array('id' => $id))->get();
                return $applications;
            }
        } else {
            $this->db->orderBy("id", "DESC");
            $applications = $this->db->get_where('applications');
            return $applications;
        }
    }

    // GET APPROVED APPLICATIONS
    public function get_approved_applications()
    {
        $applications = $this->db->table('applications')->where(array('status' => 1))->get();
        return $applications;
    }

    // GET PENDING APPLICATIONS
    public function get_pending_applications()
    {
        $applications = $this->db->table('applications')->where(array('status' => 0))->get();
        return $applications;
    }

    //UPDATE STATUS OF INSTRUCTOR APPLICATION
    public function update_status_of_application($status, $application_id)
    {
        $application_details = $this->get_applications($application_id, 'application');
        if ($application_details->getNumRows() > 0) {
            $application_details = $application_details->getRowArray();
            if ($status == 'approve') {
                $application_data['status'] = 1;
                $this->db->where('id', $application_id);
                $this->db->table('applications')->update($application_data);

                $instructor_data['is_instructor'] = 1;
                $this->db->where('id', $application_details['user_id']);
                $this->db->table('users')->update($instructor_data);

                $this->session->setFlashdata('flash_message', get_phrase('application_approved_successfully'));
                redirect(site_url('admin/instructor_application'), 'refresh');
            } else {
                $this->db->where('id', $application_id);
                $this->db->table('applications')->delete();
                $this->session->setFlashdata('flash_message', get_phrase('application_deleted_successfully'));
                redirect(site_url('admin/instructor_application'), 'refresh');
            }
        } else {
            $this->session->setFlashdata('error_message', get_phrase('invalid_application'));
            redirect(site_url('admin/instructor_application'), 'refresh');
        }
    }

    // ASSIGN PERMISSION
    public function assign_permission()
    {
        $argument = html_escape($this->request->getPost('arg'));
        $argument = explode('-', $argument);
        $admin_id = $argument[0];
        $module = $argument[1];

        // CHECK IF IT IS A ROOT ADMIN
        if (is_root_admin($admin_id)) {
            return false;
        }

        $permission_data['admin_id'] = $admin_id;
        $previous_permissions = json_decode($this->get_admins_permission_json($permission_data['admin_id']), TRUE);

        if (in_array($module, $previous_permissions)) {
            $new_permission = array();
            foreach ($previous_permissions as $permission) {
                if ($permission != $module) {
                    array_push($new_permission, $permission);
                }
            }
        } else {
            array_push($previous_permissions, $module);
            $new_permission = $previous_permissions;
        }

        $permission_data['permissions'] = json_encode($new_permission);

        $this->db->where('admin_id', $admin_id);
        $this->db->table('permissions')->update($permission_data);
        return true;
    }

    // GET ADMIN'S PERMISSION JSON
    public function get_admins_permission_json($admin_id)
    {
        $admins_permissions = $this->db->table('permissions')->where(['admin_id' => $admin_id])->get()->getRowArray();
        return $admins_permissions['permissions'];
    }

    // GET MULTI INSTRUCTOR DETAILS WITH COURSE ID
    public function get_multi_instructor_details_with_csv($csv)
    {
        $instructor_ids = explode(',', $csv);
        $this->db->whereIn('id', $instructor_ids);
        return $this->db->table('users')->get()->getResultArray();
    }

    function quiz_submission_checker($quiz_id = ""){
        $quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->getRowArray();
        $total_quiz_seconds = time_to_seconds($quiz_details['duration']);

        $this->db->where('quiz_id', $quiz_id);
        $this->db->where('user_id', $this->session->get('user_id'));
        $query = $this->db->orderBy('quiz_result_id', 'desc')->get('quiz_results');
        if($query->getNumRows() > 0){
            $row = $query->getRowArray();
            if(($total_quiz_seconds + $row['date_added']) < time() && $total_quiz_seconds > 0 || $row['is_submitted'] == 1){

                if($row['is_submitted'] != 1){
                    $this->db->where('quiz_id', $quiz_id);
                    $this->db->where('user_id', $this->session->get('user_id'));
                    $this->db->table('quiz_results')->update(array('is_submitted' => 1));
                }

                return 'submitted';
            }else{
                return 'on_progress';
            }
        }else{
            return 'no_data';
        }
    }



/*START LOGIN LOGOUT AND DEVICE ALLOW SECTION*/
    // For device login tracker
    public function new_device_login_tracker($user_id = "", $is_verified = '')
    {
        $pre_sessions = array();
        $updated_session_arr = array();
        $current_session_id = session_id();
        $this->db->where('id', $user_id);
        $sessions = $this->db->table('users')->get();

        if($sessions->getRow()->role_id == 1){
            return;
        }

        $pre_sessions = json_decode($sessions->getRow()->sessions, true);

        if(is_array($pre_sessions) && count($pre_sessions) > 0){
            if($is_verified == true && !in_array($current_session_id, $pre_sessions)){
                $allowed_device = get_settings('allowed_device_number_of_loging');
                $previous_tatal_device = count($pre_sessions) + 1; //current device

                $removeable_device = $previous_tatal_device - $allowed_device;

                foreach($pre_sessions as $key => $pre_session){
                    if($removeable_device >= 1){
                        $this->db->where('id', $pre_session);
                        $this->db->table('ci_sessions')->delete();
                    }else{

                        if($this->db->table('ci_sessions')->where(['id' => $pre_session])->get()->getNumRows() > 0){
                            array_push($updated_session_arr, $pre_session);                        
                        }
                    }
                    $removeable_device = $removeable_device - 1;
                }
                array_push($updated_session_arr, $current_session_id);
            }else{
                if(!in_array($current_session_id, $pre_sessions)){
                    if(count($pre_sessions) >= get_settings('allowed_device_number_of_loging')){
                        $this->email_model->new_device_login_alert($user_id);
                        redirect(site_url('login/new_login_confirmation'), 'refresh');
                    }else{
                        $updated_session_arr = $pre_sessions;
                        array_push($updated_session_arr, $current_session_id);
                    }
                }
            }
        }else{
            $updated_session_arr = [$current_session_id];
        }

        if(count($updated_session_arr) > 0){
            $data['sessions'] = json_encode($updated_session_arr);
            $this->db->where('id', $user_id);
            $this->db->table('users')->update($data);
        }
    }

    function set_login_userdata($user_id = ""){
        // Checking login credential for admin
        $query = $this->db->table('users')->where(array('id' => $user_id))->get();

        if ($query->getNumRows() > 0) {
            $row = $query->getRow();
            //604800s == 7 days
            $this->session->set('custom_session_limit', (time()+864000));
            $this->session->set('user_id', $row->id);
            $this->session->set('role_id', $row->role_id);
            $this->session->set('role', get_user_role('user_role', $row->id));
            $this->session->set('name', $row->first_name . ' ' . $row->last_name);
            $this->session->set('is_instructor', $row->is_instructor);
            $this->session->setFlashdata('flash_message', get_phrase('welcome') . ' ' . $row->first_name . ' ' . $row->last_name);
            if ($row->role_id == 1) {
                $this->session->set('admin_login', '1');
                redirect(site_url('admin/dashboard'), 'refresh');
            } else if ($row->role_id == 2) {
                $this->session->set('user_login', '1');
                if($this->session->get('url_history')){
                    redirect($this->session->get('url_history'), 'refresh');
                }
                redirect(site_url('home'), 'refresh');
            }
        } else {
            $this->session->setFlashdata('error_message', get_phrase('invalid_login_credentials'));
            redirect(site_url('login'), 'refresh');
        }
    }

    function check_session_data($user_type = ""){
        $this->remove_garbage_collection();

        if (!$this->session->get('cart_items')) {
            $this->session->set('cart_items', array());
        }

        if (!$this->session->get('language')) {
            $this->session->set('language', get_settings('language'));
        }

        if($user_type == 'admin'){
            if($this->session->get('custom_session_limit') >= time()){
                $this->session->set('custom_session_limit', (time()+864000));
            }else{
                $this->session_destroy();
                redirect(site_url('login'), 'refresh');
            }

            if ($this->session->get('admin_login') != true) {
                redirect(site_url('login'), 'refresh');
            }
        }elseif($user_type == 'user'){
            if($this->session->get('custom_session_limit') >= time()){
                $this->session->set('custom_session_limit', (time()+864000));
            }else{
                $this->session_destroy();
                redirect(site_url('login'), 'refresh');
            }

            if ($this->session->get('user_login') != true) {
                redirect(site_url('login'), 'refresh');
            }else{
                if($this->get_all_user($this->session->get('user_id'))->getNumRows() == 0){
                    $this->session_destroy();
                    redirect(site_url('login'), 'refresh');
                }
            }
        }elseif($user_type == 'login'){
            if ($this->session->get('admin_login')) {
                redirect(site_url('admin'), 'refresh');
            } elseif ($this->session->get('user_login')) {
                redirect(site_url('home/my_courses'), 'refresh');
            }
        }
    }

    public function session_destroy()
    {
        $this->remove_garbage_collection();

        $logged_in_user_id = $this->session->get('user_id');
        if($logged_in_user_id > 0 && $this->session->get('user_login') == 1){
            $pre_sessions = array();
            $updated_session_arr = array();
            $current_session_id = session_id();

            $this->db->where('id', $logged_in_user_id);
            $sessions = $this->db->table('users')->get()->getRow()->sessions;
            $pre_sessions = json_decode($sessions, true);
            if(is_array($pre_sessions)){
                foreach($pre_sessions as $key => $pre_session){
                    if($pre_session != $current_session_id){
                        if($this->db->table('ci_sessions')->where(['id' => $pre_session])->get()->getNumRows() > 0){
                            array_push($updated_session_arr, $pre_session);                        
                        }
                    }else{
                        $this->db->where('id', $pre_session);
                        $this->db->table('ci_sessions')->delete();
                    }
                }
                $data['sessions'] = json_encode($updated_session_arr);
                $this->db->where('id', $logged_in_user_id);
                $this->db->table('users')->update($data);
            }
        }

        $this->session->remove('admin_login');
        $this->session->remove('user_login');
        $this->session->remove('custom_session_limit');
        $this->session->remove('user_id');
        $this->session->remove('role_id');
        $this->session->remove('role');
        $this->session->remove('name');
        $this->session->remove('is_instructor');
        $this->session->remove('url_history');
        $this->session->remove('app_url');
        $this->session->remove('total_price_of_checking_out');
        $this->session->remove('register_email');
        $this->session->remove('applied_coupon');
        $this->session->remove('new_device_code_expiration_time');
        $this->session->remove('new_device_user_email');
        $this->session->remove('new_device_user_id');
        $this->session->remove('new_device_verification_code');

    }

    function remove_garbage_collection(){
        $this->db->where('timestamp <', time()-864000);
        $this->db->table('ci_sessions')->delete();
    }
    /*END LOGIN LOGOUT AND DEVICE ALLOW SECTION*/


   /* function update_unique_identifier($user_id = ""){
        $data['unique_identifier'] = $user_id.strtolower(random(10));
        $this->db->where('unique_identifier', 0);
        $this->db->where('id', $user_id);
        $this->db->table('users')->update($data);
    }*/



    //course-gift-ryan

    function get_user_by_email($email = ""){
        if($email){
            $this->db->where('email', $email);
        }
        return $this->db->table('users')->get();
    }

    //course-gift-ryan



    // Instructor Follow

    public function toggle_following($instructor_id, $user_id) {
        $this->db->where('instructor_id', $instructor_id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->table('instructor_followings')->get();
        if ($query->getNumRows() > 0) {
            $this->db->where('instructor_id', $instructor_id);
            $this->db->where('user_id', $user_id);
            $this->db->table('instructor_followings')->delete();
            return ['status' => 'unfollowed'];
        } else {
            $data = [
                'instructor_id' => $instructor_id,
                'user_id' => $user_id,
                'is_following' => 1
            ];
            $this->db->table('instructor_followings')->insert($data);
            $this->email_model->instructor_followups_reminder($instructor_id, $user_id);
            return ['status' => 'followed'];
            
            
        }
       
    }
    
    
    public function is_following($instructor_id, $user_id) {
        $this->db->where('instructor_id', $instructor_id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->table('instructor_followings')->get();
        
        return $query->getNumRows() > 0;
    }
    
    public function get_following_instructors($user_id) {
        $this->db->where('user_id', $user_id);
        $query = $this->db->table('instructor_followings')->get();
        return $query->getResult();
    }
    


}


