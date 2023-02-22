<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        }

	public function index()
	{
        $this->form_validation->set_rules('email','Email','trim|required|valid_email');
        $this->form_validation->set_rules('password','Password','trim|required');
        if($this->form_validation->run() == false){
        $data['title'] = 'Laporan Keuangan - 2023 | Login';
        $this->load->view('templates/auth_header', $data);
		$this->load->view('auth/login');
        $this->load->view('templates/auth_footer');
        } else {
            $this->_login();
        }
	}
    
    private function _login(){
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('users',['email' => $email])->row_array();
        if($user){
            if($user['is_active'] == 1){
                if(password_verify($password,$user['password'])){
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    redirect('dashboard');
                }else{
                    $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">Password salah!</div>');
            redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">Email belum diaktivasi!</div>');
            redirect('auth');
            }
        }else{
            $this->session->set_flashdata('message','<div class="alert alert-danger" role="alert">Akun belum terdaftar!</div>');
            redirect('auth');
        }
    }
    public function register()
    {

        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[users.email]', [
            'is_unique' => 'Email sudah terdaftar!'
        ]);
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[6]|matches[password2]', [
            'matches' => 'Password tidak sama!',
            'min_length' => 'Password terlalu pendek.'
            ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');


        if($this->form_validation->run() == false){
        $data['title'] = 'Laporan Keuangan - 2023 | Login';
        $this->load->view('templates/auth_header', $data);
        $this->load->view('/auth/register');
        $this->load->view('templates/auth_footer');  
        } else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name',true)),
                'email' => htmlspecialchars($this->input->post('email',true)),
                'images' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'),PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'date_created' => time()
            ];

            $this->db->insert('users',$data);
            $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">Akun berhasil dibuat! Tolong login</div>');
            redirect('auth');
        } 
    }

    public function logout(){
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">Terima kasih telah berkunjung!</div>');
        redirect('auth');
    }
}
