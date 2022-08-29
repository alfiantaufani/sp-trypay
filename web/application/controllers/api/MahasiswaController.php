<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MahasiswaController extends CI_Controller {
    
	public function __construct()
    {
        parent::__construct(); 
        $this->load->library('form_validation');
        $this->load->helper('url');
    }

    public function index()
    {
        $data = $this->db->get('mahasiswa')->result();
        echo json_encode($data);
    }
}