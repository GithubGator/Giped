<?php
/*
	Fonctions utilisateur
	======================
*/

date_default_timezone_set('Europe/Paris');
$date_jour = date("Y-m-d H:i:s");
$date_jour_mysql = date("Y-m-d");

//---------------------------------------------------------------------------------
function ExecRequete($requete, $connection) {
    if ($result = $connection->query($requete)) {
        return $result;
    } else {
        echo "Erreur dans exécution de la requette ". '<br>'.$requete.'<br>';
        echo $connection->error;
        $result->close();
        exit;
    }
} // end ExecRequete


function get_lib_ref($field, $valeur, $conn) {
    //
    // Renvoie le libellé d'un champ d'une table de référence ref_field
    // en fonction de sa valeur dans donnees
    //
    // Récupère le nom de la table
    $table_ref = "ref_".$field;
    $ref = new $table_ref;
    $requ = "select lib from $table_ref where ".$field." = '".$valeur."' ";
    // Récupère le libellé
    $res = mysqli_fetch_assoc($conn->query($requ));

    return $res["lib"];
}


//---------------------------------------------------------------------------------
function MenuSelect($table, $tableref, $key, $tri, $lib, $Titre_Select, $Selected, $connection) {
    /*
        Construit un menu déroulant d'une table de référence
        $table est le nom de la table qui pointe sur $tableref
        $key est le nom du champ identifiant de $tableref constituant le menu
        Si $table existe, le menu sera constitué avec une jointure entre les deux tables
        (cela suppose que le pointeur dans $table a le même nom que l'identifiant dans $tableref.

        $tableref doit impérativement respecter la structure suivante :
         - champ 1 = identifiant
        et ne pas être trop grosse !

        Les paramètres à passer sont :
         - $table = nom de la table (ex. adresses)
         - $tableref = nom de la table de référence (ex. cat_adresses) qui constituera le menu
         - $key = nom du champ pointeur (ex. cat_adr_id commun aux deux tables)
         - $tri = indice du champ de la table pour le critère de tri (> 1)
         - $lib = indice du champ de la table pour le critère d'affichage (> 1)
           ne pas dépasser le nombre de champs de la table sous peine d'erreur (non gérée).
         - $Titre_Select = libellé du menu déroulant (première option)
         - $Selected = identifiant de l'item à présélectionner, 0 si aucun.
         - $connection = identifiant connection courante.

         Elle va retourner le menu déroulant correspondant.
    */
    if (!$table) {
        $mySelect_query = "select * from $tableref order by $tri, $lib";
    }
    else {
        $mySelect_query = "select distinct $tableref.* from $table,$tableref where ($table.$key = $tableref.$key) order by $tri, $lib";
    }

    $result = ExecRequete($mySelect_query, $connection);

    //
    // Construit le menu. Le nom nom du champ clé unique ($key) est utilisé comme "name" dans le SELECT.
    $Select = "<select name='".$key."'>\n";
    $Select .= "<option value=\"\"> ".$Titre_Select."</option>\n";
    while ($ligne =mysqli_fetch_row($result)) {
        $Select .= "<option value=\"".$ligne[0]."\"";
        if ($ligne[0] == $Selected) {
            $Select .= " selected=\"selected\">";
        }
        else {
            $Select .= ">";
        }
        $Select .= encode_texte($ligne[$lib - 1])."</option>\n";
    }
    $Select .= "</select>\n";

    return $Select;
} // end MenuSelect

//---------------------------------------------------------------------------------
function MenuSelectRecords($requete, $key, $Titre_Select, $Selected, $connection, $javascript=NULL) {
    /*
        Construit un menu déroulant  partir d'enregistrements extraits d'une table
        $table est le nom de la table contenant les enregistrements  extraire
        $requete est la requete  excuter pour extraire les enregistrements.
        Si $requete est vide, elle n'est pas excute et le select est vide.

        Les paramtres  passer sont :
         - $requete = ex. "select cat_adr_id, cat_adr_nom from cat_adresses where cat_adr_id=3;"
         - $key = nom du champ clé (ex. cat_adr_id)
         - $Titre_Select = libellé du menu déroulant (premire option)
         - $Selected = identifiant de l'item  prslectionner, 0 si aucun.
         - $connection = identifiant connection courante.
         - $javascript = par exemple onchange="submit();" rendant le select actif.

         Elle va retourner le menu droulant correspondant.
    */

    $result = ExecRequete($requete, $connection);
    //
    // Construit le menu. Le nom nom du champ clé unique ($key) est utilisé comme "name" dans le SELECT.
    $Select = "<select name='".$key."' id='".$key."' ".$javascript.">\n";
    $Select .= "<option value=\"\"> ".$Titre_Select."</option>\n";
    while ($ligne = mysqli_fetch_row($result)) {
        $Select .= "<option value=\"".$ligne[0]."\"";
        if ($ligne[0] == $Selected) {
            $Select .= " selected=\"selected\">";
        }
        else {
            $Select .= ">";
        }
        $Select .= encode_texte($ligne[1])."</option>\n";
    }
    $Select .= "</select>\n";

    return $Select;
} // end MenuSelect


