<?php

namespace App\Models;

use App\Models\NativeBaseModel;
class Api_instructor_model extends NativeBaseModel
{

	// constructor
	function __construct()
	{
		parent::__construct();
		/*cache control*/

	}

	// Login mechanism
	public function login_post()
	{
		$response = array();
		$credential = array('email' => $_POST['email'], 'password' => sha1($_POST['password']), 'is_instructor' => 1, 'role_id' => 2, 'status' => 1);
		$query = $this->db->table('users')->where($credential)->get();
		if ($query->getNumRows() > 0) {
			$row = $query->getRowArray();
			$response['user_id'] = $row['id'];
			$response['first_name'] = $row['first_name'];
			$response['last_name'] = $row['last_name'];
			$response['email'] = $row['email'];
			$response['role'] = strtolower(get_user_role('user_role', $row['id']));
			$response['image'] = $this->user_model->get_user_image_url($row['id']);
			$response['validity'] = 1;
		} else {
			$response['status'] = 403;
			$response['validity'] = 0;
			$response['message'] = api_phrase('user_not_found');
		}
		return $response;
	}

	public function change_profile_photo_post($user_id = "")
	{
		$query = $this->user_model->get_all_user($user_id);

		if ($query->getNumRows() > 0) {
			if (isset($_FILES['user_image']['name']) && !empty($_FILES['user_image']['name'])) {
				$data['image'] = md5(rand(10000, 99999));

				if (file_exists('uploads/user_image/' . $query->getRow()->image . 'jpg')) {
					unlink('uploads/user_image/' . $query->getRow()->image . 'jpg');
				}
				move_uploaded_file($_FILES['user_image']['tmp_name'], 'uploads/user_image/' . $data['image'] . '.jpg');

				$this->db->where('id', $user_id);
				$this->db->table('users')->update($data);

				$response['photo'] = $this->user_model->get_user_image_url($user_id);
				$response['message'] = api_phrase('photo_uploaded_successfully');
				$response['status'] = 200;
				$response['validity'] = true;
			} else {
				$response['status'] = 403;
				$response['validity'] = false;
				$response['message'] = api_phrase('first_select_your_image');
			}
		} else {
			$response['status'] = 403;
			$response['validity'] = false;
			$response['message'] = api_phrase('user_not_found');
		}

		return $response;
	}

	public function change_password_post($user_id = "")
	{
		$response = array();

		$query = $this->user_model->get_user($user_id);
		if ($query->getNumRows() > 0) {
			$user_details = $query->getRowArray();
			$current_password = $this->request->getPost('current_password');
			$new_password = $this->request->getPost('new_password');
			$confirm_password = $this->request->getPost('confirm_password');
			if ($user_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
				$data['password'] = sha1($new_password);
				$this->db->where('id', $user_id);
				$this->db->table('users')->update($data);

				$response['status'] = 200;
				$response['validity'] = true;
				$response['message'] = api_phrase('password_changed');
			} else {
				$response['status'] = 403;
				$response['validity'] = false;
				$response['message'] = api_phrase('password_is_not_matching');
			}
		} else {
			$response['status'] = 403;
			$response['validity'] = false;
			$response['message'] = api_phrase('user_not_found');
		}

		return $response;
	}

	function forgot_password_post(){
    	$email = $this->request->getPost('email');
        $verification_code = str_replace('=', '', base64_encode($email.'_Uh6#@#6hU_'.rand(111111, 9999999)));
        $this->db->where('email', $email);
        $this->db->table('users')->update(array('verification_code' => $verification_code, 'last_modified' => time()));
        // send new password reset link to user email
        $this->email_model->password_reset_email($verification_code, $email);
        return true;
    }

	public function userdata_get($user_id = "")
	{
		$response = array();
		$credential = array('id' => $user_id, 'role_id' => '2', 'status' => 1);
		$query = $this->db->table('users')->where($credential)->get();
		if ($query->getNumRows() > 0) {
			$user_details = $query->getRowArray();
			$response['user_id'] = $user_details['id'];
			$response['first_name'] = $user_details['first_name'];
			$response['last_name'] = $user_details['last_name'];
			$response['email'] = $user_details['email'];
			$social_links = json_decode($user_details['social_links'], true);
			$response['facebook'] = $social_links['facebook'];
			$response['twitter'] = $social_links['twitter'];
			$response['linkedin'] = $social_links['linkedin'];
			$response['biography'] = strip_tags($user_details['biography']);
			$response['image'] = $this->user_model->get_user_image_url($user_details['id']);

			$response['message'] = api_phrase('userdata_successfully');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['status'] = 403;
			$response['validity'] = 0;
			$response['message'] = api_phrase('user_not_found');
		}
		return $response;
	}

	public function update_userdata_post($user_id = "")
	{
		$response = array();
		$validity = $this->user_model->check_duplication('on_update', $this->request->getPost('email'), $user_id);
		if ($validity) {
			if (html_escape($this->request->getPost('first_name')) != "") {
				$data['first_name'] = html_escape($this->request->getPost('first_name'));
			} else {
				$response['status'] = 403;
				$response['validity'] = 0;
				$response['message'] = api_phrase('first_name_can_not_be_empty');
				return $response;
			}
			if (html_escape($this->request->getPost('last_name')) != "") {
				$data['last_name'] = html_escape($this->request->getPost('last_name'));
			} else {
				$response['status'] = 403;
				$response['validity'] = 0;
				$response['message'] = api_phrase('last_name_can_not_be_empty');
				return $response;
			}
			if (isset($_POST['email']) && html_escape($this->request->getPost('email')) != "") {
				$data['email'] = html_escape($this->request->getPost('email'));
			} else {
				$response['status'] = 403;
				$response['validity'] = 0;
				$response['message'] = api_phrase('email_can_not_be_empty');
				return $response;
			}

			$social_link['facebook'] = html_escape($this->request->getPost('facebook_link'));
			$social_link['twitter'] = html_escape($this->request->getPost('twitter_link'));
			$social_link['linkedin'] = html_escape($this->request->getPost('linkedin_link'));
			$data['social_links'] = json_encode($social_link);
			$data['biography'] = $this->request->getPost('biography');
			$this->db->where('id', $user_id);
			$this->db->table('users')->update($data);

			$response['status'] = 200;
			$response['validity'] = 1;
			$response['message'] = api_phrase('updated_successfully');
		} else {
			$response['status'] = 403;
			$response['validity'] = 0;
			$response['message'] = api_phrase('already_exist_this_email');
		}
		return $response;
	}


