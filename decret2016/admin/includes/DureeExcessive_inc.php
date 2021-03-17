<?php

/*************************************************************************
*                            DATEDEB et DATDECPE Manquantes                                            *
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();
	
$nbLignes=$DureeExcessive->num_rows;
if($nbLignes == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Durée excessive d\'une prestation ou d\'une mesure'));
}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Durée excessive d\'une prestation ou d\'une mesure'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','B',9);

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("La durée effective d'une prestation/mesure (définie par les variables DATDEB et DATFIN) ne devrait pas excéder 2 années."));
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
	$PDF->Write(4,"\n".utf8_decode("La durée effective d'une prestation/mesure (définie par les variables DATDEB et DATFIN) ne devrait pas excéder 2 années."));
//	$PDF->Cell(80,8, utf8_decode("La date de fin effective d'une prestation/mesure (variable DATFIN) ne peut être renseignée que pour les évenements de fin (TYPEV=3)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	if($nbLignes>10){
		$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}else{
		$PDF->Cell(40,8, utf8_decode("Les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    identifiant | Durée en jours| decision."));
}

$libMesure="";
$j=0;
while ($record = $DureeExcessive->fetch_assoc()) {
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$j++;
	if($j >10)
		break;
	switch($record['DECISION']){
		case '1':
			$libMesure="Prestation administrative";
			break;
		case '2':
			$libMesure="Mesure judiciaire";
			break;
		default : 	
			$libMesure="Non défini";
			break;
	
	}
	$PDF->Ln(5);
	$PDF->SetFont('Arial','',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $record['NUMANONYMDEP']." | ".$record['nbj']." | ".$libMesure);
	
	if($PDF->GetY() > 264){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
	}
	
			
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>