<?php

namespace App\Models\addons;

use App\Models\NativeBaseModel;
class Ai_model extends NativeBaseModel
{

    function __construct()
    {
        parent::__construct();
        /*cache control*/

    }

    function chat_gpt()
    {
        $open_ai = get_settings('open_ai', true);

        if ($_POST['service_type'] == 'Course thumbnail') {
            $number_of_image_creation = (int)$open_ai['number_of_image_creation'];
            $prompt = $_POST['keyword'];
            $curlopt_post = ['prompt' => $prompt, 'size' => '512x512', 'n' => $number_of_image_creation];
            $curlopt_post_url = 'https://api.openai.com/v1/images/generations';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $curlopt_post_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $open_ai['ai_secret_key'],
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlopt_post));

            $response = curl_exec($ch);

            curl_close($ch);

            $response_arr = json_decode($response, true);
            if (array_key_exists('choices', $response_arr)) {
                return $response_arr['choices'][0]['text'];
            } else {
                $this->session->set('ai_image', $response_arr['data']);
                return json_encode($response_arr['data']);
            }
        } else {
            $prompt = "write me a ";
            $prompt .= $_POST['service_type'];
            $prompt .= " on ";
            $prompt .= $_POST['keyword'];
            $prompt .= " in ";
            $prompt .= $_POST['language'];
            $prompt .= " language";


            $instructions = "You are a ".$_POST['service_type']." writer";
            return $this->gpt_assistant($prompt, $instructions, true);
        }

    }

    function gpt_assistant($gpt_assistant_command = "", $instructions = "", $return = false)
    {
        $user_details = $this->user_model->get_all_user($this->session->get('user_id'))->getRowArray();

        if ($gpt_assistant_command == '') {
            $gpt_assistant_command = $this->request->getPost('gpt_assistant_command');
            if ($gpt_assistant_command == '') {
                return;
            }
        }
        if ($instructions == '') {
            $instructions = "You are a helpful assistant. Instructions: Please address the user as " . $user_details['first_name'] . " " . $user_details['last_name'] . ".";
        }

        $ai_settings = get_settings('open_ai', true);
        $endpoint = "https://api.openai.com/v1/chat/completions";
        $OPENAI_API_KEY = $ai_settings['ai_secret_key'];
        // Instructions: Please address the user as " . $user_details['first_name'] . " " . $user_details['last_name'] . ". The user has a premium account.
        $data = array(
            "model" => $ai_settings['model'],
            "messages" => array(
                array(
                    "role" => "system",
                    "content" => $instructions
                ),
                array(
                    "role" => "user",
                    "content" => "$gpt_assistant_command"
                )
            )
        );

        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $OPENAI_API_KEY
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            //print_r($response);
            $response = json_decode($response, true);

            if($return){
                return $response['choices'][0]['message']['content'] ?? '';
            }else{
                echo $response['choices'][0]['message']['content'] ?? '';
            }
        }
    }


    function create_assistant()
    {
        $ai_settings = get_settings('open_ai', true);
        $endpoint = 'https://api.openai.com/v1/assistants';
        $OPENAI_API_KEY = $ai_settings['ai_secret_key'];

        $data = array(
            "instructions" => "You are a help full assistant",
            "name" => $ai_settings['name_of_personal_assistant'],
            "tools" => array(
                array("type" => "code_interpreter")
            ),
            "model" => "gpt-3.5-turbo-0125" // "gpt-4" //gpt-4 for premium account
        );
        $data_string = json_encode($data);
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $OPENAI_API_KEY,
                'OpenAI-Beta: assistants=v1',
                'Content-Length: ' . strlen($data_string)
            )
        );
        $result = curl_exec($ch);
        echo $result;
        //Example of response: {"id":"asst_ETPFQkNDTPODYteskKSDIn9u","object":"assistant","created_at":1708444069,"name":"Uban","description":null,"model":"gpt-3.5-turbo-0125","instructions":"You are a help full assistant","tools":[{"type":"code_interpreter"}],"file_ids":[],"metadata":{}}
    }

    function create_assistant_thread()
    {
        $ai_settings = get_settings('open_ai', true);
        $endpoint = "https://api.openai.com/v1/threads";
        $OPENAI_API_KEY = $ai_settings['ai_secret_key'];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $OPENAI_API_KEY,
            'OpenAI-Beta: assistants=v1'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        echo $result;
        //Example of response: {"id":"thread_0fuEKv0PnBMsLnWlRqz4QvfF","object":"thread","created_at":1708444576,"metadata":{},"tool_resources":[]}
    }

    function add_a_message_to_a_thread($thread = "")
    {
        $ai_settings = get_settings('open_ai', true);
        $endpoint = "https://api.openai.com/v1/threads/$thread/messages";
        $OPENAI_API_KEY = $ai_settings['ai_secret_key'];

        $data = array(
            "role" => "user",
            "content" => "Hello"
        );

        $data_string = json_encode($data);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $OPENAI_API_KEY,
            'OpenAI-Beta: assistants=v1'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        echo $result;
        //Example of response: {"id":"msg_y9TwDv2x212Ei4IYSDO1NOvl","object":"thread.message","created_at":1708446301,"assistant_id":null,"thread_id":"thread_0fuEKv0PnBMsLnWlRqz4QvfF","run_id":null,"role":"user","content":[{"type":"text","text":{"value":"Hello","annotations":[]}}],"file_ids":[],"metadata":{}}
    }

    function run_the_assistant($thread = "", $assistant_id = "")
    {
        $user_details = $this->user_model->get_all_user($this->session->get('user_id'))->getRowArray();

        $ai_settings = get_settings('open_ai', true);
        $endpoint = "https://api.openai.com/v1/threads/$thread/runs";
        $OPENAI_API_KEY = $ai_settings['ai_secret_key'];

        $data = array(
            "assistant_id" => $assistant_id,
            "instructions" => "Please address the user as " . $user_details['first_name'] . " " . $user_details['last_name'] . ". The user has a premium account."
        );

        $data_string = json_encode($data);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $OPENAI_API_KEY,
            'OpenAI-Beta: assistants=v1'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        echo $result;
        //Example of response: {"id":"run_2SNSsNLgEK8FR1SlxtgpAahD","object":"thread.run","created_at":1708450773,"assistant_id":"asst_ETPFQkNDTPODYteskKSDIn9u","thread_id":"thread_0fuEKv0PnBMsLnWlRqz4QvfF","status":"queued","started_at":null,"expires_at":1708451373,"cancelled_at":null,"failed_at":null,"completed_at":null,"last_error":null,"model":"gpt-3.5-turbo-0125","instructions":"Please address the user as John Doe. The user has a premium account.","tools":[{"type":"code_interpreter"}],"file_ids":[],"metadata":{},"usage":null}
    }

    function get_assistant_response($thread = "")
    {
        $ai_settings = get_settings('open_ai', true);
        $endpoint = "https://api.openai.com/v1/threads/$thread/messages";
        $OPENAI_API_KEY = $ai_settings['ai_secret_key'];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $OPENAI_API_KEY,
            'OpenAI-Beta: assistants=v1'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        echo $result;
        //Example response: {"object":"list","data":[{"id":"msg_f52ZrqeyycPQPw8xa47DTNaS","object":"thread.message","created_at":1708450774,"assistant_id":"asst_ETPFQkNDTPODYteskKSDIn9u","thread_id":"thread_0fuEKv0PnBMsLnWlRqz4QvfF","run_id":"run_2SNSsNLgEK8FR1SlxtgpAahD","role":"assistant","content":[{"type":"text","text":{"value":"Hello John! How can I assist you today?","annotations":[]}}],"file_ids":[],"metadata":{}},{"id":"msg_p4jRIVpktpDQqfLRi612GiAa","object":"thread.message","created_at":1708450717,"assistant_id":"asst_ETPFQkNDTPODYteskKSDIn9u","thread_id":"thread_0fuEKv0PnBMsLnWlRqz4QvfF","run_id":"run_4zZ8ZukNvZdaGsjjFtJHGvhx","role":"assistant","content":[{"type":"text","text":{"value":"Hello John! How can I assist you today?","annotations":[]}}],"file_ids":[],"metadata":{}},{"id":"msg_y9TwDv2x212Ei4IYSDO1NOvl","object":"thread.message","created_at":1708446301,"assistant_id":null,"thread_id":"thread_0fuEKv0PnBMsLnWlRqz4QvfF","run_id":null,"role":"user","content":[{"type":"text","text":{"value":"Hello","annotations":[]}}],"file_ids":[],"metadata":{}}],"first_id":"msg_f52ZrqeyycPQPw8xa47DTNaS","last_id":"msg_y9TwDv2x212Ei4IYSDO1NOvl","has_more":false}
    }

    function ai_img_download()
    {
        if (isset($_GET['index']) && !empty($_GET['index'])) {
            $all_images = $this->session->get('ai_image');
            $exi = $_GET['index'] - 1;
            $image_url = $all_images[$exi]['url'];
        } else {
            $this->session->setFlashdata('error_message', get_phrase('image_url_not_found'));
            redirect(site_url(), 'refresh');
        }
        $this->load->helper('download');


        $data = file_get_contents($image_url);
        $name = random(5) . '.png';
        force_download($name, $data);
    }

    function update_open_ai_settings($param1 = "")
    {
        if (get_settings('open_ai')) {
            $this->db->where('key', 'open_ai')->update('settings', ['value' => json_encode($_POST)]);
        } else {
            $this->db->table('settings')->insert(['value' => json_encode($_POST)]);
        }

        $this->session->setFlashdata('flash_message', get_phrase('ai_settings_updated_successfully'));
        redirect(site_url('admin/open_ai_settings'), 'refresh');
    }
}


