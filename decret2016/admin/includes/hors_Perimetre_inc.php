<?php

/*************************************************************************
*                            Hors périmètre                                       *
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();


$PDF->SetFont('Arial','I',9);


$nbLignes=$horsPerimetre->num_rows;
if($nbLignes == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Périmètre de l\'extraction'));
}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Périmètre de l\'extraction'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$info1 = "Conformément au décret n°2016-1966 du 28 décembre 2016, les informations transmises ";
$info2 = "à l'ONPE l'année N, concerne les mesures décidées, débutées, renouvelées ou terminées l'année N-1.";
$info3 = "Il convient de vérifier que toutes les informations transmises, en particulier celles dont les dates de décision semblent en dehors de cette période, vérifient bien ces critères.";


if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode($info1.$info2."\n\n".$info3));
	//$PDF->Write(4,"\n".utf8_decode($info2));
	//$PDF->Write(4,"\n".utf8_decode($info3));
	

	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode($info1));
	$PDF->Write(4,"\n".utf8_decode($info2));
	$PDF->Write(4,"\n".utf8_decode($info3));
	
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	
	if ($nbLignes > 10){
		$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées."));
	}else{
		$PDF->Cell(40,8, utf8_decode("Les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}
	
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    Identifiant | Naissance | Décision | Début | Fin."));
}

$j=0;
while ($record = $horsPerimetre->fetch_assoc()) {
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$j++;
	if($j >10)
		break;
	$PDF->Ln(5);
	$PDF->SetFont('Arial','',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $record['NUMANONYMDEP']." | ".$record['ANAIS']." | ".$record['DATDECPE']." | ".$record['DATDEB']." | ".$record['DATFIN']);
	
	if($PDF->GetY() > 264){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
	}
	
			
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>