function MenuSelectStruct ($id, $tableref1, $prefixID, $tableref2, $condition, $concat, $Titre_Select, $selection, $javascript, $connection) {
    global $disabled;
    //
    /*
        Construit un menu déroulant structuré par des optgroup d'une table de référence (tableref1)
        liée elle-même à une table de catégories (tableref2) par la clé primaire primary2.

        $id est l'identifiant du champ à mettre à jour dans tableref1
        $tableref1 est la table à mettre à jour
        $taableref2 est une table optionnelle de catégories structurant $tableref1
        Si $tableref2 existe, le menu sera qtructuré avec une jointure entre tableref2 et tableref1 par la clé primaire de tableref2

        Le paramètre optionnel $prefixID permettra de préfixer l'id du select en cas de champs multiples dans la page...

        La condition optionnelle $condition doit être en clair ex. "where champ = valeur "
        Le paramètre optionnel $concat permettra de concaténer deux champs (ex; num département + libellé) et doit être en clair
        ex. "concat(champ1, champ2)"
        Le paramètre optionnel $javascript permettra d'ajouter un appel de fonction javascript (ex;  onclick="...

        --------------------------------------------------------------- Si tabkeref2 existe :
        $tableref1 doit impérativement respecter la structure suivante (au moins dans _dbclass.php :
         - champ 1 = identifiant
         - champ 2 = pointeur tableref2
         - champ 3 = ordre de tri
         - champ 4 = libellé

        $tableref2 doit impérativement respecter la structure suivante :
         - champ 1 = identifiant
         - champ 2 = ordre de tri
         - champ 3 = libellé

        --------------------------------------------------------------- Si tabkeref2 n'existe pas :
        $tableref1 impérativement respecter la structure suivante :
         - champ 1 = identifiant
         - champ 2 = ordre de tri
         - champ 3 = libellé


         Elle va retourner le menu déroulant structuré correspondant.
    */
    // récupération des données descriptives
    $ref1_table = get_class($tableref1); // Nom de la table
    $ref1_champs = array_keys(get_object_vars($tableref1)); // Noms des champs
    $disable = array_search("type", $ref1_champs);

    //
    if (isset($tableref2)) {
        $ref2_table = get_class($tableref2); // Nom de la table
        $ref2_champs = array_keys(get_object_vars($tableref2)); // Noms des champs
    }
    //
    // Construction de la requête
    //
    if (isset($ref2_table)) {
        if (isset($concat)) {
            $sql = "select distinct $ref1_table.".$ref1_champs[0].", ".$concat.", ";
        } else {
            $sql = "select distinct $ref1_table.".$ref1_champs[0].", $ref1_table.".$ref1_champs[3].", ";
        }
        $sql .= "$ref2_table.".$ref2_champs[0].", $ref2_table.".$ref2_champs[2]." ";
    } else {
        if (isset($concat)) {
            $sql = "select distinct $ref1_table.".$ref1_champs[0].", ".$concat." ";
        } else {
            $sql = "select distinct $ref1_table.".$ref1_champs[0].", $ref1_table.".$ref1_champs[2]." ";
        }
    }
    // Ajoute le champ type s'il existe
    if ($disable > 0) $sql .= ", $ref1_table.".$ref1_champs[$disable]." ";
    //
    $sql .= "from ".$ref1_table." ";
    // Jointures
    if (isset($ref2_table)) $sql .= "join $ref2_table using (".$ref2_champs[0].") ";
    if ($sql != NULL) $sql .= $condition;
    // Order by
    if (isset($ref2_table)) {
        //$sql .= "order by 1, 3, ".$ref1_champs[2].", ".$ref1_champs[3]." ";
        $sql .= "order by ".$ref2_champs[1].", ".$ref2_champs[2].", ".$ref1_champs[2].", ".$ref1_champs[3]." ";
    } else {
        $sql .= "order by ".$ref1_champs[1].", ".$ref1_champs[2]." ";
    }
    //
    $result = $connection->query($sql);
    //
    // Construction du select
    //
    $monSelect = "";
    $monSelect .= "\n<select ".$disabled." name=\"".$prefixID.$id."\" id=\"".$prefixID.$id."\" size=\"1\" ".$javascript.">\n
						\n<option value=\"\">".$Titre_Select."</option>\n";

    $categorie = 0;
    while ($ligne = $result->fetch_array()) {
//print_r($ligne); echo "<br /><br />";
        if(isset($ref2_table)) {
            if ($ligne[2] != $categorie) {
                if ($categorie != 0) $monSelect .= "</optgroup>\n";
                $monSelect .= "<optgroup label=\"".$ligne[3]."\">\n";
                $categorie = $ligne[2];
            }
        }
        $selected = NULL;
        if ($ligne[0] == $selection) {
            $selected = "selected=\"selected\"";
        } else {
            $selected = NULL;
        }
        if ($ligne["type"] > 0) $selected = "disabled=\"disabled\"";
        //
        // debug affiche valeur $monSelect .= "<option value=\"".$ligne[0]."\" $selected>".$ligne[0]." - ".$ligne[1]."</option>\n";
        $monSelect .= "<option value=\"".$ligne[0]."\" $selected>".$ligne[1]."</option>\n";

    }

    if ($categorie > 0) $monSelect .= "</optgroup>\n";
    $monSelect .= "</select>";

    return $monSelect;
}


