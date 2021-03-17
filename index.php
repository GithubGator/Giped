<?php


    require_once("./_session.php");
	require_once("./_dbclass.php");
	require_once("./_connect.php"); // Paramètres de connection et... connection
	require_once("./_fonctions.php");


setlocale(LC_ALL, 'fr_FR.utf8');
date_default_timezone_set('Europe/Paris');
//die('ici');
error_reporting(E_ALL ^ E_NOTICE);
extract($_REQUEST,EXTR_PREFIX_ALL,"e");
//
ini_set('display_errors', 0);


ini_set('max_execution_time','0');
ini_set('memory_limit','-1');
$bFiche=false;



if(isset($e_id) && isset($e_decret) ){
    /*
    echo "id=".$e_id;
    echo "decret=".$e_decret;
    exit();
    */
    $monImportation = new importationsXML;

    if($e_decret=="2016")
        $monImportation ->get($e_id, $maConnection);
    else
        $monImportation ->get($e_id, $maConnection1);

    $bFiche=true;

    /*
    $monImportation->code_departement = $numDept;
    $monImportation->annee =$anneeRef;
    $monImportation->nom_fichier_ini=$e_file;
    $monImportation->nom_fichier_traduit =$csvfile;
    $monImportation->num_lignes =$i;
    $monImportation->commentaires = $commentaire ;
    $monImportation->date_importation= date ("Y-m-d H:i:s.", filemtime($xmlfile));
    $monImportation->date_traitement = date("Y-m-d H:i:s");
    */


}else{

    $sql = "select * from  importationsXML where  1";
    if(isset($e_codeDept) && $e_codeDept !="")
        $sql .= " and code_departement ='". $e_codeDept."' ";
    if(isset($e_annee) && $e_annee !="")
        $sql .= " and annee ='".$e_annee."' ";

    if((!isset($e_codeDept)|| $e_codeDept =="") && (isset($e_annee)|| $e_annee !=""))
        $sql .= " group by code_departement ";

    $sql .= " order by annee DESC, date_importation DESC, id_importation DESC"; // dans l'ordre de création

    //echo $sql;

    if($e_decret =="2016"){
        $mesImportations = $maConnection->query($sql);
    }else{
        $mesImportations = $maConnection1->query($sql);
    }
    $nbRecords = $mesImportations->num_rows;

    echo "<!-- ".$e_decret."-".$sql." -->";

}


