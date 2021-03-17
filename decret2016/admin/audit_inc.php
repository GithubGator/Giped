<?php
// Creation du document
include("phpToPDF.php");

//setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');
setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.UTF-8');

$versionAudit = "0.1.2";

$date           = strftime("%A %d %B %Y."); //Affichera par exemple "date du jour en français : samedi 24 juin 2006."
$dateAppel    = "";


// Définition des propriétés du tableau.
$proprietesTableau = array(
	'TB_ALIGN' => 'L',
	'L_MARGIN' => 15,
	'BRD_COLOR' => array(0,92,177),
	'BRD_SIZE' => '0.3',
	);

// Définition des propriétés du header du tableau.	
$proprieteHeader = array(
	'T_COLOR' => array(150,10,10),
	'T_SIZE' => 12,
	'T_FONT' => 'Arial',
	'T_ALIGN' => 'C',
	'V_ALIGN' => 'T',
	'T_TYPE' => 'B',
	'LN_SIZE' => 7,
	'BG_COLOR_COL0' => array(255,255,255),
	'BG_COLOR' => array(170, 240, 230),
	'BRD_COLOR' => array(0,92,177),
	'BRD_SIZE' => 0.2,
	'BRD_TYPE' => '1',
	'BRD_TYPE_NEW_PAGE' => '',
	);

// Contenu du header du tableau.	
//~ $contenuHeader = array(
//~ 50, 50, 50,
//~ " ", "M. DUPOND Pierre", "Mme PAUL Andrée",
//~ );

// Définition des propriétés du reste du contenu du tableau.	
$proprieteContenu = array(
	'T_COLOR' => array(0,0,0),
	'T_SIZE' => 10,
	'T_FONT' => 'Arial',
	'T_ALIGN_COL0' => 'L',
	'T_ALIGN' => 'R',
	'V_ALIGN' => 'M',
	'T_TYPE' => '',
	'LN_SIZE' => 6,
	'BG_COLOR_COL0' => array(245, 245, 150),
	'BG_COLOR' => array(255,255,255),
	'BRD_COLOR' => array(0,0,0),
	'BRD_SIZE' => 0.1,
	'BRD_TYPE' => '1',
	'BRD_TYPE_NEW_PAGE' => '',
	);	


//Tableau des scripts include
$tabScripts=array();



$posY_retour= 0;
$posX_debut = 0;
$posY_debut = 0;
$posX_fin = 0;
$posY_fin = 0;
	
$PDF = new phpToPDF();
$PDF->AddPage();
//$PDF->Filigrane('Confidentiel'); //Dans le header

$PDF->SetNumAppel($id_appel);
$PDF->AliasNbPages(); //Nbre total de pages 
$PDF->SetXY(-60,30);
//Sélection de la police
$PDF->SetFont('Arial','',9);

$PDF->Cell(55,10,'Paris, le '.$date);
$PDF->Ln(10);

//Sélection de la police
$PDF->SetFont('Arial','B',14);

//Décalage de 8 cm à droite
//$PDF->Cell(70);
$PDF->SetX(65);

//$PDF->SetFillColor(255,102,51);
$PDF->SetFillColor(244,159,45);
//Texte centré dans une cellule 20*10 mm encadrée et retour à la ligne
//$PDF->Cell(78,8,utf8_decode('FICHE D\'ENTRETIEN n° ').$id_appel,1,1,'C',1); 
$PDF->Cell(78,8,utf8_decode('RAPPORT D\'AUDIT TECHNIQUE'),1,1,'C',1); 

$PDF->Ln(2);
$PDF->SetFont('Arial','B',12);

$PDF->Cell(0,8,utf8_decode('Fichier OLINPE transmis par le département  CD'.$numDept),0,0,'C');

$h_cadre=37;

if($isFichier){
	    //Affiche le filigrane
	
}

/*
$PDF->SetFont('Arial','B',60);
$PDF->SetTextColor(255,192,203);
$PDF->RotatedText(35,190,"Confidentiel",45);
$PDF->SetTextColor(0,0,0);
*/

