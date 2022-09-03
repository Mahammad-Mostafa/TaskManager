<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller
	{
	static $token = "panel";

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function index()
		{
		session_start();
		$fnc = $this->uri->segment(1);
		if(isset($_SESSION['userid']) AND !empty($_SESSION['userid']))
			{
			if($fnc == "logout")
				{
				$this->$fnc();
				}
			$this->load->view("page/head");
			$this->load->view("page/body");
			$this->load->view("page/overlay");
			$this->load->view("page/script");
			}
		else
			{
			if($fnc == "login")
				{
				$this->login();
				}
			else
				{
				redirect('login');
				}
			}
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function login()
		{
		$this->load->view("page/head");
		$this->form_validation->set_rules("name" , "name" , "required");
		$this->form_validation->set_rules("password" , "password" , "required");
		if($this->form_validation->run() == FALSE)
            {
			$this->load->view("page/login");
			}
		else
			{
			$name = $this->input->post("name");
			$password = $this->input->post("password");
			$this->db->group_start()->where(["name" => $name , "password" => $password])->group_end();
			$this->db->or_group_start()->where(["name" => $name , "password" => sha1($password . "justin")])->group_end();
			$user = $this->db->get("users")->result_array();
			if(count($user) == 1)
				{
				$_SESSION['userid'] = $user[0]['id'];
				$_SESSION['levelid'] = $user[0]['levelid'];
				redirect('home');
				}
			else
				{
				$data['message'] = "Invalid credentials";
				$this->load->view("page/login" , $data);
				}
			}
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function logout()
		{
		session_destroy();
		redirect('login');
		}

	}