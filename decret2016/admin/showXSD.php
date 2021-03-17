<?php
	/******************************************************************************************************/
	/* Ce script génère le schéma xsd à partir de la table NOMENCLATURE et des tables de référence associées    */
	/*                                                                                                                                                                                                    */
	/******************************************************************************************************/
	
	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // Paramètres de connection et... connection
	require_once("../_fonctions.php"); 


	header("Content-type: text/html; charset=utf-8");
	setlocale(LC_ALL, 'fr_FR.utf8');
	//error_reporting(E_ERROR | E_PARSE);
	error_reporting(E_ALL);
	import_request_variables("GP");
	//
	
	// Période concernée
	$debutExport = date("Y",strtotime("-1 year"))."-01-01"; 
	$finExport =  date("Y")."-05-01"; 

	$reqUpdate="";
	$reqSelect="";
	$reqSelectOne="";
	$reqInsert="";
	$bufID="";
	

	$i=0;
	
	$xsd = '<?xml version="1.0" encoding="utf-8" ?>
<xs:schema attributeFormDefault="unqualified" elementFormDefault="qualified" xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="DECRET">
    <xs:complexType>
      <xs:sequence>
	<xs:element name="INTRO">
    	   <xs:complexType>
      	     <xs:sequence>
                <xs:element maxOccurs="1" minOccurs="1" name="VERSIONXSD">
                   <!--Version du schema xsd -->
                   <xs:simpleType>
                      <xs:restriction base="xs:string">
                        <xs:pattern value="2.0"/>
                      </xs:restriction>
                   </xs:simpleType>
                </xs:element>
        	<xs:element maxOccurs="1" minOccurs="1" name="DATEXPORT">
                  <!--Date de generation du fichier XML -->
                  <xs:simpleType>
                     <xs:restriction base="xs:string">
		       <xs:pattern value="()|(0000-00-00)|(9999-99-99)|((19)[0-9]{2}|(20)[0-9]{2})-((0)[1-9]{1}|(1)[0-2]{1})-((0)[1-9]{1}|(1)[0-9]{1}|(2)[0-9]{1}|(3)[0,1]{1})"/>
                     </xs:restriction>
                  </xs:simpleType>
                </xs:element>
      	     </xs:sequence>
	    </xs:complexType>
	</xs:element>
	<xs:element minOccurs="0" maxOccurs="unbounded" name="DONNEE">
          <xs:complexType>
            <xs:sequence>'."\n"; 

	//$order = "numDecret"; //pour pointage après génération du schéma
	$order = "NewField";
	$reqSelect = "SELECT * FROM `NOMENCLATURE` WHERE `NewField` <>''  ";
	if(isset($order)){
		$reqSelect .= " ORDER BY ".$order;
		//echo $order; exit();
		
	} else {
		$reqSelect .= " ORDER BY `NewField`";
	}
	//$reqSelect = "SELECT ID, id_donnee FROM `donnees` ORDER BY ID ";
	
	$result = ExecRequete($reqSelect, $maConnection);
	
		
	while ($record = $result->fetch_assoc()) {
		$i++;
	
		//echo $i."->". $record['NewField']."<br>";
		
		// tous les éléments sont facultatifs sauf  NUMANONYM qui doit exister, de plus chaque élément ne peut avoir qu'une seule instance dans la sequence
		
		if($record['NewField'] == 'NUMANONYM'){
			$xsd .= "              <xs:element minOccurs=\"1\" maxOccurs=\"1\" name=\"".$record['NewField']."\"> <!--".trim($record['Lib']) ." --> \n" ;

		} else {
			$xsd .= "              <xs:element minOccurs=\"0\" maxOccurs=\"1\" name=\"".$record['NewField']."\"> <!--".trim($record['Lib']) ." --> \n" ;
		}
		
		$xsd .= "                <xs:simpleType>\n";
		
		if($record['Type'] !='date'){
		$nomTable = "ref_".$record['NewField'];
		
		/*
		              <xs:element minOccurs="0" name="JPLUSACC">
                <xs:simpleType>
                  <xs:restriction base="xs:string">
                    <xs:enumeration value="" />
                    <xs:enumeration value="0" />
                    <xs:enumeration value="1" />
                    <xs:enumeration value="9" />
                  </xs:restriction>
                </xs:simpleType>
              </xs:element>
	      */
		
		
		if(strpos("ref_NUMANONYM;ref_NUMANONYMANT,ref_MNAIS,ref_ANAIS;ref_NBPER;ref_NBFRAT;ref_ANSMERE; ref_ANSPERE;ref_DATDCMERE; ref_DATDCPERE; ref_AUTREDA; ref_AUTREDJ; ref_DATDECMIN; ref_ANSA1; ref_ANSA2;ref_NBCHGLIEU;ref_NBENF",$nomTable) === false){
			
			$xsd .="                  <xs:restriction base=\"xs:string\">\n";
			$xsd .="                    <xs:enumeration value=\"\" />\n";
						
			$req ="SELECT * FROM $nomTable" ;		
			$res = ExecRequete($req, $maConnection);

			while ($ligne = $res->fetch_assoc()) {
				$xsd .= "                    <xs:enumeration value=\"".$ligne['id']."\" />";
				$xsd .="<!-- ".$ligne['libelle']." -->\n";
			
			}
	
		
		} else {
			//echo  "`".$record['NewField']."` ". $record['Type']." NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";
			
			switch ($record['NewField']) {
				case 'NUMANONYM':
				case 'NUMANONYMANT' :
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"[0-9a-fA-F]{40}\" />\n";	
					break;
				case 'ANAIS' :
				case 'ANSA1' :
				case 'ANSA2':
				case 'ANSMERE' :
				case 'ANSPERE' :
				        $xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"(9999)|()|(0000)|(19)[0-9]{2}|(20)[0-9]{2}\" />\n";	
					break;
				case 'MNAIS':
				case 'NBFRAT' :
				case 'NBPER':
				case 'NBENF':
				case 'NBCHGLIEU':
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"()|[0-9]{1,2}\" />\n";	
					break;
				case 'AUTREDA' :
				case 'AUTREDJ':
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:maxLength value=\"40\" />\n";
					break;
				case 'DATDCMERE' :
				case 'DATDCPERE' :
				case 'DATDECMIN':
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="			<xs:pattern value=\"()|(0000-00)|((9999)|(19)[0-9]{2}|(20)[0-9]{2})-((99)|(0)[1-9]{1}|(1)[0-2]{1})\" />\n";	
					break;
				case 'AUTRE' :
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                    <xs:enumeration value=\"\" />\n";
					$xsd .="                    <xs:enumeration value=\"1\" /><!-- OUI -->\n";
					$xsd .="                    <xs:enumeration value=\"2\" /><!-- NON -->\n";
					$xsd .="                    <xs:enumeration value=\"9\" /><!-- NE SAIT PAS -->\n";
					break;
				default :
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"[0-9]{4}\" /><! -- A modifier  -->\n";	
					echo  "`".$record['NewField']."` ". $record['Type']." NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";
					break;
			}
			
			


		}
			$xsd .="                  </xs:restriction>\n";
			$xsd .="                </xs:simpleType>\n";
			$xsd .="              </xs:element>\n";
	
		
		
		}else {
			//dates
			$xsd .="                  <xs:restriction base=\"xs:string\">\n";
			$xsd .="                   <xs:pattern value=\"()|(0000-00-00)|(9999-99-99)|((19)[0-9]{2}|(20)[0-9]{2})-((0)[1-9]{1}|(1)[0-2]{1})-((0)[1-9]{1}|(1)[0-9]{1}|(2)[0-9]{1}|(3)[0,1]{1})\" />\n";
			$xsd .="                  </xs:restriction>\n";
			$xsd .="                </xs:simpleType>\n";
			$xsd .="              </xs:element>\n";
			
		}
		
	}
	
	
	$xsd .='           </xs:sequence>
          </xs:complexType>
        </xs:element>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>';
	
	/* Champ de nomenclature
	echo "<pre>";
	echo $varNomenclature;
	echo "</pre>";
	*/

	echo $xsd;
	
	// Sauvegarde du schéma
	$xsdfile="./decret.xsd";
	$fp = fopen($xsdfile, 'w'); 
	fputs($fp, $xsd);
	fclose($fp); 	
	
?>
