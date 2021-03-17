<?php

/* _dbclass.php                      */
/* ================================== */
//_____________________________________________________________________ Fonctions


function print_methods($obj) {
	// Affiche à l'écran les méthodes d'un objet passé en argument
    $arr = get_class_methods(get_class($obj));
    foreach ($arr as $method)
        echo "function $method()<br>";
}

function requete_insert($obj) {
	//
	// renvoie la requte SQL d'insert
	// pour un enregistrement complet mais sans l'id
	// (chaque table doit avoir un id unique en auto-increment au début de sa définition)
	//
	
	$obj->datecrea = date("Y-m-d H:i:s");
	$obj->datemodif = date("Y-m-d H:i:s");
	
	$arr = get_object_vars($obj); // Noms des champs
	$table = get_class($obj); // Nom de la table
	// On va constituer la requte update en deux chanes parallles :
	// Champs et Valeurs
	$mesChamps = "insert into ".$table." (";
	$mesValeurs = " values(";
	foreach ($arr as $key => $value) {
		//if (get_magic_quotes_gpc() != 1) $value = addslashes($value);
		if (strpos($value, "\\") == FALSE) $value = addslashes($value);
		$mesChamps .= "$key,";
		$mesValeurs .= "'$value',";
	}

	// Les deux parties sont constituées mais avec une virgule de trop
	$mesChamps = substr($mesChamps,0,(strlen($mesChamps) - 1));
	$mesValeurs = substr($mesValeurs,0,(strlen($mesValeurs) - 1));
	// et sont à terminer par une ")"
	$mesChamps .= ") ";
	$mesValeurs .= ") ";

	$monInsert = $mesChamps.$mesValeurs;
//echo "<br>$monInsert<br>";
	return $monInsert;
}

function requete_update($obj) {
	//
	// Renvoie la requte SQL d'update
	// pour un enregistrement complet.
	// (chaque table doit avoir un id unique en auto-increment au début de sa définition)
	//
	$obj->datemodif = date("Y-m-d H:i:s");
	
	$arr = get_object_vars($obj); // Noms des champs
	$table = get_class($obj); // Nom de la table
	$monUpdate = "update $table set ";
	$index = 0;
	foreach ($arr as $key => $value) {
		//if (!get_magic_quotes_gpc()) $value = addslashes($value);
		if (strpos($value, "\\") == FALSE) $value = addslashes($value);

		if ($index == 0) {
			// Le premier champ est l'identifiant
			// utilisé pour la condition where
			$maCondition = "$key = '$value'";
			$index++;
		}
		else {
			$monUpdate .= " $key = '$value',";
		}
	}
	// On élimine la virgule de trop
	$monUpdate = substr($monUpdate,0,(strlen($monUpdate) - 1));
	// et on termine par la condition
	$monUpdate .= " where $maCondition";

	return $monUpdate;
}


function requete_update_fr($obj) {
	//
	// Renvoie la requte SQL d'update
	// pour tous les les champs sauf langue autre que _fr.
	// (chaque table doit avoir un id unique en auto-increment au début de sa définition)
	//
	$obj->datemodif = date("Y-m-d H:i:s");
	
	$arr = get_object_vars($obj); // Noms des champs
	$table = get_class($obj); // Nom de la table
	$monUpdate = "update $table set ";
	$index = 0;
	foreach ($arr as $key => $value) {
		if (!get_magic_quotes_gpc()) $value = addslashes($value);

		if ($index == 0) {
			// Le premier champ est l'identifiant
			// utilisé pour la condition where
			$maCondition = "$key = '$value'";
			$index++;
		}
		else {
			if (strstr($key,"_en") || strstr($key,"_sp") || strstr($key,"_ru")) {
			} else {
				$monUpdate .= " $key = '$value',";
			}
		}
	}
	// On élimine la virgule de trop
	$monUpdate = substr($monUpdate,0,(strlen($monUpdate) - 1));
	// et on termine par la condition
	$monUpdate .= " where $maCondition";

	return $monUpdate;
}

	
//_____________________________________________________________________ Class

class decret {
   
	function readvars($post_vars) {
		//
		// Garnit un objet à partir du tableau passé en argument (_POST ou mysqli_fetch_assoc).
		// Ce tableau peut contenir des valeurs qui n'appartiennent pas à l'objet...
		// Les champs du formulaire doivent avoir le même nom que les champs de l'objet !
		//
		// Récupère les noms des variables de l'objet
		$class_vars = get_class_vars(get_class($this));
		//print_r($class_vars);
		// Garnit l'objet avec les valeurs pour les noms trouvés dans $class_vars
		foreach ($class_vars as $nom=>$valeur) {
			$this->$nom = $post_vars["$nom"];
		}
	}
	
