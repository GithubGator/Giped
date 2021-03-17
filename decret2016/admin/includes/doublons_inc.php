<?php

/*************************************************************************
*                            DOUBLONS                                                                                          *
**************************************************************************/

$PDF->SetCol(0);
$nbLignes = count($nbCsvStr) ;
if($nbLignes == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Doublons concernant les mesures/prestations - Test'));
}else{
	$PDF->TitreSectionOrange($numParagraphe.utf8_decode('Doublons concernant les mesures/prestations'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("On appelle doublon 2 lignes rigoureusement identiques, c.à.d pour lesquelles toutes les variables renseignées ont la même valeur."));
$PDF->Ln(6);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre de doublons :"));
$numLigne=0;
if($nbLignes == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucun doublons. "));
}else{
	
	if($nbCsvStr >1)
		$PDF->Cell(40,8, $nbLigneDoublons." lignes et ".count($nbCsvStr).utf8_decode(" identifiants différents concernés"));
		
	$PDF->Ln(4);
	if($nbLignes >10){
		$PDF->Cell(30,8," (10 exemples parmi les ".$nbLigneDoublons." anomalies - voir fichier joint)");
	}else{
		$PDF->Cell(30,8," (Les ".$nbLigneDoublons." anomalies - voir fichier joint)");
	}
	$PDF->SetFont('Arial','',9);

	$j=0;
	foreach($nbCsvStr as $cle=>$nbDoublons){
		//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
		$j++;
		if($j >10)
			break;
		
		$PDF->Ln(5);
		$PDF->SetFont('Arial','',8);
		$PDF->Cell(8,8, ""); // tabulation
		$PDF->Cell(80,8, $cle);
	
		
		$PDF->SetFont('Arial','',8);
		if($nbDoublons >1)
			$PDF->Cell(40,8," (".$nbDoublons." doublons)");
		else 
			$PDF->Cell(40,8," (".$nbDoublons." doublon)");
			
		if($PDF->GetY() > 264){
				$PDF->Encadre($posY_debut,0, $pageNo_debut);
				$posY_debut = 8;
				$pageNo_debut +=1;
				
		}	
			
			
	}
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>