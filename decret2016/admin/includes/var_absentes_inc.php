<?php
/*************************************************************************
*                             Variables manquantes                                                                         *
**************************************************************************/
$PDF->SetCol(0);
if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();
if(isset($listeVariablesAbsentes) && count($listeVariablesAbsentes) > 0){
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Variables absentes du fichier'));
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Toutes les variables du décret devraient être présentes, éventuellement vides pour certaines situations qui ne correspondent pas"));
	$PDF->Ln(5);
	$PDF->Cell(80,8, utf8_decode("aux modalités prévues."));
	$PDF->Ln(5);
	$PDF->Cell(80,8, utf8_decode("Par exemple, les variables spécifiques aux mesures judiciaires s'il s'agit de prestations administratives."));
	
	$PDF->Ln(6);
	
	
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre de variables absentes : ".count($listeVariablesAbsentes)));
	$maListe = array();
	// répartition 8 variables par ligne
	$ligne=1;
	$cpt=1;
	$PDF->SetFont('Arial','',9);
	foreach($listeVariablesAbsentes as $cle){
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
	
	
	

}else{
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Variables absentes du fichier'));
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode("Toutes les variables du décret doivent être présentes, éventuellement vides pour certaines situations qui ne correspondent pas"));
	$PDF->Ln(5);
	$PDF->Cell(80,8, utf8_decode("aux modalités prévues."));
	$PDF->Ln(5);
	$PDF->Cell(80,8, utf8_decode("Par exemple, les variables spécifiques aux mesures judiciaires s'il s'agit de prestations administratives."));
	
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre de variables absentes :"));
	
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));


}
$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>