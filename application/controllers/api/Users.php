<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Users extends RestController {

    function __construct()
    {
        parent::__construct();
    }

	/*
		Get Data User All & By ID

		All
		- GET http://baseurl/codeigniter3-rest-api/api/users
		By ID
		- GET http://baseurl/codeigniter3-rest-api/api/users/{id}
	*/

    public function index_get($id = 0)
    {
		if($id) {
			$users = $this->db
				->where('id', $id)
				->limit(1)
				->get('users')
				->result();

			if(empty($users)){
				$this->response([
					'status' => FALSE,
					'msg' => 'User not found.',
					'data' => [],
				], self::HTTP_NOT_FOUND);
			} else {
				$this->response([
					'status' => TRUE,
					'msg' => "Get user by User ID {$id}.",
					'data' => $users[0],
				], self::HTTP_OK);
			}
		} else {
			$users = $this->db->get('users')->result();

			if(empty($users)){
				$this->response([
					'status' => FALSE,
					'msg' => 'Users is empty.',
					'data' => [],
				], self::HTTP_NOT_FOUND);
			} else { 
				$this->response([
					'status' => TRUE,
					'msg' => 'List all users.',
					'data' => $users,
				], self::HTTP_OK);
			}
		}
    }

	/*
		Insert Data User

		POST http://baseurl/codeigniter3-rest-api/api/users

		Raw Post Data: 
			{
				"full_name": "Aris Arisandi",
				"email": "aris.arisandi17@gmail.com",
				"password": "123456"
			}
	*/
	public function index_post()
	{
		$full_name = $this->post('full_name');
		$email = $this->post('email');
		$password = password_hash($this->post('password'), PASSWORD_BCRYPT, ["cost" => 12]);

		// Check Email
		if($this->check_email_is_exists($email)) {
			$this->response([
				'status' => true,
				'msg' => 'Email already exists.'
			], self::HTTP_BAD_REQUEST);
		}

		$data_user = [
			'full_name' => $full_name,
			'email' => $email,
			'password' => $password,
			'created_at' => DATE('Y-m-d H:i:s'),
		];

		$save_user = $this->db->insert('users', $data_user);

		if($save_user) {
			$this->response([
				'status' => true,
				'msg' => 'User has been saved.'
			], self::HTTP_CREATED);
		} else {
			$this->response([
				'status' => true,
				'msg' => 'Failed to save user.'
			], self::HTTP_INTERNAL_ERROR);
		}
	}


	/*
		Update Data User

		PUT http://baseurl/codeigniter3-rest-api/api/users/{id}
	*/
	public function index_put($id)
	{
		$full_name = $this->put('full_name');
		$email = $this->put('email');
		$password = password_hash($this->put('password'), PASSWORD_BCRYPT, ["cost" => 12]);

		// Check Email
		if($this->check_email_is_exists($email, $id)) {
			$this->response([
				'status' => true,
				'msg' => 'Email already exists.'
			], self::HTTP_BAD_REQUEST);
		}

		$data_user = [
			'full_name' => $full_name,
			'email' => $email,
			'password' => $password,
			'created_at' => DATE('Y-m-d H:i:s'),
		];

		$update_user = $this->db->update('users', $data_user, ['id' => $id], 1);

		if($update_user) {
			$this->response([
				'status' => true,
				'msg' => 'User has been updated.'
			], self::HTTP_CREATED);
		} else {
			$this->response([
				'status' => true,
				'msg' => 'Failed to update user.'
			], self::HTTP_INTERNAL_ERROR);
		}
	}

	public function index_delete($id)
	{
		$users = $this->db->where('id', $id)->get('users', 1)->result();

		if(!empty($users)){
			$user = $users[0];
			// Delete User By ID and limit 1 (for safety single delete);
			$this->db->delete('users', ['id', $id], 1);
			$this->response([
				'status' => TRUE,
				'msg' => 'User has been deleted.',
				'data' => [],
			], self::HTTP_OK);
		} else {
			$this->response([
				'status' => FALSE,
				'msg' => 'User not found.',
				'data' => [],
			], self::HTTP_NOT_FOUND);
		}
	}

	// Helper 
	public function check_email_is_exists($email, $updateId = 0)
	{
		$users = $this->db->from('users')->where('email', $email);

		if($updateId){
			$users->where('id !=', $updateId);
		}

		return $users->count_all_results();
	}
}