	function readrecord($myRecord) {
		//
		// Garnit un objet à partir d'un record mysqli_fetch_row passé en argument.
		// Ce tableau contient les valeurs de l'enregistrement indexées (origine 0)...
		// Valable seulement pour un select *...
		//
		// Récupre les noms des variables de l'objet
		$class_vars = get_class_vars(get_class($this));
		// Garnit l'objet avec les valeurs indicées
		$indice = 0;
		foreach ($class_vars as $nom=>$valeur) {
			$this->$nom = $myRecord[$indice];
			$indice++;
		}
		
	}
	
	function get($id, $conn) {
		//
		// Garnit un objet en lisant un enregistrement dans la table concernée
		// Le paramètre $id est soit une clé primaire (numérique), soit une requête.
		// Le champ clé primaire doit être le premier dans la définition de la table
		//
		// Récupère le nom de la table
		$table = get_class($this); // Nom de la table
		// Récupère les noms des champs de l'objet
		$primary_key = array_keys(get_class_vars(get_class($this)));
		if (is_numeric($id)) {
			$requ = "select * from $table where ".$primary_key[0]." = '$id' ";
		} else {
			$requ = $id;
		}
		
//echo $requ."<br /> ";
		$res = mysqli_fetch_assoc($conn->query($requ));
			if ($res) {
			foreach ($res as $nom=>$valeur) {
				$this->$nom = $res["$nom"];
			}
		} else {
//echo $requ."<br /> ";
		}
	}
	
	function get_field($id, $field, $conn) {
		//
		// Renvoie le contenu d'un champ en lisant un enregistrement dans la table concernée
		// Le paramètre $id est soit une clé primaire (numérique), soit une requête.
		// Le champ clé primaire doit être le premier dans la définition de la table
		//
		// Récupère le nom de la table
		$table = get_class($this); // Nom de la table
		// Récupère les noms des champs de l'objet
		$primary_key = array_keys(get_class_vars(get_class($this)));
		//
		if (is_numeric($id)) {
			$requ = "select $field from $table where ".$primary_key[0]." = '$id' ";
		} else {
			$requ = $id;
		}
		$res = mysqli_fetch_assoc($conn->query($requ));

		return $res[$field];
	}
		
}

//
//--------------------------------------------------------- Données
//

class evenements_liens extends decret
{
	var $id_lien;
	var $IDENFANT;
	var $id_donnee_origine;
	var $id_donnee;
	var $id_evenement_type;
	var $evenement_valeur;
	var $datecrea;
	var $datemodif;
}

class evenements_types extends decret
{
	var $id_evenement_type;
	var $ordre;
	var $lib;
	var $field;
	var $date_debut;
	var $date_fin;
	var $code;
	var $code_parent;
	var $field2;
	var $datecrea;
	var $datemodif;
}

class formulaires extends decret
{
	var $id_formulaire;
	var $id_formulaire_grp;
	var $ordre;
	var $code;
	var $lib;
	var $lib_court;
	var $ss_menu;
	var $menu_parent;
	var $datecrea;
	var $datemodif;
}

class liens extends decret
{
	var $id_lien;
	var $IDENFANT;
	var $id_donnee;
	var $id_ip;
	var $SUITEINFO;
	var $id_evaluation;
	var $SUITEVAL;
	var $id_decision;
	var $TYPEINT;
	var $id_intervention;
	var $code_formulaire;
	var $id_mesure;
	var $MOTIFARR;
	var $ORIENT;
	var $datecrea;
	var $datemodif;
}

class nomenclature extends decret
{
	var $IdVar;
	var $IDENFANT;
	var $date_auto;
	var $code_formulaire;
	var $Field;
	var $NEWFIELD;
	var $KEEP;
	var $Type;
	var $Null;
	var $Key;
	var $Lib;
}

class params extends decret
{
	var $id_param;
	var $UTDP;
	var $DEPT;
	var $datecrea;
	var $datemodif;
}

class enfants extends decret
{
	var $IDENFANT;
	var $renseignes;
	var $NUMDO;
	var $PRENOM;
	var $PRENOMAUTRES;
	var $NOMUSAGE;
	var $PRENOMMERE;
	var $NOMMERE;
	var $NOMMEREJEUNE;
	var $JNAIS;
	var $MNAIS;
	var $ANAIS;
	var $SEX;
	var $PHONEXPRENOM;
	var $PHONEXNOM;
	var $ID;
	var $IDPREC;
	var $ADTENRE;
	var $datecrea;
	var $datemodif;
}

