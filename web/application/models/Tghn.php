<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Tghn extends CI_Model{
	public function data($a){
		$sql = "SELECT * FROM tagihan WHERE kode='$a' ORDER BY kode ";
		$querySQL = $this->db->query($sql);
		if($querySQL){return $querySQL->result();}
		else{return 0;}
	}
}
?>