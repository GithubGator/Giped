<?php

/******************************************************************************************************
*     Placement judiciaire - caractère lmodulable de l'accueil                                                                                  *
******************************************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

$nbLignes=$sansPLACMOD->num_rows;
if($nbLignes == 0){
	$texte = messageCle('PLACMOD');
	if($texte == ""){
		$PDF->TitreSectionVert($numParagraphe.utf8_decode("Placement judiciaire - caractère modulable de l'accueil"));
		$texte= "Aucune anomalie constatée";
	}else{
		$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Placement judiciaire - caractère modulable de l'accueil"));
	}

}else{
	$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Placement judiciaire - caractère modulable de l'accueil"));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("Caractère modulable de l'accueil du mineur pour les décisions judiciaires de placement ou décisions relatives à l'autorité parentale (variable PLACMOD)."));
	
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	
	$PDF->Cell(40,8, utf8_decode($texte));
	
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("Caractère modulable de l'accueil du mineur pour les décisions judiciaires de placement ou décisions relatives à l'autorité parentale (variable PLACMOD)."));

	$PDF->Ln(4);
	$texte = messageEnLien('PLACMOD');
	if($texte != ""){
		$PDF->Write(4,"\n".utf8_decode($texte));
	}
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	if ($nbLignes > 10){
		$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}else{
		$PDF->Cell(40,8, utf8_decode("Les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    Identifiant | Naissance | Décision."));
}

$j=0;
while ($record = $sansPLACMOD->fetch_assoc()) {
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