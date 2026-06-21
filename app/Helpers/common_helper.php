<?php defined('APPPATH') || exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package     CodeIgniter
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license     http://codeigniter.com/user_guide/license.html
 * @link        http://codeigniter.com
 * @since       Version 1.0
 * @filesource
 */
//phpinfo();
if (! function_exists('remove_js')) {
    function remove_js($description = '', $convert_string = false) {

        if ($convert_string == true) {
            $description = nl2br(htmlspecialchars($description));
        } else {
            //make script to string
            $description = str_replace("&lt;script&gt;", "", $description);
            $description = str_replace("&lt;/script&gt;", "", $description);

            //removing <script> tags
            $description = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $description);
            $description = preg_replace("/[<][^<]*script.*[>].*[<].*[\/].*script*[>]/i", "", $description);

            //removing inline js events
            $description = preg_replace("/([ ]on[a-zA-Z0-9_-]{1,}=\".*\")|([ ]on[a-zA-Z0-9_-]{1,}='.*')|([ ]on[a-zA-Z0-9_-]{1,}=.*[.].*)/", "", $description);
            $description = preg_replace('/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', "$1 $3", $description);

            //removing inline js
            $description = preg_replace("/([ ]href.*=\".*javascript:.*\")|([ ]href.*='.*javascript:.*')|([ ]href.*=.*javascript:.*)/i", "", $description);
        }

        return $description;
    }
}


if (! function_exists('htmlspecialchars_')) {
    function htmlspecialchars_($description = '') {
        return htmlspecialchars($description ?? "");
    }
}
if (! function_exists('htmlspecialchars_decode_')) {
    function htmlspecialchars_decode_($description = '') {
        return htmlspecialchars_decode($description ?? "");
    }
}

