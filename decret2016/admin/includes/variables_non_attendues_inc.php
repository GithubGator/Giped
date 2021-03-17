<?php

/*************************************************************************
*                             Variables non attendues                                                                    *
**************************************************************************/

if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

if(count($nbVariablesNonAttendues) > 0){
	$PDF->TitreSectionOrange($numParagraphe.'Variables non-attendues');

	//$posY_debut = debutCadre();
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();

	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Certaines variables n'ont de sens que comme compléments et/ou précisions à apporter en lien avec une autre variable."));
	$PDF->Ln(4);
	$PDF->Cell(80,8, utf8_decode("Elles ne doivent par conséquent être renseignées que dans ces cas précis, sinon elles doivent rester vides."));
	$PDF->Ln(6);


	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les variables non-attendues constatées :"));

	$numLigne=0;

	foreach($nbVariablesNonAttendues as $cle=>$nbVariables){
		//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
		
		$PDF->Ln(5);
		$PDF->Cell(6,8, ""); // tabulation
		$PDF->SetFont('Arial','B',8);
		$PDF->Cell(20,8, $cle);
		$PDF->SetFont('Arial','',8);
		$PDF->Cell(30,8," (10 exemples parmi les ".$nbVariables." anomalies)");
		
		if($PDF->GetY() > 264){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
				
		}
		
		// exemples
		$j=0;
		foreach($tabVariablesNonAttendues[$cle] as $exemples){
			$j++;
			if($j >10)
				break;
			$PDF->Ln(4);
			$PDF->Cell(12,8, ""); // tabulation
			$PDF->Cell(90,8, utf8_decode($exemples));
			if($PDF->GetY() > 264){
				$PDF->Encadre($posY_debut,0, $pageNo_debut);
				$posY_debut = 8;
				$pageNo_debut +=1;
				
			}
			
			
		}
	}				
}else{
	$PDF->TitreSectionVert($numParagraphe.'Variables non-attendues');

	//$posY_debut = debutCadre();
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();


	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
	
}
$PDF->Encadre($posY_debut,0, $pageNo_debut);
?>
	
