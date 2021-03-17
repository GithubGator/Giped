<?php

/******************************************************************************************************
*      Caractère modulable de l'accueil dans le cadre d'une décision administrtaive d'accueil provisoire                *
******************************************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

$nbLignes=$sansACCMOD->num_rows;
if($nbLignes == 0){
	$texte = messageCle('ACCMOD');
	if($texte == ""){
		$PDF->TitreSectionVert($numParagraphe.utf8_decode("Accueil provisoire - caractère modulable ou non"));
		$texte= "Aucune anomalie constatée";
	}else{
		$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Accueil provisoire - caractère modulable ou non"));
	}

}else{
	$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Accueil provisoire - caractère modulable ou non"));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Caractère modulable de l'accueil du mineur/majeur dans le cadre d'une décision adminitrative d'accueil provisoire (variable ACCMOD)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	
	$PDF->Cell(40,8, utf8_decode($texte));
	
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Caractère modulable de l'accueil du mineur/majeur dans le cadre d'une décision adminitrative d'accueil provisoire (variable ACCMOD)."));
	$PDF->Ln(4);
	$texte = messageEnLien('ACCMOD');
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
while ($record = $sansACCMOD->fetch_assoc()) {
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