function MenuRadio ($type=NULL, $champ, $tablebase=NULL, $tableRadio=NULL, $tableCible=NULL, $condition=NULL, $id_donnee, $pos, $connection) {
    global $disabled;
    //
    /*
        En fonction du type,
        Si 1 :
            Construit une série de boutons radio liés à chaque enregistrement d'une table de base (tablebase -> libellé de la série radio, code champ).
            liée elle-même à une table de valeurs (tableRadio -> code champ, libellé bouton) par le code champ.
        Si 0 :
            Construit une série de boutons radio avec un libellé standard (non, oui, ne sait pas ou masculin, féminin, ne sait pas)

        $tablebase est la table contenant les libellés titre de la série
        $tableRadio est la table décrivant les boutons liés (libellé et valeur de chaque bouton)
        $tableCible est la table à mettre à jour
        $id_donnee est l'identifiant du record dans la table à mettre à jour

        $pos = 1 donnera une mise en page en une ligne, $pos = 0 donnera une mise en page en liste verticale
    */
    /*
        // récupération des données descriptives
        $tablebase_table = get_class($tablebase); // Nom de la table
        $tablebase_champs = array_keys(get_object_vars($tablebase)); // Noms des champs
        //
        $tableRadio = get_class($tableRadio); // Nom de la table
        $tableRadio_champs = array_keys(get_object_vars($tableRadio)); // Noms des champs
    print_r ($tablebase_champs); exit();
    */
    //
    $Donnees = new $tableCible;
    $Donnees->get($id_donnee, $connection);

    if (isset($tablebase)) $monItem = new $tablebase;
    $monBouton = new $tableRadio;
    //
    // Construction du code
    //
    if ($type == 1) {
        //
        // Construction de la requête
        //
        $sql = "select ".$tablebase.".* ";
        $sql .= "from ".$tablebase." ";
        if (strlen($condition) > 5) $sql .= $condition." ";
        $sql .= "order by ".$tablebase.".ordre ";
        $result = $connection->query($sql);

        $monCode = "\n<table style=\"font-size: 90%;border-bottom: 1px solid #cccccc;\">";

        while ($record = $result->fetch_assoc()) {
            $monItem->readvars($record);

            $sql = "select * from ".$tableRadio." ";
            $sql .= "where champ = '".$monItem->champ."' ";
            $sql .= "order by ordre ";
            $boutons = $connection->query($sql);
            //
            $monCode .= "\n<tr>";
            $monCode .= "\n<td>".$monItem->lib."</td>";
            $monCode .= "\n<td>\n";
            $nbre = 0;
            //
            if ($pos < 1) $monCode .= "\n<table>";
            //
            while ($record = $boutons->fetch_assoc()) {
                $monBouton->readvars($record);
                $id = $monItem->champ.$nbre;
                $champ = $monItem->champ;
                $nbre++;
                //
                if ($monBouton->valeur == $Donnees->$champ) {
                    $checked = "checked=\"checked\" ";
                } else {
                    $checked = NULL;
                }
                //
                if ($pos == 1) {
                    // boutons affichés en une ligne
                    $monCode .= "&nbsp;".$monBouton->lib."&nbsp;";
                    $monCode .= "<input ".$disabled." name=\"".$monItem->champ."\" id=\"".$id."\" type=\"radio\" value=\"".$monBouton->valeur."\" $checked";
                    $monCode .= "onchange=\"updateField(document.getElementById('".$id."'), '".$tableCible."', ".$id_donnee.")\" ";
                    $monCode .= "/>&nbsp;\n";
                } else {
                    // boutons affichés en tableau une ligne par bouton
                    $monCode .= "\n<tr>\n<td style=\"text-align: left;\">";
                    $monCode .= "<input ".$disabled." name=\"".$monItem->champ."\" id=\"".$id."\" type=\"radio\" value=\"".$monBouton->valeur."\" $checked";
                    $monCode .= "onchange=\"updateField(document.getElementById('".$id."'), '".$tableCible."', ".$id_donnee.")\" ";
                    $monCode .= "/>&nbsp;\n";
                    $monCode .= "&nbsp;".$monBouton->lib;
                    $monCode .= "\n</td>\n</tr>";
                }
            }
            if ($pos < 1) $monCode .= "\n</table>";

            $monCode .= "</td>";

            $monCode .= "\n</tr>";
        }
        $monCode .= "\n</table>";

    } else {

        $monCode = "\n<table style=\"font-size: 90%;border-bottom: 1px solid #cccccc;\">";

        $sql = "select * from ".$tableRadio." ";
        $sql .= "order by ordre ";
        $boutons = $connection->query($sql);
        //
        $monCode .= "\n<tr>";
        $monCode .= "\n<td>\n";
        //
        if ($pos < 1) $monCode .= "\n<table>";
        //
        $nbre = 0;
        while ($record = $boutons->fetch_assoc()) {
            $monBouton->readvars($record);
            $id = $champ.$nbre;
            $nbre++;
            //
            if ($monBouton->valeur == $Donnees->$champ) {
                $checked = "checked=\"checked\" ";
            } else {
                $checked = NULL;
            }
            //
            if ($pos == 1) {
                // boutons affichés en une ligne
                $monCode .= "&nbsp;".$monBouton->lib."&nbsp;";
                $monCode .= "<input ".$disabled." name=\"".$champ."\" id=\"".$id."\" type=\"radio\" value=\"".$monBouton->valeur."\" $checked";
                $monCode .= "onchange=\"updateField(document.getElementById('".$id."'), '".$tableCible."', ".$id_donnee.")\" ";
                $monCode .= "/>&nbsp;\n";
            } else {
                // boutons affichés en tableau une ligne par bouton
                $monCode .= "\n<tr>\n<td style=\"text-align: left;\">";
                $monCode .= "<input ".$disabled." name=\"".$champ."\" id=\"".$id."\" type=\"radio\" value=\"".$monBouton->valeur."\" $checked";
                $monCode .= "onchange=\"updateField(document.getElementById('".$id."'), '".$tableCible."', ".$id_donnee.")\" ";
                $monCode .= "/>&nbsp;\n";
                $monCode .= "&nbsp;".$monBouton->lib;
                $monCode .= "</td>\n</tr>";
            }
        }

        if ($pos < 1) $monCode .= "\n</table>\n";

        $monCode .= "\n</td>";

        $monCode .= "\n</tr>";
        $monCode .= "\n</table>\n";
    }

    return $monCode;
}


