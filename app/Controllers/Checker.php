<?php

namespace App\Controllers;

/*
 *  @author     : Creativeitem
 *  date        : 14 september, 2017
 *  Specification    :    Mobile app response, JSON formatted data for iOS & android app
 *  Ekattor School Management System Pro
 *  http://codecanyon.net/user/Creativeitem
 *  http://support.creativeitem.com
 */
class Mobile extends BaseController
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        //Authenticate data manipulation with the user level security key
        if ($this->validate_auth_key() != 'success')
            die;
    }
    // response of class list
    function get_class()
    {
        $response = array();
        $classes  = $this->db->table('class')->get()->getResultArray();
        foreach ($classes as $row) {
            $data['class_id']     = $row['class_id'];
            $data['name']         = $row['name'];
            $data['name_numeric'] = $row['name_numeric'];
            $data['teacher_id']   = $row['teacher_id'];
            $sections             = $this->db->table('section')->where(array(
                'class_id' => $row['class_id']
            )->get())->getResultArray();
            $data['sections']     = $sections;
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    // returns image of user, returns blank image if not found.
    function get_image_url($type = '', $id = '')
    {
        $type     = $this->request->getPost('user_type');
        $id       = $this->request->getPost('user_id');
        $response = array();
        if (file_exists('uploads/' . $type . '_image/' . $id . '.jpg'))
            $response['image_url'] = base_url() . 'uploads/' . $type . '_image/' . $id . '.jpg';
        else
            $response['image_url'] = base_url() . 'uploads/user.jpg';
        echo json_encode($response);
    }
    // returns system name and logo as public call
    function get_system_info()
    {
        $response['system_name'] = $this->db->table('settings')->where(array(
            'type' => 'system_name'
        )->get())->getRow()->description;
        echo json_encode($response);
    }
    // returns the students of a specific class according to requested class_id
    // ** class_id, year required to get students from enroll table
    function get_students_of_class()
    {
        $response     = array();
        $class_id     = $this->request->getPost('class_id');
        $running_year = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $students     = $this->db->table('enroll')->where(array(
            'class_id' => $class_id,
            'year' => $running_year
        )->get())->getResultArray();
        foreach ($students as $row) {
            $data['student_id']  = $row['student_id'];
            $data['roll']        = $row['roll'];
            $data['name']        = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->name;
            $data['birthday']    = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->birthday;
            $data['gender']      = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->sex;
            $data['address']     = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->address;
            $data['phone']       = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->phone;
            $data['email']       = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->email;
            $data['class']       = $this->db->table('class')->where(array(
                'class_id' => $row['class_id']
            )->get())->getRow()->name;
            $data['section']     = $this->db->table('section')->where(array(
                'section_id' => $row['section_id']
            )->get())->getRow()->name;
            $parent_id           = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->parent_id;
            $data['parent_name'] = $this->db->table('parent')->where(array(
                'parent_id' => $parent_id
            )->get())->getRow()->name;
            $data['image_url']   = $this->crud_model->get_image_url('student', $row['student_id']);
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    // get students basic info
    function get_student_profile_information()
    {
        $response        = array();
        $running_year    = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $student_id      = $this->request->getPost('student_id');
        $roll            = $this->db->table('enroll')->where(array(
            'student_id' => $student_id,
            'year' => $running_year
        )->get())->getRow()->roll;
        $class_id        = $this->db->table('enroll')->where(array(
            'student_id' => $student_id,
            'year' => $running_year
        )->get())->getRow()->class_id;
        $section_id      = $this->db->table('enroll')->where(array(
            'student_id' => $student_id,
            'year' => $running_year
        )->get())->getRow()->section_id;
        $student_profile = $this->db->table('student')->where(array(
            'student_id' => $student_id
        )->get())->getResultArray();
        foreach ($student_profile as $row) {
            $data['student_id']  = $row['student_id'];
            $data['name']        = $row['name'];
            $data['birthday']    = $row['birthday'];
            $data['gender']      = $row['sex'];
            $data['address']     = $row['address'];
            $data['phone']       = $row['phone'];
            $data['email']       = $row['email'];
            $data['roll']        = $roll;
            $data['class']       = $class_id;
            $data['section']     = $section_id;
            $data['parent_name'] = $this->db->table('parent')->where(array(
                'parent_id' => $row['parent_id']
            )->get())->getRow()->name;
            $data['image_url']   = $this->crud_model->get_image_url('student', $row['student_id']);
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    // get student's mark info
    // ** exam_id, student_id, year required to get students from mark table
    function get_student_mark_information()
    {
        $response            = array();
        $mark_array          = array();
        $exam_id             = $this->request->getPost('exam_id');
        $student_id          = $this->request->getPost('student_id');
        $running_year        = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $student_marks       = $this->db->table('mark')->where(array(
            'exam_id' => $exam_id,
            'student_id' => $student_id,
            'year' => $running_year
        )->get())->getResultArray();
        $response['exam_id'] = $exam_id;
        foreach ($student_marks as $row) {
            $data['mark_obtained'] = $row['mark_obtained'];
            $data['subject']       = $this->db->table('subject')->where(array(
                'subject_id' => $row['subject_id'],
                'year' => $running_year
            )->get())->getRow()->name;
            $grade                 = $this->crud_model->get_grade($row['mark_obtained']);
            $data['grade']         = $grade['name'];
            array_push($mark_array, $data);
        }
        $response['marks'] = $mark_array;
        echo json_encode($response);
    }
    // teacher list of the school
    function get_teachers()
    {
        $response = array();
        $teachers = $this->db->table('teacher')->get()->getResultArray();
        foreach ($teachers as $row) {
            $data['teacher_id'] = $row['teacher_id'];
            $data['name']       = $row['name'];
            $data['birthday']   = $row['birthday'];
            $data['gender']     = $row['sex'];
            $data['address']    = $row['address'];
            $data['phone']      = $row['phone'];
            $data['email']      = $row['email'];
            $data['image_url']  = $this->crud_model->get_image_url('teacher', $row['teacher_id']);
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    // teacher profile information
    function get_teacher_profile()
    {
        $response   = array();
        $teacher_id = $this->request->getPost('teacher_id');
        $response   = $this->db->table('teacher')->where(array(
            'teacher_id' => $teacher_id
        )->get())->getRow();
        echo json_encode($response);
    }
    // get parent list
    function get_parents()
    {
        $response = array();
        $parents  = $this->db->table('parent')->get()->getResultArray();
        foreach ($parents as $row) {
            $data['parent_id']  = $row['parent_id'];
            $data['name']       = $row['name'];
            $data['profession'] = $row['profession'];
            $data['address']    = $row['address'];
            $data['phone']      = $row['phone'];
            $data['email']      = $row['email'];
            $data['image_url']  = $this->crud_model->get_image_url('parent', $row['parent_id']);
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    // get single parent profile
    function get_parent_profile()
    {
        $response  = array();
        $parent_id = $this->request->getPost('parent_id');
        $response  = $this->db->table('parent')->where(array(
            'parent_id' => $parent_id
        )->get())->getRow();
        echo json_encode($response);
    }
    // income or expense history of school of submitted month
    function get_accounting()
    {
        $response        = array();
        $month           = $this->request->getPost('month');
        $year            = $this->request->getPost('year');
        $type            = $this->request->getPost('type');
        $start_timestamp = strtotime("1-" . $month . "-" . $year);
        $end_timestamp   = strtotime("30-" . $month . "-" . $year);
        $this->db->where("timestamp >=", $start_timestamp);
        $this->db->where("timestamp <=", $end_timestamp);
        $this->db->where("payment_type", $type);
        $response = $this->db->table('payment')->get()->getResultArray();
        echo json_encode($response);
    }
    // attendance data response
    // ** timestamp, year, class_id, section_id, student_id to get attendance from attendance table
    function get_attendance()
    {
        $response     = array();
        $date         = $this->request->getPost('date');
        $month        = $this->request->getPost('month');
        $year         = $this->request->getPost('year');
        $class_id     = $this->request->getPost('class_id');
        $timestamp    = strtotime($date . '-' . $month . '-' . $year);
        $running_year = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $students     = $this->db->table('enroll')->where(array(
            'class_id' => $class_id,
            'year' => $running_year
        )->get())->getResultArray();
        foreach ($students as $row) {
            $data['student_id'] = $row['student_id'];
            $data['roll']       = $row['roll'];
            $data['name']       = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->name;
            $attendance_query   = $this->db->table('attendance')->where(array(
                'timestamp' => $timestamp,
                'student_id' => $row['student_id']
            )->get());
            if ($attendance_query->getNumRows() > 0) {
                $attendance_result_row = $attendance_query->getRow();
                $data['status']        = $attendance_result_row->status;
            } else {
                $data['status'] = '0';
            }
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    // class routine : class and weekly day wise
    // ** class_id, section_id, subject_id, year to get section wise class routine from class_routine table
    function get_class_routine()
    {
        $response       = array();
        $class_id       = $this->request->getPost('class_id');
        $section_id     = $this->request->getPost('section_id');
        $day            = $this->request->getPost('day');
        $running_year   = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $class_routines = $this->db->table('class_routine')->where(array(
            'class_id' => $class_id,
            'section_id' => $section_id,
            'day' => $day,
            'year' => $running_year
        )->get())->getResultArray();
        foreach ($class_routines as $row) {
            $data['class_id']       = $row['class_id'];
            $data['subject']        = $this->db->table('subject')->where(array(
                'subject_id' => $row['subject_id'],
                'year' => $running_year
            )->get())->getRow()->name;
            $data['time_start']     = $row['time_start'];
            $data['time_end']       = $row['time_end'];
            $data['time_start_min'] = $row['time_start_min'];
            $data['time_end_min']   = $row['time_end_min'];
            $data['day']            = $row['day'];
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    // get subject name of subject_id
    function get_subject_name()
    {
        $response   = array();
        $subject_id = $this->request->getPost('subject_id');
        $response   = $this->db->table('subject')->where(array(
            'subject_id' => $subject_id
        )->get())->getRow();
        echo json_encode($response);
    }
    // event calendar or noticeboard event list
    function get_event_calendar()
    {
        $response = array();
        $response = $this->db->table('noticeboard')->get()->getResultArray();
        echo json_encode($response);
    }
    // exam list
    // **  year required to get exam list from exam table
    function get_exam_list()
    {
        $running_year = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $response     = array();
        $response     = $this->db->table('exam')->where(array(
            'year' => $running_year
        )->get())->getResultArray();
        echo json_encode($response);
    }
    // get subjects of a class
    // ** class_id, year required to get subjects of a class from subject table
    function get_subject_of_class()
    {
        $response     = array();
        $class_id     = $this->request->getPost('class_id');
        $running_year = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $subjects     = $this->db->table('subject')->where(array(
            'class_id' => $class_id,
            'year' => $running_year
        )->get())->getResultArray();
        foreach ($subjects as $row) {
            $data['subject_id'] = $row['subject_id'];
            $data['name']       = $row['name'];
            $teacher_query      = $this->db->table('teacher')->where(array(
                'teacher_id' => $row['teacher_id']
            )->get());
            if ($teacher_query->getNumRows() > 0) {
                $teacher_query_row    = $teacher_query->getRow();
                $data['teacher_name'] = $teacher_query_row->name;
            } else {
                $data['teacher_name'] = '';
            }
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    // student mark list, subject, class, exam wise
    // ** exam_id, class_id, subject_id, year required to get student wise marks
    function get_marks()
    {
        $response     = array();
        $exam_id      = $this->request->getPost('exam_id');
        $class_id     = $this->request->getPost('class_id');
        $subject_id   = $this->request->getPost('subject_id');
        $running_year = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $marks        = $this->db->table('mark')->where(array(
            'exam_id' => $exam_id,
            'class_id' => $class_id,
            'subject_id' => $subject_id,
            'year' => $running_year
        )->get())->getResultArray();
        foreach ($marks as $row) {
            $data['class_id']      = $row['class_id'];
            $data['student_id']    = $row['student_id'];
            $data['student_name']  = $this->db->table('student')->where(array(
                'student_id' => $row['student_id']
            )->get())->getRow()->name;
            $data['student_roll']  = $this->db->table('enroll')->where(array(
                'student_id' => $row['student_id'],
                'year' => $running_year
            )->get())->getRow()->roll;
            $data['exam_id']       = $row['exam_id'];
            $data['mark_obtained'] = $row['mark_obtained'];
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    function get_loggedin_user_profile()
    {
        $response      = array();
        $login_type    = $this->request->getPost('login_type');
        $login_user_id = $this->request->getPost('login_user_id');
        $user_profile  = $this->db->get_where($login_type, array(
            $login_type . '_id' => $login_user_id
        ))->getResultArray();
        foreach ($user_profile as $row) {
            $data['name']      = $row['name'];
            $data['email']     = $row['email'];
            $data['image_url'] = $this->crud_model->get_image_url($login_type, $login_user_id);
            break;
        }
        array_push($response, $data);
        echo json_encode($response);
    }
    function update_user_image()
    {
        $response  = array();
        $user_type = $this->request->getPost('login_type');
        $user_id   = $this->request->getPost('login_user_id');
        $directory = 'uploads/' . $user_type . '_image/' . $user_id . '.jpg';
        move_uploaded_file($_FILES['user_image']['tmp_name'], $directory);
        $response = array(
            'update_status' => 'success'
        );
        echo json_encode($response);
    }
    function update_user_info()
    {
        $response      = array();
        $user_type     = $this->request->getPost('login_type');
        $user_id       = $this->request->getPost('login_user_id');
        $data['name']  = $this->request->getPost('name');
        $data['email'] = $this->request->getPost('email');
        $this->db->where($user_type . '_id', $user_id);
        $this->db->update($user_type, $data);
        $response = array(
            'update_status' => 'success'
        );
        echo json_encode($response);
    }
    function update_user_password()
    {
        $response         = array();
        $user_type        = $this->request->getPost('login_type');
        $user_id          = $this->request->getPost('login_user_id');
        $old_password     = sha1($this->request->getPost('old_password'));
        $data['password'] = sha1($this->request->getPost('new_password'));
        // verify if old password matches
        $this->db->where($user_type . '_id', $user_id);
        $this->db->where('password', $old_password);
        $verify_query = $this->db->get($user_type);
        if ($verify_query->getNumRows() > 0) {
            $this->db->where($user_type . '_id', $user_id);
            $this->db->update($user_type, $data);
            $response = array(
                'update_status' => 'success'
            );
        } else {
            $response = array(
                'update_status' => 'failed'
            );
        }
        echo json_encode($response);
    }
    // total number of students
    // ** year required to get total student from enrollment table
    // ** timestamp, status required to get todays present students from student table
    function get_total_summary()
    {
        $response     = array();
        $running_year = $this->db->table('settings')->where(array(
            'type' => 'running_year'
        )->get())->getRow()->description;
        $this->db->where('year', $running_year);
        $this->db->from('enroll');
        $response['total_student']       = $this->db->count_all_results();
        $response['total_teacher']       = $this->db->count_all('teacher');
        $response['total_parent']        = $this->db->count_all('parent');
        // student present today
        $check                           = array(
            'timestamp' => strtotime(date('d-m-Y')),
            'status' => '1'
        );
        $query                           = $this->db->table('attendance')->where($check)->get();
        $present_today                   = $query->getNumRows();
        $response['total_present_today'] = $present_today;
        echo json_encode($response);
    }
    // dummy function
    function getdata()
    {
        $response = array();
        $postvar  = $this->request->getPost('postvar');
        $response = $this->db->table('table')->where(array(
            'postvar' => $postvar
        )->get())->getResultArray();
        echo json_encode($response);
    }
    // Parents functions : own child list, class routine, exam marks of child, invoice of child, event schedule
    function get_children_of_parent()
    {
        $response             = array();
        $parent_id            = $this->request->getPost('parent_id');
        $response['children'] = $this->db->table('student')->where(array(
            'parent_id' => $parent_id
        )->get())->getResultArray();
        echo json_encode($response);
    }
    function get_child_class_routine()
    {
    }
    function get_child_exam_marks()
    {
    }
    function get_child_accounting()
    {
    }
    // Students functions : own child list, class routine, exam marks of child, invoice of child, event schedule
    function get_own_subjects()
    {
    }
    function get_own_class_routine()
    {
    }
    function get_own_marks()
    {
    }
    function get_single_student_accounting()
    {
        $response   = array();
        $student_id = $this->request->getPost("student_id");
        $this->db->where("student_id", $student_id);
        $response = $this->db->table('invoice')->get()->getResultArray();
        echo json_encode($response);
    }
    // user login matching with db
    function login()
    {
        $response = array();
        $email    = $this->request->getPost("email");
        $password = sha1($this->request->getPost("password"));
        // Checking login credential for admin
        $query    = $this->db->table('admin')->where(array(
            'email' => $email,
            'password' => $password
        )->get());
        if ($query->getNumRows() > 0) {
            $row                            = $query->getRow();
            $authentication_key             = md5(rand(10000, 1000000));
            $response['status']             = 'success';
            $response['login_type']         = 'admin';
            $response['login_user_id']      = $row->admin_id;
            $response['name']               = $row->name;
            $response['authentication_key'] = $authentication_key;
            // update the new authentication key into user table
            $this->db->where('admin_id', $row->admin_id);
            $this->db->table('admin')->update(array(
                'authentication_key' => $authentication_key
            ));
            echo json_encode($response);
            return;
        }
        // Checking login credential for teacher
        $query = $this->db->table('teacher')->where(array(
            'email' => $email,
            'password' => $password
        )->get());
        if ($query->getNumRows() > 0) {
            $row                            = $query->getRow();
            $authentication_key             = md5(rand(10000, 1000000));
            $response['status']             = 'success';
            $response['login_type']         = 'teacher';
            $response['login_user_id']      = $row->teacher_id;
            $response['name']               = $row->name;
            $response['authentication_key'] = $authentication_key;
            // update the new authentication key into user table
            $this->db->where('teacher_id', $row->teacher_id);
            $this->db->table('teacher')->update(array(
                'authentication_key' => $authentication_key
            ));
            echo json_encode($response);
            return;
        }
        // Checking login credential for student
        $query = $this->db->table('student')->where(array(
            'email' => $email,
            'password' => $password
        )->get());
        if ($query->getNumRows() > 0) {
            $running_year                   = $this->db->table('settings')->where(array(
                'type' => 'running_year'
            )->get())->getRow()->description;
            $row                            = $query->getRow();
            $authentication_key             = md5(rand(10000, 1000000));
            $response['status']             = 'success';
            $response['login_type']         = 'student';
            $response['login_user_id']      = $row->student_id;
            $response['name']               = $row->name;
            $response['authentication_key'] = $authentication_key;
            $response['class_id']           = $this->db->table('enroll')->where(array(
                'student_id' => $row->student_id,
                'year' => $running_year
            )->get())->getRow()->class_id;
            $response['section_id']         = $this->db->table('enroll')->where(array(
                'student_id' => $row->student_id,
                'year' => $running_year
            )->get())->getRow()->section_id;
            // update the new authentication key into user table
            $this->db->where('student_id', $row->student_id);
            $this->db->table('student')->update(array(
                'authentication_key' => $authentication_key
            ));
            echo json_encode($response);
            return;
        }
        // Checking login credential for parent
        $query = $this->db->table('parent')->where(array(
            'email' => $email,
            'password' => $password
        )->get());
        if ($query->getNumRows() > 0) {
            $row                            = $query->getRow();
            $authentication_key             = md5(rand(10000, 1000000));
            $response['status']             = 'success';
            $response['login_type']         = 'parent';
            $response['login_user_id']      = $row->parent_id;
            $response['name']               = $row->name;
            $response['authentication_key'] = $authentication_key;
            $response['children']           = $this->db->table('student')->where(array(
                'parent_id' => $row->parent_id
            )->get())->getResultArray();
            // update the new authentication key into user table
            $this->db->where('parent_id', $row->parent_id);
            $this->db->table('parent')->update(array(
                'authentication_key' => $authentication_key
            ));
            echo json_encode($response);
            return;
        } else {
            $response['status'] = 'failed';
        }
        echo json_encode($response);
    }
    // forgot password link
    function reset_password()
    {
        $response           = array();
        $response['status'] = 'false';
        $email              = $_POST["email"];
        $reset_account_type = '';
        //resetting user password here
        $new_password       = substr(rand(100000000, 20000000000), 0, 7);
        // Checking credential for admin
        $query              = $this->db->table('admin')->where(array(
            'email' => $email
        )->get());
        if ($query->getNumRows() > 0) {
            $reset_account_type = 'admin';
            $this->db->where('email', $email);
            $this->db->table('admin')->update(array(
                'password' => sha1($new_password)
            ));
            $response['status'] = 'true';
        }
        // Checking credential for student
        $query = $this->db->table('student')->where(array(
            'email' => $email
        )->get());
        if ($query->getNumRows() > 0) {
            $reset_account_type = 'student';
            $this->db->where('email', $email);
            $this->db->table('student')->update(array(
                'password' => sha1($new_password)
            ));
            $response['status'] = 'true';
        }
        // Checking credential for teacher
        $query = $this->db->table('teacher')->where(array(
            'email' => $email
        )->get());
        if ($query->getNumRows() > 0) {
            $reset_account_type = 'teacher';
            $this->db->where('email', $email);
            $this->db->table('teacher')->update(array(
                'password' => sha1($new_password)
            ));
            $response['status'] = 'true';
        }
        // Checking credential for parent
        $query = $this->db->table('parent')->where(array(
            'email' => $email
        )->get());
        if ($query->getNumRows() > 0) {
            $reset_account_type = 'parent';
            $this->db->where('email', $email);
            $this->db->table('parent')->update(array(
                'password' => sha1($new_password)
            ));
            $response['status'] = 'true';
        }
        // send new password to user email
        $this->email_model->password_reset_email($new_password, $reset_account_type, $email);
        echo json_encode($response);
    }
    function get_notices()
    {
        $response = array();
        $query    = $this->db->table(\"noticeboard\")->get()->getResultArray();
        foreach ($query as $row) {
            $data['notice_id']    = $row['notice_id'];
            $data['notice_title'] = $row['notice_title'];
            $data['notice']       = $row['notice'];
            $data['date']         = date('d-M-Y', $row['create_timestamp']);
            array_push($response, $data);
        }
        echo json_encode($response);
    }

    // private messaging
    // @ $user -> user_type-user_id -> admin-1
    function get_message_threads() {
        $response = array();
        $user = $this->request->getPost('user');
        $this->db->where('sender', $user);
        $this->db->orWhere('reciever', $user);
        $threads = $this->db->table('message_thread')->get()->getResultArray();
        foreach ($threads as $row) {
            $sender   = explode('-', $row['sender']);
            $receiver = explode('-', $row['reciever']);
            $sender_name = $this->db->get_where($sender[0], array($sender[0].'_id' => $sender[1]))->getRow()->name;
            $receiver_name = $this->db->get_where($receiver[0], array($receiver[0].'_id' => $receiver[1]))->getRow()->name;
            $user_type = ($user == $row['sender']) ? $receiver[0] : $sender[0];
            $user_name = ($user == $row['sender']) ? $receiver_name : $sender_name;
            $user_id = ($user == $row['sender']) ? $receiver[1] : $sender[1];
            if (file_exists('uploads/'.$user_type.'_image/'.$user_id.'.jpg'))
                $image_url = base_url('uploads/'.$user_type.'_image/'.$user_id.'.jpg');
            else
                $image_url = base_url('uploads/user.jpg');
            $data['message_thread_code']    =   $row['message_thread_code'];
            $data['user_type']              =   $user_type;
            $data['user_name']              =   $user_name;
            $data['image_url']              =   $image_url;
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    function get_messages() {
        $response = array();
        $message_thread_code = $this->request->getPost('message_thread_code');
        $this->db->where('message_thread_code', $message_thread_code);
        $this->db->orderBy('timestamp', 'asc');
        $messages = $this->db->table('message')->get()->getResultArray();
        foreach ($messages as $row) {
            $sender = explode('-', $row['sender']);
            $sender_name = $this->db->get_where($sender[0], array($sender[0].'_id' => $sender[1]))->getRow()->name;
            $data['sender']         =   $row['sender'];
            $data['sender_type']    =   $sender[0];
            $data['sender_id']      =   $sender[1];
            $data['sender_name']    =   $sender_name;
            $data['message']        =   $row['message'];
            $data['date']           =   date('d M, Y', $row['timestamp']);
            array_push($response, $data);
        }
        echo json_encode($response);
    }
    function get_receivers() {
        $student_array = array();
        $teacher_array = array();
        $parent_array = array();
        $admin_array = array();
        $response = array();
        $for_user = $this->request->getPost('for_user');
        $for_user = explode('-', $for_user);
        $type = $for_user[0];
        // students
        $this->db->orderBy('name', 'asc');
        $students = $this->db->table('student')->get()->getResultArray();
        foreach ($students as $row) {
            $data['id'] =   $row['student_id'];
            $data['type'] =   'student';
            $data['name'] =   $row['name'];
            array_push($student_array, $data);
        }
        // teachers
        $this->db->orderBy('name', 'asc');
        $teachers = $this->db->table('teacher')->get()->getResultArray();
        foreach ($teachers as $row) {
            $data['id'] =   $row['teacher_id'];
            $data['type'] =   'teacher';
            $data['name'] =   $row['name'];
            array_push($teacher_array, $data);
        }
        // parents
        $this->db->orderBy('name', 'asc');
        $parents = $this->db->table('parent')->get()->getResultArray();
        foreach ($parents as $row) {
            $data['id'] =   $row['parent_id'];
            $data['type'] =   'parent';
            $data['name'] =   $row['name'];
            array_push($parent_array, $data);
        }
        // admins
        $this->db->orderBy('name', 'asc');
        $admins = $this->db->table('admin')->get()->getResultArray();
        foreach ($admins as $row) {
            $data['id'] =   $row['admin_id'];
            $data['type'] =   'admin';
            $data['name'] =   $row['name'];
            array_push($admin_array, $data);
        }
        if ($type == 'admin') {
            $response = array_merge($teacher_array, $parent_array, $student_array);
            echo json_encode($response);
        } else if ($type == 'teacher') {
            $response = array_merge($admin_array, $parent_array, $student_array);
            echo json_encode($response);
        } else if ($type == 'student') {
            $response = array_merge($admin_array, $teacher_array);
            echo json_encode($response);
        } else {
            $response = array_merge($admin_array, $teacher_array);
            echo json_encode($response);
        }
    }
    function send_new_message() {
        $response   =   array();
        $message    =   $this->request->getPost('message');
        $receiver   =   $this->request->getPost('receiver');
        $sender     =   $this->request->getPost('sender');
        $timestamp  =   strtotime(date("Y-m-d H:i:s"));
        //check if the thread between those 2 users exists, if not create new thread
        $num1 = $this->db->table('message_thread')->where(array('sender' => $sender, 'reciever' => $receiver)->get())->getNumRows();
        $num2 = $this->db->table('message_thread')->where(array('sender' => $receiver, 'reciever' => $sender)->get())->getNumRows();
        if ($num1 == 0 && $num2 == 0) {
            $message_thread_code                        = substr(md5(rand(100000000, 20000000000)), 0, 15);
            $data_message_thread['message_thread_code'] = $message_thread_code;
            $data_message_thread['sender']              = $sender;
            $data_message_thread['reciever']            = $receiver;
            $this->db->table('message_thread')->insert($data_message_thread);
        }
        if ($num1 > 0)
            $message_thread_code = $this->db->table('message_thread')->where(array('sender' => $sender, 'reciever' => $receiver)->get())->getRow()->message_thread_code;
        if ($num2 > 0)
            $message_thread_code = $this->db->table('message_thread')->where(array('sender' => $receiver, 'reciever' => $sender)->get())->getRow()->message_thread_code;
        $data_message['message_thread_code']    = $message_thread_code;
        $data_message['message']                = $message;
        $data_message['sender']                 = $sender;
        $data_message['timestamp']              = $timestamp;
        $this->db->table('message')->insert($data_message);
        $data['message_thread_code']    =   $message_thread_code;
        array_push($response, $data);
        echo json_encode($response);
    }
    function send_reply() {
        $message_thread_code    =   $this->request->getPost('message_thread_code');
        $message                =   $this->request->getPost('message');
        $timestamp              =   strtotime(date("Y-m-d H:i:s"));
        $sender                 =   $this->request->getPost('sender');

        $data_message['message_thread_code']    = $message_thread_code;
        $data_message['message']                = $message;
        $data_message['sender']                 = $sender;
        $data_message['timestamp']              = $timestamp;
        $this->db->table('message')->insert($data_message);
        $data['message_thread_code']    =   $message_thread_code;
        echo 'success';
    }

    // authentication_key validation
    function validate_auth_key()
    {
        /*
         * Ignore the authentication and returns success by default to constructor
         * For pubic calls: login, forget password.
         * Pass post parameter 'authenticate' = 'false' to ignore the user level authentication
         */
        if ($this->request->getPost('authenticate') == 'false')
            return 'success';
        $response           = array();
        $authentication_key = $this->request->getPost("authentication_key");
        $user_type          = $this->request->getPost("user_type");
        $query              = $this->db->get_where($user_type, array(
            'authentication_key' => $authentication_key
        ));
        if ($query->getNumRows() > 0) {
            $row                    = $query->getRow();
            $response['status']     = 'success';
            $response['login_type'] = 'admin';
            if ($user_type == 'admin')
                $response['login_user_id'] = $row->admin_id;
            if ($user_type == 'teacher')
                $response['login_user_id'] = $row->teacher_id;
            if ($user_type == 'student')
                $response['login_user_id'] = $row->student_id;
            if ($user_type == 'parent')
                $response['login_user_id'] = $row->parent_id;
            $response['authentication_key'] = $authentication_key;
        } else {
            $response['status'] = 'failed';
        }
        //return json_encode($response);
        return $response['status'];
    }
}


