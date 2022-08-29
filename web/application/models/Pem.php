<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pem extends CI_Model{
	public function data($a){
		$sql = "SELECT * FROM pembayaran WHERE id='$a' ORDER BY id";
		$querySQL = $this->db->query($sql);
		if($querySQL){return $querySQL->result();}
		else{return 0;}
	}
}
?>