function SaisieDate ($champ, $tableCible, $id_donnee, $connection) {
    /*
        Construit une série de trois champs J, MM, AAAA pour la saisie d'une date mysql
    */
    //
//echo $tableCible; exit;
    global $disabled;
    //
    $Donnees = new $tableCible;
    $Donnees->get($id_donnee, $connection);
    list($annee, $mois, $jour) = explode("-", $Donnees->$champ);
    //
    // Construction du code
    //

    $monCode = "<span style=\"font-size: 90%;\">\n";
    $monCode .= "jour&nbsp;";
    $prefix = "J|";
    $monCode .= "<input ".$disabled." type=\"text\" name=\"".$prefix.$champ."\" id=\"".$prefix.$champ."\" size=\"2\" maxlength=\"2\" value=\"".$jour."\" onchange=\"updateField(document.getElementById('".$prefix.$champ."'), '".$tableCible."', ".$Donnees->id_donnee.")\" onfocus=\"this.select();\" />\n";
    $monCode .= "mois&nbsp;";
    $prefix = "M|";
    $monCode .= "<input ".$disabled." type=\"text\" name=\"".$prefix.$champ."\" id=\"".$prefix.$champ."\" size=\"2\" maxlength=\"2\" value=\"".$mois."\" onchange=\"updateField(document.getElementById('".$prefix.$champ."'), '".$tableCible."', ".$Donnees->id_donnee.")\" onfocus=\"this.select();\" />\n";
    $monCode .= "ann&eacute;e&nbsp;";
    $prefix = "A|";
    $monCode .= "<input ".$disabled." type=\"text\" name=\"".$prefix.$champ."\" id=\"".$prefix.$champ."\" size=\"4\" maxlength=\"4\" value=\"".$annee."\" onchange=\"updateField(document.getElementById('".$prefix.$champ."'), '".$tableCible."', ".$Donnees->id_donnee.")\" onfocus=\"this.select();\" />\n";
    $monCode .=	"</span>\n";

    return $monCode;
}


