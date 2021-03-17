<?php
/*************************************************************************
*                             Classement suivant la pertinence des variables                               *
**************************************************************************/

$PDF->SetCol(0);
$PDF->TitreSection('Classement suivant leur pertinence (ne sais pas exclu pour le classement)');

$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();
$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Variables pertinentes | présentes  ------------- ratio pertinentes/nbre de lignes "));


arsort($nbVariablesPertinentes);
foreach($nbVariablesPertinentes as $cle=>$val){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$PDF->Ln(4);
	$PDF->Cell(10,8, ""); // tabulation
	$PDF->SetFont('Arial','B',8);
	$PDF->Cell(24,8, $cle);
	$PDF->SetFont('Arial','',8);
	$PDF->Cell(20,8," ".$val." | ".$tabVariablesPresentes[$cle] );
	$PDF->Cell(30,8," ----------------------" );
	$PDF->Cell(20,8," ".round(100*($nbVariablesPertinentes[$cle]/$nbLignes),2)."% ");
	
				
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

?>