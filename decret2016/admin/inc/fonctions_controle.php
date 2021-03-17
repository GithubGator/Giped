<?php

function envoi_mail_chiffres ( $destinataire, $add = "", $messageType="RDD",$CD="00") {
	global $maConnection;
	//
	$headers ='From: "Informatique" <informatique@giped.gouv.fr>'."\n";
	$headers .='Reply-To: informatique@giped.gouv.fr'."\n";
	$headers .= "Return-Path: informatique@giped.gouv.fr\n";
	$headers .= "CC: informatique@giped.gouv.fr\n";

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

			Le service informatique
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
	global $cptErreur, $tabCol, $anneeConcernee,$numDept, $tabDecision, $flagNUMDEPT, $nbVariablesPertinentes, $nbVariablesPresentes, $tabVariablesAbsentes,$nbVariablesNonAttendues, $nbVariablesReserveesMajeurs, $nbModalitesReserveesMajeurs, $nbVariablesReserveesMineurs, $nbModalitesReserveesMineurs, $majeur;
	global $tempo2, $cptFrat;	
	$erreurDetectee = "";
	//echo "En entree : " .$cle."->".$val."<br />";
	$res = Conso($tempo2,1);
	
	
	// prétraitement de la variable TYPEV
	
	VariablesPresentes($cle, $val);	
	VariablesRenseignees($cle, $val);
	
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"){
						VariablesNonAttendues($cle,$val,"NATPDECADM = ".$tabCol['NATPDECADM']);
					}
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
				if($val !="9999"){
					VariablesPertinentes($cle);
					if( ($val < $tabCol['ANSA1'] && $tabCol['ANSA1']!= '9999')  ||  ($val < $tabCol['ANSA2'] && $tabCol['ANSA2']!= '9999')  || ($val < $tabCol['ANSMERE'] && $tabCol['ANSMERE']!= '9999') || ($val < $tabCol['ANSPERE'] && $tabCol['ANSPERE']!= '9999')  )
						$erreurDetectee .= $cptErreur++." : valeur '".$cle."'->". $val." ant鲩eure aux adultes (".  $tabCol['ANSA1'] ." | ". $tabCol['ANSA2']." | ".  $tabCol['ANSMERE']." | ". $tabCol['ANSPERE'].")<br />";
	
				}
			}
	
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}	
			}
			break;		
		
		
		case "ANSPERE":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				VariablesPertinentes($cle);
				if($tabCol['DECISION'] != 1 ){
					VariablesNonAttendues($cle,$val,"DECISION = ".$tabCol['DECISION']);
				}
			}
			break;		
		
		
		case "AUTREDJ":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				VariablesPertinentes($cle);
				if($tabCol['DECISION'] != 2){
					VariablesNonAttendues($cle,$val,"DECISION = ".$tabCol['DECISION']);
				}
			}
			break;		
		
		
		case "AUTREHEBER":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "AUTRLIEUACC":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"){
						VariablesNonAttendues($cle,$val,"NATPDECADM = ".$tabCol['NATPDECADM']);
					}
				}
			}
			break;		
		
		
		case "AUTRLIEUAR":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"){
						VariablesNonAttendues($cle,$val,"NATDECASSED = ".$tabCol['NATDECASSED']);
					}
				
				}
			}
			break;		
		
		
		case "CHGLIEU":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle, $val);
					}else if($tabCol['TYPEV'] != "2"){
						VariablesNonAttendues($cle,$val, "TYPEV different de 2" );
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val=="3" ){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "CONDEDEV":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle, $val);
					}
				}
			}
			break;		
		
		
		case "CONDEDUC":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "CONFL":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				if($val !="99"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;
		
		case "CSPA1":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				if($val !="9"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "CSPA2":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				if($val !="9"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		case "CSPJM":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				if($val !="9"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;	
		
		case "DANGER":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" ){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "DATDCPERE":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
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
			if($val==""){
				if($tabCol['DECAP'] != ''){
					$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car DECAP est pr鳥nt<br />";
				}
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99"){
					VariablesPertinentes($cle);
					if($tabCol['MOTIFML'] != "18"){
						VariablesNonAttendues($cle,$val,"MOTIFML = ".$tabCol['MOTIFML']);
					}
				}
			}
			break;		
		
		
		case "DATDECPE":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
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
					
		case "DATFIN":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99"){
					VariablesPertinentes($cle);
					if($tabCol['TYPEV'] != "3"){
						VariablesNonAttendues($cle,$val,"TYPEV = ".$tabCol['TYPEV']);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99"){
					VariablesPertinentes($cle);
					if($tabCol['TRAITINFO'] != 4){
						if($tabCol['TRAITINFO'] =="")
							VariablesNonAttendues($cle,$val,"TRAITINFO n'est pas renseigné" );
						else
							VariablesNonAttendues($cle,$val,"TRAITINFO = ".$tabCol['TRAITINFO']);
					}
				}
			}
			break;		
		
		
		case "DATSIGN":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99"){
					VariablesPertinentes($cle);
					if($tabCol['TRAITINFO'] != 3  && $val != ""){ // contrainte à traiter ultérieurement
						VariablesNonAttendues($cle,$val,"TRAITINFO =".$tabCol['TRAITINFO']);
					}
				}
			}
			break;		
			
		case "DATPPE":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9999-99-99"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
					if($tabCol['PROJET'] != 2 ){ // contrainte à traiter ultérieurement
						VariablesNonAttendues($cle,$val,"PROJET = ".$tabCol['PROJET']);
					}
				
				}
			}
			break;		
		
		
		case "DCMERE":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "DCPERE":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		case "DECAP":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9" ){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "DIPLOME":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9" ){					
					VariablesPertinentes($cle);
					if($tabCol['TYPEV'] != "3"){
						VariablesNonAttendues($cle,$val,"cette variable n'est à renseigner que pour les fins de mesure");
					}
				}
			}
				break;		
		
		
		case "EMPLA1":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "EMPLA2":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;				
		
		case "EMPLJM":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9" ){
					VariablesPertinentes($cle);
					if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18" && $tabCol['NATDECASSED'] != "21"){
						VariablesNonAttendues($cle,$val,"NATDECASSED = ".$tabCol['NATDECASSED']);
					}
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
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "LIENA2":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
				
		
		case "LIEUACC":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($tabCol['NATPDECADM'] != "11" && $tabCol['NATPDECADM'] != "12" && $tabCol['NATPDECADM'] != "13"  && $tabCol['NATPDECADM'] != "14" && $tabCol['NATPDECADM'] != "15" && $tabCol['NATPDECADM'] != "16"  && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "19" && $tabCol['NATPDECADM'] != "21"){
						VariablesNonAttendues($cle,$val,"NATPDECADM =".$tabCol['NATPDECADM']);
					}
				}
			}
			break;		
		
		
		case "LIEUPLAC":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if( $tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"){
						VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
					}
				}
			}
			break;		
		
		case "MENAGEJM":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9"){
					VariablesPertinentes($cle);
					if(!$majeur){
						VariablesReserveesMajeurs($cle,$val);
					}
				}
			}
			break;	
		
		
		case "MEREINC":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" ){
					VariablesPertinentes($cle);
					if($majeur  && $val != ""){
						VariablesReserveesMineurs($cle,$val);
					}
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
				if($val !="9" ){
					VariablesPertinentes($cle);
					if($tabCol['SCODTCOM'] != "1" ){
						VariablesNonAttendues($cle,$val, " Réservé aux mineurs non scolarisés");
					}

				}
			}
			break;		
		
		
		case "MORALITE":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
			
		case "MOTFININT":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3"){
					VariablesPertinentes($cle);
					if($tabCol['TYPEV'] != "3"){
						VariablesNonAttendues($cle,$val,"TYPEV =".$tabCol['TYPEV']);
					}
				}
			}
			break;		
		
		
		case "MOTIFML":
			if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					//echo "valeur '".$cle."' : |".$val."| <br />";
								
					if($tabCol['MOTFININT'] != "1" && $tabCol['MOTFININT'] != "2" ){
						VariablesNonAttendues($cle,$val, "MOTFININT=".$tabCol['MOTFININT']);
					}
				}	
			}
			break;		
		
		
		case "MOTIFSIG":
			if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			
			
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val == "3" || $val == "4"){
					VariablesPertinentes($cle);
					if($tabCol['TRAITINFO'] != "3")	
						VariablesNonAttendues($cle,$val);
						
				}
			
			
			}
			break;		
		
		
		case "NATDECASSED":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($tabCol['DECISION'] != "2" )
						VariablesNonAttendues($cle,$val,"DECISION =".$tabCol['DECISION']);
			
				}
				if(!$majeur  && $val=="24"){
						ModalitesReserveesMajeurs($cle,$val);
				}
				if($majeur &&  $val !="21" &&  $val !="24"){
						ModalitesReserveesMineurs($cle,$val);
				}
				
			}
				
			break;		
		
		
		case "NATDECPLAC":
			if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
					if($val=="1" || $val=="2"){
						VariablesPertinentes($cle);
						if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18" && $tabCol['NATDECASSED'] != "21" ){
							VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
						}
					
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
			if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99"){
					VariablesPertinentes($cle);
					if($tabCol['DECISION'] != 1){
						VariablesNonAttendues($cle,$val,"DECISION =".$tabCol['DECISION'] );
					}
					
					
				}
				
				if(!$majeur  && ($val=="20" || $val=="21" )){
						ModalitesReserveesMajeurs($cle,$val);
				}
				if($majeur && $val !="18" && $val !="20" &&  $val !="21" ){
						ModalitesReserveesMineurs($cle,$val);
				}
			}

			break;		
		
		case "NBCHGLIEU":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}else if($tabCol['TYPEV'] != "3"){
						VariablesNonAttendues($cle,$val);
					}	
				
				}
			}
		
			break;	
		
		case "NBFRAT":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="0" && $val !="99"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}		
			}
			break;		
		
		
		case "NBPER":
		
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val >2)
					VariablesPertinentes($cle);
			}
			break;		
		
		
		case "NEGLIG":
			if($val==""){
					//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}

			break;		
		
		
		case "NIVSCO":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="999"){
					VariablesPertinentes($cle);
					if($tabCol['SCODTCOM'] != 2){
						VariablesNonAttendues($cle,$val, " SCODTCOM =".$tabCol['SCODTCOM']);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9"){
					VariablesPertinentes($cle);
					if($tabCol['ORIENTDEC'] != 2 ){
						VariablesNonAttendues($cle,$val, "ORIENTDEC =".$tabCol['ORIENTDEC'] );
					}
				}
			}

			break;		
		
		case "PEREINC":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur  && $val != ""){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
		
			break;		
		
		
		case "PLACMOD":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($tabCol['NATDECASSED'] != "17" && $tabCol['NATDECASSED'] != "18"  && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "22" && $tabCol['NATDECASSED'] != "23"){
						VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
					}
				
				}
			}
			break;		
		
		
		case "PROJET":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur ){
						VariablesReserveesMineurs($cle,$val);
					//echo "PROJET"." et jeune majeur :". $val ."<br />";
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "SCOCLASPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($tabCol['SCODTCOM'] != "2" ){
						VariablesNonAttendues($cle,$val, " Réservé aux mineurs/majeurs scolarisés en milieu ordinaire");
					}
					
				}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "SEXA1":
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "SEXA2":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
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
			if($val==""){
				$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		case "TRAITINFO":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($tabCol['TYPEV'] != 1){
						VariablesNonAttendues($cle,$val, "TYPEV =".$tabCol['TYPEV']);
					}
				}
			}
			break;
		
		case "TRANSIP":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="999"){
					VariablesPertinentes($cle);
					if($tabCol['TRAITINFO'] != "1" && $tabCol['TRAITINFO'] != "2"){    // A traiter ultérieurement
						VariablesNonAttendues($cle,$val, "TRAITINFO=".$tabCol['TRAITINFO']);
					}
				}
			}
			break;		
		
		
		case "TYPCLASSPE":
		
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="9"){
					VariablesPertinentes($cle);
					if($tabCol['SCOCLASPE'] != "2" ){
						VariablesNonAttendues($cle,$val, "mineur non scolarisé avec un dispositif spécifique");
					}
				
				
				}
			}
			break;		
		
		
		case "TYPDECJUD":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($tabCol['NATDECASSED'] != "11" && $tabCol['NATDECASSED'] != "14" && $tabCol['NATDECASSED'] != "15" && $tabCol['NATDECASSED'] != "16"  && $tabCol['NATDECASSED'] != "19" && $tabCol['NATDECASSED'] != "21" && $tabCol['NATDECASSED'] != "24"){
						VariablesNonAttendues($cle,$val,"NATDECASSED =".$tabCol['NATDECASSED']);
					}
				}
			}
			break;		
		
		
		case "TYPETABSPE":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="999"){
					VariablesPertinentes($cle);
					if($tabCol['SCOCLASPE'] != "2"){
						VariablesNonAttendues($cle,$val, "SCOCLASPE =".$tabCol['SCOCLASPE']);
					}
				}
			}
			break;		
		
		
		case "TYPINTERDOM":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val !="99" ){
					VariablesPertinentes($cle);
					if($tabCol['NATPDECADM'] != "10" && $tabCol['NATPDECADM'] != "18" && $tabCol['NATPDECADM'] != "20"){
						VariablesNonAttendues($cle,$val,"NATPDECADM =".$tabCol['NATPDECADM']);
					}
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
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" ){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		case "VIOLFAM":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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
				if($val=="1" || $val=="2" || $val =="3" || $val == "4"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				
				}
			}
			break;		
		
		
		case "VIOLPHYS":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val == "4"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "VIOLPSY":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
				}
			}
			break;		
		
		
		case "VIOLSEX":
			if($val==""){
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
			}else{
				//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
				if($val=="1" || $val=="2" || $val =="3" || $val == "4"){
					VariablesPertinentes($cle);
					if($majeur){
						VariablesReserveesMineurs($cle,$val);
					}
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

function ageDecision($dateNaissance, $dateRef ){
	// Retourne l'age en année
	if($dateNaissance =="" || $dateRef =="" || substr($dateNaissance,0,1) == "9" || substr($dateRef,0,1) =="9" || substr($dateRef,0,1) =="N")  // N comme NULL
		return 0;
	
	if(substr($dateNaissance,5,2) == "99" || substr($dateRef,5,2) == "99")
		return 0;
	
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
	
	return  $diff->y;

}

function VariablesPresentes($cle, $val){
	global  $nbVariablesPresentes,$tabColNSP, $nbVariablesNonVides, $tabColType;
	if($val !="NULL")
		$nbVariablesPresentes[$cle] =(isset($nbVariablesPresentes[$cle])) ? $nbVariablesPresentes[$cle]+1 : 1;
	if(trim($val) !="")
		$nbVariablesNonVides[$cle] =(isset($nbVariablesNonVides[$cle])) ? $nbVariablesNonVides[$cle]+1 : 1;

}



function VariablesRenseignees($cle, $val){
	global  $nbVariablesRenseignees,$tabColNSP, $tabColType;
	
	
	if($val !="" && $val != $tabColNSP[$cle] && $tabColType[$cle] != "date"  ){	
		$nbVariablesRenseignees[$cle] =(isset($nbVariablesRenseignees[$cle])) ? $nbVariablesRenseignees[$cle]+1 : 1;
	}else if ( $val !="9999-99-99" && $val != "0000-00-00" && $tabColType[$cle] == "date" ) {
		$nbVariablesRenseignees[$cle] =(isset($nbVariablesRenseignees[$cle])) ? $nbVariablesRenseignees[$cle]+1 : 1;
	}
}


function VariablesPertinentes($cle){
	global $nbVariablesPertinentes, $tabCol;
	$nbVariablesPertinentes[$cle] =(isset($nbVariablesPertinentes[$cle])) ? $nbVariablesPertinentes[$cle]+1 : 1;
	
}

function DatesFormatErrone($cle,$val,$motif=""){
	global $nbDatesFormatErrone, $tabDatesFormatErrone, $tabCol,$tabAnonym,$cptErreur;
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

function ModalitesReserveesMajeurs($cle,$val, $motif =""){
	global $nbModalitesReserveesMajeurs, $tabModalitesReserveesMajeurs, $tabCol , $tabAnonym;
	$nbModalitesReserveesMajeurs[$cle] =(isset($nbModalitesReserveesMajeurs[$cle])) ? $nbModalitesReserveesMajeurs[$cle]+1 : 1;
	$tabModalitesReserveesMajeurs[$cle][]=$tabAnonym['NUMANONYM']."| ".$cle.": ".$val ." modalité spécifique aux majeurs";
	
}
function ModalitesReserveesMineurs($cle,$val, $motif =""){
	global $nbModalitesReserveesMineurs, $tabModalitesReserveesMineurs,$tabCol, $tabAnonym;
	$nbModalitesReserveesMineurs[$cle] =(isset($nbModalitesReserveesMineurs[$cle])) ? $nbModalitesReserveesMineurs[$cle]+1 : 1;
	$tabModalitesReserveesMineurs[$cle][]=$tabAnonym['NUMANONYM']."| ".$cle.": ".$val ." modalité spécifique aux mineurs";

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

function messageCle($cle){
	// retourne un message générique pour les variables non renseignées
	global $listeVariablesAbsentes,$listeVariablesVides,$listeVariablesToujoursNSP;
	
	if(in_array($cle,$listeVariablesAbsentes)){
		return("La variable ".$cle." étant absente du fichier, il n'est pas possible d'effectuer cette analyse.");
	}
	
	if(in_array($cle,$listeVariablesVides)){
		return("La variable ".$cle." étant toujours vide dans ce fichier, il n'est pas possible d'effectuer cette analyse.");
	}
	
	if(in_array($cle,$listeVariablesToujoursNSP)){
		return("La variable ".$cle." étant systématiquement codifiée en \"Ne sait pas\", il n'est pas possible d'effectuer cette analyse.");
	}	
	//sinon
	return("");
}
				
function messageEnLien($cle){
	// retourne un message générique pour alerter sur les anomalies rencontrées lors de test sur des variables non renseignées
	global $listeVariablesAbsentes,$listeVariablesVides,$listeVariablesToujoursNSP;
	
	if(in_array($cle,$listeVariablesAbsentes)){
		return("Les anomalies rencontrées sont en lien avec le fait que cette variable ".$cle." est absente du fichier.");
	}
	
	if(in_array($cle,$listeVariablesVides)){
		return("Les anomalies rencontrées sont en lien avec le fait que cette variable ".$cle." est systématiquement vide dans ce fichier.");
	}
	
	if(in_array($cle,$listeVariablesToujoursNSP)){
		return("Les anomalies rencontrées sont en lien avec le fait que cette variable ".$cle." est systématiquement codifiée en \"Ne sait pas\".");
	}	
	//sinon
	return("");
}

?>