//---------------------------------------------------------------------------------
function SelectDate($nom, $date_select, $annee_depart, $jours) {
    /*
        Construit un menu droulant d'une srie de jours, mois et annes
        Les paramtres  passer sont :
         - $nom : qui sera le nom de la variable POST pour le SELECT
         - $date_select : date sous la forme YYYY-MM-JJ (format mySQL) pour re-slectionner une date existante.
             Si $date_select n'est pas fourni, l'anne la plus ancienne sera l'anne en cours,
             sauf si le paramtre $annee_depart est fourni.
             Si $date_select est fourni, l'anne la plus ancienne sera l'anne de $date_select,
             sauf si le paramtre $annee_depart est fourni.
             Si $date_select n'est pas fourni, l'anne la plus ancienne sera l'anne en cours.
         - $annee_depart : si l'argument est fourni, le menu des annes commencera  l'anne fournie.
         - $jours : si $jours = 1, le menu des jours sera gnr, sinon il ne le sera pas
             (il n'y aura que mois et annes).

         La fonction va retourner les 3 menus droulants jour, mois et anne.
    */
    // Anne courante
    $aujourdhui = date("Y-m-j");
    //$aujourdhui = strtotime($aujourdhui);
    $aujourdhui = getdate(strtotime($aujourdhui));
    $notreAnnee = $aujourdhui['year'];

    // Date fournie en argument
    if ($date_select) {
        $date_select = explode("-", $date_select);
        $monAnnee = $date_select[0];
        $monMois =  $date_select[1];
        $monJour =  $date_select[2];
    }

    if ($jours == 1) {
        // Menu droulant des jours (1-31)
        $Select = "<select name='".$nom."_j'>\n";
        $Select .= "<option value=''>Jour</option>\n";

        for ($index = 1; $index < 32; $index++) {
            $Select .= "<option value=$index";
            if ($index == $monJour) {
                $Select .= " selected=\"selected\">$index</option>\n";
            }
            else {
                $Select .= ">$index</option>\n";
            } // for
        }
        $Select .= "</select>\n";
    }

    // Menu droulant des mois (1-12)
    $Select .= "<select name='".$nom."_m'>\n";
    $Select .= "<option value=''>Mois</option>\n";

    for ($m = 1; $m < 13; $m++) {
        $Select .= "<option value=$m";
        if ($m == $monMois) {
            $Select .= " selected=\"selected\">$m</option>\n";
        }
        else {
            $Select .= ">$m</option>\n";
        }
    }
    $Select .= "</select>\n";

    // Menu droulant des annes.
    $fin = $notreAnnee + 3;
    if (!$date_select) {
        // Si la date n'est pas fournie en argument, on constitue le menu
        if (!$annee_depart) {
            //  partir de l'anne courante si $annee_depart n'est pas prcise
            $debut = $notreAnnee;
        } else {
            //  partir de $annee_depart si $annee_depart est prcise
            $debut = $annee_depart;
        }
    }
    else {
        // Si la date est fournie en argument, on constitue le menu
        // de l'anne fournie  l'anne courante + 3
        if (!$annee_depart) {
            // si $annee_depart n'est pas prcise, on prend l'anne fournie
            // si elle est <=  l'anne courante, sinon l'anne courante.
            if ($monAnnee > $notreAnnee) {
                $debut = $notreAnnee;
            } else {
                $debut = $monAnnee;
            }
        } else {
            //  partir de $annee_depart si $annee_depart est prcise
            $debut = $annee_depart;
        }
    }
    $Select .= "<select name='".$nom."_a'>\n";
    $Select .= "<option value=''>Ann&eacute;e</option>\n";

    for ($y = $debut; $y < $fin; $y++) {
        $Select .= "<option value=$y";
        if ($y == $monAnnee) {
            $Select .= " selected=\"selected\">$y</option>\n";
        }
        else {
            $Select .= ">$y</option>\n";
        }
    }
    $Select .= "</select>\n";

    return $Select;
} // end SelectDate
//---------------------------------------------------------------------------------
function Condition($op,$valeur) {
    //
    // Cette fonction construit une expression oprande valeur et renvoie un résultat
    // sous forme d'un tableau
    //   - [0] requête SQL
    //   - [1] requête en language clair
    //
    // Les paramtres sont les suivants :
    // type = char ou num
    // Pour un type char :
    //		contient -> like '%valeur%'
    //		%		 -> like 'valeur%'
    //		=		 -> = 'valeur'
    //		vide	-> valeur
    // Pour un type num :
    //		op		 -> op valeur
    //		vide	-> valeur
    //

    switch ($op) {
        case "contient":
            $resultat = "like '%".$valeur."%'";
            $requete = "contient&nbsp;'...".$valeur."...'";
            break;
        case "termine":
        case "se termine":
        case "se termine par":
            $resultat = "like '%".$valeur."' ";
            $requete = "se&nbsp;termine&nbsp;par&nbsp; '...".$valeur."'&nbsp;";
            break;
        case "%":
        case "like":
        case "commence":
        case "commence par":
            $resultat = "like '".$valeur."%'";
            $requete = "commence&nbsp;par&nbsp;'".$valeur."...'&nbsp;";
            break;
        default:
            $resultat = " ".$op." '".$valeur."' ";
            $requete = $resultat;
            break;
    } // switch ($op)

    return array($resultat, $requete);
}
//---------------------------------------------------------------------------------
function Erreur($champ, $erreur) {
    //
    // Cette fonction renvoie le libellé du champ de saisie en rouge
    // pour signaler un champ obligatoire, en couleur par défaut sinon.
    //
    if ($erreur == 1) {
        return "<span style=\"color: #FF0000;font-weight: bold;\">$champ</span>";
    }
    else {
        return "$champ";
    }
}
//---------------------------------------------------------------------------------
function encode_texte ($texte) {
    //
    // Cette fonction reoit un texte et l'encode pour l'affichage html
    // s'il s'agit dj de texte balis html, on ne fait que la conversion des accentus.
    // s'il s'agit de texte non balis html, on fait la convertion des caractres et des sauts de ligne.
    //
    // Vive PHP
    // On va convertir les caractres pour affichage HTML, sauf < et > pour ne pas pter les tags
    // Coup de bol pour une fois, ces deux caractres sont en fin de tableau !
    $convertion = get_html_translation_table (HTML_ENTITIES);
    $convertion = array_slice($convertion, 0, count($convertion) - 4);
    //
    $texte = stripslashes($texte);
    $texte = str_replace("&", "&amp;", $texte);
    //$texte = nl2br(strtr($texte, $convertion));
    $texte = strtr($texte, $convertion);
    if (!strpos($texte, "</")) {
        $texte = nl2br($texte);
    }
//return ereg_replace('Õ', "&rsquo;", $texte);
    return $texte;
}
//---------------------------------------------------------------------------------
function Pagination ($ma_fonction, $n_total, $n_page, $enr_debut) {
    //
    // Cette fonction renvoie une liste de pages sur une ligne en format html
    // Elle reoit les paramtres suivants :
    //  - $ma_fonction = valeur de l'argument fonction  renvoyer dans l'url
    //  - $n_total = total des rponses  la requte sans LIMIT
    //  - $n_page = le nombre de rponses  afficher par page
    //  - $enr_debut = rang de l'enregistrement dbut de la page courante.
    //
    // Pour rester dans une largeur raisonnable, le nombre de pages ne doit pas dpasser 8
    // On rectifie donc le nombre page en fonction du total
    /*
    if (($n_total / $n_page) > 8) {
        $n_page = ceil($n_total / 8);
    }
    */
    $nbre_pages = ($n_total / $n_page) + 1;

    $pages = array ();
    for ($page = 1; $page < $nbre_pages; $page += 1) {
        $debut_page = ($page * $n_page) - ($n_page - 1);
        $fin_page = ($page * $n_page);
        if ($fin_page > $n_total) {
            $fin_page = $n_total;
        }
        array_push($pages, array ($debut_page,$fin_page));
    }
//print_r $pages;
    $pagination = "";
    $compteur = 0;

    foreach($pages as $key=>$value) {
        $compteur++;
        if ($value[0] != $enr_debut) {
            $pagination .= "| <a href='".$PHP_SELF."?fonction=$ma_fonction&amp;total=$n_total&amp;debut=$value[0]&amp;fin=$value[1]'>".$value[0]."-".$value[1]." ";
            $pagination .= "</a> ";
        } else {
            $pagination .= "| $value[0]-$value[1] ";
        }

        if ($compteur > 15) {
            $pagination .= "|\n<br />\n";
            $compteur = 0;
        }

    }
    $pagination .= "|";

    return $pagination;
}

