<?php

namespace App\Controllers\addons;

/*
*  @author   : Creativeitem
*  date    : 09 February, 2022
*  Academy
*  http://codecanyon.net/user/Creativeitem
*  http://support.creativeitem.com
*/

use App\Controllers\BaseController;

class Assignment extends BaseController{

    protected $unique_identifier = "assignment";
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Crud_model','crud_model');

        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');

        /*ADDON SPECIFIC MODELS*/
        $this->load->model('addons/Assignment_model','assignment_model');

        // CHECK IF THE ADDON IS ACTIVE OR NOT
        $this->check_addon_status();
    }


    public function load_assignment_form($course_id = ""){
        $page_data['course_id'] = $course_id;
        $this->load->view('backend/'.strtolower($this->session->get('role')).'/assignment_form', $page_data);
    }

    public function add_assignment($course_id = ""){
        echo $this->assignment_model->add_assignment($course_id);
    }

    public function load_assignment_list($course_id = ""){
        $page_data['assignment_list'] = $this->assignment_model->get_assignment_by_course_id($course_id)->getResultArray();
        $this->load->view('backend/'.strtolower($this->session->get('role')).'/assignment_list', $page_data);
    }

    public function load_single_assignment($notice_id = ""){
        $page_data['assignment_list'] = $this->assignment_model->get_assignments($assignment_id)->getResultArray();
        $this->load->view('backend/'.strtolower($this->session->get('role')).'/assignment_list', $page_data);
    }

    public function edit_assignment($course_id = "", $assignment_id = "", $param1 = ""){
        if($param1 == 'update'):
            echo $this->assignment_model->edit_assignment($assignment_id);
        else:
            $page_data['course_id'] = $course_id;
            $page_data['assignment'] = $this->assignment_model->get_assignments($assignment_id)->getRowArray();
            $this->load->view('backend/'.strtolower($this->session->get('role')).'/assignment_form_edit', $page_data);
        endif;
    }

    public function assignment_details($assignment_id = ""){
        $assignment_description = $this->assignment_model->get_assignments($assignment_id)->getRowArray();
        echo '<h5>'.htmlspecialchars_decode($assignment_description['title']).'</h5><small>'.htmlspecialchars_decode($assignment_description['status']).'</small>';
    }

    public function update_assignment_status($status = "", $assignment_id = ""){
        echo $this->assignment_model->update_assignment_status($status, $assignment_id);
    }

    public function assignment_delete($assignment_id = ""){
        echo $this->assignment_model->assignment_delete($assignment_id);
    }

    public function load_submitted_assignment($assignment_id = "", $course_id = ""){
        $page_data['course_id'] = $course_id;
        $page_data['submitted_assignments'] = $this->assignment_model->get_submitted_assignment_by_assignment_id($assignment_id)->getResultArray();
        $page_data['assignments'] = $this->assignment_model->get_submitted_assignment_by_assignment_id($assignment_id);
        $this->load->view('backend/'.strtolower($this->session->get('role')).'/submitted_assignment_list', $page_data);
    }

    public function view_answer($submission_id = "", $course_id = ""){
        $page_data['course_id'] = $course_id;
        $page_data['submitted_answer'] = $this->assignment_model->get_answer_by_submission_id($submission_id)->getRowArray();
        $this->load->view('backend/'.strtolower($this->session->get('role')).'/view_assignment_submission', $page_data);
    }

    public function update_assignment_mark($submission_id = "") {
        echo $this->assignment_model->update_assignment_mark($submission_id);
    }

    //STUDENT PART START FROM HERE

    function load_assignments_with_ajax($course_id = ""){
        $data['course_id'] = $course_id;
        $this->load->view('lessons/assignment_body', $data);
    }

    public function assignment_submit_form($course_id = "", $assignment_id = "")
    {
        $page_data['page_title'] = get_phrase('submit_assignment');
        $page_data['page_name'] = 'assignment_submit_form';
        $page_data['course_id'] = $course_id;
        $page_data['assignment_id'] = $assignment_id;
        $this->load->view('lessons/assignment_submit_form', $page_data);
    }

    public function submit_assignment() 
    {
        echo $this->assignment_model->submit_assignment();
    }

    public function submitted_assignment_result($course_id = "", $assignment_id = "") 
    {
        $user_id = $this->session->get('user_id');
        $this->db->where('assignment_id' , $assignment_id);
        $this->db->where('user_id' , $user_id);
        $page_data['results'] = $this->db->table('assignment_submission')->get()->getRowArray();
        $page_data['course_id'] = $course_id;
        $page_data['page_title'] = get_phrase('assignment_result');
        $page_data['page_name'] = 'assignment_result';
        $this->load->view('lessons/assignment_result', $page_data);
    }


    // CHECK IF THE ADDON IS ACTIVE OR NOT. IF NOT REDIRECT TO DASHBOARD
    public function check_addon_status() {
        $checker = array('unique_identifier' => $this->unique_identifier);
        $this->db->where($checker);
        $addon_details = $this->db->table('addons')->get()->getRowArray();
        if ($addon_details['status']) {
            return true;
        }else{
            redirect(site_url(), 'refresh');
        }
    }

}


