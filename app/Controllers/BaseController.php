<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'cookie', 'common', 'user', 'multi_language', 'pagination', 'addon'];

    protected $db;
    protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->db = \Config\Database::connect();
        $this->session = session();

        if (function_exists('get_settings')) {
            $timezone = get_settings('timezone');
            date_default_timezone_set($timezone ?: 'UTC');
        }

        ini_set('memory_limit', '1024M');

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
    }
}


