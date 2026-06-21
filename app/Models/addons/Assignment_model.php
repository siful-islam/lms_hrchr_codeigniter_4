<?php

namespace App\Models\addons;

use App\Models\NativeBaseModel;
class Assignment_model extends NativeBaseModel
{
    function __construct()
    {
        parent::__construct();
        /*cache control*/

    }


    function add_assignment($course_id = "")
    {   
        $response['status'] = 'success';          
        $response['message'] = get_phrase('assignment_added_successfully');

        $data['title'] = html_escape($this->request->getPost('assignment_title'));
        if(empty($data['title'])){
            $response['status'] = 'error';          
            $response['message'] = get_phrase('fill_up_the_required_fields');
        }
        $data['course_id'] = html_escape($this->request->getPost('course_id'));
        $data['user_id'] = $this->session->get('user_id'); 
        $data['questions'] = htmlspecialchars($this->request->getPost('questions'));
        if(empty($data['questions'])){
            $response['status'] = 'error';          
            $response['message'] = get_phrase('fill_up_the_required_fields');
        }
        $data['deadline_date'] = strtotime($this->request->getPost('deadline_date'));
        $data['deadline_time'] = strtotime($this->request->getPost('deadline_date').' '.$this->request->getPost('deadline_time'));
        if (isset($_FILES['question_file']['name']) && $_FILES['question_file']['name'] != "") {
            $question_name = '('.$this->request->getPost('course_id').' - '.$data['user_id'].') '.$_FILES['question_file']['name'];
            $data['question_file'] = $question_name;
            move_uploaded_file($_FILES['question_file']['tmp_name'], 'uploads/assignment_files/assignments/' . $question_name);
        }
        $data['total_marks'] = html_escape($this->request->getPost('total_marks'));
        if(empty($data['total_marks'])){
            $response['status'] = 'error';          
            $response['message'] = get_phrase('fill_up_the_required_fields');
        }
        $data['status'] = $this->request->getPost('status');
        if($this->request->getPost('status') != ""){
            $data['status'] = $this->request->getPost('status');
        } else {
            $data['status'] = 'draft';
        }
        $data['note'] = html_escape($this->request->getPost('note'));
        $data['added_date'] = time();

        if($response['status'] == 'success'){
            $this->db->table('assignment')->insert($data);
            $insert_id = $this->db->insertID();

            $response['insert_id'] = $insert_id;
        }
        
        return json_encode($response);    
    }

    public function edit_assignment($assignment_id = ""){
        $response['status'] = 'success';          
        $response['message'] = get_phrase('assignment_updated_successfully');

        $data['title'] = html_escape($this->request->getPost('assignment_title'));
        if(empty($data['title'])){
            $response['status'] = 'error';          
            $response['message'] = get_phrase('fill_up_the_required_fields');
        }
        $data['user_id'] = $this->session->get('user_id'); 
        $data['questions'] = htmlspecialchars($this->request->getPost('questions'));
        if(empty($data['questions'])){
            $response['status'] = 'error';          
            $response['message'] = get_phrase('fill_up_the_required_fields');
        }
        $data['deadline_date'] = strtotime($this->request->getPost('deadline_date'));
        $data['deadline_time'] = strtotime($this->request->getPost('deadline_date').' '.$this->request->getPost('deadline_time'));
        if (isset($_FILES['question_file']['name']) && $_FILES['question_file']['name'] != "") {
            $question_name = '('.$this->request->getPost('course_id').' - '.$data['user_id'].') '.$_FILES['question_file']['name'];
            $data['question_file'] = $question_name;
                move_uploaded_file($_FILES['question_file']['tmp_name'], 'uploads/assignment_files/assignments/' . $question_name);
        }
        $data['total_marks'] = html_escape($this->request->getPost('total_marks'));
        if(empty($data['total_marks'])){
            $response['status'] = 'error';          
            $response['message'] = get_phrase('fill_up_the_required_fields');
        }
        $data['note'] = html_escape($this->request->getPost('note'));
        $data['updated_date'] = time();
        $this->db->where('assignment_id', $assignment_id);
        $this->db->table('assignment')->update($data);

        return json_encode($response);
    }

    public function get_assignments($assignment_id = ""){
        if($assignment_id > 0){
            $this->db->where('assignment_id', $assignment_id);
        }
        $this->db->orderBy('assignment_id', 'desc');
        return $this->db->table('assignment')->get();
    }

