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
$ipaddr = "127.0.0.1";
$port = "23";
$username = "guest";

// Client logging to file
$log_dir = dirname(__FILE__)."/logs/";
$log_file = fopen($log_dir."clnt_".date("Y-m-d").".log", "a+");
fputs($log_file, date("Y-m-d H:i:s")." "."____________________RECV_START_________________\r\n");

// Main Thread >>
	//   1. Clear Screen
	clrscr();
	//   2. Display Banner
	banner_display();
	//   3. Message recv thread
	while(true) {
		msg_recv(1);
	}
// <<

// Banner display function
function banner_display() {
	global $ipaddr, $port;
	// Create main sockets
	if(($m_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
		error_info("Cannot create socket because ".socket_strerror(socket_last_error()), 1);
	}
	// Connect to server
	if(socket_connect($m_sock, $ipaddr, $port) === false) {
		error_info("Cannot connect to server because ".socket_strerror(socket_last_error()), 1);
	}
	
	// Send command 
	socket_write($m_sock, "banner\r\n");
	// Display the banner
	while(true) {
		$banner_msg = socket_read($m_sock, 2048, PHP_NORMAL_READ)."";
		if(strpos($banner_msg,"[FCS-BANNER-END]") > 0){
			break;
		} else {
			echo $banner_msg;
		}
	}
	fwrite(STDOUT, "\r\n\r\n");
	
	// Quit
	socket_write($m_sock, "quit\r\n");
	sleep(1);
	// Disconnect from server
	socket_close($m_sock);
}

// Message recv function
function msg_recv($sleeptime) {
	global $ipaddr, $port;
	// Create main sockets
	if(($m_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
		error_info("Cannot create socket because ".socket_strerror(socket_last_error()), 1);
	}
	// Connect to server
	if(socket_connect($m_sock, $ipaddr, $port) === false) {
		error_info("Cannot connect to server because ".socket_strerror(socket_last_error()), 1);
	}
	// read message to server
	echo socket_read($m_sock, 2048, PHP_NORMAL_READ)."\r\n";
	// Quit
	socket_write($m_sock, "quit\r\n");
	sleep(1);
	// Disconnect from server
	socket_close($m_sock);
	sleep($sleeptime);
}

// Clear screen
function clrscr() {
	for($i=0; $i<30; $i++){
		fputs(STDOUT, "\r\n");
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
	echo date("H:i:s")." ".$error_msg; 
	global $log_file;
	fputs($log_file, date("Y-m-d H:i:s")." ".$error_msg);
}

// Close Loggig file
fputs($log_file, date("Y-m-d H:i:s")." "."__________________RECV_SHUTDOWN________________\r\n");
echo date("H:i:s")." [INFOR] "."Bye-bye!\r\n";
fclose($log_file);

?>