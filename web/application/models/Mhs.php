<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Mhs extends CI_Model{
	public function data(){
		$sql = "SELECT * FROM mahasiswa ORDER BY nim";
		$querySQL = $this->db->query($sql);
		if($querySQL){return $querySQL->result();}
		else{return 0;}
	}
}
?>