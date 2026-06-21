<?php

namespace App\Models\addons;

use App\Models\NativeBaseModel;
require_once(APPPATH."libraries/razorpay-php/Razorpay.php");
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
class Ebook_model extends NativeBaseModel
{


    public function get_category_id($slug = "")
	{
		$category_details = $this->db->table('ebook_category')
			->where('slug', $slug)
			->get()
			->getRowArray();

		return $category_details['category_id'] ?? null;
	}
    public function get_categories($param1 = "")
    {
        if($param1 != ""){
            $this->db->where('category_id',$param1);
        }
        return $this->db->table('ebook_category')->get();
    }

    public function get_active_ebook(){
        $this->db->where('is_active', 1);
        return $this->db->table('ebook')->get();
    }
    public function get_active_addon_by_category_id($category_id = "", $category_id_type = "category_id"){
        $this->db->where($category_id_type, $category_id);
        $this->db->where('is_active', 1);
        return $this->db->table('ebook')->get();
    }
    public function get_category_details_by_id($id)
    {
        return $this->db->table('ebook_category')->where(array('category_id' => $id))->get();
    }
    function filter_ebook($selected_category_id = "", $selected_price = "", $selected_rating = "", $search_text ="")
    {

        $ebook_ids = array();
        if ($selected_category_id != "all") {
            $category_id = $this->get_category_details_by_id($selected_category_id)->getRow()->category_id;
        }

        if ($selected_rating != "all") {
            $this->db->where('is_active', 1);
            $ebooks = $this->db->table('ebook')->get()->getResultArray();
            foreach ($ebooks as $key => $ebook) {
                $total_rating =  $this->get_ratings( $ebook['ebook_id'], true)->getRow()->rating;
                $number_of_ratings = $this->get_ratings($ebook['ebook_id'])->getNumRows();
                if ($number_of_ratings > 0) {
                    $average_ceil_rating = ceil($total_rating / $number_of_ratings);
                    if ($average_ceil_rating == $selected_rating) {
                        array_push($ebook_ids, $ebook['ebook_id']);
                    }
                    
                }
                
            }
        }

        if($search_text != ""){
            $this->db->groupStart();
            
            $this->db->like('title', $search_text);
            $this->db->orLike('description', $search_text)->groupEnd();
        }
        
        if ($selected_category_id != "all") {
            
            $this->db->where('category_id', $category_id);
        }
        
        if ($selected_price != "all") {
            if ($selected_price == "paid") {
               
                $this->db->where('is_free', 0);
            } elseif ($selected_price == "free") {
               
                $this->db->where('is_free', 1);
            }
        }

        if ($selected_rating != "all") {
            if(!empty($ebook_ids)){
                $this->db->whereIn('ebook_id', $ebook_ids);

            }else{
                $this->db->whereIn('ebook_id', "");
            }
        }
        $this->db->where('is_active', 1);
            
        return $this->db->table('ebook')->get()->getResultArray();
        
           
        
    }

    public function get_ratings ($ratable_id = "", $is_sum = false)
    {
        if ($is_sum) {
            $this->db->selectSum('rating');
            return $this->db->table('ebook_reviews')->where(array('ebook_id' => $ratable_id))->get();
        } else {
            return $this->db->table('ebook_reviews')->where(array('ebook_id' => $ratable_id))->get();
        }
    }

    function get_user_rating($user_id = "", $ebook_id = ""){
        $this->db->where('user_id', $user_id);
        $this->db->where('ebook_id', $ebook_id);
        return $this->db->table('ebook_reviews')->get();
    }

    public function get_ebook_thumbnail_url($ebook_id = '')
    {
        $thumb = $this->db->table('ebook')->where(array('ebook_id' => $ebook_id))->get()->getRow()->thumbnail;
       
        if (file_exists('uploads/ebook/thumbnails/' . $thumb))
            return base_url() . 'uploads/ebook/thumbnails/' . $thumb;
        else
            return base_url() . 'uploads/ebook/thumbnails/placeholder.png';
    }