    public function get_assignment_by_course_id($course_id = ""){
        if($course_id > 0){
            $this->db->where('course_id', $course_id);
        }
        $this->db->orderBy('assignment_id', 'desc');
        return $this->db->table('assignment')->get();
    }

    public function update_assignment_status($status = "", $assignment_id = ""){

        $data['status'] = $status;
        $this->db->where('assignment_id', $assignment_id);
        $this->db->table('assignment')->update($data);

        $response['status'] = 'success';
        $response['message'] = get_phrase('assignment_status_has_been_updated');
        return json_encode($response);
    }

    public function assignment_delete($assignment_id = ""){
        $this->db->where('assignment_id', $assignment_id);
        $this->db->table('assignment')->delete();

        $response['status'] = 'success';
        $response['message'] = get_phrase('assignment_deleted_successfully');
        return json_encode($response);
    }

    public function get_submitted_assignment_by_assignment_id($assignment_id = ""){
        if($assignment_id > 0){
            $this->db->where('assignment_id', $assignment_id);
        }
        $this->db->orderBy('submission_id', 'asc');
        return $this->db->table('assignment_submission')->get();
    }

    public function get_answer_by_submission_id($submission_id = ""){
        $this->db->where('submission_id', $submission_id);
        return $this->db->table('assignment_submission')->get();
    }

    function update_assignment_mark($submission_id) 
    {
        $data['marks'] = $this->request->getPost('assignment_marks');
        $data['remarks'] = htmlspecialchars($this->request->getPost('assignment_remarks'));
        $data['review_status'] = 'reviewed';

        $this->db->where('submission_id', $submission_id);
        $this->db->table('assignment_submission')->update($data);

        $response['status'] = 'success';
        $response['message'] = get_phrase('assignment_marks_has_been_updated');
        return json_encode($response);
    }

    public function is_valid_user($course_id = ""){
        if ($this->session->get('admin_login') == true) {
            $query = $this->db->table('course')->where(array('id' => $course_id))->get();
        }else {
            $user_id = $this->session->get('user_id');
            $query = $this->db->table('course')->where(array('id' => $course_id, 'user_id' => $user_id))->get();
        }
        return $query;
    }


    //STDTENT PART START FROM HERE

    function submit_assignment() 
    { 
       
        $user_id = $this->session->get('user_id');
        $is_present = $this->db->table('assignment_submission')->where(array('user_id' => $user_id, 'assignment_id' => $this->request->getPost('assignment_id'))->get())->getNumRows();
        $is_valid_user = $this->db->table('enrol')->where(array('user_id' => $user_id, 'course_id' =>$this->request->getPost('course_id'))->get())->getNumRows();
        $data['user_id'] = $user_id;
        $data['assignment_id'] = $this->request->getPost('assignment_id');
        $data['answer'] = html_escape($this->request->getPost('answer'));
        if (isset($_FILES['answer_file']['name']) && $_FILES['answer_file']['name'] != "") {
            $answer_name = '('.$this->request->getPost('course_id').' - '.$user_id.') '.$_FILES['answer_file']['name'];
            $data['answer_file'] = $answer_name;
            move_uploaded_file($_FILES['answer_file']['tmp_name'], 'uploads/assignment_files/submitted_answers/' . $answer_name);
        }
        $data['note'] = html_escape($this->request->getPost('note'));
        $data['review_status'] = 'pending';
        $data['added_date'] = strtotime(date('d M Y'));
        if(isset($data['answer']) && $data['answer'] != "") 
        {
            if($is_valid_user > 0){
                if($is_present > 0) {
                    $this->db->where('assignment_id', $data['assignment_id']);
                    $this->db->table('assignment_submission')->update($data);
                    return json_encode(array('status' => 'success', 'message' => get_phrase('assignment_updated_successfully')));
                } else {
                    $this->db->table('assignment_submission')->insert($data);
                    return json_encode(array('status' => 'success', 'message' => get_phrase('assignment_submitted_successfully')));
                }
            }
        }
        return json_encode(array('status' => 'error', 'message' => get_phrase('assignment_submission_failed.').' '.get_phrase('answer_field_can_not_be_empty.')));
    }

}


