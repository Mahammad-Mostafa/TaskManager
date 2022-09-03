<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class API extends CI_Controller
	{
	public function index()
		{
		session_start();
		$this->access();
		$method = $this->validate($this->uri->segment(2) , "method");
		$this->$method();
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function respond($status , $body)
		{
		$response["status"] = $status;
		$response["body"] = $body;
		echo json_encode($response);
		exit;
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function access()
		{
		if(!isset($_SESSION['userid']) OR empty($_SESSION['userid']))
			{
			$this->respond(1 , "Invalid user");
			}
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function table()
		{
		$table = $this->validate($this->uri->segment(3) , "table");
		$actions = $filters = $forms = $boxes = $disables = [];
		switch($table)
			{
			case "users":
				$title = "Available users";
				if($_SESSION['levelid'] == 2)
					{
					$heads = $fields = ["name"];
					$events = "(SELECT COUNT(*) FROM events WHERE events.userid = " . $_SESSION['userid'] . " AND events.assignid = reviewers.userid) AS events";
					$values = $this->query("userid AS id , (SELECT name FROM users WHERE users.id = reviewers.userid) AS name , $events" , "reviewers" , "reviewerid = " . $_SESSION['userid'] , "events DESC , name ASC");
					}
				else if($_SESSION['levelid'] == 3)
					{
					$heads = ["name" , "reviewers" , "level"];
					$fields = ["name" , "reviewers" , "levelid"];
					$events = "(SELECT COUNT(*) FROM events WHERE events.userid = " . $_SESSION['userid'] . " AND events.assignid = users.id)";
					$select = "MAX(id) AS id , name , IFNULL(GROUP_CONCAT(reviewer) , 'none') AS reviewers , MAX(levelid) AS levelid , MAX($events) AS events";
					$reviewer = "CASE WHEN users.id = reviewers.reviewerid THEN NULL ELSE (SELECT name FROM users WHERE users.id = reviewers.reviewerid) END AS reviewer";
					$join = "LEFT JOIN reviewers ON users.id = reviewers.userid";
					$table = "(SELECT users.id AS id , users.name AS name , $reviewer , users.levelid FROM users $join) users";
					$values = $this->db->query("SELECT $select FROM $table Where id <> " . $_SESSION['userid'] . " GROUP BY name ORDER BY events DESC")->result_array();
					$actions = ["editing" , "deleting"];
					$filters = array_merge([["id" => "0" , "name" => "all"]] , $this->query("id , name" , "levels" , "" , "id ASC"));
					$forms = ["name" , "email" , "password" , "levelid"];
					$boxes = ["levelid" => $this->select("levelid")];
					}
				else
					{
					$this->respond(2 , "Invalid permission");
					}
				break;
			case "tasks":
				$record = $this->validate(intval($this->uri->segment(4)) , "record");
				$title = $this->query("name" , "users" , "id = $record" , "")[0]['name'];
				$heads = ["name" , "assign" , "due" , "status"];
				$fields = ["name" , "assign" , "due" , "stateid"];
				$values = $this->query("id , name , assign , due , stateid , (SELECT COUNT(*) FROM events WHERE events.userid = " . $_SESSION['userid'] . " AND events.assignid = tasks.userid) AS events" , "tasks" , "userid = $record" , "stateid ASC , due ASC");
				$filters = array_merge([["id" => "0" , "name" => "all"]] , $this->query("id , name" , "states" , "" , "id ASC"));
				$boxes = ["stateid" => $this->select("stateid")];
				if($_SESSION['levelid'] == 1)
					{
					$disables = ["assign" , "due"];
					}
				else
					{
					$actions = ["editing" , "deleting"];
					$forms = ["name" , "assign" , "due"];
					}
			}
		$this->respond(0 , ["title" => $title , "heads" => $heads , "fields" => $fields , "values" => $values , "actions" => $actions , "filters" => $filters , "forms" => $forms , "boxes" => $boxes , "disables" => $disables]);
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function select($select = NULL)
		{
		$return = TRUE;
		if(!isset($select))
			{
			$return = FALSE;
			$select = $this->validate($this->uri->segment(3) , "select");
			}
		switch($select)
			{
			case "stateid":
				$select = array_merge([["id" => "" , "name" => "choose a status"]] , $this->query("id , name" , "states" , "" , ""));
				break;
			case "levelid":
				$select = array_merge([["id" => "" , "name" => "choose a level"]] , $this->query("id , name" , "levels" , "" , ""));
				break;
			}
		if($return)
			{
			return $select;
			}
		else
			{
			$this->respond(0 , ["select" => $select]);
			}
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function form()
		{
		$table = $this->validate($this->uri->segment(3) , "table");
		$action = $this->validate($this->uri->segment(4) , "action");
		$record = $this->validate($this->uri->segment(5) , "record");
		if($_SESSION['levelid'] < 3 AND in_array($table , ["users" , "reviewers"]) AND $action != "account")
			{
			$this->respond(2 , "Invalid permission");
			}
		if($_SESSION['levelid'] == 1 AND !in_array($action , ["stateid" , "comment" , "account"] , TRUE))
			{
			$this->respond(2 , "Invalid permission");			
			}
		if($action == "insert" AND $table == "users")
			{
			$fields = ["name" , "email" , "password" , "levelid"];
			}
		else if($action == "insert" AND $table == "tasks")
			{
			$fields = ["userid" , "stateid" , "name" , "assign" , "due"];
			}
		else if($action == "account")
			{
			$table = "users";
			$record = $_SESSION['userid'];
			$fields = ["name" , "email" , "password"];
			}
		else if($action == "review")
			{
			$table = "reviewers";
			$fields = ["reviewerid[]"];
			}
		else
			{
			$fields = [$action];
			}
		$this->submit($table , $action , $fields , $record);
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function delete()
		{
		$table = $this->validate($this->uri->segment(3) , "table");
		$record = $this->validate(intval($this->uri->segment(4)) , "record");
		if($_SESSION['levelid'] == 1 AND in_array($table , ["users" , "tasks"]))
			{
			$this->respond(2 , "Invalid permission");
			}
		else if($_SESSION['levelid'] == 2 AND in_array($table , ["users"]))
			{
			$this->respond(2 , "Invalid permission");
			}
		switch($table)
			{
			case "tasks":
				$this->db->delete("events" , ["taskid" => $record]);
				$this->db->delete("comments" , ["taskid" => $record]);
				break;
			case "users":
				$this->db->delete("events" , ["userid" => $record]);
				$this->db->delete("tasks" , ["userid" => $record]);
				$this->db->where("userid" , $record)->or_where("reviewerid" , $record);
				$this->db->delete("reviewers");
				break;
			}
		$this->db->delete($table , ["id" => $record]);
		$this->respond(0 , "success");
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function validate($value , $type)
		{
		switch($type)
			{
			case "method":
				if(!isset($value) OR empty($value) OR !method_exists($this , $value))
					{
					$this->respond(3 , "Invalid method");
					}
				break;
			case "table":
				if(!in_array($value , ["users" , "reviewers" , "tasks" , "comments" , "events"] , TRUE))
					{
					$this->respond(4 , "Invalid table");
					}
				break;
			case "select":
				if(!in_array($value , ["stateid" , "levelid"] , TRUE))
					{
					$this->respond(5 , "Invalid select");
					}
				break;
			case "action":
				if(!in_array($value , ["insert" , "name" , "review" , "levelid" , "assign" , "due" , "stateid" , "comment" , "account"] , TRUE))
					{
					$this->respond(6 , "Invalid action");
					}
				break;
			case "record":
				if(empty($value))
					{
					$this->respond(7 , "Invalid record");
					}
				break;
			}
		return $value;
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function query($select , $table , $where , $sort)
		{
		if(!empty($select))
			{
			$this->db->select($select);
			}
		if(!empty($where))
			{
			$this->db->where($where);
			}
		if(!empty($sort))
			{
			$this->db->order_by($sort);
			}
		return $this->db->get($table)->result_Array();
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function submit($table , $action , $fields , $record)
		{
		foreach($fields as $field)
			{
			$this->form_validation->set_rules("record" , "record" , "trim|required");
			if($field != "password" AND $field != "reviewerid[]")
				{
				$this->form_validation->set_rules($field , $field , "trim|required");
				}
			}
		if($this->form_validation->run() == TRUE)
			{
			$record = $this->input->post("record" , TRUE);
			$values = $this->input->post($fields , TRUE);
			if(in_array($action , ["insert" , "review" , "comment"] , TRUE))
				{
				if($table == "reviewers")
					{
					$this->db->delete("reviewers" , ["userid" => $record , "reviewerid <>" => $record]);
					if(is_array($values['reviewerid[]']) AND count($values['reviewerid[]']) > 0)
						{
						foreach($values['reviewerid[]'] as $value)
							{
							$this->db->insert($table , ["userid" => $record , "reviewerid" => $value]);
							}
						}
					}
				else if($table == "comments")
					{
					$this->db->insert($table , ["taskid" => $record , "userid" => $_SESSION['userid'] , "comment" => $values['comment']]);
					$this->events($record);
					}
				else
					{
					$this->db->insert($table , $values);
					if($table == "users")
						{
						$userid = $this->query("id" , "users" , "name = '" . $values['name'] . "' AND email = '" . $values['email'] . "'" , "")[0]['id'];
						$this->db->insert("reviewers" , ["userid" => $userid , "reviewerid" => $userid]);
						}
					else if($table == "tasks")
						{
						$this->events($this->query("id" , "tasks" , $values , "")[0]['id']);
						}
					}
				if(in_array($table , ["tasks" , "users"] , TRUE))
					{
					//$this->email($table , $values);
					}
				}
			else
				{
				if($action == "account")
					{
					if(strlen(trim($values['password'])) > 0)
						{
						$values['password'] = sha1($values['password'] . "justin");
						}
					else
						{
						unset($values['password']);
						}
					}
				$this->db->update($table , $values , ["id" => $record]);
				if($action == "levelid" AND $values['levelid'] == 1)
					{
					if($values['levelid'] == 1)
						{
						$this->db->delete("events" , ["userid" => $record , "assignid <>" => $record]);
						$this->db->delete("reviewers" , ["userid <>" => $record , "reviewerid" => $record]);
						}
					else if($values['levelid'] == 3)
						{
						$this->db->delete("reviewers" , ["userid <>" => $record , "reviewerid" => $record]);
						}
					}
				}
			$this->respond(0 , "success");
			}
		else if(!empty($record))
			{
			if($table == "reviewers")
				{
				$values = $this->query("id , name , IFNULL((SELECT reviewerid FROM reviewers WHERE reviewers.userid = $record AND reviewers.reviewerid = users.id) , 0) AS selected" , "users" , "id <> $record AND levelid = 2" , "name ASC");				
				}
			else if($table == "comments")
				{
				$values = $this->query("(SELECT name FROM users WHERE userid = users.id) AS name , comment , time" , $table , "taskid = $record" , "");
				$this->db->delete("events" , ["userid" => $_SESSION['userid'] , "taskid" => $record]);
				}
			else
				{
				$values = $this->query($fields , $table , "id = $record" , "")[0];
				if($action == "account")
					{
					$values['password'] = "";
					}
				}
			$this->respond(0 , ["values" => $values]);
			}
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function events($taskid)
		{
		$managers = $this->query("id" , "users" , "id <> " . $_SESSION['userid'] . " AND levelid = 3" , "");
		$userid = $this->query("userid" , "tasks" , "id = $taskid" , "")[0]['userid'];
		$reviewers = $this->query("reviewerid AS id" , "reviewers" , "reviewerid <> " . $_SESSION['userid'] . " AND userid = " . $userid , "");
		$users = array_merge($managers , $reviewers);
		if(count($users) > 0)
			{
			foreach($users as $user)
				{
				$this->db->insert("events" , ["taskid" => $taskid , "userid" => $user['id'] , "assignid" => $userid]);
				}
			}
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function email($table , $values)
		{
		$config['charset'] = "utf-8";
		$config['mailtype'] = "html";
		$this->load->library("email" , $config);
		$this->email->from("test@test.com" , "Task Management");
		if($table == "users")
			{
			$this->email->to($values['email']);
			$this->email->subject("Welcome " . $values['name'] . " in our portal");
			$message = "<h2 align='center'>Welcome aboard!</h2><p>You can access your account on our portal from here:<br/><a href='" . site_url() . "'>" . site_url() . "</a></p>";
			$message .= "<p><b>User Name:</b><br/>" . $values['name'] . "</p>";
			$message .= "<p><b>Password:</b><br/>" . $values['password'] . "</p>";
			$message .= "<p><font color='red'>THIS PASSWORD IS TEMPORARILY, PLEASE CHANGE ONCE LOGGED IN</font></p>";
			}
		else if($table == "tasks")
			{
			$this->email->subject("New task for you");
			$this->email->to($this->query("email" , "users" , "id = " . $values['userid'] , "")[0]['email']);
			$message = "<h2 align='center'>Check out your new task!</h2><p>You can access your account on our portal from here:<br/><a href='" . site_url() . "'>" . site_url() . "</a></p>";
			$message .= "<p><b>Task:</b><br/>" . $values['name'] . "</p>";
			$message .= "<p><b>Assign:</b><br/>" . $values['assign'] . "</p>";
			$message .= "<p><b>Due:</b><br/>" . $values['due'] . "</p>";
			}
		$this->email->message($message);
		$this->email->send();
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function notify()
		{
		$events = $this->query("COUNT(*) AS events" , "events" , ["userid" => $_SESSION['userid']] , "")[0]['events'];
		$this->output->set_header("Cache-Control: no-cache");
		$this->output->set_header("Content-Type: text/event-stream");
		$this->output->set_output(": \n\n");
		$this->output->set_output("data: " . json_encode($events) . "\n\n");
		}
	}