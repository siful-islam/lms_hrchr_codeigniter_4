<?php

namespace App\Models\addons;

use App\Models\NativeBaseModel;
class Team_package_model extends NativeBaseModel
{

    function __construct()
    {
        parent::__construct();
        /*cache control*/

    }

    // reuseable functions
    // 1. get record from any table
    function get_table($table, $id = '', $sort = '')
    {
        if ($sort != '') {
            $this->db->orderBy('order_by', $sort);
        }
        if ($id != '' && is_numeric($id)) {
            $this->db->where('id', $id);
        } elseif ($id != '' && !is_numeric($id)) {
            $arr = explode('-', $id);
            $this->db->where($arr[0], $arr[1]);
        }

        return $this->db->get($table);
    }

    // 2. file uploader function
    function upload_files($name, $path, $original = '')
    {
        if (isset($_FILES[$name]) && $_FILES[$name]['name'] != "") {
            $extension = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
            if ($original == 'original') {
                $file_name = $_FILES[$name]['name'];
            } elseif ($original == 'class_record') {
                if ($extension == 'mp4' || $extension == 'mov' || $extension == 'avi' || $extension == 'wmv' || $extension == 'WebM') {
                    $file_name = 'cls_rec_' . rand(100000, 999999) . '.' . $extension;
                } else {
                    return FALSE;
                }
            } elseif ($original == '') {
                $file_name = md5(rand(10000000, 20000000)) . '.' . $extension;
            }

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            move_uploaded_file($_FILES[$name]['tmp_name'], $path . $file_name);
            return $file_name;
        }
        return FALSE;
    }

    // 3. trim and json response
    function trim_and_return_json($untrimmed_array = [])
    {
        if (!is_array($untrimmed_array)) {
            $untrimmed_array = [];
        }
        $trimmed_array = array();
        if (sizeof($untrimmed_array) > 0) {
            foreach ($untrimmed_array as $row) {
                if ($row != "") {
                    array_push($trimmed_array, $row);
                }
            }
        }
        return json_encode($trimmed_array);
    }

    // .4 delete row
    function delete_item($table, $id)
    {
        $this->db->where('id', $id);
        $this->db->delete($table);
        return true;
    }

    // 5. get package thumbnail
    function get_package_thumbnail($package_id)
    {
        if (is_numeric($package_id) && $package_id > 0) {
            $package_details = $this->db->where('id', $package_id)->get('team_packages')->getRowArray();
            if (!empty($package_details) && !empty($package_details['thumbnail'])) {
                return base_url() . 'uploads/team_training/thumbnail/' . $package_details['thumbnail'];
            }
        }
        $course_media_placeholders = themeConfiguration(get_frontend_settings('theme'), 'course_media_placeholders');
        return base_url() . $course_media_placeholders['course_thumbnail' . '_placeholder'];
    }

    // 6. is purchased
    function is_purchased($package_id)
    {
        $package_details = $this->db->where('id', $package_id)->get('team_packages')->getRowArray();
        $user = $this->session->get('user_id');
        $this->db->where(['package_id' => $package_id, 'user_id' => $user]);
        $this->db->orderBy('id', 'desc');
        $purchase_details = $this->db->table('team_package_payment')->get()->getRowArray();

        if ($purchase_details && $package_details['expiry_period'] == 'lifetime') {
            return true;
        }

        $is_purchase_valid = $purchase_details && $purchase_details['expiry_date'] > time();
        return $is_purchase_valid ? $purchase_details : false;
    }

    function get_package_purchase($package_id)
    {
        $purchase = $this->db->where('package_id', $package_id)->get('team_package_payment')->getNumRows();
        return $purchase;
    }

    // check_member_capacity
    function check_member_capacity($package_id)
    {
        $package_details = $this->db->where('id', $package_id)->get('team_packages')->getRowArray();
        $occupied = $this->db->where(['team_id' => $package_id, 'leader_id' => $this->session->get('user_id')])
            ->get('team_members')->getNumRows();

        $capacity_info = [
            'max_capacity' => $package_details['max_students'],
            'occupied' => $occupied,
            'available' => $package_details['max_students'] - $occupied,
        ];
        return $capacity_info;
    }

