<?php

error_reporting(0);

require 'lib/phpMQTT.php';
require 'config.php';

if(!isset($_GET['action']) || empty($_GET['action'])) {
	exit('Error: no action.');
}

if(!isset($_GET['topic']) || empty($_GET['topic'])) {
	exit('Error: no topic.');
}

$action = $_GET['action'];
$topic = $_GET['topic'];

// callback function called when a message is received
function procmsg($topic, $msg){
	echo 'data: ' . json_encode(['topic' => $topic, 'msg' => $msg]) . "\n\n";
	exit();
}

if($action == 'publish') {
	$username = $password = null;
	$qos = 0;
	$retain = 0;

	if(isset($_GET['username']) && !empty($_GET['username'])) {
		$username = $_GET['username'];
	}

	if(isset($_GET['password']) && !empty($_GET['password'])) {
		$password = $_GET['password'];
	}

	if(isset($_GET['qos']) && !empty($_GET['qos'])) {
		$qos = $_GET['qos'];
	}

	if(isset($_GET['retain']) && !empty($_GET['retain'])) {
		$retain = $_GET['retain'];
	}

	if(!isset($_GET['msg']) || empty($_GET['msg'])) {
		exit('Error: no message.');
	}

	$message = $_GET['msg'];

	$mqtt = new phpMQTT(MQTT_ADDRESS, MQTT_PORT, MQTT_CLIENTID);
	$mqtt->connect(true, null, $username, $password);

	$mqtt->publish($topic, $message, $qos, $retain);

	$mqtt->close();
} elseif ($action == 'subscribe') {
	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');

	$username = $password = null;
	$qos = 0;

	if(isset($_GET['username']) && !empty($_GET['username'])) {
		$username = $_GET['username'];
	}

	if(isset($_GET['password']) && !empty($_GET['password'])) {
		$password = $_GET['password'];
	}

	if(isset($_GET['qos']) && !empty($_GET['qos'])) {
		$qos = $_GET['qos'];
	}

	$mqtt = new phpMQTT(MQTT_ADDRESS, MQTT_PORT, MQTT_CLIENTID);
	$mqtt->connect(true, null, $username, $password);

	$mqtt->subscribe([$topic => ['qos' => $qos, 'function' => 'procmsg']], $qos);

	while($mqtt->proc()) {}

	$mqtt->close();
} else {
	exit('Error: invalid action');
}