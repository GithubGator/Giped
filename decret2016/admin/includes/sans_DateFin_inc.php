<?php

/*************************************************************************
*                            DATFIN Manquantes                                            *
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

$nbLignes=$DateFinManquantes->num_rows;
if($nbLignes == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Date de fin effective non renseignée'));
}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Date de fin effective non renseignée'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Chaque évènement de type fin de mesure/prestation doit comporter une date de fin effective (variable DATFIN)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Chaque évènement de type fin de mesure/prestation devrait comporter une date de fin effective (variable DATFIN)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées."));
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    Identifiant | Naissance | Décision."));
}

$j=0;
while ($record = $DateFinManquantes->fetch_assoc()) {
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$j++;
	if($j >10)
		break;
	$PDF->Ln(5);
	$PDF->SetFont('Arial','',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $record['NUMANONYMDEP']." | ".$record['ANAIS']." | ".$record['DATDECPE']);
	
	if($PDF->GetY() > 264){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
	}
	
			
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>