if (!function_exists('isJson')) {
    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

if (!function_exists('set_url_history')) {
    function set_url_history($url) {
        session()->set('url_history', $url);
    }
}



if (!function_exists('upload_description_images')) {
    function upload_description_images($description = "", $path = ""){
        // Find all the image tags in the Summernote content
        preg_match_all('/<img[^>]+src="data:image\/([a-zA-Z0-9]+);base64,([^"]+)"/', $description, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            // Define the path to where you want to save the image
            $imagePath = $path.'/' . time().random(20).'.'.$match[1];
            $image_tag = str_replace('data:image/png;base64,'.$match[2], base_url($imagePath), $match[0]);
            $description = str_replace($match[0], $image_tag, $description);


            file_put_contents($imagePath, base64_decode($match[2]));
        }
        return $description;
    }
}

if (!function_exists('remove_description_images')) {
    function remove_description_images($description = ""){
        // Find all the image tags in the Summernote content
        preg_match_all('/<img[^>]+>/i', $description, $matches);
        foreach ($matches[0] as $match) {
            //$match this is image tag
            preg_match('/src=[\'"]([^\'"]+)[\'"]/i', $match, $srcMatches);
            $image_path_arr = explode('uploads/', $srcMatches[1]);
            $image_path = 'uploads/'.$image_path_arr[1];
            if(file_exists($image_path)){
                unlink($image_path);
            }
        }
    }
}

if (!function_exists('has_permission')) {
    function has_permission($permission_for = '', $admin_id = '')
    {
        $db = \Config\Database::connect();
        if (empty($admin_id)) {
            $admin_id = session()->get('user_id');
        }
        $row = $db->table('permissions')->where('admin_id', $admin_id)->get()->getRowArray();
        if (!$row) {
            return true;
        }
        $permissions = json_decode($row['permissions'] ?? '[]');
        return is_array($permissions) && in_array($permission_for, $permissions);
    }
}



if (!function_exists('check_permission')) {
    function check_permission($permission_for)
    {
        if (!has_permission($permission_for)) {
            session()->setFlashdata('error_message', get_phrase('you_are_not_authorized_to_access_this_page'));
            return redirect()->to(site_url('admin/dashboard'))->send();
        }
    }
}





if (!function_exists('is_root_admin')) {
    function is_root_admin($admin_id = '')
    {
        $db = \Config\Database::connect();
        if (empty($admin_id)) {
            $admin_id = session()->get('user_id');
        }
        return $db->table('permissions')->where('admin_id', $admin_id)->countAllResults() == 0;
    }
}



if (!function_exists('custom_date')) {
    function custom_date($strtotime = "", $format = "")
    {
        if ($format == "") {
            return date('d', $strtotime) . ' ' . site_phrase(date('M', $strtotime)) . ' ' . date('Y', $strtotime);
        } elseif ($format == 1) {
            return site_phrase(date('D', $strtotime)) . ', ' . date('d', $strtotime) . ' ' . site_phrase(date('M', $strtotime)) . ' ' . date('Y', $strtotime);
        }
    }
}

if (!function_exists('nice_number')) {
    function nice_number($n) {
        // first strip any formatting;
        $n = (0+str_replace(",", "", $n));

        // is this a number?
        if (!is_numeric($n)) return false;

        // now filter it;
        if($n <= 1000) return number_format($n);
        elseif ($n > 1000000000000) return round(($n/1000000000000), 1).'T';
        elseif ($n > 1000000000) return round(($n/1000000000), 1).'M';
        elseif ($n > 1000000) return round(($n/1000000), 1).'M';
        elseif ($n > 1000) return round(($n/1000), 1).'k';

        return number_format($n);
    }
}

if (!function_exists('nice_number')) {
    function nice_number($n) {
        // first strip any formatting;
        $n = (0+str_replace(",", "", $n));

        // is this a number?
        if (!is_numeric($n)) return false;

        // now filter it;
        if($n <= 1000) return number_format($n);
        elseif ($n > 1000000000000) return round(($n/1000000000000), 1).'T';
        elseif ($n > 1000000000) return round(($n/1000000000), 1).'M';
        elseif ($n > 1000000) return round(($n/1000000), 1).'M';
        elseif ($n > 1000) return round(($n/1000), 1).'k';

        return number_format($n);
    }
}

if (! function_exists('get_past_time')) {
    function get_past_time( $time = "" ) {
        $time_difference = time() - $time;

        if( $time_difference < 1 ) { return 'less than 1 second ago'; }

        //864000 = 10 days
        if($time_difference > 864000){ return custom_date($time, 1); }

        $condition = array( 12 * 30 * 24 * 60 * 60 =>  site_phrase('year'),
                    30 * 24 * 60 * 60       =>  site_phrase('month'),
                    24 * 60 * 60            =>  site_phrase('day'),
                    60 * 60                 =>  site_phrase('hour'),
                    60                      =>  site_phrase('minute'),
                    1                       =>  site_phrase('second')
        );

        foreach( $condition as $secs => $str )
        {
            $d = $time_difference / $secs;

            if( $d >= 1 )
            {
                $t = round( $d );
                return $t . ' ' . $str . ( $t > 1 ? 's' : '' ) .' '. site_phrase('ago');
            }
        }
    }
}

if (! function_exists('resizeImage')) {
    function resizeImage($filelocation = '', $target_path = '', $width = '', $height = '') {
        $width = $width === '' ? 200 : (int) $width;
        $image = \Config\Services::image()->withFile($filelocation);
        try {
            if ($height === '') {
                $image->resize($width, 0, true)->save($target_path);
            } else {
                $image->resize($width, (int) $height, false)->save($target_path);
            }
            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Image resize failed: ' . $e->getMessage());
            return false;
        }
    }
}



if (!function_exists('get_settings')) {
    function get_settings($key = '', $type = '')
    {
        $db = \Config\Database::connect();
        $row = $db->table('settings')->select('value')->where('key', $key)->get()->getRow();
        $result = $row ? $row->value : null;
        return $type ? json_decode((string) $result, true) : $result;
    }
}



if (!function_exists('currency')) {
    function currency($price = '')
    {
        $db = \Config\Database::connect();
        $currencyRow = $db->table('settings')->select('value')->where('key', 'system_currency')->get()->getRow();
        $currencyCode = $currencyRow ? $currencyRow->value : '';
        $symbolRow = $db->table('currency')->select('symbol')->where('code', $currencyCode)->get()->getRow();
        $symbol = $symbolRow ? $symbolRow->symbol : '';
        if ($price === '') {
            return $symbol;
        }
        $positionRow = $db->table('settings')->select('value')->where('key', 'currency_position')->get()->getRow();
        $position = $positionRow ? $positionRow->value : 'left';
        return match ($position) {
            'right' => $price . $symbol,
            'right-space' => $price . ' ' . $symbol,
            'left-space' => $symbol . ' ' . $price,
            default => $symbol . $price,
        };
    }
}



if (!function_exists('currency_code_and_symbol')) {
    function currency_code_and_symbol($type = '')
    {
        $db = \Config\Database::connect();
        $currencyRow = $db->table('settings')->select('value')->where('key', 'system_currency')->get()->getRow();
        $currencyCode = $currencyRow ? $currencyRow->value : '';
        $symbolRow = $db->table('currency')->select('symbol')->where('code', $currencyCode)->get()->getRow();
        $symbol = $symbolRow ? $symbolRow->symbol : '';
        return $type === '' ? $symbol : $currencyCode;
    }
}



if (!function_exists('get_frontend_settings')) {
    function get_frontend_settings($key = '')
    {
        $db = \Config\Database::connect();
        $row = $db->table('frontend_settings')->select('value')->where('key', $key)->get()->getRow();
        $result = $row ? $row->value : null;
        if ($key === 'banner_image') {
            $bannerImages = json_decode((string) $result, true) ?: [];
            return $bannerImages[get_frontend_settings('home_page')] ?? null;
        }
        return $result;
    }
}



if (!function_exists('get_current_banner')) {
    function get_current_banner($key = '')
    {
        $db = \Config\Database::connect();
        $row = $db->table('frontend_settings')->select('value')->where('key', $key)->get()->getRow();
        $bannerImages = json_decode((string) ($row->value ?? ''), true) ?: [];
        $activeHomePage = get_frontend_settings('home_page');
        return $bannerImages[$activeHomePage] ?? null;
    }
}



if (!function_exists('slugify')) {
    function slugify($text)
    {
        if (empty($text))
            return 'n-a';

        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        //$text = preg_replace('~[^-\w]+~', '', $text);
        return $text;
    }
}

if (!function_exists('get_video_extension')) {
    // Checks if a video is youtube, vimeo or any other
    function get_video_extension($url)
    {
        if (strpos($url, '.mp4') > 0) {
            return 'mp4';
        } elseif (strpos($url, '.webm') > 0) {
            return 'webm';
        } else {
            return 'unknown';
        }
    }
}

if (!function_exists('ellipsis')) {
    // Checks if a video is youtube, vimeo or any other
    function ellipsis($long_string, $max_character = 30)
    {
        $short_string = strlen($long_string) > $max_character ? mb_substr($long_string, 0, $max_character) . "..." : $long_string;
        return $short_string;
    }
}

// This function helps us to decode the theme configuration json file and return that array to us
if (!function_exists('themeConfiguration')) {
    function themeConfiguration($theme, $key = "")
    {
        $themeConfigs = [];
        if (file_exists('assets/frontend/' . $theme . '/config/theme-config.json')) {
            $themeConfigs = file_get_contents('assets/frontend/' . $theme . '/config/theme-config.json');
            $themeConfigs = json_decode($themeConfigs, true);
            if ($key != "") {
                if (array_key_exists($key, $themeConfigs)) {
                    return $themeConfigs[$key];
                } else {
                    return false;
                }
            } else {
                return $themeConfigs;
            }
        } else {
            return false;
        }
    }
}

// Human readable time
if (!function_exists('readable_time_for_humans')) {
    function readable_time_for_humans($duration)
    {
        if ($duration) {
            $duration_array = explode(':', $duration);
            $hour   = $duration_array[0];
            $minute = $duration_array[1];
            $second = $duration_array[2];
            if ($hour > 0) {
                $duration = $hour . ' ' . get_phrase('hr') . ' ' . $minute . ' ' . get_phrase('min');
            } elseif ($minute > 0) {
                if ($second > 0) {
                    $duration = ($minute + 1) . ' ' . get_phrase('min');
                } else {
                    $duration = $minute . ' ' . get_phrase('min');
                }
            } elseif ($second > 0) {
                $duration = $second . ' ' . get_phrase('sec');
            } else {
                $duration = '00:00';
            }
        } else {
            $duration = '00:00';
        }
        return $duration;
    }
}

// Human readable time
if (!function_exists('seconds_to_time_format')) {
    function seconds_to_time_format($seconds = "0")
    {
        if ($seconds) {
            $hours = floor($seconds / 3600); // Calculate the number of hours
            $minutes = floor(($seconds % 3600) / 60); // Calculate the number of minutes
            $totalSeconds = $seconds % 60; // Calculate the number of seconds

            return sprintf("%02d:%02d:%02d", $hours, $minutes, $totalSeconds); // Format the time as HH:MM:SS
        } else {
            $duration = '00:00:00';
        }
        return $duration;
    }
}

// Human readable time
if (!function_exists('time_to_seconds')) {
    function time_to_seconds($time)
    {
        $time = explode(':', $time);
        $seconds = $time[0] * 3600;
        $seconds = $seconds + ($time[1] * 60);
        return $seconds = $seconds + $time[2];
    }
}

if (!function_exists('trimmer')) {
    function trimmer($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text))
            return 'n-a';
        return $text;
    }
}

