<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");

defined('BASEPATH') or exit('No direct script access allowed');

class RiwayatController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('url');
    }

    public function index()
    {
        $id_registrasi = $this->input->get('id_registrasi');

        $this->db->select('*');
        $this->db->form('pembayaran');
        $this->db->join('detail_transaksi', 'pembayaran.id=detail_transaksi.id_pembayaran');
        $this->db->where('pembayaran.id_registrasi', $id_registrasi);
        $data = $this->db->get();

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data->result(),
        ]);
    }
}
