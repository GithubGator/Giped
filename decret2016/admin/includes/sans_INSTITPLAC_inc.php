<?php

/*************************************************************************
*                Decision judicicire de placement - A qui est confié l'enfant                   *
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

$nbLignes=$sansINSTITPLAC->num_rows;
$nbPertinents17 = $sansINSTITPLAC17_nonNSP->num_rows;
$nbPertinents18 = $sansINSTITPLAC18_nonNSP->num_rows;

if($nbLignes == 0){
	$texte = messageCle('INSTITPLAC');
	if($texte == ""){
		if($nbPertinents17==0){
			$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Placement judiciaire - à qui est confié l'enfant"));
			$texte= "Variable systématiquement codée en NSP pour les décisions judiciaires de placement.\n";
		
		}else if ($nbPertinents18==0){	
			$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Placement judiciaire - à qui est confié l'enfant"));
			$texte .= "Variable systématiquement codée en NSP dans le cas d'un placement direct.";
		
		}else{
			$PDF->TitreSectionVert($numParagraphe.utf8_decode("Placement judiciaire - à qui est confié l'enfant"));
			$texte= "Aucune anomalie constatée";		
		}
		
	}else{
		$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Placement judiciaire - à qui est confié l'enfant"));
	}

}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode("Placement judiciaire - à qui est confié l'enfant"));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Si décision judiciaire de placement, préciser la personne ou l'institution à qui est confié l'enfant (variable INSTITPLAC)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	if($texte != ""){
		$PDF->Write(4,"\n".utf8_decode($texte));
	}
	$PDF->Cell(10,8,'');
	
	//$PDF->Cell(40,8, utf8_decode($texte));
	
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Si décision judiciaire de placement, préciser la personne ou l'institution à qui est confié l'enfant (variable INSTITPLAC)."));
	$PDF->Ln(4);
	$texte = messageEnLien('INSTITPLAC');
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
while ($record = $sansINSTITPLAC->fetch_assoc()) {
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