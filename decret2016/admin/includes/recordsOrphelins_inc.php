<?php

/*************************************************************************
* Recherche du Record Début (TYPEV=1 ou 2) associé  Record fin (TYPEV=3)  *
* A condition que ce soit la même année.
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > 180)
	$PDF->AddPage();

$nbLignes=count($tabRecordsOrphelins);
if($nbLignes == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Présence d\'une ligne "Début" pour chaque ligne "Fin"'));
}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Présence d\'une ligne "Début" pour chaque ligne "Fin"'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();


if ($nbLignes == 0){

	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Recherche de l'évènements de type début (TYPEV=1 ou 2) qui correspond aux informations renseignées dans l'évènement"));
	$PDF->Ln(4);
	$PDF->Cell(80,8, utf8_decode("de fin de prestation/mesure (TYPEV=3)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Recherche de l'évènements de type début (TYPEV=1 ou 2) qui correspond aux informations renseignées dans l'évènement"));
	$PDF->Ln(4);
	$PDF->Cell(80,8, utf8_decode("de fin de prestation/mesure (TYPEV=3)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	//$PDF->Cell(8,8, ""); // tabulation
	if($nbLignes >20){
		$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées :"));
	}else{
		$PDF->Cell(40,8, utf8_decode( $nbLignes." anomalies constatées :"));
	}
	$PDF->Ln(6);
	$PDF->Cell(40,8, utf8_decode("                    Identifiant anonymisé | Décision | Date de début | Date de fin de mesure."));
}

$j=0;
foreach ($tabRecordsOrphelins AS $record ) {
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$j++;
	if($j >10 && $nbLignes >20)
		break;
	$PDF->Ln(5);
	$PDF->SetFont('Arial','',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $record['NUMANONYMDEP']." | ".$record['DATDECPE']." | ".$record['DATDEB']." | ".$record['DATFIN']);
	
	if($PDF->GetY() > 264){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
	}
	
			
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>