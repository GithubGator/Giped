<?php

/*************************************************************************
*                             INFORMATIONS PB NUMERO ANONYME                                      *
**************************************************************************/
$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();
if($nbNumAnonymIncoherents == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Incohérences pour un même identifiant'));
}else{
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Incohérences pour un même identifiant'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("L'identifiant anonyme (NUMANONYM) est construit en partie avec l'année et le mois de naissance du mineur/jeune majeur."));
$PDF->Ln(5);
$PDF->Cell(80,8, utf8_decode("Un même identifiant ne peux donc pas être présent dans 2 lignes où soit l'année soit le mois de naissance du jeune est différent."));
$PDF->Ln(6);

$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'incohérences :"));
$numLigne=0;
if($nbNumAnonymIncoherents == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	$PDF->Cell(40,8," (".$nbNumAnonymIncoherents." anomalies ANAIS|MNAIS|SEXE)");
}
foreach($tabNumAnonymIncoherents as $cle=>$nbIncoherences){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$PDF->Ln(5);
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $cle);
	$PDF->SetFont('Arial','',9);
	//$PDF->Cell(40,8," (".$nbNumAnonymIncoherents." anomalies)");
	
	// exemples
	$j=0;
	
	foreach($tabNumAnonymIncoherents[$cle] as $exemples){
		$j++;
		if($j >10)
			break;
		$PDF->Ln(4);
		$PDF->Cell(12,8, ""); // tabulation
		$PDF->Cell(80,8, $exemples);
		
	}
				
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>