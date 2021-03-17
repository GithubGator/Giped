<?php

/*************************************************************************
*                            Variable DECISION non renseignée                                      *
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

$nbLignes=$DecisionManquantes->num_rows;
if($nbLignes == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Variable DECISION non renseignée'));
}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Variable DECISION  non renseignée'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("Chaque enregistrement du fichier doit précisier la nature de la décision (DECISION)."));
	//$PDF->Cell(80,8, utf8_decode("Chaque enregistrement du fichier doit comporter la date de décision (DATDECPE), et éventuellement la date de début effective (DATDEB)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("Chaque enregistrement du fichier doit précisier la nature de la décision (DECISION)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	if ($nbLignes > 10){
		$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}else{
		$PDF->Cell(40,8, utf8_decode("Les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    Identifiant | Naissance | Décision | Début de mesure."));
}

$j=0;
while ($record = $DecisionManquantes->fetch_assoc()) {
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$j++;
	if($j >10)
		break;
	$PDF->Ln(5);
	$PDF->SetFont('Arial','',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $record['NUMANONYMDEP']." | ".$record['ANAIS']." | ".$record['DATDECPE'] ." | ".$record['DATDEB'] );
	
	if($PDF->GetY() > 264){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
	}
	
			
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>