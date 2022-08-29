<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$route['default_controller'] = 'MahasiswaController';
$route['404_override'] = 'Pagesystem';
$route['translate_uri_dashes'] = FALSE;

$route['api/mahasiswa'] = 'api/MahasiswaController';
$route['api/tagihan'] = 'api/gettagihan';
$route['api/pembayaran'] = 'api/getpem';
$route['api/regitrasi'] = 'api/getreg';
$route['api/keranjang'] = 'api/getker';

