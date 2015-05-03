<?php
    include "Common.php";

    $f = fopen(Flag, "w");
    fwrite($f, "stop");
    fclose($f);
?>