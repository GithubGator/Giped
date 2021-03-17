<?php


/*************************************************************************
*                             INFORMATIONS MINEURS                                      *
**************************************************************************/

if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();

if($nbTYPEV[1]>0 && $nbTYPEV[2]>0 && $nbTYPEV[3]>0 ){
	$PDF->TitreSectionVert($numParagraphe.utf8_decode('Répartition par type d\'évènement (variable TYPEV)'));
}else{
	$PDF->TitreSectionOrange($numParagraphe.utf8_decode('Répartition par type d\'évènement (variable TYPEV) '));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("Il s'agit de dénombrer le nombre des évenements renseignés par type."));


if($nbTYPEV[1]>0 && $nbTYPEV[2]>0 && $nbTYPEV[3]>0 ){
	$PDF->Ln(6);
	$PDF->SetFont('Arial','',9);
	$PDF->Cell(80,8, utf8_decode("Aucune anomalie constatée."));
}else{
	$PDF->Ln(4);
	$PDF->Cell(80,8, utf8_decode("Il est très surprenant qu'un de ces évènements ne soit jamais renseigné."));
}

$PDF->Ln(6);	
$PDF->SetFont('Arial','B',9);
if($nbTYPEV[1]>0)
	$PDF->Cell(40,8, utf8_decode("Mesure ou prestation : ". $nbTYPEV[1]));
else
	$PDF->Cell(40,8, utf8_decode("Mesure ou prestation : Aucun"));

$PDF->Ln(4);
if($nbTYPEV[2]>0)
	$PDF->Cell(40,8, utf8_decode("Renouvellement de mesure ou de prestation : ". $nbTYPEV[2]));
else
	$PDF->Cell(40,8, utf8_decode("Renouvellement de mesure ou de prestation : Aucun"));

$PDF->Ln(4);
if($nbTYPEV[3]>0)
	$PDF->Cell(40,8, utf8_decode("Fin de mesure ou de prestation : ". $nbTYPEV[3]));
else
	$PDF->Cell(40,8, utf8_decode("Fin de mesure ou de prestation : Aucun"));

//$nbVariablesReserveesMineurs;
$PDF->Encadre($posY_debut,0, $pageNo_debut);
?>