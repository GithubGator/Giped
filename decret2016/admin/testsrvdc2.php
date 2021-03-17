<?php
	require_once("../_session.php");
	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // Paramtres de connection et... connection
	require_once("../_fonctions.php"); 

	
	$dest = "\\srvdc2\Demographes\Importation XML\CG66";
	$csvstr="Ceci est un test";
	
	$dateJour=date("Y-m-d");
	//$csvfile="./".$Fichier."-".$dateJour.".csv";
	$csvfile= $dest."\csv.txt";
	echo $csvfile;
	$fp = fopen($csvfile, 'w'); 
	fputs($fp, $csvstr);
	fclose($fp); 
?>
