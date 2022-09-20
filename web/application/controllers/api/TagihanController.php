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

        $this->db->select('tagihan.kode, tagihan.deskripsi, tagihan.semester, tagihan.tahun_ajaran, tagihan.nominal, pembayaran.status_bayar');
        $this->db->from('tagihan');
        $this->db->join('pembayaran', 'tagihan.kode=pembayaran.kode_tagihan', 'right');
        $this->db->join('registrasi', 'pembayaran.id_registrasi=registrasi.id', 'left');
        // $this->db->join('mahasiswa', 'registrasi.nim=mahasiswa.nim', 'right');
        // $this->db->where('registrasi.nim', $nim);
        $this->db->where('tagihan.semester', $semester);
        $this->db->where('tagihan.periode', $periode);
        $this->db->where('tagihan.tahun_ajaran', $tahun_ajaran);
        $user = $this->db->get();

        if ($user->num_rows() > 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => $user->result()
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