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
        $this->db->from('pembayaran');
        $this->db->where('pembayaran.id_registrasi', $id_registrasi);
        $data = $this->db->get();
        $pembayaran = $data->result();

        foreach ($pembayaran as $value) {
            $detail_pembayaran = $this->db->get_where('detail_transaksi', ['id_pembayaran' => $value->id])->result();

            @$value->detail_pembayaran = $detail_pembayaran;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $pembayaran,
        ]);
    }

    public function detail()
    {
        $id = $this->input->get('id');

        $this->db->select('*');
        $this->db->from('pembayaran');
        $this->db->where('pembayaran.id', $id);
        $data = $this->db->get();
        $pembayaran = $data->result();

        foreach ($pembayaran as $value) {
            $detail_pembayaran = $this->db->get_where('detail_transaksi', ['id_pembayaran' => $value->id])->result();

            @$value->detail_pembayaran = $detail_pembayaran;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $pembayaran,
        ]);
    }
}
