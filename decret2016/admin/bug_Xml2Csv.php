<?php
	//***********************************************************************************
	//   Parcours le fichier XML original, converti les formats des dates, construit le fichier XML corrigé et le CSV correspondant
	//
	//
	//***********************************************************************************

	require_once("../_session.php");
	require_once("../_dbclass.php");
	require_once("../_connect.php"); // Paramètres de connection et... connection
	require_once("../_fonctions.php");
	require_once("../_classes/calc_dates.php");
	require_once("../_classes/phonex.class.php");
	require_once("../_classes/cleID.class.php");
	require_once("../_classes/cleONED.inc.php"); // Cle nécessaire à la seconde anonymisation


	header("Content-type: text/html; charset=utf-8");
	setlocale(LC_ALL, 'fr_FR.utf8');
	//error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set('Europe/Paris');

	error_reporting(E_ALL ^ E_NOTICE);
//	import_request_variables("GP","e_");
	ini_set('display_errors', 1);
	ini_set('max_execution_time','0');
	ini_set('memory_limit','-1');
	$error = array();
	// Objet dom XML pour valider le schéma
	$dom = new DomDocument();
	
	
	if($e_test==1){
		$test=true;  // pour effectuer des tests sans émettre de mail ni déplacer le fichier
	}else{
		//echo "En prod";
		$test = false;
	}
	
	// pour profilage
	$tempo1=0;
	$tempo2=0;
	$tempo3=0;
	$tempo4=0;
	$tempo5=0;
	$tempo6=0;
	$nbInsert =0;
	
	$time_start =0;
	$time_end = 0;
	
	$trace="";
	$cptErreur=0;
	$tabFichiers = array();
	$anneeConcernee= "2011";
	$tabDecision = array();
	$flagDATDC=false;  // pour les dates de décès non conformes
	$flagNUMDEPT = false; // pour le 
	
	$folder = "/home/imports_dept";
	
	// initialisation tableau des scripts
	require_once("./includes/scripts_inc.php");
		
	// fichier csv
	$separateur=";";
	$csvstr="";
	$tabCsvStr= array(); // pour la recherche de doublon
	$nbCsvStr= array();
	
	$wrapperName = "DONNEE";
	
	$bDetail=false;	
	$nbMineurs=0;
	$nbJeunesMajeurs=0;
	$tabAnonym = array();
	$tabJeunes['all'] = array();
	$tabJeunes[$anneeConcernee] = array();
	$listeErreurs="";
	
	$tabVariablesPresentes = array();
	
	// denombrement
	$nbDatesFormatErrone= array();
	$nbNumAnonymIncoherents = 0;
	$nbDoublons = 0;
	$nbLigneDoublons = 0;
	$nbVariablesRenseignees = array();
	$nbVariablesPertinentes = array();
	$nbVariablesNonAttendues = array();
	$nbVariablesReserveesMajeurs = array();
	$nbVariablesReserveesMineurs = array();
	$nbErreursDatesNaissances = array();
	
	// tableau des chaines de caractères
	$tabDatesFormatErrone = array();
	$tabErreursDatesNaissances = array();
	$tabVariablesPertinentes = array();
	$tabVariablesNonRenseignees = array();
	$tabVariablesNonAttendues = array();
	$tabVariablesReserveesMajeurs = array();
	$tabVariablesReserveesMineurs = array();
	$tabNumAnonymIncoherents = array();
	$tabDoublons = array();
	$tabLignesSupprimees = array();
	
	$dateTraitement = date("Y-m-d H:i:s");
	$tabValues= array();
	
	if(!isset ($e_file)){
	
		$dossier = opendir($folder);
		/*
		while ($Fichier = readdir($dossier)) {
		  if ($Fichier != "." && $Fichier != ".." && $Fichier !="Backup") {
		    $nomFichier = $folder."/".$Fichier;
		    //echo $nomFichier."<BR>";
		    $url = "ONPE_Xml2Csv.php?file=".$Fichier;
		    echo "<a href='".$url."'>".$Fichier."</a> - (<a href='".$url."&test=1'>test</a>)</br>";
		  }
		}
		*/
		
		while ($Fichier = readdir($dossier)) {
		  if ($Fichier != "." && $Fichier != ".." && $Fichier !="Backup") {
			$tabFichiers[]=$Fichier;
		  }
		}
		sort($tabFichiers);
		foreach($tabFichiers as $fichier){
			$nomFichier = $folder."/".$fichier;
			//echo $nomFichier."<BR>";
			$url = "ONPE_Xml2Csv.php?file=".$fichier;
			echo "<a href='".$url."'>".$fichier."</a> - (<a href='".$url."&test=1'>test</a>)</br>";
		}
		
		closedir($dossier);
	
		exit();
	
	}else{
	
		// Sauvegarde du ficher xml
		$dateJour=date("Y-m-d");
		//$csvfile="./".$Fichier."-".$dateJour.".csv";
		$xmlNewfile= "d2016_".substr($e_file,0,-3)."xml";
		$csvfile= substr($e_file,0,-3)."csv";
		$csvfileVerif= "Verif_".substr($e_file,0,-3)."csv";
	
	}
	
 
	
	// nom du fichier
	//$nomFic="ONED-035-20120702_2012.XML";

	// Lecture du ficher xml
	//$xmlfile="./imports/".$nomFic;

	$xmlfile=$folder."/".$e_file;
	// echo "Chargement du fichier XML";
	//recherche de la date de ce fichier
	
	$i=0;
	$nbLignesTraitees=0; // nb lignes traitée
	$nbLignesIni=0;

	$xml = new XMLReader();
	
	if(!$xml->open($xmlfile)){
		die("Failed to open input file.");
	}
	/*
	$schemaFile = './decret.xsd';
	echo $xml->setSchema($schemaFile);
	
	exit();
	*/
	$xmlstr = "";
	$xmlstr ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	//$xmlstr .= "<SNATED xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"snated.xsd\">";
	$xmlstr .= " 
	    <DECRET>
		<INTRO>
			<VERSIONXSD>2.01</VERSIONXSD>
			<DATEXPORT>".date('Y-m-d')."</DATEXPORT>
		</INTRO>
		";
	
	$fp = fopen($xmlNewfile, 'w'); 
	fputs($fp, $xmlstr);
	fclose($fp); 
	
	$xmlstr= "";
	
	//initialisation du fichier csv car ensuite �criture ligne par ligne
	$csvstr.= "";
	$fp = fopen($csvfile, 'w'); 
	fputs($fp, $csvstr);
	fclose($fp); 
	
	
	
	
	
	
	
	// création de la structure du fichier CSV
	$sql = "SELECT `NEWFIELD`, `Type`,`Nsp` FROM `NOMENCLATURE` WHERE 1  ORDER BY `NEWFIELD` ";
	//echo $sql ."<br />";
	$result  = ExecRequete($sql, $maConnection);
	$code ="";
	$tabCol = array();
	$tabColNSP = array();
	$tabColType = array();
	$listeVariables = array();
	$listeVariablesAbsentes = array();
	$listeColonnes="";
	
	while ($record = $result->fetch_assoc()) {

		$listeColonnes .= $record['NEWFIELD'].",";
		// Gestion des Nsp
		
		$tabCol[$record['NEWFIELD']]="NULL";
		$tabColNSP[$record['NEWFIELD']]=$record['Nsp'];	
		$tabColType[$record['NEWFIELD']]=$record['Type'];	
		
		$listeVariables[]=$record['NEWFIELD'];
		$csvstr .= $record['NEWFIELD'].$separateur;
		$code.= 		"\n		case \"".$record['NEWFIELD']."\":
		
			if(\$val==\"\"){
				//echo \$cptErreur++.\": valeur '\".\$cle.\"' manquante <br />\";
			}else{
				//echo \$cptErreur++.\": valeur '\".\$cle.\"' non attendue <br />\";
			}
			break;		
		
		";
		
	}
	$csvstr.= "\n";
	$listeColonnes = substr($listeColonnes,0,(strlen($listeColonnes) - 1));
	
	$reqTest = " insert IGNORE into DONNEES_TMP (".$listeColonnes.",DateTraitement) values "; 
	
	/*
	
	echo $code;
	exit();
	*/
	/*
	echo "<pre>";
	print_r($tabColNSP);
	print_r($tabColType);
	echo "</pre>";
	exit();
	*/

	if(true){
	$cptNBFrat=0;
	$cpt=0;
	while($xml->read()){
		if($xml->nodeType==XMLReader::ELEMENT && $xml->name == $wrapperName){
			if($nbLignesIni==1300){
				break; //simplement pendant la mise au point
			}
			
			$xmlstr .= "<DONNEE>";
			$res = Conso($tempo4,0);
			if($cpt==1108 || $cpt==1124 || $cpt ==1299){
						echo "<br />-------------------------------------Entree de boucle while :".$cpt."<br />";;
			}
			while($xml->read() && $xml->name != $wrapperName){
				
				if($xml->nodeType==XMLReader::ELEMENT){

					$cle=$xml->name;
					//$tabVariablesPresentes[$cle] =(isset($tabVariablesPresentes[$cle])) ? $tabVariablesPresentes[$cle]+1 : 1;
					/*
					if(isset($tabVariablesPresentes[$cle]))
						$tabVariablesPresentes[$cle]++;
					else
						$tabVariablesPresentes[$cle]=1;
					*/
					$val = $xml->readString();
					
					
					// traitement des fichiers au format du décret de 2011
					//convertSchema();
					
					if($cle == "NBFRAT")
						 $cptNBFrat++;	
					
					switch ($cle) {
						case "NUMANONYM":
							$tabAnonym[$cle]=$val;
							/*
							echo $cle .":".$val."<br /> ";
							
							if(strpos($val, "76551") >1)
								echo $cle .":".$val."*********************************<br /> ";
							*/
						case "":
							break;
	
						case "NUMANONYMANT":

							if(trim($val != '')){ // seconde anonymisation.
								$newVal = substr(hash('sha1',substr((string)$val,0,20).$cleSecreteONED),0,20).substr(hash('sha1',substr((string)$val,20,20).$cleSecreteONED),0,20);
									if($newVal == '694d3d6ed5c0fc7bf26e7c80b17ec7ea05674348'){
										//echo $val. "->".$newVal;
										//exit();
										$trace = true;
									}else{
										$trace = false;
									}
									$tabCol[$cle]=$newVal;
							} else {
									$tabCol[$cle]= "ffffffffffffffffffffffffffffffffffffffff";
							}
							//echo $cle .":".$val."<br /> ";
							//echo $tabCol["NUMANONYM"];
	
							/*
							if (!in_array ($tabCol[$cle],$tabJeunes['all']))
								$tabJeunes['all'][]=$tabCol[$cle];
							*/
							break;
							
						/*
						echo "<pre>";
						echo "  ".$cle."-> ".$val;
						echo "</pre>";
						*/

						case "ANSPERE":
						case "ANSMERE":
						case "ANSA1":
						case "ANSA2":
							$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
							if($tabCol[$cle] == "1850" || $tabCol[$cle] == "1880" || (strlen($tabCol[$cle]) != 4 && trim($tabCol[$cle]) !="") || ( trim($tabCol[$cle]) !="" && substr($tabCol[$cle],0,2) != '99' &&  substr($tabCol[$cle],0,2) != '20' && substr($tabCol[$cle],0,2) != '19') ){
								DatesFormatErrone($cle,$val," Valeur non conforme "); 
								//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' erron&eacute;e : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$tabCol[$cle]=""; // valeur manquante
								
							}
							
							break;
							
						case "DATIP":
						case "DATSIGN":
						case "DATJE":
						
						case "DATDECPE":
						case "DATPPE":
						case "DATDEB":
						case "DATDECAP":
						case "DATFIN":
						case "NOTIFEVAL":
						case "FINEVAL":
							$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
							//$buf = $tabCol[$cle];
							
							// traitement des dates au format jj/mm/AAAA
							if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
								DatesFormatErrone($cle,$val,"JJ/MM/AAAA au lieu de AAAA-MM-JJ  "); 
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("/", $tabCol[$cle] );
								$tabCol[$cle] = $parts[2]."-".$parts[1];	
								$flagDATDC=true;
								
							}else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
								DatesFormatErrone($cle,$val,"JJ-MM-AAAA au lieu de AAAA-MM-JJ  "); 
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("-", $tabCol[$cle] );
								$tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[2];	
								$flagDATDC=true;
							}else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
								DatesFormatErrone($cle,$val,"AAAA/MM/JJ au lieu de AAAA-MM-JJ  "); 
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("/", $tabCol[$cle] );					
								$tabCol[$cle] = $parts[0]."-".$parts[1]."-".$parts[2];
								$flagDATDC=true;								
							}
							//echo $buf."-> ".$tabCol[$cle]."<br /> ";
							
							if( $tabCol[$cle] != ""  && substr($tabCol[$cle],0,2) != '99' &&  substr($tabCol[$cle],0,2) != '20' && substr($tabCol[$cle],0,2) != '19')
								DatesFormatErrone($cle,$val," Valeur non conforme "); 
							
							break;
							
						
						
						
						
						
						case "DATDCPERE":
						case "DATDCMERE":
						
							$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
							//$buf = $tabCol[$cle];
							
							// traitement des dates au format jj/mm/AAAA
							if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
								DatesFormatErrone($cle,$val,"JJ/MM/AAAA au lieu de AAAA-MM  "); 
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("/", $tabCol[$cle] );
								$tabCol[$cle] = $parts[2]."-".$parts[1];	
								$flagDATDC=true;
								
							}else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
								DatesFormatErrone($cle,$val,"JJ-MM-AAAA au lieu de AAAA-MM  "); 
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("-", $tabCol[$cle] );
								$tabCol[$cle] = $parts[2]."-".$parts[1];	
								$flagDATDC=true;
							}else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
								DatesFormatErrone($cle,$val,"AAAA/MM/JJ au lieu de AAAA-MM  "); 
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("/", $tabCol[$cle] );					
								$tabCol[$cle] = $parts[0]."-".$parts[1];
								$flagDATDC=true;								
							}else if(preg_match( '`^\d{4}-\d{2}-\d{2}$`' , $tabCol[$cle] )){ // le format est correct mais il faut supprimer le jour
								DatesFormatErrone($cle,$val,"AAAA-MM-JJ au lieu de AAAA-MM  "); 
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("-", $tabCol[$cle] );					
								$tabCol[$cle] = $parts[0]."-".$parts[1];	
								$flagDATDC=true;
							}
							//echo $buf."-> ".$tabCol[$cle]."<br /> ";
							
							if( $tabCol[$cle] != ""  && substr($tabCol[$cle],0,2) != '99' &&  substr($tabCol[$cle],0,2) != '20' && substr($tabCol[$cle],0,2) != '19')
								DatesFormatErrone($cle,$val," Valeur non conforme "); 
							
							break;
								
						case "MNAIS":
						case "ANAIS":
						case "SEXE":
							//$tabAnonym[$cle]=$val;
							// controle sur age
							$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
							break;
						
						case "MOTFININT":
							$MotFinInt = (string)$val;    // $val est un objet il faut un cast
							if($MotFinInt =='1' || $MotFinInt =='2' || $MotFinInt =='3' || $MotFinInt =='9'){
								if($tabCol['TYPEV'] == '9')  // valeur provenant de la conversion
									$tabCol['TYPEV'] = '3';
							}else{
								if($tabCol['TYPEV'] == '9')
									$tabCol['TYPEV'] = '2';
							}
							$tabCol[$cle]= $MotFinInt;
							break;							
						case	 "SUPP":
							//Clés surchargée par convertSchema() et  ignorées
							break;
						case "TYPEV":
							//echo "Dans traitement ".$cle."------------------------------------------------".$val."<br />";
							$tabCol[$cle]= (string)$val; 
							break;
					
						default :
							
							//echo $cle ."--->".$val."<br />";
							
							$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
							// traitement des dates au format jj/mm/AAAA
							
							if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
								
								//echo $cptErreur++.": format date JJ/MM/AAAA pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("/", $tabCol[$cle] );
								$tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];	
								
								DatesFormatErrone($cle,$val, "au lieu de ".$tabCol[$cle]); 
							
							}else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("-", $tabCol[$cle] );
								$tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];	
								DatesFormatErrone($cle,$val, "au lieu de ".$tabCol[$cle]); 
								
							}else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
								//echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								$parts = explode("/", $tabCol[$cle] );					
								$tabCol[$cle] = $parts[0]."-".$parts[1]."-".$parts[2];	
								DatesFormatErrone($cle,$val, "au lieu de ".$tabCol[$cle]); 
							}
							//if($cle == "FINEVAL")
							//	echo $cle ."--->".$val.":".$tabCol[$cle]."<br />";
							break;
							
						
					}
					//
									
				}
			}
			if($cpt==1107 || $cpt==1123 ||  $cpt==1298){
				echo "fin de boucle"."--------------".$cpt."<br />";
			}
					
			$tempo4 += Conso($tempo4,0);
		//echo "Fin anticipee : ".$tabCol['DATDEB'];
			//exit();

			//echo "<br />TYPEV*******************************************=".$tabCol["TYPEV"]."<br />";
				
			// On reconstitue le ficher XML
		
			//if($tabCol['NUMANONYM'] == "694d3d6ed5c0fc7bf26e7c80b17ec7ea05674348")
			//	$trace = true;
			$j=0;
			
			$cols="";
			$values = "";
			/*
			foreach($tabCol as $cle=>$val){
				if($cle != $listeVariables[$j])
					echo $cptErreur++.":'".$cle."' --> '". $listeVariables[$j]."'  ligne ".$i."<br />";	
					
			*/
			$tabAnonym['NEWNUMANONYM']=$tabCol["NUMANONYM"];
			
			// prétraitement de TYPEV
			
		
			
			switch($tabCol["TYPEV"]){
				case '1X':
				case '2X':
				case '3X':
				case '6X': // Il s'agit de valeur traduite de CODEV
					$modalitesCADM = array('10','11','12','13','14','15','16','18','19','20','21');
					$modalitesCASSED = array('11','14','15','16','17','18','19','21','22','23','24');
					if( in_array($tabCol["NATPDECADM"], $modalitesCADM) ||  in_array($tabCol["NATDECASSED"],$modalitesCASSED)) {
						$tabCol["TYPEV"]= 1;
					}else{
						$tabLignesSupprimees[]=$tabCol["TYPEV"];
						$nbLS++;
						// déactivation de la suppression
						//$tabCol["TYPEV"]= "SUPP";
						
						
					}
					break;
				
				case '4X':
					$tabCol["TYPEV"]= "1";
					break;
				case '5X':

					if($tabCol["MOTFININT"] == '1' || $tabCol["MOTFININT"] == '2' || $tabCol["MOTFININT"] == '3'){
						if($tabCol["DATFIN"] !="9999-99-99" &&  $tabCol["DATFIN"] != ""){
							$tabCol["TYPEV"]= "3";
						}else{
							//aucun changement
						}
					}else{
						if($tabCol["DATFIN"] !="9999-99-99" &&  $tabCol["DATFIN"] != ""){
							//aucun changement
						}else{
							$tabCol["TYPEV"]= "2";
						}
					}
					break;
				case '9X': // en standby
					if(($tabCol["DATFIN"] !="9999-99-99" &&  $tabCol["DATFIN"] != "") || $tabCol["MOTFININT"] != ''   )	
						$tabCol["TYPEV"]= 3;
					else
						$tabCol["TYPEV"]= 2;
			
					break;
			
				default : // On conserve les valeurs saisies
					
			}
	
			
			if($tabCol['TYPEV'] == "SUPP"){
				// intéruption du traitement, on passe à la ligne suivante 
				unset($tabCol);
				continue;
			}
	
			
			
			// Majorité ?
			$dateNaissance = $tabCol['ANAIS']."-".$tabCol['MNAIS']."-28";  // ne connaisant pas le jour
			if($tabCol["TYPEV"] == '3'){
				$dateRef = $tabCol['DATFIN'];
			}else{
				if(isset($tabCol["DATDEB"]) && $tabCol["DATDEB"] !="9999-99-99" &&  $tabCol["DATDEB"] != "" )
					$dateRef = $tabCol['DATDEB'];
				else
					$dateRef = $tabCol['DATDECPE'];
			}
			
			// SIRUS
			$dateRef = substr($dateRef,0,4)."-01-01";
			

			$majeur = Majeur($dateNaissance,$dateRef);
			
			if($majeur){
				$nbJeunesMajeurs++;
			
			}else{
				$nbMineurs++;
				// control de la cohérence sur la date de naissance
				if($tabCol["ANAIS"] > $tabCol['ANSA1'] || $tabCol["ANAIS"] > $tabCol['ANSA2']){
					$tabErreursDatesNaissances[] =  $tabAnonym['NUMANONYM']. " Age du jeune ".$tabCol["ANAIS"]." supérieur à celui d'un des parents ". $tabCol["ANAIS"]." | ".$tabCol['ANSA2'];
				
				}
			
			}
			
			if (!in_array ($tabCol['NUMANONYM'],$tabJeunes['all'])){
				$tabJeunes['all'][]=$tabCol['NUMANONYM'];
			}
			
			
			
			
			$res= Conso($tempo5,0);
			
						
			foreach($listeVariables as $cle){
 
				
				$val= trim($tabCol[$cle]);
				/*
				if(!isset($tabCol[$cle]))
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non presente pour la  ligne ".$i."<br />";	
				*/
				// pour bdd, inutile
				//$cols.=$cle.",";
				
				$values.="'".addslashes($val)."',";
				
				
				
				$xmlstr .= toXML($val,$cle,1);
				
				if ($val != "NULL" && $val != ""){
					$csvstr .= $val.$separateur;
					
				}else if($val == "NULL"){
					$csvstr .= $separateur;
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante pour la  ligne ".$i."<br />";	
				}else{
					$csvstr .= $separateur;
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' vide pour la  ligne ".$i."<br />";	
				}
				//if($trace)
					//echo $cle ."--->".$val."<br />";
				// on initialise
				
				
				$listeErreurs .= controlCoherenceBug($cle,$val,$cpt);
				
				 
				$j++;
				//$tabCol[$cle]="NULL";
			}
			
			
			$tempo5 += Conso($tempo5,0);
			//echo $tabAnonym['NUMANONYM'].' : '.$tabAnonym['MNAIS']."|" .$tabAnonym['ANAIS']."|".$tabAnonym['SEXE']."<br >";
			if(isset($tabControl[$tabAnonym['NUMANONYM']])){
				//on compare
				if($tabControl[$tabAnonym['NUMANONYM']] != $tabCol['ANAIS']."|" .$tabCol['MNAIS']."|".$tabCol['SEXE']){
					
					//echo "<br />***************** ".$tabCol["NUMANONYM"]."    ***********<br />  ";
					//echo $cptErreur++." : ERREUR :".$tabAnonym['NUMANONYM']."->".$tabControl[$tabAnonym['NUMANONYM']]." different de ".$tabAnonym['MNAIS']."|" .$tabAnonym['ANAIS']."|".$tabAnonym['SEXE']."          ".$tabAnonym['NEWNUMANONYM']."<br />";
					$nbNumAnonymIncoherents ++;
					$tabNumAnonymIncoherents[$tabAnonym['NUMANONYM']][]= $tabControl[$tabAnonym['NUMANONYM']]." <> ".$tabCol['ANAIS']."|" .$tabCol['MNAIS']."| ".$tabCol['SEXE'] ;
				
				}
			}else{
				
				$tabControl[$tabAnonym['NUMANONYM']] = $tabCol['ANAIS']."|" .$tabCol['MNAIS']."|".$tabCol['SEXE'];
				//echo $tabAnonym['NUMANONYM'].":---------------------------------------------:". $tabControl[$tabAnonym['NUMANONYM']]."<br /> ";
			}
			 
						
			// Recherche de Doublon
			$bDoublon = false;
			if(isset($tabCsvStr[$tabAnonym['NUMANONYM']])){
				//echo "Connu <br />";
				if (in_array ($csvstr,$tabCsvStr[$tabAnonym['NUMANONYM']])){
					// doublon
					//echo "Doublon détecté <br />";
					$nbCsvStr[$tabAnonym['NUMANONYM']] =(isset($nbCsvStr[$tabAnonym['NUMANONYM']])) ? $nbCsvStr[$tabAnonym['NUMANONYM']]+1 : 1;
					$bDoublon = true;
					$nbLigneDoublons++;
					// pour vérification par pointage voir génération du fichier csv
					$tabDoublons[]=$tabAnonym['NUMANONYM'];
				}else{ 
					$tabCsvStr[$tabAnonym['NUMANONYM']][]=$csvstr;
					//$nbCsvStr[$tabAnonym['NUMANONYM']] =(isset($nbCsvStr[$tabAnonym['NUMANONYM']])) ? $nbCsvStr[$tabAnonym['NUMANONYM']]+1 : 1;
				}
			}else{
				//echo "Inconnu <br />";
				$tabCsvStr[$tabAnonym['NUMANONYM']][]=$csvstr;
				//$nbCsvStr[$tabAnonym['NUMANONYM']] = 1;

			}
			
			
			// annee conernee est renseignée
			
			if(!isset($tabJeunes[$anneeConcernee]))
				$tabJeunes[$anneeConcernee][0] = "ffffffffffffffffffffffffffffffffffffffff";
				
			if (!in_array ($tabAnonym['NUMANONYM'],$tabJeunes[$anneeConcernee]))
				$tabJeunes[$anneeConcernee][]=$tabAnonym['NUMANONYM'];
			
			 // Avant
			
			
			if(!$bDoublon){
				$values = substr($values,0,(strlen($values) - 1));
				//echo $values."<br />";
				$tabValues[] = $values;
			}		
			/*
			// en base de donnée
			$cols = substr($cols,0,(strlen($cols) - 1));
			$sql = "insert IGNORE into DONNEES_TMP (".$cols.",DateTraitement) values (".$values.",'".$dateTraitement."')";
			$result  = ExecRequete($sql, $maConnection);
			//echo $sql ."<br />";
			*/
			$nbVariablesPresentes = count($tabVariablesPresentes);

			unset($tabCol);
		
			
			$xmlstr.= "\n</DONNEE>\n";
			$csvstr.= "\n";
			
			if(!$bDoublon){
				$fp = fopen($xmlNewfile, 'a'); 
				fputs($fp, $xmlstr);
				fclose($fp); 
			
				$fp = fopen($csvfile, 'a'); 
				fputs($fp, $csvstr);
				fclose($fp); 
				
				$nbLignesTraitees++;
			}else{
				$fp = fopen($csvfileVerif, 'a'); 
				//fputs($fp, $csvstr);
				fputs($fp, $tabAnonym['NUMANONYM'].";\n");
				fclose($fp); 
				
			
			}
			$xmlstr= "";
			$csvstr= "";
			
			//echo  $i.":".$xml->name."<br />";
			$nbLignesIni++;
			/*
			if($i==100)
				break;
			*/
			
			$cpt++;
		}
		//print_r ($tabValues);
		
	
	}
	
	$xmlstr.= "\n</DECRET>\n";
	
	$xml->close();
	$res = Conso($tempo6,1);	
	$listeInserts ="";
	foreach($tabValues AS $values){
		//echo $values."<br />";
		$listeInserts .= "(".$values.",'".$dateTraitement."'),";
		
		//echo $sql ."<br />";
	}
	$listeInserts = substr($listeInserts,0,(strlen($listeInserts) - 1));
	$reqTotale = $reqTest.$listeInserts.";";
	
	//$result  = ExecRequete($reqTotale, $maConnection);
	$tempo6 += Conso($tempo6,0);		
	
	//exit();
	} // false
	
	//exit();

	/*
	// Sauvegarde du ficher xml
	$dateJour=date("Y-m-d");
	//$csvfile="./".$Fichier."-".$dateJour.".csv";
	$xmlfile= "new_".substr($e_file,0,-3)."xml";
	*/
	//echo "Nbre de lignes : ".$i."->".$xmlNewfile;
	
	$fp = fopen($xmlNewfile, 'a+'); 
	fputs($fp, $xmlstr);
	fclose($fp);
	
		
		
	$max=0;
	$anneeRef="";
	krsort($tabDecision);
	$nbLignes = $nbLignesIni;
	
	foreach($tabDecision as $cle=>$val){
		if($val > $max){
			$max = $val;
			$anneeRef=$cle;
		}
		//$commentaire .= $cle." : ".$val." décisions \n";
		$commentaire .= $cle." : ".$val." décisions (".(count($tabJeunes[$cle] )-1).")\n";
	}
	
	foreach($listeVariables as $cle){
		if(!isset($tabVariablesPresentes[$cle]))	
			$listeVariablesAbsentes[]=$cle;
	}
	/*
	foreach($listeVariables as $cle){
		//echo "cle:".$cle." ->".$tabVariablesPertinentes[$cle]."<br />";
		if(!isset($nbVariablesPertinentes[$cle]) && isset($tabVariablesPresentes[$cle] ))	
			$listeVariablesNonRenseignees[]=$cle;
	}
	*/
	
	 //echo $cptNBFrat++ ."NBFRAT =".$val."<br />";	

	foreach($listeVariables as $cle){
		//echo "cle:".$cle." ->".$tabVariablesPertinentes[$cle]."<br />";
		if(!isset($nbVariablesRenseignees[$cle]))	
			$listeVariablesNonRenseignees[]=$cle;
	}
	
	
	if($flagDATDC)
		$commentaire .=" format DATDCPERE ou DATDCMERE non conforme \n"; 
	 if($flagNUMDEPT)
		$commentaire .=" format NUMDEPT non conforme \n"; 
		
	/*if(count($tabLignesSupprimees))
		$commentaire .= " Nombre de lignes supprimées du fait de CODEV non interprétable : ".count($tabLignesSupprimees)." \n"; 
	*/
	if($nbLigneDoublons >0)
		$commentaire .= " Nombre de lignes supprimées pour cause de doublons : ".$nbLigneDoublons." \n"; 
	$commentaire = utf8_encode($commentaire);
	
	
	// Nom du fichier destination
	$nomFicDest = "CD".$numDept."_ExportOnpe_".$anneeRef.".csv";
	$nomFicAudit = "CD".$numDept."_ExportOnpe_".$anneeRef.".pdf";
		
	$monImportation = new importationsXML;
	$monImportation->code_departement = $numDept;
	$monImportation->annee =$anneeRef;
	$monImportation->nom_fichier_ini=$e_file;
	$monImportation->nom_fichier_traduit =$nomFicDest;
	$monImportation->num_lignes =$nbLignesTraitees;
	$monImportation->commentaires = $commentaire ;
	$monImportation->date_importation= date ("Y-m-d H:i:s.", filemtime($xmlfile));
	$monImportation->date_traitement = $dateTraitement;
		
	$result = $maConnection->query(requete_insert($monImportation));
	//echo "\n".	requete_insert($monImportation)."\n";
	$lastInsert = $maConnection->insert_id;
	
	
	$nomRepertoire = "../../chiffres/".$anneeRef;
	if (is_dir($nomRepertoire)) {
                   // echo 'Le r걥rtoire existe dꫠ!';  
        }else { 
		mkdir($nomRepertoire);
		//echo "Crꢴion :".$nomRepertoire;
	}

	$commande = 'mv '.$csvfile.' '.$nomRepertoire.'/'.$nomFicDest;
	exec($commande);
	
	
	
	
	
	if($test){
		
		include ("./includes/audit_main_inc.php");
		//exit();

		echo "<pre>";
		//exit();
		/*
		arsort($tabVariablesPresentes);
		$tabRatioVariablesPresentesTMP= array();
		$valBuf = -1;
		foreach($tabVariablesPresentes as $cle=>$val){
			//echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
			echo $cle." (".$val.") : <br />";
			// traitement supplémentaire pour ordonner au second niveau
			if( $val <> $valBuf){
				echo "Nouvelle valeur : ".$val . " en buf :".$valBuf." <br />";
				if($valBuf >=0 && isset($tabRatioVariablesPresentesTMP) ){
					arsort($tabRatioVariablesPresentesTMP);
					foreach($tabRatioVariablesPresentesTMP as $var=>$ratio){
						
						echo " ".$var." ".$nbVariablesPertinentes[$var]." | ".$valBuf ."--------------".$ratio."% <br />";

					}
					echo "<pre>";
					print_r($tabRatioVariablesPresentesTMP);
					echo "</pre>";
					unset($tabRatioVariablesPresentesTMP);
					
				}
				echo "Ajout de la cle ".$cle."<br/>";
				$tabRatioVariablesPresentesTMP[$cle]=round(100*($nbVariablesPertinentes[$cle]/$nbLignes),2);
				$valBuf=$val;
				
			}else{
				//echo "on rempli le tableau <br />";
				echo "ajout de la cle ".$cle."<br/>";
				$tabRatioVariablesPresentesTMP[$cle]=round(100*($nbVariablesPertinentes[$cle]/$nbLignes),2);
			}

		}
		*/
 
		/*		
		echo "Liste des variables pertinente <br />";
		print_r($nbVariablesPertinentes);
		print_r($tabVariablesPresentes);
		
		
		echo "Liste des variables NSP <br />";
		print_r($listeVariablesNonRenseignees);
		*/
		
		//print_r($tabCsvStr);
		//print_r($nbCsvStr);
		//print_r($tabNumAnonymIncoherents);
		//echo "nbPbNum : ".$nbNumAnonymIncoherents;
		//print_r($tabVariablesNonAttendues);
		//print_r($nbDatesFormatErrone);
		//print_r($tabDatesFormatErrone);
		//print_r($nbVariablesReserveesMajeurs);
		//print_r($tabVariablesReserveesMajeurs);
		//print_r($nbVariablesReserveesMineurs);
		echo "</pre>";
		//exit;
	
	}else{
		$isFichier=true;
		include ("./includes/audit_main_inc.php");
	
	}
	
	
	
	
	
	echo "<hr/>";
	echo "d&eacute;partement " .$monImportation->code_departement." <br/>";
	echo "Boucle1 (convertShema) : ".$tempo1." <br/>";
	echo "Boucle2 (ControlCoherence) : ".$tempo2." <br/>";
	echo "Boucle3 (BDD) : ".$tempo3." <br/>";
	echo "Boucle4 (read) : ".$tempo4." <br/>";
	echo "Boucle5 (foreach) : ".$tempo5." <br/>";
	echo "Boucle6 (Insert Blob) : ".$tempo6." <br/>";
	echo "Nbre insert : ".$nbInsert." <br/>";
	echo "Nbre de lignes : ".$nbLignes ." <br/>";
	echo "Nbre de jeunes : ". count($tabJeunes['all']) ." <br/>";
	echo "<pre>";
	echo htmlspecialchars($commentaire, ENT_QUOTES);
	echo "</pre>";
	 
	 echo "Nombres de variables pr&eacute;sentes dans ce fichier :".$nbVariablesPresentes. "/".count($listeVariables). "<br />";
	 //Traitement du fichier cr�� dans le r�pertoire accessible par Web
 
	echo "Nombre de lignes supprim&eacute;es du fait de CODEV non interp&eacute;tables : ".count($tabLignesSupprimees). "<br />";
	echo "Nombre de lignes supprim&eacute;es du fait de doublons : ".$nbLigneDoublons. "<br />";

	// backup du fichier source
	$folder = "/home/imports_dept";
	
	if(!$test) {
			
		$commande = 'mv '.$folder.'/'.$e_file.' '.$folder.'/Backup/'.$e_file;
		exec($commande);
		echo " le fichier : ".$csvfile." a &eacute;t&eacute; d&eacute;pos&eacute; dans le r&eacute;pertoire : olinpe.giped.fr/".$anneeRef."/  <br/>";
	}
	
	//echo "trace :".$trace;
	echo "<br/>";
	echo "<br/>";
	//Url à transmettre par mail
	echo "<a href = \"//olinpe.giped.fr/index.php?id=".$lastInsert."\">Interface Web</a>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<a href=\"ONPE_Xml2Csv.php\" >Retour </a>";
	
	echo "<br/>";
	echo "<br/>";
	 
	if($test && false) {
		
	
		echo "Variables pertinentes|pr&eacute;sentes  ----------------------- ratio pertinentes/nbre de lignes :<br />";
		//print_r($nbVariablesPertinentes);
		echo "<br/>";
		echo " Classement suivant la presence des variables<br/>";
		echo "<br/>";
		arsort($tabVariablesPresentes);
		foreach($tabVariablesPresentes as $cle=>$val){
			echo $cle." ".$nbVariablesPertinentes[$cle]."|".$val."   -----------------------           ". round(100*($nbVariablesPertinentes[$cle]/$nbLignes),2)."%      <br />";
		}
		
		echo " Classement suivant leur pertinence (ne sais pas exclu)<br/>";
		echo "<br/>";
		
		arsort($nbVariablesPertinentes);
		foreach($nbVariablesPertinentes as $cle=>$val){
			echo $cle." ".$val."|".$tabVariablesPresentes[$cle]."   -----------------------           ". round(100*($nbVariablesPertinentes[$cle]/$nbLignes),2)."%      <br />";
		}
		
		//print_r($tabVariablesPresentes);
		echo "Valeurs non-attendues :<br/>";
		$numLigne=0;
		foreach($tabVariablesNonAttendues as $cle=>$lignes){
			echo $cle." (".$nbVariablesNonAttendues[$cle].") : <br />";
				foreach($lignes as $ligne){
					echo $numLigne++ ."-> ".$cle." : ". $ligne. " <br />";
				}
		}

		
		echo "Valeurs réservées aux mineurs :<br/>";
		$numLigne=0;
		foreach($tabVariablesReserveesMineurs as $cle=>$lignes){
			echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
				foreach($lignes as $ligne){
					echo $numLigne++ ."-> ".$cle." : ". $ligne. " <br />";
				}
		}
		
		
		echo "Valeurs réservées aux jeunes-majeurs :<br/>";
		$numLigne=0;
		foreach($tabVariablesReserveesMajeurs as $cle=>$lignes){
			echo $cle." (".$nbVariablesReserveesMajeurs[$cle].") : <br />";
				foreach($lignes as $ligne){
					echo $numLigne++ ."-> ".$cle." : ". $ligne. " <br />";
				}
		}
		
		echo "<pre>";
		print_r($nbDatesFormatErrone);
		print_r($nbVariablesReserveesMajeurs);
		print_r($nbVariablesReserveesMineurs);
		echo "</pre>";
		
		
		
	}else{
		echo $listeErreurs;
		envoi_mail_chiffres("mroger@giped.gouv.fr",$lastInsert,"RDD",$monImportation->code_departement." (".$anneeRef.")");
	}
	exit();
	
	
	

