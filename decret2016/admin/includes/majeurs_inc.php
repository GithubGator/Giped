<?php

/*************************************************************************
*                             INFORMATIONS JEUNES MAJEURS                                    *
**************************************************************************/
//if($PDF->GetY() > 220) $PDF->AddPage();
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

if(count($nbVariablesReserveesMajeurs) == 0 && count($nbModalitesReserveesMajeurs) == 0){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Variables ou modalitées réservées aux jeunes majeurs'));
}else{
	$PDF->TitreSectionOrange($numParagraphe.utf8_decode('Variables ou modalitées réservées aux jeunes majeurs'));
}


$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();
$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("Certaines variables concernent exclusivement les jeunes majeurs. Ces variables doivent être vides pour les mineurs concernés."));
$PDF->Ln(4);
$PDF->Cell(80,8, utf8_decode("d'autres par contre ont des modalités de réponse qui leur sont réservées"));
$PDF->Ln(6);


$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les variables réservées aux jeunes majeurs constatées :"));
if(count($nbVariablesReserveesMajeurs) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}
//$nbVariablesReserveesMajeurs;

foreach($nbVariablesReserveesMajeurs as $cle=>$nbVariables){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	
	$PDF->Ln(5);
	$PDF->Cell(6,8, ""); // tabulation
	$PDF->SetFont('Arial','B',8);
	$PDF->Cell(20,8, $cle);
	$PDF->SetFont('Arial','',8);
	if($nbVariables>10)
		$PDF->Cell(30,8," (10 exemples parmi les ".$nbVariables." anomalies)");
	else
		$PDF->Cell(30,8," (Les ".$nbVariables." anomalies)");
	// exemples
	$j=0;
	foreach($tabVariablesReserveesMajeurs[$cle] as $exemples){
		$j++;
		if($j >10)
			break;
		$PDF->Ln(4);
		$PDF->Cell(12,8, ""); // tabulation
		$PDF->Cell(90,8, utf8_decode($exemples));
		if($PDF->GetY() > 268){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
		}
		
	}
				
}

$PDF->Ln(6);


$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les modalitées de réponses réservées aux jeunes majeurs :"));
if(count($nbModalitesReserveesMajeurs) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}
//$nbVariablesReserveesMajeurs;

foreach($nbModalitesReserveesMajeurs as $cle=>$nbVariables){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	
	$PDF->Ln(5);
	$PDF->Cell(6,8, ""); // tabulation
	$PDF->SetFont('Arial','B',8);
	$PDF->Cell(20,8, $cle);
	$PDF->SetFont('Arial','',8);
	if($nbVariables>10)
		$PDF->Cell(30,8," (10 exemples parmi les ".$nbVariables." anomalies)");
	else
		$PDF->Cell(30,8," (Les ".$nbVariables." anomalies)");
	// exemples
	$j=0;
	foreach($tabModalitesReserveesMajeurs[$cle] as $exemples){
		$j++;
		if($j >10)
			break;
		$PDF->Ln(4);
		$PDF->Cell(12,8, ""); // tabulation
		$PDF->Cell(90,8, utf8_decode($exemples));
		if($PDF->GetY() > 268){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
		}
		
	}
				
}


$PDF->Encadre($posY_debut,0, $pageNo_debut);
?>