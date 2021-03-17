<?php
//Tableau des scripts include, ce tableau sera parcouru 3 fois pour prioriser l'affichage par bloc, (Rouge, Orange, Vert), pour chacun l'affichage se fera en fonction de l'ordre défini par la première clé et le flagqui peut prendre les valeurs.

$tabScripts = array(
    1 => array(
	    'id' => '0001',
	    'fic' => 'presence',
            'lib' => 'Classement suivant la presence des variables',
	    'publication' => false,
            'bloc' => 'Orange',
	    
        ),

   

   2 => array(       
	    'id' => '0002',
	    'fic' => 'var_absentes',
            'lib' => 'Variables absentes',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
	
    3 => array(       
	    'id' => '0003',
	    'fic' => 'vides',
            'lib' => 'Variables vides',
	    'publication' => true,
            'bloc' => 'Orange',
        ),	

     4 => array(       
	    'id' => '0004',
	    'fic' => 'nsp',
            'lib' => 'Variables toujours codées en Ne Sais Pas',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

   5 => array(       
	    'id' => '0005',
	    'fic' => 'identifiants',
            'lib' => 'Incohérences sur le numéro d\'anonymat',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
 
	
 6 => array(
            'id' => '0006',
	    'fic' => 'pertinence',
            'lib' => ' Classement suivant la pertinence des variables',
	    'publication' => false,
            'bloc' => 'Orange',
        ),	
	
 7 => array(
            'id' => '0007',
	    'fic' => 'typev',
            'lib' => ' Répartition des évènement par type',
	    'publication' => true,
            'bloc' => 'Orange',
        ),	 
 	
 9 => array(       
	    'id' => '0009',
	    'fic' => 'dates_incorrectes',
            'lib' => 'Format des dates incorrectes',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
 10 => array(       
	    'id' => '0010',
	    'fic' => 'dates_naissances',
            'lib' => 'Dates naissances à vérifier',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
	
  11 => array(       
	    'id' => '0011',
	    'fic' => 'mineurs',
            'lib' => 'Variables réservées aux mineurs',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
 12 => array(       
	    'id' => '0012',
	    'fic' => 'majeurs',
            'lib' => 'Variables réservées aux jeunes majeurs',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
 13 => array(       
	    'id' => '0013',
	    'fic' => 'variables_non_attendues',
            'lib' => 'Variables non attendues',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
   15 => array(       
	    'id' => '0015',
	    'fic' => 'test',
            'lib' => 'Test',
	    'publication' => false,
            'bloc' => 'Orange',
        ),
    18 => array(       
	    'id' => '0018',
	    'fic' => 'doublons',
            'lib' => 'Doublons',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

    22 => array(       
	    'id' => '0022',
	    'fic' => 'doublonsAdministratifs',
            'lib' => 'Doublons administratifs',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
	
    26 => array(       
	    'id' => '0026',
	    'fic' => 'doublonsJudiciaires',
            'lib' => 'Doublons judiciaires',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

   28 => array(       
	    'id' => '0028',
	    'fic' => 'sans_DECISION',
            'lib' => 'DECISION absent',
	    'publication' => true,
            'bloc' => 'Rouge',
        ),

   30 => array(       
	    'id' => '0030',
	    'fic' => 'sans_DateDecision',
            'lib' => 'DATEDECPE absent',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

   32 => array(       
	    'id' => '0032',
	    'fic' => 'sans_DateDebut',
            'lib' => 'DATEDEB absent',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

   34 => array(       
	    'id' => '0034',
	    'fic' => 'sans_DateFin',
            'lib' => 'DATFIN absent',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

   35=> array(       
	    'id' => '0035',
	    'fic' => 'sans_MOTFININT',
            'lib' => 'MOTFININT absent et TYPEV = 3',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
     
     
     
  36 => array(       
	    'id' => '0036',
	    'fic' => 'hors_Perimetre',
            'lib' => 'Evenement hors périmètre',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

  38 => array(       
	    'id' => '0038',
	    'fic' => 'sans_NATPDECADM',
            'lib' => 'Décision administrative sans nature de la décision',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

  44 => array(       
	    'id' => '0044',
	    'fic' => 'sans_TYPINTERDOM',
            'lib' => 'Aide à domicile-type d\'intervention.',
	    'publication' => true,
            'bloc' => 'Orange',
        ),	
	
  45 => array(       
	    'id' => '0045',
	    'fic' => 'sans_LIEUACC',
            'lib' => 'Accueil provisoire, principal lieu d\'accueil',
	    'publication' => true,
            'bloc' => 'Orange',
        ),		
	
  46 => array(       
	    'id' => '0046',
	    'fic' => 'sans_ACCMOD',
            'lib' => 'Décision administrative, accueil provisoire, accueil modulable',
	    'publication' => true,
            'bloc' => 'Orange',
        ),	
	
	
  47 => array(       
	    'id' => '0047',
	    'fic' => 'sans_AUTRLIEUACC',
            'lib' => 'Accueil provisoire, autre lieu d\'accueil',
	    'publication' => true,
            'bloc' => 'Orange',
        ),	

   48 => array(       
	    'id' => '0048',
	    'fic' => 'sans_NATDECASSED',
            'lib' => 'Décision judiciaire sans nature de la décision',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
 
    49 => array(       
	    'id' => '0049',
	    'fic' => 'sans_TYPDECJUD',
            'lib' => 'MJE ou AEMO',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
	
   50=> array(       
	    'id' => '0050',
	    'fic' => 'sans_INSTITPLAC',
            'lib' => 'si placement judiciaire, a qui le mineur est-il confié.',
	    'publication' => true,
            'bloc' => 'Orange',
        ),	
	
   51 => array(       
	    'id' => '0051',
	    'fic' => 'sans_NATDECPLAC',
            'lib' => 'si placement judiciaire, nature du placement',
	    'publication' => true,
            'bloc' => 'Orange',
        ),


	

  52 => array(       
	    'id' => '0052',
	    'fic' => 'sans_LIEUPLAC',
            'lib' => 'Placement judiciaire -  principal lieu d\'accueil',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

   53 => array(       
	    'id' => '0053',
	    'fic' => 'sans_PLACMOD',
            'lib' => 'Placement judiciaire -  caractère modulable de l\'accueil',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
  54 => array(       
	    'id' => '0054',
	    'fic' => 'sans_AUTRLIEUAR',
            'lib' => 'Placement judiciaire -  autre lieu d\'accueil régulier ',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
   
   56 => array(       
	    'id' => '0056',
	    'fic' => 'sans_CHGLIEU',
            'lib' => 'Renouvellement de mesure/prestation - changement de lieu d\'accueil ',
	    'publication' => true,
            'bloc' => 'Orange',
        ),
   
   58 => array(       
	    'id' => '0058',
	    'fic' => 'DateFinMauvaisTypev',
            'lib' => 'DateFin et Mauvais Typev',
	    'publication' => true,
            'bloc' => 'Orange',
        ),	
   60 => array(       
	    'id' => '0060',
	    'fic' => 'recordsOrphelins',
            'lib' => 'Record fin sans record debut associé',
	    'publication' => true,
            'bloc' => 'Orange',
        ),	

   80 => array(       
	    'id' => '0080',
	    'fic' => 'DateFinSansDateDebut',
            'lib' => 'DATFIN alors que DATDEB absent',
	    'publication' => false,
            'bloc' => 'Orange',
        ),

  95 => array(       
	    'id' => '0095',
	    'fic' => 'DureeExcessive',
            'lib' => 'Durée excessive',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

   99 => array(       
	    'id' => '0099',
	    'fic' => 'autres',
            'lib' => 'Autres anomalies',
	    'publication' => true,
            'bloc' => 'Orange',
        ),

);
	
//print_r ($tabScripts);
?>