if (!function_exists('lesson_progress')) {
    function lesson_progress($lesson_id = '', $user_id = '', $course_id = '')
    {
        $db = \Config\Database::connect();
        if ($user_id === '') {
            $user_id = session()->get('user_id');
        }
        if ($course_id === '') {
            $lesson = $db->table('lesson')->select('course_id')->where('id', $lesson_id)->get()->getRow();
            $course_id = $lesson ? $lesson->course_id : '';
        }
        $row = $db->table('watch_histories')->where(['course_id' => $course_id, 'student_id' => $user_id])->get()->getRowArray();
        if (!$row) {
            return 0;
        }
        $lessonIds = json_decode($row['completed_lesson'] ?? '[]', true);
        return is_array($lessonIds) && in_array($lesson_id, $lessonIds) ? 1 : 0;
    }
}


if (!function_exists('course_progress')) {
    function course_progress($course_id = '', $user_id = '', $return_type = '')
    {
        $db = \Config\Database::connect();
        if ($user_id === '') {
            $user_id = session()->get('user_id');
        }
        $watchHistory = $db->table('watch_histories')->where(['course_id' => $course_id, 'student_id' => $user_id])->get()->getRowArray();
        $completedLessons = isset($watchHistory['completed_lesson']) ? json_decode($watchHistory['completed_lesson'], true) : [];
        if (!is_array($completedLessons)) {
            $completedLessons = [];
        }
        $lessonRows = $db->table('lesson')->select('id')->where('course_id', $course_id)->get()->getResultArray();
        $lessonIds = array_column($lessonRows, 'id');
        $filteredCompletedLessons = array_values(array_intersect($completedLessons, $lessonIds));
        if ($return_type === 'completed_lesson_ids') {
            return $filteredCompletedLessons;
        }
        $totalLessons = count($lessonIds);
        $calculatedProgress = $totalLessons > 0 ? (count($filteredCompletedLessons) / $totalLessons) * 100 : 0;
        if (!empty($watchHistory)) {
            $existingProgress = $watchHistory['course_progress'] ?? 0;
            $watchHistoryId = $watchHistory['watch_history_id'] ?? null;
            if ($watchHistoryId !== null && ($completedLessons !== $filteredCompletedLessons || $calculatedProgress != $existingProgress)) {
                $db->table('watch_histories')->where('watch_history_id', $watchHistoryId)->update([
                    'completed_lesson' => json_encode($filteredCompletedLessons),
                    'course_progress' => $calculatedProgress,
                ]);
            }
        }
        return $calculatedProgress;
    }
}




