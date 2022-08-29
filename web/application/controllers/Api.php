<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
    
	public function __construct()
    {
        parent::__construct(); 
        $this->load->library('form_validation');
        $this->load->helper('url');
       
    }

    public function mhs()
    {
        $dtx=$this->Mhs->data();
        echo json_encode($dtx);
       
    }

	public function gettagihan()
	{
		$dtx=$this->Tghn->data('$a');
        echo json_encode($dtx);
	}

    public function getpem()
	{
		$dtx=$this->Pem->data('$a');
        echo json_encode($dtx);
	}

    public function getreg()
	{
		$dtx=$this->Reg->data('$a');
        echo json_encode($dtx);
	}

    public function getker()
	{
		$dtx=$this->Ker->data();
        echo json_encode($dtx);
	}

}