?>
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>




    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>
        Remont&eacute; des donn&eacute;es	</title>
    <!-- appel des css -->
    <link rel="stylesheet" href="styles/css_bleu/style_bleu.css" type="text/css" title="css_bleu" media="screen">
    <link rel="stylesheet" href="styles/css_bleu/style_print.css" type="text/css" title="css_bleu" media="print">



    <style type="text/css">
        <!--
        #main_pec table {
            border-collapse: collapse;
            text-align:center;
            font-size: 1.1em;
        }

        #main_pec table td {
            border: solid black 1px;
            padding:0 10px 0 10px;


        }
        #main_pec table th {
            background: #AACCEE;
            border: 1px solid #565248;
            color: #000;
        }
        tr.pair {
            background-color: #ECE9D8;
        }

        tr.impair {
            background-color: #FFFFE1;
        }



        #main_pec {
            margin: 100px 20px 0 20px;
            padding: 40px;
            -moz-border-radius: 10px 10px 0 0;
            border-radius: 10px 10px 0  0 ;
            background: #fff ;

        }

        p#footer_pec {
            /* padding: 10px 20px;
            */

            padding-top: 0;
            padding-bottom: 10px;
            margin: 0 20px 0 20px;
            clear: both;
            background: #AACCEE ;
            -moz-border-radius: 0 0 10px 10px;
            border-radius: 0 0 10px 10px ;
            border-top: 1px dotted #5F7A94;
            text-align: center;
        }

        .Critere{
            padding: 4px;
            width: 910px;
            height: 32px;
            border: 1px solid #565248;
            border-radius: 6px  ;
            margin-bottom : 2px;
            /*background: #D9DC2C; vert ONED*/
            background: #FAD805; jaune ONED
            /*background: #FFF28E;*/
            background-repeat:no-repeat;
            background-position:6px 10px;

        }
        .Critere p{
            margin-left:60px;
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }


        .Ligne{
            padding: 4px;
            width: 910px;
            height: 28px;
            border: 1px solid #565248;
            border-radius: 6px  ;
            margin-bottom : 2px;
            /*background: #D9DC2C; vert ONED
            background: #FAD805; jaune ONED*/
            background: #FFF28E;
            background-image:url(images/fiche1.png);
            background-repeat:no-repeat;
            background-position:6px 10px;

        }
        .Ligne p{
            margin-left:60px;
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }

        .LigneVert{
            padding: 4px;
            width: 910px;
            height: 28px;
            border: 1px solid #565248;
            border-radius: 6px  ;
            margin-bottom : 2px;
            /*background: #D9DC2C; vert ONED
            background: #FAD805; jaune ONED*/
            background: #87c966;
            background-image:url(images/fiche1.png);
            background-repeat:no-repeat;
            background-position:6px 10px;

        }
        .LigneVert p{
            margin-left:60px;
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }
        .cell0{
            width:60px;
            display: inline-block;
            margin-left:40px;
            margin-top:6px;
        }
        .cell1{
            width:240px;
            display: inline-block;
            margin-left:30px;
            margin-top: 6px;
        }
        .Resume{
            padding: 4px;
            width: 600px;
            height: 240px;
            border: 1px solid #565248;
            -moz-border-radius: 6px ;
            border-radius: 6px  ;
            /*background: #D9DC2C; vert ONED
            background: #FAD805; jaune ONED*/
            background: #FFF28E;
            background-image:url(images/note4.png);
            background-repeat:no-repeat;
            background-position:6px 10px;

        }

        .Note{
            padding: 4px;
            width: 600px;
            height: 400px;
            border: 1px solid #565248;
            -moz-border-radius: 6px ;
            border-radius: 6px  ;
            /*background: #D9DC2C; vert ONED
            background: #FAD805; jaune ONED*/
            background: #FFF28E;
            background-image:url(images/note4.png);
            background-repeat:no-repeat;
            background-position:6px 10px;

        }
        .Note p{
            margin-left:60px;
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }

        .Resume p{
            margin-left:60px;
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }

        .thumbNote{
            padding: 4px;
            width: 600px;
            height: 60px;
            border: 1px solid #565248;
            -moz-border-radius: 6px ;
            border-radius: 6px  ;
            background: #ccc ;
            background-image:url(images/note4.png);
            background-repeat:no-repeat;
            background-position:6px center;

        }

        .thumbNote a{
            display:block;
            text-decoration: none;
            width:100%;
            height:100%;
        }
        .thumbNote p{
            margin-left:60px;
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }

        .thumbPDF{
            display:block;
            padding: 4px;
            width: 600px;
            height: 60px;
            border: 1px solid #565248;
            -moz-border-radius: 6px ;
            border-radius: 6px  ;
            background: #87c966 ;
            background-image:url(images/pdf4.png);
            background-repeat:no-repeat;
            background-position: 6px center;

        }

        .thumbPDF a{
            display:block;
            text-decoration: none;
            width:100%;
            height:100%;
        }

        .thumbPDF p{
            margin-left:60px;

            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }

        .thumbOoo{
            padding: 4px;
            width: 600px;
            height: 60px;
            border: 1px solid #565248;
            -moz-border-radius: 6px ;
            border-radius: 6px  ;
            background: #ccc ;
            background-image:url(images/Ooo4.png);
            background-repeat:no-repeat;
            background-position: 6px center;

        }

        .thumbExcel{
            padding: 4px;
            width: 600px;
            height: 60px;
            border: 1px solid #565248;
            -moz-border-radius: 6px ;
            border-radius: 6px  ;
            background: #ccc ;
            background-image:url(images/excel4.png);
            background-repeat:no-repeat;
            background-position:6px center;

        }

        .thumbExcel a{
            display:block;
            text-decoration: none;
            width:100%;
            height:100%;
        }
        .thumbExcel p{
            margin-left:60px;
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }
        .thumbCsv{
            padding: 4px;
            width: 600px;
            height: 60px;
            border: 1px solid #565248;
            -moz-border-radius: 6px ;
            border-radius: 6px  ;
            background: #ccc ;
            background-image:url(images/csv4.png);
            background-repeat:no-repeat;
            background-position:6px center;

        }

        .thumbCsv a{
            display:block;
            text-decoration: none;
            width:100%;
            height:100%;
        }
        .thumbCsv p{
            margin-left:60px;
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 1.1em;

        }

        #logoONED{
            position:absolute;
            top: 100px;
            right: 100px;
            width: 240px;
            height: 152px;
            background: #ccc ;
            background-image:url(images/Logo_onpe_150.png);
            background-size: contain;
        }

        -->
    </style>


    <script type="text/javascript">
        <!-- Remplacer entrée historique

        //-->
    </script>

