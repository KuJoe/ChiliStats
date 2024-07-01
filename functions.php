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

function checkAdmin($staff_id) {
	$sql = "SELECT COUNT(*) FROM staff WHERE staff_active = ?";
	try {
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':staff_active', $staff_active);
		$staff_active = 1;
		$stmt->execute();
		$count = $stmt->fetchColumn();

		if ($count === 0) {
			return true;
		} else {
			return false;
		}
		$stmt = null;
	} catch(PDOException $e) {
		die("Database error: " . $e->getMessage());
	}
	return false;
}

function checkLockedOut($staff_id) {
	$sql = "SELECT staff_locked FROM staff WHERE staff_id = ?";
	try {
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':staff_id', $staff_id);
		$stmt->execute();
		$locked = $stmt->fetchColumn();
		if ($locked) {
			$locked_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $locked);
			$now = new DateTime();
			if ($locked_datetime > $now) {
			  return true;
			} else {
			  return false;
			}
		} else {
			return false;
		}
		$stmt = null;
	} catch(PDOException $e) {
		die("Database error: " . $e->getMessage());
	}
	return false;
}

?>