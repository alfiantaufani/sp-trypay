<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");

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

    public function store()
    {
        $merchantRef = 'INVOICE-' . (int)preg_replace('/(0)\.(\d+) (\d+)/', '$3$1$2', microtime()); //your merchant reference
        $init = $this->tripay->initTransaction($merchantRef);
        
        $item = array();
        $kode = $this->input->get('kode');
        $deskripsi = $this->input->get('deskripsi');
        $nominal = $this->input->get('nominal');
        foreach ($kode as $key => $value) {
            $item[] = [
                'sku'       => $value,
                'name'      => $deskripsi[$key],
                'price'     => $nominal[$key],
                'quantity'  => 1
            ];
        }
        echo json_encode([
            'item' => $item,
            // 'kode' => $this->input->get('kode'),
            // 'total' => $this->input->get('total_nominal'),
        ]);

        // $init->setAmount($this->input->get('total_nominal')); // for close payment
        // $signature = $init->createSignature();

        // $transaction = $init->closeTransaction();
        // $transaction->setPayload([
        //     'method'            => $this->input->get('method'),
        //     'merchant_ref'      => $merchantRef,
        //     'amount'            => $init->getAmount(),
        //     'customer_name'     => $this->input->get('nama'),
        //     'customer_email'    => $this->input->get('email'),
        //     'customer_phone'    => $this->input->get('hp'),
        //     'order_items'       => [
        //         [
        //             'sku'       => $this->input->get('kode'),
        //             'name'      => $this->input->get('deskripsi'),
        //             'price'     => $init->getAmount(),
        //             'quantity'  => 1
        //         ]
        //     ],
        //     'callback_url'      => 'https://tripay.desakedungotok.com/web/api/pembayaran/callback',
        //     'return_url'        => 'https://tripay.desakedungotok.com/web/api/pembayaran/redirect',
        //     'expired_time'      => (time() + (24 * 60 * 60)), // 24 jam
        //     'signature'         => $init->createSignature()
        // ]);

        // $transaction->getPayload();
        // $result = $transaction->getData();
        // $get_redirect = $transaction->getJson();

        // // echo json_encode($result);
        // $pembayaran = [
        //     'id_registrasi' => $this->input->get('idregistrasi'),
        //     'kode_tagihan' => $this->input->get('kode'),
        //     'referensi' => $result->reference,
        //     'channel_bayar' => $result->payment_method,
        //     'nama_channel' => $result->payment_name,
        //     'amount' => $result->amount,
        //     'amount_receive' => $result->amount_received,
        //     'total_fee' => $result->total_fee,
        //     'pay_code' => $result->pay_code,
        //     'checkout_url' => $result->checkout_url,
        //     'status_bayar' => $result->status,
        //     'tgl_checkout' => date("Y-m-d h:i:sa"),
        //     'expired_time' => $result->expired_time,
        //     'tgl_bayar' => date("Y-m-d h:i:sa"),
        // ];
        // $insert = $this->db->insert('pembayaran', $pembayaran);
        // $id_pembayaran = $this->db->insert_id();

        // $detail_transaksi = [
        //     'id_pembayaran' => $id_pembayaran,
        //     'deskripsi' => $this->input->get('deskripsi'),
        //     'semester' => '6',
        //     'periode' => 'Genap',
        //     'tahun_ajaran' => '2022',
        //     'nominal' => $this->input->get('total_nominal'),
        // ];
        // $this->db->insert('detail_transaksi', $detail_transaksi);

        
        // if ($insert) {
        //     $this->db->delete('keranjang', array('id' => $this->input->get('idkeranjang')));
        //     return $this->output->set_content_type('application/json')
        //         ->set_status_header(200)
        //         ->set_output(json_encode([
        //             'status' => 'success',
        //             'message' => 'Pembayaran berhasil, silahkan klick ok',
        //             'redirect' => $get_redirect->data->checkout_url,
        //         ]));
        // } else {
        //     return $this->output->set_content_type('application/json')
        //         ->set_status_header(500)
        //         ->set_output(json_encode([
        //             'status' => 'error',
        //             'message' => 'Pembayaran gagal'
        //         ]));
        // }
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

        $cek_pembayaran = $this->db->get_where('pembayaran', ['referensi' => $result->reference]);
        if ($cek_pembayaran->num_rows() > 0) {
            if ($result->status == "PAID") {
                $status_bayar = "PAID";
            } else {
                $status_bayar = $result->status;
            }

            $this->db->where('referensi', $result->reference);
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

    public function redirect()
    {
        return true;
    }
}