</head><body>
<!-- appels -->
<div>
    <span><a name="Top"></a></span>
</div>

<div id="logoONED"></div>

<div id="main_pec" style="margin-top: 20px;">


    <div>  <!-- id = "formulaire" -->

        <br style="clear:both;" />

        <?php if($bFiche){?>
        <div class="Resume">
            <p>Remont&eacute;e de donn&eacute;es du d&eacute;partement <?php echo $monImportation->code_departement ?> :</p>

            <?php

            echo "<div><div class=\"cell1\">Ann&eacute;e concern&eacute;e </div><div class=\"cell1\">".$monImportation->annee."</div></div>";
            if($e_decret =="2016")
                echo "<div><div class=\"cell1\">En application du d&eacutecret</div><div class=\"cell1\"> 2016 </div></div>";
            else
                echo "<div><div class=\"cell1\">En application du d&eacutecret</div><div class=\"cell1\"> 2011 </div></div>";
            echo "<div><div class=\"cell1\">Date de l'importation  </div><div class=\"cell1\">".$monImportation->date_importation."</div></div>";
            echo "<div><div class=\"cell1\">Date du traitement</div><div class=\"cell1\">".$monImportation->date_traitement."</div></div>";
            echo "<div><div class=\"cell1\">Nom du fichier initial </div><div class=\"cell1\">".$monImportation->nom_fichier_ini."</div></div>";
            echo "<div><div class=\"cell1\">Nombre de lignes </div><div class=\"cell1\">".$monImportation->num_lignes."</div></div>";
            ?>

        </div><br />

        <br style="clear:both;" />
        <?php

        $pdfAudit = $monImportation->nom_fichier_traduit;
        $pdfAudit = substr($pdfAudit,0,-3)."pdf";
        if (file_exists( "./".$monImportation->annee."/".$pdfAudit)) {
            ?>
            <div class="thumbPDF"><a href="<?php echo  "./".$monImportation->annee."/".$pdfAudit?>">
                    <p>Visualiser le rapport d'audit (<?php echo $pdfAudit?>)</p></a></div><br />


            <br style="clear:both;" />
        <?php } ?>

        <div class="thumbCsv"><a href="./telecharge.php?csv=<?php echo $monImportation->nom_fichier_traduit."&amp;anneeRef=".$monImportation->annee?>">
                <p>T&eacute;l&eacute;charger le fichier CSV (<?php echo $monImportation->nom_fichier_traduit?>)</p> </a></div><br />

        <br style="clear:both;" />

        <?php

        $csvAudit = $monImportation->nom_fichier_traduit;
        $csvAudit = substr($csvAudit,0,-4)."_Verif.csv";
        if (file_exists( "./".$monImportation->annee."/".$csvAudit)) {
            ?>
            <div class="thumbCsv"><a href="./telecharge.php?csv=<?php echo $csvAudit."&amp;anneeRef=".$monImportation->annee?>">
                    <p>T&eacute;l&eacute;charger le fichier d'anomalies (<?php echo $csvAudit?>)</p> </a></div><br />

            <br style="clear:both;" />
        <?php } ?>

        <?php
        $h = round((strlen($monImportation->commentaires)/25)*20) +50;
        $h.= "px";

        //$h="400px";
        ?>

        <div class="Note" style ="height:<?php echo $h?>">
            <p>Note</p>
            <?php if($e_decret ==2011) {?>
            <p><?php echo str_replace("\n", "<br />",$monImportation->commentaires) ?></p></div><br />
        <?php } else{?>
        <p><?php echo str_replace("\n", "<br />",$monImportation->commentaires) ?></p></div><br />
    <?php }?>
    <br style="clear:both;" />
    <div class="thumbPDF"><a href="./telecharge.php?pdf=Guide_Olinpe_MAJ2018.pdf">
            <p>Guide OLINPE (Version  du 20/09/2018)</p></a></div><br />

    <a href=".<?php echo $_SERVER['PHP_SELF'];?>?decret=2016&codeDept=<?php echo $monImportation->code_departement ?>" > Autre(s) fichier(s) import&eacute;(s) pour ce même d&eacute;partement au format 2016</a><br /><br />
    <a href=".<?php echo $_SERVER['PHP_SELF'];?>?decret=2011&codeDept=<?php echo $monImportation->code_departement ?>" > Autre(s) fichier(s) import&eacute;(s) pour ce même d&eacute;partement au format 2011</a><br /><br />
    <a href=".<?php echo $_SERVER['PHP_SELF'];?>?decret=<?php echo $e_decret ?>"> Retour &agrave; la liste des importations</a>

    <?php }else {
        echo "<!--";
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        echo " -->";
        $url =".".$_SERVER['PHP_SELF']."?decret=".$e_decret."&id=";
        $monImportation = new importationsXML;
        //$id = $monImportation->code_departement;
        $id=$dept_num;
        echo "<form method=\"post\" action=\".".$_SERVER['PHP_SELF']."\"> ";
        echo "<div class=\"Critere\"><div class=\"cell10\">";
        echo "&nbsp;" .select_NUMDEP($e_codeDept);
        echo "&nbsp;".select_annee($e_annee);
        echo "&nbsp;D&eacute;cret : ".select_decret($e_decret);
        echo "&nbsp; Nombre de r&eacute;sultats : ".$nbRecords;
        echo "</div></div>";
        echo "</form>";

        $bufAnnee ="";
        $bufDepartement="";

        while($record = $mesImportations->fetch_assoc()) {
            $monImportation->readvars($record);
            if(isset($e_codeDept) && $e_codeDept !=""){
                if($monImportation->annee != $bufAnnee){
                    //première ligne pour cette année là
                    $class ="LigneVert";
                    $bufAnnee=$monImportation->annee;
                }else{
                    //autre importation ou traitement plus ancien
                    $class="Ligne";
                }
            }else{
                $class="Ligne";
            }

            echo "<a href=\"".$url.$monImportation->id_importation."\"><div class=\"".$class."\"><div class=\"cell0\">Dept ".$monImportation->code_departement ."</div>";
            echo "<div class=\"cell1\" >Transmis le ".$monImportation->date_importation."</div>";

            echo "<div class=\"cell1\" >Ann&eacute;e concern&eacute;e ".$monImportation->annee."</div>";
            echo "<div class=\"cell1\" >Trait&eacute le ".$monImportation->date_traitement."</div>";
            echo "</div></a>";


        }
        ?>




    <?php }?>
