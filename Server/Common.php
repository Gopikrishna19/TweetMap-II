<?php
    define("MaxTime", isset($_GET['exectime']) ? $_GET['exectime'] : 60 * 60); // run for an hour atleast or as required
    define("GraceTime", 30); // 30s for graceful stop
    define("StartTime", time());
    define("TimeLimit", StartTime + MaxTime - GraceTime);

    define("Flag", "_flag");
    define("Log", "_log");

    define("ServerIP", "172.31.3.146");
    // define("ServerIP", "127.0.0.1");

    define("ServerPort", 7765);
    define("FeedPort", ServerPort + 1);

    // codes
    // 1001 : new client // client
    // 1002 : new sqs message // server
    // 1003 : new sns noification // server
    // 1004 : push status to client // client
    // 1007 : sns invoked but something went wrong // client
    // 1008 : tweet skipped due to alchemy error // client
    // 1009 : alchemy error // server
    // 1010 : close // client
?>