function mac2win($string) {
    // Convertion des catactres pour le mail
    return strtr($string, "", "éèËàêôîïëöûü");
}

function ascii($string) {
    // Convertion des catactères en US-ASCII pour un URL
    return strtolower(strtr($string, " éèêëàùçÉÈÊËÀÇ", "_eeeeaucEEEEAC"));
}

function mail_valide ($email) {
    // Controle la validit d'une adresse mail
    //
    // Structure
    if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", trim($email))) {
        //if (ereg("^[-.a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]+$", trim($email))) {
        // structure OK on controle le domaine
        $monDomaine = substr($email, strpos($email , "@") + 1);
        if (checkdnsrr($monDomaine) == 1) return '1';
    } else {
        return '2';
    }
}

function envoi_mail ($expediteur, $sujet, $destinataire, $message) {
    //
    // Permet l'envoi de mails sans passer par sendmail
    // Le compte de l'expditeur doit exister sur le serveur mail indiqu
    //
    //header('Content-Type: text/plain');
    $params['host'] = 'mail.monsoft.com';	// The smtp server host/ip
    $params['port'] = 25;					// The smtp server port
    $params['helo'] = exec('monsoft.com');	// What to use when sending the helo command. Typically, your domain/hostname
    $params['auth'] = TRUE;					// Whether to use basic authentication or not
    $params['user'] = 'lw';					// Username for authentication
    $params['pass'] = 'wrdwrd';				// Password for authentication

    /***************************************
     ** These parameters get passed to the
     ** smtp->send() call.
     ***************************************/

    $send_params['recipients'] = array($destinataire);	// Destinataire (peuvent tre multiples)
    $send_params['headers'] = array(
        "Content-Type: text/plain; charset=\"iso-8859-1\"",
        "Content-Transfer-Encoding: 8bit",
        "From: <$expediteur>",	// Headers
        "To: $destinataire",
        "Subject: ".$sujet
    );
    $send_params['from']		= $expediteur;			// This is used as in the MAIL FROM: cmd
    // It should end up as the Return-Path: header
    $send_params['body']		= $message;				// The body of the email


    /***************************************
     ** The code that creates the object and
     ** sends the email.
     ***************************************/

    if(is_object($smtp = smtp::connect($params)) AND $smtp->send($send_params)) {
        $reponse = 'Email sent successfully!'."\r\n\r\n";
        // Any recipients that failed (relaying denied for example) will be logged in the errors variable.
        //print_r($smtp->errors);
    } else {
        $reponse = 'Error sending mail'."\r\n\r\n";
        // The reason for failure should be in the errors variable
        //print_r($smtp->errors);
    }
    return $smtp->errors;
}

