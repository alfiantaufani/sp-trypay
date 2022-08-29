<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$route['default_controller'] = 'Api';
$route['404_override'] = 'Pagesystem';
$route['translate_uri_dashes'] = FALSE;

$route['mahasiswa'] = 'api/mhs';
$route['tagihan'] = 'api/gettagihan';
$route['pembayaran'] = 'api/getpem';
$route['regitrasi'] = 'api/getreg';
$route['keranjang'] = 'api/getker';

