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
        $merchantRef = 'INVOICE-' . (int)preg_replace('/(0)\.(\d+) (\d+)/', '$3$1$2', microtime()); //your merchant reference
        $init = $this->tripay->initTransaction($merchantRef);

        $init->setAmount($this->input->get('nominal')); // for close payment
        $signature = $init->createSignature();

        $transaction = $init->closeTransaction(); // define your transaction type, for close transaction use `closeTransaction()`
        $transaction->setPayload([
            'method'            => 'BRIVA', // IMPORTANT, dont fill by `getMethod()`!, for more code method you can check here https://tripay.co.id/developer
            'merchant_ref'      => $merchantRef,
            'amount'            => $init->getAmount(),
            'customer_name'     => $this->input->get('nama'),
            'customer_email'    => $this->input->get('email'),
            'customer_phone'    => $this->input->get('hp'),
            'order_items'       => [
                [
                    'sku'       => $this->input->get('kode'),
                    'name'      => $this->input->get('deskripsi'),
                    'price'     => $init->getAmount(),
                    'quantity'  => 1
                ]
            ],
            'callback_url'      => 'https://tripay.desakedungotok.com/web/api/pembayaran/callback',
            'return_url'        => 'https://tripay.desakedungotok.com/web/api/pembayaran/redirect',
            'expired_time'      => (time() + (24 * 60 * 60)), // 24 jam
            'signature'         => $init->createSignature()
        ]); // set your payload, with more examples https://tripay.co.id/developer

        $transaction->getPayload();

        return $this->output->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($transaction->getData()));

        // $data = [
        //     'id_registrasi' => $this->input->get('id_registrasi'),
        //     'referensi' => $this->input->get('referensi'),
        // ];
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
            } else {
                return $this->output->set_content_type('application/json')
                    ->set_status_header(200)
                    ->set_output(json_encode([
                        'status' => 'success',
                        'message' => 'Pembayaran berhasil'
                    ]));
            }
        } else {
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