//---------------------------------------------------------------------------------
function Ctrl_Jour ($monAnnee, $monMois, $monJour) {
    //
    // Cette fonction reoit une date au format ci-dessus
    // et retourne la mme date ventuellement corrige
    // au format mySQL YYYY-MM-DD
    // On contrle la cohrence du nombre de jours par mois en corrigeant les erreurs !
    //
    $NbreJours = Date_Calc::daysInMonth($monMois,$monAnnee);
    if ($monJour > $NbreJours)
        $monJour = $NbreJours;
    $maDate = $monAnnee."-".str_pad($monMois, 2, "000", STR_PAD_LEFT)."-".str_pad($monJour, 2, "000", STR_PAD_LEFT);

    return $maDate;
}

function format_tel($tel) {
    $ch = 10;                               // Numéro à 10 chiffres
    $tel = eregi_replace('[^0-9]',"",$tel); // supression sauf chiffres
    $tel = trim($tel);                      // suppression espaces avant et après
    if (strlen($tel) > $ch) {
        $d = strlen($tel) - $ch; // retrouve la position pour ne garder que les $ch derniers
    } else {
        $d = 0;
    }
    $tel = substr($tel,$d,$ch); // récupération des $ch derniers chiffres
    $regex = '([0-9]{1,2})([0-9]{1,2})([0-9]{1,2})([0-9]{1,2})([0-9]{1,2})$';
    //$newtel = eregi_replace($regex, '\\1-\\2-\\3-\\4-\\5',$tel); // mise en forme avec "-"
    $newtel = eregi_replace($regex, '\\1 \\2 \\3 \\4 \\5',$tel); // mise en forme avec "-"
    return $newtel; /* Exemple : 03-81-51-45-78  */
}

