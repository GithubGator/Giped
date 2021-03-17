<?php


/*************************************************************************
*                             INFORMATIONS MINEURS                                      *
**************************************************************************/

if ($PDF->GetY() > 220)
	$PDF->AddPage();

if(count($nbVariablesReserveesMineurs) == 0 && count($nbModalitesReserveesMineurs) == 0 ){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Variables ou modalitées réservées aux mineurs'));
}else{
	$PDF->TitreSectionOrange($numParagraphe.utf8_decode('Variables ou modalitées réservées aux mineurs'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("Certaines variables concernent exclusivement les mineurs concernés. Ces variables doivent être vides pour les jeunes majeurs."));
$PDF->Ln(4);
$PDF->Cell(80,8, utf8_decode("d'autres par contre ont des modalités de réponse qui leur sont réservées"));
$PDF->Ln(6);

$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les variables réservées aux mineurs constatées :"));
if(count($nbVariablesReserveesMineurs) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	foreach($nbVariablesReserveesMineurs as $cle=>$nbVariables){
		//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
		
		$PDF->Ln(5);
		$PDF->Cell(6,8, ""); // tabulation
		$PDF->SetFont('Arial','B',8);
		$PDF->Cell(20,8, $cle);
		$PDF->SetFont('Arial','',8);
		if($nbVariables >10)
			$PDF->Cell(30,8," ( 10 exemples parmi les ".$nbVariables." anomalies)");
		else
			$PDF->Cell(30,8," ( Les ".$nbVariables." anomalies)");
		if($PDF->GetY() > 262){
				$PDF->Encadre($posY_debut,0, $pageNo_debut);
				$posY_debut = 8;
				$pageNo_debut +=1;
				
		}
		
		// exemples
		$j=0;
		foreach($tabVariablesReserveesMineurs[$cle] as $exemples){
			$j++;
			if($j >10)
				break;
			$PDF->Ln(4);
			$PDF->Cell(12,8, ""); // tabulation
			$PDF->Cell(90,8,  utf8_decode($exemples));
			if($PDF->GetY() > 262){
				$PDF->Encadre($posY_debut,0, $pageNo_debut);
				$posY_debut = 8;
				$pageNo_debut +=1;
				
			}
		}
					
	}
}
// Modalite de reponse
$PDF->Ln(6);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les modalites de réponse réservées aux mineurs :"));
if(count($nbModalitesReserveesMineurs) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	foreach($nbModalitesReserveesMineurs as $cle=>$nbVariables){
		//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
		
		$PDF->Ln(5);
		$PDF->Cell(6,8, ""); // tabulation
		$PDF->SetFont('Arial','B',8);
		$PDF->Cell(20,8, $cle);
		$PDF->SetFont('Arial','',8);
		if($nbVariables >10)
			$PDF->Cell(30,8," ( 10 exemples parmi les ".$nbVariables." anomalies)");
		else
			$PDF->Cell(30,8," ( Les ".$nbVariables." anomalies)");
		if($PDF->GetY() > 262){
				$PDF->Encadre($posY_debut,0, $pageNo_debut);
				$posY_debut = 8;
				$pageNo_debut +=1;
				
		}
		
		// exemples
		$j=0;
		foreach($tabModalitesReserveesMineurs[$cle] as $exemples){
			$j++;
			if($j >10)
				break;
			$PDF->Ln(4);
			$PDF->Cell(12,8, ""); // tabulation
			$PDF->Cell(90,8,  utf8_decode($exemples));
			if($PDF->GetY() > 262){
				$PDF->Encadre($posY_debut,0, $pageNo_debut);
				$posY_debut = 8;
				$pageNo_debut +=1;
				
			}
		}
					
	}
}


//$nbVariablesReserveesMineurs;
$PDF->Encadre($posY_debut,0, $pageNo_debut);
?>