// RANDOM NUMBER GENERATOR FOR ELSEWHERE
if (!function_exists('random')) {
    function random($length_of_string)
    {
        // String of all alphanumeric character
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shufle the $str_result and returns substring
        // of specified length
        return substr(str_shuffle($str_result), 0, $length_of_string);
    }
}

// RANDOM NUMBER GENERATOR FOR ELSEWHERE
if (!function_exists('phpFileUploadErrors')) {
    function phpFileUploadErrors($error_code)
    {
        $phpFileUploadErrorsArray = array(
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        );
        return $phpFileUploadErrorsArray[$error_code];
    }
}


// course bundle subscription data
if (!function_exists('get_bundle_validity')) {
    function get_bundle_validity($bundle_id = '', $user_id = '')
    {
        $db = \Config\Database::connect();
        if ($user_id === '') {
            $user_id = session()->get('user_id');
        }
        if ($bundle_id === '') {
            return 'invalid';
        }
        $addonExists = $db->table('addons')->where('unique_identifier', 'course_bundle')->countAllResults() > 0;
        if (!$addonExists) {
            return 'invalid';
        }
        $courseBundle = $db->table('course_bundle')->where('id', $bundle_id)->get()->getRowArray();
        if (!$courseBundle) {
            return 'invalid';
        }
        $bundlePayment = $db->table('bundle_payment')->where(['bundle_id' => $bundle_id, 'user_id' => $user_id])->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
        if (!$bundlePayment) {
            return 'invalid';
        }
        $today = strtotime(date('d M Y'));
        $maxValidDate = ($bundlePayment['date_added'] ?? 0) + (($courseBundle['subscription_limit'] ?? 0) * 86400);
        return $today <= $maxValidDate ? 'valid' : 'expire';
    }
}




