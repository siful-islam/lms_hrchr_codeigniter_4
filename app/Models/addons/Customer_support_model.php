<?php

namespace App\Models\addons;

use App\Models\NativeBaseModel;
class Customer_support_model extends NativeBaseModel {

    function __construct() {
        parent::__construct();
        /*cache control*/

    }

    function add_support_category(){
        $data['title'] = html_escape($this->request->getPost('title'));
        $data['status'] = 'active';
        $this->db->where('title', $data['title']);
        $query=$this->db->table('support_category')->get();  
        if($query->getNumRows()>0)
        {
            return 0;
        }
        else
        {
            $this->db->table('support_category')->insert($data);
            return 1;
        }
    }

    function update_support_category($id = ""){
        $data['title'] = html_escape($this->request->getPost('title'));
        $this->db->where('title', $data['title']);
        $query=$this->db->table('support_category')->get();  
        if($query->getNumRows()>0)
        {
            return 0;
        }
        else
        {
            $this->db->where('id', $id);
            $this->db->table('support_category')->update($data);
            return 1;
        }
    }

     function change_category_status($status = "", $id = ''){
        $data['status'] = $status;
        $this->db->where('id', $id);
        return $this->db->table('support_category')->update($data);
        
    }

    function get_support_categories($id = ''){
        if($id > 0){
            $this->db->where('id', $id);
        }
        return $this->db->table('support_category')->get();        
    }

    function get_tickets($id = ''){
        if($id > 0){
            $this->db->where('id', $id);
        }
        return $this->db->table('tickets')->get();
    	
    }

    function get_tickets_by_user_id($user_id = ''){
        if($user_id > 0){
            $this->db->where('user_id', $user_id);
        }
        $this->db->orderBy('id', 'desc');
        return $this->db->table('tickets')->get();       
    }

     function get_tickets_by_code($code = ''){
        if($code != null){
            $this->db->where('code', $code);
        } 
        return $this->db->table('tickets')->get();
        
    }

    function get_tickets_by_status($status = ''){
        if($status != null){
            $this->db->where('status', $status);
        } 
        $this->db->orderBy('id', 'desc');
        return $this->db->table('tickets')->get();
        
    }

    function get_ticket_details($code = ''){
        if($code != null){
            $this->db->where('code', $code);
        } 
        $this->db->orderBy('id', 'desc');
        return $this->db->table('ticket_description')->get();
        
    }

    function change_status($status = "", $id = ''){
        $data['status'] = $status;
        $this->db->where('id', $id);
        return $this->db->table('tickets')->update($data);
        
    }

    function change_priority($priority = "", $id = ''){
        $data['priority'] = $priority;
        $this->db->where('id', $id);
        return $this->db->table('tickets')->update($data);
        
    }

    function delete_ticket($id = "")
    {
        $ticket_code = $this->get_tickets($id)->getRow()->code;
        $this->db->where('code', $ticket_code);
        $this->db->table('ticket_description')->delete();
        $this->db->where('id', $id);
        $this->db->table('tickets')->delete();
    }

    function delete_support_category($id = "")
    {
        $this->db->where('id', $id);
        $this->db->table('support_category')->delete();
    }

    function get_support_macros($id = ''){
        if($id > 0){
            $this->db->where('id', $id);
        }
        return $this->db->table('support_macro')->get();        
    }

     function add_support_macro(){
        $data['title'] = html_escape($this->request->getPost('title'));
        $data['description'] = $this->request->getPost('description'); 
        $this->db->table('support_macro')->insert($data);
    }

    function update_support_macro($id = ""){
        $data['title'] = html_escape($this->request->getPost('title'));
        $data['description'] = $this->request->getPost('description');
        $this->db->where('id', $id);
        $this->db->table('support_macro')->update($data);   
    }

    function delete_support_macro($id = "")
    {
        $this->db->where('id', $id);
        $this->db->table('support_macro')->delete();
    }

    function add_support_ticket(){
        $data['title'] = html_escape($this->request->getPost('title'));
        $data['code'] = html_escape($this->request->getPost('code'));
        $data['category_id'] = html_escape($this->request->getPost('category_id'));
        $data['user_id'] = html_escape($this->request->getPost('user_id'));
        $data['status'] = 'opened';
        $data['priority'] = html_escape($this->request->getPost('priority'));
        $data['date'] = strtotime(date('d M Y'));

        $this->db->table('tickets')->insert($data);

        $data1['code'] = $data['code'];
        $data1['user_id'] = $data['user_id'];
        $data1['description'] = $this->request->getPost('description');
        $data1['date'] = $data['date'];
        $ext = pathinfo($_FILES['support_file']['name'], PATHINFO_EXTENSION);
        $data1['file_name'] = rand(500000, 1000000).'.'.$ext;
    
        $this->db->table('ticket_description')->insert($data1);
        move_uploaded_file($_FILES['support_file']['tmp_name'], 'uploads/support_files/' . $data1['file_name']);  
    }

    function add_user_support_ticket(){
        $data['title'] = html_escape($this->request->getPost('title'));
        $data['code'] = substr(rand(500000, 1000000), 0, 6);
        $data['category_id'] = html_escape($this->request->getPost('category_id'));
        $data['user_id'] = $this->session->get('user_id'); 
        $data['status'] = 'opened';
        $data['priority'] = html_escape($this->request->getPost('priority'));
        $data['date'] = strtotime(date('d M Y'));

        $this->db->table('tickets')->insert($data);

        $data1['code'] = $data['code'];
        $data1['user_id'] = $data['user_id'];
        $data1['description'] = $this->request->getPost('description');
        $data1['date'] = $data['date'];
        $ext = pathinfo($_FILES['support_file']['name'], PATHINFO_EXTENSION);
        $data1['file_name'] = rand(500000, 1000000).'.'.$ext;
    
        $this->db->table('ticket_description')->insert($data1);
        move_uploaded_file($_FILES['support_file']['tmp_name'], 'uploads/support_files/' . $data1['file_name']);  
    }

    function support_reply(){
        $data['code'] = html_escape($this->request->getPost('code'));
        $data['user_id'] = $this->session->get('user_id'); 
        $data['description'] = $this->request->getPost('description');
        $data['date'] = strtotime(date('d M Y'));
        $ext = pathinfo($_FILES['support_file']['name'], PATHINFO_EXTENSION);
        $data['file_name'] = rand(500000, 1000000).'.'.$ext;
    
        $this->db->table('ticket_description')->insert($data);
        move_uploaded_file($_FILES['support_file']['tmp_name'], 'uploads/support_files/' . $data['file_name']);  
    }

}

