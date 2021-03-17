<?php


/*************************************************************************
*                             Variables vides                                                                                    *
**************************************************************************/
$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();
	
if(count($listeVariablesVides) > 0){
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Variables toujours vides'));
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Nous attirons votre attention sur le fait que bien que présentes, ces variables ne sont jamais renseignées "));
	$PDF->Ln(4);
	$PDF->Cell(80,8, utf8_decode("dans ce fichier export concerné."));
	
	$PDF->Ln(6);
	
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre de variables toujours vides ou avec des valeurs nulles : ".count($listeVariablesVides)));
	$maListe = array();
	// répartition 8 variables par ligne
	$ligne=1;
	$cpt=1;
	$PDF->SetFont('Arial','',9);
	foreach($listeVariablesVides as $cle){
		$cpt++;
		$maListe[$ligne] .=$cle.", ";
		if($cpt > 8){
			$ligne++;
				$cpt=1;
		}
	}
	foreach($maListe as $mesVariables){
		
		$PDF->Ln(4);
		$PDF->Cell(10,8,'');
		$PDF->Cell(40,8, utf8_decode($mesVariables));
		//$nbVariablesReserveesMajeurs;
	}
	
	
	$PDF->Encadre($posY_debut,0, $pageNo_debut);

}else{
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Variables toujours vides"'));
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre de variables toujours vide : "));
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->SetFont('Arial','',9);
	$PDF->Cell(40,8, utf8_decode("Aucune variable concernée. "));
	$PDF->Encadre($posY_debut,0, $pageNo_debut);


}

?>