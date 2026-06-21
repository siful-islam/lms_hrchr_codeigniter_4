<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set(get_settings('timezone'));

        $this->load->database();
        $this->load->library('session');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');

        $this->user_model->check_session_data('admin');

        ini_set('memory_limit', '128M');
    }

    public function index()
    {
        if ($this->session->get('admin_login') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }

    public function dashboard()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('dashboard');
        $this->load->view('backend/index.php', $page_data);
    }

    public function categories($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('category');

        if ($param1 == 'add') {

            $response = $this->crud_model->add_category();
            if ($response) {
                $this->session->setFlashdata('flash_message', get_phrase('data_added_successfully'));
            } else {
                $this->session->setFlashdata('error_message', get_phrase('category_name_already_exists'));
            }
            redirect(site_url('admin/categories'), 'refresh');
        } elseif ($param1 == "edit") {

            $response = $this->crud_model->edit_category($param2);
            if ($response) {
                $this->session->setFlashdata('flash_message', get_phrase('data_added_successfully'));
            } else {
                $this->session->setFlashdata('error_message', get_phrase('category_name_already_exists'));
            }
            redirect(site_url('admin/categories'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->crud_model->delete_category($param2);
            $this->session->setFlashdata('flash_message', get_phrase('data_deleted'));
            redirect(site_url('admin/categories'), 'refresh');
        } elseif ($param1 == "sub_category_image") {
            $this->crud_model->delete_subcategory_image($param2);
            $this->session->setFlashdata('flash_message', get_phrase('data_deleted'));
            redirect(site_url('admin/categories'), 'refresh');
        }

        $page_data['page_name']  = 'categories';
        $page_data['page_title'] = get_phrase('categories');
        $page_data['categories'] = $this->crud_model->get_categories($param2);
        $this->load->view('backend/index', $page_data);
    }

    public function category_form($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('category');

        if ($param1 == "add_category") {

            $page_data['page_name']  = 'category_add';
            $page_data['categories'] = $this->crud_model->get_categories()->getResultArray();
            $page_data['page_title'] = get_phrase('add_category');
        }
        if ($param1 == "edit_category") {

            $page_data['page_name']   = 'category_edit';
            $page_data['page_title']  = get_phrase('edit_category');
            $page_data['categories']  = $this->crud_model->get_categories()->getResultArray();
            $page_data['category_id'] = $param2;
        }

        $this->load->view('backend/index', $page_data);
    }

    public function sub_categories_by_category_id($category_id = 0)
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $category_id = $this->request->getPost('category_id');
        redirect(site_url("admin/sub_categories/$category_id"), 'refresh');
    }

    public function sub_category_form($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('category');

        if ($param1 == 'add_sub_category') {
            $page_data['page_name']  = 'sub_category_add';
            $page_data['page_title'] = get_phrase('add_sub_category');
        } elseif ($param1 == 'edit_sub_category') {
            $page_data['page_name']       = 'sub_category_edit';
            $page_data['page_title']      = get_phrase('edit_sub_category');
            $page_data['sub_category_id'] = $param2;
        }
        $page_data['categories'] = $this->crud_model->get_categories();
        $this->load->view('backend/index', $page_data);
    }

    public function instructors($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('instructor');

        if ($param1 == "add") {
            $this->user_model->add_user(true); // PROVIDING TRUE FOR INSTRUCTOR
            redirect(site_url('admin/instructors'), 'refresh');
        } elseif ($param1 == "edit") {
            $this->user_model->edit_user($param2);
            redirect(site_url('admin/instructors'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->user_model->delete_user($param2);
            redirect(site_url('admin/instructors'), 'refresh');
        }

        $page_data['page_name']  = 'instructors';
        $page_data['page_title'] = get_phrase('instructor');
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_form($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('instructor');

        if ($param1 == 'add_instructor_form') {
            $page_data['page_name']  = 'instructor_add';
            $page_data['page_title'] = get_phrase('instructor_add');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_instructor_form') {
            $page_data['page_name']  = 'instructor_edit';
            $page_data['user_id']    = $param2;
            $page_data['page_title'] = get_phrase('instructor_edit');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function users($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('student');

        if ($param1 == "add") {
            $this->user_model->add_user();
            redirect(site_url('admin/users'), 'refresh');
        } elseif ($param1 == "edit") {
            $this->user_model->edit_user($param2);
            redirect(site_url('admin/users'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->user_model->delete_user($param2);
            redirect(site_url('admin/users'), 'refresh');
        }

        $page_data['page_name']  = 'users';
        $page_data['page_title'] = get_phrase('student');
        $this->load->view('backend/index', $page_data);
    }

    public function server_side_users_data()
    {

        $data = [];
        //mentioned all with colum of database table that related with html table
        $columns = ['id', 'id', 'first_name', 'email', 'phone', 'id', 'id'];

        $limit = htmlspecialchars_($this->request->getPost('length'));
        $start = htmlspecialchars_($this->request->getPost('start'));

        $column_index = $columns[$this->request->getPost('order')[0]['column']];

        $dir                 = $this->request->getPost('order')[0]['dir'];
        $total_number_of_row = $this->db->where('role_id !=', 1)->get('users')->getNumRows();

        $filtered_number_of_row = $total_number_of_row;
        $search                 = $this->request->getPost('search')['value'];

        if (empty($search)) {
            $this->db->select('*');
            $this->db->limit($limit, $start);
            $this->db->orderBy($column_index, $dir);
            $this->db->where('role_id', 2);
            $students = $this->db->table('users')->get()->getResultArray();
        } else {
            $this->db->select('*');
            $this->db->like('first_name', $search);
            $this->db->orLike('last_name', $search);
            $this->db->orLike('email', $search);
            $this->db->orLike('phone', $search);
            $this->db->where('role_id', 2);
            $this->db->limit($limit, $start);
            $this->db->orderBy($column_index, $dir);
            $students = $this->db->table('users')->get()->getResultArray();

            $this->db->select('*');
            $this->db->like('first_name', $search);
            $this->db->orLike('last_name', $search);
            $this->db->orLike('email', $search);
            $this->db->orLike('phone', $search);
            $this->db->where('role_id', 2);
            $filtered_number_of_row = $this->db->table('users')->get()->getNumRows();
        }

        foreach ($students as $key => $student):

            //photo
            $photo = '<img src="' . $this->user_model->get_user_image_url($student['id']) . '" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">';

            //user name
            if ($student['status'] != 1) {
                $status = '<small><p>' . get_phrase('status') . '<span class="badge badge-danger-lighten">' . get_phrase('unverified') . '</span></p></small>';
            } else {
                $status = '';
            }
            $name = $student['first_name'] . ' ' . $student['last_name'] . $status;

            //user email
            $email = $student['email'];

            //enrolled courses
            $enrolled_courses       = $this->crud_model->enrol_history_by_user_id($student['id']);
            $enrolled_courses_title = '<ul>';
            foreach ($enrolled_courses->getResultArray() as $enrolled_course):
                $course_details = $this->crud_model->get_course_by_id($enrolled_course['course_id'])->getRowArray();
                $enrolled_courses_title .= '<li>' . $course_details['title'] . '</li>';
            endforeach;
            $enrolled_courses_title .= '</ul>';

            $action = '<div class="dropright dropright">
		                            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                                <i class="mdi mdi-dots-vertical"></i>
		                            </button>
		                            <ul class="dropdown-menu">
		                                <li><a class="dropdown-item" href="' . site_url('admin/user_form/edit_user_form/' . $student['id']) . '">' . get_phrase('edit') . '</a></li>
		                                <li><a class="dropdown-item" href="#" onclick="confirm_modal(&#39;' . site_url('admin/users/delete/' . $student['id']) . '&#39;);">' . get_phrase('delete') . '</a></li>
		                            </ul>
		                        </div>';

            $nestedData['key']              = ++$key;
            $nestedData['photo']            = $photo;
            $nestedData['name']             = $name;
            $nestedData['email']            = $email;
            $nestedData['phone']            = $student['phone'];
            $nestedData['enrolled_courses'] = $enrolled_courses_title;
            $nestedData['action']           = $action . '<script>$("a, i").tooltip();</script>';
            $data[]                         = $nestedData;
        endforeach;

        $json_data = [
            "draw"            => intval($this->request->getPost('draw')),
            "recordsTotal"    => intval($total_number_of_row),
            "recordsFiltered" => intval($filtered_number_of_row),
            "data"            => $data,
        ];
        echo json_encode($json_data);
    }

    public function server_side_instructors_data()
    {

        $data = [];
        //mentioned all with colum of database table that related with html table
        $columns = ['id', 'id', 'first_name', 'email', 'phone', 'id', 'id'];

        $limit = htmlspecialchars_($this->request->getPost('length'));
        $start = htmlspecialchars_($this->request->getPost('start'));

        $column_index = $columns[$this->request->getPost('order')[0]['column']];

        $dir                 = $this->request->getPost('order')[0]['dir'];
        $total_number_of_row = $this->db->where('is_instructor', 1)->where('role_id !=', 1)->get('users')->getNumRows();

        $filtered_number_of_row = $total_number_of_row;
        $search                 = $this->request->getPost('search')['value'];

        if (empty($search)) {
            $this->db->select('*');
            $this->db->limit($limit, $start);
            $this->db->orderBy($column_index, $dir);
            $this->db->groupStart();
            $this->db->where('role_id', 2);
            $this->db->where('is_instructor', 1);
            $this->db->groupEnd();
            $instructors = $this->db->table('users')->get()->getResultArray();
        } else {
            $this->db->select('*');
            $this->db->groupStart();
            $this->db->like('first_name', $search);
            $this->db->orLike('last_name', $search);
            $this->db->orLike('email', $search);
            $this->db->orLike('phone', $search);
            $this->db->groupEnd();
            $this->db->groupStart();
            $this->db->where('role_id', 2);
            $this->db->where('is_instructor', 1);
            $this->db->groupEnd();
            $this->db->limit($limit, $start);
            $this->db->orderBy($column_index, $dir);
            $instructors = $this->db->table('users')->get()->getResultArray();

            $this->db->select('*');
            $this->db->groupStart();
            $this->db->like('first_name', $search);
            $this->db->orLike('last_name', $search);
            $this->db->orLike('email', $search);
            $this->db->orLike('phone', $search);
            $this->db->groupEnd();
            $this->db->groupStart();
            $this->db->where('role_id', 2);
            $this->db->where('is_instructor', 1);
            $this->db->groupEnd();
            $filtered_number_of_row = $this->db->table('users')->get()->getNumRows();
        }

        foreach ($instructors as $key => $instructor):

            //photo
            $photo = '<img src="' . $this->user_model->get_user_image_url($instructor['id']) . '" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">';

            //user name
            if ($instructor['status'] != 1) {
                $status = '<small><p>' . get_phrase('status') . '<span class="badge badge-danger-lighten">' . get_phrase('unverified') . '</span></p></small>';
            } else {
                $status = '';
            }
            $name = $instructor['first_name'] . ' ' . $instructor['last_name'] . $status;

            //user email
            $email = $instructor['email'];

            //enrolled courses
            $enrolled_courses       = $this->crud_model->enrol_history_by_user_id($instructor['id']);
            $enrolled_courses_title = '<ul>';
            foreach ($enrolled_courses->getResultArray() as $enrolled_course):
                $course_details = $this->crud_model->get_course_by_id($enrolled_course['course_id'])->getRowArray();
                $enrolled_courses_title .= '<li>' . $course_details['title'] . '</li>';
            endforeach;
            $enrolled_courses_title .= '</ul>';

            $action = '<div class="dropright dropright">
		                            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                                <i class="mdi mdi-dots-vertical"></i>
		                            </button>
		                            <ul class="dropdown-menu">
		                                <li><a class="dropdown-item" href="' . site_url('admin/courses?category_id=all&status=all&instructor_id=' . $instructor['id'] . '&price=all') . '">' . get_phrase('view_courses') . '</a></li>
		                                <li><a class="dropdown-item" href="' . site_url('admin/instructor_form/edit_instructor_form/' . $instructor['id']) . '">' . get_phrase('edit') . '</a></li>
		                                <li><a class="dropdown-item" href="#" onclick="confirm_modal(&#39;' . site_url('admin/instructors/delete/' . $instructor['id']) . '&#39;);">' . get_phrase('delete') . '</a></li>
		                            </ul>
		                        </div>';

            $nestedData['key']              = ++$key;
            $nestedData['photo']            = $photo;
            $nestedData['name']             = $name;
            $nestedData['email']            = $email;
            $nestedData['phone']            = $instructor['phone'];
            $nestedData['enrolled_courses'] = $enrolled_courses_title;
            $nestedData['action']           = $action . '<script>$("a, i").tooltip();</script>';
            $data[]                         = $nestedData;
        endforeach;

        $json_data = [
            "draw"            => intval($this->request->getPost('draw')),
            "recordsTotal"    => intval($total_number_of_row),
            "recordsFiltered" => intval($filtered_number_of_row),
            "data"            => $data,
        ];
        echo json_encode($json_data);
    }

    public function add_shortcut_student()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('student');

        $is_instructor = 0;
        echo $this->user_model->add_shortcut_user($is_instructor);
    }

    public function user_form($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('student');

        if ($param1 == 'add_user_form') {
            $page_data['page_name']  = 'user_add';
            $page_data['page_title'] = get_phrase('student_add');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_user_form') {
            $page_data['page_name']  = 'user_edit';
            $page_data['user_id']    = $param2;
            $page_data['page_title'] = get_phrase('student_edit');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function enrol_history($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        if ($param1 != "") {
            $date_range                   = $this->request->getGet('date_range');
            $date_range                   = explode(" - ", $date_range);
            $page_data['timestamp_start'] = strtotime($date_range[0]);
            $page_data['timestamp_end']   = strtotime($date_range[1]);
        } else {
            $first_day_of_month           = "1 " . date("M") . " " . date("Y") . ' 00:00:00';
            $last_day_of_month            = date("t") . " " . date("M") . " " . date("Y") . ' 23:59:59';
            $page_data['timestamp_start'] = strtotime($first_day_of_month);
            $page_data['timestamp_end']   = strtotime($last_day_of_month);
        }
        $page_data['page_name']     = 'enrol_history';
        $page_data['enrol_history'] = $this->crud_model->enrol_history_by_date_range($page_data['timestamp_start'], $page_data['timestamp_end']);
        $page_data['page_title']    = get_phrase('enrol_history');
        $this->load->view('backend/index', $page_data);
    }

    public function enrol_student($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        if ($param1 == 'enrol') {
            $this->crud_model->enrol_a_student_manually();
            redirect(site_url('admin/enrol_history'), 'refresh');
        }
        $page_data['page_name']  = 'enrol_student';
        $page_data['page_title'] = get_phrase('course_enrolment');
        $this->load->view('backend/index', $page_data);
    }

    public function shortcut_enrol_student()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        echo $this->crud_model->shortcut_enrol_a_student_manually();
    }

    public function admin_revenue($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('revenue');

        if ($param1 != "") {
            $date_range                   = $this->request->getGet('date_range');
            $date_range                   = explode(" - ", $date_range);
            $page_data['timestamp_start'] = strtotime($date_range[0] . ' 00:00:00');
            $page_data['timestamp_end']   = strtotime($date_range[1] . ' 23:59:59');
        } else {
            $page_data['timestamp_start'] = strtotime(date("m/01/Y 00:00:00"));
            $page_data['timestamp_end']   = strtotime(date("m/t/Y 23:59:59"));
        }

        $page_data['page_name']       = 'admin_revenue';
        $page_data['payment_history'] = $this->crud_model->get_revenue_by_user_type($page_data['timestamp_start'], $page_data['timestamp_end'], 'admin_revenue');
        $page_data['page_title']      = get_phrase('admin_revenue');

        $this->load->view('backend/index', $page_data);
    }

    public function instructor_revenue($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('revenue');

        $page_data['page_name']       = 'instructor_revenue';
        $page_data['payment_history'] = $this->crud_model->get_revenue_by_user_type("", "", 'instructor_revenue');
        $page_data['page_title']      = get_phrase('instructor_revenue');
        $this->load->view('backend/index', $page_data);
    }

    public function invoice($payout_id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name']  = 'invoice';
        $page_data['payout_id']  = $payout_id;
        $page_data['page_title'] = get_phrase('invoice');
        $this->load->view('backend/index', $page_data);
    }

    public function enrol_history_delete($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        $this->crud_model->delete_enrol_history($param1);
        $this->session->setFlashdata('flash_message', get_phrase('data_deleted_successfully'));
        redirect(site_url('admin/enrol_history'), 'refresh');
    }

    public function purchase_history()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name']        = 'purchase_history';
        $page_data['purchase_history'] = $this->crud_model->purchase_history();
        $page_data['page_title']       = get_phrase('purchase_history');
        $this->load->view('backend/index', $page_data);
    }

    public function system_settings($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'system_update') {
            $this->crud_model->update_system_settings();
            $this->session->setFlashdata('flash_message', get_phrase('system_settings_updated'));
            redirect(site_url('admin/system_settings'), 'refresh');
        }

        if ($param1 == 'logo_upload') {
            move_uploaded_file($_FILES['logo']['tmp_name'], 'assets/backend/logo.png');
            $this->session->setFlashdata('flash_message', get_phrase('backend_logo_updated'));
            redirect(site_url('admin/system_settings'), 'refresh');
        }

        if ($param1 == 'favicon_upload') {
            move_uploaded_file($_FILES['favicon']['tmp_name'], 'assets/favicon.png');
            $this->session->setFlashdata('flash_message', get_phrase('favicon_updated'));
            redirect(site_url('admin/system_settings'), 'refresh');
        }

        $page_data['languages']  = $this->crud_model->get_all_languages();
        $page_data['page_name']  = 'system_settings';
        $page_data['page_title'] = get_phrase('system_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function frontend_settings($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'frontend_update') {
            $this->crud_model->update_frontend_settings();
            $this->session->setFlashdata('flash_message', get_phrase('frontend_settings_updated'));
            redirect(site_url('admin/frontend_settings?tab=frontendsettings'), 'refresh');
        }

        if ($param1 == 'recaptcha_update') {
            $this->crud_model->update_recaptcha_settings();
            $this->session->setFlashdata('flash_message', get_phrase('recaptcha_settings_updated'));
            redirect(site_url('admin/frontend_settings?tab=recaptcha'), 'refresh');
        }

        if ($param1 == 'banner_image_update') {
            $this->crud_model->update_frontend_banner();
            $this->session->setFlashdata('flash_message', get_phrase('banner_image_update'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }
        if ($param1 == 'light_logo') {
            $this->crud_model->update_light_logo();
            $this->session->setFlashdata('flash_message', get_phrase('logo_updated'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }
        if ($param1 == 'dark_logo') {
            $this->crud_model->update_dark_logo();
            $this->session->setFlashdata('flash_message', get_phrase('logo_updated'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }
        if ($param1 == 'small_logo') {
            $this->crud_model->update_small_logo();
            $this->session->setFlashdata('flash_message', get_phrase('logo_updated'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }
        if ($param1 == 'favicon') {
            $this->crud_model->update_favicon();
            $this->session->setFlashdata('flash_message', get_phrase('favicon_updated'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }

        if ($param1 == 'motivational_speech') {
            $this->crud_model->update_motivational_speech();
            $this->session->setFlashdata('flash_message', get_phrase('Motivational speech updated successfully'));
            redirect(site_url('admin/home_page_builder?tab=pre-built-home-settings'), 'refresh');
        }

        if ($param1 == 'website_faq') {
            $this->crud_model->update_website_faq();
            $this->session->setFlashdata('flash_message', get_phrase('Website FAQS updated successfully'));
            redirect(site_url('admin/frontend_settings?tab=websitefaqs'), 'refresh');
        }

        if ($param1 == 'contact_info') {
            $this->crud_model->update_contact_info();
            $this->session->setFlashdata('flash_message', get_phrase('Contact information updated successfully'));
            redirect(site_url('admin/frontend_settings?tab=contact_information'), 'refresh');
        }

        if ($param1 == 'custom_codes') {
            $this->crud_model->update_custom_codes();
            $this->session->setFlashdata('flash_message', get_phrase('Your custom codes updated successfully'));
            redirect(site_url('admin/frontend_settings?tab=custom_codes'), 'refresh');
        }

        if ($param1 == 'home_page_settings') {
            echo $this->crud_model->update_home_page_settings($param2);
            return;
        }

        if ($param1 == 'water_mark') {
            $this->crud_model->update_water_mark();
            $this->session->setFlashdata('flash_message', get_phrase('video water marks updated successfully'));
            redirect(site_url('admin/frontend_settings?tab=water_mark'), 'refresh');
        }

        if ($param1 == 'review_store') {
            $this->crud_model->review_store();
            $this->session->setFlashdata('flash_message', get_phrase('Review  Added successfully'));
            redirect(site_url('admin/frontend_settings?tab=review'), 'refresh');
        }

        if ($param1 == 'review_update') {
            $this->crud_model->review_update();
            $this->session->setFlashdata('flash_message', get_phrase('Review  Update successfully'));
            redirect(site_url('admin/frontend_settings?tab=review'), 'refresh');
        }

        $page_data['page_name']  = 'frontend_settings';
        $page_data['page_title'] = get_phrase('frontend_settings');
        $this->load->view('backend/index', $page_data);
    }
    public function payment_settings($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'system_currency') {
            $this->crud_model->update_system_currency();
            redirect(site_url('admin/payment_settings'), 'refresh');
        }

        if (isset($_POST['identifier'])) {
            $this->crud_model->update_payment_settings();
            redirect(site_url('admin/payment_settings'), 'refresh');
        }

        $page_data['payment_gateways'] = $this->crud_model->get_payment_gateways()->getResultArray();
        $page_data['page_name']        = 'payment_settings';
        $page_data['page_title']       = get_phrase('payment_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function notification_settings($param1 = "", $param2 = "", $param3 = "")
    {
        if ($param1 == 'smtp_settings') {
            $this->crud_model->update_smtp_settings();
            $this->session->setFlashdata('flash_message', get_phrase('smtp_settings_updated_successfully'));
            redirect(site_url('admin/notification_settings'), 'refresh');
        }

        if ($param1 == 'notification_enable_diable') {
            echo $this->crud_model->notification_enable_diable();
            return;
        }

        if (isset($_GET['tab'])) {
            $page_data['tab'] = $_GET['tab'];
        } else {
            $page_data['tab'] = 'smtp-settings';
        }

        $page_data['page_name']  = 'notification_settings';
        $page_data['page_title'] = get_phrase('Notification settings');
        $this->load->view('backend/index', $page_data);
    }

    public function edit_email_template($id = "", $param2 = "")
    {

        if ($param2 == 'update') {
            $data['subject']  = json_encode($this->request->getPost('subject'));
            $data['template'] = json_encode($this->request->getPost('template'));
            $this->db->where('id', $id)->update('notification_settings', $data);
            $this->session->setFlashdata('flash_message', get_phrase('Email template updated successfully'));
            redirect(site_url('admin/notification_settings?tab=email-template'), 'refresh');
        }
        $page_data['notification'] = $this->db->where('id', $id)->get('notification_settings')->getRowArray();
        $this->load->view('backend/admin/edit_email_template', $page_data);
    }

    public function social_login_settings($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'update') {
            $this->crud_model->update_social_login_settings();
            $this->session->setFlashdata('flash_message', get_phrase('social_login_settings_updated_successfully'));
            redirect(site_url('admin/social_login_settings'), 'refresh');
        }

        $page_data['page_name']  = 'social_login';
        $page_data['page_title'] = get_phrase('social_login');
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_settings($param1 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('instructor');

        if ($param1 == 'update') {
            $this->crud_model->update_instructor_settings();
            $this->session->setFlashdata('flash_message', get_phrase('instructor_settings_updated'));
            redirect(site_url('admin/instructor_settings'), 'refresh');
        }

        $page_data['page_name']  = 'instructor_settings';
        $page_data['page_title'] = get_phrase('instructor_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function theme_settings($action = '')
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('theme');

        $page_data['page_name']  = 'theme_settings';
        $page_data['page_title'] = get_phrase('theme_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function theme_actions($action = "", $theme = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('theme');

        if ($action == 'activate') {
            $theme_to_active  = $this->request->getPost('theme');
            $installed_themes = $this->crud_model->get_installed_themes();
            if (in_array($theme_to_active, $installed_themes)) {
                $this->crud_model->activate_theme($theme_to_active);
                echo true;
            } else {
                echo false;
            }
        } elseif ($action == 'remove') {
            if ($theme == get_frontend_settings('theme')) {
                $this->session->setFlashdata('error_message', get_phrase('activate_a_theme_first'));
            } else {
                $this->crud_model->remove_files_and_folders(APPPATH . '/views/frontend/' . $theme);
                $this->crud_model->remove_files_and_folders(FCPATH . '/assets/frontend/' . $theme);
                $this->session->setFlashdata('flash_message', $theme . ' ' . get_phrase('theme_removed_successfully'));
            }
            redirect(site_url('admin/theme_settings'), 'refresh');
        }
    }

    public function courses()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        $page_data['selected_category_id']   = isset($_GET['category_id']) ? $_GET['category_id'] : "all";
        $page_data['selected_instructor_id'] = isset($_GET['instructor_id']) ? $_GET['instructor_id'] : "all";
        $page_data['selected_price']         = isset($_GET['price']) ? $_GET['price'] : "all";
        $page_data['selected_status']        = isset($_GET['status']) ? $_GET['status'] : "all";

        $page_data['page_name']  = 'courses-server-side';
        $page_data['categories'] = $this->crud_model->get_categories();
        $page_data['page_title'] = get_phrase('active_courses');
        $this->load->view('backend/index', $page_data);
    }
	
	public function export_student_progress_csv($course_id = null)
	{
		if(!$course_id){
			show_error('Course ID is required');
			return;
		}

		$company_name = $this->request->getGet('company_name');
		$progress_status = $this->request->getGet('progress_status');

		$this->db->select('enrol.*, users.first_name, users.last_name, users.email, users.company_name, watch_histories.course_progress, watch_histories.completed_date, watch_histories.date_updated, watch_histories.completed_lesson, watch_histories.downloaded_number');
		$this->db->from('enrol');
		$this->db->join('users', 'users.id = enrol.user_id');
		$this->db->join('watch_histories', 'watch_histories.student_id = enrol.user_id AND watch_histories.course_id = enrol.course_id', 'left');
		$this->db->where('enrol.course_id', $course_id);

		if(!empty($company_name)){
			$this->db->like('users.company_name', $company_name);
		}

		if(!empty($progress_status) && $progress_status != 'all'){
			if($progress_status == 'not_started'){
				$this->db->where('(watch_histories.course_progress IS NULL OR watch_histories.course_progress = 0)');
			} elseif($progress_status == 'in_progress'){
				$this->db->where('watch_histories.course_progress >', 0);
				$this->db->where('watch_histories.course_progress <', 100);
			} elseif($progress_status == 'completed'){
				$this->db->where('watch_histories.course_progress', 100);
			}
		}

		$enrolments = $this->db->get()->getResultArray();

		if(empty($enrolments)){
			$this->session->setFlashdata('error_message', 'No data found for this course.');
			redirect($_SERVER['HTTP_REFERER']);
		}

		$csv_data = [];
		$csv_data[] = ['SL','Student','Email','Company Name','Enrolled From','Last Seen','Completed On','Progress','Completed Lessons','Watched Duration','Certificate Downloads'];

		$lessons = $this->crud_model->get_lessons('course', $course_id);
		$total_lesson = $lessons->getNumRows();
		$sl = 1;

		foreach($enrolments as $enrolment){
			$course_progress = $enrolment['course_progress'] ?? 0;
			$completed_lesson_arr = json_decode($enrolment['completed_lesson'] ?? '[]', true);
			$completed_lesson = is_array($completed_lesson_arr) ? count($completed_lesson_arr) : 0;

			$date_updated = !empty($enrolment['date_updated']) ? date('d M Y, H:i a', $enrolment['date_updated']) : 'Not started yet';
			$completed_date = !empty($enrolment['completed_date']) ? date('d M Y', $enrolment['completed_date']) : 'Not completed yet';

			$total_watched_duration = 0;
			$watched_durations = $this->db->table('watched_duration')->where(['watched_student_id' => $enrolment['user_id'], 'watched_course_id' => $course_id])->get();
			foreach($watched_durations->getResultArray() as $watched_duration){
				$total_watched_duration += count(json_decode($watched_duration['watched_counter'], true)) * 5;
			}

			$csv_data[] = [
				$sl++,
				$enrolment['first_name'].' '.$enrolment['last_name'],
				$enrolment['email'],
				$enrolment['company_name'],
				!empty($enrolment['date_added']) ? date('d M Y', $enrolment['date_added']) : '',
				$date_updated,
				$completed_date,
				$course_progress.'%',
				$completed_lesson.'/'.$total_lesson,
				gmdate('H:i:s', $total_watched_duration),
				$enrolment['downloaded_number'] ?? 0
			];
		}

		// Convert array to CSV manually
		$filename = 'student_progress_course_'.$course_id.'.csv';
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		$output = fopen('php://output', 'w');

		foreach($csv_data as $row){
			fputcsv($output, $row);
		}

		fclose($output);
		exit;
	}
    // This function is responsible for loading the course data from server side for datatable SILENTLY
    public function get_courses()
    {
        $data = [];
        //mentioned all with colum of database table that related with html table
        $columns = ['id', 'title', 'sub_category_id', 'section', 'id', 'status', 'price', 'id'];

        // Filter portion
        $category_id   = $this->request->getPost('selected_category_id');
        $instructor_id = $this->request->getPost('selected_instructor_id');
        $price         = $this->request->getPost('selected_price');
        $status        = $this->request->getPost('selected_status');

        $limit = htmlspecialchars_($this->request->getPost('length'));
        $start = htmlspecialchars_($this->request->getPost('start'));

        $column_index = $columns[$this->request->getPost('order')[0]['column']];

        $dir = $this->request->getPost('order')[0]['dir'];

        $total_number_of_row = $this->crud_model->get_courses()->getNumRows();
        $search              = $this->request->getPost('search')['value'];

        //FILTERED DATA
        $this->db->select('*');
        if (! empty($search)) {
            $this->db->groupStart();
            $this->db->like('title', $search);
            $this->db->orLike('status', $search);
            $this->db->orLike('price', $search);
            $this->db->orLike('discounted_price', $search);
            $this->db->groupEnd();
        }
        if (! empty($category_id) && $category_id != 'all') {
            $this->db->where('sub_category_id', $category_id);
        }
        if (! empty($instructor_id) && $instructor_id != 'all') {
            $this->db->where('creator', $instructor_id);
        }
        if (! empty($price) && $price != 'all') {
            if ($price == 'free') {
                $this->db->where('is_free_course', 1);
            } elseif ($price == 'paid') {
                $this->db->where('is_free_course', null);
            }
        }

        if (! empty($status) && $status != 'all') {
            $this->db->groupStart();
            $this->db->where('status', $status);
            $this->db->groupEnd();
        }

        $this->db->limit($limit, $start);
        $this->db->orderBy($column_index, $dir);
        $courses = $this->db->table('course')->get()->getResultArray();

        //WITHOUT FILTERED DATA
        $this->db->select('*');
        if (! empty($search)) {
            $this->db->groupStart();
            $this->db->like('title', $search);
            $this->db->orLike('status', $search);
            $this->db->orLike('price', $search);
            $this->db->orLike('discounted_price', $search);
            $this->db->groupEnd();
        }
        if (! empty($category_id) && $category_id != 'all') {
            $this->db->where('sub_category_id', $category_id);
        }
        if (! empty($instructor_id) && $instructor_id != 'all') {
            $this->db->where('creator', $instructor_id);
        }
        if (! empty($price) && $price != 'all') {
            if ($price == 'free') {
                $this->db->where('is_free_course', 1);
            } elseif ($price == 'paid') {
                $this->db->where('is_free_course', null);
            }
        }
        if (! empty($status) && $status != 'all') {
            $this->db->groupStart();
            $this->db->where('status', $status);
            $this->db->groupEnd();
        }
        $filtered_number_of_row = $this->db->table('course')->get()->getNumRows();

        // Fetch the data and make it as JSON format and return it.
        if (! empty($courses)) {
            foreach ($courses as $key => $row) {
                $instructor_details = $this->user_model->get_all_user($row['creator'])->getRowArray();
                $category_details   = $this->crud_model->get_category_details_by_id($row['sub_category_id'])->getRowArray();
                $sections           = $this->crud_model->get_section('course', $row['id']);
                $lessons            = $this->crud_model->get_lessons('course', $row['id']);
                $enroll_history     = $this->crud_model->enrol_history($row['id']);

                $status_badge = "badge-success-lighten";
                if ($row['status'] == 'pending') {
                    $status_badge = "badge-danger-lighten";
                } elseif ($row['status'] == 'draft') {
                    $status_badge = "badge-dark-lighten";
                } elseif ($row['status'] == 'private') {
                    $status_badge = "badge-dark";
                } elseif ($row['status'] == 'upcoming') {
                    $status_badge = "badge-warning-lighten";
                }

                $price_badge = "badge-dark-lighten";
                $price       = 0;
                if ($row['is_free_course'] == null) {
                    if ($row['discount_flag'] == 1) {
                        $price = currency($row['discounted_price']);
                    } else {
                        $price = currency($row['price']);
                    }
                } elseif ($row['is_free_course'] == 1) {
                    $price_badge = "badge-success-lighten";
                    $price       = get_phrase('free');
                }

                $price_field = '<span class="badge ' . $price_badge . '">' . $price . '</span>';
                if ($row['expiry_period'] > 0) {
                    $price_field .= '<p class="text-12">' . '( ' . $row['expiry_period'] . ' ' . get_phrase('Months') . ' )' . '</p>';
                } else {
                    $price_field .= '<p class="text-12">' . '( ' . get_phrase('Lifetime') . ' )' . '</p>';
                }

                $view_course_on_frontend_url = site_url('home/course/' . rawurlencode(slugify($row['title'])) . '/' . $row['id']);
                $go_to_course_playing_page   = site_url('home/lesson/' . rawurlencode(slugify($row['title'])) . '/' . $row['id']);
                $edit_this_course_url        = site_url('admin/course_form/course_edit/' . $row['id']);
                $duplicate_this_course_url   = site_url('admin/course_form/course_duplicate/' . $row['id']);
                $section_and_lesson_url      = site_url('admin/course_form/course_edit/' . $row['id']);
                $academic_progress_url       = site_url('admin/course_form/course_edit/' . $row['id'] . '?tab=academic_progress');

                if ($row['status'] == 'active') {
                    $course_status_changing_message = get_phrase('mark_as_pending');
                    if ($row['user_id'] != $this->session->get('user_id')) {
                        $course_status_changing_action = "showAjaxModal('" . site_url('modal/popup/mail_on_course_status_changing_modal/pending/' . $row['id'] . '/' . $category_id . '/' . $instructor_id . '/all/' . $status) . "', '" . $course_status_changing_message . "')";
                    } else {
                        $course_status_changing_action = "confirm_modal('" . site_url('admin/change_course_status_for_admin/pending/' . $row['id'] . '/' . $category_id . '/' . $instructor_id . '/all/' . $status) . "')";
                    }
                } else {
                    $course_status_changing_message = get_phrase('mark_as_active');
                    if ($row['user_id'] != $this->session->get('user_id')) {
                        $course_status_changing_action = "showAjaxModal('" . site_url('modal/popup/mail_on_course_status_changing_modal/active/' . $row['id'] . '/' . $category_id . '/' . $instructor_id . '/all/' . $status) . "', '" . $course_status_changing_message . "')";
                    } else {
                        $course_status_changing_action = "confirm_modal('" . site_url('admin/change_course_status_for_admin/active/' . $row['id'] . '/' . $category_id . '/' . $instructor_id . '/all/' . $status) . "')";
                    }
                }

                $delete_course_url = "confirm_modal('" . site_url('admin/course_actions/delete/' . $row['id']) . "')";

                if ($row['course_type'] == 'general') {
                    $section_and_lesson_menu = '<li><a class="dropdown-item" href="' . $section_and_lesson_url . '">' . get_phrase("section_and_lesson") . '</a></li>';
                } else {
                    $section_and_lesson_menu = "";
                }

                $course_academic_progress_menu = '<li><a class="dropdown-item" href="' . $academic_progress_url . '">' . get_phrase("Academic progress") . '</a></li>';

                $course_edit_menu = '<li><a class="dropdown-item" href="' . $edit_this_course_url . '">' . get_phrase("edit_this_course") . '</a></li>';

                $course_duplicate_menu = '<li><a class="dropdown-item" href="' . $duplicate_this_course_url . '">' . get_phrase("duplicate_this_course") . '</a></li>';

                $course_delete_menu = '<li><a class="dropdown-item" href="javascript:;" onclick="' . $delete_course_url . '">' . get_phrase("delete") . '</a></li>';

                $action = '
                <div class="dropright dropright">
                <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="' . $view_course_on_frontend_url . '" target="_blank">' . get_phrase("view_course_on_frontend") . '</a></li>
                <li><a class="dropdown-item" href="' . $go_to_course_playing_page . '" target="_blank">' . get_phrase("go_to_course_playing_page") . '</a></li>
                ' . $course_academic_progress_menu . $course_edit_menu . $course_duplicate_menu . $section_and_lesson_menu . '
                <li><a class="dropdown-item" href="javascript:;" onclick="' . $course_status_changing_action . '">' . $course_status_changing_message . '</a></li>
                ' . $course_delete_menu . '
                </ul>
                </div>
                ';

                $nestedData['#'] = $key + 1;

                $instructor_names = "";
                foreach ($this->crud_model->get_course_instructors_id($row['id']) as $instructor_id) {
                    $multi_instructor = $this->user_model->get_all_user($instructor_id)->getRowArray();
                    $instructor_names = $multi_instructor['first_name'] . ' ' . $multi_instructor['last_name'];
                }

                $nestedData['title'] = '<strong><a href="' . site_url('admin/course_form/course_edit/' . $row['id']) . '">' . $row['title'] . '</a></strong><br>
                <small class="text-muted">' . get_phrase('instructor') . ': <b>' . $instructor_names . '</b></small>';

                $nestedData['category'] = '<span class="badge badge-dark-lighten">' . $category_details['name'] . '</span>';

                if ($row['course_type'] == 'scorm') {
                    $nestedData['lesson_and_section'] = '<span class="badge badge-info-lighten">' . get_phrase('scorm_course') . '</span>';
                } elseif ($row['course_type'] == 'h5p') {
                    $nestedData['lesson_and_section'] = '<span class="badge badge-info-lighten">' . get_phrase('h5p_course') . '</span>';
                } elseif ($row['course_type'] == 'general') {
                    $nestedData['lesson_and_section'] = '
                    <small class="text-muted"><b>' . get_phrase('Section') . '</b>: ' . $sections->getNumRows() . '</small><br>
                    <small class="text-muted"><b>' . get_phrase('Lesson') . '</b>: ' . $lessons->getNumRows() . '</small>';
                }

                $nestedData['enrolled_student'] = '<small class="text-muted"><b>' . get_phrase('Enrollments') . '</b>: ' . $enroll_history->getNumRows() . '</small>';

                $nestedData['status'] = '<span class="badge ' . $status_badge . '">' . get_phrase($row['status']) . '</span>';

                $nestedData['price'] = $price_field;

                $nestedData['actions'] = $action;

                $nestedData['course_id'] = $row['id'];

                $data[] = $nestedData;
            }
        }

        $json_data = [
            "draw"            => intval($this->request->getPost('draw')),
            "recordsTotal"    => intval($total_number_of_row),
            "recordsFiltered" => intval($filtered_number_of_row),
            "data"            => $data,
        ];

        echo json_encode($json_data);
    }

    public function pending_courses()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        $page_data['page_name']  = 'pending_courses';
        $page_data['page_title'] = get_phrase('pending_courses');
        $this->load->view('backend/index', $page_data);
    }

    public function course_actions($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($param1 == "add") {
            $course_id = $this->crud_model->add_course();
            redirect(site_url('admin/course_form/course_edit/' . $course_id), 'refresh');
        } elseif ($param1 == 'add_shortcut') {
            echo $this->crud_model->add_shortcut_course();
        } elseif ($param1 == "edit") {

            $this->crud_model->update_course($param2); 

            // CHECK IF LIVE CLASS ADDON EXISTS, ADD OR UPDATE IT TO ADDON MODEL
            if (addon_status('live-class')) {
                $this->load->model('addons/Liveclass_model', 'liveclass_model');
                $this->liveclass_model->update_live_class($param2);
            }

            // CHECK IF JITSI LIVE CLASS ADDON EXISTS, ADD OR UPDATE IT TO ADDON MODEL
            if (addon_status('jitsi-live-class')) {
                $this->load->model('addons/jitsi_liveclass_model', 'jitsi_liveclass_model');
                $this->jitsi_liveclass_model->update_live_class($param2);
            }

            redirect(site_url('admin/course_form/course_edit/' . $param2));
        } elseif ($param1 == 'delete') {

            $this->is_drafted_course($param2);
            $this->crud_model->delete_course($param2);
            redirect(site_url('admin/courses'), 'refresh');
        }
    }

    public function course_form($param1 = "", $param2 = "")
    {

        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($param1 == 'add_course') {

            $page_data['languages']  = $this->crud_model->get_all_languages();
            $page_data['categories'] = $this->crud_model->get_categories();
            $page_data['page_name']  = 'course_add';
            $page_data['page_title'] = get_phrase('add_course');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'add_course_shortcut') {
            $page_data['languages']  = $this->crud_model->get_all_languages();
            $page_data['categories'] = $this->crud_model->get_categories();
            $this->load->view('backend/admin/course_add_shortcut', $page_data);
        } elseif ($param1 == 'course_edit') {

            $this->is_drafted_course($param2);
            $page_data['page_name']  = 'course_edit';
            $page_data['course_id']  = $param2;
            $page_data['page_title'] = get_phrase('edit_course');
            $page_data['languages']  = $this->crud_model->get_all_languages();
            $page_data['categories'] = $this->crud_model->get_categories();
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'course_duplicate') {
            $this->duplicate_course($param2);
            $this->session->setFlashdata('flash_message', get_phrase('Course Duplicate Successfully'));
            redirect(site_url('admin/courses'), 'refresh');
        }
    }

    public function duplicate_course($id)
    {
        $course        = $this->db->where('id', $id)->get('course')->getRowArray();
        $max_course_id = $this->db->select_max('id')->get('course')->getRowArray();
        $course['id']  = $max_course_id['id'] + 1;
        $this->db->table('course')->insert($course);
    }

    private function is_drafted_course($course_id)
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $course_details = $this->crud_model->get_course_by_id($course_id)->getRowArray();
        if ($course_details['status'] == 'draft') {
            $this->session->setFlashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_course'));
            redirect(site_url('admin/courses'), 'refresh');
        }
    }

    public function change_course_status($updated_status = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $course_id     = $this->request->getPost('course_id');
        $category_id   = $this->request->getPost('category_id');
        $instructor_id = $this->request->getPost('instructor_id');
        $price         = $this->request->getPost('price');
        $status        = $this->request->getPost('status');
        if (isset($_POST['mail_subject']) && isset($_POST['mail_body'])) {
            $mail_subject = $this->request->getPost('mail_subject');
            $mail_body    = $this->request->getPost('mail_body');
            $this->email_model->send_mail_on_course_status_changing($course_id, $mail_subject, $mail_body);
        }
        $this->crud_model->change_course_status($updated_status, $course_id);
        $this->session->setFlashdata('flash_message', get_phrase('course_status_updated'));
        //redirect(site_url('admin/courses?category_id=' . $category_id . '&status=' . $status . '&instructor_id=' . $instructor_id . '&price=' . $price), 'refresh');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function change_course_status_for_admin($updated_status = "", $course_id = "", $category_id = "", $status = "", $instructor_id = "", $price = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->crud_model->change_course_status($updated_status, $course_id);
        $this->session->setFlashdata('flash_message', get_phrase('course_status_updated'));
        redirect(site_url('admin/courses?category_id=' . $category_id . '&status=' . $status . '&instructor_id=' . $instructor_id . '&price=' . $price), 'refresh');
    }

    public function sections($param1 = "", $param2 = "", $param3 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($param2 == 'add') {
            $this->crud_model->add_section($param1);
            $this->session->setFlashdata('flash_message', get_phrase('section_has_been_added_successfully'));
        } elseif ($param2 == 'edit') {
            $this->crud_model->edit_section($param3);
            $this->session->setFlashdata('flash_message', get_phrase('section_has_been_updated_successfully'));
        } elseif ($param2 == 'delete') {
            $this->crud_model->delete_section($param1, $param3);
            $this->session->setFlashdata('flash_message', get_phrase('section_has_been_deleted_successfully'));
        }
        redirect(site_url('admin/course_form/course_edit/' . $param1));
    }

    public function lessons($course_id = "", $param1 = "", $param2 = "")
    {
        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($param1 == 'add') {
            $response = $this->crud_model->add_lesson();
            echo $response;
            return;
        } elseif ($param1 == 'edit') {
            $response = $this->crud_model->edit_lesson($param2);
            echo $response;
            return;
        } elseif ($param1 == 'delete') {
            $this->crud_model->delete_lesson($param2);
            $this->session->setFlashdata('flash_message', get_phrase('lesson_has_been_deleted_successfully'));
            redirect('admin/course_form/course_edit/' . $course_id);
        } elseif ($param1 == 'filter') {
            redirect('admin/lessons/' . $this->request->getPost('course_id'));
        }
        $page_data['page_name']  = 'lessons';
        $page_data['lessons']    = $this->crud_model->get_lessons('course', $course_id);
        $page_data['course_id']  = $course_id;
        $page_data['page_title'] = get_phrase('lessons');
        $this->load->view('backend/index', $page_data);
    }

    public function watch_video($slugified_title = "", $lesson_id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $lesson_details          = $this->crud_model->get_lessons('lesson', $lesson_id)->getRowArray();
        $page_data['provider']   = $lesson_details['video_type'];
        $page_data['video_url']  = $lesson_details['video_url'];
        $page_data['lesson_id']  = $lesson_id;
        $page_data['page_name']  = 'video_player';
        $page_data['page_title'] = get_phrase('video_player');
        $this->load->view('backend/index', $page_data);
    }

    // Language Functions
    public function manage_language($param1 = '', $param2 = '', $param3 = '')
    {

        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'add_language') {
            $language = strtolower(trimmer($this->request->getPost('language')));
            if ($language == 'n-a') {
                $this->session->setFlashdata('error_message', get_phrase('language_name_can_not_be_empty_or_can_not_have_special_characters'));
                redirect(site_url('admin/manage_language'), 'refresh');
            }

            if (! $this->db->field_exists($language, 'language')) {
                $this->load->dbforge();
                $fields = [
                    $language => [
                        'type'      => 'TEXT',
                        'default'   => null,
                        'null'      => true,
                        'collation' => 'utf8_unicode_ci',
                    ],
                ];
                $this->dbforge->add_column('language', $fields);
            }

            saveDefaultJSONFile($language);
            $this->session->setFlashdata('flash_message', get_phrase('language_added_successfully'));
            redirect(site_url('admin/manage_language'), 'refresh');
        }
        if ($param1 == 'add_phrase') {
            $new_phrase = get_phrase($this->request->getPost('phrase'));
            $this->session->setFlashdata('flash_message', $new_phrase . ' ' . get_phrase('has_been_added_successfully'));
            redirect(site_url('admin/manage_language'), 'refresh');
        }

        if ($param1 == 'edit_phrase') {
            $page_data['edit_profile'] = $param2;
        }

        if ($param1 == 'delete_language') {
            if (file_exists('application/language/' . $param2 . '.json')) {
                unlink('application/language/' . $param2 . '.json');
                $this->session->setFlashdata('flash_message', get_phrase('language_deleted_successfully'));
                redirect(site_url('admin/manage_language'), 'refresh');
            }
        }
        $page_data['languages']  = $this->crud_model->get_all_languages();
        $page_data['page_name']  = 'manage_language';
        $page_data['page_title'] = get_phrase('multi_language_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function update_phrase_with_ajax()
    {
        $current_editing_language = $this->request->getPost('currentEditingLanguage');
        $updatedValue             = $this->request->getPost('updatedValue');
        $key                      = $this->request->getPost('key');
        saveJSONFile($current_editing_language, $key, $updatedValue);
        echo $current_editing_language . ' ' . $key . ' ' . $updatedValue;
    }

    public function message($param1 = 'message_home', $param2 = '', $param3 = '')
    {
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('messaging');

        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->setFlashdata('flash_message', get_phrase('message_sent'));
            redirect(site_url('admin/message/message_read/' . $message_thread_code), 'refresh');
        }

        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
            $this->session->setFlashdata('flash_message', get_phrase('message_sent'));
            redirect(site_url('admin/message/message_read/' . $param2), 'refresh');
        }

        if ($param1 == 'message_read') {
            $page_data['current_message_thread_code'] = $param2; // $param2 = message_thread_code
            $this->crud_model->mark_thread_messages_read($param2);
        }

        $page_data['message_inner_page_name'] = $param1;
        $page_data['page_name']               = 'message';
        $page_data['page_title']              = get_phrase('private_messaging');
        $this->load->view('backend/index', $page_data);
    }

    /******MANAGE OWN PROFILE AND CHANGE PASSWORD***/
    public function manage_profile($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'update_profile_info') {
            $this->user_model->edit_user($param2);
            redirect(site_url('admin/manage_profile'), 'refresh');
        }
        if ($param1 == 'change_password') {
            $this->user_model->change_password($param2);
            redirect(site_url('admin/manage_profile'), 'refresh');
        }
        $page_data['page_name']  = 'manage_profile';
        $page_data['page_title'] = get_phrase('manage_profile');
        $page_data['edit_data']  = $this->db->table('users')->where([
            'id' => $this->session->get('user_id')->get(),
        ])->getResultArray();
        $this->load->view('backend/index', $page_data);
    }

    public function paypal_checkout_for_instructor_revenue()
    {
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['amount_to_pay']        = $this->request->getPost('amount_to_pay');
        $page_data['payout_id']            = $this->request->getPost('payout_id');
        $page_data['instructor_name']      = $this->request->getPost('instructor_name');
        $page_data['production_client_id'] = $this->request->getPost('production_client_id');

        // BEFORE, CHECK PAYOUT AMOUNTS ARE VALID
        $payout_details = $this->crud_model->get_payouts($page_data['payout_id'], 'payout')->getRowArray();
        if ($payout_details['amount'] == $page_data['amount_to_pay'] && $payout_details['status'] == 0) {
            $this->load->view('backend/admin/paypal_checkout_for_instructor_revenue', $page_data);
        } else {
            $this->session->setFlashdata('error_message', get_phrase('invalid_payout_data'));
            redirect(site_url('admin/instructor_payout'), 'refresh');
        }
    }

    // PAYPAL CHECKOUT ACTIONS
    public function paypal_payment($payout_id = "", $paypalPaymentID = "", $paypalPaymentToken = "", $paypalPayerID = "")
    {
        $payout_details  = $this->crud_model->get_payouts($payout_id, 'payout')->getRowArray();
        $instructor_id   = $payout_details['user_id'];
        $instructor_data = $this->db->table('users')->where(['id' => $instructor_id])->get()->getRowArray();

        $payment_keys          = json_decode($instructor_data['payment_keys'], true);
        $paypal_keys           = $payment_keys['paypal'];
        $production_client_id  = $paypal_keys['production_client_id'];
        $production_secret_key = $paypal_keys['production_secret_key'];

        //THIS IS HOW I CHECKED THE PAYPAL PAYMENT STATUS
        $status = $this->payment_model->paypal_payment($paypalPaymentID, $paypalPaymentToken, $paypalPayerID, $production_client_id, $production_secret_key);
        if (! $status) {
            $this->session->setFlashdata('error_message', get_phrase('an_error_occurred_during_payment'));
            redirect(site_url('admin/instructor_payout'), 'refresh');
        }
        $this->crud_model->update_payout_status($payout_id, 'paypal');
        $this->session->setFlashdata('flash_message', get_phrase('payout_updated_successfully'));
        redirect(site_url('admin/instructor_payout'), 'refresh');
    }

    public function stripe_checkout_for_instructor_revenue($payout_id)
    {
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // BEFORE, CHECK PAYOUT AMOUNTS ARE VALID
        $payout_details = $this->crud_model->get_payouts($payout_id, 'payout')->getRowArray();
        if ($payout_details['amount'] > 0 && $payout_details['status'] == 0) {
            $page_data['user_details']  = $this->user_model->get_user($payout_details['user_id'])->getRowArray();
            $page_data['amount_to_pay'] = $payout_details['amount'];
            $page_data['payout_id']     = $payout_details['id'];
            $this->load->view('backend/admin/stripe_checkout_for_instructor_revenue', $page_data);
        } else {
            $this->session->setFlashdata('error_message', get_phrase('invalid_payout_data'));
            redirect(site_url('admin/instructor_payout'), 'refresh');
        }
    }

    // STRIPE CHECKOUT ACTIONS
    public function stripe_payment($payout_id = "", $session_id = "")
    {
        $payout_details = $this->crud_model->get_payouts($payout_id, 'payout')->getRowArray();
        $instructor_id  = $payout_details['user_id'];
        //THIS IS HOW I CHECKED THE STRIPE PAYMENT STATUS
        $response = $this->payment_model->stripe_payment($instructor_id, $session_id, true);

        if ($response['payment_status'] === 'succeeded') {
            $this->crud_model->update_payout_status($payout_id, 'stripe');
            $this->session->setFlashdata('flash_message', get_phrase('payout_updated_successfully'));
        } else {
            $this->session->setFlashdata('error_message', $response['status_msg']);
        }

        redirect(site_url('admin/instructor_payout'), 'refresh');
    }

    public function razorpay_checkout_for_instructor_revenue($user_id = "", $payout_id = "", $param1 = "", $razorpay_order_id = "", $payment_id = "", $amount = "", $signature = "")
    {
        if ($param1 == 'paid') {
            $status = $this->payment_model->razorpay_payment($razorpay_order_id, $payment_id, $amount, $signature);
            if ($status == true) {
                $this->crud_model->update_payout_status($payout_id, 'razorpay');
                $this->session->setFlashdata('flash_message', get_phrase('payout_updated_successfully'));
            } else {
                $this->session->setFlashdata('error_message', $response['status_msg']);
            }

            redirect(site_url('admin/instructor_payout'), 'refresh');
        }

        $page_data['payout_id']     = $payout_id;
        $page_data['user_details']  = $this->user_model->get_user($user_id)->getRowArray();
        $page_data['amount_to_pay'] = $this->request->getPost('total_price_of_checking_out');
        $this->load->view('backend/admin/razorpay_checkout', $page_data);
    }

    public function preview($course_id = '')
    {
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        $this->is_drafted_course($course_id);
        if ($course_id > 0) {
            $courses = $this->crud_model->get_course_by_id($course_id);
            if ($courses->getNumRows() > 0) {
                $course_details = $courses->getRowArray();
                redirect(site_url('home/lesson/' . rawurlencode(slugify($course_details['title'])) . '/' . $course_details['id']), 'refresh');
            }
        }
        redirect(site_url('admin/courses'), 'refresh');
    }

    // Manage Quizes
    public function quizes($course_id = "", $action = "", $quiz_id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($action == 'add') {
            $this->crud_model->add_quiz($course_id);
            $this->session->setFlashdata('flash_message', get_phrase('quiz_has_been_added_successfully'));
        } elseif ($action == 'edit') {
            $this->crud_model->edit_quiz($quiz_id);
            $this->session->setFlashdata('flash_message', get_phrase('quiz_has_been_updated_successfully'));
        } elseif ($action == 'delete') {
            $this->crud_model->delete_section($course_id, $quiz_id);
            $this->session->setFlashdata('flash_message', get_phrase('quiz_has_been_deleted_successfully'));
        }
        redirect(site_url('admin/course_form/course_edit/' . $course_id));
    }

    // Manage Quize Questions
    public function quiz_questions($quiz_id = "", $action = "", $question_id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->getRowArray();

        if ($action == 'add' || $action == 'edit') {
            echo $this->crud_model->manage_quiz_questions($quiz_id, $question_id, $action);
        } elseif ($action == 'delete') {
            $response = $this->crud_model->delete_quiz_question($question_id);
            $this->session->setFlashdata('flash_message', get_phrase('question_has_been_deleted'));
            redirect(site_url('admin/course_form/course_edit/' . $quiz_details['course_id']), 'refresh');
        }
    }

    // software about page
    public function about()
    {
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['application_details'] = $this->crud_model->get_application_details();
        $page_data['page_name']           = 'about';
        $page_data['page_title']          = get_phrase('about');
        $this->load->view('backend/index', $page_data);
    }

    public function install_theme($theme_to_install = '')
    {

        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('theme');

        $uninstalled_themes = $this->crud_model->get_uninstalled_themes();
        if (! in_array($theme_to_install, $uninstalled_themes)) {
            $this->session->setFlashdata('error_message', get_phrase('this_theme_is_not_available'));
            redirect(site_url('admin/theme_settings'));
        }

        if (! class_exists('ZipArchive')) {
            $this->session->setFlashdata('error_message', get_phrase('your_server_is_unable_to_extract_the_zip_file') . '. ' . get_phrase('please_enable_the_zip_extension_on_your_server') . ', ' . get_phrase('then_try_again'));
            redirect(site_url('admin/theme_settings'));
        }

        $zipped_file_name   = $theme_to_install;
        $unzipped_file_name = substr($zipped_file_name, 0, -4);
        // Create update directory.
        $views_directory  = 'application/views/frontend';
        $assets_directory = 'assets/frontend';

        //Unzip theme zip file and remove zip file.
        $theme_path   = 'themes/' . $zipped_file_name;
        $theme_zip    = new ZipArchive;
        $theme_result = $theme_zip->open($theme_path);
        if ($theme_result === true) {
            $theme_zip->extractTo('themes');
            $theme_zip->close();
        }

        // unzip the views zip file to the application>views folder
        $views_path   = 'themes/' . $unzipped_file_name . '/views/' . $zipped_file_name;
        $views_zip    = new ZipArchive;
        $views_result = $views_zip->open($views_path);
        if ($views_result === true) {
            $views_zip->extractTo($views_directory);
            $views_zip->close();
        }

        // unzip the assets zip file to the assets/frontend folder
        $assets_path   = 'themes/' . $unzipped_file_name . '/assets/' . $zipped_file_name;
        $assets_zip    = new ZipArchive;
        $assets_result = $assets_zip->open($assets_path);
        if ($assets_result === true) {
            $assets_zip->extractTo($assets_directory);
            $assets_zip->close();
        }

        unlink($theme_path);
        $this->crud_model->remove_files_and_folders('themes/' . $unzipped_file_name);
        $this->session->setFlashdata('flash_message', get_phrase('theme_imported_successfully'));
        redirect(site_url('admin/theme_settings'));
    }

    public function available_addon()
    {
        $collectionId   = '8226729';
        $personal_token = "FkA9UyDiQT0YiKwYLK3ghyFNRVV9SeUn";

        //setting the header for the rest of the api
        $bearer = 'bearer ' . $personal_token;

        $header   = [];
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json; charset=utf-8';
        $header[] = 'Authorization: ' . $bearer;

        $verify_url = 'https://api.envato.com/v3/market/catalog/collection';
        $ch_verify  = curl_init($verify_url . '?id=' . $collectionId);

        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch_verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_verify, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $cinit_verify_data = curl_exec($ch_verify);
        curl_close($ch_verify);

        // collection, items, pagination
        $response_data = json_decode($cinit_verify_data, true);

        $page_data['items']        = $response_data['items'];
        $page_data['collectionId'] = $collectionId;

        $page_data['page_name']  = 'available_addons';
        $page_data['page_title'] = get_phrase('available_addons');
        $this->load->view('backend/index', $page_data);
    }

    //ADDON MANAGER PORTION STARTS HERE
    public function addon($param1 = "", $param2 = "", $param3 = "")
    {
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('addon');

        // ADD NEW ADDON FORM
        if ($param1 == 'add') {

            // CHECK ACCESS PERMISSION
            check_permission('addon');
            $page_data['page_name']  = 'addon_add';
            $page_data['page_title'] = get_phrase('add_addon');
        }

        if ($param1 == 'update') {
            // CHECK ACCESS PERMISSION
            check_permission('addon');

            $page_data['page_name']  = 'addon_update';
            $page_data['page_title'] = get_phrase('add_update');
        }

        // INSTALLING AN ADDON
        if ($param1 == 'install' || $param1 == 'version_update') {
            // CHECK ACCESS PERMISSION
            check_permission('addon');

            $this->addon_model->install_addon($param1);
        }

        // ACTIVATING AN ADDON
        if ($param1 == 'activate') {

            $update_message = $this->addon_model->addon_activate($param2);
            $this->session->setFlashdata('flash_message', get_phrase($update_message));
            redirect(site_url('admin/addon'), 'refresh');
        }

        // DEACTIVATING AN ADDON
        if ($param1 == 'deactivate') {
            $update_message = $this->addon_model->addon_deactivate($param2);
            $this->session->setFlashdata('flash_message', get_phrase($update_message));
            redirect(site_url('admin/addon'), 'refresh');
        }

        // REMOVING AN ADDON
        if ($param1 == 'delete') {
            $this->addon_model->addon_delete($param2);
            $this->session->setFlashdata('flash_message', get_phrase('addon_is_deleted_successfully'));
            redirect(site_url('admin/addon'), 'refresh');
        }

        // SHOWING LIST OF INSTALLED ADDONS
        if (empty($param1)) {
            $page_data['page_name']  = 'addons';
            $page_data['addons']     = $this->addon_model->addon_list()->getResultArray();
            $page_data['page_title'] = get_phrase('addon_manager');
        }
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_application($param1 = "", $param2 = "")
    { // param1 is the status and param2 is the application id
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('instructor');

        if ($param1 == 'approve' || $param1 == 'delete') {
            $this->user_model->update_status_of_application($param1, $param2);
        }
        $page_data['page_name']             = 'application_list';
        $page_data['page_title']            = get_phrase('instructor_application');
        $page_data['approved_applications'] = $this->user_model->get_approved_applications();
        $page_data['pending_applications']  = $this->user_model->get_pending_applications();
        $this->load->view('backend/index', $page_data);
    }

    // INSTRUCTOR PAYOUT SECTION
    public function instructor_payout($param1 = "")
    {
        if ($this->session->get('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('instructor');

        if ($param1 != "") {
            $date_range                   = $this->request->getGet('date_range');
            $date_range                   = explode(" - ", $date_range);
            $page_data['timestamp_start'] = strtotime($date_range[0]);
            $page_data['timestamp_end']   = strtotime($date_range[1]);
        } else {
            $page_data['timestamp_start'] = strtotime(date('m/01/Y'));
            $page_data['timestamp_end']   = strtotime(date('m/t/Y'));
        }

        $page_data['page_name']         = 'instructor_payout';
        $page_data['page_title']        = get_phrase('instructor_payout');
        $page_data['completed_payouts'] = $this->crud_model->get_completed_payouts_by_date_range($page_data['timestamp_start'], $page_data['timestamp_end']);
        $page_data['pending_payouts']   = $this->crud_model->get_pending_payouts();
        $this->load->view('backend/index', $page_data);
    }

    // ADMINS SECTION STARTS
    public function admins($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('admin');

        if ($param1 == "add") {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $this->user_model->add_user(false, true); // PROVIDING TRUE FOR INSTRUCTOR
            redirect(site_url('admin/admins'), 'refresh');
        } elseif ($param1 == "edit") {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $this->user_model->edit_user($param2);
            redirect(site_url('admin/admins'), 'refresh');
        } elseif ($param1 == "delete") {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $this->user_model->delete_user($param2);
            redirect(site_url('admin/admins'), 'refresh');
        }

        $page_data['page_name']  = 'admins';
        $page_data['page_title'] = get_phrase('admins');
        $page_data['admins']     = $this->user_model->get_admins()->getResultArray();
        $this->load->view('backend/index', $page_data);
    }

    public function admin_form($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($param1 == 'add_admin_form') {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $page_data['page_name']  = 'admin_add';
            $page_data['page_title'] = get_phrase('admin_add');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_admin_form') {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $page_data['page_name']  = 'admin_edit';
            $page_data['user_id']    = $param2;
            $page_data['page_title'] = get_phrase('admin_edit');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function permissions()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        // CHECK ACCESS PERMISSION
        check_permission('admin');

        if (! isset($_GET['permission_assing_to']) || empty($_GET['permission_assing_to'])) {
            $this->session->setFlashdata('error_message', get_phrase('you_have_select_an_admin_first'));
            redirect(site_url('admin/admins'), 'refresh');
        }

        $page_data['permission_assing_to'] = $this->request->getGet('permission_assing_to');
        $user_details                      = $this->user_model->get_all_user($page_data['permission_assing_to']);
        if ($user_details->getNumRows() == 0) {
            $this->session->setFlashdata('error_message', get_phrase('invalid_admin'));
            redirect(site_url('admin/admins'), 'refresh');
        } else {
            $user_details = $user_details->getRowArray();
            if ($user_details['role_id'] != 1) {
                $this->session->setFlashdata('error_message', get_phrase('invalid_admin'));
                redirect(site_url('admin/admins'), 'refresh');
            }
            if (is_root_admin($user_details['id'])) {
                $this->session->setFlashdata('error_message', get_phrase('you_can_not_set_permission_to_the_root_admin'));
                redirect(site_url('admin/admins'), 'refresh');
            }
        }

        $page_data['permission_assign_to'] = $user_details;
        $page_data['page_name']            = 'admin_permission';
        $page_data['page_title']           = get_phrase('assign_permission');
        $this->load->view('backend/index', $page_data);
    }

    // ASSIGN PERMISSION TO ADMIN
    public function assign_permission()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('admin');

        echo $this->user_model->assign_permission();
    }

    // REMOVING INSTRUCTOR FROM COURSE
    public function remove_an_instructor($course_id, $instructor_id)
    {
        // CHECK ACCESS PERMISSION
        check_permission('course');

        $course_details = $this->crud_model->get_course_by_id($course_id)->getRowArray();

        if ($course_details['creator'] == $instructor_id) {
            $this->session->setFlashdata('error_message', get_phrase('course_creator_can_be_removed'));
            redirect('admin/course_form/course_edit/' . $course_id);
        }

        if ($course_details['multi_instructor']) {
            $instructor_ids = explode(',', $course_details['user_id']);

            if (in_array($instructor_id, $instructor_ids)) {
                if (count($instructor_ids) > 1) {
                    if (($key = array_search($instructor_id, $instructor_ids)) !== false) {
                        unset($instructor_ids[$key]);

                        $data['user_id'] = implode(",", $instructor_ids);
                        $this->db->where('id', $course_id);
                        $this->db->table('course')->update($data);

                        $this->session->setFlashdata('flash_message', get_phrase('instructor_has_been_removed'));
                        if ($this->session->get('user_id') == $instructor_id) {
                            redirect('admin/courses/');
                        } else {
                            redirect('admin/course_form/course_edit/' . $course_id);
                        }
                    }
                } else {
                    $this->session->setFlashdata('error_message', get_phrase('a_course_should_have_at_least_one_instructor'));
                    redirect('admin/course_form/course_edit/' . $course_id);
                }
            } else {
                $this->session->setFlashdata('error_message', get_phrase('invalid_instructor_id'));
                redirect('admin/course_form/course_edit/' . $course_id);
            }
        } else {
            $this->session->setFlashdata('error_message', get_phrase('a_course_should_have_at_least_one_instructor'));
            redirect('admin/course_form/course_edit/' . $course_id);
        }
    }

    /** Coupons functionality starts */
    public function coupons($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('coupon');

        if ($param1 == "add") {
            // CHECK ACCESS PERMISSION
            check_permission('coupon');

            $response = $this->crud_model->add_coupon(); // PROVIDING TRUE FOR INSTRUCTOR
            $response ? $this->session->setFlashdata('flash_message', get_phrase('coupon_added_successfully')) : $this->session->setFlashdata('error_message', get_phrase('coupon_code_already_exists'));
            redirect(site_url('admin/coupons'), 'refresh');
        } elseif ($param1 == "edit") {
            // CHECK ACCESS PERMISSION
            check_permission('coupon');

            $response = $this->crud_model->edit_coupon($param2);
            $response ? $this->session->setFlashdata('flash_message', get_phrase('coupon_updated_successfully')) : $this->session->setFlashdata('error_message', get_phrase('coupon_code_already_exists'));
            redirect(site_url('admin/coupons'), 'refresh');
        } elseif ($param1 == "delete") {
            // CHECK ACCESS PERMISSION
            check_permission('coupon');

            $response = $this->crud_model->delete_coupon($param2);
            $response ? $this->session->setFlashdata('flash_message', get_phrase('coupon_deleted_successfully')) : $this->session->setFlashdata('error_message', get_phrase('coupon_code_already_exists'));
            redirect(site_url('admin/coupons'), 'refresh');
        }

        $page_data['page_name']  = 'coupons';
        $page_data['page_title'] = get_phrase('coupons');
        $page_data['coupons']    = $this->crud_model->get_coupons()->getResultArray();
        $this->load->view('backend/index', $page_data);
    }

    public function coupon_form($param1 = "", $param2 = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('coupon');

        if ($param1 == 'add_coupon_form') {

            $page_data['page_name']  = 'coupon_add';
            $page_data['page_title'] = get_phrase('add_coupons');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_coupon_form') {

            $page_data['page_name']  = 'coupon_edit';
            $page_data['coupon']     = $this->crud_model->get_coupons($param2)->getRowArray();
            $page_data['page_title'] = get_phrase('coupon_edit');
            $this->load->view('backend/index', $page_data);
        }
    }
    // ADMINS SECTION ENDS

    // AJAX PORTION
    // this function is responsible for managing multiple choice question
    public function quiz_fields_type_wize()
    {
        $page_data['question_type'] = $this->request->getPost('question_type');
        $this->load->view('backend/admin/quiz_fields_type_wize', $page_data);
    }

    public function ajax_get_sub_category($category_id)
    {
        $page_data['sub_categories'] = $this->crud_model->get_sub_categories($category_id);

        return $this->load->view('backend/admin/ajax_get_sub_category', $page_data);
    }

    public function ajax_get_section($course_id)
    {
        $page_data['sections'] = $this->crud_model->get_section('course', $course_id)->getResultArray();
        return $this->load->view('backend/admin/ajax_get_section', $page_data);
    }

    public function ajax_get_video_details()
    {
        $video_details = $this->video_model->getVideoDetails($_POST['video_url']);
        if (is_array($video_details)) {
            echo $video_details['duration'];
        }
    }
    public function ajax_sort_section()
    {
        $section_json = $this->request->getPost('itemJSON');
        $this->crud_model->sort_section($section_json);
    }
    public function ajax_sort_lesson()
    {
        $lesson_json = $this->request->getPost('itemJSON');
        $this->crud_model->sort_lesson($lesson_json);
    }
    public function ajax_sort_question()
    {
        $question_json = $this->request->getPost('itemJSON');
        $this->crud_model->sort_question($question_json);
    }

    //Start blog
    public function add_blog_category()
    {
        $this->load->view('backend/admin/blog_category_add');
    }

    public function edit_blog_category($blog_category_id = "")
    {
        $data['blog_category'] = $this->crud_model->get_blog_categories($blog_category_id)->getRowArray();
        $this->load->view('backend/admin/blog_category_edit', $data);
    }

    public function blog_category($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            $response = $this->crud_model->add_blog_category();
            if ($response == true) {
                $this->session->setFlashdata('flash_message', get_phrase('blog_category_added_successfully'));
            } else {
                $this->session->setFlashdata('error_message', get_phrase('there_is_already_a_blog_with_this_name'));
            }
            redirect(site_url('admin/blog_category'), 'refresh');
        } elseif ($param1 == 'update') {
            $response = $this->crud_model->update_blog_category($param2);
            if ($response == true) {
                $this->session->setFlashdata('flash_message', get_phrase('blog_category_updated_successfully'));
            } else {
                $this->session->setFlashdata('error_message', get_phrase('there_is_already_a_blog_with_this_name'));
            }
            redirect(site_url('admin/blog_category'), 'refresh');
        } elseif ($param1 == 'delete') {
            $this->crud_model->delete_blog_category($param2);
            $this->session->setFlashdata('flash_message', get_phrase('blog_category_deleted_successfully'));
            redirect(site_url('admin/blog_category'), 'refresh');
        }
        $page_data['categories'] = $this->crud_model->get_blog_categories();
        $page_data['page_title'] = get_phrase('blog_category');
        $page_data['page_name']  = 'blog_category';
        $this->load->view('backend/index', $page_data);
    }

    public function add_blog()
    {
	$page_data['blog_categories'] = $this->crud_model->get_blog_categories();
        $page_data['page_title'] = get_phrase('add_blog');
        $page_data['page_name']  = 'blog_add';
        $this->load->view('backend/index', $page_data);
    }

    public function edit_blog($blog_id = "")
    {
        $page_data['blog']       = $this->crud_model->get_blogs($blog_id)->getRowArray();
        $page_data['page_title'] = get_phrase('edit_blog');
        $page_data['page_name']  = 'blog_edit';
        $this->load->view('backend/index', $page_data);
    }

    public function blog($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            $this->crud_model->add_blog();
            $this->session->setFlashdata('flash_message', get_phrase('blog_added_successfully'));
            redirect(site_url('admin/blog'), 'refresh');
        } elseif ($param1 == 'update') {
            $this->crud_model->update_blog($param2);
            $this->session->setFlashdata('flash_message', get_phrase('blog_updated_successfully'));
            redirect(site_url('admin/blog'), 'refresh');
        } elseif ($param1 == 'status') {
            $this->crud_model->update_blog_status($param2);
            $this->session->setFlashdata('flash_message', get_phrase('blog_status_has_been_updated'));
            redirect(site_url('admin/blog'), 'refresh');
        } elseif ($param1 == 'delete') {
            $this->crud_model->blog_delete($param2);
            $this->session->setFlashdata('flash_message', get_phrase('blog_deleted_successfully'));
            redirect(site_url('admin/blog'), 'refresh');
        }
        $page_data['blogs']      = $this->crud_model->get_blogs();
        $page_data['page_title'] = get_phrase('blog');
        $page_data['page_name']  = 'blog';
        $this->load->view('backend/index', $page_data);
    }

    public function instructors_pending_blog($param1 = "", $param2 = "")
    {
        if ($param1 == 'approval_request') {
            $this->crud_model->approve_blog($param2);
            $this->session->setFlashdata('flash_message', get_phrase('the_blog_has_been_approved'));
            redirect(site_url('admin/instructors_pending_blog'), 'refresh');
        } elseif ($param1 == 'delete') {
            $this->crud_model->blog_delete($param2);
            $this->session->setFlashdata('flash_message', get_phrase('blog_deleted_successfully'));
            redirect(site_url('admin/instructors_pending_blog'), 'refresh');
        }
        $page_data['pending_blogs'] = $this->crud_model->get_instructors_pending_blog();
        $page_data['page_title']    = get_phrase('instructors_pending_blog');
        $page_data['page_name']     = 'instructors_pending_blog';
        $this->load->view('backend/index', $page_data);
    }

    public function blog_settings($param1 = "")
    {
        if ($param1 == 'update') {
            $this->crud_model->update_blog_settings();
            $this->session->setFlashdata('flash_message', get_phrase('blog_settings_updated_successfully'));
            redirect(site_url('admin/blog_settings'), 'refresh');
        }
        $page_data['page_title'] = get_phrase('blog_settings');
        $page_data['page_name']  = 'blog_settings';
        $this->load->view('backend/index', $page_data);
    }
    //End blog

    //Don't remove this code for security reasons
    public function save_valid_purchase_code($param1 = "")
    {
        if ($param1 == 'update') {
            $data['value'] = htmlspecialchars_($this->request->getPost('purchase_code'));
            $status        = $this->crud_model->curl_request($data['value']);
            if ($status) {
                $this->db->where('key', 'purchase_code');
                $this->db->table('settings')->update($data);
                $this->session->setFlashdata('flash_message', get_phrase('purchase_code_has_been_updated'));
                echo 1;
            } else {
                echo 0;
            }
        } else {
            $this->load->view('backend/admin/save_purchase_code_form');
        }
    }

    public function drip_content_settings($param1 = "")
    {
        if ($param1 == 'update') {
            $this->crud_model->save_drip_content_settings();
            $this->session->setFlashdata('flash_message', get_phrase('drip_content_settings_updated_successfully'));
            redirect(site_url('admin/drip_content_settings'), 'refresh');
        }
        $page_data['drip_content_settings'] = json_decode(get_settings('drip_content_settings'), true);
        $page_data['page_title']            = get_phrase('drip_content_settings');
        $page_data['page_name']             = 'drip_content_settings';
        $this->load->view('backend/index', $page_data);
    }

    public function custom_page($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            $this->crud_model->add_custom_page();
            $this->session->setFlashdata('flash_message', get_phrase('new_page_added_successfully'));
            redirect(site_url('admin/custom_page'), 'refresh');
        }

        if ($param1 == 'update') {
            $this->crud_model->update_custom_page($param2);
            $this->session->setFlashdata('flash_message', get_phrase('page_updated_successfully'));
            redirect(site_url('admin/custom_page'), 'refresh');
        }

        if ($param1 == 'delete') {
            $this->crud_model->delete_custom_page($param2);
            $this->session->setFlashdata('flash_message', get_phrase('page_deleted_successfully'));
            redirect(site_url('admin/custom_page'), 'refresh');
        }

        $page_data['custom_pages'] = $this->crud_model->get_custom_pages();
        $page_data['page_title']   = get_phrase('custom_pages');
        $page_data['page_name']    = 'custom_page';
        $this->load->view('backend/index', $page_data);
    }

    public function add_custom_page($custom_page_id = "")
    {
        $page_data['page_title'] = get_phrase('add_custom_page');
        $page_data['page_name']  = 'add_custom_page';
        $this->load->view('backend/index', $page_data);
    }

    public function edit_custom_page($custom_page_id = "")
    {
        $page_data['custom_page'] = $this->crud_model->get_custom_pages($custom_page_id)->getRowArray();
        $page_data['page_title']  = get_phrase('edit_custom_page');
        $page_data['page_name']   = 'edit_custom_page';
        $this->load->view('backend/index', $page_data);
    }

    //Start Academy Cloud coding
    public function academy_cloud($param1 = "")
    {
        if ($param1 == 'update') {
            $this->academy_cloud_model->save_access_token();
            $this->session->setFlashdata('flash_message', get_phrase('access_token_saved_successfully'));
            redirect(site_url('admin/academy_cloud'), 'refresh');
        }

        $page_data['subscription_details'] = $this->academy_cloud_model->get_subscription_details();
        $page_data['cloud_videos']         = $this->academy_cloud_model->get_cloud_videos();
        $page_data['page_title']           = get_phrase('academy_cloud');
        $page_data['page_name']            = 'academy_cloud';
        $this->load->view('backend/index', $page_data);
    }
    //End of Academy Cloud coding

    //Start data center
    //Select 2 server-side user data
    public function get_select2_user_data($default = "")
	{
		$response = [];

		if (isset($_GET['searchVal'])) {
			$result = $this->db->where('role_id !=', 1)
				->groupStart()
					->like('first_name', $_GET['searchVal'])
					->orLike('last_name', $_GET['searchVal'])
					->orLike('email', $_GET['searchVal'])
				->groupEnd()
				->limit(100)
				->get('users')
				->getResultArray();

			if ($default != '') {
				$response[] = ['id' => $default, 'text' => get_phrase($default)];
			}

			foreach ($result as $row) {
				$response[] = [
					'id' => $row['id'],
					'text' => "{$row['first_name']} {$row['last_name']} ({$row['email']})"
				];
			}
										   
																																   
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response))
			->_display();
		exit; // <– stop CI from loading any view
	}
    //Select 2 server-side user data
    public function get_select2_instructor_data($default = "")
    {
        $response = [];
		if(isset($_GET['searchVal'])){	   
			$result   = $this->db->where('is_instructor', 1)->groupStart()->like('first_name', $_GET['searchVal'])->orLike('last_name', $_GET['searchVal'])->orLike('email', $_GET['searchVal'])->groupEnd()->limit(100)->get('users')->getResultArray();
			if ($default != '') {
				$response[] = [['id' => $default, 'text' => get_phrase($default)]];
			}
			foreach ($result as $key => $row) {
				$response[] = ['id' => $row['id'], 'text' => $row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['email'] . ')'];
			}
		}							
        echo json_encode($response);
    }

    //Select 2 server-side enrollable data
    public function get_select2_course_for_enroll($default = "")
    {
        $response = [];
        $result   = $this->db->groupStart()->where('status', 'active')->orWhere('status', 'private')->groupEnd()->groupStart()->like('title', $_GET['searchVal'])->orLike('description', $_GET['searchVal'])->groupEnd()->limit(100)->get('course')->getResultArray();
        if ($default != '') {
            $response[] = [['id' => $default, 'text' => get_phrase($default)]];
        }
        foreach ($result as $key => $row) {
            $user       = $this->user_model->get_all_user($row['creator'])->getRowArray();
            $response[] = ['id' => $row['id'], 'text' => $row['title'] . ' (' . get_phrase('Creator') . ': ' . $user['first_name'] . ' ' . $user['last_name'] . ')'];
        }
        echo json_encode($response);
    }

    //Select 2 server-side general data
    public function get_select2_general_course($default = "")
    {
        $response = [];
        $result   = $this->db->where('course_type', 'general')->groupStart()->like('title', $_GET['searchVal'])->orLike('description', $_GET['searchVal'])->groupEnd()->limit(100)->get('course')->getResultArray();
        if ($default != '') {
            $response[] = [['id' => $default, 'text' => get_phrase($default)]];
        }
        foreach ($result as $key => $row) {
            $user       = $this->user_model->get_all_user($row['creator'])->getRowArray();
            $response[] = ['id' => $row['id'], 'text' => $row['title'] . ' (' . get_phrase('Creator') . ': ' . $user['first_name'] . ' ' . $user['last_name'] . ')'];
        }
        echo json_encode($response);
    }

    public function instructor_payment($instructor_id = "")
    {
        $this->payment_model->configure_instructor_payment($instructor_id);
        redirect(site_url('payment'));
    }

    public function open_ai_settings($param1 = "")
    {
        if ($param1 == "update") {
            $this->load->model('addons/ai_model');
            $this->ai_model->update_open_ai_settings();
        }
        $page_data['page_title'] = get_phrase('openai_settings');
        $page_data['page_name']  = 'open_ai_settings';
        $this->load->view('backend/index', $page_data);
    }

    public function ai_img_download()
    {
        $this->load->model('addons/ai_model');
        $this->ai_model->ai_img_download();
    }

    public function chat_gpt()
    {
        if (isset($_POST['service_type']) && ! empty($_POST['service_type'])) {
            $this->load->model('addons/ai_model');
            echo $this->ai_model->chat_gpt();
        } else {
            $this->load->view('backend/admin/chat_gpt');
        }
    }

    public function gpt_assistant()
    {
        $this->load->model('addons/ai_model');
        echo $this->ai_model->gpt_assistant();
    }

    public function upload_theme()
    {
        if (is_array($_FILES) && count($_FILES) > 0) {
            move_uploaded_file($_FILES['theme_zip']['tmp_name'], 'themes/' . $_FILES['theme_zip']['name']);
            redirect(site_url('admin/theme_settings'), 'refresh');
        }
        $this->load->view('backend/admin/upload_theme');
    }

    public function delete_course_review($rating_id = "")
    {
        $query          = $this->db->where('id', $rating_id);
        $course_details = $this->db->where('id', $query->get('rating')->getRow()->ratable_id)->get('course')->getRowArray();
        $this->db->where('id', $rating_id)->delete('rating');

        $this->session->setFlashdata('flash_message', get_phrase('user_review_deleted_successfully'));
        redirect(site_url('home/course/' . slugify($course_details['title']) . '/' . $course_details['id']), 'refresh');
    }

    //Start Notification
    public function get_my_notification($type = "")
    {
        $user_id = $this->session->get('user_id');

        if ($type == 'mark_all_as_read') {
            $this->db->where('to_user', $user_id);
            $this->db->table('notifications')->update(['status' => 1]);
        }

        if ($type == 'remove_all') {
            $this->db->where('to_user', $user_id);
            $this->db->table('notifications')->delete();
        }

        $this->db->where('to_user', $user_id);
        $this->db->limit(50);
        $query                      = $this->db->orderBy('status ASC, id desc');
        $page_data['notifications'] = $query->get('notifications');

        if ($query->where('status', 0)->get('notifications')->getNumRows() > 0):
            $response['notification_icon_class'] = 'noti-icon-badge';
        else:
            $response['notification_icon_class'] = '';
        endif;
        $response['rendered_view'] = $this->load->view('backend/header_notification', $page_data, true);

        echo json_encode($response);
    }
    //End notification

    public function language_import()
    {
        $this->load->dbforge();

        foreach ($_FILES['language_files']['name'] as $key => $language) {
            $language_name = strtolower(preg_replace('/\s+/', '_', explode('.', $_FILES['language_files']['name'][$key])[0]));
            //Create language column if not exist
            if (! $this->db->field_exists($language_name, 'language')) {
                $fields = [
                    $language_name => [
                        'type'      => 'LONGTEXT',
                        'default'   => null,
                        'null'      => true,
                        'collation' => 'utf8_unicode_ci',
                    ],
                ];
                $this->dbforge->add_column('language', $fields);
            }

            $language_content_arr = json_decode(file_get_contents($_FILES['language_files']['tmp_name'][$key]), true);
            if (is_array($language_content_arr)) {
                //Upload the json file
                move_uploaded_file($_FILES['language_files']['tmp_name'][$key], 'application/language/' . $language_name . '.json');
            } else {
                $this->session->setFlashdata('error_message', get_phrase('JSON_validation_failed') . '!');
                redirect(site_url('admin/manage_language'), 'refresh');
            }

            foreach ($language_content_arr as $phrase_key => $phrase) {
                $phrase_key = strtolower(preg_replace('/\s+/', '_', $phrase_key));
                $query      = $this->db->table('language')->where(['phrase' => $phrase_key])->get();

                if ($query->getNumRows() > 0) {
                    $this->db->where('phrase', $phrase_key);
                    $this->db->table('language')->update([$language_name => $phrase]);
                } else {
                    $this->db->table('language')->insert(['phrase' => $phrase_key, $language_name => $phrase]);
                }
            }
        }

        $this->session->setFlashdata('flash_message', get_phrase('language_file_import_successfully'));
        redirect(site_url('admin/manage_language'), 'refresh');
    }

    public function export_language($language)
    {
        $this->load->helper('download');
        $language     = strtolower($language);
        $json_content = [];

        foreach ($this->db->table('language')->get()->getResultArray() as $row) {
            $json_content[$row['phrase']] = $row[$language];
        }
        force_download($language . '.json', json_encode($json_content));
    }

    public function subscribed_user($type = "", $id = "")
    {
        if ($type == 'delete') {
            $this->db->where('id', $id)->delete('newsletter_subscriber');
            $this->session->setFlashdata('flash_message', get_phrase('Newsletter subscription deleted successfully'));
            redirect(site_url('admin/subscribed_user'), 'refresh');
        }

        if ($_POST) {
            $data = [];
            //mentioned all with colum of database table that related with html table
            $columns = ['id', 'email', 'id', 'id'];

            $limit = htmlspecialchars_($this->request->getPost('length'));
            $start = htmlspecialchars_($this->request->getPost('start'));

            $column_index = $columns[$this->request->getPost('order')[0]['column']];

            $dir                 = $this->request->getPost('order')[0]['dir'];
            $total_number_of_row = $this->db->table('newsletter_subscriber')->get()->getNumRows();

            $filtered_number_of_row = $total_number_of_row;
            $search                 = $this->request->getPost('search')['value'];

            if (empty($search)) {
                $this->db->select('*');
                $this->db->limit($limit, $start);
                $this->db->orderBy($column_index, $dir);
                $newsletter_subscriber = $this->db->table('newsletter_subscriber')->get()->getResultArray();
            } else {
                $this->db->select('*');
                $this->db->like('email', $search);
                $this->db->limit($limit, $start);
                $this->db->orderBy($column_index, $dir);
                $newsletter_subscriber = $this->db->table('newsletter_subscriber')->get()->getResultArray();

                $filtered_number_of_row = count($newsletter_subscriber);
            }

            foreach ($newsletter_subscriber as $key => $row):
                $user_row = $this->db->where('email', $row['email'])->get('users');
                //user email
                $email = $row['email'];

                if ($user_row->getNumRows() > 0) {
                    if ($user_row->getRow()->is_instructor != 1) {
                        $user_status = '<p class="my-0">' . $user_row->getRow()->first_name . ' ' . $user_row->getRow()->last_name . '</p>';
                        $user_status .= '<span class="badge badge-primary">' . get_phrase('Student') . '</span>';
                    } else {
                        $user_status = '<p class="my-0">' . $user_row->getRow()->first_name . ' ' . $user_row->getRow()->last_name . '</p>';
                        $user_status .= '<span class="badge badge-success">' . get_phrase('Instructor') . '</span>';
                    }
                } else {
                    $user_status = '<span class="badge badge-warning">' . get_phrase('Not registered') . '</span>';
                }

                $action = '<div class="dropright dropright">
		                                <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                                    <i class="mdi mdi-dots-vertical"></i>
		                                </button>
		                                <ul class="dropdown-menu">
		                                    <li><a class="dropdown-item" href="#" onclick="confirm_modal(&#39;' . site_url('admin/subscribed_user/delete/' . $row['id']) . '&#39;);">' . get_phrase('delete') . '</a></li>
		                                </ul>
		                            </div>';

                $nestedData['key']         = ++$key;
                $nestedData['email']       = $email;
                $nestedData['user_status'] = $user_status;
                $nestedData['action']      = $action . '<script>$("a, i").tooltip();</script>';
                $data[]                    = $nestedData;
            endforeach;

            $json_data = [
                "draw"            => intval($this->request->getPost('draw')),
                "recordsTotal"    => intval($total_number_of_row),
                "recordsFiltered" => intval($filtered_number_of_row),
                "data"            => $data,
            ];
            echo json_encode($json_data);
        } else {
            $page_data['page_name']  = 'subscribed_user';
            $page_data['page_title'] = get_phrase('Subscribed user');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function newsletter_add_form()
    {
        $this->load->view('backend/admin/add_newsletter');
    }

    public function newsletter_edit_form($id)
    {
        $page_data['newsletter'] = $this->db->where('id', $id)->get('newsletters')->getRowArray();
        $this->load->view('backend/admin/edit_newsletter', $page_data);
    }

    public function newsletter_send_form($id)
    {
        $page_data['newsletter'] = $this->db->where('id', $id)->get('newsletters')->getRowArray();
        $this->load->view('backend/admin/send_newsletter', $page_data);
    }

    public function newsletters($type = "", $id = "")
    {
        if ($type == 'add') {
            $this->crud_model->add_newsletter();
            $this->session->setFlashdata('flash_message', get_phrase('Newsletter added successfully'));
            redirect(site_url('admin/newsletters'), 'refresh');
        }
        if ($type == 'edit') {
            $this->crud_model->update_newsletter($id);
            $this->session->setFlashdata('flash_message', get_phrase('Newsletter updated successfully'));
            redirect(site_url('admin/newsletters?tab=' . $id), 'refresh');
        }

        if ($type == 'send') {
            $to = [];

            $subject     = $this->request->getPost('subject');
            $description = $this->request->getPost('description', false);
            $send_to     = $this->request->getPost('send_to');

            if ($send_to == 'all') {
                $all_users = $this->db->where('status', 1)->where('role_id !=', 1)->get('users')->getResultArray();
                foreach ($all_users as $key => $all_user):
                    $to[] = $all_user['email'];
                endforeach;
            } elseif ($send_to == 'student') {
                $all_users = $this->db->where('status', 1)->where('role_id !=', 1)->where('is_instructor !=', 1)->get('users')->getResultArray();
                foreach ($all_users as $key => $all_user):
                    $to[] = $all_user['email'];
                endforeach;
            } elseif ($send_to == 'instructor') {
                $all_users = $this->db->where('status', 1)->where('role_id !=', 1)->where('is_instructor', 1)->get('users')->getResultArray();
                foreach ($all_users as $key => $all_user):
                    $to[] = $all_user['email'];
                endforeach;
            } elseif ($send_to == 'all_subscriber') {
                $all_subscriber = $this->db->table('newsletter_subscriber')->get()->getResultArray();
                foreach ($all_subscriber as $key => $subscriber):
                    $to[] = $subscriber['email'];
                endforeach;
            } elseif ($send_to == 'registered_subscriber') {
                $all_subscriber = $this->db->table('newsletter_subscriber')->get()->getResultArray();
                foreach ($all_subscriber as $key => $subscriber):
                    $registration = $this->db->where('status', 1)->where('email', $subscriber['email'])->get('users');
                    if ($registration->getNumRows() > 0) {
                        $to[] = $subscriber['email'];
                    }
                endforeach;
            } elseif ($send_to == 'non_registered_subscriber') {
                $all_subscriber = $this->db->table('newsletter_subscriber')->get()->getResultArray();
                foreach ($all_subscriber as $key => $subscriber):
                    $registration = $this->db->where('status', 1)->where('email', $subscriber['email'])->get('users');
                    if ($registration->getNumRows() == 0) {
                        $to[] = $subscriber['email'];
                    }
                endforeach;
            } elseif ($send_to == 'selected_user') {
                $user_ids  = $this->request->getPost('user_id');
                $all_users = $this->db->whereIn('id', $user_ids)->get('users')->getResultArray();
                foreach ($all_users as $key => $all_user):
                    $to[] = $all_user['email'];
                endforeach;
            } else {
                $this->session->setFlashdata('error_message', get_phrase('You must select at least one single user'));
                redirect(site_url('admin/newsletters'), 'refresh');
            }

            $email_data['subject'] = $subject;
            $email_data['message'] = $description;
            $email_template        = $this->load->view('email/static_common_template', $email_data, true);

            $this->crud_model->assignEmailToSendList($to, $subject, $email_template);

            //$this->email_model->send_smtp_mail($email_template, $subject, $to);//
            $this->session->setFlashdata('flash_message', get_phrase('Users are assigned to newsletter mailing list') . ' ' . get_phrase('Please wait'));
            redirect(site_url('admin/newsletters'), 'refresh');
        }

        if ($type == 'delete') {
            $this->crud_model->delete_newsletter($id);
            $this->session->setFlashdata('flash_message', get_phrase('Newsletter deleted successfully'));
            redirect(site_url('admin/newsletters'), 'refresh');
        }
        $page_data['page_name']  = 'newsletters';
        $page_data['page_title'] = get_phrase('Newsletters');
        $this->load->view('backend/index', $page_data);
    }

    public function newsletter_history($type = "", $id = "")
    {

        if ($_POST) {
            $data = [];
            //mentioned all with colum of database table that related with html table
            $columns = ['id', 'subject', 'email', 'id', 'id'];

            $limit = htmlspecialchars_($this->request->getPost('length'));
            $start = htmlspecialchars_($this->request->getPost('start'));

            $column_index = $columns[$this->request->getPost('order')[0]['column']];

            $dir                 = $this->request->getPost('order')[0]['dir'];
            $total_number_of_row = $this->db->where('status', $type)->get('newsletter_histories')->getNumRows();

            $filtered_number_of_row = $total_number_of_row;
            $search                 = $this->request->getPost('search')['value'];

            if (empty($search)) {
                $this->db->select('*');
                $this->db->where('status', $type);
                $this->db->limit($limit, $start);
                $this->db->orderBy($column_index, $dir);
                $newsletter_histories = $this->db->table('newsletter_histories')->get()->getResultArray();
            } else {
                $this->db->select('*');
                $this->db->like('email', $search);
                $this->db->orLike('subject', $search);
                $this->db->orLike('description', $search);

                $this->db->groupStart();
                $this->db->where('status', $type);
                $this->db->groupEnd();

                $this->db->limit($limit, $start);
                $this->db->orderBy($column_index, $dir);
                $newsletter_histories = $this->db->table('newsletter_histories')->get()->getResultArray();

                $filtered_number_of_row = count($newsletter_histories);
            }

            foreach ($newsletter_histories as $key => $row):
                if ($row['status'] != 'sent') {
                    $action = '<a class="btn btn-primary" href="javascript:void(0)" onclick="actionTo(&#39;' . site_url('admin/newsletter_history/send/' . $row['id']) . '&#39;);">' . get_phrase('Send') . '</a>';
                    if ($row['status'] == 'faild') {
                        $status = '<span class="text-capitalize text-danger">' . $row['status'] . '</span>';
                    } elseif ($row['status'] == 'pending') {
                    $status = '<span class="text-capitalize text-warning">' . $row['status'] . '</span>';
                } else {
                    $status = '<span class="text-capitalize text-secondary">' . $row['status'] . '</span>';
                }
            } else {
                $action = '<a class="dropdown-item" href="javascript:void(0)" onclick="actionTo(&#39;' . site_url('admin/newsletter_history/send/' . $row['id']) . '&#39;);">' . get_phrase('Send Again') . '</a>';
                $status = '<span class="text-capitalize text-success">' . $row['status'] . '</span>';
            }

            $nestedData['key']     = ++$key;
            $nestedData['subject'] = $row['subject'];
            $nestedData['email']   = $row['email'];
            $nestedData['status']  = $status;
            $nestedData['action']  = $action;
            $data[]                = $nestedData;
            endforeach;

            $json_data = [
                "draw"            => intval($this->request->getPost('draw')),
                "recordsTotal"    => intval($total_number_of_row),
                "recordsFiltered" => intval($filtered_number_of_row),
                "data"            => $data,
            ];
            echo json_encode($json_data);
        } elseif ($type == "send") {
            $this->db->where('id', $id);
            $newsletter_history = $this->db->table('newsletter_histories')->get()->getRowArray();
            $response           = $this->email_model->send_smtp_mail($newsletter_history['description'], $newsletter_history['subject'], $newsletter_history['email']);

            if ($response) {
                $this->db->where('id', $id);
                $newsletter_history = $this->db->table('newsletter_histories')->update(['status' => 'sent']);
                $sending_response   = [
                    'run_function' => 'refreshTable',
                    'success'      => get_phrase('Mail sent successfully'),
                ];
                echo json_encode($sending_response);
            } else {
                $sending_response = [
                    'error' => get_phrase('Failed to send mail'),
                ];
                echo json_encode($sending_response);
            }
        } else {
            $page_data['type']       = $type;
            $page_data['page_name']  = 'newsletter_history';
            $page_data['page_title'] = get_phrase('Newsletter history');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function newsletter_statistics()
    {
        echo $this->load->view('backend/admin/newsletter_statistics', [], true);
    }

    public function student_academic_progress($course_id = "")
    {
        $course_details    = $this->crud_model->get_course_by_id($course_id)->getRowArray();
        $multi_instructors = explode(',', $course_details['user_id']);
		 $page_data['company_name']   = isset($_GET['company_name']) ? $_GET['company_name'] : "";
		$page_data['progress_status'] = isset($_GET['progress_status']) ? $_GET['progress_status'] : "all"; 
        $page_data['course_details'] = $course_details;
        $this->load->view('backend/admin/student_academic_progress', $page_data);
    }

    public function student_academic_quiz_result($course_id = "", $student_id = "")
    {
        $course_details    = $this->crud_model->get_course_by_id($course_id)->getRowArray();
        $multi_instructors = explode(',', $course_details['user_id']);

        if (! in_array($this->session->get('user_id'), $multi_instructors)) {
            return false;
        }

        $page_data['course_details'] = $course_details;
        $page_data['student_id']     = $student_id;
        $this->load->view('backend/admin/student_academic_quiz_result', $page_data);
    }

    public function home_page_layout($home_page = "")
    {
        $this->db->where('key', 'home_page');
        $this->db->table('frontend_settings')->update(['value' => $home_page]);
        $this->session->setFlashdata('flash_message', get_phrase('New home page layout has been activated'));
        redirect(site_url('admin/home_page_builder'), 'refresh');
    }

    public function student_certificate($user_id = "", $course_id = "")
    {
        $this->load->model('addons/Certificate_model', 'certificate_model');
        $course_progress = $this->crud_model->get_watch_histories($user_id, $course_id)->getRow()->course_progress;
        if ($course_progress >= 100) {
            $this->certificate_model->check_certificate_eligibility($course_id, $user_id);
            $certificate = $this->db->table('certificates')->where(['course_id' => $course_id, 'student_id' => $user_id])->get();
            redirect(site_url('certificate/' . $certificate->getRow()->shareable_url));
        } else {
            $this->session->setFlashdata('error_message', get_phrase('The course is not compleated yet'));
            redirect(site_url('admin/course_form/course_edit/' . $course_id . '?tab=academic_progress'));
        }
    }

    public function contact($type = "", $id = "")
    {
        if ($type == 'delete_selected_contact') {
            $selected_ids = $this->request->getGet('selected_ids'); // Assuming selected_ids are passed via GET
            $ids          = explode(',', $selected_ids);       // Convert the comma-separated string to an array

            if (! empty($ids)) {
                $this->db->whereIn('id', $ids)->delete('contact');
                $this->session->setFlashdata('flash_message', get_phrase('Contacts deleted successfully'));
            } else {
                $this->session->setFlashdata('flash_message', get_phrase('No contacts selected for deletion'));
            }

            redirect(site_url('admin/contact'), 'refresh');
        }

        if ($type == 'delete') {
            $this->db->where('id', $id)->delete('contact');
            $this->session->setFlashdata('flash_message', get_phrase('Contact deleted successfully'));
            redirect(site_url('admin/contact'), 'refresh');
        }

        if ($type == '') {
            $page_data['page_name']  = 'contact';
            $page_data['page_title'] = get_phrase('Contact');
            $this->load->view('backend/index', $page_data);
        }

        if ($type == 'contact_reply_form' && $id != '') {
            $page_data['contact'] = $this->crud_model->get_contacts($id)->getRowArray();
            $this->load->view('backend/admin/contact_reply_form', $page_data);
        }

        if ($type == 'send_reply' && $id != '') {
            $message         = $this->request->getPost('reply_message');
            $contact_details = $this->crud_model->get_contacts($id)->getRowArray();
            $this->email_model->send_smtp_mail($message, get_phrase('Reply from - ') . get_settings('system_name'), $contact_details['email']);
            $this->db->where('id', $id)->update('contact', ['replied' => 1]);
            $this->session->setFlashdata('flash_message', get_phrase('Reply sent successfully'));
            redirect(site_url('admin/contact'), 'refresh');
        }

        if ($type == 'data-table' && $_GET) {
            $this->db->where('has_read', null)->update('contact', ['has_read' => 1]);

            $data = [];
            //mentioned all with colum of database table that related with html table
            $columns = ['id', 'first_name', 'email', 'message', 'id'];

            $limit = htmlspecialchars_($this->request->getGet('length'));
            $start = htmlspecialchars_($this->request->getGet('start'));

            $column_index = $columns[$this->request->getGet('order')[0]['column']];

            $dir                 = $this->request->getGet('order')[0]['dir'];
            $total_number_of_row = $this->db->table('contact')->get()->getNumRows();

            $filtered_number_of_row = $total_number_of_row;
            $search                 = $this->request->getGet('search')['value'];

            if (empty($search)) {
                $this->db->limit($limit, $start);
                $this->db->orderBy($column_index, $dir);
                $contacts = $this->db->table('contact')->get()->getResultArray();
            } else {
                $this->db->like('first_name', $search);
                $this->db->orLike('last_name', $search);
                $this->db->orLike('email', $search);
                $this->db->orLike('phone', $search);
                $this->db->orLike('address', $search);
                $this->db->orLike('message', $search);
                $this->db->limit($limit, $start);
                $this->db->orderBy($column_index, $dir);
                $contacts               = $this->db->table('contact')->get();
                $filtered_number_of_row = $contacts->getNumRows();
                $contacts               = $contacts->getResultArray();
            }

            foreach ($contacts as $key => $row):
                if ($row['replied'] == 1):
                    $reply_sent = ' <i class="fas fa-check-circle text-success" title="' . get_phrase('Reply sent') . '" data-toggle="tooltip"></i>';
                else:
                    $reply_sent = '';
                endif;

                $user_row = $this->db->where('email', $row['email'])->get('users');
                if ($user_row->getNumRows() > 0) {
                    if ($user_row->getRow()->is_instructor != 1) {
                        $user_status = '<span class="badge badge-primary">' . get_phrase('Student') . '</span>';
                    } else {
                        $user_status = '<span class="badge badge-success">' . get_phrase('Instructor') . '</span>';
                    }
                } else {
                    $user_status = '<span class="badge badge-warning">' . get_phrase('Not registered') . '</span>';
                }

                $contact_info = '<p class="my-0">' . get_phrase('Email') . ': <a href="mailto:' . $row['email'] . '">' . $row['email'] . '</a></p>';
                if ($row['phone'] != '') {
                    $contact_info .= '<p class="my-0">' . get_phrase('Phone') . ': <a href="tel:' . $row['phone'] . '">' . $row['phone'] . '</a></p>';
                }
                if ($row['address'] != '') {
                    $contact_info .= '<p class="my-0">' . $row['address'] . '</a></p>';
                }

                $action = '<div class="dropright dropright">
		                                <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                                    <i class="mdi mdi-dots-vertical"></i>
		                                </button>
		                                <ul class="dropdown-menu">
		                                    <li><a class="dropdown-item" href="#" onclick="showAjaxModal(&#39;' . site_url('admin/contact/contact_reply_form/' . $row['id']) . '&#39;, &#39;' . get_phrase('Reply to ' . $row['first_name'] . ' ' . $row['last_name']) . '&#39;);">' . get_phrase('Reply') . '</a></li>
		                                    <li><a class="dropdown-item" href="#" onclick="confirm_modal(&#39;' . site_url('admin/contact/delete/' . $row['id']) . '&#39;);">' . get_phrase('delete') . '</a></li>
		                                </ul>
		                            </div>';

                $nestedData['checkbox'] = '<input type="checkbox" name="selected_contacts[]" value="' . $row['id'] . '" data-row-id="' . $row['id'] . '">';
                $nestedData['key']      = ++$key;
                $nestedData['name']     = '<p class="my-0">' . $row['first_name'] . ' ' . $row['last_name'] . $reply_sent . '</p>' . $user_status;
                $nestedData['contact']  = $contact_info;
                $nestedData['message']  = $row['message'];
                $nestedData['action']   = $action . '<script>$("a, i").tooltip();</script>';
                $data[]                 = $nestedData;
            endforeach;

            $json_data = [
                "draw"            => intval($this->request->getPost('draw')),
                "recordsTotal"    => intval($total_number_of_row),
                "recordsFiltered" => intval($filtered_number_of_row),
                "data"            => $data,
            ];
            echo json_encode($json_data);
        }
    }

    public function update_language_direction()
    {
        $language      = $this->request->getPost('language');
        $dir           = $this->request->getPost('dir');
        $language_dirs = get_settings('language_dirs') ? json_decode(get_settings('language_dirs'), true) : ['english' => 'ltr'];

        $language_dirs[$language] = $dir;

        $data['value'] = json_encode($language_dirs);

        if ($this->db->table('settings')->where(['key' => 'language_dirs'])->get()->getNumRows() > 0) {
            $this->db->where('key', 'language_dirs')->update('settings', $data);
        } else {
            $data['key'] = 'language_dirs';
            $this->db->table('settings')->insert($data);
        }
        echo get_phrase('Language direction updated successfully');
    }

    public function resource_files($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            if (isset($_FILES['resource_file']['name']) && $_FILES['resource_file']['name'] != "") {
                $data['file_name'] = random(20) . '.' . pathinfo($_FILES['resource_file']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['resource_file']['tmp_name'], 'uploads/resource_files/' . $data['file_name']);
            }

            $data['title']      = $this->request->getPost('title');
            $data['lesson_id']  = $param2;
            $data['created_at'] = time();
            $this->db->table('resource_files')->insert($data);

            $response['replace'] = ['elem' => '.resource_file_content', 'content' => $this->load->view('backend/admin/resource_files', ['param2' => $param2], true)];
            echo json_encode($response);
        } elseif ($param1 == 'update') {
            $file_details = $this->db->table('resource_files')->where(['id' => $param2])->get()->getRowArray();
            if (isset($_FILES['resource_file']['name']) && $_FILES['resource_file']['name'] != "") {
                if (file_exists('uploads/resource_files/' . $file_details['file_name']) && $file_details['file_name']) {
                    unlink('uploads/resource_files/' . $file_details['file_name']);
                }
                $data['file_name'] = random(20) . '.' . pathinfo($_FILES['resource_file']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['resource_file']['tmp_name'], 'uploads/resource_files/' . $data['file_name']);
            }

            $data['title']      = $this->request->getPost('title');
            $data['updated_at'] = time();
            $this->db->where('id', $param2);
            $this->db->table('resource_files')->update($data);

            $response['replace'] = ['elem' => '.resource_file_content', 'content' => $this->load->view('backend/admin/resource_files', ['param2' => $file_details['lesson_id']], true)];
            echo json_encode($response);
        } elseif ($param1 == 'delete') {
            $file_details = $this->db->table('resource_files')->where(['id' => $param2])->get()->getRowArray();
            if (file_exists('uploads/resource_files/' . $file_details['file_name']) && $file_details['file_name']) {
                unlink('uploads/resource_files/' . $file_details['file_name']);
            }

            $this->db->where('id', $param2);
            $this->db->table('resource_files')->delete();

            $response['replace'] = ['elem' => '.resource_file_content', 'content' => $this->load->view('backend/admin/resource_files', ['param2' => $file_details['lesson_id']], true)];
            $response['success'] = get_phrase('Resource deleted successfully');
            $response['fadeOut'] = '#resource_file_' . $file_details['id'];
            echo json_encode($response);
        }
    }

    public function cronjob($type = "")
    {
        // Write some content to the cron file for CURL call.
        $content = '<?php
        $url = "' . base_url("home/sendEmailToAssignedAddresses") . '";
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL session
        $response = curl_exec($ch);

        // Close cURL session
        curl_close($ch);';

        //White this file using curl ($content) content
        $newsletter_cron_file_path = "uploads/cronjob/newsletter_cron.php";
        if (file_exists($newsletter_cron_file_path)) {
            unlink($newsletter_cron_file_path);
        }

        // //CRON CONTENTS
        // // Get PHP Binary Path
        // $setInterval_1m = '* * * * *'; // for every 1 minute
        // //$phpIniFile = php_ini_loaded_file();
        // $phpBinaryPath = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
        // // Get the application path
        // $applicationPath = realpath(APPPATH . '..') . '/' . $newsletter_cron_file_path;

        // Get PHP Binary Path
        $phpSapi       = php_sapi_name();
        $phpBinaryPath = ($phpSapi === 'cli') ? PHP_BINARY : PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
                                        // Rest of your code remains unchanged
        $setInterval_1m  = '* * * * *'; // for every 1 minute
        $applicationPath = realpath(APPPATH . '..') . '/' . $newsletter_cron_file_path;

        if ($type == 'start') {
            if (! is_dir('uploads/cronjob')) {
                mkdir('uploads/cronjob', 0777, true);
            }

            // Open the file for writing (creates the file if it doesn't exist)
            $fileHandle = fopen($newsletter_cron_file_path, "w");
            // Check if the file was opened successfully
            if ($fileHandle) {
                fwrite($fileHandle, $content);
                // Close the file handle
                fclose($fileHandle);
            } else {
                $this->session->setFlashdata('error_message', get_phrase("Failed to create the cron file") . '. File path:' . $newsletter_cron_file_path);
                redirect(site_url('admin/newsletters'), 'refresh');
            }

            // Execute Shell Command
            // $cronCommand = 'crontab -l | { cat; echo "' . $setInterval_1m . ' ' . $phpBinaryPath . ' ' . $applicationPath . '"; } | crontab -';
            // exec($cronCommand, $output, $return_var);

            // if ($return_var !== 0) {
            //     unlink($newsletter_cron_file_path);
            //     $this->session->setFlashdata('error_message', get_phrase('Cron job setup failed') . ' Output:' . implode("\n", $output));
            //     redirect(site_url('admin/newsletters'), 'refresh');
            // } else {
            //     $this->session->setFlashdata('flash_message', get_phrase('Cron job successfully set up'));
            //     redirect(site_url('admin/newsletters'), 'refresh');
            // }

            redirect(site_url('admin/newsletters'), 'refresh');
        } elseif ($type == 'stop') {
            // Remove Cron Job
            // $cronCommandRemove = 'crontab -l | grep -v "' . $phpBinaryPath . ' ' . $applicationPath . '" | crontab -';
            // exec($cronCommandRemove, $outputRemove, $returnVarRemove);

            // if ($returnVarRemove !== 0) {
            //     $this->session->setFlashdata('error_message', get_phrase('Cron job removal failed') . ' Output:' . implode("\n", $outputRemove));
            //     redirect(site_url('admin/newsletters'), 'refresh');
            // } else {
            //     $this->session->setFlashdata('flash_message', get_phrase('Cron job successfully removed'));
            //     redirect(site_url('admin/newsletters'), 'refresh');
            // }

            $newsletter_cron_file_path = "uploads/cronjob/newsletter_cron.php";
            unlink($newsletter_cron_file_path);
            redirect(site_url('admin/newsletters'), 'refresh');
        }
    }

    public function wasabi_settings($type = '')
    {

        if ($type == 'update') {
            if ($this->db->where('key', 'wasabi_key')->get('settings')->getNumRows() > 0) {
                $data['value'] = $this->request->getPost('access_key');
                $this->db->where('key', 'wasabi_key');
                $this->db->table('settings')->update($data);
            } else {
                $data['value'] = $this->request->getPost('access_key');
                $data['key']   = 'wasabi_key';
                $this->db->table('settings')->insert($data);
            }

            if ($this->db->where('key', 'wasabi_secret_key')->get('settings')->getNumRows() > 0) {
                $data['value'] = $this->request->getPost('secret_key');
                $this->db->where('key', 'wasabi_secret_key');
                $this->db->table('settings')->update($data);
            } else {
                $data['value'] = $this->request->getPost('secret_key');
                $data['key']   = 'wasabi_secret_key';
                $this->db->table('settings')->insert($data);
            }

            if ($this->db->where('key', 'wasabi_bucketname')->get('settings')->getNumRows() > 0) {
                $data['value'] = $this->request->getPost('bucket_name');
                $this->db->where('key', 'wasabi_bucketname');
                $this->db->table('settings')->update($data);
            } else {
                $data['value'] = $this->request->getPost('bucket_name');
                $data['key']   = 'wasabi_bucketname';
                $this->db->table('settings')->insert($data);
            }

            if ($this->db->where('key', 'wasabi_region')->get('settings')->getNumRows() > 0) {
                $data['value'] = $this->request->getPost('region_name');
                $this->db->where('key', 'wasabi_region');
                $this->db->table('settings')->update($data);
            } else {
                $data['value'] = $this->request->getPost('region_name');
                $data['key']   = 'wasabi_region';
                $this->db->table('settings')->insert($data);
            }

            $this->session->setFlashdata('flash_message', get_phrase('Wasabi Settings Updated Successfully'));
            redirect(site_url('admin/wasabi_settings'), 'refresh');
        }

        $page_data['page_name']  = 'wasabi_settings';
        $page_data['page_title'] = get_phrase('Wasabi Storage Settings');
        $this->load->view('backend/index', $page_data);
    }

    public function bbb_live_class_settings($type = "")
    {
        if ($type == 'update') {
            $data['value'] = json_encode($_POST);
            if ($this->db->where('key', 'bbb_setting')->get('settings')->getNumRows() > 0) {
                $this->db->where('key', 'bbb_setting')->update('settings', $data);
            } else {
                $data['key'] = 'bbb_setting';
                $this->db->table('settings')->insert($data);
            }
            $this->session->setFlashdata('flash_message', get_phrase('BigBlueButton configuration has been Updated'));
            redirect(site_url('admin/bbb_live_class_settings'), 'refresh');
        }

        $page_data['page_name']  = 'bbb_live_class_settings';
        $page_data['page_title'] = get_phrase('BBB live class settings');
        $this->load->view('backend/index', $page_data);
    }

    public function save_bbb_meeting($course_id = "")
    {
        $data['meeting_id']   = $this->request->getPost('bbb_meeting_id');
        $data['moderator_pw'] = $this->request->getPost('bbb_moderator_pw');
        $data['viewer_pw']    = $this->request->getPost('bbb_viewer_pw');
        $data['instructions'] = $this->request->getPost('instructions');

        if ($this->db->where('course_id', $course_id)->get('bbb_meetings')->getNumRows() > 0) {
            $data['updated_at'] = time();
            $this->db->where('course_id', $course_id)->update('bbb_meetings', $data);
        } else {
            $data['course_id']  = $course_id;
            $data['created_at'] = time();
            $data['updated_at'] = $data['created_at'];
            $this->db->table('bbb_meetings')->insert($data);
        }

        echo get_phrase("BigBlueButton Meeting has been updated");
    }

    public function start_bbb_meeting($course_id = "")
    {
        $course_details = $this->crud_model->get_courses($course_id)->getRowArray();
        $bbb_meeting    = $this->db->where('course_id', $course_id)->get('bbb_meetings');
        $current_url    = site_url('admin/course_form/course_edit/' . $course_id . '?tab=bbb-live-class');

        if ($bbb_meeting->getNumRows() > 0) {
            $bbb_meeting = $bbb_meeting->getRowArray();
            //Sanitize API URL START
            $api_url = get_settings('bbb_setting', true)['endpoint'] ?? '';
            // Parse the URL
            $parsed_url = parse_url($api_url);
            // Remove the 'api' part if it exists in the path
            $path = rtrim(str_replace('/api', '', $parsed_url['path']), '/');
            // Rebuild the URL
            $api_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $path;
            //Sanitize API URL END

            //Create BBB meeting START
            $query_data = http_build_query([
                'name'        => $course_details['title'],
                'meetingID'   => $bbb_meeting['meeting_id'],
                'attendeePW'  => $bbb_meeting['viewer_pw'],
                'moderatorPW' => $bbb_meeting['moderator_pw'],
                'redirectURL' => $current_url,
            ]);
            $response = $this->crud_model->callBbbApi('create', $query_data, $bbb_meeting['meeting_id']);
            //Create BBB meeting END

            // Handle response & redirect to meeting url
            if ($response) {
                $xml        = simplexml_load_string($response);
                $returncode = (string) $xml->returncode;

                if ($returncode == 'SUCCESS') {
                    // $moderator_details = $this->user_model->get_all_user($this->session->get('user_id'))->getRowArray();
                    // //JOIN AS A viewer
                    // $full_name = $moderator_details['first_name'].' '.$moderator_details['last_name']; // The full name of the participant
                    // $role = 'moderator'; // The role of the user (either "viewer" or "moderator")
                    // $join_url = $api_url."/api/join?meetingID=".$bbb_meeting['meeting_id']."&fullName=$full_name&password=".$bbb_meeting['moderator_pw']."&joinViaHtml5=true&redirect=true&joinParam[role]=$role";
                    // echo $join_url;
                    // return;
                    echo $this->crud_model->join_bbb_meeting_by_curl_calls($course_id, true);
                    return;
                } else {
                    $this->session->setFlashdata('error_message', get_phrase("Failed to create meeting. Error code: ____", [$returncode]));
                }
            } else {
                $this->session->setFlashdata('error_message', get_phrase("Failed to connect to BigBlueButton API"));
            }
        } else {
            $this->session->setFlashdata('error_message', get_phrase("Please save your meeting info first"));
        }
        echo $current_url;
    }

    public function change_course_author($course_id = "")
    {
        if (isset($_POST) && count($_POST) > 0) {
            if ($_POST['instructor_id'] > 0) {
                $this->db->where('id', $course_id)->update('course', ['creator' => $_POST['instructor_id']]);
                $this->session->setFlashdata('flash_message', get_phrase("Course author changed successfully"));
            } else {
                $this->session->setFlashdata('error_message', get_phrase("Something is wrong"));
            }
            redirect(site_url('admin/course_form/course_edit/' . $course_id . '?tab=basic'), 'refresh');
        } else {
            $page_data['instructors']    = $this->user_model->get_instructor()->getResultArray();
            $page_data['course_details'] = $this->crud_model->get_course_by_id($course_id)->getRowArray();
            $this->load->view('backend/admin/change_course_author', $page_data);
        }
    }

    public function seo_settings($param1 = "", $param2 = "")
    {
        if ($param1 == 'update') {
            $this->crud_model->save_seo_settings($param2);
            $this->session->setFlashdata('flash_message', get_phrase('seo_settings_updated_successfully'));
            redirect(site_url('admin/seo_settings/' . $param2), 'refresh');
        }
        $page_data['seo_meta_tags'] = $this->crud_model->get_seo_meta_tags()->getResultArray();
        $page_data['active_tab']    = ! empty($param1) ? $param1 : 'home';
        $page_data['page_title']    = get_phrase('seo_settings');
        $page_data['page_name']     = 'seo_settings';
        $this->load->view('backend/index', $page_data);
    }

    public function sitemap_settings()
    {
        $blogs           = $this->crud_model->get_all_blogs()->getResultArray();
        $blog_categories = $this->crud_model->get_blog_categories()->getResultArray();
        $courses         = $this->crud_model->get_courses()->getResultArray();
        $categories      = $this->crud_model->get_categories()->getResultArray();

        // Construct URLs for each blog
        $blog_url_array = [];

        foreach ($blogs as $blog) {
            $slug             = slugify($blog['title']);
            $blog_id          = $blog['blog_id'];
            $url              = base_url("blog/details/$slug/$blog_id");
            $blog_url_array[] = $url;
        }

        // Construct URLs for each blog category
        $blog_category_url_array = [];

        foreach ($blog_categories as $blog_category) {
            $slug                      = $blog_category['slug'];
            $url                       = base_url("blogs?category=$slug");
            $blog_category_url_array[] = $url;
        }

        // Construct URLs for each category
        $category_url_array = [];

        foreach ($categories as $category) {
            $slug                 = $category['slug'];
            $url                  = base_url("home/courses?category=$slug");
            $category_url_array[] = $url;

            // Retrieve subcategories for the current category using its ID
            $sub_categories = $this->crud_model->get_sub_categories($category['id']);

            foreach ($sub_categories as $sub_category) {
                $sub_slug             = $sub_category['slug'];
                $sub_url              = base_url("home/courses?category=$sub_slug");
                $category_url_array[] = $sub_url;
            }
        }

        // Construct URLs for each course
        $course_url_array = [];

        foreach ($courses as $course) {
            $slug               = slugify($course['title']);
            $course_id          = $course['id'];
            $url                = base_url("home/course/$slug/$course_id");
            $course_url_array[] = $url;
        }

        $page_data['sitemap'] = [
            'key'   => 'sitemap_xml',
            'value' => get_settings('sitemap_xml'), // Fetch the sitemap XML content.
        ];
        $page_data['courses']         = $course_url_array;
        $page_data['categories']      = $category_url_array;
        $page_data['blogs']           = $blog_url_array;
        $page_data['blog_categories'] = $blog_category_url_array;
        $page_data['page_title']      = get_phrase('sitemap_settings');
        $page_data['page_name']       = 'sitemap_settings';
        $this->load->view('backend/index', $page_data);
    }

    public function export_enrol_history_csv()
    {
        // Check if the request method is POST
        if ($this->input->method() === 'post') {
            // Get enrol IDs from the request
            $enrol_ids = $this->request->getPost('enrol_ids');

            // Validate enrol IDs
            if (! is_array($enrol_ids) || empty($enrol_ids)) {
                show_error('No enrol IDs provided.', 400);
            }

            // Fetch enrol data for the provided IDs
            $this->db->whereIn('id', $enrol_ids);
            $query = $this->db->table('enrol')->get();

            // Check if data exists
            if ($query->getNumRows() === 0) {
                show_error('No data found for the provided enrol IDs.', 404);
            }

            // Prepare the CSV header
            $csv_data = '"Id","Student Name","Course Title","Purchase Date","Expiry Date"' . "\n";

            // Populate the CSV rows
            foreach ($query->getResultArray() as $row) {
                // Fetch related user and course data
                $user_data   = $this->db->table('users')->where(['id' => $row['user_id']])->get()->getRowArray();
                $course_data = $this->db->table('course')->where(['id' => $row['course_id']])->get()->getRowArray();

                // Add a CSV row
                $csv_data .= '"' . $row['id'] . '",';
                $csv_data .= '"' . $user_data['first_name'] . ' ' . $user_data['last_name'] . '",';
                $csv_data .= '"' . $course_data['title'] . '",';
                $csv_data .= '"' . date('d-m-Y', $row['date_added']) . '",';
                $csv_data .= '"' . ($row['expiry_date'] ? date('d-m-Y', $row['expiry_date']) : 'Lifetime access') . '"' . "\n";
            }

            // Send the CSV data as a response
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="enrol_history.csv"');
            echo $csv_data;
            exit;
        } else {
            show_error('Invalid request method.', 405);
        }
    }

    public function enrol_list($course_id = "")
    {
        $course_details              = $this->crud_model->get_course_by_id($course_id)->getRowArray();
        $multi_instructors           = explode(',', $course_details['user_id']);
        $page_data['course_details'] = $course_details;

        $page_data['enrol_history'] = $this->crud_model->enrol_history($course_id);

        $this->load->view('backend/admin/course_enrol_list', $page_data);
    }

   /*  public function export_student_progress_csv($course_id)
    {
        // Get the enrolments for the given course
        $enrolments = $this->db->where('course_id', $course_id)->get('enrol')->getResultArray();

        // Initialize an array to hold the CSV data
        $csv_data = '"ID","Enrolment ID","Student","Date Enrolled","Last Seen","Completed On","Progress","Completed Lessons","Watched Duration"' . "\n";

        // Initialize incremental ID
        $incremental_id = 1;

        // Loop through each enrolment and fetch the relevant data
        foreach ($enrolments as $enrolment) {
            // Fetch student data
            $student = $this->user_model->get_all_user($enrolment['user_id'])->getRowArray();

            // Fetch watch history for the student
            $watch_history = $this->db->where('course_id', $course_id)->where('student_id', $enrolment['user_id'])->get('watch_histories')->getRowArray();

            // Handle completed lessons and course progress
            $completed_lesson_arr = isset($watch_history['completed_lesson']) ? json_decode($watch_history['completed_lesson'], true) : [];
            $completed_lesson     = is_array($completed_lesson_arr) ? count($completed_lesson_arr) : 0;
            $course_progress      = isset($watch_history['course_progress']) ? $watch_history['course_progress'] : 0;

            // Format dates in d-m-Y format
            $enrollment_date = date('d-m-Y', $enrolment['date_added']);
            $last_seen       = isset($watch_history['date_updated']) ? date('d-m-Y, H:i a', $watch_history['date_updated']) : 'Not started yet';
            $completed_date  = isset($watch_history['completed_date']) ? date('d-m-Y', $watch_history['completed_date']) : 'Not completed yet';

                                         // Get watched duration
            $total_watched_duration = 0; // seconds
            $watched_durations      = $this->db->table('watched_duration')->where(['watched_student_id' => $enrolment['user_id'], 'watched_course_id' => $course_id])->get();
            foreach ($watched_durations->getResultArray() as $watched_duration) {
                $total_watched_duration += count(json_decode($watched_duration['watched_counter'], true)) * 5;
            }
            $watched_duration = seconds_to_time_format($total_watched_duration);

            // Prepare the data for the CSV row with Incremental ID
            $csv_data .= '"' . $incremental_id . '",';
            $csv_data .= '"' . $enrolment['id'] . '",';
            $csv_data .= '"' . $student['first_name'] . ' ' . $student['last_name'] . '",';
            $csv_data .= '"' . $enrollment_date . '",';
            $csv_data .= '"' . $last_seen . '",';
            $csv_data .= '"' . $completed_date . '",';
            $csv_data .= '"' . $course_progress . '%",';
            $csv_data .= '"' . $completed_lesson . ' out of 10",';
            $csv_data .= '"' . $watched_duration . '"' . "\n";

            // Increment the ID for the next row
            $incremental_id++;
        }

        // Set the headers to trigger a file download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="student_progress.csv"');
        echo $csv_data;
        exit;
    }
 */
    public function export_admins_csv()
    {
        // Check if the request method is POST
        if ($this->input->method() === 'post') {
            // Get admin IDs from the request
            $admin_ids = $this->request->getPost('admin_ids');

            // Validate admin IDs
            if (! is_array($admin_ids) || empty($admin_ids)) {
                show_error('No admin IDs provided.', 400);
            }

            // Fetch admin data for the provided IDs
            $this->db->whereIn('id', $admin_ids);
            $query = $this->db->table('users')->get();

            // Check if data exists
            if ($query->getNumRows() === 0) {
                show_error('No data found for the provided admin IDs.', 404);
            }

            // Prepare the CSV header
            $csv_data = '"Id","Name","Email","Phone"' . "\n";

            // Populate the CSV rows
            foreach ($query->getResultArray() as $row) {
                // Add a CSV row
                $csv_data .= '"' . $row['id'] . '",';
                $csv_data .= '"' . $row['first_name'] . ' ' . $row['last_name'] . '",';
                $csv_data .= '"' . $row['email'] . '",';
                $csv_data .= '"' . $row['phone'] . '"' . "\n";
            }

            // Send the CSV data as a response
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="admins.csv"');
            echo $csv_data;
            exit;
        } else {
            show_error('Invalid request method.', 405);
        }
    }

    // Admin Create Review
    public function review_add()
    {
        $this->load->view('backend/admin/review_add');
    }
    public function review_edit($id = '')
    {
        $page_data['rating'] = $this->db->table('rating')->where(['id' => $id])->get()->getRowArray();
        $this->load->view('backend/admin/review_edit', $page_data);
    }
    public function review($param1 = "", $param2 = "")
    {
        if ($param1 == 'delete' && ! empty($param2)) {
            $this->crud_model->review_delete($param2);
            $this->session->setFlashdata('flash_message', get_phrase('review_deleted_successfully'));
            redirect(site_url('admin/frontend_settings?tab=review'), 'refresh');
        }
    }

    // Badges
    public function badges($param1 = "", $param2 = "")
    {
        if ($param1 == 'course_count') {
            $this->crud_model->badges_store();
            $this->session->setFlashdata('flash_message', get_phrase('course count badges create successfully.'));
            redirect(site_url('admin/badges?tab=courseCount'), 'refresh');
        }
        if ($param1 == 'courses_rating') {
            $this->crud_model->badges_store();
            $this->session->setFlashdata('flash_message', get_phrase('course rating badges create successfully.'));
            redirect(site_url('admin/badges?tab=coursesRating'), 'refresh');
        }
        if ($param1 == 'courses_sale') {
            $this->crud_model->badges_store();
            $this->session->setFlashdata('flash_message', get_phrase('course sale badges create successfully.'));
            redirect(site_url('admin/badges?tab=courseSale'), 'refresh');
        }
        if ($param1 == 'articles') {
            $this->crud_model->badges_store();
            $this->session->setFlashdata('flash_message', get_phrase('course articles badges create successfully.'));
            redirect(site_url('admin/badges?tab=articles'), 'refresh');
        }
        if ($param1 == 'course_completed') {
            $this->crud_model->badges_store();
            $this->session->setFlashdata('flash_message', get_phrase('course completed badges create successfully.'));
            redirect(site_url('admin/badges?tab=courseCompleted'), 'refresh');
        }
        if ($param1 == 'certificate') {
            $this->crud_model->badges_store();
            $this->session->setFlashdata('flash_message', get_phrase('course certificate badges create successfully.'));
            redirect(site_url('admin/badges?tab=certificate'), 'refresh');
        }

        if ($param1 == 'update') {
            $this->crud_model->badges_update();
            $this->session->setFlashdata('flash_message', get_phrase('Badges  Update successfully'));
            redirect(site_url('admin/badges?tab=courseCount'), 'refresh');
        }

        if ($param1 == 'delete') {
            $this->crud_model->badges_delete($param2);
            $this->session->setFlashdata('flash_message', get_phrase('Badges delete successfully.'));
            redirect(site_url('admin/badges?tab=courseCount'), 'refresh');
        }

        $page_data['badgesList'] = $this->crud_model->badges_List()->getResultArray();
        $page_data['page_title'] = get_phrase('badges');
        $page_data['page_name']  = 'badges';
        $this->load->view('backend/index', $page_data);
    }

    public function badges_add()
    {
        $page_data['type'] = $this->request->getGet('type');
        $this->load->view('backend/admin/badges_add', $page_data);
    }

    public function badges_edit($id = '')
    {
        $page_data['badges'] = $this->db->table('badges')->where(['id' => $id])->get()->getRowArray();
        $this->load->view('backend/admin/badges_edit', $page_data);
    }

    // Endbadges



// Custom Field Start

public function custom_field_add($param2)
{
    $custom_type = $this->request->getPost('custom_type');
    if (!$custom_type) return;

    $course_id    = $param2;
    $custom_title = $this->request->getPost($custom_type . '_custom_title');

    // ================= CHECK EXISTING ROW =================
    $existing = $this->db->table('custom_fields')->where([
        'course_id'    => $course_id,
        'custom_type'  => $custom_type,
        'custom_title' => $custom_title
    ])->get()->getRowArray();

    $existing_items = [];
    $counter = 1;

    if ($existing) {
        $decoded = json_decode($existing['custom_field'], true);
        $existing_items = $decoded['data'] ?? [];

        if (!empty($existing_items)) {
            $ids = array_column($existing_items, 'id');
            $counter = max($ids) + 1; 
        }
    }

    $custom_items = [];

    // ================= IMAGE =================
    if ($custom_type == 'image') {

        if (!file_exists('uploads/custom_fields')) {
            mkdir('uploads/custom_fields', 0777, true);
        }

        foreach ($this->request->getPost('image_title') as $k => $title) {

            $file = '';
            if (!empty($_FILES['image_file']['name'][$k])) {
                $ext  = pathinfo($_FILES['image_file']['name'][$k], PATHINFO_EXTENSION);
                $file = time() . '_' . md5(uniqid()) . '.' . $ext;
                move_uploaded_file(
                    $_FILES['image_file']['tmp_name'][$k],
                    'uploads/custom_fields/' . $file
                );
            }

            $custom_items[] = [
                'id'          => $counter++,
                'title'       => $title,
                'description' => $_POST['image_description'][$k] ?? '',
                'file'        => $file
            ];
        }
    }

    // ================= TEXT =================
    if ($custom_type == 'text') {
        foreach ($this->request->getPost('text_content') as $content) {
            $custom_items[] = [
                'id'          => $counter++,
                'title'       => '',
                'description' => $content,
                'file'        => ''
            ];
        }
    }

    // ================= SLIDER =================
    if ($custom_type == 'slider') {

        if (!file_exists('uploads/custom_fields')) {
            mkdir('uploads/custom_fields', 0777, true);
        }

        foreach ($this->request->getPost('slider_title') as $k => $title) {

            $files = [];

            if (!empty($_FILES['slider_images']['name'][$k])) {
                foreach ($_FILES['slider_images']['name'][$k] as $i => $img) {
                    if (!empty($img)) {
                        $ext  = pathinfo($img, PATHINFO_EXTENSION);
                        $name = time() . '_' . md5(uniqid()) . '.' . $ext;

                        move_uploaded_file(
                            $_FILES['slider_images']['tmp_name'][$k][$i],
                            'uploads/custom_fields/' . $name
                        );

                        $files[] = $name;
                    }
                }
            }

            $custom_items[] = [
                'id'          => $counter++,
                'title'       => $title,
                'description' => $_POST['slider_description'][$k] ?? '',
                'file'        => $files
            ];
        }
    }

    // ================= VIDEO =================
    if ($custom_type == 'video') {
        foreach ($this->request->getPost('video_url') as $url) {
            $custom_items[] = [
                'id'    => $counter++,
                'title' => '',
                'file'  => $url
            ];
        }
    }

    // ================= FAQ =================
    if ($custom_type == 'faq') {
        foreach ($this->request->getPost('faq_question') as $k => $q) {
            $custom_items[] = [
                'id'          => $counter++,
                'title'       => $q,
                'description' => $_POST['faq_answer'][$k] ?? '',
                'file'        => ''
            ];
        }
    }

    // ================= GALLERY =================
    if ($custom_type == 'gallery') {

        if (!file_exists('uploads/custom_fields')) {
            mkdir('uploads/custom_fields', 0777, true);
        }

        foreach ($_FILES['gallery_images']['name'] as $k => $img) {

            $file = '';
            if (!empty($img)) {
                $ext  = pathinfo($img, PATHINFO_EXTENSION);
                $file = time() . '_' . md5(uniqid()) . '.' . $ext;
                move_uploaded_file(
                    $_FILES['gallery_images']['tmp_name'][$k],
                    'uploads/custom_fields/' . $file
                );
            }

            $custom_items[] = [
                'id'          => $counter++,
                'file'        => $file
            ];
        }
    }

    // ================= SAVE =================
    if ($existing) {

        $all_items = array_merge($existing_items, $custom_items);

        $this->db->where('id', $existing['id']);
        $this->db->table('custom_fields')->update([
            'custom_field' => json_encode(['data' => $all_items], JSON_UNESCAPED_UNICODE),
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

    } else {

        $this->db->table('custom_fields')->insert([
            'course_id'    => $course_id,
            'custom_type'  => $custom_type,
            'custom_title' => $custom_title,
            'custom_field' => json_encode(['data' => $custom_items], JSON_UNESCAPED_UNICODE),
            'sorting'      => 0,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

        $insert_id = $this->db->insertID();
        $this->db->where('id', $insert_id)->update('custom_fields', ['sorting' => $insert_id]);
    }

    $this->session->setFlashdata('flash_message', get_phrase('custom_field_added_successfully'));
    redirect(site_url('admin/course_form/course_edit/' . $course_id));
}





public function custom_field_section_update($field_id)
{
    // Fetch existing row
    $field = $this->db->table('custom_fields')->where(['id' => $field_id])->get()->getRowArray();
    if (!$field) {
        $this->session->setFlashdata('error_message', get_phrase('custom_field_not_found'));
        redirect(site_url('admin/course_list'));
    }

    // Get new custom_title from form
    $custom_title = $this->request->getPost('custom_title');

    // Update only custom_title, keep custom_field JSON intact
    $this->db->where('id', $field_id);
    $this->db->table('custom_fields')->update([
        'custom_title' => $custom_title,
        'updated_at'   => date('Y-m-d H:i:s')
    ]);

    $this->session->setFlashdata('flash_message', get_phrase('custom_field_updated_successfully'));
    redirect(site_url('admin/course_form/course_edit/'.$field['course_id']));
}

public function custom_field_section_delete($id)
{
    $field = $this->db->table('custom_fields')->where(['id' => $id])->get()->getRowArray();
    $this->db->where('id', $id);
    $this->db->table('custom_fields')->delete();
    $this->session->setFlashdata('flash_message', get_phrase('custom_field_deleted_successfully'));
    redirect(site_url('admin/course_form/course_edit/' . $field['course_id']));
}

public function custom_field_item_update($field_id, $item_id)
{
    $field = $this->db->table('custom_fields')->where(['id' => $field_id])->get()->getRowArray();
    if (!$field) {
        return;
    }

    $decoded = json_decode($field['custom_field'], true);
    $items   = $decoded['data'] ?? [];

    // loop 
    foreach ($items as &$item) {

        if ($item['id'] == $item_id) {

            // ===== TEXT DATA UPDATE =====
            if ($this->request->getPost('image_title')) {
                $item['title'] = $this->request->getPost('image_title')[0];
            }
            if ($this->request->getPost('image_description')) {
                $item['description'] = $this->request->getPost('image_description')[0];
            }
            if ($this->request->getPost('text_content')) {
                $item['description'] = $this->request->getPost('text_content')[0];
            }
            if ($this->request->getPost('video_url')) {
                $item['file'] = $this->request->getPost('video_url')[0];
            }
            if ($this->request->getPost('faq_question')) {
                $item['title'] = $this->request->getPost('faq_question')[0];
            }
            if ($this->request->getPost('faq_answer')) {
                $item['description'] = $this->request->getPost('faq_answer')[0];
            }

            // ===== IMAGE UPDATE (unlink old if new exists) =====
            if (!empty($_FILES['image_file']['name'][0])) {

                $old_file = $item['file'] ?? null;

                $file_name = time().'_'.$_FILES['image_file']['name'][0];
                $tmp_name  = $_FILES['image_file']['tmp_name'][0];

                move_uploaded_file(
                    $tmp_name,
                    'uploads/custom_fields/'.$file_name
                );

                // old image delete
                if ($old_file && file_exists('uploads/custom_fields/'.$old_file)) {
                    unlink('uploads/custom_fields/'.$old_file);
                }

                $item['file'] = $file_name;
            }

            break;
        }
    }

    // JSON update
    $decoded['data'] = $items;

    $this->db->where('id', $field_id)->update('custom_fields', [
        'custom_field' => json_encode($decoded),
        'updated_at'   => date('Y-m-d H:i:s')
    ]);

    $this->session->setFlashdata('flash_message', get_phrase('custom_field_item_updated_successfully'));
    redirect(site_url('admin/course_form/course_edit/' . $field['course_id']));
}



public function custom_field_item_delete($field_id = '', $item_id = '')
{
    $field = $this->db->table('custom_fields')->where(['id' => $field_id])->get()->getRowArray();

    if (!$field) {
        $this->session->setFlashdata('error', 'Invalid field');
        redirect(site_url('admin/course_form'));
        return;
    }

    $custom_field = json_decode($field['custom_field'], true);

    if (!isset($custom_field['data']) || !is_array($custom_field['data'])) {
        $this->session->setFlashdata('success', 'Item deleted successfully');
        redirect(site_url('admin/course_form/course_edit/' . $field['course_id']));
        return;
    }

    $custom_field['data'] = array_values(array_filter(
        $custom_field['data'],
        function ($item) use ($item_id) {
            return isset($item['id']) && $item['id'] != $item_id;
        }
    ));

    $this->db->where('id', $field_id);
    $this->db->table('custom_fields')->update([
        'custom_field' => json_encode($custom_field)
    ]);

    $this->session->setFlashdata('success', 'Item deleted successfully');
    redirect(site_url('admin/course_form/course_edit/' . $field['course_id']));
}


public function custom_field_section_sort_update()
{
    $order = $this->request->getPost('order');

    if (is_array($order) && count($order) > 0) {
        foreach ($order as $position => $id) {
            $this->db->where('id', $id);
            $this->db->table('custom_fields')->update([
                'sorting' => $position + 1
            ]);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}




// Custom Field End





    // Home page builder
    public function home_page_builder($type = "", $id = "")
    {
        if ($type == 'add') {

            $data['title']        = $this->request->getPost('title');
            $data['description']  = $this->request->getPost('description');
            $data['identifier']   = slugify($this->request->getPost('title'));
            $data['is_permanent'] = 0;
            $data['status']       = 0;

            if ($_FILES['thumbnail']['name'] != "") {
                if (! file_exists('uploads/home-pages')) {
                    mkdir('uploads/home-pages', 0777, true);
                }
                $data['thumbnail'] = 'uploads/home-pages/' . md5(rand(10000000, 20000000)) . '.jpg';
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], $data['thumbnail']);
            }

            $this->db->table('home_pages')->insert($data);
            $this->session->setFlashdata('flash_message', get_phrase('home_page_added_successfully'));
            redirect(site_url('admin/home_page_builder'), 'refresh');
        } elseif ($type == 'update') {

            $data['title']       = $this->request->getPost('title');
            $data['description'] = $this->request->getPost('description');
            $data['identifier']  = slugify($this->request->getPost('title'));

            if ($_FILES['thumbnail']['name'] != "") {
                if (! file_exists('uploads/home-pages')) {
                    mkdir('uploads/home-pages', 0777, true);
                }
                $page_details = $this->db->table('home_pages')->where(['id' => $id])->get()->getRowArray();
                if (file_exists($page_details['thumbnail']) && $page_details['thumbnail']) {
                    unlink($page_details['thumbnail']);
                }
                $data['thumbnail'] = 'uploads/home-pages/' . md5(rand(10000000, 20000000)) . '.jpg';
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], $data['thumbnail']);
            }

            $this->db->where('id', $id);
            $this->db->table('home_pages')->update($data);
            $this->session->setFlashdata('flash_message', get_phrase('home_page_updated_successfully'));
            redirect(site_url('admin/home_page_builder'), 'refresh');
        } elseif ($type == 'delete') {

            $page_details = $this->db->table('home_pages')->where(['id' => $id])->get()->getRowArray();
            if (file_exists($page_details['thumbnail']) && $page_details['thumbnail']) {
                unlink($page_details['thumbnail']);
            }

            $this->db->where('id', $id);
            $this->db->table('home_pages')->delete();
            $this->session->setFlashdata('flash_message', get_phrase('home_page_deleted_successfully'));
            redirect(site_url('admin/home_page_builder'), 'refresh');
        } elseif ($type == 'status') {

            $this->db->where('status', 1);
            $this->db->table('home_pages')->update(['status' => 0]);

            $this->db->where('id', $id);
            $this->db->table('home_pages')->update(['status' => 1]);

            $this->session->setFlashdata('flash_message', get_phrase('home_page_updated_successfully'));
            redirect(site_url('admin/home_page_builder'), 'refresh');
        }

        $page_data['page_title'] = get_phrase('home_page_builder');
        $page_data['page_name']  = 'home_page_builder';
        $this->load->view('backend/index', $page_data);
    }

    public function home_page($type = '', $id = "")
    {
        $page_data['page'] = $this->db->table('home_pages')->where(['id' => $id])->get()->getRowArray();
        $this->load->view('backend/admin/home_page_builder/index', $page_data);
    }

    public function preview_home_page($id = "")
    {
        $page_data['home'] = $this->db->table('home_pages')->where(['id' => $id])->get()->getRowArray();
        $this->load->view('backend/admin/home_page_builder/preview', $page_data);
    }

    public function home_page_layout_update($id = "")
    {
        // $get_developer_elements = $this->developer_file_elements();
        $post_builder_elements = $_POST['builder_elements'];
        // print_r($post_builder_elements);
        // die;

        // dd($post_builder_elements);

        $built_file_names = [];

        foreach ($post_builder_elements as $file_name => $builder_elements) {
            // Skip invalid blocks
            // $arr_values = array_values($builder_elements);
            // if ($arr_values[0]['tag'] == 'null') continue;

            $built_file_names[] = $file_name;

            // Load original developer section
            $file_path      = APPPATH . 'views/components/main/' . $file_name . '.php';
            $developer_html = $this->encode_some_special_characters(file_get_contents($file_path));

            // Protect PHP opening tags (<?php, <?=)
            $developer_html_safe = preg_replace_callback(
                '/<\?(?:php|=)?[\s\S]*?\?>/i',
function ($matches) {
// encode the entire PHP snippet
return '__PHP_OPEN__' . base64_encode($matches[0]) . '__PHP_CLOSE__';
},
$developer_html
);

// ✅ Wrap in dummy container to prevent <html>

    $wrapped_html = '<div id="__temp_wrapper__">' . $developer_html_safe . '</div>';

    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    // $dom->loadHTML(mb_convert_encoding($wrapped_html, 'HTML-ENTITIES', 'UTF-8'));
    $dom->loadHTML('
    <?xml encoding="utf-8" ?>' . $wrapped_html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new \DOMXPath($dom);
    $container = $dom->getElementById('__temp_wrapper__');

    foreach ($builder_elements as $builder_element) {
    $identity = $builder_element['identity'];

    $tag = $this->decodeContent($builder_element['tag']);
    $content = $this->decodeContent($builder_element['content']);
    $src = $this->decodeContent($builder_element['src']);
    $element = $this->decodeContent($builder_element['element']);
    $element_safe = preg_replace_callback(
    '/<\?(?:php|=)?[\s\S]*?\?>/i',
        fn($m) => '__PHP_OPEN__' . base64_encode($m[0]) . '__PHP_CLOSE__',
        $element
        );

        // ✅ Safe fallback support for new + old format
        $dropAreaIndex = $builder_element['dropAreaIndex'] ?? null;
        $droppedIndex = $builder_element['droppedIndex'] ?? null;

        // New nested (hierarchical) structure
        $dropAreaPath = $builder_element['dropAreaPath'] ?? [];
        if (! is_array($dropAreaPath)) {
        $decoded = json_decode($dropAreaPath, true);
        $dropAreaPath = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        // die;

        // Check if element already exists
        $existingNode = $xpath->query("//*[@builder-identity='$identity']")->item(0);
        // print_r($builder_element);
        // die;

        if ($existingNode) {

        // 🟢 Update existing element
        if ($tag === 'img') {
        // handle image src
        $builder_element_src = explode('/uploads/', $src);
        $builder_element_src2 = explode('/assets/', $src);
        if (array_key_exists(1, $builder_element_src)) {
        $updated_url_from_builder = '<?= base_url(); ?>' . 'uploads/' . $builder_element_src[1];
        } elseif (array_key_exists(1, $builder_element_src2)) {
        $updated_url_from_builder = '<?= base_url(); ?>' . 'assets/' . $builder_element_src2[1];
        } else {
        $updated_url_from_builder = '<?= base_url(); ?>' . $src;
        }
        $existingNode->setAttribute('src', $updated_url_from_builder);
        } else {

        // Replace content safely (keep Blade)
        while ($existingNode->firstChild) {
        $existingNode->removeChild($existingNode->firstChild);
        }

        $text = "<?= get_phrase('" . addslashes($content) . "') ?>";
        $textNode = $dom->createTextNode($text);
        $existingNode->appendChild($textNode);

        // while ($existingNode->firstChild) {
        // $existingNode->removeChild($existingNode->firstChild);
        // }

        // // Properly insert raw HTML or PHP snippets
        // $fragment = $dom->createDocumentFragment();
        // $fragment->appendXML($content);
        // $existingNode->appendChild($fragment);
        }
        } else {
        // Create new element
        if ($tag === 'img') {
        // handle image src
        $builder_element_src = explode('/public/', $src);
        if (array_key_exists(1, $builder_element_src)) {
        $updated_url_from_builder = '<?= base_url(); ?>' . $builder_element_src[1];
        } else {
        $updated_url_from_builder = '<?= base_url(); ?>' . $src;
        }
        $newElement = $dom->createElement($tag, $updated_url_from_builder);
        } else {
        // dd($tag);
        $newElement = $dom->createElement($tag, $content);
        }
        // Create new element ended

        // Extract all attributes from $element and add to $newElement
        $tempDom = new DOMDocument();
        libxml_use_internal_errors(true);
        // $tempDom->loadHTML(mb_convert_encoding($element, 'HTML-ENTITIES', 'UTF-8'));
        $tempDom->loadHTML('
        <?xml encoding="utf-8" ?>' . $element, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();
        $tempXpath = new DOMXPath($tempDom);
        $tempNode = $tempXpath->query('/*')->item(0) ?? $tempXpath->query('/html/body/*')->item(0);

        // print_r($tempNode);

        if ($tempNode !== null) {
        foreach ($tempNode->attributes ?? [] as $attr) {
        $newElement->setAttribute($attr->nodeName, $attr->nodeValue);
        }
        }
        // Extract all attributes from $element and add to $newElement ended

        // Insert the new element at the specific position
        if ($container) {
        $dropAreaNode = 0;
        $dropAreas = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' drop-area ')]", $container);

        if ($dropAreas->length > $dropAreaIndex) {
        $dropAreaNode = $dropAreas->item($dropAreaIndex);
        }
        if ($dropAreaNode) {
        // Insert at specific position
        $children = [];
        foreach ($dropAreaNode->childNodes as $child) {
        if ($child->nodeType === XML_ELEMENT_NODE) {
        $children[] = $child;
        }
        }
        $newElement = $dom->importNode($newElement, true);

        // if the index not exists the insert to the end
        if (! isset($children[$droppedIndex])) {
        $dropAreaNode->appendChild($newElement);
        } else {
        // print_r($newElement->attributes);
        $dropAreaNode->insertBefore($newElement, $children[$droppedIndex]);
        }
        }
        }

        // Insert the new element at the specific position ended
        }
        }

        // ✅ Extract only real HTML (skip <html>

            $container = $dom->getElementById('__temp_wrapper__');

            // ✅ Extract only real HTML (skip <html>

                $newHtml = '';
                foreach ($container->childNodes ?? [] as $child) {
                // Only nodes that belong to $dom
                $newHtml .= $dom->saveHTML($child);
                }

                // Restore PHP code
                $newHtml = preg_replace_callback('/__PHP_OPEN__(.*?)__PHP_CLOSE__/s', function ($matches) {
                return base64_decode($matches[1]);
                }, $newHtml);

                // ✅ Decode Blade-safe content (restore {{ }}, ->, etc.)
                $newHtml = html_entity_decode($newHtml, ENT_QUOTES | ENT_HTML5);
                $newHtml = $this->decode_some_special_characters(urldecode($newHtml));

                // ✅ Save cleaned section (no <html>

                    file_put_contents(
                    APPPATH . "views/components/builder/" . $id . '-' . $file_name . '.php',
                    $newHtml
                    );
                    }

                    // Update database with built section list
                    $this->db->where('id', $id);
                    $this->db->table('home_pages')->update(['html_file_names' => json_encode($built_file_names)]);

                    // $this->session->setFlashdata('flash_message', get_phrase('data_added_successfully'));
                    // redirect(site_url('admin/home_page/builder/' . $id), 'refresh');
                    echo 'Layout Updated';
                    }

                    public function encode_some_special_characters($content = "")
                    {
                    if ($content == "") {
                    return;
                    }

                    // $content = preg_replace('/&/', '&amp;', $content);
                    // $content = preg_replace('/"/', '&quot;', $content);
                    // $content = preg_replace('/</', '&lt;' , $content); // $content=preg_replace(' />/', '&gt;', $content);
                    // return $content;

                    $content = str_replace("&", "__apmsign_amp__", $content);
                    $content = str_replace('+', "__plussign_plus__", $content);

                    return $content;
                    }
                    public function decode_some_special_characters($content = "")
                    {
                    if ($content == "") {
                    return;
                    }

                    // $content = preg_replace('/&/', '&amp;', $content);
                    // $content = preg_replace('/"/', '&quot;', $content);
                    // $content = preg_replace('/</', '&lt;' , $content); // $content=preg_replace(' />/', '&gt;', $content);
                    // return $content;

                    $content = str_replace("__apmsign_amp__", "&", $content);
                    $content = str_replace('__plussign_plus__', "+", $content);

                    return $content;
                    }

                    public function decodeContent($content = "")
                    {
                    if (! $content || empty($content) || $content == 'null') {
                    return "null";
                    }

                    return urldecode(htmlspecialchars_decode(base64_decode($content)));
                    }

                    public function upload_page_builder_image()
                    {

                    if (! file_exists('uploads/home-pages')) {
                    mkdir('uploads/home-pages', 0777, true);
                    }

                    if ($_POST['remove_file']) {
                    $remove_file_arr = explode('/uploads/', $_POST['remove_file']);
                    if (isset($remove_file_arr[1]) && file_exists('uploads/' . $remove_file_arr[1])) {
                    unlink('uploads/' . $remove_file_arr[1]);
                    }
                    }

                    $image = 'uploads/home-pages/' . md5(rand(10000000, 20000000)) . '.png';
                    move_uploaded_file($_FILES['file']['tmp_name'], $image);
                    echo base_url($image);
                    }

                    public function developer_file_elements()
                    {
                    $developer_file_elements = [];

                    $componentPath = APPPATH . 'views/components/main';
                    $files = array_diff(scandir($componentPath), ['.', '..']);

                    foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                    continue;
                    }
                    // only .blade.php files

                    $fileName = str_replace('.blade', '', pathinfo($file, PATHINFO_FILENAME));
                    $filePath = $componentPath . '/' . $file;

                    $html = file_get_contents($filePath);

                    // Load HTML into DOM
                    $dom = new DOMDocument();
                    libxml_use_internal_errors(true);
                    // $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                    $dom->loadHTML('
                    <?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                    libxml_clear_errors();

                    $xpath = new DOMXPath($dom);
                    $nodes = $xpath->query('//*[@builder-identity]');

                    $elements = [];

                    foreach ($nodes as $node) {
                    $identity = $node->getAttribute('builder-identity');
                    $tag = $node->nodeName;

                    // Get full element HTML
                    $elementHTML = $dom->saveHTML($node);

                    // Detect src (for images)
                    $src = $node->hasAttribute('src') ? $node->getAttribute('src') : null;

                    // Get inner content (without wrapping tag)
                    $content = '';
                    foreach ($node->childNodes as $child) {
                    $content .= $dom->saveHTML($child);
                    }

                    // 🔹 Get selector path
                    $selector = $this->getDomSelectorPath($node);

                    $elements[$identity] = [
                    'element' => $elementHTML,
                    'tag' => $tag,
                    'identity' => $identity,
                    'selector' => $selector,
                    'content' => $content ? $content : null,
                    'src' => $src ? $src : null,
                    ];
                    }

                    $developer_file_elements[$fileName] = $elements;
                    }

                    return $developer_file_elements;
                    }

                    public function getDomSelectorPath(DOMNode $node)
                    {
                    $path = [];

                    while ($node && $node->nodeType === XML_ELEMENT_NODE && $node->nodeName !== 'html') {
                    $tag = strtolower($node->nodeName);

                    // Find position among siblings of same tag
                    $index = 1;
                    $sibling = $node->previousSibling;
                    while ($sibling) {
                    if ($sibling->nodeType === XML_ELEMENT_NODE && $sibling->nodeName === $node->nodeName) {
                    $index++;
                    }
                    $sibling = $sibling->previousSibling;
                    }

                    // Add nth-of-type if needed
                    $selector = $tag . ($index > 1 ? ":nth-of-type($index)" : '');
                    array_unshift($path, $selector);

                    $node = $node->parentNode;
                    }

                    return 'html > ' . implode(' > ', $path);
                    }
                    // Home page builder

                    }