    // related course packages
    function course_related_packages($course_id)
    {
        $related_packages = $this->db->where('course_id', $course_id)
            ->orderBy('id', 'desc')->get('team_packages', 10)->getResultArray();
        return $related_packages;
    }

    // package sells
    function package_sells($package_id)
    {
        return $this->db->where('package_id', $package_id)->get('team_package_payment')->getNumRows();
    }

    // get team members and check member exists or not
    function my_team_member($package_id, $member_id = '')
    {
        if ($member_id == '') {
            $this->db->select('team_members.*, team_members.id as member_table_id, users.id as id, users.first_name, users.last_name, users.email');
            $this->db->from('team_members');
            $this->db->join('users', 'team_members.member_id = users.id');
            $this->db->where('team_members.leader_id', $this->session->get('user_id'));
            $this->db->where('team_members.team_id', $package_id);
            $query = $this->db->get()->getResultArray();
            return $query;
        } else {
            $selected_member = $this->db->where('leader_id', $this->session->get('user_id'))
                ->where('member_id', $member_id)
                ->where('team_id', $package_id)
                ->get('team_members')->getRowArray();
            return $selected_member ? $selected_member : false;
        }
    }

    //                                       	6. package payment config
    /*------------------------------------------------------------------------------------------------------------*/
    function configure_package_payment($package_details)
    {
        $items = [];
        $total_payable_amount = 0;

        //item detail
        $item_details['id'] = $package_details['id'];
        $item_details['title'] = $package_details['title'];
        $item_details['creator_id'] = $package_details['user_id'];
        $item_details['user_id'] = $this->session->get('user_id');
        $item_details['price'] = $package_details['price'];
        $item_details['actual_price'] = $package_details['price'];

        //ended item detail
        $items += [$item_details];

        //common structure for all payment gateways and all type of payment
        $data['total_payable_amount'] = $item_details['actual_price'];
        $data['items'] = $items;
        $data['is_instructor_payout_user_id'] = 0;
        $data['payment_title'] = get_phrase('pay_for_package_purchase');
        $data['success_url'] = site_url('addons/team_training/success_package_payment');
        $data['cancel_url'] = site_url('payment');
        $data['back_url'] = site_url('addons/team_training/package_details/' . $item_details['id']);
        $this->session->set('payment_details', $data);
    }

    //                                       	7. bootcamp payment history
    /*------------------------------------------------------------------------------------------------------------*/
    function store_payment_history($payment_method, $payment_details)
    {
        $package_details = $this->db->where('id', $payment_details['items'][0]['id'])->get('team_packages')->getRowArray();

        if (empty($package_details)) {
            return ['err', 'package_not_found'];
        }

        // store payment history
        $data['package_id'] = $payment_details['items'][0]['id'];
        $data['purchase_date'] = time();
        $data['expiry_date'] = strtotime('+' . $package_details['expiry_period'] . 'month');
        $data['max_students'] = $package_details['max_students'];
        $data['user_id'] = $payment_details['items'][0]['user_id'];
        $data['paid_amount'] = $payment_details['items'][0]['actual_price'];
        $data['payment_method'] = $payment_method;
        $data['payment_keys'] = isset($_GET['session_id']) ? json_encode(['transaction_id' => $_GET['session_id']]) : '';
        $data['instructor_revenue'] = round($data['paid_amount'] * (get_settings('instructor_revenue') / 100));
        $data['admin_revenue'] = round($data['paid_amount'] - $data['instructor_revenue']);
        $data['instructor_payment_status'] = 0;
        $data['date_added'] = time();
        $data['updated_date'] = $data['date_added'];

        $insert = $this->db->table('team_package_payment')->insert($data);
        if ($insert) {
            return ['', 'payment_successful.', 'addons/team_training/package_details/' . $data['package_id']];
        }
        return ['err', 'something_went_wrong', ''];
    }

    // add new team member
    function insert_member($package_id, $user_id)
    {
        $member['team_id'] = $package_id;
        $member['leader_id'] = $this->session->get('user_id');
        $member['member_id'] = $user_id;
        $member['date_added'] = time();
        $member['updated_date'] = $member['date_added'];
        $insert = $this->db->table('team_members')->insert($member);
        return $insert ? true : false;
    }

