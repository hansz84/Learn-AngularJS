<?php
	require_once 'DB.php';
	$db = new DB();
	$tblName = 'users';
/*	$conn = new mysqli('localhost','root','','test');
	$records = $conn->query("SELECT * FROM users");
	while ($result = $records->fetch_assoc())
	{
		var_dump($result);
	}*/
	
	if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
		$type = $_REQUEST['type'];
		switch ($type) {
			case 'view':
				$records = $db->getRows($tblName);
				if ($records) {
					$data['records'] = $db->getRows($tblName);
					$data['status'] = 'OK';
				} else {
					$data['records'] = array();
					$data['status'] = 'ERR';
				}
				echo json_encode($data);
				break;
			case 'add':
				if (!empty($_POST['data'])) {
					$userData = array(
						'name' => $_POST['data']['name'],
						'email' => $_POST['data']['email'],
						'phone' => $_POST['data']['phone']
					);
					$dataType = array(
						'name' => 's',
						'email' => 's',
						'phone' => 's'
					);
					$insert = $db->insert($tblName, $userData, $dataType);
					if ($insert) {
						$data['data'] = $insert;
						$data['status'] = 'OK';
						$data['msg'] = 'User data has been added successfully.';
					} else {
						$data['status'] = 'ERR';
						$data['msg'] = 'Some problem occured, please try again.';
					}
				} else {
					$data['status'] = 'ERR';
					$data['msg'] = 'Some problem occured, please try again.';
				}
				echo json_encode($data);
				break;
			case 'edit':
				if (!empty($_POST['data'])) {
					$userData = array(
						'name' => $_POST['data']['name'],
						'email' => $_POST['data']['email'],
						'phone' => $_POST['data']['phone']
					);
					$condition = array('id' => $_POST['data']['id']);
					$update = $db->update($tblName, $userData, $condition);
					if ($update) {
						$data['status'] = 'OK';
						$data['msg'] = 'User data has been updated successfully.';
					} else {
						$data['status'] = 'ERR';
						$data['msg'] = 'Some problem occured, please try again.';
					}
				} else {
					$data['status'] = 'ERR';
					$data['msg'] = 'Some problem occured, please try again.';
				}
				echo json_encode($data);
				break;
			case 'delete':
				if (!empty($_POST['id'])) {
					$condition = array('id' => $_POST['id']);
					$delete = $db->delete($tblName, $condition);
					if ($delete) {
						$data['status'] = 'OK';
						$data['msg'] = 'User data has been deleted successfully.';
					} else {
						$data['status'] = 'ERR';
						$data['msg'] = 'Some problem occured, please try again.';
					}
				} else {
					$data['status'] = 'ERR';
					$data['msg'] = 'Some problem occured, please try again.';
				}
				echo json_encode($data);
				break;
			default:
				echo '{"status":"INVALID"}';
		}
	}
?>