<?php
require 'vendor/autoload.php';
defined('BASEPATH') or exit('No direct script access allowed');

use Tripay\Main;

class PembayaranController extends CI_Controller
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
        $data = [
            'id_registrasi' => $this->input->get('id_registrasi'),
            'referensi' => $this->input->get('referensi'),
        ];
    }

    public function callback()
    {
        if ($this->input->get_request_header('X-Callback-Event') != "payment_status") {
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Akses dilarang'
                ]));
        }
        $init = $this->tripay->initCallback();
        $result = $init->getJson();

        $cek_pembayaran = $this->db->get_where('pembayaran', ['pay_code', $result->merchant_ref]);
        if ($cek_pembayaran->num_rows() > 0) {
            if ($result->status == "PAID") {
                $status_bayar = "PAID";
            } else {
                $status_bayar = $result->status;
            }

            $this->db->where('pay_code', $result->merchant_ref);
            $this->db->update('pembayaran', ['status_bayar' => $status_bayar]);
            if ($this->db->error()) {
                return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Pembayaran gagal'
                ]));
            }else{
                return $this->output->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Pembayaran berhasil'
                ]));
            }
        }else{
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Pembayaran tidak tersedia'
                ]));
        }
    }

    public function callback_test()
    {
        $init = $this->tripay->initCallback();
        $result = $init->getJson();
        echo json_encode($result);
    }
}
