<?php

/******************************************************************************************************
*               Type d'intervention mise en oeuvre au titre de la décision adminitrative  d'aide à domicile                   *
******************************************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

$nbLignes=$sansLIEUACC->num_rows;
if($nbLignes == 0){
	$texte = messageCle('LIEUACC');
	if($texte == ""){
		$PDF->TitreSectionVert($numParagraphe.utf8_decode("Accueil provisoire - principal lieu d'accueil"));
		$texte= "Aucune anomalie constatée";
	}else{
		$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Accueil provisoire - principal lieu d'accueil"));
	}

}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode("Accueil provisoire - principal lieu d'accueil"));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Principal lieu d'accueil du mineur/majeur dans le cadre d'une décision adminitrative d'accueil provisoire (variable LIEUACC)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	
	$PDF->Cell(40,8, utf8_decode($texte));
	
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Principal lieu d'accueil du mineur/majeur dans le cadre d'une décision adminitrative d'accueil provisoire (variable LIEUACC)."));
	$PDF->Ln(4);
	$texte = messageEnLien('LIEUACC');
	if($texte != ""){
		$PDF->Write(4,"\n".utf8_decode($texte));
	}
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	if($nbLignes>10){
		$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}else{
		$PDF->Cell(40,8, utf8_decode("Les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    Identifiant | Naissance | Décision."));
}

$j=0;
while ($record = $sansLIEUACC->fetch_assoc()) {
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