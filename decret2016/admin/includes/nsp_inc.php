<?php


/*************************************************************************
*                             Variables toujours codées en  "Ne sais pas"                                                                        *
**************************************************************************/
$PDF->SetCol(0);

if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();
	
if(count($listeVariablesToujoursNSP > 0)){
	$PDF->TitreSectionRouge($numParagraphe.utf8_decode('Variables toujours codées par "Ne sait pas"'));
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	
	$PDF->SetFont('Arial','I',9);
	$PDF->Cell(80,8, utf8_decode('Il est peu probable que les variables suivantes soient toujours codées en "NSP",  en partie parce que pour certaines situations'));
	$PDF->Ln(5);
	$PDF->Cell(80,8, utf8_decode('aucune valeur n\'est attendue. Pour plus de détail voir la section "Variables non-attendues".'));
	$PDF->Ln(6);
	
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode('Nombre de variables toujours codifiées en "Ne sait pas" : '.count($listeVariablesToujoursNSP)));
	$maListe = array();
	// répartition 8 variables par ligne
	$ligne=1;
	$maListe[1] ="";
	
	$cpt=1;
	$PDF->SetFont('Arial','',9);
	foreach($listeVariablesToujoursNSP as $cle){
		$cpt++;
		$maListe[$ligne] .=$cle.", ";
		if($cpt > 8){
			$ligne++;
			$maListe[$ligne] =""; 
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
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Variables toujours codifiées en "Ne sait pas"'));
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Aucune variable n'est concernée : ".count($listeVariablesToujoursNSP)));
	$PDF->SetFont('Arial','',9);
	
	$PDF->Encadre($posY_debut,0, $pageNo_debut);

}

?>