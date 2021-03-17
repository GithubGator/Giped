<?php

/*************************************************************************
*                            DOUBLONS      JUDICIAIRES                                 *
**************************************************************************/



$PDF->SetCol(0);

$nbLignes=$DoublonsAdministratifs->num_rows;
if($nbLignes== 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Doublons concernant les prestations administratives'));
}else{
	$PDF->TitreSectionOrange($numParagraphe.utf8_decode('Doublons concernant les prestations administratives'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("On appelle doublon administratif 2 lignes pour lesquelles les variables TYPEV, NATPDECADM, DATDECPE, LIEUACC"));
$PDF->Ln(4);
$PDF->Cell(80,8, utf8_decode("et TYPINTERDOM sont identiques pour un même identifiant"));
$PDF->Ln(6);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(56,8, utf8_decode("Nombre de doublons judiciaires :"));
$numLigne=0;

if($nbLignes == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucun doublons. "));
}else{
	
	//$PDF->Cell(40,8, count($tabDoublonsJudiciaires)." lignes et ".count($nbCsvStrJudiciaire).utf8_decode(" identifiants différents concernés"));
		
	$PDF->Ln(4);
	if($nbLignes >10){
		$PDF->Cell(30,8," (10 exemples parmi les ".$nbLignes ." anomalies - voir fichier joint)");
	}else{
		$PDF->Cell(30,8," (Les ".$nbLignes ." anomalies - voir fichier joint)");
	}
	//$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("          Identifiant | Type d'évènement | Date de décision | Lieu d'accueil | Nature de la décision| Type d'intervention."));

	$j=0;
	
	while ($record = $DoublonsAdministratifs->fetch_assoc()) {
		
		$j++;
		if($j >10)
			break;
		
		$PDF->Ln(5);
		$PDF->SetFont('Arial','',8);
		$PDF->Cell(8,8, ""); // tabulation
		$PDF->Cell(120,8, $record['NUMANONYMDEP']." | ".$record['TYPEV']." | ".$record['DATDECPE']." | ".$record['LIEUACC']." | ".$record['NATPDECADM']." | ".$record['TYPINTERDOM']." | ");
		
		if($PDF->GetY() > 264){
				$PDF->Encadre($posY_debut,0, $pageNo_debut);
				$posY_debut = 8;
				$pageNo_debut +=1;
				
		}
		
		$PDF->SetFont('Arial','',8);
					
	}
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>