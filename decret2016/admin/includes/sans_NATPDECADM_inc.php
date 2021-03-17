<?php

/*************************************************************************
*                            NATPDECADM Manquantes                                            *
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

$nbLignes  =$sansNATPDECADM->num_rows;
$nbPertinents = $NATPDECADM_nonNSP->num_rows;
if($nbLignes == 0){
	if($nbPertinents == 0){
		$PDF->TitreSectionOrange($numParagraphe.utf8_decode('Nature de la décision administrative absente'));
	}else{
		$PDF->TitreSectionVert($numParagraphe.utf8_decode('Nature de la décision administrative absente'));
	}
}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Nature de la décision administrative absente'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Si décision administrative de protection de l'enfance, en préciser la nature (variable NATPDECADM)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$texte = messageCle('NATPDECADM');
	if($texte != ""){
		$PDF->Cell(40,8, utf8_decode($texte));
	}else{
		if($nbPertinents == 0){
			$PDF->Cell(40,8, utf8_decode("Bien que présente, cette variable semble toujours renseignée avec une valeur par défaut : NSP . "));
		}else{
			$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
		}
	}
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Si décision administrative de protection de l'enfance, en préciser la nature (variable NATPDECADM)."));
	$PDF->Ln(4);
	$texte = messageEnLien('NATPDECADM');
	if($texte != ""){
		$PDF->Write(4,"\n".utf8_decode($texte));
	}
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	if($nbLignes >10)
		$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	else
		$PDF->Cell(40,8, utf8_decode("Les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    Identifiant | Naissance | Décision."));
}

$j=0;
while ($record = $sansNATPDECADM->fetch_assoc()) {
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