    public function add_team_package()
    {
        // check package price
        if ($this->request->getPost('price') < 1) {
            return ['err', 'invalid_price'];
        }

        // check package expiry
        $expiryPeriod = $this->request->getPost('expiry_period');
        $numberOfMonths = $this->request->getPost('number_of_month');

        if ($expiryPeriod != 'limited_time') {
            $data['expiry_period'] = 'lifetime';
        } elseif ($numberOfMonths > 0) {
            $data['expiry_period'] = $numberOfMonths;
        } else {
            return ['err', 'please_set_valid_schedule'];
        }

        $enable_this_package = $this->request->getPost('pkg_status');
        if (isset($enable_this_package) && $enable_this_package) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }

        // upload team thumbnail
        $thumbnail = $this->team_package_model->upload_files('thumbnail', 'uploads/team_training/thumbnail/');
        if ($thumbnail) {
            $data['thumbnail'] = $thumbnail;
        }

        $data['features'] = $this->crud_model->trim_and_return_json($this->request->getPost('features'));
        $data['title'] = html_escape($this->request->getPost('title'));
		 $course_ids = $this->request->getPost('course_id');
        if (empty($course_ids) || !is_array($course_ids)) {
            return ['err', 'please_select_course'];
        }
        $data['course_id'] = json_encode($course_ids);											  
        $data['max_students'] = html_escape($this->request->getPost('max_students'));
        $data['price'] = html_escape($this->request->getPost('price'));
        $data['privacy'] = html_escape($this->request->getPost('privacy'));

        //for instructors package creation
        if (empty($data['privacy'])) {
            $data['privacy'] = 'public';
        }

        $data['is_free_package'] = html_escape($this->request->getPost('is_free_package'));
		$course_details = $this->crud_model->get_course_by_id($course_ids[0])->getRowArray();				   
        $data['user_id'] = $course_details['creator'];
        $data['date_added'] = time();
        $data['updated_date'] = $data['date_added'];

        //seo-fileds
        $data['meta_title'] = html_escape($this->request->getPost('meta_title'));
        $data['meta_description'] = html_escape($this->request->getPost('meta_description'));
        $data['meta_keywords'] = html_escape($this->request->getPost('meta_keywords'));
        $data['og_title'] = html_escape($this->request->getPost('og_title'));
        $data['og_description'] = html_escape($this->request->getPost('og_description'));
        $data['json_ld'] = $this->request->getPost('json_ld');