function format_prix($my_num) {
    return number_format($my_num,2,","," ");
}

function format_date_fr ($maDate, $monHeure=0) {
    if ($monHeure == 1) {
        return date("d/m/Y - H:i", strtotime($maDate));
    } else {
        return date("d/m/Y", strtotime($maDate));
    }
}

function format_date_mysql_fr ($maDate, $monJour=1) {
    // renvoie une date mysql sous la forme jj mois aaaa
    list($annee, $mois, $jour) = split('[/.-]', $maDate);
    //
    if ($annee == "0000") $annee = "????";
    if ($mois == "00") {
        $mois = "????";
    } else {
        settype($mois, "integer");
    }
    if ($jour == "00") $jour = "??";
    //
    return $jour."&nbsp;".Date_Calc::getMonthFullname($mois)."&nbsp;".$annee;
}

function format_date_timestamp ($maDate) {
    // renvoie une date mysql sous la forme timestamp
    // date fournie en YYYY-MM-JJ H:M:S
    list($date, $time) = explode(" ", $maDate);
    list($annee, $mois, $jour) = explode("-", $date);
    list($heures, $minutes, $secondes) = explode(":", $time);
    //return mktime(settype($heures, "integer"), settype($minutes, "integer"), settype($secondes, "integer"), settype($mois, "integer"), settype($jour, "integer"), settype($annee, "integer"));
    return mktime($heures, $minutes, $secondes, $mois, $jour,$annee);
}

function format_date_mysql ($jour, $mois, $annee) {
    return $annee."-".str_pad($mois, 2, "0", STR_PAD_LEFT)."-".str_pad($jour, 2, "0", STR_PAD_LEFT);
}

function format_heure ($maDate) {
    return date("H:i", strtotime($maDate));
}

function duree ($d_debut, $d_fin=NULL) {
    //
    // Renvoie la durée écoulée entre la date/time fournie au format mysql et l'instant présent
    // au format JJ:HH:MM:SS
    //
    // Calcul du temps écoulé
    $zeros = "0000";
    $debut = format_date_timestamp($d_debut);
    //
    if (!isset($d_fin)) {
        $maintenant = date("Y-m-d H:i:s");
        $fin = format_date_timestamp($maintenant);
    } else {
        $fin = format_date_timestamp($d_fin);
    }
    $duree = $fin - $debut;
    //
    $jours = floor($duree / 86400); //Calcul des jours écoulés
    $duree = $duree - ($jours * 86400);
    $jours = substr($zeros.$jours, -2);

    $heures = floor($duree / 3600); //Calcul des heures écoulées
    $duree = $duree - ($heures * 3600);
    $heures = substr($zeros.$heures, -2);

    $minutes = floor($duree / 60); //Calcul des minutes écoulées
    $duree = $duree - ($minutes * 60);
    $minutes = substr($zeros.$minutes, -2);

    $secondes =$duree; //Calcul des secondes écoulées
    $secondes = substr($zeros.$secondes, -2);
    //

//echo $heures.":".$minutes.":".$secondes; exit;
    return ($jours.":".$heures.":".$minutes.":".$secondes);
}

function format_duree ($maDuree) {
    $laps=explode(":",$maDuree);
    return ($laps[1]."h".$laps[2]."m");
}


function limite ($chaine, $taille) {
    // renvoie une chane tronque  la taille indique
    if (strlen($chaine) > $taille) {
        return substr($chaine, 0, $taille)."...";
    } else {
        return $chaine;
    }
}


function password ($taille=8) {
    // Génération d'un mot de passe unique
    // La taille est optionnelle et transmise en argument
    global $maConnection;

    $pwd = substr(md5(uniqid(rand())), 0, $taille);
    $existe = ExecRequete("select adr_id from adresses where adr_adh_password='$pwd';", $maConnection);
    if(mysqli_num_rows($existe) > 0) {
        password ();
    } else {
        return $pwd;
    }
}

function dateFR( $time) {
    return strftime(' %D &agrave; %Hh %Mmin ', strtotime($time));
}
function dateFR_( $time) {
    return strftime('%A %d %B &agrave; %Hh %Mmin %Ss', strtotime($time));
}

function  import_request_variables_54($s='GP',$pre="e") { // fonction deprecie dans php 5.4 mais toujours présente dans php 5.3
    extract($_REQUEST);
}


?>