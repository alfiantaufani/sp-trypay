<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");

defined('BASEPATH') or exit('No direct script access allowed');

class LoginController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('url');
    }

    public function index()
    {
        $email = $this->input->post('email');
        $password = md5($this->input->post('password'));

        $this->db->select('*');
        $this->db->from('mahasiswa');
        $this->db->join('registrasi', 'mahasiswa.nim=registrasi.nim');
        $this->db->where('mahasiswa.email', $email);
        $this->db->where('mahasiswa.password', $password);
        $user = $this->db->get();

        if ($user->num_rows() > 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Login berhasil',
                'data' => $user->row()
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Email atau Password salah'
            ]);
        }
    }
}
