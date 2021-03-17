<?php
/*************************************************************************
*                             Classement suivant la presence des variables                               *
**************************************************************************/

$PDF->SetCol(0);
$PDF->TitreSection(utf8_decode('Classement des variables suivant leur présence'));

$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("Une variable est considérée 'présente' si sa valeur est une des modalités de réponses prévues par le décret."));
$PDF->Ln(5);
$PDF->Cell(80,8, utf8_decode("Une variable 'présente' est considérée 'pertinente' si sa valeur est différente de 'Ne sais pas'."));
$PDF->Ln(6);

$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Variables pertinentes | présentes  ------------- ratio présence/nbre de lignes "));


arsort($tabVariablesPresentes);
$tabRatioVariablesPresentesTMP= array();
$valBuf = -1;
foreach($tabVariablesPresentes as $cle=>$val){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	if(!isset($nbVariablesPertinentes[$cle]))
		$nbVariablesPertinentes[$cle] = 0;
	
	// traitement supplémentaire pour ordonner au second niveau
	if( $val <> $valBuf){
		if($valBuf >=0 && isset($tabRatioVariablesPresentesTMP)){
			arsort($tabRatioVariablesPresentesTMP);
			foreach($tabRatioVariablesPresentesTMP as $var=>$ratio){
				$PDF->Ln(4);
				$PDF->Cell(10,8, ""); // tabulation
				$PDF->SetFont('Arial','B',8);
				$PDF->Cell(24,8, $var);
				$PDF->SetFont('Arial','',8);
				$PDF->Cell(20,8," ".$nbVariablesPertinentes[$var]." | ".$valBuf );
				$PDF->Cell(30,8," ----------------------" );
				$PDF->Cell(20,8," ".$ratio."% ");
			
			}
			
			unset($tabRatioVariablesPresentesTMP);
		}
		//$tabRatioVariablesPresentesTMP[$cle]=round(100*($nbVariablesPertinentes[$cle]/$nbLignes),2);
		$tabRatioVariablesPresentesTMP[$cle]=round(100*($val/$nbLignes),2);
		$valBuf=$val;
		
	}else{
		//$tabRatioVariablesPresentesTMP[$cle]=round(100*($nbVariablesPertinentes[$cle]/$nbLignes),2);
		$tabRatioVariablesPresentesTMP[$cle]=round(100*($val/$nbLignes),2);
	}
	// traitement du dernier tableau temporaire
	if(isset($tabRatioVariablesPresentesTMP)){
		arsort($tabRatioVariablesPresentesTMP);
		foreach($tabRatioVariablesPresentesTMP as $var=>$ratio){
				$PDF->Ln(4);
				$PDF->Cell(10,8, ""); // tabulation
				$PDF->SetFont('Arial','B',8);
				$PDF->Cell(24,8, $var);
				$PDF->SetFont('Arial','',8);
				$PDF->Cell(20,8," ".$nbVariablesPertinentes[$var]." | ".$valBuf );
				$PDF->Cell(30,8," ----------------------" );
				$PDF->Cell(20,8," ".$ratio."% ");
			
		}
		unset($tabRatioVariablesPresentesTMP);
	}

				
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>