function envoi_mail_chiffres ( $destinataire, $add = "", $messageType="RDD",$CD="00") {
	global $maConnection;
	//
	$headers ='From: "Informatique" <informatique@giped.gouv.fr>'."\n";
	$headers .='Reply-To: informatique@giped.gouv.fr'."\n";
	$headers .= "Return-Path: informatique@giped.gouv.fr\n";
	//$headers .= "CC: dhuynh@giped.gouv.fr\n";

	$headers .='Content-Type: text/html; charset="iso-8859-1"'."\n";
	$headers .= "X-Priority: 1 (Highest)\n";
	$headers .= "X-MSMail-Priority: High\n";
	$headers .= "Importance: High\n";
	$headers .='Content-Transfer-Encoding: 8bit'."\n";
	$headers .= 'MIME-version: 1.0'."\n";  
     
	switch($messageType){
		case "RDD" :
			$sujet = "Dispositif de remont�e des donn�es du CD".$CD;
			$message = "<html><head><title>Remont�e des donn�es</title></head><body>
			
			Bonjour,<br /><br />

			Un fichier vient d'�tre r�ceptionn� sur le serveur et pr�par� en vue de son analyse. 
			<br /><br /><br />
			
			<a href='http://olinpe.giped.fr/index.php?decret=2016&id=".$add."' >Informations relatives � ce fichier</a><br />
			
			Cliquez sur ce lien pour acc�der aux informations sur cette exportation et pour t�l�charger le fichier csv. 
			
			<br /><br /><br />
			En cas d'anomalie, nous alerter d�s que possible.

			<br /><br />
				
			
			
			Bien cordialement <br />

			Michel ROGER
			</body>
			</html>
			";
			
			break;
		
			
		default :
	
	}
     if(mail($destinataire,$sujet,$message,$headers ))
     {
	  return true;
     }
     else
     {
	  return false;
     } 
      
}	
	
	
function toXML($champ,$nomChamp="ENCOURS",$tabu=0) {
	
	if(trim($champ)=="" || $champ =="NULL")
		return "\n".toTab($tabu)."<".text_to_xml($nomChamp)." />";

	else
		return "\n".toTab($tabu)."<".text_to_xml($nomChamp).">".text_to_xml($champ)."</".$nomChamp.">";
}



