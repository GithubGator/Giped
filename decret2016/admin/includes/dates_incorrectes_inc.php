<?php
/*************************************************************************
*                             INFORMATIONS DATES INCORRECTES                                      *
**************************************************************************/

$PDF->SetCol(0);
if ($PDF->GetY() > 180)
	$PDF->AddPage();
	
if(count($nbDatesFormatErrone) > 0){
	$PDF->TitreSectionRouge($numParagraphe.'Format incorrect et valeur aberrante sur certaines dates');
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("Les dates  doivent d'une part respecter le format AAAA-MM-JJ ou AAAA mais leur valeur ne devrait pas être antérieure à 1920. Ce dernier cas correspond manifestement à une aberration liée à la conversion en date d'une valeur nulle.
	\n Ce contrôle est effectué pour les variables ANSMERE, ANSPERE, ANSA1 et ANSA2."));
	$PDF->Ln(6);

	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les dates constatées :"));
	$numLigne=0;

	foreach($nbDatesFormatErrone as $cle=>$nbDates){
		//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
		$PDF->Ln(5);
		$PDF->SetFont('Arial','B',9);
		$PDF->Cell(8,8, ""); // tabulation
		$PDF->Cell(20,8, $cle);
		$PDF->SetFont('Arial','',9);
		$PDF->Cell(40,8," (".$nbDates." anomalies)");
		
		// exemples
		$j=0;
		
		foreach($tabDatesFormatErrone[$cle] as $exemples){
			$j++;
			if($j >10)
				break;
			$PDF->Ln(4);
			$PDF->Cell(12,8, ""); // tabulation
			$PDF->Cell(80,8, $exemples);
			
		}
					
	}
}else{
	$PDF->TitreSectionVert($numParagraphe.'Format incorrect et valeur aberrante sur certaines dates');
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	
	$PDF->SetFont('Arial','I',9);
	$PDF->Write(4,"\n".utf8_decode("Les dates  doivent d'une part respecter le format AAAA-MM-JJ ou AAAA mais leur valeur ne devrait pas être antérieure à 1920. Ce dernier cas correspond manifestement à une aberration liée à la conversion en date d'une valeur nulle.
	\n Ce contrôle est effectué pour les variables ANSMERE, ANSPERE, ANSA1 et ANSA2."));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur le format des dates :"));
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));

}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>