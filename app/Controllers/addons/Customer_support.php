<?php

namespace App\Controllers\addons;

use App\Controllers\BaseController;

class Customer_support extends BaseController {
    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');
        $this->load->model('addons/customer_support_model');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');

        if (!$this->session->get('user_id')) {
            redirect(site_url('login'), 'refresh');
        }
    }


    function tickets($status = ''){
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if($status == null)
        {
            $status = 'opened';
        }
        $page_data['tickets'] = $this->customer_support_model->get_tickets_by_status($status);
        $page_data['status'] = $status;
        $page_data['page_title'] = get_phrase('tickets');
        $page_data['page_name'] = 'tickets';
        $this->load->view('backend/index', $page_data);
    }

    function user_tickets($user_id = '')
    {
        if ($this->session->get('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['tickets'] = $this->customer_support_model->get_tickets_by_user_id($this->session->get('user_id'));
        $page_data['page_title'] = get_phrase('tickets');
        $page_data['page_name'] = 'tickets';
        $this->load->view('backend/index', $page_data);

    }

    function view_ticket($ticket_code = ''){
        $page_data['tickets'] = $this->customer_support_model->get_tickets_by_code($ticket_code)->getRowArray();
        $page_data['ticket_details'] = $this->customer_support_model->get_ticket_details($ticket_code);
        $page_data['page_title'] = get_phrase('ticket_details');
        $page_data['page_name'] = 'view_ticket';
        $this->load->view('backend/index', $page_data);
    }

    function change_status($status = '', $id = "", $code= ""){
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->change_status($status, $id);
        $this->session->setFlashdata('flash_message', get_phrase('status_has_been_updated'));
        if($status == 'opened')
        {
            $status = 'closed';
        } 
        else 
        {
            $status = 'opened';
        }
        if($code != null) 
        {
            redirect(site_url('addons/customer_support/view_ticket/'.$code), 'refresh');
        } 
        else 
        {
            redirect(site_url('addons/customer_support/tickets/'.$status), 'refresh');
        }
    }

    function edit_priority_form($id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['tickets'] = $this->customer_support_model->get_tickets($id)->getRowArray();;
        $page_data['page_title'] = get_phrase('change_priority');
        $page_data['page_name'] = 'change_priority_form';
        $this->load->view('backend/admin/change_priority_form', $page_data);
    }

    function change_priority($id = "", $code= ""){
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $priority = $this->request->getPost('priority');
        $this->customer_support_model->change_priority($priority, $id);
        $this->session->setFlashdata('flash_message', get_phrase('priority_has_been_updated'));
        redirect(site_url('addons/customer_support/view_ticket/'.$code), 'refresh');
    }

    function delete_ticket($id = "", $status = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->delete_ticket($id);
        $this->session->setFlashdata('flash_message', get_phrase('ticket_has_been_deleted'));
        redirect(site_url('addons/customer_support/tickets/'.$status), 'refresh');
    }

    function get_support_categories()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['support_category'] = $this->customer_support_model->get_support_categories();
        $page_data['page_title'] = get_phrase('support_categories');
        $page_data['page_name'] = 'support_categories';
        $this->load->view('backend/index', $page_data);
    }

    function add_support_category_form()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_title'] = get_phrase('add_support_category');
        $page_data['page_name'] = 'add_support_category_form';
        $this->load->view('backend/admin/add_support_category_form', $page_data);
    }

    function add_support_category(){
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $response=$this->customer_support_model->add_support_category();
        if($response == 1)
        {
            $this->session->setFlashdata('flash_message', get_phrase('category_has_been_added_successfully'));
        } 
        else 
        {
            $this->session->setFlashdata('error_message', get_phrase('category_already_exists'));
        }
        redirect(site_url('addons/customer_support/get_support_categories'), 'refresh');
    }

    function edit_support_category_form($id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['category'] = $this->customer_support_model->get_support_categories($id)->getRowArray();
        $page_data['page_title'] = get_phrase('edit_support_category');
        $page_data['page_name'] = 'edit_support_category_form';
        $this->load->view('backend/admin/edit_support_category_form', $page_data);
    }

    function update_support_category($id = ""){
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $response=$this->customer_support_model->update_support_category($id);
        if($response == 1)
        {
            $this->session->setFlashdata('flash_message', get_phrase('category_has_been_updated_successfully'));
        } 
        else 
        {
            $this->session->setFlashdata('error_message', get_phrase('category_already_exists'));
        }
        redirect(site_url('addons/customer_support/get_support_categories'), 'refresh');
    }

    function change_category_status($status = '', $id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->change_category_status($status, $id);
        $this->session->setFlashdata('flash_message', get_phrase('status_has_been_updated'));
        redirect(site_url('addons/customer_support/get_support_categories'), 'refresh');
    }

    function delete_support_category($id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->delete_support_category($id);
        $this->session->setFlashdata('flash_message', get_phrase('category_has_been_deleted'));
        redirect(site_url('addons/customer_support/get_support_categories'), 'refresh');
    }

    function get_support_macros()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['support_macros'] = $this->customer_support_model->get_support_macros();
        $page_data['page_title'] = get_phrase('support_macros');
        $page_data['page_name'] = 'support_macros';
        $this->load->view('backend/index', $page_data);
    }

    function add_support_macro_form()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_title'] = get_phrase('add_support_macro');
        $page_data['page_name'] = 'add_support_macro_form';
        $this->load->view('backend/index', $page_data);
    }

    function add_support_macro()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->add_support_macro();
        $this->session->setFlashdata('flash_message', get_phrase('macro_has_been_added_successfully'));
        redirect(site_url('addons/customer_support/get_support_macros'), 'refresh');
    }

    function update_support_macro($id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->update_support_macro($id);
        $this->session->setFlashdata('flash_message', get_phrase('macro_has_been_updated_successfully'));
        redirect(site_url('addons/customer_support/get_support_macros'), 'refresh');
    }

    function delete_support_macro($id = "")
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->delete_support_macro($id);
        $this->session->setFlashdata('flash_message', get_phrase('macro_has_been_deleted'));
        redirect(site_url('addons/customer_support/get_support_macros'), 'refresh');
    }

    function create_support_ticket()
    {
        $page_data['page_title'] = get_phrase('create_new_ticket');
        $page_data['page_name'] = 'create_ticket';
        $this->load->view('backend/index', $page_data);
    }

    function add_support_ticket()
    {
        if ($this->session->get('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->add_support_ticket();
        $this->session->setFlashdata('flash_message', get_phrase('ticket_has_been_created_successfully'));
        redirect(site_url('addons/customer_support/tickets'), 'refresh');
    }

    function add_user_support_ticket()
    {
        if ($this->session->get('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->customer_support_model->add_user_support_ticket();
        $this->session->setFlashdata('flash_message', get_phrase('ticket_has_been_created_successfully'));
        redirect(site_url('addons/customer_support/user_tickets'), 'refresh');
    }

    function support_reply()
    {
        $code = $data['code'] = html_escape($this->request->getPost('code'));
        $description = $this->request->getPost('description');
        if($description != null || $_FILES['support_file']['name'] != null)
        {
            $this->customer_support_model->support_reply();
            $this->session->setFlashdata('flash_message', get_phrase('reply_posted_successfully'));
        } 
        else
        {
            $this->session->setFlashdata('error_message', get_phrase('message_send_failed'));
        }
        redirect(site_url('addons/customer_support/view_ticket/'.$code), 'refresh');
    }

}

