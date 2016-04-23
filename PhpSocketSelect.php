<?php
// $sfd = stream_socket_server ('tcp://0.0.0.0:11000', $errno, $errstr);
// stream_set_blocking($sfd, 0);
// $base = event_base_new();
// $event = event_new();
// event_set($event, $sfd, EV_READ | EV_PERSIST, 'ev_accept', $base);
// event_base_set($event, $base);
// event_add($event);
// event_base_loop($base);
// function ev_accept($socket, $flag, $base)
// {
// 	$connection = stream_socket_accept($socket);
// 	stream_set_blocking($connection, 0);
// 	$buffer = event_buffer_new($connection, 'ev_read', NULL, 'ev_error', $connection);
// 	event_buffer_base_set($buffer, $base);
// 	event_buffer_timeout_set($buffer, 30, 30);
// 	event_buffer_watermark_set($buffer, EV_READ, 0, 0xffffff);
// 	event_buffer_priority_set($buffer, 10);
// 	event_buffer_enable($buffer, EV_READ | EV_PERSIST);
// }

// function ev_error($buffer, $error, $connection)
// {
// 	event_buffer_disable($buffer, EV_READ | EV_WRITE);
// 	event_buffer_free($buffer);
// 	fclose($connection);
// }

// function ev_read($buffer, $connection)
// {
// 	$read = event_buffer_read($buffer, 256);
// 	//do something....
// 	//
// }

?>