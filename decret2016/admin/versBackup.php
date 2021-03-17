<?php
$anneeRef ="2020";

$nomRepertoire = "./backup".$anneeRef;
	if (is_dir($nomRepertoire)) {
                   // echo 'Le r걥rtoire existe dꫠ!';  
        }else { 
		mkdir($nomRepertoire);
		//echo "Crꢴion :".$nomRepertoire;
	}

$commande ="mv /home/imports_dept/cg23-DecretOlinpe_2017_20200622.xml ".$nomRepertoire."/cg23-DecretOlinpe_2017_20200622.xml";

$resultat = exec($commande,$output,$retval);
echo $commande ."<br />"."res :".$resultat." <br /> retval : ".$retval;
echo "<pre>";
print_r ($output);
echo "</pre>";
?>