        $insert = $this->db->table('team_packages')->insert($data);
        return $insert ? ['', 'package_added_successfully'] : ['err', 'failed_to_create_package'];
    }

    public function edit_team_package($team_package_id = "")
    {

        //pkg expiry period
        if ($this->request->getPost('expiry_period') == 'limited_time' && is_numeric($this->request->getPost('number_of_month')) && $this->request->getPost('number_of_month') > 0) {
            $data['expiry_period'] = $this->request->getPost('number_of_month');
        } else {
            $data['expiry_period'] = null;
        }


        $enable_this_package = $this->request->getPost('pkg_status');
        if (isset($enable_this_package) && $enable_this_package) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }

        // upload bootcamp
        if ($_FILES['thumbnail']['name'] != '') {
            $thumbnail = $this->team_package_model->upload_files('thumbnail', 'uploads/team_training/thumbnail/');
            if ($thumbnail) {
                $data['thumbnail'] = $thumbnail;
            }
        }


        $data['features'] = $this->crud_model->trim_and_return_json($this->request->getPost('features'));
        $data['title'] = html_escape($this->request->getPost('title'));
        $data['course_id'] = html_escape($this->request->getPost('course_id'));
        $data['max_students'] = html_escape($this->request->getPost('max_students'));
        $data['price'] = html_escape($this->request->getPost('price'));

        $data['privacy'] = $this->request->getPost('privacy');

        //for instructors package creation
        if (empty($data['privacy'])) {
            $data['privacy'] = 'public';
        }

        $data['is_free_package'] = html_escape($this->request->getPost('is_free_package'));

        //$course_details = $this->crud_model->get_course_by_id($data['course_id'])->getRowArray();
		$course_ids = $this->request->getPost('course_id');

        if (!is_array($course_ids)) {
            $course_ids = [$course_ids];
        }

        $data['course_id'] = json_encode($course_ids);

        $course_details = $this->crud_model->get_course_by_id($course_ids[0])->getRowArray();

        $data['user_id'] = $course_details['creator'];
        $data['updated_date'] = time();

        //seo-fileds
        $data['meta_title'] = html_escape($this->request->getPost('meta_title'));
        $data['meta_description'] = html_escape($this->request->getPost('meta_description'));
        $data['meta_keywords'] = html_escape($this->request->getPost('meta_keywords'));
        $data['og_title'] = html_escape($this->request->getPost('og_title'));
        $data['og_description'] = html_escape($this->request->getPost('og_description'));
        $data['json_ld'] = $this->request->getPost('json_ld');


        $this->db->where('id', $team_package_id);
        $this->db->table('team_packages')->update($data);
        $this->session->setFlashdata('flash_message', get_phrase('team_package_updated_successfully'));
    }

    public function delete_team_package($team_package_id)
    {
        $this->db->where('id', $team_package_id);
        $this->db->table('team_packages')->delete();
        $this->session->setFlashdata('flash_message', get_phrase('team_package_deleted_successfully'));
    }




    public function get_status_wise_courses($status, $user_id = "")
    {
        $scorm_status = addon_status('scorm_course');
        $h5p_status = addon_status('h5p');

        $this->db->select('id, title, price, expiry_period');
        $this->db->where('status', $status);

        if ($user_id) {
            $this->db->where('creator', $user_id);
        }

        $this->db->where('course_type', 'general');
        if ($scorm_status) {
            $this->db->orWhere('course_type', 'scorm');
        }
        if ($h5p_status) {
            $this->db->orWhere('course_type', 'h5p');
        }
        $courses = $this->db->table('course')->get()->getResultArray();
        return $courses;
    }

    public function get_team_package_by_id($team_package_id = "")
    {
        return $this->db->table('team_packages')->where(array('id' => $team_package_id))->get();
    }

    public function change_team_package_status($status = "", $team_package_id = "")
    {
        if ($status == 'active') {
            if ($this->session->get('admin_login') != true) {
                redirect(site_url('login'), 'refresh');
            }
        }
        $updater = array(
            'status' => $status
        );
        $this->db->where('id', $team_package_id);
        $this->db->table('team_packages')->update($updater);
    }




    function package_purchase($method = "", $package_id = "", $amount = "", $transaction_id = "", $session_id = "")
    {
        $package_details = $this->team_package_model->get_team_package_by_id($package_id)->getRowArray();
        $data['paid_amount'] = $package_details['price'];
        $user_id = $this->session->get('user_id');
        $data['package_id'] = $package_id;
        $data['user_id'] = $user_id;
        $data['payment_method'] = $method;
        $data['payment_keys'] = json_encode(array('transaction_id' => $transaction_id, 'session_id' => $session_id));

        if (get_user_role('role_id', $package_details['user_id']) == 1) {
            $data['admin_revenue'] = $data['paid_amount'];
            $data['instructor_revenue'] = 0;
            $data['instructor_payment_status'] = 1;
        } else {
            if (get_settings('allow_instructor') == 1) {
                $instructor_revenue_percentage = get_settings('instructor_revenue');
                $data['instructor_revenue'] = ceil(($data['paid_amount'] * $instructor_revenue_percentage) / 100);
                $data['admin_revenue'] = $data['paid_amount'] - $data['instructor_revenue'];
            } else {
                $data['instructor_revenue'] = 0;
                $data['admin_revenue'] = $data['paid_amount'];
            }
            $data['instructor_payment_status'] = 0;
        }
        $data['max_students'] =  $package_details['max_students'];
        $data['purchase_date'] =  time();
        if ($package_details['expiry_period'] != null || $package_details['expiry_period'] != '') {
            $data['expiry_date'] = strtotime("+" . $package_details['expiry_period'] . "months", $data['purchase_date']);
        }
        $data['date_added'] =  time();
        $payment = $this->db->table('team_package_payment')->where(array('package_id' => $package_id, 'user_id' => $user_id))->get();
        if ($payment->getNumRows() <= 0) {
            $this->db->table('team_package_payment')->insert($data);
        }
    }



    function team_progress($package_id = "", $user_id = "")
    {
        $team_users = $this->team_members($package_id);
        $course_id = $this->get_course_by_package_id($package_id);
        $team_progress = 0;

        if (isset($user_id) && in_array($user_id, $team_users) || $this->session->get('admin_login') == true) {
            foreach ($team_users as $team_user) {
                $watch_history = $this->crud_model->get_watch_histories($team_user, $course_id)->getRowArray();
                $team_progress += isset($watch_history['course_progress']) ? $watch_history['course_progress'] : 0;
            }
            return $team_progress;
        }
    }

    function team_members($package_id)
    {
        $this->db->select('user_id');
        $this->db->where('team_package_id', $package_id);
        $result = $this->db->table('enrol')->get()->getResultArray();
        $userIds = array_column($result, 'user_id');
        return $userIds;
    }

    function get_course_by_package_id($package_id)
    {
        $course_id = $this->db->select('course_id')
            ->get_where('team_packages', array('id' => $package_id))
            ->getRow()->course_id;

        return $course_id;
    }

    function enrol_in_package($package_id, $user_id)
    {
        if (1) {
            $course_id = $this->get_course_by_package_id($package_id);
            $expiry_date = $this->check_package_expiry($package_id, 'return_value');
            $data['expiry_date'] = $expiry_date;
            $data['gifted_by'] = 0;
            $data['team_package_id'] = $package_id;

            if ($this->db->table('enrol')->where(['user_id' => $user_id, 'course_id' => $course_id])->get()->getNumRows() == 0) {
                $data['user_id'] = $user_id;
                $data['course_id'] = $course_id;
                $data['date_added'] = strtotime(date('D, d-M-Y'));
                $this->db->table('enrol')->insert($data);
            } else {
                $data['last_modified'] = time();
                $this->db->where('course_id', $course_id);
                $this->db->where('user_id', $user_id);
                $this->db->table('enrol')->update($data);
            }
        }
    }


    function is_package_creator_or_admin($package_id)
    {
        $owner_claim = $this->db->where('id', $package_id)->where('user_id', $this->session->get('user_id'))->get('team_packages')->getNumRows();
        if ($owner_claim > 0 || $this->session->get('role_id') == 1) {
            return true;
        } else {
            return false;
        }
    }

    function is_team_leader($package_id, $user_id = '')
    {
        $user_id = $user_id ? $user_id : $this->session->get('user_id');
        $this->db->where('package_id', $package_id);
        $this->db->where('user_id', $user_id);
        $package = $this->db->get('team_package_payment', $user_id)->getRowArray();
        return $package ? $package : false;
    }

    function check_package_expiry($package_id, $return_value = "")
    {
        $validity = $this->db->select('expiry_date')
            ->get_where('team_package_payment', array('package_id' => $package_id))
            ->getRow()->expiry_date;
        //return the expiry date
        if (isset($return_value) && $return_value != '') {
            return $validity;
        }
        //return whether its expired or not in true or false
        else {
            if ($validity == '' || $validity == null) {
                return true;
            } else {
                if (time() <= $validity) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }



    function check_package_max_students($package_id, $return_value = "")
    {

        $max_allowed_students = $this->db->select('max_students')
            ->get_where('team_package_payment', array('package_id' => $package_id))
            ->getRow()->max_students;

        if (isset($return_value) && $return_value != '') {

            return $max_allowed_students;
        } else {

            if ($max_allowed_students > count($this->team_members($package_id))) {

                return true;
            } else {

                return false;
            }
        }
    }



    public function user_package_purchase_history($user_id = "")
    {
        if ($user_id > 0) {
            return $this->db->table('team_package_payment')->where(array('user_id' => $user_id))->get();
        } else {
            return $this->db->table('team_package_payment')->get();
        }
    }


    public function backend_purchase_history($user_id = "")
    {
         $this->db->select('team_package_payment.*, team_packages.user_id as package_creator, team_packages.title as package_title, team_packages.course_id as package_course');
        $this->db->join('team_packages', 'team_packages.id = team_package_payment.package_id');

        if ($user_id > 0) {
            $this->db->where('team_packages.user_id', $user_id);
        }
        return $this->db->table('team_package_payment')->get()->getResultArray();
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
        if ($user_id > 0) {
            $this->db->where('id', $user_id);
        }
        return $this->db->table('users')->get();
    }

    public function add_shortcut_user($is_instructor = false)
    {
        $validity = $this->check_duplication('on_create', $this->request->getPost('email'));
        if ($validity == false) {
            $response['status'] = 0;
            $response['message'] = get_phrase('this_email_already_exits') . '. ' . get_phrase('please_use_another_email');
            return json_encode($response);
        } else {
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

    public function unlock_screen_by_password($password = "")
    {
        $password = sha1($password);
        return $this->db->table('users')->where(array('id' => $this->session->get('user_id')->get(), 'password' => $password))->getNumRows();
    }

    public function register_user($data)
    {
        $this->db->table('users')->insert($data);
        $user_id = $this->db->insertID();
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
        if (file_exists('uploads/user_image/optimized/' . $user_profile_image . '.jpg')) {
            return base_url() . 'uploads/user_image/optimized/' . $user_profile_image . '.jpg';
        } elseif (file_exists('uploads/user_image/' . $user_profile_image . '.jpg')) {
            //resizeImage
            resizeImage('uploads/user_image/' . $user_profile_image . '.jpg', 'uploads/user_image/optimized/', 220);
            return base_url() . 'uploads/user_image/' . $user_profile_image . '.jpg';
        } else {
            return base_url() . 'uploads/user_image/placeholder.png';
        }
    }

    public function get_instructor_list()
    {
        return $this->db->table('users')->where(array('status' => '1', 'is_instructor' => '1'))->get();
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

    public function update_instructor_razorpay_settings($user_id = '')
    {
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
        if ($user_id == "") {
            $user_id = $this->request->getPost('id');
        }
        $user_details = $this->get_all_user($user_id)->getRowArray();

        if ($this->request->getPost('email')) {
            $email = $this->request->getPost('email');
        } else {
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
            $this->session->setFlashdata('flash_message', site_phrase('You have successfully submitted your application.') . ' ' . get_phrase('We will review it and notify you via email notification'));
            redirect(site_url('user/become_an_instructor'), 'refresh');
        } else {
            $this->session->setFlashdata('error_message', get_phrase('user_not_found'));
            redirect(site_url('user/become_an_instructor'), 'refresh');
        }
    }

    function instructor_application()
    {
        // FIRST GET THE USER DETAILS
        $user = $this->db->table('users')->where(['email' => $this->request->getPost('email')])->get();
        if ($user->getNumRows() > 0) {
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

                $document_custom_name = random(15) . '.' . pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
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

    function quiz_submission_checker($quiz_id = "")
    {
        $quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->getRowArray();
        $total_quiz_seconds = time_to_seconds($quiz_details['duration']);

        $this->db->where('quiz_id', $quiz_id);
        $this->db->where('user_id', $this->session->get('user_id'));
        $query = $this->db->orderBy('quiz_result_id', 'desc')->get('quiz_results');
        if ($query->getNumRows() > 0) {
            $row = $query->getRowArray();
            if (($total_quiz_seconds + $row['date_added']) < time() && $total_quiz_seconds > 0 || $row['is_submitted'] == 1) {

                if ($row['is_submitted'] != 1) {
                    $this->db->where('quiz_id', $quiz_id);
                    $this->db->where('user_id', $this->session->get('user_id'));
                    $this->db->table('quiz_results')->update(array('is_submitted' => 1));
                }

                return 'submitted';
            } else {
                return 'on_progress';
            }
        } else {
            return 'no_data';
        }
    }

    // For device login tracker
    public function new_device_login_tracker($user_id = "", $is_verified = '')
    {
        $pre_sessions = array();
        $updated_session_arr = array();
        $current_session_id = session_id();
        $this->db->where('id', $user_id);
        $sessions = $this->db->table('users')->get();

        if ($sessions->getRow()->role_id == 1) {
            return;
        }

        $pre_sessions = json_decode($sessions->getRow()->sessions, true);

        if (is_array($pre_sessions) && count($pre_sessions) > 0) {
            if ($is_verified == true && !in_array($current_session_id, $pre_sessions)) {
                $allowed_device = get_settings('allowed_device_number_of_loging');
                $previous_tatal_device = count($pre_sessions) + 1; //current device

                $removeable_device = $previous_tatal_device - $allowed_device;

                foreach ($pre_sessions as $key => $pre_session) {
                    if ($removeable_device >= 1) {
                        $this->db->where('id', $pre_session);
                        $this->db->table('ci_sessions')->delete();
                    } else {

                        if ($this->db->table('ci_sessions')->where(['id' => $pre_session])->get()->getNumRows() > 0) {
                            array_push($updated_session_arr, $pre_session);
                        }
                    }
                    $removeable_device = $removeable_device - 1;
                }
                array_push($updated_session_arr, $current_session_id);
            } else {
                if (!in_array($current_session_id, $pre_sessions)) {
                    if (count($pre_sessions) >= get_settings('allowed_device_number_of_loging')) {
                        $this->email_model->new_device_login_alert($user_id);
                        redirect(site_url('login/new_login_confirmation'), 'refresh');
                    } else {
                        $updated_session_arr = $pre_sessions;
                        array_push($updated_session_arr, $current_session_id);
                    }
                }
            }
        } else {
            $updated_session_arr = [$current_session_id];
        }

        if (count($updated_session_arr) > 0) {
            $data['sessions'] = json_encode($updated_session_arr);
            $this->db->where('id', $user_id);
            $this->db->table('users')->update($data);
        }
    }

    function set_login_userdata($user_id = "")
    {
        // Checking login credential for admin
        $query = $this->db->table('users')->where(array('id' => $user_id))->get();

        if ($query->getNumRows() > 0) {
            $row = $query->getRow();
            $this->session->set('custom_session_limit', (time() + 864000));
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
                if ($this->session->get('url_history')) {
                    redirect($this->session->get('url_history'), 'refresh');
                }
                redirect(site_url('home'), 'refresh');
            }
        } else {
            $this->session->setFlashdata('error_message', get_phrase('invalid_login_credentials'));
            redirect(site_url('login'), 'refresh');
        }
    }

    function check_session_data($user_type = "")
    {
        $this->remove_garbage_collection();

        if (!$this->session->get('cart_items')) {
            $this->session->set('cart_items', array());
        }

        if (!$this->session->get('language')) {
            $this->session->set('language', get_settings('language'));
        }

        if ($user_type == 'admin') {
            if ($this->session->get('custom_session_limit') >= time()) {
                $this->session->set('custom_session_limit', (time() + 864000));
            } else {
                $this->session_destroy();
                redirect(site_url('login'), 'refresh');
            }

            if ($this->session->get('admin_login') != true) {
                redirect(site_url('login'), 'refresh');
            }
        } elseif ($user_type == 'user') {
            if ($this->session->get('custom_session_limit') >= time()) {
                $this->session->set('custom_session_limit', (time() + 864000));
            } else {
                $this->session_destroy();
                redirect(site_url('login'), 'refresh');
            }

            if ($this->session->get('user_login') != true) {
                redirect(site_url('login'), 'refresh');
            } else {
                if ($this->get_all_user($this->session->get('user_id'))->getNumRows() == 0) {
                    $this->session_destroy();
                    redirect(site_url('login'), 'refresh');
                }
            }
        } elseif ($user_type == 'login') {
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
        if ($logged_in_user_id > 0 && $this->session->get('user_login') == 1) {
            $pre_sessions = array();
            $updated_session_arr = array();
            $current_session_id = session_id();

            $this->db->where('id', $logged_in_user_id);
            $sessions = $this->db->table('users')->get()->getRow()->sessions;
            $pre_sessions = json_decode($sessions, true);
            if (is_array($pre_sessions)) {
                foreach ($pre_sessions as $key => $pre_session) {
                    if ($pre_session != $current_session_id) {
                        if ($this->db->table('ci_sessions')->where(['id' => $pre_session])->get()->getNumRows() > 0) {
                            array_push($updated_session_arr, $pre_session);
                        }
                    } else {
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

    function remove_garbage_collection()
    {
        $this->db->where('timestamp <', time() - 864000);
        $this->db->table('ci_sessions')->delete();
    }

    function get_user_by_email($email = "")
    {
        if ($email) {
            $this->db->where('email', $email);
        }
        return $this->db->table('users')->get();
    }
}