	public function courses_get($user_id = "")
	{
		$response = array();

		$this->db->select('id');
		$this->db->select('title');
		$this->db->select('user_id');
		$this->db->select('status');

		$this->db->groupStart();
        $this->db->like('user_id', ','.$user_id);
        $this->db->orLike('user_id', $user_id.',');
        $this->db->orWhere('creator', $user_id);
        $this->db->groupEnd();

		$query = $this->db->table('course')->get();
		$total_course_number = $query->getNumRows();
		if ($total_course_number > 0) {
			$response['courses'] = $query->getResultArray();
			$response['total_courses'] = $total_course_number;
			$response['message'] = api_phrase('course_list');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('course_not_found');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}


	public function add_course_form_get()
	{
		$response = array();
		$all_categories = array();
		$all_languages = array();

		$languages = $this->crud_model->get_all_languages();
		foreach ($languages as $key => $language) {
			$language_arr['name'] = $languages[$key];
			$all_languages[$key] = $language_arr;
		}

		$this->db->select('id');
		$this->db->select('name');
		$this->db->where('parent >', 0);
		$all_categories = $this->db->get_where('category')->getResultArray();

		$response['categories'] = $all_categories;
		$response['languages'] = $all_languages;
		$response['status'] = 200;
		$response['validity'] = 1;
		$response['message'] = api_phrase('course_form_data');

		return $response;
	}

	public function add_course_post($user_id = "")
	{
		$response = array();

		$data['course_type'] = 'general';
		$data['title'] = html_escape($this->request->getPost('title'));
		$data['outcomes'] = '[]';
		$data['language'] = $this->request->getPost('language');
		$data['sub_category_id'] = $this->request->getPost('sub_category_id');
		$category_details = $this->crud_model->get_category_details_by_id($this->request->getPost('sub_category_id'))->getRowArray();
		$data['category_id'] = $category_details['parent'];

		$data['requirements'] = '[]';
		$data['price'] = $this->request->getPost('price');
		$data['discount_flag'] = $this->request->getPost('discount_flag');
		$data['discounted_price'] = $this->request->getPost('discounted_price');
		$data['level'] = $this->request->getPost('level');
		$data['is_top_course'] = $this->request->getPost('is_top_course');
		$data['is_free_course'] = $this->request->getPost('is_free_course');

		$data['date_added'] = strtotime(date('D, d-M-Y'));
		$data['section'] = json_encode(array());

		$data['user_id'] = $user_id;
		$data['creator'] = $user_id;

		$data['is_admin'] = 0;

		$data['status'] = 'pending';

		if ($data['is_free_course'] == 1 || $data['is_free_course'] != 1 && $data['price'] > 0 && $data['discount_flag'] != 1 || $data['discount_flag'] == 1 && $data['discounted_price'] > 0) {
			$this->db->table('course')->insert($data);

			$response['message'] = api_phrase('course_has_been_added_successfully');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('please_fill_up_the_price_field');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}

	public function edit_course_form_get($course_id = "", $user_id = "")
	{
		$response = array();
		$all_categories = array();
		$all_languages = array();
		$course_details = $this->db->table('course')->where(array('id' => $course_id))->get()->getRowArray();

		$languages = $this->crud_model->get_all_languages();
		foreach ($languages as $key => $language) {
			$language_arr['name'] = $languages[$key];
			$all_languages[$key] = $language_arr;
		}

		$this->db->select('id');
		$this->db->select('name');
		$this->db->where('parent >', 0);
		$all_categories = $this->db->get_where('category')->getResultArray();
		$response['categories'] = $all_categories;
		$response['languages'] = $all_languages;

		$response['title'] = $course_details['title'];
		$response['short_description'] = $course_details['short_description'];
		$response['description'] = $course_details['description'];
		$response['sub_category_id'] = $course_details['sub_category_id'];
		$response['level'] = $course_details['level'];
		$response['language'] = $course_details['language'];
		$response['course_overview_provider'] = $course_details['course_overview_provider'];
		$response['video_url'] = $course_details['video_url'];
		$response['is_top_course'] = $course_details['is_top_course'];

		//Media section
		$course_media_images = array();
		$course_media_files = themeConfiguration(get_frontend_settings('theme'), 'course_media_files');
		foreach ($course_media_files as $course_media => $size) {
			$course_media_images[$course_media . '_size'] = $size;
			$course_media_images[$course_media] = $this->crud_model->get_course_thumbnail_url($course_details['id'], $course_media);
		}

		$response['theme'] = get_frontend_settings('theme');
		$response['course_media_images'] = $course_media_images;
		// End media section

		$response['meta_keywords'] = $course_details['meta_keywords'];
		$response['meta_description'] = $course_details['meta_description'];

		$response['status'] = 200;
		$response['validity'] = 1;
		$response['message'] = api_phrase('course_form_data');

		return $response;
	}

	public function update_course_post($course_id = "", $user_id = "")
	{
		$response = array();

		$course_details = $this->crud_model->get_course_by_id($course_id)->getRowArray();

		$data['title'] = $this->request->getPost('title');
		$data['short_description'] = html_escape($this->request->getPost('short_description'));
		$data['description'] = $this->request->getPost('description');
		$data['language'] = $this->request->getPost('language');
		$data['sub_category_id'] = $this->request->getPost('sub_category_id');
		$category_details = $this->crud_model->get_category_details_by_id($this->request->getPost('sub_category_id'))->getRowArray();
		$data['category_id'] = $category_details['parent'];
		$data['is_free_course'] = $this->request->getPost('is_free_course');
		$data['level'] = $this->request->getPost('level');
		$data['video_url'] = $this->request->getPost('course_overview_url');

		if ($this->request->getPost('course_overview_url') != "") {
			$data['course_overview_provider'] = html_escape($this->request->getPost('course_overview_provider'));
		} else {
			$data['course_overview_provider'] = "";
		}

		$data['meta_description'] = $this->request->getPost('meta_description');
		$data['meta_keywords'] = $this->request->getPost('meta_keywords');
		$data['last_modified'] = strtotime(date('D, d-M-Y'));

		if ($this->request->getPost('is_top_course') != 1) {
			$data['is_top_course'] = 0;
		} else {
			$data['is_top_course'] = 1;
		}


		$this->db->where('id', $course_id);
		$this->db->table('course')->update($data);

		// Upload different number of images according to activated theme. Data is taking from the config.json file
		$course_media_files = themeConfiguration(get_frontend_settings('theme'), 'course_media_files');
		foreach ($course_media_files as $course_media => $size) {
			if (isset($_FILES[$course_media]['name']) && $_FILES[$course_media]['name'] != "") {
				move_uploaded_file($_FILES[$course_media]['tmp_name'], 'uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id . '.jpg');
			}
		}

		$response['message'] = api_phrase('course_updated_successfully');
		$response['status'] = 200;
		$response['validity'] = 1;

		return $response;
	}

	public function update_course_status_get($course_id = "", $status = "", $user_id = "")
	{
		if ($status == 'active') {
			$status = 'pending';
		}

		$updater = array('status' => $status);
		$this->db->where('id', $course_id);
		$this->db->table('course')->update($updater);

		$response['message'] = api_phrase('status_updated');
		$response['status'] = 200;
		$response['validity'] = 1;

		return $response;
	}

	public function edit_course_requirements_get($course_id = "", $user_id = "")
	{
		$this->db->where('id', $course_id);
		$requirements = $this->db->table('course')->get()->getRow()->requirements;

		$response['total_requirements'] = count(json_decode($requirements));
		$response['requirements'] = json_decode($requirements);
		$response['message'] = api_phrase('course_requirements');
		$response['status'] = 200;
		$response['validity'] = 1;

		return $response;
	}

	public function update_course_requirements_post($course_id = "", $user_id = "")
	{
		$requirements = json_decode($_POST['requirements'], true);
		$data['requirements'] = $this->crud_model->trim_and_return_json($requirements);
		$this->db->where('id', $course_id);
		$this->db->table('course')->update($data);

		$response['message'] = api_phrase('requirements_updated');
		$response['status'] = 200;
		$response['validity'] = 1;

		return $response;
	}

	public function edit_course_outcomes_get($course_id = "", $user_id = "")
	{
		$this->db->where('id', $course_id);
		$outcomes = $this->db->table('course')->get()->getRow()->outcomes;

		$response['total_outcomes'] = count(json_decode($outcomes));
		$response['outcomes'] = json_decode($outcomes);
		$response['message'] = api_phrase('course_outcomes');
		$response['status'] = 200;
		$response['validity'] = 1;

		return $response;
	}

	public function update_course_outcomes_post($course_id = "", $user_id = "")
	{
		$outcomes = json_decode($_POST['outcomes'], true);
		$data['outcomes'] = $this->crud_model->trim_and_return_json($outcomes);
		$this->db->where('id', $course_id);
		$this->db->table('course')->update($data);

		$response['message'] = api_phrase('outcomes_updated');
		$response['status'] = 200;
		$response['validity'] = 1;

		return $response;
	}

	public function delete_course_get($course_id = "", $user_id = "")
	{
		$response = array();
		$query = $this->crud_model->get_courses_by_instructor_id($user_id);
		if ($query->getNumRows() > 0) {
			$this->crud_model->delete_course($course_id);
			$response['message'] = api_phrase('course_deleted_successfully');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('course_not_found');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}

	public function section_and_lesson_get($course_id = "", $user_id = "")
	{
		$response = array();
		$section_and_lesson = array();

		$this->db->orderBy('order', 'asc');
		$this->db->where('course_id', $course_id);
		$query = $this->db->table('section')->get();
		if ($query->getNumRows() > 0) {
			foreach ($query->getResultArray() as $key => $section) {
				$sections['id'] = $section['id'];
				$sections['title'] = $section['title'];

				$this->db->select('id');
				$this->db->select('video_type');
				$this->db->select('lesson_type');
				$this->db->select('attachment_type');
				$this->db->select('title');
				$this->db->orderBy('order', 'asc');
				$this->db->where('section_id', $section['id']);
				$lessons = $this->db->table('lesson')->get()->getResultArray();

				$section_and_lesson[$key] = $sections;
				$section_and_lesson[$key]['lessons'] = $lessons;
			}


			$response['section_and_lesson'] = $section_and_lesson;
			$response['course_id'] = $course_id;
			$response['message'] = api_phrase('course_sections');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('section_not_found');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}

	public function sections_get($course_id = "", $user_id = "")
	{
		$response = array();
		$sections = array();

		$this->db->orderBy('order', 'asc');
		$this->db->where('course_id', $course_id);
		$query = $this->db->table('section')->get();
		if ($query->getNumRows() > 0) {
			$response['sections'] = $query->getResultArray();
			$response['message'] = api_phrase('course_sections');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('section_not_found');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}


	public function add_section_post($course_id = "", $user_id = "")
	{
		$response = array();

		$data['title'] = html_escape($this->request->getPost('title'));
		$data['course_id'] = $course_id;
		$this->db->table('section')->insert($data);
		$section_id = $this->db->insertID();

		if ($section_id > 0) {
			$course_details = $this->crud_model->get_course_by_id($course_id)->getRowArray();
			$previous_sections = json_decode($course_details['section']);

			if (sizeof($previous_sections) > 0) {
				array_push($previous_sections, $section_id);
				$updater['section'] = json_encode($previous_sections);
				$this->db->where('id', $course_id);
				$this->db->table('course')->update($updater);
			} else {
				$previous_sections = array();
				array_push($previous_sections, $section_id);
				$updater['section'] = json_encode($previous_sections);
				$this->db->where('id', $course_id);
				$this->db->table('course')->update($updater);
			}

			$response['message'] = api_phrase('section_added');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('section_not_added');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}

	public function update_section_post($section_id = "", $user_id = "")
	{
		$response = array();

		if ($section_id > 0) {
			$data['title'] = $this->request->getPost('title');
			$this->db->where('id', $section_id);
			$this->db->table('section')->update($data);

			$response['message'] = api_phrase('section_updated');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('section_not_updated');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}

	public function delete_section_post($section_id = "", $course_id = "", $user_id = "")
	{
		$response = array();

		$this->db->where('id', $section_id);
		$this->db->table('section')->delete();

		$this->db->where('section_id', $section_id);
		$this->db->table('lesson')->delete();



		$course_details = $this->crud_model->get_course_by_id($course_id)->getRowArray();
		$previous_sections = json_decode($course_details['section']);

		if (sizeof($previous_sections) > 0) {
			$new_section = array();
			for ($i = 0; $i < sizeof($previous_sections); $i++) {
				if ($previous_sections[$i] != $section_id) {
					array_push($new_section, $previous_sections[$i]);
				}
			}
			$updater['section'] = json_encode($new_section);
			$this->db->where('id', $course_id);
			$this->db->table('course')->update($updater);
		}

		$response['message'] = api_phrase('section_deleted');
		$response['status'] = 200;
		$response['validity'] = 1;

		return $response;
	}

	//Start add lesson
	public function add_lesson_post($user_id = "")
	{
		$response = array();

		$data['course_id'] = html_escape($this->request->getPost('course_id'));
		$data['title'] = html_escape($this->request->getPost('title'));
		$data['section_id'] = html_escape($this->request->getPost('section_id'));

		$lesson_type_array = explode('-', $this->request->getPost('lesson_type'));
		$lesson_type = $lesson_type_array[0];

		$attachment_type = $lesson_type_array[1];
		$data['attachment_type'] = $attachment_type;
		$data['lesson_type'] = $lesson_type;

		if ($lesson_type == 'video') {
			// This portion is for web application's video lesson
			$lesson_provider = $this->request->getPost('lesson_provider');
			if ($lesson_provider == 'youtube' || $lesson_provider == 'vimeo') {
				$video_details = $this->video_model->getVideoDetails($_POST['video_url']);
				$duration_formatter = explode(':', $video_details['duration']);
				$hour = sprintf('%02d', $duration_formatter[0]);
				$min = sprintf('%02d', $duration_formatter[1]);
				$sec = sprintf('%02d', $duration_formatter[2]);
				$data['duration'] = $hour . ':' . $min . ':' . $sec;

				if ($this->request->getPost('video_url') == "" || $hour <= 0 && $min <= 0 && $sec <= 0) {
					$response['message'] = api_phrase('invalid_lesson_url');
					$response['status'] = 403;
					$response['validity'] = 0;
					return $response;
					die();
				}
				$data['video_url'] = html_escape($this->request->getPost('video_url'));
				$data['video_type'] = $video_details['provider'];
			} elseif ($lesson_provider == 'html5') {
				if ($this->request->getPost('html5_video_url') == "" || $this->request->getPost('html5_duration') == "") {
					$response['message'] = api_phrase('invalid_lesson_url');
					$response['status'] = 403;
					$response['validity'] = 0;
					return $response;
					die();
				}
				$data['video_url'] = html_escape($this->request->getPost('html5_video_url'));
				$duration_formatter = explode(':', $this->request->getPost('html5_duration'));
				$hour = sprintf('%02d', $duration_formatter[0]);
				$min = sprintf('%02d', $duration_formatter[1]);
				$sec = sprintf('%02d', $duration_formatter[2]);
				$data['duration'] = $hour . ':' . $min . ':' . $sec;
				$data['video_type'] = 'html5';
			 }elseif ($lesson_provider == 'google_drive') {
                if ($this->request->getPost('google_drive_video_url') == "" || $this->request->getPost('google_drive_video_duration') == "") {
                    $this->session->setFlashdata('error_message', get_phrase('invalid_lesson_url_and_duration'));
                    redirect(site_url(strtolower($this->session->get('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                }
                $data['video_url'] = html_escape($this->request->getPost('google_drive_video_url'));
                $duration_formatter = explode(':', $this->request->getPost('google_drive_video_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'google_drive';
			} else {
				$response['message'] = api_phrase('invalid_lesson_provider');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}

		} elseif ($lesson_type == "s3") {
			// SET MAXIMUM EXECUTION TIME 600
			ini_set('max_execution_time', '600');

			$fileName           = $_FILES['video_file_for_amazon_s3']['name'];
			$tmp                = explode('.', $fileName);
			$fileExtension      = strtoupper(end($tmp));

			$video_extensions = ['WEBM', 'MP4'];
			if (!in_array($fileExtension, $video_extensions)) {
				$response['message'] = api_phrase('please_select_valid_video_file');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}

			if ($this->request->getPost('amazon_s3_duration') == "") {
				$response['message'] = api_phrase('invalid_lesson_duration');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}

			$upload_loaction = get_settings('video_upload_location');
			$access_key = get_settings('amazon_s3_access_key');
			$secret_key = get_settings('amazon_s3_secret_key');
			$bucket = get_settings('amazon_s3_bucket_name');
			$region = get_settings('amazon_s3_region_name');

			$s3config = array(
				'region'  => $region,
				'version' => 'latest',
				'credentials' => [
					'key'    => $access_key, //Put key here
					'secret' => $secret_key // Put Secret here
				]
			);


			$tmpfile = $_FILES['video_file_for_amazon_s3'];

			$s3 = new Aws\S3\S3Client($s3config);
			$key = str_replace(".", "-" . rand(1, 9999) . ".", $tmpfile['name']);

			$result = $s3->putObject([
				'Bucket' => $bucket,
				'Key'    => $key,
				'SourceFile' => $tmpfile['tmp_name'],
				'ACL'   => 'public-read'
			]);

			$data['video_url'] = $result['ObjectURL'];
			$data['video_type'] = 'amazon';
			$data['lesson_type'] = 'video';
			$data['attachment_type'] = 'file';

			$duration_formatter = explode(':', $this->request->getPost('amazon_s3_duration'));
			$hour = sprintf('%02d', $duration_formatter[0]);
			$min = sprintf('%02d', $duration_formatter[1]);
			$sec = sprintf('%02d', $duration_formatter[2]);
			$data['duration'] = $hour . ':' . $min . ':' . $sec;

			$data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
			$data['video_type_for_mobile_application'] = "html5";
			$data['video_url_for_mobile_application'] = $result['ObjectURL'];
		} elseif ($lesson_type == "system") {
			// SET MAXIMUM EXECUTION TIME 600
			ini_set('max_execution_time', '600');

			$fileName           = $_FILES['system_video_file']['name'];

			// CHECKING IF THE FILE IS AVAILABLE AND FILE SIZE IS VALID
			if (array_key_exists('system_video_file', $_FILES)) {
				if ($_FILES['system_video_file']['error'] !== UPLOAD_ERR_OK) {
					$error_code = $_FILES['system_video_file']['error'];
					$response['message'] = phpFileUploadErrors($error_code);
					$response['status'] = 403;
					$response['validity'] = 0;
					return $response;
					die();
				}
			} else {
				$response['message'] = api_phrase('please_select_valid_video_file');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			};

			$tmp                = explode('.', $fileName);
			$fileExtension      = strtoupper(end($tmp));

			$video_extensions = ['WEBM', 'MP4'];

			if (!in_array($fileExtension, $video_extensions)) {
				$response['message'] = api_phrase('please_select_valid_video_file');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}

			// custom random name of the video file
			$uploadable_video_file    =  md5(uniqid(rand(), true)) . '.' . strtolower($fileExtension);

			if ($this->request->getPost('system_video_file_duration') == "") {
				$response['message'] = api_phrase('please_select_valid_video_file');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}



			$tmp_video_file = $_FILES['system_video_file']['tmp_name'];

			if (!file_exists('uploads/lesson_files/videos')) {
				mkdir('uploads/lesson_files/videos', 0777, true);
			}
			$video_file_path = 'uploads/lesson_files/videos/' . $uploadable_video_file;
			move_uploaded_file($tmp_video_file, $video_file_path);
			$data['video_url'] = site_url($video_file_path);
			$data['video_type'] = 'system';
			$data['lesson_type'] = 'video';
			$data['attachment_type'] = 'file';

			$duration_formatter = explode(':', $this->request->getPost('system_video_file_duration'));
			$hour = sprintf('%02d', $duration_formatter[0]);
			$min = sprintf('%02d', $duration_formatter[1]);
			$sec = sprintf('%02d', $duration_formatter[2]);
			$data['duration'] = $hour . ':' . $min . ':' . $sec;

			$data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
			$data['video_type_for_mobile_application'] = "html5";
			$data['video_url_for_mobile_application'] = site_url($video_file_path);
		}elseif($lesson_type == 'text' && $attachment_type == 'description'){
            $data['attachment'] = htmlspecialchars_(remove_js($this->request->getPost('text_description', false)));
		} else {
			if ($attachment_type == 'iframe') {
				if (empty($this->request->getPost('iframe_source'))) {
					$response['message'] = api_phrase('invalid_source');
					$response['status'] = 403;
					$response['validity'] = 0;
					return $response;
					die();
				}
				$data['attachment'] = $this->request->getPost('iframe_source');
			} else {
				if ($_FILES['attachment']['name'] == "") {
					$response['message'] = api_phrase('invalid_attachment');
					$response['status'] = 403;
					$response['validity'] = 0;
					return $response;
					die();
				} else {
					$fileName           = $_FILES['attachment']['name'];
					$tmp                = explode('.', $fileName);
					$fileExtension      = end($tmp);
					$uploadable_file    =  md5(uniqid(rand(), true)) . '.' . $fileExtension;
					$data['attachment'] = $uploadable_file;

					if (!file_exists('uploads/lesson_files')) {
						mkdir('uploads/lesson_files', 0777, true);
					}
					move_uploaded_file($_FILES['attachment']['tmp_name'], 'uploads/lesson_files/' . $uploadable_file);
				}
			}
		}

		$data['date_added'] = strtotime(date('D, d-M-Y'));
		$data['summary'] = htmlspecialchars_(remove_js($this->request->getPost('summary')));
		$data['is_free'] = htmlspecialchars_($this->request->getPost('is_free'));

		$this->db->table('lesson')->insert($data);
		$inserted_id = $this->db->insertID();

		if (isset($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['name'] != "") {
			if (!file_exists('uploads/thumbnails/lesson_thumbnails')) {
				mkdir('uploads/thumbnails/lesson_thumbnails', 0777, true);
			}
			move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/thumbnails/lesson_thumbnails/' . $inserted_id . '.jpg');
		}

		$response['message'] = api_phrase('lesson_added');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}
	//End add lesson

	public function lesson_all_data_get($lesson_id = "", $user_id = "")
	{
		$response = array();
		$lesson_id = $_GET['lesson_id'];
		$response['lesson_details'] = $this->db->table('lesson')->where(array('id' => $lesson_id))->get()->getRowArray();
		$response['message'] = api_phrase('lesson_data');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}

	public function update_lesson_post($lesson_id = "", $user_id = "")
	{

		$response = array();

		$data['title'] = html_escape($this->request->getPost('title'));
		$data['section_id'] = html_escape($this->request->getPost('section_id'));

		$lesson_type_array = explode('-', $this->request->getPost('lesson_type'));
		$lesson_type = $lesson_type_array[0];

		$attachment_type = $lesson_type_array[1];
		$data['attachment_type'] = $attachment_type;
		$data['lesson_type'] = $lesson_type;

		if ($lesson_type == 'video') {
			// This portion is for web application's video lesson
			$lesson_provider = $this->request->getPost('lesson_provider');
			if ($lesson_provider == 'youtube' || $lesson_provider == 'vimeo') {
				$video_details = $this->video_model->getVideoDetails($_POST['video_url']);
				$duration_formatter = explode(':', $video_details['duration']);
				$hour = sprintf('%02d', $duration_formatter[0]);
				$min = sprintf('%02d', $duration_formatter[1]);
				$sec = sprintf('%02d', $duration_formatter[2]);
				$data['duration'] = $hour . ':' . $min . ':' . $sec;

				if ($this->request->getPost('video_url') == "" || $hour <= 0 && $min <= 0 && $sec <= 0) {
					$response['message'] = api_phrase('invalid_lesson_url');
					$response['status'] = 403;
					$response['validity'] = 0;
					return $response;
					die();
				}
				$data['video_url'] = html_escape($this->request->getPost('video_url'));
				$data['video_type'] = $video_details['provider'];
			} elseif ($lesson_provider == 'html5') {
				if ($this->request->getPost('html5_video_url') == "" || $this->request->getPost('html5_duration') == "") {
					$response['message'] = api_phrase('invalid_lesson_url');
					$response['status'] = 403;
					$response['validity'] = 0;
					return $response;
					die();
				}
				$data['video_url'] = html_escape($this->request->getPost('html5_video_url'));
				$duration_formatter = explode(':', $this->request->getPost('html5_duration'));
				$hour = sprintf('%02d', $duration_formatter[0]);
				$min = sprintf('%02d', $duration_formatter[1]);
				$sec = sprintf('%02d', $duration_formatter[2]);
				$data['duration'] = $hour . ':' . $min . ':' . $sec;
				$data['video_type'] = 'html5';
			}elseif ($lesson_provider == 'google_drive') {
                if ($this->request->getPost('google_drive_video_url') == "" || $this->request->getPost('google_drive_video_duration') == "") {
                    $this->session->setFlashdata('error_message', get_phrase('invalid_lesson_url_and_duration'));
                    redirect(site_url(strtolower($this->session->get('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                }
                $data['video_url'] = html_escape($this->request->getPost('google_drive_video_url'));
                $duration_formatter = explode(':', $this->request->getPost('google_drive_video_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'google_drive';
			} else {
				$response['message'] = api_phrase('invalid_lesson_provider');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}
		} elseif ($lesson_type == "s3") {
			// SET MAXIMUM EXECUTION TIME 600
			ini_set('max_execution_time', '600');

			$fileName           = $_FILES['video_file_for_amazon_s3']['name'];
			$tmp                = explode('.', $fileName);
			$fileExtension      = strtoupper(end($tmp));

			$video_extensions = ['WEBM', 'MP4'];
			if (!in_array($fileExtension, $video_extensions)) {
				$response['message'] = api_phrase('please_select_valid_video_file');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}

			if ($this->request->getPost('amazon_s3_duration') == "") {
				$response['message'] = api_phrase('invalid_lesson_duration');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}

			$upload_loaction = get_settings('video_upload_location');
			$access_key = get_settings('amazon_s3_access_key');
			$secret_key = get_settings('amazon_s3_secret_key');
			$bucket = get_settings('amazon_s3_bucket_name');
			$region = get_settings('amazon_s3_region_name');

			$s3config = array(
				'region'  => $region,
				'version' => 'latest',
				'credentials' => [
					'key'    => $access_key, //Put key here
					'secret' => $secret_key // Put Secret here
				]
			);


			$tmpfile = $_FILES['video_file_for_amazon_s3'];

			$s3 = new Aws\S3\S3Client($s3config);
			$key = str_replace(".", "-" . rand(1, 9999) . ".", $tmpfile['name']);

			$result = $s3->putObject([
				'Bucket' => $bucket,
				'Key'    => $key,
				'SourceFile' => $tmpfile['tmp_name'],
				'ACL'   => 'public-read'
			]);

			$data['video_url'] = $result['ObjectURL'];
			$data['video_type'] = 'amazon';
			$data['lesson_type'] = 'video';
			$data['attachment_type'] = 'file';

			$duration_formatter = explode(':', $this->request->getPost('amazon_s3_duration'));
			$hour = sprintf('%02d', $duration_formatter[0]);
			$min = sprintf('%02d', $duration_formatter[1]);
			$sec = sprintf('%02d', $duration_formatter[2]);
			$data['duration'] = $hour . ':' . $min . ':' . $sec;

			$data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
			$data['video_type_for_mobile_application'] = "html5";
			$data['video_url_for_mobile_application'] = $result['ObjectURL'];
		} elseif ($lesson_type == "system") {
			// SET MAXIMUM EXECUTION TIME 600
			ini_set('max_execution_time', '600');

			if (isset($_FILES['system_video_file']['name']) && $_FILES['system_video_file']['name'] == "") {
				$fileName           = $_FILES['system_video_file']['name'];

				// CHECKING IF THE FILE IS AVAILABLE AND FILE SIZE IS VALID
				if (array_key_exists('system_video_file', $_FILES)) {
					if ($_FILES['system_video_file']['error'] !== UPLOAD_ERR_OK) {
						$error_code = $_FILES['system_video_file']['error'];
						$response['message'] = phpFileUploadErrors($error_code);
						$response['status'] = 403;
						$response['validity'] = 0;
						return $response;
						die();
					}
				}


				if ($fileName != "") {
					// custom random name of the video file
					$tmp                = explode('.', $fileName);
					$fileExtension      = strtoupper(end($tmp));
					$uploadable_video_file    =  md5(uniqid(rand(), true)) . '.' . strtolower($fileExtension);
					$video_extensions = ['WEBM', 'MP4'];

					if (!in_array($fileExtension, $video_extensions)) {
						$response['message'] = api_phrase('please_select_valid_video_file');
						$response['status'] = 403;
						$response['validity'] = 0;
						return $response;
						die();
					}

					$tmp_video_file = $_FILES['system_video_file']['tmp_name'];

					if (!file_exists('uploads/lesson_files/videos')) {
						mkdir('uploads/lesson_files/videos', 0777, true);
					}
					$video_file_path = 'uploads/lesson_files/videos/' . $uploadable_video_file;
					move_uploaded_file($tmp_video_file, $video_file_path);
					$data['video_url'] = site_url($video_file_path);
				}
			}

			if ($this->request->getPost('system_video_file_duration') == "") {
				$response['message'] = api_phrase('please_enter_video_duration');
				$response['status'] = 403;
				$response['validity'] = 0;
				return $response;
				die();
			}

			$data['video_type'] = 'system';
			$data['lesson_type'] = 'video';
			$data['attachment_type'] = 'file';

			$duration_formatter = explode(':', $this->request->getPost('system_video_file_duration'));
			$hour = sprintf('%02d', $duration_formatter[0]);
			$min = sprintf('%02d', $duration_formatter[1]);
			$sec = sprintf('%02d', $duration_formatter[2]);
			$data['duration'] = $hour . ':' . $min . ':' . $sec;

			$data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
			$data['video_type_for_mobile_application'] = "html5";
			if(isset($video_file_path)){
				$data['video_url_for_mobile_application'] = site_url($video_file_path);
			}
		}elseif($lesson_type == 'text' && $attachment_type == 'description'){
            $data['attachment'] = htmlspecialchars_(remove_js($this->request->getPost('text_description', false)));
		} else {
			if ($attachment_type == 'iframe') {
				if (empty($this->request->getPost('iframe_source'))) {
					$response['message'] = api_phrase('invalid_source');
					$response['status'] = 403;
					$response['validity'] = 0;
					return $response;
					die();
				}
				$data['attachment'] = $this->request->getPost('iframe_source');
			} else {
				if (isset($_FILES['attachment']['name']) && $_FILES['attachment']['name'] == "") {
					$fileName           = $_FILES['attachment']['name'];
					$tmp                = explode('.', $fileName);
					$fileExtension      = end($tmp);
					$uploadable_file    =  md5(uniqid(rand(), true)) . '.' . $fileExtension;
					$data['attachment'] = $uploadable_file;

					if (!file_exists('uploads/lesson_files')) {
						mkdir('uploads/lesson_files', 0777, true);
					}
					move_uploaded_file($_FILES['attachment']['tmp_name'], 'uploads/lesson_files/' . $uploadable_file);
				}
			}
			
		}

		$data['date_added'] = strtotime(date('D, d-M-Y'));
		$data['summary'] = $this->request->getPost('summary');
		$data['is_free'] = htmlspecialchars_($this->request->getPost('is_free'));

		$this->db->where('id', $lesson_id);
		$this->db->table('lesson')->update($data);

		if (isset($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['name'] != "") {
			if (!file_exists('uploads/thumbnails/lesson_thumbnails')) {
				mkdir('uploads/thumbnails/lesson_thumbnails', 0777, true);
			}
			move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/thumbnails/lesson_thumbnails/' . $inserted_id . '.jpg');
		}

		$response['message'] = api_phrase('lesson_updated');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}

	public function delete_lesson_get($lesson_id = "", $user_id = "")
	{
		$response = array();

		$this->db->where('id', $lesson_id);
		$this->db->table('lesson')->delete();
		$response['message'] = api_phrase('lesson_deleted');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}

	public function sort_post($user_id = "", $type = "")
	{
		$response = array();
		$item_json = $this->request->getPost('item_json');
		if ($type == "section") {
			$this->crud_model->sort_section($item_json);
		} elseif ($type == "lesson") {
			$this->crud_model->sort_lesson($item_json);
		}

		$response['message'] = api_phrase('item_sorted');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}

	//End  lesson


	public function course_pricing_form_get($user_id = "", $course_id = "")
	{
		$response = array();
		$this->db->where('id', $course_id);
		$course_details = $this->db->table('course')->get()->getRowArray();

		$course_pricing['id'] = $course_details['id'];
		$course_pricing['price'] = $course_details['price'];
		$course_pricing['discount_flag'] = $course_details['discount_flag'];
		$course_pricing['discounted_price'] = $course_details['discounted_price'];
		$course_pricing['is_free_course'] = $course_details['is_free_course'];
		$course_pricing['currency'] = currency();


		$response['course_details'] = $course_pricing;
		return $response;
	}

	public function update_course_price_post($user_id = "", $course_id = "")
	{
		$response = array();

		$data['price'] = $_POST['price'];
		$data['discount_flag'] = $_POST['discount_flag'];
		$data['discounted_price'] = $_POST['discounted_price'];
		$data['is_free_course'] = $_POST['is_free_course'];

		$this->db->where('id', $course_id);
		$this->db->table('course')->update($data);

		$response['message'] = api_phrase('course_pricing_updated');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}

	public function sales_report_get($user_id = "")
	{
		$response = array();

		if (isset($_GET['date_range']) && !empty($_GET['date_range'])) {
			$date_range = explode('_', $_GET['date_range']);
			$start_date = strtotime($date_range[0]);
			$end_date = strtotime($date_range[1] . ' 23:59:59');
		} else {
			$start_date = strtotime(date('d M Y'));
			$end_date = strtotime(date('d M Y 23:59:59'));
		}

		$sales_report = $this->crud_model->get_instructor_revenue($user_id, $start_date, $end_date);

		$response['sales_report'] = $sales_report;
		$response['message'] = api_phrase('sales_reports');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}

	public function details_of_sales_report_get($payment_id = "")
	{
		$response = array();

		$this->db->where('id', $payment_id);
		$payment_details = $this->db->table('payment')->get()->getRowArray();

		$student_details = $this->db->table('users')->where(array('id' => $payment_details['user_id']))->get()->getRowArray();

		$response['payment_details'] = $payment_details;
		$response['payment_date'] = date('d M Y', $payment_details['date_added']);
		$response['course_title'] = $this->db->table('course')->where(array('id' => $payment_details['course_id']))->get()->getRow()->title;
		$response['enrolled_student'] = $student_details['first_name'] . ' ' . $student_details['last_name'];
		$response['message'] = api_phrase('sales_reports');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}

	public function payout_report_get($user_id = "")
	{
		$response = array();

		$response['payouts'] = $this->crud_model->get_payouts($user_id, 'user')->getResultArray();
		$response['total_pending_amoun'] = $this->crud_model->get_total_pending_amount($user_id);
		$response['total_payout_amount'] = $this->crud_model->get_total_payout_amount($user_id);
		$response['requested_withdrawal_amount'] = strval($this->crud_model->get_requested_withdrawal_amount($user_id));

		$response['message'] = api_phrase('payout_reports');
		$response['status'] = 200;
		$response['validity'] = 1;
		return $response;
	}

	public function add_withdrawal_request_post($user_id = "")
	{
		$response = array();

		$total_pending_amount = $this->crud_model->get_total_pending_amount($user_id);
		$requested_withdrawal_amount = $this->request->getPost('withdrawal_amount');

		if ($total_pending_amount > 0 && $total_pending_amount >= $requested_withdrawal_amount) {
			$data['amount']     = $requested_withdrawal_amount;
			$data['user_id']    = $user_id;
			$data['date_added'] = strtotime(date('D, d M Y'));
			$data['status']     = 0;
			$this->db->table('payout')->insert($data);

			$response['message'] = api_phrase('withdrawal_requested');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('invalid_withdrawal_amount');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}

	public function delete_withdrawal_request_get($user_id = "")
	{
		$response = array();

		$checker = array(
			'user_id' => $user_id,
			'status' => 0
		);
		$requested_withdrawal = $this->db->table('payout')->where($checker)->get();
		if ($requested_withdrawal->getNumRows() > 0) {
			$this->db->where($checker);
			$this->db->table('payout')->delete();

			$response['message'] = api_phrase('withdrawal_deleted');
			$response['status'] = 200;
			$response['validity'] = 1;
		} else {
			$response['message'] = api_phrase('withdrawal_not_found');
			$response['status'] = 403;
			$response['validity'] = 0;
		}

		return $response;
	}

	function live_class_get($user_id = "", $course_id = ""){
		$this->db->where('course_id', $course_id);
		$live_class = $this->db->table('live_class')->get();

		if($live_class->getNumRows() > 0):
			return array('live_class' => $live_class->getRowArray(), 'status' => 200);
		else:
			return array('live_class' => [], 'status' => 403);
		endif;
	}

	function save_live_class_data_post($user_id = ""){
		$data['course_id'] = htmlspecialchars_($this->request->getPost('course_id'));
		$data['date'] = strtotime($this->request->getPost('date'));
		$data['time'] = strtotime($this->request->getPost('time'));
		$data['zoom_meeting_id'] = htmlspecialchars_($this->request->getPost('zoom_meeting_id'));
		$data['zoom_meeting_password'] = htmlspecialchars_($this->request->getPost('zoom_meeting_password'));
		$data['note_to_students'] = htmlspecialchars_($this->request->getPost('note_to_students'));

		$this->db->where('course_id', $data['course_id']);
		$live_class = $this->db->table('live_class')->get();
		if($live_class->getNumRows() > 0){
			$this->db->where('course_id', $data['course_id']);
			$this->db->table('live_class')->update($data);
		}else{
			$this->db->table('live_class')->insert($data);
		}
	}


	function live_class_settings($user_id = "", $return_type = ""){

		if($return_type == 'get'){
			$row = $this->db->table('live_class_settings')->where(array('user_id' => $user_id))->get();
			if($row->getNumRows() > 0){
				$response = $row->getRowArray();
				foreach(json_decode($response['credentials']) as $key => $credential){
					if($key == 'password'){
						$credential = base64_decode($credential);
					}
					$response[$key] = $credential;
				}
				$response['status'] = 200;
				$response['message'] = 'Live class';
			}else{
				$response = array('status' => 403, 'message' => 'Live class not found');
			}
			return $response;
		}else{
			$data['live_class_provider'] = htmlspecialchars_($this->request->getPost('live_class_provider'));
			$data['user_id'] = $user_id;

			$credentials = array(
				'sdk_key' => htmlspecialchars_($this->request->getPost('sdk_key')),
				'sdk_secret_key' => htmlspecialchars_($this->request->getPost('sdk_secret_key')),
				'email' => htmlspecialchars_($this->request->getPost('email')),
				'password' => base64_encode($this->request->getPost('password'))
			);
			$data['credentials'] = json_encode($credentials);

			$existing_credentials = $this->db->table('live_class_settings')->where(array('user_id' => $user_id))->get();
			if($existing_credentials->getNumRows() > 0){
				$this->db->where('user_id', $user_id);
				$this->db->table('live_class_settings')->update($data);
				$response = array('status' => 200, 'message' => 'Live class settings updated');
			}else{
				$this->db->table('live_class_settings')->insert($data);
				$response = array('status' => 200, 'message' => 'Live class settings added');
			}

			return $response;
		}
	}
}


