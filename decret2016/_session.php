<?php
//session_save_path("./_sessions");
session_start();

if (isset($_REQUEST["mod"])) {
    $mod = $_REQUEST["mod"];
}
?>