function text_to_xml ($string){
    $char_bad = array(
	'<',
	'>',
	'&'
	);
    $char_good = array(
	'&lt;',
        '&gt;',
	'&amp;'
	);  
    // Correction mauvais encodage des caract�res non ISO-8859-1		
    $carKo=array("\xC2\x9C","\xC2\x8C","\xC2\x80");  //"�","�","�"
    $carOk=array("\xC5\x93","\xC5\x92","\xE2\x82\xAC");
    
    return  str_replace($carKo,$carOk,utf8_encode(str_replace($char_bad,$char_good,$string)));
}


function toComment($lib){
	return "<!-- ".text_to_xml($lib)." -->";
}
function toTab($n){

	switch($n){
		case 1 :
			$indent = "    ";
			break;
		case 2 :
			$indent = "        ";
			break;
		case 3 :
			$indent = "            ";
			break;
		case 4 :
			$indent = "                ";
			break;	
		case 5 :
			$indent = "                    ";
			break;
		case 6 :
			$indent = "                        ";
			break;
		case 7 :
			$indent = "                            ";
			break;			
		default :
			$indent = "";
		
	}

	return $indent;

}

function baliseXML($balise, $tabu){

	return "\n".toTab($tabu)."<".$balise.">";
}

function controlCoherence($cle, $val){
	global $cptErreur, $tabCol, $anneeConcernee,$numDept, $tabDecision, $flagNUMDEPT, $nbVariablesPertinentes, $tabVariablesPresentes, $tabVariablesAbsentes,$nbVariablesNonAttendues, $nbVariablesReserveesMajeurs, $nbVariablesReserveesMineurs, $majeur;
	global $tempo2, $cptFrat;	
	$erreurDetectee = "";
	
	$res = Conso($tempo2,1);
		
	if($val == "NULL")
		 $val = "";
		 
	if( $val == ""){
	
		//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
	}else{
		$tabVariablesPresentes[$cle] =(isset($tabVariablesPresentes[$cle])) ? $tabVariablesPresentes[$cle]+1 : 1;
	}
	// prétraitement de la variable TYPEV
	VariablesRenseignees();
	
	switch ($cle){
		case "ACCFAM":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "ACCMOD":
			if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATPDECADM = ".$tabCol['NATPDECADM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "ALLOC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "ANAIS":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
			}
			if( ($val < $tabCol['ANSA1'] && $tabCol['ANSA1']!= '9999')  ||  ($val < $tabCol['ANSA2'] && $tabCol['ANSA2']!= '9999')  || ($val < $tabCol['ANSMERE'] && $tabCol['ANSMERE']!= '9999') || ($val < $tabCol['ANSPERE'] && $tabCol['ANSPERE']!= '9999')  )
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."'->". $val." ant�rieure aux adultes (".  $tabCol['ANSA1'] ." | ". $tabCol['ANSA2']." | ".  $tabCol['ANSMERE']." | ". $tabCol['ANSPERE'].")<br />";
			
			if($val !="9999")
					VariablesPertinentes($cle);
					
			
		
			break;		
		
		
		case "ANSA1":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999")
					VariablesPertinentes($cle);
					
			}
			break;		
		
		
		case "ANSA2":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999")
					VariablesPertinentes($cle);
					
			}
			break;		
		
		
		case "ANSMERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999")
						VariablesPertinentes($cle);
						
				}
			}
			break;		
		
		
		case "ANSPERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999")
						VariablesPertinentes($cle);
						
				}
			}
			break;		
		
		
		case "AUTRE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "AUTREDA":
			if($tabCol['DECISION'] != 1  && $val != ""){
				VariablesNonAttendues($cle,$val,"DECISION = ".$tabCol['DECISION']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "AUTREDJ":
			if($tabCol['DECISION'] != 2  && $val != ""){
				VariablesNonAttendues($cle,$val,"DECISION = ".$tabCol['DECISION']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "AUTREHEBER":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "AUTRLIEUACC":
			if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATPDECADM = ".$tabCol['NATPDECADM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "AUTRLIEUAR":
			if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED = ".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CHGLIEU":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle, $val);
			}else if($tabCol['TYPEV'] != "2" && $val != "" ){
				VariablesNonAttendues($cle,$val, "TYPEV different de 2" );
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		
		case "CODEV":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val =="1" || $val =="2" || $val =="3" || $val =="4" || $val =="5" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "COMPOMENAG":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "CONDADD":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val=="3" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CONDEDEV":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle, $val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CONDEDUC":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CONFL":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CONTMERE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "CONTPERE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;	
			
		case "COMPOMENAG":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" && $val !="")
					VariablesPertinentes($cle);
			}
			break;
		case "CSPA1":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CSPA2":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "CSPJM":
			if(!$majeur  && $val != ""){
				VariablesReserveesMajeurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		case "DANGER":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" )
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		case "DATAVIS":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
					
			}
			break;		
		
		
		case "DATDCMERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATDCPERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		/*
		case "DATDEBACC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATDEBAD":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATDEBINTER":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATDEBPLAC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		*/
		
		case "DATDEB":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		case "DATDECAP":
		
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					if($tabCol['DECAP'] != ''){
						$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car DECAP est pr�sent<br />";
					}
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATDECMDPH":
		
			if($val==""){
				if($tabCol['HANDICAP'] == '2'){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car mineur pris en charge HANDICAP  <br />";
				}
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATDECMIN":
			if($tabCol['MOTIFML'] != "18" && $val != "" ){
				VariablesNonAttendues($cle,$val,"MOTIFML = ".$tabCol['MOTIFML']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATDECPE":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				//on m�morise la plus garnde date de d�cision comme variable $anneeConcern�e
				/*
				if(substr($val,0,4) > $anneeConcernee){
					$anneeConcernee = substr($val,0,4);
					echo "ANNEE :".$val. "->".$anneeConcernee."<br />";
				}
				*/
				$anneeConcernee = substr($val,0,4);
				$tabDecision[$anneeConcernee]++;
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
				
			}
			break;		
		
		
		case "DATEXDECMDPH":
		
			if($val==""){
				if($tabCol['HANDICAP'] == '2'){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car mineur pris en charge HANDICAP  <br />";
				}
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		/*
		case "DATFINACC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATFINAD":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATFININTER":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATFINPLAC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		*/
		
		case "DATFIN":
			if($tabCol['TYPEV'] != "3" && $val != ""){
				VariablesNonAttendues($cle,$val,"TYPEV = ".$tabCol['TYPEV']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		case "DATIP":
		
			if($val==""){
				if($tabCol['CODEV'] == '1'){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante CODEV =".$tabCol['CODEV']."<br />";
				}
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATJE":
			if($tabCol['TRAITINFO'] != 4  && $val != ""){
				if($tabCol['TRAITINFO'] =="")
					VariablesNonAttendues($cle,$val,"TRAITINFO n'est pas renseigné" );
				else
					VariablesNonAttendues($cle,$val,"TRAITINFO = ".$tabCol['TRAITINFO']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATSIGN":
			if($tabCol['TRAITINFO'] != 3  && $val != "" && false){ // contrainte à traiter ultérieurement
				VariablesNonAttendues($cle,$val,"TRAITINFO =".$tabCol['TRAITINFO']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATPPE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DCMERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DCPERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DECAP":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DECISION":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DEFINTEL":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DIPLOME":
		
			
			if($tabCol['TYPEV'] != "3" && $val != ""){
				VariablesNonAttendues($cle,$val,"cette variable n'est à renseigner que pour les fins de mesure");
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9" )
						
						VariablesPertinentes($cle);
				}
			}

			break;		
		
		
		case "EMPLA1":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "EMPLA2":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "EMPLJM":
			if(!$majeur  && $val != ""){
				VariablesReserveesMajeurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		
		
		case "ENQPENAL":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "ETABSCOSPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "FINEVAL":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "FREQSCO":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "HANDICAP":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "INSTITPLAC":
			if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18" && $tabCol['NATDECASSED'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED = ".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "INTERANT":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val =="1" || $val =="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "LIENA1":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "LIENA2":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle, $val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;		
				
		
		case "LIEUACC":
			if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATPDECADM =".$tabCol['NATPDECADM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "LIEUPLAC":
			if( $tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "MENAGEJM":
			if(!$majeur  && $val != ""){
				VariablesReserveesMajeurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		
		case "MEREINC":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "MESANT":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		case "MINA":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" )
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		
		case "MINIMA":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "MNAIS":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "MODACC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "MORALITE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "MOTFININT":
			if($tabCol['TYPEV'] != "3" && $val != "" ){
				VariablesNonAttendues($cle,$val,"TYPEV =".$tabCol['TYPEV']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val =="3")
						VariablesPertinentes($cle);
					
				}
			}
			break;		
		
		
		case "MOTIFML":
			if($tabCol['MOTFININT'] != "1" && $tabCol['MOTFININT'] != "2" && $val != "" ){
				VariablesNonAttendues($cle,$val, "MOTFININT=".$tabCol['MOTFININT']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" ){
						VariablesPertinentes($cle);
						//echo "valeur '".$cle."' : |".$val."| <br />";
					}
				}
			}
			break;		
		
		
		case "MOTIFSIG":
			if($tabCol['TRAITINFO'] != "3"  && $val != ""){
				VariablesNonAttendues($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val == "3" || $val == "4")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NATDECASSED":
			if($tabCol['DECISION'] != "2"  && $val != ""){
				VariablesNonAttendues($cle,$val,"DECISION =".$tabCol['DECISION']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NATDECPLAC":
			if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18" && $tabCol['NATDECASSED'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NATNOUVDECPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val=="3" || $val=="4")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NATPDECADM":
			if($tabCol['DECISION'] != 1  && $val != ""){
				VariablesNonAttendues($cle,$val,"DECISION =".$tabCol['DECISION'] );
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "NBCHGLIEU":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else if($tabCol['TYPEV'] != "3" && $val != ""){
				VariablesNonAttendues($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		case "NBFRAT":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="0" && $val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NBPER":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val <2)
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NEGLIG":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NIVSCO":
			if($tabCol['SCODTCOM'] != 2  && $val != ""){
				VariablesNonAttendues($cle,$val, " SCODTCOM =".$tabCol['SCODTCOM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="999")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NONSCO":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="999")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NOTIFEVAL":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NOUVDECPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NUMANONYM":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NUMANONYMANT":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NUMDEP":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				$numDept=$val; // on ne conservera que la derni�re valeur
				if(iconv_strlen($val)<2){
					$flagNUMDEPT = true;
					echo $cptErreur++.": codification '".$cle."' non conforme '".$val."' ->".iconv_strlen($val)."<br />";
					$numDept="0".$val;
				}
				VariablesPertinentes($cle);
			}
			break;		
		
		
		case "ORIGIP":
			if($tabCol['TRAITINFO'] != "1" && $tabCol['TRAITINFO'] != "2"  && $tabCol['TRANSIP']=="" && $val != "" &&  false){  // traitement des incohérences à définir
				VariablesNonAttendues($cle,$val,"TRAITINFO =".$tabCol['TRAITINFO']." et TRANSIP vide " );
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "ORIENTDEC":
			
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;		
		
		case "ORIENTEFF":
			if($tabCol['ORIENTDEC'] != 2  && $val != ""){
				VariablesNonAttendues($cle,$val, "ORIENTDEC =".$tabCol['ORIENTDEC'] );
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "PEREINC":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "PLACMOD":
			if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "PROJET":
			
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
				//echo "PROJET"." et jeune majeur :". $val ."<br />";
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "RESMENAG":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val =="4" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "REVTRAV":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SAISJUR":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SANTE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "SCOCLASPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SCODTCOM":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SECURITE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "SEXA1":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "SEXA2":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "SEXAUT1":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SEXAUT2":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SEXE":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SIGNMIN":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SIGNPAR":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SITAPML":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val =="4" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SOUTSOC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "STATOCLOG":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val =="4" || $val == "5" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITEVAL":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITSIGJE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITSIGNCG":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITSIGOPP":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITSIGSS":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "TITAP":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "TRAITINFO":
		
			if($tabCol['TYPEV'] != 1  && $val != ""){
				VariablesNonAttendues($cle,$val, "TYPEV =".$tabCol['TYPEV']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;
		case "TRANSIP":
			if($tabCol['TRAITINFO'] != "1" && $tabCol['TRAITINFO'] != "2"  && $val != "" && false){    // A traiter ultérieurement
				VariablesNonAttendues($cle,$val, "TRAITINFO=".$tabCol['TRAITINFO']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="999")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "TYPCLASSPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "TYPDECJUD":
			if($tabCol['NATDECASSED'] != "11" && $tabCol['NATDECASSED'] != "14" && $tabCol['NATDECASSED'] != "15" && $tabCol['NATDECASSED'] != "16"  && $tabCol['NATDECASSED'] != "19" && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "24"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "TYPETABSPE":
			if($tabCol['SCOCLASPE'] != "2"  && $val != ""){
				VariablesNonAttendues($cle,$val, "SCOCLASPE =".$tabCol['SCOCLASPE']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="999")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "TYPINTERDOM":
			if($tabCol['NATPDECADM'] != "10" && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "20"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATPDECADM =".$tabCol['NATPDECADM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "TYPEV":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val =="1" || $val =="2" || $val =="3" )
					VariablesPertinentes($cle);
			}
			break;		
		
		case "TYPINTERV":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		
		
		case "VIOLCONJ":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "VIOLFAM":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "VIOLFAMPHYS":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "VIOLPERS":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val == "4")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "VIOLPHYS":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val =="3" || $val == "4")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "VIOLPSY":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "VIOLSEX":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val =="3" || $val == "4")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
			
	
		
	
	
		default :
				break;
	
	
	}
	$tempo2 += Conso($tempo2,0);
	return $erreurDetectee;
	
}

function controlCoherenceBug($cle, $val,$cpt){
	global $cptErreur, $tabCol, $anneeConcernee,$numDept, $tabDecision, $flagNUMDEPT, $nbVariablesPertinentes, $tabVariablesPresentes, $tabVariablesAbsentes,$nbVariablesNonAttendues, $nbVariablesReserveesMajeurs, $nbVariablesReserveesMineurs, $majeur;
	global $tempo2, $cptFrat;	
	$erreurDetectee = "";
	
	if($cpt != 1107 && $cpt != 1123){
	 return "";
	} 
	
	
	
	$res = Conso($tempo2,1);
		
	if($val == "NULL")
		 $val = "";
		 
	if( $val == ""){
	
		//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
	}else{
		$tabVariablesPresentes[$cle] =(isset($tabVariablesPresentes[$cle])) ? $tabVariablesPresentes[$cle]+1 : 1;
	}
	// prétraitement de la variable TYPEV
	
	VariablesRenseignees();
	
	 
	switch ($cle){
		case "ACCFAM":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "ACCMOD":
			if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATPDECADM = ".$tabCol['NATPDECADM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "ALLOC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "ANAIS":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
			}
			if( ($val < $tabCol['ANSA1'] && $tabCol['ANSA1']!= '9999')  ||  ($val < $tabCol['ANSA2'] && $tabCol['ANSA2']!= '9999')  || ($val < $tabCol['ANSMERE'] && $tabCol['ANSMERE']!= '9999') || ($val < $tabCol['ANSPERE'] && $tabCol['ANSPERE']!= '9999')  )
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."'->". $val." ant鲩eure aux adultes (".  $tabCol['ANSA1'] ." | ". $tabCol['ANSA2']." | ".  $tabCol['ANSMERE']." | ". $tabCol['ANSPERE'].")<br />";
			
			if($val !="9999")
					VariablesPertinentes($cle);
					
			
		
			break;		
		
		
		case "ANSA1":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999")
					VariablesPertinentes($cle);
					
			}
			break;		
		
		
		case "ANSA2":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999")
					VariablesPertinentes($cle);
					
			}
			break;		
		
		
		case "ANSMERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999")
						VariablesPertinentes($cle);
						
				}
			}
			break;		
		
		
		case "ANSPERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999")
						VariablesPertinentes($cle);
						
				}
			}
			break;		
		
		
		case "AUTRE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "AUTREDA":
			if($tabCol['DECISION'] != 1  && $val != ""){
				VariablesNonAttendues($cle,$val,"DECISION = ".$tabCol['DECISION']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "AUTREDJ":
			if($tabCol['DECISION'] != 2  && $val != ""){
				VariablesNonAttendues($cle,$val,"DECISION = ".$tabCol['DECISION']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "AUTREHEBER":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "AUTRLIEUACC":
			if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATPDECADM = ".$tabCol['NATPDECADM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "AUTRLIEUAR":
			if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED = ".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CHGLIEU":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle, $val);
			}else if($tabCol['TYPEV'] != "2" && $val != "" ){
				VariablesNonAttendues($cle,$val, "TYPEV different de 2" );
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		
		case "CODEV":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val =="1" || $val =="2" || $val =="3" || $val =="4" || $val =="5" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "COMPOMENAG":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "CONDADD":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val=="3" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CONDEDEV":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle, $val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CONDEDUC":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CONFL":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CONTMERE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "CONTPERE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;	
			
		case "COMPOMENAG":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" && $val !="")
					VariablesPertinentes($cle);
			}
			break;
		case "CSPA1":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "CSPA2":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "CSPJM":
			if(!$majeur  && $val != ""){
				VariablesReserveesMajeurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		case "DANGER":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" )
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		case "DATAVIS":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
					
			}
			break;		
		
		
		case "DATDCMERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATDCPERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		/*
		case "DATDEBACC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATDEBAD":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATDEBINTER":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATDEBPLAC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		*/
		
		case "DATDEB":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		case "DATDECAP":
		
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					if($tabCol['DECAP'] != ''){
						$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car DECAP est pr鳥nt<br />";
					}
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATDECMDPH":
		
			if($val==""){
				if($tabCol['HANDICAP'] == '2'){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car mineur pris en charge HANDICAP  <br />";
				}
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATDECMIN":
			if($tabCol['MOTIFML'] != "18" && $val != "" ){
				VariablesNonAttendues($cle,$val,"MOTIFML = ".$tabCol['MOTIFML']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATDECPE":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				//on m魯rise la plus garnde date de d飩sion comme variable $anneeConcern饍
				/*
				if(substr($val,0,4) > $anneeConcernee){
					$anneeConcernee = substr($val,0,4);
					echo "ANNEE :".$val. "->".$anneeConcernee."<br />";
				}
				*/
				$anneeConcernee = substr($val,0,4);
				$tabDecision[$anneeConcernee]++;
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
				
			}
			break;		
		
		
		case "DATEXDECMDPH":
		
			if($val==""){
				if($tabCol['HANDICAP'] == '2'){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car mineur pris en charge HANDICAP  <br />";
				}
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		/*
		case "DATFINACC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATFINAD":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATFININTER":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATFINPLAC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		*/
		
		case "DATFIN":
			if($tabCol['TYPEV'] != "3" && $val != ""){
				VariablesNonAttendues($cle,$val,"TYPEV = ".$tabCol['TYPEV']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		case "DATIP":
		
			if($val==""){
				if($tabCol['CODEV'] == '1'){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante CODEV =".$tabCol['CODEV']."<br />";
				}
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DATJE":
			if($tabCol['TRAITINFO'] != 4  && $val != ""){
				if($tabCol['TRAITINFO'] =="")
					VariablesNonAttendues($cle,$val,"TRAITINFO n'est pas renseigné" );
				else
					VariablesNonAttendues($cle,$val,"TRAITINFO = ".$tabCol['TRAITINFO']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATSIGN":
			if($tabCol['TRAITINFO'] != 3  && $val != "" && false){ // contrainte à traiter ultérieurement
				VariablesNonAttendues($cle,$val,"TRAITINFO =".$tabCol['TRAITINFO']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DATPPE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9999-99-99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DCMERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DCPERE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DECAP":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DECISION":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "DEFINTEL":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "DIPLOME":
			echo $cpt."AV----------------------".$cle."<br />";
			
			if($tabCol['TYPEV'] != "3" && $val != ""){
				echo $cpt."VariablesNonAttendues----------------------".$cle."<br />";
				VariablesNonAttendues($cle,$val,"cette variable n'est à renseigner que pour les fins de mesure");
			}else{
				 
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9" ){	
						echo $cpt."Else----------------------".$cle."<br />";					
						VariablesPertinentes($cle);
						echo $cpt."AP----------------------".$val."<br />";
					}
				}
			}

			break;		
		
		
		case "EMPLA1":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "EMPLA2":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "EMPLJM":
			if(!$majeur  && $val != ""){
				VariablesReserveesMajeurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		
		
		case "ENQPENAL":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "ETABSCOSPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "FINEVAL":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "FREQSCO":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "HANDICAP":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "INSTITPLAC":
			if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18" && $tabCol['NATDECASSED'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED = ".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "INTERANT":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val =="1" || $val =="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "LIENA1":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "LIENA2":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle, $val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;		
				
		
		case "LIEUACC":
			if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATPDECADM =".$tabCol['NATPDECADM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "LIEUPLAC":
			if( $tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "MENAGEJM":
			if(!$majeur  && $val != ""){
				VariablesReserveesMajeurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		
		case "MEREINC":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "MESANT":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		case "MINA":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" )
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		
		case "MINIMA":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "MNAIS":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "MODACC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "MORALITE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "MOTFININT":
			if($tabCol['TYPEV'] != "3" && $val != "" ){
				VariablesNonAttendues($cle,$val,"TYPEV =".$tabCol['TYPEV']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val =="3")
						VariablesPertinentes($cle);
					
				}
			}
			break;		
		
		
		case "MOTIFML":
			if($tabCol['MOTFININT'] != "1" && $tabCol['MOTFININT'] != "2" && $val != "" ){
				VariablesNonAttendues($cle,$val, "MOTFININT=".$tabCol['MOTFININT']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" ){
						VariablesPertinentes($cle);
						//echo "valeur '".$cle."' : |".$val."| <br />";
					}
				}
			}
			break;		
		
		
		case "MOTIFSIG":
			if($tabCol['TRAITINFO'] != "3"  && $val != ""){
				VariablesNonAttendues($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val == "3" || $val == "4")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NATDECASSED":
			if($tabCol['DECISION'] != "2"  && $val != ""){
				VariablesNonAttendues($cle,$val,"DECISION =".$tabCol['DECISION']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NATDECPLAC":
			if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18" && $tabCol['NATDECASSED'] != "21"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NATNOUVDECPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val=="3" || $val=="4")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NATPDECADM":
			if($tabCol['DECISION'] != 1  && $val != ""){
				VariablesNonAttendues($cle,$val,"DECISION =".$tabCol['DECISION'] );
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "NBCHGLIEU":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else if($tabCol['TYPEV'] != "3" && $val != ""){
				VariablesNonAttendues($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;	
		
		case "NBFRAT":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="0" && $val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NBPER":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val <2)
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NEGLIG":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NIVSCO":
			if($tabCol['SCODTCOM'] != 2  && $val != ""){
				VariablesNonAttendues($cle,$val, " SCODTCOM =".$tabCol['SCODTCOM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="999")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "NONSCO":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="999")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NOTIFEVAL":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NOUVDECPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NUMANONYM":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NUMANONYMANT":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NUMDEP":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				$numDept=$val; // on ne conservera que la derni貥 valeur
				if(iconv_strlen($val)<2){
					$flagNUMDEPT = true;
					echo $cptErreur++.": codification '".$cle."' non conforme '".$val."' ->".iconv_strlen($val)."<br />";
					$numDept="0".$val;
				}
				VariablesPertinentes($cle);
			}
			break;		
		
		
		case "ORIGIP":
			if($tabCol['TRAITINFO'] != "1" && $tabCol['TRAITINFO'] != "2"  && $tabCol['TRANSIP']=="" && $val != "" &&  false){  // traitement des incohérences à définir
				VariablesNonAttendues($cle,$val,"TRAITINFO =".$tabCol['TRAITINFO']." et TRANSIP vide " );
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "ORIENTDEC":
			
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;		
		
		case "ORIENTEFF":
			if($tabCol['ORIENTDEC'] != 2  && $val != ""){
				VariablesNonAttendues($cle,$val, "ORIENTDEC =".$tabCol['ORIENTDEC'] );
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="9")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "PEREINC":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "PLACMOD":
			if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "PROJET":
			
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
				//echo "PROJET"." et jeune majeur :". $val ."<br />";
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "RESMENAG":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val =="4" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "REVTRAV":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SAISJUR":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SANTE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "SCOCLASPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SCODTCOM":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SECURITE":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "SEXA1":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "SEXA2":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "SEXAUT1":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SEXAUT2":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SEXE":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SIGNMIN":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SIGNPAR":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SITAPML":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val =="4" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SOUTSOC":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "STATOCLOG":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val =="4" || $val == "5" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITEVAL":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITSIGJE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITSIGNCG":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITSIGOPP":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "SUITSIGSS":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "TITAP":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "TRAITINFO":
		
			if($tabCol['TYPEV'] != 1  && $val != ""){
				VariablesNonAttendues($cle,$val, "TYPEV =".$tabCol['TYPEV']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;
		case "TRANSIP":
			if($tabCol['TRAITINFO'] != "1" && $tabCol['TRAITINFO'] != "2"  && $val != "" && false){    // A traiter ultérieurement
				VariablesNonAttendues($cle,$val, "TRAITINFO=".$tabCol['TRAITINFO']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="999")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "TYPCLASSPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "TYPDECJUD":
			if($tabCol['NATDECASSED'] != "11" && $tabCol['NATDECASSED'] != "14" && $tabCol['NATDECASSED'] != "15" && $tabCol['NATDECASSED'] != "16"  && $tabCol['NATDECASSED'] != "19" && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "24"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "TYPETABSPE":
			if($tabCol['SCOCLASPE'] != "2"  && $val != ""){
				VariablesNonAttendues($cle,$val, "SCOCLASPE =".$tabCol['SCOCLASPE']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="999")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "TYPINTERDOM":
			if($tabCol['NATPDECADM'] != "10" && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "20"  && $val != ""){
				VariablesNonAttendues($cle,$val,"NATPDECADM =".$tabCol['NATPDECADM']);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val !="99" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "TYPEV":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val =="1" || $val =="2" || $val =="3" )
					VariablesPertinentes($cle);
			}
			break;		
		
		case "TYPINTERV":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" )
					VariablesPertinentes($cle);
			}
			break;		
		
		
		
		
		case "VIOLCONJ":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" )
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		case "VIOLFAM":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "VIOLFAMPHYS":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "VIOLPERS":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val == "4")
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "VIOLPHYS":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val =="3" || $val == "4")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "VIOLPSY":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
		
		case "VIOLSEX":
			if($majeur  && $val != ""){
				VariablesReserveesMineurs($cle,$val);
			}else{
				if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
				}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2" || $val =="3" || $val == "4")
						VariablesPertinentes($cle);
				}
			}
			break;		
		
			
	
		
	
	
		default :
				break;
	
	
	}
	
	$tempo2 += Conso($tempo2,0);
	return $erreurDetectee;
	
}



function convertSchema(){ // cette fonction converti les clé/valeurs en vigeur avant le decret de 2016
	global $cle, $val;
	global $tempo1;
	
	$res = Conso($tempo1,1);
	
	switch ($cle) {
		case "DATEDECMDPH":
		case "DATEXDECMDPH":
		case "SUITSIGNCG":
		case "SUITSIGOPP":
		case "SUITSIGJE":
		case "SUITSIGSS":
		case "DATAVIS":
		case "NBPER":
		case "CONTMERE":
		case "CONTPERE":
		case "RESMENAG":
		case "REVTRAV":
		case "AUTRE":
		case "SUITEVAL":
		case "LIENAUT1":
		case "LIENAUT2":
		case "SEXAUT1":
		case "SEXEAUT2":
		case "MINAUT1":
		case "MINAUT2":
		case "SIGNPAR":
		case "SIGNMIN":
		case "STATOCLOG":
		case "NATNOUVDECPE":
		case "NOUVDECPE":
		case "TYPINTERV":
		case "NONSCO": 
		case "VIOLFAMPHYS":
		case "SITAPML": 
			$cle = "SUPP";
			break;
		case "DATSIGNPROJ": 
			$cle = "DATPPE";
			break;
			
		case "DATDEBAD": 
		case "DATDEBACC": 
		case "DATDEBINTER": 
		case "DATDEBPLAC": 
			if($val !="9999-99-99" && $val !="")
				$cle = "DATDEB";
			/*
			if($val !="")
				echo "DATDEB ".$val."*******************************"; 
			*/
			break;
		case "DATFINAD": 
		case "DATFINACC": 
		case "DATFININTER": 
		case "DATFINPLAC": 
			if($val !="9999-99-99" && $val !="")
			$cle = "DATFIN";
			break;

		case "CODEV": // cas particulier des lignes qui ne correspondent plus au périmètre d'observation
			//echo "CODEV -------------------------------".$val;
			$cle="TYPEV";
			$val .='X';  // Pour traitement dans avant controle cohérence et éviter surcharge quand TYPEV  sera présent;
			break;
		
		case "EMPLA1":
		case "EMPLA2":
			switch($val) {
				case "10":
					$val='6';
					break;
				case "11":
					$val='7';
					break;
				case "12":
					$val='5';
					break;
				case "13":
					$val='3'; // ou 4 à valider
					break;
				case "40":
					$val='';
					break;
				default:
			}
			break;
		
		case "FREQSCO":
			switch($val) {
				case "1":
					$val='';
					break;
				case "2":
					$val='6';
					break;
				case "3":
					$val='8';
					break;
				case "4":
					$val='8';
					break;
				case "5":
					$val='7';
					break;
			}
			break;
		 
		case "LIEUPLAC":
			$modalitesAbandonnes = array('7','14','15','16','17','18');
			if(in_array($val,$modalitesAbandonnes))
				$val='';
			break;
		case "LIEUACC":
			if($val=='15')
				$val='';
			break;
		case "NATDECPLAC":
			if($val=='3')
				$val='';
			break;
		case "NATDECASSED":
			$modalitesAbandonnes = array('10','12','13','20');
			if(in_array($val,$modalitesAbandonnes))
				$val='';
			break;
		case "NATPDECADM":
			if($val=='17')
				$val='';
			break;
		case "NIVSCO":
			switch($val) {
				case "360":
					$val='399';
					break;
				case "450":
					$val='499';
					break;
				case "570":
					$val='599';
					break;
				case "640":
					$val='699';
					break;
			}
			break;
		case "NUMDEP":
			if($val=='69')
				$val='69D';
			break;
		case "MOTIFML":
			if($val=='17')
				$val='';
			break;
		case "TYPDECJUD":
			$modalitesAbandonnes = array('1','2','11');
			if(in_array($val,$modalitesAbandonnes))
				$val='';
			break;
		case "TYPCLASSPE":
			switch($val) {
				case "1":
					$val='12';
					break;
				case "2":
					$val='12';
					break;
				case "3":
					$val='12';
					break;
				case "4":
					$val='13';
					break;
				case "5":
					$val='15';
					break;
				case "6":
					$val='15';
					break;
				case "9":
					$val='99';
					break;
				default:
			}
			break;
		
		case "TYPETABSPE":
			switch($val) {
				case "810":
					$val='890';
					break;
				case "820":
					$val='890';
					break;
				case "830":
					$val='890';
					break;
				case "840":
					$val='890';
					break;
	
				default:
			}
			break;
		
		
		case "TYPINTERDOM":
			if($val=='8')
				$val='';
			break;
		case "TITAP":
			if($val ==20 || $val == 10)
				$val=11;
			break;
		
		case "VIOLPERS":
			if($val=='2' || $val=='3' || $val=='4'){
				$cle='VIOLFAM';
				$val='2';
			}else if($val=='1'){
				$cle = "VIOLCONJ";
				$val='2';
			}
			break;
		
		case "VIOLPHYS":
		case "VIOLSEX":

			if($val=='2' || $val=='3' || $val=='4'){
				$val='2';
			}
			break;
		
		default : // rien clé et valeurs non modifiées

	}
	$tempo1 += Conso($tempo1,0);
}

 

function Majeur($dateNaissance, $dateRef ){
	// Retourne vrai ou faux
	if($dateNaissance =="" || $dateRef =="" || substr($dateNaissance,0,1) == "9" || substr($dateRef,0,1) =="9" || substr($dateRef,0,1) =="N")  // N comme NULL
		return false;
	
	if(substr($dateNaissance,5,2) == "99" || substr($dateRef,5,2) == "99")
		return false;
	
	/*
	$date1=date_create($dateNaissance);
	$date2=date_create($dateRef);
	*/
	$date1 = new DateTime($dateNaissance);
	$date2 = new DateTime($dateRef);
	/*
	var_dump ($date1);
	var_dump ($date2);
	echo "<br />";
	echo "date1 :".$date1->date." date2 :".$date2->date ."->".$dateNaissance." | ".$dateRef. "<br />";
	echo "----------------------------------------------<br />";
	*/
	$diff=date_diff($date1,$date2);
	//var_dump($diff);
	//echo $diff->y ;
	if( $diff->y >=18 && $diff->invert ==0 )
		return true;
	else
		return false;

}

function VariablesRenseignees(){
	global $cle, $val, $nbVariablesRenseignees,$tabColNSP, $tabColType;
	
	
	if($val !="" && $val != $tabColNSP[$cle] && $tabColType[$cle] != "date"  ){	
		$nbVariablesRenseignees[$cle] =(isset($nbVariablesRenseignees[$cle])) ? $nbVariablesRenseignees[$cle]+1 : 1;
	}else if ( $val !="9999-99-99" && $val != "0000-00-00" && $tabColType[$cle] == "date" ) {
		$nbVariablesRenseignees[$cle] =(isset($nbVariablesRenseignees[$cle])) ? $nbVariablesRenseignees[$cle]+1 : 1;
	}
}


function VariablesPertinentes($cle){
	global $nbVariablesPertinentes, $tabCol;
	$nbVariablesPertinentes[$cle] =(isset($nbVariablesPertinentes[$cle])) ? $nbVariablesPertinentes[$cle]+1 : 1;
	//$tabVariablesPertinentes[$cle][]=$tabAnonym['NUMANONYM']."|".$tabCol['NUMANONYM']."->"." valeur saisie pour la variable ".$cle.": ".$val ." ".$motif;
	if($cle=="DIPLOME")
		echo $nbVariablesPertinentes[$cle];
}

function DatesFormatErrone($cle,$val,$motif=""){
	global $nbDatesFormatErrone, $tabDatesFormatErrone, $tabCol,$tabAnonym;
	$nbDatesFormatErrone[$cle] =(isset($nbDatesFormatErrone[$cle])) ? $nbDatesFormatErrone[$cle]+1 : 1;
	//echo $cptErreur++.": pour '".$cle."' non-conforme : ".$tabCol[$cle]."  -> ".$motif."<br />";	
	//echo $tabAnonym['NUMANONYM']."| ".$cle.": ".$val ."<br />";	
	$tabDatesFormatErrone[$cle][]=$tabAnonym['NUMANONYM']."| ".$cle.": ".$val." ".$motif ;
}
function VariablesNonAttendues($cle,$val, $motif =""){
	global $nbVariablesNonAttendues, $tabVariablesNonAttendues, $tabCol, $tabAnonym;
	$nbVariablesNonAttendues[$cle] =(isset($nbVariablesNonAttendues[$cle])) ? $nbVariablesNonAttendues[$cle]+1 : 1;
	
	//if($tabAnonym['NUMANONYM'] == "51B31F8AADA802678C0E5E3B10FADCF81F32E780")
	//$tabVariablesNonAttendues[$cle][]=$tabAnonym['NUMANONYM']."|".$tabCol['NUMANONYM']."->"." valeur saisie pour la variable ".$cle.": ".$val ." ".$motif;
	
	// si la fin de $motif est un signe = alors il faut ajouter non-défini.
	if( $motif != ""){
		if(strpos(trim($motif),"=",1) == (strlen(trim($motif))-1))
			$tabVariablesNonAttendues[$cle][]=$tabAnonym['NUMANONYM']."| ".$cle.": ".$val ." alors que ".$motif." indéfini";
		else
			$tabVariablesNonAttendues[$cle][]=$tabAnonym['NUMANONYM']."| ".$cle.": ".$val ." alors que ".$motif;
	}else{
		$tabVariablesNonAttendues[$cle][]=$tabAnonym['NUMANONYM']."| ".$cle.": ".$val ;
	}
}
function VariablesReserveesMajeurs($cle,$val, $motif =""){
	global $nbVariablesReserveesMajeurs, $tabVariablesReserveesMajeurs, $tabCol , $tabAnonym;
	$nbVariablesReserveesMajeurs[$cle] =(isset($nbVariablesReserveesMajeurs[$cle])) ? $nbVariablesReserveesMajeurs[$cle]+1 : 1;
	$tabVariablesReserveesMajeurs[$cle][]=$tabAnonym['NUMANONYM']."| ".$cle.": ".$val ." est réservée aux majeurs exclusivement";
	
}
function VariablesReserveesMineurs($cle,$val, $motif =""){
	global $nbVariablesReserveesMineurs, $tabVariablesReserveesMineurs,$tabCol, $tabAnonym;
	$nbVariablesReserveesMineurs[$cle] =(isset($nbVariablesReserveesMineurs[$cle])) ? $nbVariablesReserveesMineurs[$cle]+1 : 1;
	$tabVariablesReserveesMineurs[$cle][]=$tabAnonym['NUMANONYM']."| ".$cle.": ".$val ." est réservée aux mineurs exclusivement";

}

function Conso($tempo,$debutChrono=true){
	global $time_start ,$time_end;
	// optimisation du code
	if($debutChrono){
		$time_start = microtime(true);
		//echo "debut :".$tempo."<br />";
		return $tempo;
	}else{
		$time_end = microtime(true);
		//echo "fin :".$tempo." +". ($time_end-$time_start)."<br />";
		return ($time_end-$time_start);			
	}
}
?>
