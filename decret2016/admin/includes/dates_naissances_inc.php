<?php
/*************************************************************************
*                             Dates de naissance à vérifier                                    *
**************************************************************************/
//if($PDF->GetY() > 220) $PDF->AddPage();

if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

if(count($tabErreursDatesNaissances) > 0){
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Dates de naissance à vérifier'));

	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("La date de naissance du mineur est incohérente avec une des dates de naissance des parents ou avec la date de décision."));
	$PDF->Ln(5);
	$PDF->Cell(80,8,  utf8_decode("Chaque ligne est analysée, il peut s'agir plusieurs fois du même mineur."));
		
	$PDF->Ln(6);
	
	
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les dates de naissances :".count($tabErreursDatesNaissances)));
	$PDF->SetFont('Arial','',8);
	foreach($tabErreursDatesNaissances as $ligneErreur){
		$PDF->Ln(4);
		$PDF->Cell(12,8, ""); // tabulation
		$PDF->Cell(80,8, utf8_decode($ligneErreur));
		if($PDF->GetY() > 268){
			$PDF->Encadre($posY_debut,0, $pageNo_debut);
			$posY_debut = 8;
			$pageNo_debut +=1;
			
		}
		
	}
	
	
}else{
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Dates de naissance à vérifier'));

	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les dates de naissances :"));
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
	
}
$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>