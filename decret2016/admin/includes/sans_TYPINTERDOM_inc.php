<?php

/******************************************************************************************************
*               Type d'intervention mise en oeuvre au titre de la décision adminitrative  d'aide à domicile                   *
******************************************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

$nbLignes=$sansTYPINTERDOM->num_rows;
if($nbLignes == 0){
	$texte = messageCle('TYPINTERDOM');
	if($texte == ""){
		$PDF->TitreSectionVert($numParagraphe.utf8_decode("Aide à domicile - type d'intervention"));
		$texte= "Aucune anomalie constatée";
	}else{
		$PDF->TitreSectionOrange($numParagraphe.utf8_decode("Aide à domicile - type d'intervention"));
	}

}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode("Aide à domicile - type d'intervention"));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

if ($nbLignes == 0){
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Type d'intervention mis en oeuvre à préciser pour chaque décision adminitrative d'aide à domicile (variable TYPINTERDOM)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies constatées :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	
	$PDF->Cell(40,8, utf8_decode($texte));
	
}else{
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Type d'intervention mis en oeuvre à préciser pour chaque décision adminitrative d'aide à domicile (variable TYPINTERDOM)."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Ln(4);
	if ($nbLignes > 10){
		$PDF->Cell(40,8, utf8_decode("10 exemples parmi les ".$nbLignes." anomalies constatées."));
	}else{
		$PDF->Cell(40,8, utf8_decode("Les ".$nbLignes." anomalies constatées (voir fichier joint)."));
	}
	$PDF->Ln(4);
	$PDF->Cell(40,8, utf8_decode("                    Identifiant | Naissance | Décision."));
}

$j=0;
while ($record = $sansTYPINTERDOM->fetch_assoc()) {
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