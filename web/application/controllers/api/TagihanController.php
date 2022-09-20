<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");

defined('BASEPATH') or exit('No direct script access allowed');

class TagihanController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('url');
    }

    public function index()
    {
        $semester = $this->input->get('semester');
        $periode = $this->input->get('periode');
        $tahun_ajaran = $this->input->get('tahun_ajaran');
        $nim = $this->input->get('nim');

        $this->db->select('*');
        $this->db->from('tagihan');
        $this->db->where('tagihan.semester', $semester);
        $this->db->where('tagihan.periode', $periode);
        $this->db->where('tagihan.tahun_ajaran', $tahun_ajaran);
        $tagihan = $this->db->get();
        $data_tagihan = $tagihan->result();

        foreach ($data_tagihan as $value) {
            $pembayaran = $this->db->select('*')->from('pembayaran')
                ->join('detail_transaksi', 'pembayaran.id=detail_pembayaran.id_pembayaran')
                ->where('detail_transaksi.kode_tagihan', $value->kode);
            
            @$value->pembayaran = $pembayaran->result();
        }

        if ($tagihan->num_rows() > 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => $data_tagihan
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Data tidak tersedia'
            ]);
        }
    }
}