</div> <!-- end div id="formulaire" -->




</div> <!-- end div id="main" -->


<p id="footer_pec">
    <br style="clear:both;" />
    |&nbsp;<a href="mailto:informatique@giped.gouv.fr;">Contact</a>&nbsp;|
</p>

</body>
</html>

<?php

function select_NUMDEP ($idDept) {

    global $maConnection;

    $sql = "select * from ref_NUMDEP ";
    //$sql .= "inner join adresses on(adr_depts.id_adr_dept = adresses.id_adr_dept) ";
    $sql .= " where 1 ";

    $sql .= "order by id asc ";
    $result = ExecRequete($sql, $maConnection);
    //
    $select = "\n<select name='codeDept' id='codeDept'  onchange='submit()' \">\n ";
    $select .= "\n<option value=\"\">&nbsp;D&eacute;partement</option>\n";
    //
    while($option = $result->fetch_assoc()) {
        $select .= "\n<option value=\"".$option["id"]."\"";
        if ($option["id"] == $idDept) $select .= " selected=\"selected\"";
        $select .= ">";
        $select.= "&nbsp;".$option["id"]." - ".$option["libelle"];
        $select .= "</option>\n";
    }
    $select .= "\n</select>\n";

    return $select;
}

function select_annee ($annee) {
    $anneeEnCours = (int)(date('Y'));

    //
    $select = "\n<select name='annee' id='annee' onchange='submit()' \">\n ";
    $select .= "\n<option value=\"\">&nbsp;Ann&eacute;e concern&eacute;e</option>\n";
    //
    for($i = $anneeEnCours;$i >2011;$i--){
        $select .= "\n<option value=\"".$i."\"";
        if ($i == $annee) $select .= " selected=\"selected\"";
        $select .= ">";
        $select.= "&nbsp;".$i;
        $select .= "</option>\n";
    }
    $select .= "\n</select>\n";

    return $select;
}
function select_decret ($e_decret) {
    //
    $select = "\n<select name='decret' id='decret' onchange='submit()' \">\n ";
    $select .= "\n<option value=\"\">&nbsp;D&eacute;cret concern&eacute;</option>\n";
    //
    $select .= "\n<option value=\"2016\"";
    if ($e_decret == "2016") $select .= " selected=\"selected\"";
    $select .= ">&nbsp;2016</option>\n";

    $select .= "\n<option value=\"2011\"";
    if ($e_decret == "2011" || $e_decret =="" || !isset($e_decret)) $select .= " selected=\"selected\"";
    $select .= ">&nbsp;2011</option>\n";

    $select .= "\n</select>\n";

    return $select;
}


?>