if (!function_exists('get_lesson_type')) {
    function get_lesson_type($lesson_id = '')
    {
        $db = \Config\Database::connect();
        $lesson = $db->table('lesson')->where('id', $lesson_id)->get()->getRowArray();
        if (!$lesson) {
            return null;
        }
        $lessonType = strtolower((string) ($lesson['lesson_type'] ?? ''));
        $videoType = strtolower((string) ($lesson['video_type'] ?? ''));
        $attachmentType = strtolower((string) ($lesson['attachment_type'] ?? ''));
        if ($lessonType === 'video' && $videoType === 'youtube') return 'youtube_video_url';
        if ($lessonType === 'video' && $videoType === 'google_drive') return 'google_drive_video_url';
        if ($lessonType === 'video' && $videoType === 'vimeo') return 'vimeo_video_url';
        if ($lessonType === 'video' && $videoType === 'amazon') return 'amazon_video_url';
        if ($lessonType === 'video' && $videoType === 'system') return 'video_file';
        if ($lessonType === 'audio') return 'audio_file';
        if ($lessonType === 'video' && $videoType === 'academy_cloud') return 'academy_cloud';
        if ($lessonType === 'video' && $videoType === 'html5') return 'html5_video_url';
        if ($lessonType === 'quiz') return 'quiz';
        if ($lessonType === 'text') return 'text';
        if ($lessonType === 'other' && $attachmentType === 'txt') return 'text_file';
        if ($lessonType === 'other' && $attachmentType === 'pdf') return 'pdf_file';
        if ($lessonType === 'other' && $attachmentType === 'doc') return 'doc_file';
        if ($lessonType === 'other' && $attachmentType === 'img') return 'image_file';
        if ($lessonType === 'wasabi' && $attachmentType === 'video') return 'wasabi_video_url';
        if ($lessonType === 'wasabi' && $attachmentType === 'image') return 'wasabi_image_file';
        if ($lessonType === 'wasabi' && $attachmentType === 'document') return 'wasabi_document_file';
        if ($lessonType === 'wasabi' && $attachmentType === 'text') return 'wasabi_text_file';
        return 'iframe';
    }
}



if (!function_exists('next_lesson')) {
    function next_lesson($course_id = '', $lesson_id = '')
    {
        $crudModel = model('App\\Models\\Crud_model');
        $lessonList = $crudModel->get_lessons('course', $course_id)->getResultArray();
        $currentIndex = -1;
        foreach ($lessonList as $index => $lesson) {
            if (($lesson['id'] ?? null) == $lesson_id) {
                $currentIndex = $index;
                break;
            }
        }
        return ($currentIndex !== -1 && isset($lessonList[$currentIndex + 1])) ? $lessonList[$currentIndex + 1]['id'] : null;
    }
}



if (!function_exists('get_seo_data')) {
    function get_seo_data() {
        $route = trim(service('uri')->getPath(), '/');
        if ($route === '') {
            $route = 'home';
        } elseif ($route === 'home/courses') {
            $route = 'courses';
        } elseif ($route === 'home/contact_us') {
            $route = 'contact_us';
        } elseif ($route === 'home/about_us') {
            $route = 'about_us';
        } elseif ($route === 'home/privacy_policy') {
            $route = 'privacy_policy';
        } elseif ($route === 'home/terms_and_condition') {
            $route = 'terms_and_condition';
        } elseif ($route === 'home/refund_policy') {
            $route = 'refund_policy';
        } elseif ($route === 'addons/bootcamp/bootcamp_list') {
            $route = 'bootcamps';
        }
        $crudModel = model('App\\Models\\Crud_model');
        $seoData = $crudModel->get_seo_by_route($route);
        return $seoData ? (array) $seoData : null;
    }
}



if (!function_exists('validate_cart_items')) {
    function validate_cart_items() {
        $db = \Config\Database::connect();
        $cartItems = session()->get('cart_items') ?? [];
        if (!is_array($cartItems) || empty($cartItems)) {
            return;
        }
        $validCourseRows = $db->table('course')->select('id')->whereIn('id', $cartItems)->get()->getResultArray();
        $validCourseIds = array_column($validCourseRows, 'id');
        $filteredCartItems = array_values(array_filter($cartItems, static fn($courseId) => in_array($courseId, $validCourseIds)));
        if ($cartItems !== $filteredCartItems) {
            session()->set('cart_items', $filteredCartItems);
        }
    }
}



// ------------------------------------------------------------------------
/* End of file common_helper.php */
/* Location: ./system/helpers/common.php */


