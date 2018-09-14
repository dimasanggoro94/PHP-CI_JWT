<?php

    defined('BASEPATH') OR exit('No direct script access allowed');

    require APPPATH . '../vendor/autoload.php';
    require APPPATH . '/libraries/REST_Controller.php';
    use \Firebase\JWT\JWT;

    class Rest extends REST_Controller {
        private $secretkey = 'kmzway87aa'; //ubah dengan kode rahasia apapun

        public function __construct(){
            parent::__construct();
            $this->load->library('form_validation');
        }

        // method untuk melihat token pada user
        public function generate_post(){

            $this->load->model('loginmodel');
            $date = new DateTime();
            $username = $this->post('username',TRUE); //ini adalah kolom username pada database yang saya berinama username.
            $pass = $this->post('password',TRUE); //ini adalah kolom password pada database yang saya berinama password.
            $dataadmin = $this->loginmodel->is_valid($username);

           /* echo json_encode(password_hash($dataadmin->password, PASSWORD_DEFAULT));exit;*/

            if ($dataadmin) {

                if (password_verify($this->post('password'),password_hash($dataadmin->password, PASSWORD_DEFAULT))) {
                    $payload['id'] = $dataadmin->id_user;
                    $payload['username'] = $dataadmin->username;
                    $payload['iat'] = $date->getTimestamp(); //waktu di buat
                    $payload['exp'] = $date->getTimestamp() + 3600; //satu jam
                    $output['token'] = JWT::encode($payload,$this->secretkey);
                    return $this->response($output,REST_Controller::HTTP_OK);
                } else {
                    $this->viewtokenfail($username);
                }
            } else {
                $this->viewtokenfail($username);
            }
        }

        // method untuk jika generate token diatas salah
        public function viewtokenfail($username){
            $this->response([
              'status'=>'0',
              'username'=>$username,
              'message'=>'Invalid Username Or Password'
              ],REST_Controller::HTTP_BAD_REQUEST);
        }

        // method untuk mengecek token setiap melakukan post, put, etc
        public function cektoken(){

            $this->load->model('loginmodel');

            $jwt = $this->input->get_request_header('Authorization');

            try {
                $decode = JWT::decode($jwt,$this->secretkey,array('HS256'));

                if ($this->loginmodel->is_valid_num($decode->username)>0) {
                    return true;
                }

            } catch (Exception $e) {
                exit(json_encode(array('status' => '0' ,'message' => 'Invalid Token',)));
            }
        }

    }
?>