<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Reg extends CI_Model{
	public function data($a){
		$sql = "SELECT * FROM `registrasi` WHERE id='$a' ORDER BY id";
		$querySQL = $this->db->query($sql);
		if($querySQL){return $querySQL->result();}
		else{return 0;}
	}
}
?>