class donnees extends decret
{
	var $id_donnee;
	var $IDENFANT;
	var $DEPT;
	var $NUMANONYM;
	var $NUMANONYMPREC;
	var $DATIP;
	var $TRANSIP;
	var $ORIGIP;
	var $VIOLSEX;
	var $VIOLPHYS;
	var $NEGLIG;
	var $VIOLPSY;
	var $DATSIGN;
	var $DATJE;
	var $SUITSIGNCG;
	var $SEXE;
	var $MNAIS;
	var $ANAIS;
	var $FREQSCO;
	var $DIPLOME;
	var $NBPER;
	var $NBFRAT;
	var $AUTREHEBER;
	var $RESMENAG;
	var $EMPLA1;
	var $STATOCLOG;
	var $LIENA1;
	var $LIENA2;
	var $SEXA1;
	var $SEXA2;
	var $CSPA1;
	var $CSPA2;
	var $MINIMA;
	var $ALLOC;
	var $MEREINC;
	var $PEREINC;
	var $ANSMERE;
	var $DCMERE;
	var $DCPERE;
	var $DATDCMERE;
	var $DATDCPERE;
	var $CONTMERE;
	var $CONTPERE;
	var $CONFL;
	var $VIOLFAM;
	var $DEFINTEL;
	var $NOTIFEVAL;
	var $FINEVAL;
	var $SEXAUT1;
	var $MINAUT1;
	var $SEXAUT2;
	var $MINAUT2;
	var $CONDEDUC;
	var $SUITEVAL;
	var $TITAP;
	var $DATDECAP;
	var $DECAP;
	var $DECISION;
	var $NATPDECADM;
	var $AUTREDA;
	var $NATDECASSED;
	var $AUTREDJ;
	var $DATDECPE;
	var $INTERANT;
	var $TYPINTERDOM;
	var $DATDEBAD;
	var $DATFINAD;
	var $DATDEBACC;
	var $DATFINACC;
	var $LIEUACC;
	var $ACCMOD;
	var $AUTRLIEUACC;
	var $TYPDECJUD;
	var $DATDEBINTER;
	var $DATFININTER;
	var $DATDEBPLAC;
	var $DATFINPLAC;
	var $AUTRLIEUAR;
	var $MOTFININT;
	var $DATDECMIN;
	var $SITAPML;
	var $CODEV;
	var $MODACC;
	var $SCODTCOM;
	var $NIVSCO;
	var $SCOCLASPE;
	var $ETABSCOSPE;
	var $TYPCLASPE;
	var $TYPETABSPE;
	var $NONSCO;
	var $HANDICAP;
	var $DATDECMDPH;
	var $DATEXDECMDPH;
	var $SUITSIGOPP;
	var $SUITSIGJE;
	var $SUITSIGSS;
	var $DATAVIS;
	var $ENQPENAL;
	var $SAISJUR;
	var $COMPOMENAG;
	var $ANSPERE;
	var $ANSA1;
	var $ANSA2;
	var $EMPLA2;
	var $REVTRAV;
	var $AUTRE;
	var $MOTIFSIG;
	var $CONDADD;
	var $VIOLPERS;
	var $VIOLFAMPHYS;
	var $SOUTSOC;
	var $SANTE;
	var $SECURITE;
	var $MORALITE;
	var $CONDEDEV;
	var $LIEUPLAC;
	var $PLACMOD;
	var $TYPINTERV;
	var $MOTIFML;
	var $NOUVDECPE;
	var $NATNOUVDECPE;
	var $NATDECPLAC;
	var $INSTITPLAC;
	var $PROJET;
	var $SIGNPAR;
	var $SIGNMIN;
	var $DATSIGNPROJ;
	var $LIENAUT1;
	var $LIENAUT2;
	var $MESANT;
	var $ACCFAM;
	var $datecrea;
	var $datemodif;
}

class importationsXML extends decret
{
	var $id_importation;
	var $code_departement;
	var $annee;
	var $nom_fichier_ini;
	var $nom_fichier_traduit;
	var $num_lignes;
	var $commentaires;
	var $date_importation;
	var $date_traitement;
	var $datecrea;
	var $datemodif;
}
//
//--------------------------------------------------------- Tables de référence
//
class ref_NUMDEPT extends decret  	
{
	var $id;
	var $code;
	var $libelle;
}

