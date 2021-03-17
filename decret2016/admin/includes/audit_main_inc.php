<?php
// Creation du document
include("phpToPDF.php");

//setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');
setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.UTF-8');

$versionAudit = "0.8";
$numParagraphe="";

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

$h_cadre=35;

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
$PDF->Cell(40,8,$nbLignesIni ." lignes (".$monImportation->num_lignes." distinctes) ");

$PDF->Ln(5);

//Nombre de lignes
/*
$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(48,8,utf8_decode('Nombre de lignes distinctes : '));
$PDF->SetFont('Arial','',9);
$PDF->Cell(40,8,$monImportation->num_lignes ." lignes");
*/
// jeunes
$PDF->SetCol(0);
$PDF->SetFont('Arial','B',9);
$PDF->Cell(35,8,'Nombre de jeunes : ');
$PDF->SetFont('Arial','',9);
//$PDF->Cell(40,8,$nbMineurs." mineurs / ".$nbJeunesMajeurs." jeunes majeurs (". count($tabJeunes['all']).")" );
$PDF->Cell(40,8,count($tabJeunes['all']));


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
$PDF->Cell(72,6,count($nbVariablesPresentes).'/'.count($listeVariables));
$PDF->Ln(3);
	
	
$PDF->SetFont('Arial','',9);
$PDF->SetTextColor(0,0,0);
$PDF->Ln(-1);
//$PDF->Encadre($posY_debut);
$PDF->SetY($posY_retour+3);


/*
// jeunes

$PDF->SetFont('Arial','B',9);
$PDF->Cell(35,8,'Nombre de jeunes : ');
$PDF->SetFont('Arial','',9);
$PDF->Cell(40,8,$nbMineurs." mineurs / ".$nbJeunesMajeurs." jeunes majeurs (". count($tabJeunes['all']).")" );
*/
$PDF->SetCol(0);
$PDF->Ln(5);
$PDF->Ln(3);

// Conventions :
$PDF->SetFont('Arial','B',9);
$PDF->Cell(80,8, utf8_decode("Avertissement : "));
$PDF->Ln(4);
$PDF->SetFont('Arial','I',9);

$PDF->Write(4,"\n".utf8_decode("Chaque section traite d'un type d'anomalie particulière plusieurs fois rencontrée dans les fichiers transmis.\n"));
//$PDF->Ln(4);
$PDF->Write(4,"\n".utf8_decode("La couleur rouge correspond à \"A corriger impérativement\", orange correspond à \"Anomalie qui a peu d'impact sur le traitement\" mais qu'il conviendrait de corriger et vert \"Tout va bien\"."));
/*
$PDF->Cell(80,8, utf8_decode("Chaque section traite d'un type d'anomalie particulière plusieurs fois rencontrée dans les fichiers transmis."));
$PDF->Ln(4);
$PDF->Cell(80,8, utf8_decode("La couleur rouge correspond à \"A corriger impérativement\", orange correspond à \"Anomalie qui a peu d'impact sur le traitement\" "));
$PDF->Ln(4);
$PDF->Cell(80,8, utf8_decode(" mais qu'il conviendrait de corriger et vert \"Tout va bien\"."));
*/
$PDF->Ln(4);
$PDF->Write(4,"\n".utf8_decode("Ce document vérifie que les modalités de réponses sont bien présentes quand cela est en lien avec la situation et conformes aux modalités prévues par le décret de 2016."));
//$PDF->Ln(4);
$PDF->Write(4,"\n".utf8_decode("Il ne présume pas de la qualité des réponses, une forte proportion de réponse NSP pour une variable, par exemple, ne fera pas apparaitre d'anomalie particulière."));
$PDF->Ln(4);

// début d'affichage des blocs
ksort($tabScripts);


$numP=1;
foreach($tabScripts as $cle=>$val){
	//echo $val['lib']."<br />";
	if($val['publication'] == true){
		//echo $numP;
		$numParagraphe=" ".$numP++."- ";
		$fichierInclude="./includes/".$val['fic']."_inc.php";
		//echo ":".$fichierInclude."<br />";
		require_once($fichierInclude); 
	}
} 

//exit();
//ecriture ligne par ligne
/*
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
 
*/


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