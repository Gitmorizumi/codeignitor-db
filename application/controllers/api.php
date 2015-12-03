<?php
require_once $_ENV['PWD'] . '/lib/vendor/twilio/sdk/Services/Twilio.php';
class api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public $layout = null; # disable the default layout so our output isn't wrapped in <html> tags

    public function view($page = 'bridge')
    {
        if($page == 'info')
            var_dump($_ENV);

        if (! file_exists('/app/codeigniter/application/views/api/' . $page . '.php'))
            show_404();

        $data['title'] = ucfirst($page);

        if($page == 'bridge') {
            foreach($this->input->get('numbers') as $number){
                $this->initiate_call($number);
            }
            $this->load->helper('url');
            header('Location: http://' . $_SERVER['HTTP_HOST']);
            exit;
        }

        $this->load->view('templates/xml/header', $data);
        $this->load->view('api/'.$page);
        $this->load->view('templates/xml/footer', $data);
    }

    private function initiate_call($to_number) {
        extract($this->load_twilio());
        $client = new Services_Twilio($sid, $secret, "2010-04-01");
        $json = json_decode($_ENV['VCAP_APPLICATION']);
        $callback = 'http://'.$json->application_uris[0].'/api/room';

        try{
            $call = $client->account->calls->create($from_num, $to_number, $callback);
        } catch(Exception $e) {
            echo "Error: \n" . $e->getMessage();
        }

    }

    private function load_twilio() {
        extract($this->config->item('twilio'));
        if((empty($sid) || empty($secret)) && $this->session->userdata('twilio'))
            extract($this->session->userdata('twilio'));
        return array('sid' => $sid, 'secret' => $secret, 'from_num' => $from_num);
    }
}
?>