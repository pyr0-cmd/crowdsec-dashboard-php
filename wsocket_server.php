<?php

include "models/db.php";
$host = '192.168.56.129';
$port = 9090;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, $host, $port);
socket_listen($socket);

$clients = [];
echo "WebSocket server started at ws://$host:$port\n";

while (true) {
    $changed_sockets = $clients;
    $changed_sockets[] = $socket;
    
    socket_select($changed_sockets, $null, $null, 0, 10);

    if (in_array($socket, $changed_sockets)) {
        $new_socket = socket_accept($socket);
        $clients[] = $new_socket;
        $handshake = socket_read($new_socket, 1024);
        perform_handshake($handshake, $new_socket, $host, $port);
        unset($changed_sockets[array_search($socket, $changed_sockets)]);
    }

    foreach ($changed_sockets as $client_socket) {
        $message = @socket_read($client_socket, 1024, PHP_BINARY_READ);
        if ($message) {
            $decoded_message = unmask($message);
            foreach ($clients as $client) {
                send_message($client, json_encode(getData()));
            }
        }
    }
    foreach ($clients as $client) {
        send_message($client, json_encode(getData()));
    }
    sleep(5);
}

function perform_handshake($received_header, $client_conn, $host, $port) {
    preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $received_header, $matches);
    $secKey = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $header = "HTTP/1.1 101 Switching Protocols\r\n" .
              "Upgrade: websocket\r\n" .
              "Connection: Upgrade\r\n" .
              "Sec-WebSocket-Accept: $secKey\r\n\r\n";
    socket_write($client_conn, $header, strlen($header));
}

function unmask($text) {
    $length = ord($text[1]) & 127;
    $masks = substr($text, 2, 4);
    $data = substr($text, 6);
    $text = '';
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
}

function send_message($client, $msg) {
    $msg = chr(129) . chr(strlen($msg)) . $msg;
    socket_write($client, $msg, strlen($msg));
}

function getData() {
    // Replace these functions with the ones that query PostgreSQL
    return [
        'decisions' => count_decisions(),
        'alerts' => count_alerts(),
    ];
}
?>