$PDF->Ln(14);

//$posY_debut = $PDF->GetY();

$posY_debut = debutCadre();

// Département concerné
$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(35,8,utf8_decode("Département : "));
$PDF->SetFont('Arial','',9);
$PDF->Cell(40,8, $numDept);

//Cadres 
$posY_fin = $PDF->GetY();
$PDF->SetY($posY_debut);
$PDF->Cell(100,$h_cadre,'',1,1,'C',0); 

$PDF->SetY($posY_debut);
$PDF->SetCol(1);
$PDF->Cell(85,$h_cadre,'',1,1,'C',0); 


$PDF->SetY($posY_fin);

//Date de l'audit
$PDF->SetCol(1);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8,'Date et heure de l\'audit ( V : '.$versionAudit.'): ');


$PDF->Ln(5);

//Nom du fichier
$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(35,8,utf8_decode('Nom du fichier : '));
$PDF->SetFont('Arial','',9);
$PDF->Cell(28,8,$monImportation->nom_fichier_ini);


//Date (valeur)
$PDF->SetCol(1);
$PDF->Cell(10,8,'   ');
$PDF->SetFont('Arial','',9);
$PDF->Cell(30,8,strftime("%A %d %B %Y ".utf8_decode("à")." %kH:%M") );

$PDF->Ln(5);

//Date extraction

$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(35,8,'Date de l\'extraction : ');
$PDF->SetFont('Arial','',9);
$PDF->Cell(40,8,date ("Y-m-d H:i:s.", filemtime($xmlfile)));


$PDF->Ln(5);

//Annèé concernée
$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(35,8,utf8_decode("Année concernée : "));
$PDF->SetFont('Arial','',9);
$PDF->Cell(30,8,$anneeRef);


$PDF->Ln(5);

//Nombre de lignes
$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(48,8,utf8_decode('Nombre de lignes traitées : '));
$PDF->SetFont('Arial','',9);
$PDF->Cell(40,8,$nbLignesIni ." lignes");

$PDF->Ln(5);

//Nombre de lignes
$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(48,8,utf8_decode('Nombre de lignes conservées : '));
$PDF->SetFont('Arial','',9);
$PDF->Cell(40,8,$monImportation->num_lignes ." lignes");



$PDF->Ln(2);
$posY_retour = $PDF->GetY();

// Urgent et Contact service d'urgence

$PDF->SetCol(1);
$posY_debut = $PDF->GetY();
$PDF->Ln(-10);

$PDF->SetFont('Arial','B',9);
 // Nombres de variables pr&eacute;sentes dans ce fichier :".$nbVariablesPresentes. "/".count($listeVariables). 
//	$PDF->SetX($PDF->GetX()+5);
$PDF->Cell(72,6,utf8_decode('Nombre de variables saisies :'));
$PDF->Ln(5);
	
$PDF->SetCol(1);
$PDF->Cell(10,8,'   ');
$PDF->SetFont('Arial','B',11);
$PDF->SetTextColor(255,0,0);
$PDF->Cell(72,6,$nbVariablesPresentes.'/'.count($listeVariables));
$PDF->Ln(3);
	
	
$PDF->SetFont('Arial','',9);
$PDF->SetTextColor(0,0,0);
$PDF->Ln(-1);
//$PDF->Encadre($posY_debut);
$PDF->SetY($posY_retour+3);



// jeunes
$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(35,8,'Nombre de jeunes : ');
$PDF->SetFont('Arial','',9);
$PDF->Cell(40,8,$nbMineurs." mineurs / ".$nbJeunesMajeurs." jeunes majeurs (". count($tabJeunes['all']).")" );

$PDF->Ln(5);



$PDF->Ln(3);


/*************************************************************************
*                             Variables manquantes                                                                         *
**************************************************************************/
$PDF->SetCol(0);
if(count($listeVariablesAbsentes > 0)){
	$PDF->TitreSectionRouge(utf8_decode('Variables absentes du fichier'));
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre de variables absentes : ".count($listeVariablesAbsentes)));
	$maListe = "";
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
	
	
	$PDF->Encadre($posY_debut,0, $pageNo_debut);

}


