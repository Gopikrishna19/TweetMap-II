<?php    
    header("Connection: close");
    ob_start();
    $s = ob_get_length();
    header("Content-length: $s");
    ob_end_flush();
    flush();

    sleep(1);

    include "Common.php";

    $addr = gethostbyname(ServerIP);
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if(@socket_connect($socket, $addr, 7765) === FALSE) {
        exec("php ./server.php > _log &");
    }
    @socket_close($socket);
?>