<?php
// Disable all error messages
error_reporting(0);
//error_reporting(E_ALL);

// Allow this program run unlimit
set_time_limit(0);

// Implicit flush.
ob_implicit_flush();

// Set timezone to China +8
date_default_timezone_set("PRC");

// Server address and port. "0.0.0.0" for allow all client
$ipaddr = "0.0.0.0";
$port = "23";
$max_conn = 5;

// Server logging to file
$log_dir = dirname(__FILE__)."/logs/";
$log_file = fopen($log_dir."serv_".date("Y-m-d").".log", "a+");

// System Start info
fputs($log_file, date("Y-m-d H:i:s")." "."___________________SERVER_START__________________\r\n");
echo date("Y-m-d H:i:s")." [INFOR] "."___________________SERVER_START__________________\r\n";
echo date("Y-m-d H:i:s")." [INFOR] "."Server: Char server version 0.1\r\n";
echo date("Y-m-d H:i:s")." [INFOR] "."Design: dfc643 @ FC-System - www.fcsys.us\r\n";
echo date("Y-m-d H:i:s")." [INFOR] "."-- MARK --\r\n";
echo date("Y-m-d H:i:s")." [INFOR] "."Server IP: $ipaddr\r\n";
echo date("Y-m-d H:i:s")." [INFOR] "."Server Port: $port\r\n";
echo date("Y-m-d H:i:s")." [INFOR] "."Max allowed connection: $max_conn\r\n";
echo date("Y-m-d H:i:s")." [INFOR] "."-- MARK --\r\n";

// Read configs from file
$conf_dir = dirname(__FILE__)."/conf/";
//  1.banner
$banner_file = fopen($conf_dir."banner.conf", "r");

// Create main sockets
if(($m_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
	error_info("Cannot create socket because ".socket_strerror(socket_last_error()), 1);
}

// Binding sockets to address and port
if(socket_bind($m_sock, $ipaddr, $port) === false) {
	error_info("Cannot binding address or port because ".socket_strerror(socket_last_error()), 1);
}

// Listening on the port
if(socket_listen($m_sock, $max_conn) === false) {
	error_info("Cannot listen on the port because ".socket_strerror(socket_last_error()), 1);
}

//Create Main thread
main_thread();

// Main thread Function
function main_thread() {
	global $m_sock, $banner_file;
	
	// Main thread
	while(true) {
		// Create message sockets
		if(($s_sock = socket_accept($m_sock)) === false) {
			error_info("Cannot create message socket because ".socket_strerror(socket_last_error()), 1);
		}

		// Reading message thread
		while(true) {
			// Read message from remote
			if(($msg_read = socket_read($s_sock, 2048, PHP_NORMAL_READ)) === false) {
				error_info("Cannot read message because ".socket_strerror(socket_last_error()), 1);
				break;
			}
			
			// Proccess some command
			if (!$msg_read = trim($msg_read)) {
				continue;
			}
			if($msg_read == "banner") {
				// Sending banner to user
				while(!feof($banner_file)) {
					$msg = fgets($banner_file);
					socket_write($s_sock, $msg);
				}
				rewind($banner_file);
				// Banner end flag
				socket_write($s_sock, ">[FCS-BANNER-END]\r\n");
			}
			if($msg_read == "quit") {
				break;
			}
			if($msg_read == "shutdown") {
				break 2;
			}
			
			// Output user message on terminal and client
			echo date("Y-m-d H:i:s")." [USERS] ".$msg_read."\r\n";
			socket_write($s_sock, "\r".date("H:i:s")." ".$msg_read."\r\n");
		}
		
		// Close message sockets
		socket_close($s_sock);
	}
}

// Error message display function
//   1. Error
//   2. Alert
//   3. Info
//   4. Debug
function error_info($msg, $level) {
	switch($level) {
		case 1: $error_msg = "[ERROR] ".$msg; break;
		case 2: $error_msg = "[ALERT] ".$msg; break;
		case 3: $error_msg = "[INFOR] ".$msg; break;
		case 4: $error_msg = "[DEBUG] ".$msg; break;
		default: $error_msg = "[INFOR] Unknown error message:".$msg;
	}
	
	// Display message on screen and output to log file
	echo date("Y-m-d H:i:s")." ".$error_msg; 
	global $log_file;
	fputs($log_file, date("Y-m-d H:i:s")." ".$error_msg);
}

// Close main sockets
socket_close($m_sock);

// Close Files
//  1. Banner
fclose($banner_file);
//  2. Loggig file
fputs($log_file, date("Y-m-d H:i:s")." "."__________________SERVER_SHUTDOWN________________\r\n");
echo date("Y-m-d H:i:s")." [INFOR] "."__________________SERVER_SHUTDOWN________________\r\n";
fclose($log_file);

?>