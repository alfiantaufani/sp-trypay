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

    public function index()
    {
        return $this->output->set_content_type('application/json')
            ->set_status_header(500)
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'Perintah tidak diketahui'
            ]));
    }

    public function store()
    {
        $apiKey       = 'DEV-Ufpae9mhYWWMorW93KY7QcMHgRajhw1nJktq9Fe6';
        $privateKey   = 'MGrGi-LVeBW-xLdyK-yKzoF-ZY8HI';
        $merchantCode = 'T14877';
        $merchantRef  = 'INVOICE-' . (int)preg_replace('/(0)\.(\d+) (\d+)/', '$3$1$2', microtime());
        $amount       = $this->input->get('total_nominal');

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

        $data = [
            'method'         => $this->input->get('method'),
            'merchant_ref'   => $merchantRef,
            'amount'         => $amount,
            'customer_name'  => $this->input->get('nama'),
            'customer_email' => $this->input->get('email'),
            'customer_phone' => $this->input->get('hp'),
            'order_items'    => $item,
            'return_url'   => '/home',
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature'    => hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey)
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => 'https://tripay.co.id/api-sandbox/transaction/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $result = json_decode($response);

        $pembayaran = [
            'id_registrasi' => $this->input->get('idregistrasi'),
            'referensi' => $result->data->reference,
            'channel_bayar' => $result->data->payment_method,
            'nama_channel' => $result->data->payment_name,
            'amount' => $result->data->amount,
            'amount_receive' => $result->data->amount_received,
            'total_fee' => $result->data->total_fee,
            'pay_code' => $result->data->pay_code,
            'checkout_url' => $result->data->checkout_url,
            'status_bayar' => $result->data->status,
            'tgl_checkout' => date("Y-m-d h:i:sa"),
            'expired_time' => $result->data->expired_time,
            'tgl_bayar' => date("Y-m-d h:i:sa"),
        ];
        $insert = $this->db->insert('pembayaran', $pembayaran);
        $id_pembayaran = $this->db->insert_id();

        foreach ($kode as $key => $value) {
            $detail_transaksi = [
                'id_pembayaran' => $id_pembayaran,
                'kode_tagihan' => $value,
                'deskripsi' => $deskripsi[$key],
                'semester' => '6',
                'periode' => 'Genap',
                'tahun_ajaran' => '2022',
                'nominal' => $nominal[$key],
            ];

            $this->db->insert('detail_transaksi', $detail_transaksi);
            $this->db->delete('keranjang', array('kode_tagihan' => $value));
        }

        if ($insert) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Pembayaran berhasil, silahkan klick ok',
                    'redirect' => $result->data->checkout_url,
                ]));
        } else {
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Pembayaran gagal'
                ]));
        }
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
