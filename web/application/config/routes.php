<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$route['default_controller'] = 'MahasiswaController';
$route['404_override'] = 'Pagesystem';
$route['translate_uri_dashes'] = FALSE;

$route['api/login'] = 'api/LoginController';

$route['api/profil'] = 'api/ProfilController';

$route['api/mahasiswa'] = 'api/MahasiswaController';
$route['api/tagihan'] = 'api/TagihanController';

$route['api/keranjang'] = 'api/KeranjangController';
$route['api/keranjang/store'] = 'api/KeranjangController/store';
$route['api/keranjang/destroy'] = 'api/KeranjangController/destroy';

$route['api/pembayaran/callback'] = 'api/PembayaranController';