/*************************************************************************
*                             Variables vides ou ne sais pas                                                                        *
**************************************************************************/
$PDF->SetCol(0);
if(count($listeVariablesNonRenseignees > 0)){
	$PDF->TitreSectionVert(utf8_decode('Variables toujours vides ou "Ne sais pas"'));
	$posY_debut = $PDF->GetY();
	$pageNo_debut = $PDF->PageNo();
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(40,8, utf8_decode("Nombre de variables jamais renseignées : ".count($listeVariablesNonRenseignees)));
	$maListe = "";
	// répartition 8 variables par ligne
	$ligne=1;
	$cpt=1;
	$PDF->SetFont('Arial','',9);
	foreach($listeVariablesNonRenseignees as $cle){
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

}


/*************************************************************************
*                             INFORMATIONS PB NUMERO ANONYME                                      *
**************************************************************************/

$PDF->SetCol(0);
if($nbNumAnonymIncoherents == 0){
	$PDF->TitreSectionVert(utf8_decode('Incohérences pour un même identifiant'));
}else{
	$PDF->TitreSectionRouge(utf8_decode('Incohérences pour un même identifiant'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("L'identifiant anonyme est construit en partie avec l'année et le mois de naissance du mineur/jeune majeur."));
$PDF->Ln(5);
$PDF->Cell(80,8, utf8_decode("Un même identifiant ne peux donc pas être présent dans 2 lignes où soit l'année soit le mois de naissance est différent."));
$PDF->Ln(6);

$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'incohérences :"));
$numLigne=0;
if($nbNumAnonymIncoherents == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}else{
	$PDF->Cell(40,8," (".$nbNumAnonymIncoherents." anomalies ANAIS|MNAIS|SEXE)");
}
foreach($tabNumAnonymIncoherents as $cle=>$nbIncoherences){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$PDF->Ln(5);
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $cle);
	$PDF->SetFont('Arial','',9);
	//$PDF->Cell(40,8," (".$nbNumAnonymIncoherents." anomalies)");
	
	// exemples
	$j=0;
	
	foreach($tabNumAnonymIncoherents[$cle] as $exemples){
		$j++;
		if($j >10)
			break;
		$PDF->Ln(4);
		$PDF->Cell(12,8, ""); // tabulation
		$PDF->Cell(80,8, $exemples);
		
	}
				
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);


/*************************************************************************
*                            DOUBLONS                                      *
**************************************************************************/

$PDF->SetCol(0);
if(count($nbCsvStr) == 0){
	$PDF->TitreSectionVert(utf8_decode('Doublons concernant les mesures/prestations - Test'));
else{
	$PDF->TitreSectionOrange(utf8_decode('Doublons concernant les mesures/prestations - Test'));
}
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("On appelle doublon 2 lignes rigoureusement identiques, c.à.d pour lesquelles toutes les variables renseignées ont la même valeur."));
$PDF->Ln(6);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre de doublons :"));
$numLigne=0;
if(count($nbCsvStr) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucun doublons. "));
}else{
	if($nbCsvStr >1)
		$PDF->Cell(40,8, $nbLigneDoublons." lignes et ".count($nbCsvStr).utf8_decode(" identifiants différents concernés"));
}
foreach($nbCsvStr as $cle=>$nbDoublons){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	$PDF->Ln(5);
	$PDF->SetFont('Arial','B',9);
	$PDF->Cell(8,8, ""); // tabulation
	$PDF->Cell(80,8, $cle);
	$PDF->SetFont('Arial','',9);
	if($nbDoublons >1)
		$PDF->Cell(40,8," (".$nbDoublons." doublons)");
	else 
		$PDF->Cell(40,8," (".$nbDoublons." doublon)");
			
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);


/*************************************************************************
*                             INFORMATIONS DATES INCORRECTES                                      *
**************************************************************************/

$PDF->SetCol(0);
$PDF->TitreSection('Format incorrect pour les dates');
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les dates constatées :"));
$numLigne=0;
if(count($nbDatesFormatErrone) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}

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

$PDF->Encadre($posY_debut,0, $pageNo_debut);


/*************************************************************************
*                             Dates de naissance à vérifier                                    *
**************************************************************************/
//if($PDF->GetY() > 220) $PDF->AddPage();

$PDF->TitreSection(utf8_decode('Dates de naissance à vérifier'));

$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();
$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les dates de naissances :"));
if(count($nbErreurDatesNaissances) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}




//$nbVariablesReserveesMajeurs;
$PDF->Encadre($posY_debut,0, $pageNo_debut);




/*************************************************************************
*                             INFORMATIONS MINEURS                                      *
**************************************************************************/

$PDF->TitreSection(utf8_decode('Variables réservées aux mineurs'));
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();
$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les variables réservées aux mineurs constatées :"));
if(count($nbVariablesReserveesMineurs) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}
foreach($nbVariablesReserveesMineurs as $cle=>$nbVariables){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	
	$PDF->Ln(5);
	$PDF->Cell(6,8, ""); // tabulation
	$PDF->SetFont('Arial','B',8);
	$PDF->Cell(20,8, $cle);
	$PDF->SetFont('Arial','',8);
	$PDF->Cell(30,8," (".$nbVariables." anomalies)");
	// exemples
	$j=0;
	foreach($tabVariablesReserveesMineurs[$cle] as $exemples){
		$j++;
		if($j >10)
			break;
		$PDF->Ln(4);
		$PDF->Cell(12,8, ""); // tabulation
		$PDF->Cell(90,8, utf8_decode($exemples));
		
	}
				
}


//$nbVariablesReserveesMineurs;
$PDF->Encadre($posY_debut,0, $pageNo_debut);

/*************************************************************************
*                             INFORMATIONS JEUNES MAJEURS                                    *
**************************************************************************/
//if($PDF->GetY() > 220) $PDF->AddPage();

$PDF->TitreSection(utf8_decode('Variables réservées aux jeunes majeurs'));

$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();
$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les variables réservées aux jeunes majeurs constatées :"));
if(count($nbVariablesReserveesMajeurs) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}
//$nbVariablesReserveesMajeurs;

foreach($nbVariablesReserveesMajeurs as $cle=>$nbVariables){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	
	$PDF->Ln(5);
	$PDF->Cell(6,8, ""); // tabulation
	$PDF->SetFont('Arial','B',8);
	$PDF->Cell(20,8, $cle);
	$PDF->SetFont('Arial','',8);
	$PDF->Cell(30,8," (".$nbVariables." anomalies)");
	// exemples
	$j=0;
	foreach($tabVariablesReserveesMajeurs[$cle] as $exemples){
		$j++;
		if($j >10)
			break;
		$PDF->Ln(4);
		$PDF->Cell(12,8, ""); // tabulation
		$PDF->Cell(90,8, utf8_decode($exemples));
		
	}
				
}

$PDF->Encadre($posY_debut,0, $pageNo_debut);

  
/*************************************************************************
*                             Variables non attendues                                                                    *
**************************************************************************/

$PDF->TitreSection('Variables non-attendues');

//$posY_debut = debutCadre();
$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();

$PDF->SetFont('Arial','I',9);
$PDF->Cell(80,8, utf8_decode("Certaines variables n'ont de sens que pour apporter des précisions sur une autre variable et seulement dans ce cas."));
$PDF->Ln(6);


$PDF->SetFont('Arial','B',9);
$PDF->Cell(40,8, utf8_decode("Nombre d'anomalies sur les variables non-attendues constatées :"));

$numLigne=0;
if(count($nbVariablesNonAttendues) == 0){
	$PDF->SetFont('Arial','',9);
	$PDF->Ln(4);
	$PDF->Cell(10,8,'');
	$PDF->Cell(40,8, utf8_decode("Aucune anomalie constatée. "));
}


foreach($nbVariablesNonAttendues as $cle=>$nbVariables){
	//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
	
	$PDF->Ln(5);
	$PDF->Cell(6,8, ""); // tabulation
	$PDF->SetFont('Arial','B',8);
	$PDF->Cell(20,8, $cle);
	$PDF->SetFont('Arial','',8);
	$PDF->Cell(30,8," (".$nbVariables." anomalies)");
	// exemples
	$j=0;
	foreach($tabVariablesNonAttendues[$cle] as $exemples){
		$j++;
		if($j >10)
			break;
		$PDF->Ln(4);
		$PDF->Cell(12,8, ""); // tabulation
		$PDF->Cell(90,8, utf8_decode($exemples));
		
	}
				
}
$PDF->Encadre($posY_debut,0, $pageNo_debut);

	

//$PDF->Cell(40,8," Position: ".$PDF->GetY());

/*************************************************************************
*                                         Autres anomalies                       *
**************************************************************************/


$cada1 = "Conformément au décret n°2016-1966 du 28 décembre 2016, les informations transmises ";
$cada2 = "à l'ONPE l'année N, concerne les mesures décidées, renouvellées ou terminées l'année N-1.";
$cada3 = "Il convient de vérifier que toutes les informations transmises, en particulier celles dont les dates de décisions semblent en dehors de cette période, vérifient bien ces critères.";
	
$PDF->AddPage();
//$PDF->Filigrane('Confidentiel'); // dans le header


//Remettre les valeurs par défaut pour le cadre
$PDF->SetDrawColor(0, 0, 0);
$PDF->SetLineWidth(0,2);
$PDF->TitreSection(utf8_decode('Autres anomalies constatées'));

$posY_debut = $PDF->GetY();
$pageNo_debut = $PDF->PageNo();
$PDF->SetFont('Arial','',10);


//ecriture ligne par ligne

$texte = "";

$PDF->SetTextColor(0,0,0);
$texte = $cada1.$cada2."\n".$cada3."\n\n". utf8_decode($commentaire);
$PDF->SetFont('Arial','B',10);
$PDF->SetTextColor(255,0,0);

$lignes = explode("\n",$texte);
$nbLigne=0;
foreach($lignes as $values){
                $nbLigne ++;
		$PDF->Write(4,"\n".utf8_decode($values));
		if( $nbLigne == 2){
			$PDF->Write(4,"\n"."   ----------------------------------------------------------------------------------------------------------------------------------------------------------");	
			$PDF->SetFont('Arial','',10);
			$PDF->SetTextColor(0,0,0);
			$PDF->Write(4,"\n".utf8_decode("Entre parenthèse figure le nombre de jeunes concernés."));
		}
                if($PDF->GetY() > 255){
                               $PDF->Encadre($posY_debut);
                               
                               $PDF->AddPage();
                               $posY_debut = $PDF->GetY();
                               if($isFichier){
                                               //Affiche le filigrane
                                               $PDF->Filigrane('Version Initiale');
						// initialise la police
					       $PDF->SetFont('Arial','',10); 
                               }
                }              

}


// fin écriture ligne par ligne
$PDF->Encadre($posY_debut,0, $pageNo_debut);
 

/*************************************************************************
*                                    Avertissement CADA                                           *
**************************************************************************/

//$txt="<a href='http://www.allo119.gouv.fr'><em>Allo119</em></a>";
//$PDF->WriteTag(0,10,$txt,0,"R");	


if($isFichier){
	
	// ne pas permettre la maj du fichier pdf si renvoi à l'ecoutant
	$filename = $nomRepertoire.'/'.$nomFicAudit;
	$PDF->Output($filename,"F");
		//logdb( $filename,"Pdf",$username,$id_appel);
		
	
	//$PDF->Output("./pdf/".trim($monEcoutant->util_nom)."-".$id_appel.".pdf","F");	
}else{
	$PDF->Output();	
}	
	
//exit();


function debutCadre(){
	global $PDF; 
	$posY = $PDF->GetY(); 
	if($posY > 255) {                              
		$PDF->AddPage();
		$posY = 10.00125; //10,00125
	}
	return $posY; //$posY_debut 

}

 


?>