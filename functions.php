<?php

if(!defined('ChiliAllowed')) {
	die('Error 001A');
}
require_once('config.php');

function getRealUserIp() {
	switch(true){
		case (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) : return $_SERVER['HTTP_CF_CONNECTING_IP'];
		case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
		case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
		case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
		default : return $_SERVER['REMOTE_ADDR'];
	}
}

function getCSRFToken() {
    $token = mt_rand();
    if(empty($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = array();
    }
    $_SESSION['csrf_tokens'][$token] = true;
    return $token;
}

function validateCSRFToken($token) {
    if(isset($_SESSION['csrf_tokens'][$token])) {
        unset($_SESSION['csrf_tokens'][$token]);
        return true;
    }
    return false;
}

function checkAdmin($staffid) {
	if($stmt = $conn->prepare('SELECT staff_id FROM ".$db_prefix."staff" WHERE user_active = ?')) {
		$user_active = '1';
		$stmt->bind_param('i', $user_active);
		$stmt->execute();
		$stmt->store_result();
		if($stmt->num_rows > 0) {
			return true;
		} else {
			return false;
		}
		$stmt->close();
	}
	$stmt->close();
	return false;
}

function checkLockedOut($staffid) {
	if($stmt = $conn->prepare('SELECT user_locked FROM ".$db_prefix."staff" WHERE staff_id = ?')) {
		$stmt->bind_param('i', $staffid);
		$stmt->execute();
		$stmt->bind_result($locked);
		$stmt->fetch();
		$now = date('Y-m-d H:i:s');
		if($locked > $now) {
			return true;
		}
	}
	$stmt->close();
	return false;
}

?>