<?php
if(isset($_GET['pdf'])){
	header("Content-type: application/pdf");
	header("Content-Disposition: attachment; filename=".$_GET['pdf']);
	readfile("./tmp/".$_GET['pdf']);
}
if(isset($_GET['xml'])){
	header("Content-type: application/xml");
	header("Content-Disposition: attachment; filename=".$_GET['xml']);
	readfile("./tmp/".$_GET['xml']);
}
if(isset($_GET['xls'])){
	header("Content-type: application/xls");
	header("Content-Disposition: attachment; filename=".$_GET['xls']);
	readfile("./tmp/".$_GET['xls']);
}

if(isset($_GET['csv']) && isset($_GET['anneeRef'])){
	header("Content-type: application/csv");
	header("Content-Disposition: attachment; filename=".$_GET['csv']);
	readfile("./".$_GET['anneeRef']."/".$_GET['csv']);
}
?>