<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");

require 'vendor/autoload.php';
defined('BASEPATH') or exit('No direct script access allowed');

use Tripay\Main;

class KeranjangController extends CI_Controller
{
    public $tripay;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('url');

        $this->tripay =  new Main(
            'DEV-Ufpae9mhYWWMorW93KY7QcMHgRajhw1nJktq9Fe6',
            'MGrGi-LVeBW-xLdyK-yKzoF-ZY8HI',
            'T14877',
            'sandbox'
        );
    }

    public function index()
    {
        $id_registrasi = $this->input->get('id_registrasi');

        $this->db->select('keranjang.id as id_keranjang, keranjang.kode_tagihan, keranjang.id_registrasi,tagihan.deskripsi, tagihan.nominal, mahasiswa.nama, mahasiswa.email, mahasiswa.hp');
        $this->db->from('keranjang');
        $this->db->join('tagihan', 'keranjang.kode_tagihan=tagihan.kode');
        $this->db->join('registrasi', 'keranjang.id_registrasi=registrasi.id');
        $this->db->join('mahasiswa', 'registrasi.nim=mahasiswa.nim');
        $this->db->where('keranjang.id_registrasi', $id_registrasi);
        $keranjang = $this->db->get();
        $data = $keranjang->result();

        $total = $this->db->query("SELECT SUM(tagihan.nominal) AS total FROM keranjang INNER JOIN tagihan ON keranjang.kode_tagihan=tagihan.kode WHERE keranjang.id_registrasi='$id_registrasi'")->row();

        $apiKey = 'DEV-Ufpae9mhYWWMorW93KY7QcMHgRajhw1nJktq9Fe6';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => 'https://tripay.co.id/api/merchant/payment-channel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ));

        $response = curl_exec($curl);
        $methode = json_decode($response);

        if ($keranjang->num_rows() > 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'total' => $total,
                'methode' => $methode,
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Data tidak tersedia'
            ]);
        }
    }

    public function store()
    {
        $cek = $this->db->get_where('keranjang', [
            'kode_tagihan' => $this->input->get('kode_tagihan'),
            'id_registrasi' => $this->input->get('id_registrasi'),
        ]);

        if ($cek->num_rows() > 0) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Tagihan sudah ada dikeranjang.'
                ]));
        }

        $data = [
            'kode_tagihan' => $this->input->get('kode_tagihan'),
            'id_registrasi' => $this->input->get('id_registrasi'),
        ];
        $this->db->insert('keranjang', $data);

        if ($this->db->error()) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan'
                ]));
        } else {
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Data gagal disimpan'
                ]));
        }
    }

    public function destroy()
    {
        $this->db->delete('keranjang', array('id' => $this->input->get('id')));
        if ($this->db->error()) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Data berhasil dihapus'
                ]));
        } else {
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Data gagal dihapus'
                ]));
        }
    }
}
