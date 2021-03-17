<?php

/*************************************************************************
*                            DATEDEB et DATDECPE Manquantes                                            *
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();
	
$nbLignes=$DateFinMauvaisTypev->num_rows;
if($nbLignes == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Date de fin renseignée pour un évènement qui n\'est pas une fin de Prestation/Mesure'));
}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Date de fin renseignée pour un évènement qui n\'est pas une fin de Prestation/Mesure'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','B',9);

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("La date de fin effective d'une prestation/mesure (variable DATFIN) ne peut être renseignée que pour les évenements de fin (TYPEV=3)."));
	//$PDF->Cell(80,8, utf8_decode("La date de fin effective d'une prestation/mesure (variable DATFIN) ne peut être renseignée que pour les évenements de fin (TYPEV=3)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	$PDF->Ln(4);

	$PDF->SetFont('Arial','',9);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("La date de fin effective d'une prestation/mesure (variable DATFIN) ne peut être renseignée que pour les évenements de fin (TYPEV=3)."));
//	$PDF->Cell(80,8, utf8_decode("La date de fin effective d'une prestation/mesure (variable DATFIN) ne peut être renseignée que pour les évenements de fin (TYPEV=3)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées."));
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    identifiant | Date de fin effective | Evenement."));
}

$libEvenement="";
$j=0;
while ($record = $DateFinMauvaisTypev->fetch_assoc()) {
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$j++;
	if($j >10)
		break;
	switch($record['TYPEV']){
		case '1':
			$libEvenement="Début de prestation/mesure";
			break;
		case '2':
			$libEvenement="Renouvellement de prestation/mesure";
			break;
		default : 	
			$libEvenement="Evènement non défini";
			break;
	
	}
	$PDF->Ln(5);
	$PDF->SetFont('Arial','',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $record['NUMANONYMDEP']." | ".$record['DATEFIN']." | ".$libEvenement);
	
	if($PDF->GetY() > 264){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
	}
	
			
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>