class ref_NUMANONYM extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NUMANONYMPREC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_TRANSIP extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ORIGIP extends decret  	
{
	var $id;
	var $libelle;
}

class ref_VIOLSEX extends decret  	
{
	var $id;
	var $libelle;
}

class ref_VIOLPHYS extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NEGLIG extends decret  	
{
	var $id;
	var $libelle;
}

class ref_VIOLPSY extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SUITSIGNCG extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SEXE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MNAIS extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ANAIS extends decret  	
{
	var $id;
	var $libelle;
}

class ref_FREQSCO extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DIPLOME extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NBPER extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NBFRAT extends decret  	
{
	var $id;
	var $libelle;
}

class ref_AUTREHEBER extends decret  	
{
	var $id;
	var $libelle;
}

class ref_RESMENAG extends decret  	
{
	var $id;
	var $libelle;
}

class ref_EMPLA1 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_STATOCLOG extends decret  	
{
	var $id;
	var $libelle;
}

class ref_LIENA1 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_LIENA2 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SEXA1 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SEXA2 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CSPA1 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CSPA2 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MINIMA extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ALLOC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MEREINC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_PEREINC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ANSMERE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DCMERE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DCPERE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DATDCMERE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DATDCPERE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CONTMERE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CONTPERE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CONFL extends decret  	
{
	var $id;
	var $libelle;
}

class ref_VIOLFAM extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DEFINTEL extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SEXAUT1 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MINAUT1 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SEXAUT2 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MINAUT2 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CONDEDUC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SUITEVAL extends decret  	
{
	var $id;
	var $libelle;
}

class ref_TITAP extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DECAP extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DECISION extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NATPDECADM extends decret  	
{
	var $id;
	var $libelle;
}

class ref_AUTREDA extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NATDECASSED extends decret  	
{
	var $id;
	var $libelle;
}

class ref_AUTREDJ extends decret  	
{
	var $id;
	var $libelle;
}

class ref_INTERANT extends decret  	
{
	var $id;
	var $libelle;
}

class ref_TYPINTERDOM extends decret  	
{
	var $id;
	var $libelle;
}

class ref_LIEUACC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ACCMOD extends decret  	
{
	var $id;
	var $libelle;
}

class ref_AUTRLIEUACC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_TYPDECJUD extends decret  	
{
	var $id;
	var $libelle;
}

class ref_AUTRLIEUAR extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MOTFININT extends decret  	
{
	var $id;
	var $libelle;
}

class ref_DATDECMIN extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SITAPML extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CODEV extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MODACC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SCODTCOM extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NIVSCO extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SCOCLASPE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ETABSCOSPE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_TYPCLASPE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_TYPETABSPE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NONSCO extends decret  	
{
	var $id;
	var $libelle;
}

class ref_HANDICAP extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SUITSIGOPP extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SUITSIGJE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SUITSIGSS extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ENQPENAL extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SAISJUR extends decret  	
{
	var $id;
	var $libelle;
}

class ref_COMPOMENAG extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ANSPERE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ANSA1 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ANSA2 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_EMPLA2 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_REVTRAV extends decret  	
{
	var $id;
	var $libelle;
}

class ref_AUTRE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MOTIFSIG extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CONDADD extends decret  	
{
	var $id;
	var $libelle;
}

class ref_VIOLPERS extends decret  	
{
	var $id;
	var $libelle;
}

class ref_VIOLFAMPHYS extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SOUTSOC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SANTE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SECURITE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MORALITE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_CONDEDEV extends decret  	
{
	var $id;
	var $libelle;
}

class ref_LIEUPLAC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_PLACMOD extends decret  	
{
	var $id;
	var $libelle;
}

class ref_TYPINTERV extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MOTIFML extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NOUVDECPE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NATNOUVDECPE extends decret  	
{
	var $id;
	var $libelle;
}

class ref_NATDECPLAC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_INSTITPLAC extends decret  	
{
	var $id;
	var $libelle;
}

class ref_PROJET extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SIGNPAR extends decret  	
{
	var $id;
	var $libelle;
}

class ref_SIGNMIN extends decret  	
{
	var $id;
	var $libelle;
}

class ref_LIENAUT1 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_LIENAUT2 extends decret  	
{
	var $id;
	var $libelle;
}

class ref_MESANT extends decret  	
{
	var $id;
	var $libelle;
}

class ref_ACCFAM extends decret  	
{
	var $id;
	var $libelle;
}

?>