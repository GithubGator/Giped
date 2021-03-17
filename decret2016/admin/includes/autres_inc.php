<?php

/*************************************************************************
*                                         Autres anomalies                       *
**************************************************************************/

if ($PDF->GetY() > $limiteInf)
	$PDF->AddPage();
//$PDF->Filigrane('Confidentiel'); // dans le header


//Remettre les valeurs par défaut pour le cadre
$PDF->SetDrawColor(0, 0, 0);
$PDF->SetLineWidth(0,2);
$PDF->TitreSectionOrange($numParagraphe.utf8_decode('Autres anomalies constatées'));

$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();
$PDF->SetFont('Arial','',9);


$texte = "Les dates de décisions sont quelquefois très éloignées de la date de début effective ou de la date de fin effective de la prestation/mesure.";
$PDF->Write(4,"\n".utf8_decode($texte));
$PDF->SetTextColor(0,0,0);

$PDF->SetFont('Arial','I',9);
//$PDF->SetTextColor(255,0,0);
$PDF->Write(4,"\n"."   ----------------------------------------------------------------------------------------------------------------------------------------------------------");	
$PDF->SetFont('Arial','',9);
$PDF->SetTextColor(0,0,0);
$PDF->Write(4,"\n".utf8_decode("Entre parenthèse figure le nombre de jeunes concernés."));
$PDF->Ln(6);
$PDF->Write(4,"\n".utf8_decode($commentaire));

// fin écriture ligne par ligne
$PDF->Encadre($posY_debut,0, $pageNo_debut);
 


?>