    public function get_ebook_banner_url($ebook_id = ''){
        $banner = $this->db->table('ebook')->where(array('ebook_id' => $ebook_id))->get()->getRow()->banner;
        if(file_exists('uploads/ebook/banner/'. $banner))
            return base_url() . 'uploads/ebook/banner/' . $banner;
        else
            return base_url() . 'uploads/ebook/banner/placeholder.png';
    }

    public function get_ebook_by_id($ebook_id = "")
    {
       return $this->db->table("ebook")->where(array("ebook_id" => $ebook_id)->get());

    }
    public function get_ebooks_by_user_id($user_id = "", $ebook_id = "")
    {
        if($user_id == ""){
            $this->db->where('user_id', $this->session->get('user_id'));
        }
        if($ebook_id > 0){
            $this->db->where('ebook_id', $ebook_id);
        }
       return $this->db->table("ebook")->get();

    }
    public function get_ebooks($category_id = "",$instructor_id = 0)
    {
       if ($category_id > 0 && $instructor_id > 0) {

            $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($instructor_id);
            $this->db->where('category_id', $category_id);
            $this->db->where('user_id', $instructor_id);

            if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
                $this->db->orWhereIn('id', $multi_instructor_course_ids);
            }

            return $this->db->table('ebook')->get();
        } elseif ($category_id > 0  && $instructor_id == 0) {
            return $this->db->table('ebook')->where(array('category_id' => $category_id))->get();
        } else {
            $this->db->where('is_active', 1);

            return $this->db->table('ebook')->get();
        }
    }

    public function get_percentage_of_specific_rating($rating = "", $ratable_type = "", $ratable_id = "")
    {
        $number_of_user_rated = $this->db->table('ebook_reviews')->where(array(
            // 'ratable_type' => $ratable_type,
            'ebook_id'   => $ratable_id
        ))->get()->getNumRows();

        $number_of_user_rated_the_specific_rating = $this->db->table('ebook_reviews')->where(array(
            // 'ratable_type' => $ratable_type,
            'ebook_id'   => $ratable_id,
            'rating'       => $rating
        ))->get()->getNumRows();

        //return $number_of_user_rated.' '.$number_of_user_rated_the_specific_rating;
        if ($number_of_user_rated_the_specific_rating > 0) {
            $percentage = ($number_of_user_rated_the_specific_rating / $number_of_user_rated) * 100;
        } else {
            $percentage = 0;
        }
        return floor($percentage);
    }
    public function get_user($user_id = 0)
    {
        if ($user_id > 0) {
            $this->db->where('id', $user_id);
        }
        // $this->db->where('role_id', 2);
        return $this->db->table('users')->get();
    }

    //backend

    public function get_all_ebooks($ebook_id = " ")
    {
        if($ebook_id > 0){
            $this->db->where('ebook_id',$ebook_id);
        }

        $this->db->orderBy('ebook_id', 'desc');
        return $this->db->table('ebook')->get();
    }
    function get_ebook_categories($ebook_category_id = ""){
        if($ebook_category_id > 0){
            $this->db->where('category_id', $ebook_category_id);
        }
        return $this->db->table('ebook_category')->get();
    }

    public function get_ebooks_list($ebook_id = " ")
    {
        if($ebook_id > 0){
            $this->db->where('ebook_id',$ebook_id);
        }
       
        $this->db->where("is_active", 1);
        $this->db->orderBy('ebook_id', 'desc');
        return $this->db->table('ebook')->get();
    }
    public function get_ebooks_by_category_id($category_id = ""){
        $this->db->where('category_id', $category_id);
        $this->db->where('is_active', 1);
        return $this->db->table('ebook')->get();
    }

    public function add_ebook(){
        $data['title'] = htmlspecialchars($this->request->getPost('title'));
        $data['description'] = htmlspecialchars(remove_js($this->request->getPost('description', false)));
        $data['category_id'] = htmlspecialchars($this->request->getPost('category_id'));
        // echo $_FILES['banner']['name'];
        // echo $_FILES['thumbnail']['name'];
        // $ext  = (new SplFileInfo($path))->getExtension();
        if ($_FILES['thumbnail']['name'] != "") {
            $ext  = (new SplFileInfo($_FILES['thumbnail']['name']))->getExtension();
            $data['thumbnail'] = md5(rand(10000000, 20000000)) .'.'. $ext;
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/ebook/thumbnails/' . $data['thumbnail']);
        }else{
            $data['thumbnail'] = 'placeholder.png';
        }

        // if ($_FILES['banner']['name'] != "") {
        //     $ext  = (new SplFileInfo($_FILES['banner']['name']))->getExtension();
        //     $data['banner'] = md5(rand(10000000, 20000000)) .'.'. $ext;
        //     move_uploaded_file($_FILES['banner']['tmp_name'], 'uploads/ebook/banner/' . $data['banner']);
        // }else{
            $data['banner'] = 'placeholder.png';
        // }
        if ($_FILES['ebook_preview_file']['name'] != "") {
            $ext  = (new SplFileInfo($_FILES['ebook_preview_file']['name']))->getExtension();
            $data['preview'] = md5(rand(10000000, 20000000)) .'.'. $ext;
            move_uploaded_file($_FILES['ebook_preview_file']['tmp_name'], 'uploads/ebook/file/ebook_preview/' . $data['preview']);
        }
        if ($_FILES['ebook_complete_file']['name'] != "") {
            $ext  = (new SplFileInfo($_FILES['ebook_complete_file']['name']))->getExtension();
            $data['file'] = md5(rand(10000000, 20000000)) .'.'. $ext;
            move_uploaded_file($_FILES['ebook_complete_file']['tmp_name'], 'uploads/ebook/file/ebook_full/' .$data['file']);
        }
        $data['user_id'] = $this->session->get('user_id');
      

        $data['price'] = $this->request->getPost('price');
        $flag = $this->request->getPost('discount_flag');
        $free = $this->request->getPost('is_free');
        $data['publication_name'] = $this->request->getPost('publication_name');
        $data['edition'] = $this->request->getPost('edition');
        if($flag != 1){
            $flag = 0;

        }
        if($free != 1){
            $free = 0;
        }
        if($this->session->get('admin_login')){
            $data['is_active'] = 1;
        }
        else{
            $data['is_active'] = 0;
        }
        $data['discounted_price'] = $this->request->getPost('discounted_price');
        $data['no_of_pages'] = $this->request->getPost('no_of_pages');
        $data['is_free'] = $free;
        $data['discount_flag'] = $flag;
        $data['added_date'] = strtotime(date('D, d-M-Y'));

        //seo-fileds
        $data['meta_title'] = html_escape($this->request->getPost('meta_title'));
        $data['meta_description'] = html_escape($this->request->getPost('meta_description'));
        $data['meta_keywords'] = html_escape($this->request->getPost('meta_keywords'));
        $data['og_title'] = html_escape($this->request->getPost('og_title'));
        $data['og_description'] = html_escape($this->request->getPost('og_description'));
        $data['json_ld'] = $this->request->getPost('json_ld');

        $this->db->table('ebook')->insert($data);
        return true;
    }

    public function update_ebook($ebook_id = ""){
        $data['title'] = htmlspecialchars($this->request->getPost('title'));
        $data['description'] = htmlspecialchars(remove_js($this->request->getPost('description', false)));
        $data['category_id'] = htmlspecialchars($this->request->getPost('category_id'));
        $ebook = $this->get_ebook_by_id($ebook_id)->getRowArray();
        if ($_FILES['thumbnail']['name'] != "") {
            $ext  = (new SplFileInfo($_FILES['thumbnail']['name']))->getExtension();
            unlink('uploads/ebook/thumbnails/'.$ebook['thumbnail']);
            $data['thumbnail'] = md5(rand(10000000, 20000000)) .'.'. $ext;
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/ebook/thumbnails/' . $data['thumbnail']);
        }
        // if ($_FILES['banner']['name'] != "") {
        //     $ext  = (new SplFileInfo($_FILES['banner']['name']))->getExtension();
        //     unlink('uploads/ebook/banner/'.$ebook['banner']);
        //     $data['banner'] = md5(rand(10000000, 20000000)) .'.'. $ext;
        //     move_uploaded_file($_FILES['banner']['tmp_name'], 'uploads/ebook/banner/' . $data['banner']);
        // }
        if ($_FILES['ebook_preview_file']['name'] != "") {
            $ext  = (new SplFileInfo($_FILES['ebook_preview_file']['name']))->getExtension();
            unlink('uploads/ebook/file/ebook_preview/'.$ebook['preview']);
            $data['preview'] = md5(rand(10000000, 20000000)) .'.'. $ext;
            move_uploaded_file($_FILES['ebook_preview_file']['tmp_name'], 'uploads/ebook/file/ebook_preview/' . $data['preview']);
        }
        if ($_FILES['ebook_complete_file']['name'] != "") {
            unlink('uploads/ebook/file/ebook_full/'.$ebook['file']);
            $ext  = (new SplFileInfo($_FILES['ebook_complete_file']['name']))->getExtension();
            $data['file'] = md5(rand(10000000, 20000000)) .'.'. $ext;
            move_uploaded_file($_FILES['ebook_complete_file']['tmp_name'], 'uploads/ebook/file/ebook_full/' .$data['file']);
        }
      

        $data['price'] = $this->request->getPost('price');
        $flag = $this->request->getPost('discount_flag');
        $free = $this->request->getPost('is_free');
        $data['publication_name'] = $this->request->getPost('publication_name');
        $data['edition'] = $this->request->getPost('edition');
        if($flag != 1){
            $flag = 0;

        }
        if($free != 1){
            $free = 0;
        }

        $data['discounted_price'] = $this->request->getPost('discounted_price');
        $data['no_of_pages'] = $this->request->getPost('no_of_pages');
        $data['is_free'] = $free;
        $data['discount_flag'] = $flag;
        $data['added_date'] = strtotime(date('D, d-M-Y'));

        //seo-fileds
        $data['meta_title'] = html_escape($this->request->getPost('meta_title'));
        $data['meta_description'] = html_escape($this->request->getPost('meta_description'));
        $data['meta_keywords'] = html_escape($this->request->getPost('meta_keywords'));
        $data['og_title'] = html_escape($this->request->getPost('og_title'));
        $data['og_description'] = html_escape($this->request->getPost('og_description'));
        $data['json_ld'] = $this->request->getPost('json_ld');

        if($this->session->get('user_login')){
            $this->db->where('user_id', $this->session->get('user_id'));
        }
        
        $this->db->where('ebook_id',$ebook_id);
        $this->db->table('ebook')->update($data);
        return true;
    }

    public function update_ebook_status($ebook_id = ""){
        $ebook = $this->get_ebook_by_id($ebook_id)->getRowArray();
        if($ebook['is_active'] == 0){
            $this->db->where('ebook_id', $ebook_id);
            $this->db->table('ebook')->update(array('is_active' => 1));
            return true;
        }
        if($ebook['is_active'] == 1){
            $this->db->where('ebook_id', $ebook_id);
            $this->db->table('ebook')->update(array('is_active' => 0));
            return false;
        }
        
    }

    public function delete_ebook($ebook_id = "")
    {
        if($this->session->get('user_login')){
            $this->db->where('user_id', $this->session->get('user_id'));
        }
        $this->db->where('ebook_id', $ebook_id);
        $this->db->table('ebook')->delete();

        return true;

    }

    public function add_ebook_category()
    {
        $data['title'] = htmlspecialchars($this->request->getPost('title'));
        $data['slug'] = slugify($data['title']);
        // $data['thumbnail'] = htmlspecialchars($this->request->getPost('thumbnail'));
        $data['added_date'] = time();
        if (!file_exists('uploads/ebook/thumbnails/category_thumbnails')) {
            mkdir('uploads/ebook/thumbnails/category_thumbnails', 0777, true);
        }
        elseif ($_FILES['category_thumbnail']['name'] == "") {
            $data['thumbnail'] = 'category-thumbnail.png';
        } 
        else {
            $data['thumbnail'] = md5(rand(10000000, 20000000)) . '.jpg';
            move_uploaded_file($_FILES['category_thumbnail']['tmp_name'], 'uploads/ebook/thumbnails/category_thumbnails/' . $data['thumbnail']);
        }
        
        $this->db->where('slug', $data['slug']);
        $row = $this->db->table('ebook_category')->get();

        if($row->getNumRows() > 0)
        {
            return false;
        }else{
            $this->db->table('ebook_category')->insert($data);
            return true;
        }

    }
    function delete_ebook_category($category_id = ""){
        $this->db->where('category_id', $category_id);
        $this->db->table('ebook_category')->delete();
    }
    function update_ebook_category($category_id = ""){
        $data['title'] = htmlspecialchars($this->request->getPost('title'));
        $data['slug'] = slugify($data['title']);
        // $data['thumbnail'] = htmlspecialchars($this->request->getPost('thumbnail'));
        $data['added_date'] = time();
        if (!file_exists('uploads/ebook/thumbnails/category_thumbnails')) {
            mkdir('uploads/ebook/thumbnails/category_thumbnails', 0777, true);
        }
        elseif ($_FILES['category_thumbnail']['name'] == "") {
            $data['thumbnail'] = 'category-thumbnail.png';
        } 
        else {
            $data['thumbnail'] = md5(rand(10000000, 20000000)) . '.jpg';
            move_uploaded_file($_FILES['category_thumbnail']['tmp_name'], 'uploads/ebook/thumbnails/category_thumbnails/' . $data['thumbnail']);
        }
        
        $this->db->where('slug', $data['slug']);
        $row = $this->db->table('ebook_category')->get();
        if($row->getNumRows() > 0 && $row->getRow()->category_id != $category_id){
            return false;
        }else{
            $this->db->where('category_id', $category_id);
            $this->db->table('ebook_category')->update($data);
            return true;
        }
    }

    public function get_instructor_wise_ebooks($instructor_id = "", $return_as = "")
    {
        
        $this->db->where('user_id', $instructor_id);

        

        $ebooks = $this->db->table('ebook')->get();
       
        
        if ($return_as == 'simple_array') {
            $array = array();
            foreach ($ebooks->getResultArray() as $ebook) {
                if (!in_array($ebook['ebook_id'], $array)) {
                    array_push($array, $ebook['ebook_id']);
                }
            }
            return $array;
        } else {
            
            return $ebooks;
        }
    }
    public function get_instructor_wise_ebook_ratings($instructor_id = "", $ratable_type = "", $is_sum = false)
    {
       
        $ebook_ids = $this->get_instructor_wise_ebooks($instructor_id, 'simple_array');
        if ($is_sum) {
            $this->db->where('ratable_type', $ratable_type);
            $this->db->whereIn('ratable_id', $course_ids);
            $this->db->selectSum('rating');
            return $this->db->table('rating')->get();
        } else {
            // $this->db->where('ratable_type', $ratable_type);
            $this->db->whereIn('ebook_id', $ebook_ids);
            return $this->db->table('ebook_reviews')->get();
        }
    }

    function my_ebooks(){
        $data['user_id'] = $this->session->get('user_id');
        $ebook_payments = $this->db->table('ebook_payment')->where(array('user_id' =>$data['user_id']))->get()->getResultArray();

        $arr = array(0);
        foreach($ebook_payments as $ebook_payment):
            array_push($arr, $ebook_payment['ebook_id']);
        endforeach;
        $this->db->where('is_active', 1);
        $this->db->whereIn('ebook_id', $arr);
        return $this->db->table('ebook')->get();

    }

    // VALIDATE STRIPE PAYMENT
    public function stripe_payment($user_id = "", $session_id = "") {
        $stripe_keys = get_settings('stripe_keys');
        $values = json_decode($stripe_keys);
        if ($values[0]->testmode == 'on') {
            $public_key = $values[0]->public_key;
            $secret_key = $values[0]->secret_key;
        } else {
            $public_key = $values[0]->public_live_key;
            $secret_key = $values[0]->secret_live_key;
        }


        // Stripe API configuration
        define('STRIPE_API_KEY', $secret_key);
        define('STRIPE_PUBLISHABLE_KEY', $public_key);

        $status_msg = '';
        $transaction_id = '';
        $paid_amount = '';
        $paid_currency = '';
        $payment_status = '';

        // Check whether stripe checkout session is not empty
        if($session_id != ""){
            //$session_id = $_GET['session_id'];

            // Include Stripe PHP library
            require_once APPPATH.'libraries/Stripe/init.php';

            // Set API key
            \Stripe\Stripe::setApiKey(STRIPE_API_KEY);

            // Fetch the Checkout Session to display the JSON result on the success page
            try {
                $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);
            }catch(Exception $e) {
                $api_error = $e->getMessage();
            }

            if(empty($api_error) && $checkout_session){
                // Retrieve the details of a PaymentIntent
                try {
                    $intent = \Stripe\PaymentIntent::retrieve($checkout_session->payment_intent);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    $api_error = $e->getMessage();
                }

                // Retrieves the details of customer
                try {
                    // Create the PaymentIntent
                    $customer = \Stripe\Customer::retrieve($checkout_session->customer);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    $api_error = $e->getMessage();
                }

                if(empty($api_error) && $intent){
                    // Check whether the charge is successful
                    if($intent->status == 'succeeded'){
                        // Customer details
                        $name = $customer->name;
                        $email = $customer->email;

                        // Transaction details
                        $transaction_id = $intent->id;
                        $paid_amount = ($intent->amount/100);
                        $paid_currency = $intent->currency;
                        $payment_status = $intent->status;

                        // If the order is successful
                        if($payment_status == 'succeeded'){
                            $status_msg = get_phrase("Your_Payment_has_been_Successful");
                        }else{
                            $status_msg = get_phrase("Your_Payment_has_failed");
                        }
                    }else{
                        $status_msg = get_phrase("Transaction_has_been_failed");;
                    }
                }else{
                    $status_msg = get_phrase("Unable_to_fetch_the_transaction_details"). ' ' .$api_error;
                }

                $status_msg = 'success';
            }else{
                $status_msg = get_phrase("Transaction_has_been_failed").' '.$api_error;
            }
        }else{
            $status_msg = get_phrase("Invalid_Request");
        }

        $response['status_msg'] = $status_msg;
        $response['transaction_id'] = $transaction_id;
        $response['paid_amount'] = $paid_amount;
        $response['paid_currency'] = $paid_currency;
        $response['payment_status'] = $payment_status;
        $response['stripe_session_id'] = $session_id;
        $response['payment_method'] = 'stripe';

        return $response;
    }

    function ebook_purchase($method = "",$ebook_id= "", $amount = "", $transaction_id = "", $session_id = "", $user_id = ""){
     

        $ebook_details = $this->ebook_model->get_ebook_by_id($ebook_id)->getRowArray();
        if($ebook_details['discount_flag'] == 1)
        {
            $data['paid_amount'] =  $ebook_details['discounted_price'];
        }else{
            $data['paid_amount'] = $ebook_details['price'];
        }

        if($user_id == ""){
            $user_id = $this->session->get('user_id');
        }
        $data['ebook_id'] = $ebook_id;
        $data['user_id'] = $user_id;
        $data['payment_method'] = 'paypal';
        $data['payment_keys'] = json_encode(array('transaction_id' => $transaction_id,'session_id' => $session_id));

        if (get_user_role('role_id', $ebook_details['user_id']) == 1) {
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

        $data['added_date'] =  time();
        $payment = $this->db->table('ebook_payment')->where(array('ebook_id' => $ebook_id, 'user_id' => $user_id))->get();
        if($payment->getNumRows() <= 0){
            $this->db->table('ebook_payment')->insert($data);
        }
      
    }




    public function get_revenue_by_user_type($timestamp_start = "", $timestamp_end = "", $revenue_type = "")
    {
        $ebook_ids = array();
        $ebooks    = array();
        
        $admin_details = $this->user_model->get_admin_details()->getRowArray();
        if ($revenue_type == 'instructor_revenue') {
            if($this->session->get('admin_login')){
                $this->db->where('user_id !=', $admin_details['id']);
            }else{
                $this->db->where('user_id', $this->session->get('user_id'));
            }
            $this->db->select('ebook_id');
            $ebooks = $this->db->table('ebook')->get()->getResultArray();
            foreach ($ebooks as $ebook) {
                if (!in_array($ebook['ebook_id'], $ebook_ids)) {
                    array_push($ebook_ids, $ebook['ebook_id']);
                }
            }
            if (sizeof($ebook_ids)) {
                $this->db->whereIn('ebook_id', $ebook_ids);
            } else {
                return array();
            }
        }
        $this->db->where('added_date >=', $timestamp_start);
        $this->db->where('added_date <=', $timestamp_end);
        $this->db->orderBy('added_date', 'desc');
        return $this->db->table('ebook_payment')->get()->getResultArray();
    }


    public function razorpayPrepareData($payable_amount = "")
    {
        $user_details = $this->user_model->get_user($this->session->get('user_id'))->getRowArray();
        $razorpay_settings = json_decode(get_settings('razorpay_keys'));

        $key_id = $razorpay_settings[0]->key;
        $secret_key = $razorpay_settings[0]->secret_key;



    
      $api = new Api($key_id, $secret_key);

      $razorpayOrder = $api->order->create(array(
        'receipt'         => rand(),
        'amount'          => $payable_amount * 100, // 2000 rupees in paise
        'currency'        => get_settings('razorpay_currency'),
        'payment_capture' => 1 // auto capture
      ));
      $amount = $razorpayOrder['amount'];
      $razorpayOrderId = $razorpayOrder['id'];
      $_SESSION['razorpay_order_id'] = $razorpayOrderId;

    $data = array(
      "key" => $key_id,
      "amount" => $amount,
      "name" => get_settings('system_title'),
      "description" => get_settings('about_us'),
      "image" => base_url('uploads/system/'.get_settings('favicon')),
      "prefill" => array(
      "name"  => $user_details['first_name'].' '.$user_details['last_name'],
      "email"  => $user_details['email'],
    ),
      "notes"  => array(
      "merchant_order_id" => rand(),
    ),
      "theme"  => array(
      "color"  => $razorpay_settings[0]->theme_color
    ),
      "order_id" => $razorpayOrderId,
    );
    return $data;
  }

  public function razorpay_payment($razorpayOrderId = "", $payment_id = "", $amount = "", $signature = "")
  {
    $razorpay_keys = json_decode(get_settings('razorpay_keys'));
    $success = true;
    $error = "payment_failed";

    if (empty($payment_id) === false) {
      $api = new Api($razorpay_keys[0]->key, $razorpay_keys[0]->secret_key);
      try {
        $attributes = array(
          'razorpay_order_id' => $razorpayOrderId,
          'razorpay_payment_id' => $payment_id,
          'razorpay_signature' => $signature
        );
        $api->utility->verifyPaymentSignature($attributes);
      } catch(SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay_Error : ' . $e->getMessage();
      }
    }
    if ($success === true) {
      return true;
    }else {
      return false;
    }
  }

    function payment_history_by_user_id($user_id = ""){
        $this->db->where('user_id', $user_id);
        return $this->db->table('ebook_payment')->get();
    }
    
}

