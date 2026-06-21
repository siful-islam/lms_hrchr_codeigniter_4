<?php

namespace App\Controllers;

class Sign_up extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        
        date_default_timezone_set(get_settings('timezone'));

        // Your own constructor code
        $this->load->database();
        $this->load->library('session');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');

        //Check custom session data
        $this->user_model->check_session_data();
    }

    public function index()
    {
         if (get_settings('public_signup') != 'enable') {
             redirect(site_url(), 'refresh');
            return;
        }

        if ($this->session->get('admin_login')) {
            redirect(site_url('admin'), 'refresh');
        } elseif ($this->session->get('user_login')) {
            redirect(site_url('user'), 'refresh');
        }
        $page_data['page_name'] = 'sign_up';
        $page_data['page_title'] = site_phrase('sign_up');
        $this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $page_data);
    }

    public function verification_code()
    {
        if (!$this->session->get('register_email')) {
            redirect(site_url('sign_up'), 'refresh');
        }
        $page_data['page_name'] = "verification_code";
        $page_data['page_title'] = site_phrase('verification_code');
        $this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $page_data);
    }

}


