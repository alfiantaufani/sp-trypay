<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Ker extends CI_Model{
	public function data(){
		$sql = "SELECT a.id AS id_keranjang, b.kode AS id_tagihan, c.id AS kode_registrasi
		FROM keranjang AS a
		LEFT JOIN tagihan AS b ON a.kode_tagihan = b.kode 
		LEFT JOIN registrasi AS c ON a.id_registrasi = c.id
		ORDER BY a.id" ;
		$querySQL = $this->db->query($sql);
		if($querySQL){return $querySQL->result();}
		else{return 0;}
	}
}
?>