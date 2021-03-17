<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	extract($_REQUEST);
	//print_r($_POST);
	//echo $ageLimite;
	
	// Valeurs des seuils , n'affecte que l'affichage
	if(!isset($seuilES))
		$seuilES = 0; 		// seuil en dessous duquel les apports ou sortie ne sont pas affichées (ni valeurs, ni  flèches
	if(!isset($seuilFlux))
		$seuilFlux = 100; 	// seuil en dessous duquel les valeurs de flux ne sont pas affichées et que la couleur est grisée
	if(!isset($seuilES))
		$seuilFlux = 0; 	// seuil en dessous duquel les valeurs de stock ne sont pas affichées et que la couleur est grisée
	
	if(!isset($ageLimiteSup))
		$ageLimiteSup = 12 ; 	
	if(!isset($ageLimiteInf) || $ageLimiteInf <0)
		$ageLimiteInf = 0 ; 
	
	if($ageLimiteInf  >= $ageLimiteSup)
		$ageLimiteSup = $ageLimiteInf +1;
		
	if($ageLimiteSup > 21)
		$ageLimiteSup=21;

?>
<!-- Giped -->
<style type="text/css">
	<!--
	body {
		background-color: white;
	}

	h2 {
		color:blue;
		margin-left: 200px;
		
	}
	#main{
		width: 1300px;
	}
	#intro {
		float:left;
		width: 800px;
		border: 1px solid black;
		margin-left: 100px;
		padding:10px;
	}
	#seuils {
		float:right;
		width: 300px;
		border: 1px solid black;
		padding:10px;

	}
	-->
	</style>
<body> 
<h2 >Parcours simplifi&eacute; des enfants</h2>
<div id="main">
<div id="intro">
Cette repr&eacute;sentation des parcours est construite dynamiquement sur la base du document fourni par Vincent Spiesser, avec quelques variantes.<br />
De 0 à 4 ans, les donn&eacute;es proviennent du Finist&egrave;re ensuite, pour les besoins de cet exemple, les donn&eacute;es sont reproduites &agrave; l'identique.
Suivant l'age limite défini, l'ensemble des parcours est visualisable en scrollant.<br/>
Conceptuellement, le graphique se construit à partir d'un tableau des stocks constatés à l'age n-1 et des flux survenus entre l'age n-1 et l'age n.
Le système calcule le stock à l'année n comme tableau initial pour l'étape n+1.
Le jeux de données provient d'une base de référence, une requète.
Il faut donc dans un premier temps constituer plusieurs tableaux (1 par age &eacute;tudi&eacute;) regroupant les flux. <br />
<b>Lecture</b><br />
 Nous considerons que le stock à l'age de 2 ans est constitué de : <br />
 9 nouveaux entrants <br />
 5 mineurs qui bénéficiait jusqu'alors d'une prestation adminisrative &agrave; domicile <br />
 aucun mineur ayant b&eacute;n&eacute;fici&eacute; d'une prestation administrative de placement <br />
 4 d'une mesure judiciaire &agrave; domicile <br />
 21 mineurs pour lesquel la mesure est renouvel&eacute;e <br />
 (2 mineurs &eacute;tant sortis depuis du champ de la protection de l'enfance.)<br />
Le total : 9+5+4+21 = 39 <br />

Pour am&eacute;liorer la lisibilit&eacute; de ce diagramme, il est possible de d&eacute;finir des seuils en dessous desquels l'affichage ne sera pas effectué.

En l'&eacute;tat, toute remarque/critique est bienvenue.<br />
 <br />Il est possible de zoomer Ctrl + ou de réduire Ctrl -

</div>
<form method="post" action="module.php" id="form1" name="form1">
<div id="seuils">
	<table>
		<tr>
			<td>
			<label for="seuilES">Seuil pour les entr&eacute;es/sorties</label>
			</td>
			<td>
			<input type="number" name="seuilES" id="seuilES" value="<?php echo $seuilES?>" style="width:60px;">
			</td>
		</tr>
		<tr>
			<td>
			<label for="SeuilFlux">Seuil pour les Flux</label>
			</td>
			<td>
			<input type="number" name="seuilFlux" id="seuilFlux" value="<?php echo $seuilFlux?>" style="width:60px;">
			</td>
		</tr>
		<tr>
			<td>
			<label for="SeuilStock">Seuil pour les Stocks</label>
			</td>
			<td>
			<input type="number" name="seuilStock" id="seuilStock" value="<?php echo $seuilStock?>" style="width:60px;">
			</td>
		</tr>
		<tr>
			<td>
			<label for="ageLimiteSup">Age limite Sup</label>
			</td>
			<td>
			<input type="number" name="ageLimiteSup" id="ageLimiteSup" value="<?php echo $ageLimiteSup?>" style="width:60px;">
			</td>
		</tr>
		<tr>
			<td>
			<label for="ageLimiteInf">Age limite Inf</label>
			</td>
			<td>
			<input type="number" name="ageLimiteInf" id="ageLimiteInf" value="<?php echo $ageLimiteInf?>" style="width:60px;">
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<center>
			<input type="submit" value="Valider">
			</center>
			</td>
		</tr>
	</table>
</div>
<input type="hidden" name="param" id="param" value="123" />
	
</form>
</div>

<?php


if(isset($_GET['nb']) && $nb >= 0){
	$nb=$_GET['nb'];
}else{
	$nb=12;
}
 $legendeX=array(
  0 => "A la naissance",
  1 => "A 1 an",
  2 => "A 2 ans",
  3 => "A 3 ans",
  4 => "A 4 ans",
  5 => "A 5 ans",
  6 => "A 6 ans",
  7 => "A 7 ans",
  8 => "A 8 ans",
  9 => "A 9 ans",
  10 => "A 10 ans",
  11 => "A 11 ans",
  12 => "A 12 ans",
  13 => "A 13 ans",
  14 => "A 14 ans",
  15 => "A 15 ans",
  16 => "A 16 ans",
  17 => "A 17 ans",
  18 => "A 18 ans",
  19=> "A 19 ans",
  20 => "A 20 ans",
  21 => "A 21 ans"
 );
 
  $legendeY_0=array(
  1 => "Suivi judiciaire",
  2 => "Suivi administratif"
 );
 
  $legendeY_1=array(
  1 => "Placement",
  2 => "A domicile",
  3 => "Placement",
  4 => "A domicile"
 );
 
 //print_r ($legendeX);

include ("./defs_inc.php");

 
 $Vincent =True;
 
$tableauFlux=array();
// initialisation
$FluxIni1 = array(0=>11,1=>0, 2=>0,3=>0,4=>0,99=>0);
$FluxIni2 = array(0=>2,1=>0, 2=>0,3=>0,4=>0,99=>0);
$FluxIni3 = array(0=>2,1=>0, 2=>0,3=>0,4=>0,99=>0);
$FluxIni4 = array(0=>13,1=>0, 2=>0,3=>0,4=>0,99=>0);

$tableauFlux[0] = array(1=>$FluxIni1,2=>$FluxIni2,3=>$FluxIni3,4=>$FluxIni4);

/*
key :
0: Flux entrant
1 : flux en provenance de tabStock[1];
2 : flux en provenance de tabStock[2];
3 : flux en provenance de tabStock[3];
4 : flux en provenance de tabStock[4];
99 : flux sortant;
*/

if($Vincent){  //...Données Finistère (Vincent)
	

	// A 1 an
	$Flux1 = array(0=>10,1=>11, 2=>0,3=>0,4=>0,99=>0);
	$Flux2 = array(0=>12,1=>0, 2=>0,3=>0,4=>0,99=>2);
	$Flux3 = array(0=>5,1=>2, 2=>0,3=>0,4=>0,99=>0);
	$Flux4 = array(0=>38,1=>1, 2=>0,3=>0,4=>3,99=>9);
	$tableauFlux[]= array(1=>$Flux1,2=>$Flux2,3=>$Flux3,4=>$Flux4);
		
	
	// A 2 an
	$Flux1 = array(0=>9,1=>21, 2=>1,3=>0,4=>0,99=>2);
	$Flux2 = array(0=>14,1=>4, 2=>5,3=>0,4=>0,99=>3);
	$Flux3 = array(0=>1,1=>0, 2=>0,3=>3,4=>0,99=>2);
	$Flux4 = array(0=>17,1=>5, 2=>6,3=>0,4=>13,99=>17);
	$tableauFlux[]= array(1=>$Flux1,2=>$Flux2,3=>$Flux3,4=>$Flux4);
	
		
	// A 3 an
	$Flux1 = array(0=>8,1=>34, 2=>1,3=>0,4=>0,99=>4);
	$Flux2 = array(0=>15,1=>2, 2=>15,3=>1,4=>1,99=>7);
	$Flux3 = array(0=>0,1=>1, 2=>0,3=>0,4=>1,99=>2);
	$Flux4 = array(0=>22,1=>3, 2=>3,3=>0,4=>10,99=>14);
	$tableauFlux[]= array(1=>$Flux1,2=>$Flux2,3=>$Flux3,4=>$Flux4);
	
		
	// A 4 an
	$Flux1 = array(0=>6,1=>44, 2=>0,3=>2,4=>0,99=>2);
	$Flux2 = array(0=>20,1=>3, 2=>22,3=>0,4=>0,99=>9);
	$Flux3 = array(0=>0,1=>1, 2=>0,3=>0,4=>0,99=>0);
	$Flux4 = array(0=>22,1=>1, 2=>3,3=>0,4=>12,99=>18);
	$tableauFlux[]= array(1=>$Flux1,2=>$Flux2,3=>$Flux3,4=>$Flux4);
	

	$tabFlux = array(1=>$Flux1,2=>$Flux2,3=>$Flux3,4=>$Flux4);
	for ($i = 4; $i <= $ageLimiteSup; $i++) {
		$tableauFlux[$i]=$tabFlux; 
		// en réalité $tabFlux sera différent à chaque fois
	}

}else{

	/*
	key :
	0: Flux entrant
	1 : flux en provenance de tabStock[1];
	2 : flux en provenance de tabStock[2];
	3 : flux en provenance de tabStock[3];
	4 : flux en provenance de tabStock[4];
	99 : flux sortant;
	*/



	// Exemple de flux constaté qui vont constituer le stock à l'age de 2 ans

	$Flux1 = array(0=>10,1=>11, 2=>12,3=>13,4=>14,99=>1);
	/*Lecture
	 lors de cette étape nous considerons que le stock à l'age de 2 ans est constitué de :
	 10 nouveaux entrant
	 11 mineurs qui bénéficiait jusqu'alors d'une prestation adminisrative a domicile
	 12 d'une prestaion administrative de placement
	 14 d'une mesure judiciaire à domicile
	 1 mineurs étant sorti depuis du champ de la protection de l'enfance.
	*/
	$Flux2 = array(0=>20,1=>21, 2=>22,3=>23,4=>24,99=>2);
	$Flux3 = array(0=>30,1=>31, 2=>32,3=>33,4=>34,99=>3);
	$Flux4 = array(0=>40,1=>41, 2=>42,3=>43,4=>44,99=>4);

	$tabFlux = array(1=>$Flux1,2=>$Flux2,3=>$Flux3,4=>$Flux4); // $tabFlux = tableaux des 4 flux à l'instant t considéré


	for ($i = 1; $i <= $ageLimiteSup; $i++) {
		$tableauFlux[$i]=$tabFlux; 
		// en réalité $tabFlux sera différent à chaque fois
	}

 
}


//...Traitement

for ($i = 0; $i <= $ageLimiteSup; $i++) {
		$tabFlux=$tableauFlux[$i];
		MajStock ($tabFlux); 
		if($i >= $ageLimiteInf){
			Calque($i,-30,-30,$tabFlux,$ageLimiteInf);
		}
}

function MajStock ($tabFlux){
	global $Stock ;
	for ($j=1; $j<=4;$j++){
		//$Stock[$j] += ${"Flux".$j}[0]+${"Flux".$j}[1]+${"Flux".$j}[2]+${"Flux".$j}[3]+${"Flux".$j}[4]+${"Flux".$j}[99];
		//$Stock[$j] += $tabFlux[$j][0]+$tabFlux[$j][1]+$tabFlux[$j][2]+$tabFlux[$j][3]+$tabFlux[$j][4]-$tabFlux[$j][99]; // logique "d'où viennent les situations"
		//$Stock[$j] = $tabFlux[$j][0]+$tabFlux[1][$j]+$tabFlux[2][$j]+$tabFlux[3][$j]+$tabFlux[4][$j]-$tabFlux[$j][99]; 
		// ce qui sort direct n'est pas à prendre en compte puisque pas pris en compte dans la répartition 
		$Stock[$j] = $tabFlux[$j][0]+$tabFlux[1][$j]+$tabFlux[2][$j]+$tabFlux[3][$j]+$tabFlux[4][$j]; // logique "où vont les flux"
	}
	/* Equivalent à :
	$tabStock[1] += $tabFlux1[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[2] += $tabFlux2[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[3] += $tabFlux3[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[4] += $tabFlux4[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	*/
}


  function Calque ($i,$x=0,$y=0,$tabFlux,$depart){
	global $legendeX, $legendeY_0, $legendeY_1, $Stock, $seuilFlux, $seuilES, $seuilStock;
		
		if ($i >=$depart)
			$offsetX = (($i-$depart)*70)+$x;
		//$offsetX = (($i)*70)+$x;	
		$offsetY = $y;
		  $g='
		  <g
		     transform="translate('.$offsetX.",".$offsetY.')"
		     inkscape:label="Calque 1"
		     inkscape:groupmode="layer"
		     id="layer'.$i.'">';
		 if(isset ($legendeX[$i])){  
		    $g.='    <!--Légendes axe des x -->
		      <text
		       text-anchor="middle"
		       x="92.5"
		       y="148"
		       style="font-size:3.5px;"
		       id="legende'.$i.'">'.$legendeX[$i].'</text>'; 
		  }
		if($i == $depart){
			for ($k=1;$k<=2;$k++){
				$yDecalage = 50 + $k*35.2;
				
				if(isset ($legendeY_0[$k])){  
				    $g.='    <!--Légendes axe des y -->
				      <text
				       text-anchor="end"
				       x="60"
				       y="'.$yDecalage.'"
				       style="font-size:3.5px;font-weight:bold;"
				       id="legende0-'.$k.'">'.$legendeY_0[$k].'</text>'; 
				       
				}
			}
			for ($k=1;$k<=4;$k++){
				$yDecalage = 60.3 + $k*17.6;
				if(isset ($legendeY_1[$k])){  
				    $g.='    <!--Légendes axe des y -->
				      <text
				       text-anchor="end"
				       x="86"
				       y="'.$yDecalage.'"
				       style="font-size:3.5px"
				       id="legende1-'.$k.'">'.$legendeY_1[$k].'</text>'; 
				}
			}
			
			
		}
		// x="18.609825"
		  $g.='   
		    <!-- Bargraph -->	';
		if($Stock[1] > $seuilStock){
			$g.= '	
			    <rect
			       style="opacity:0.95999995;fill:#ff4939;fill-opacity:0.50196078;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
			       id="barRouge'.$i.'"
			       width="7.7593513"
			       height="17.759354"
			       x="88.609825"
			       y="66.915871" />
			    <text
			       text-anchor="middle"
			       x="92.5"
			       y="77.886238"
			       style="font-size:3.5px"
			       id="textRouge'.$i.'">'.$Stock[1].'</text>';
		}else{
			$g.= '
				<rect
			       style="opacity:0.95999995;fill:#fff;fill-opacity:0.50196078;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
			       id="barRouge'.$i.'"
			       width="7.7593513"
			       height="17.759354"
			       x="88.609825"
			       y="66.915871" />';		
	
		}
		if($Stock[2] > $seuilStock){
			$g.= '      
		    <rect
		       style="opacity:0.95999995;fill:#f89107;fill-opacity:0.44705882;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barJaune'.$i.'"
		       width="7.7593513"
		       height="17.759354"
		       x="88.609825"
		       y="84.916145" />
		    <text
		       text-anchor="middle"
		       x="92.5"
		       y="95.345848"
		       style="font-size:3.5px"
		       id="textJaune'.$i.'">'.$Stock[2].'</text>' ;
		}else{
			$g.= '
		     <rect
		       style="opacity:0.95999995;fill:#fff;fill-opacity:0.44705882;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barJaune'.$i.'"
		       width="7.7593513"
		       height="17.759354"
		       x="88.609825"
		       y="84.916145" />';
		}
		if($Stock[3] > $seuilStock){
			$g.= '
		    <rect
		       style="opacity:0.95999995;fill:#349108;fill-opacity:0.48235294;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barVert-5"
		       width="7.7593513"
		       height="17.759354"
		       x="88.609825"
		       y="102.91663" />
		    <text
		       text-anchor="middle"
		       x="92.5"
		       y="113.12086"
		       style="font-size:3.5px"
		       id="textVert'.$i.'">'.$Stock[3].'</text>' ;
		}else{
			$g.= '
		    <rect
		       style="opacity:0.95999995;fill:#fff;fill-opacity:0.48235294;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barVert-5"
		       width="7.7593513"
		       height="17.759354"
		       x="88.609825"
		       y="102.91663" />';
		}
		if($Stock[4] > $seuilStock){
			$g.= '
		    <rect
		       style="opacity:0.95999995;fill:#3468f5;fill-opacity:0.70980392;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barBleu'.$i.'"
		       width="7.7593513"
		       height="17.759354"
		       x="88.609825"
		       y="120.91687" />
		    <text
		       text-anchor="middle"
		       x="92.5"
		       y="131.00429"
		       style="font-size:3.5px"
		       id="textBleu'.$i.'">'.$Stock[4].'</text>';
		}else{
			$g.= '
		    <rect
		       style="opacity:0.95999995;fill:#3468f5;fill-opacity:0.70980392;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barBleu'.$i.'"
		       width="7.7593513"
		       height="17.759354"
		       x="88.609825"
		       y="120.91687" />';
		}
				
		//if($i !=0){  
		if($i > $depart){
		$g.='  
		    <!-- Bifurcations--> ';
	
		$g.=' 
		    <path
		       style="opacity:0.8;fill:url(#linearGradient854-4-2);fill-opacity:1;stroke:#fc0000;stroke-width:0.26458332;stroke-opacity:1"
		       d="M 41.544553,76.655185 36.84665,73.285883 c -0.01,0.680287 -0.0152,1.360471 -0.0227,2.040698 h -8.084225 v 2.657725 h 8.072345 c 0.004,0.680057 0.0137,1.360084 0.0346,2.040178 z"
		       id="flecheHtRouge'.$i.'"
		       inkscape:connector-curvature="0" /> ';
				
		if($tabFlux[1][99] > $seuilES ){ //Flux sortie Rouge
			$g.='     
			    <path
			       style="opacity:0.8;fill:url(#linearGradient1391-2);fill-opacity:1;stroke:#fc0000;stroke-width:0.26458332;stroke-opacity:1"
			       d="m 33.381718,67.333736 -3.369279,4.697903 c 0.680084,0.0209 1.360121,0.0296 2.040168,0.0346 v 3.196702 h 2.657705 v -3.208592 c 0.680217,-0.007 1.360391,-0.0127 2.040678,-0.0227 z"
			       id="flecheHzRouge'.$i.'"
			       inkscape:connector-curvature="0" /> ';
			$g.='  
			   <text
			       text-anchor="middle"
			       x="33.5"
			       y="65.851044"
			       style="font-size:3.5px"
			       id="textSortieRouge'.$i.'">'.$tabFlux[1][99].'</text>';
		}
		$g.='     
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1020-8-1);fill-opacity:1;stroke:#fe4939;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 41.544622,96.378711 -4.69789,-3.369279 c -0.01,0.680274 -0.0152,1.360461 -0.0227,2.040688 h -8.084225 v 2.657705 h 8.072335 c 0.005,0.680047 0.0137,1.360071 0.0346,2.040168 z"
		       id="flecheHtJaune'.$i.'"
		       inkscape:connector-curvature="0" /> ';

		if($tabFlux[2][99] > $seuilES){ //Flux sortie jaune
			$g.='     
			    <path
			       style="opacity:0.8;fill:url(#linearGradient1451-0);fill-opacity:1;stroke:#fe4939;stroke-width:0.26458332;stroke-opacity:1"
			       d="m 33.445262,87.296755 -3.369291,4.697873 c 0.680281,0.01 1.360461,0.0152 2.040691,0.0227 v 2.983782 h 2.6577 v -2.971892 c 0.68005,-0.005 1.36008,-0.0137 2.04018,-0.0346 z"
			       id="flecheHzJaune'.$i.'"
			       inkscape:connector-curvature="0" /> ';
			$g.=' 		
			    <text
			       x="33.5"
			       y="85.90522"
			       style="font-size:3.5px;text-anchor:middle"
			       id="textSortieJaune'.$i.'">'.$tabFlux[2][99].'</text>';
			       
		}

		$g.='     
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1124-48-7);fill-opacity:1;stroke:#12a439;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 41.809204,108.69884 -4.69788,-3.36931 c -0.01,0.68028 -0.0152,1.36047 -0.0227,2.0407 h -8.084236 v 2.65773 h 8.072346 c 0.005,0.68004 0.0137,1.36008 0.0346,2.04019 z"
		       id="flecheHtVert'.$i.'"
		       inkscape:connector-curvature="0" /> ';

		
		if($tabFlux[3][99] > $seuilES){ //Flux sortie vert
			$g.='     
			    <path
			       style="opacity:0.8;fill:url(#linearGradient1124-4-3-0-0-8);fill-opacity:1;stroke:#12a439;stroke-width:0.26458332;stroke-opacity:1"
			       d="m 32.26227,110.06689 v 3.48142 c -0.680064,0.005 -1.360081,0.0137 -2.040178,0.0346 l 3.369292,4.69788 3.36928,-4.69788 c -0.68027,-0.01 -1.36046,-0.0152 -2.04069,-0.0227 v -3.4933 z"
			       id="flecheHzVert'.$i.'"
			       inkscape:connector-curvature="0" /> ';
			$g.='
			    <text
			       x="33.5"
			       y="121.83437"
			       style="font-size:3.5px;text-anchor:middle"
			       id="textSortieVerte'.$i.'">'.$tabFlux[3][99].'</text>';
			       
		}
		$g.='        
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1255-2-0);fill-opacity:1;stroke:#1216ee;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 41.544614,128.70434 -4.69788,-3.3693 c -0.01,0.68028 -0.0152,1.36047 -0.0227,2.04071 h -8.084227 v 2.65771 h 8.072337 c 0.005,0.68006 0.0137,1.36009 0.0346,2.04019 z"
		       id="flecheHtBleu'.$i.'"
		       inkscape:connector-curvature="0" /> ';
		 

		if($tabFlux[4][99] > $seuilES){ //Flux sortie bleu
			$g.='     
			    <path
			       style="opacity:0.8;fill:url(#linearGradient1569-0);fill-opacity:1;stroke:#1216ee;stroke-width:0.26458332;stroke-opacity:1"
			       d="m 32.040944,130.05482 v 3.17551 c -0.680057,0.005 -1.360074,0.0137 -2.040178,0.0346 l 3.369278,4.69788 3.36929,-4.69788 c -0.68029,-0.01 -1.36045,-0.0152 -2.04068,-0.0227 v -3.18739 z"
			       id="flecheHzBleu'.$i.'"
			       inkscape:connector-curvature="0" /> ';
			$g.='
			    <text
			       x="33.5"
			       y="141.35919"
			       style="font-size:3.5px;text-anchor:middle"
			       id="textSortieBleu'.$i.'">'.$tabFlux[4][99].'</text>';
		
		}
		       
		$g.='          
	       
		    <!-- Répartition -->
		    <!-- ligne 1-->
		    <path
		       style="opacity:0.8;fill:none;fill-opacity:1;stroke:#121600;stroke-width:0.20737766;stroke-opacity:1"
		       d="m 82.934459,77.874176 h 0.032 c 0.004,0.599397 0.01,1.198894 0.0243,1.798331 l 3.274199,-2.969822 -3.274189,-2.969315 c -0.007,0.599617 -0.0108,1.198754 -0.016,1.798331 h -0.0403 z"
		       id="flecheVide'.$i.'-1"
		       inkscape:connector-curvature="0" />
		
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1158-7);';
		
		if($tabFlux[1][4] > $seuilFlux ){ //Flux repartition 1ère ligne
			$g.='fill-opacity:1;
			 stroke:#1217ed;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='      
		      
		       id="rectBleu'.$i.'-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="43.977058"
		       y="74.566689" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1160-9);';
		
		if($tabFlux[1][3] > $seuilFlux ){ //Flux repartition 1ère ligne
			$g.='fill-opacity:1;
			stroke:#13a438;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.42745098" ';
		}else{
			$g.='fill-opacity:0;
			stroke:#fff;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.42745098" ';
		}
		 $g.=' 
		       id="rectVert'.$i.'-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="53.735558"
		       y="74.566689" />
		    <rect
		       
			style="opacity:0.95999995;fill:url(#linearGradient1162-9);';
		
		
		if($tabFlux[1][2] > $seuilFlux){ //Flux repartition 1ère ligne
			$g.='fill-opacity:1;
			 stroke:#fc0000;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.=' 
		       id="rectJaune'.$i.'-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="63.462585"
		       y="74.566689" />
		       
		    <rect
		      style="opacity:0.8;fill:url(#linearGradient1164-1);';
		
		if($tabFlux[1][1] > $seuilFlux ){ //Flux repartition 1ère ligne
			$g.='fill-opacity:1;
			fill-rule:nonzero;stroke:#fe493a;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			fill-rule:nonzero;stroke:#fff;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"';
		}
		 $g.='  
		       id="rectRouge'.$i.'-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="73.245537"
		       y="74.566689" />';
		
		
		
		
		
		
		
		if($tabFlux[1][4] > $seuilFlux){		
			$g.='		
			    <text
			       x="48.5"
			       y="77.893211"
			       style="font-size:3.5px;text-anchor:middle"
			       id="textBleu'.$i.'-1">'.$tabFlux[1][4].'</text>';
		}       
		 if($tabFlux[1][3] > $seuilFlux){
			 $g.='      
			    <text
			       x="58.5"
			       y="77.901222"
			       style="font-size:3.5px;text-anchor:middle"
			       id="textVert'.$i.'-1">'.$tabFlux[1][3].'</text>';
		}
		if($tabFlux[1][2] > $seuilFlux){
			$g.='		
			    <text
			       x="68"
			       y="77.901222"
			       style="font-size:3.5px;text-anchor:middle"
			       id="texJaune'.$i.'-1">'.$tabFlux[1][2].'</text>';
		}
		if($tabFlux[1][1] > $seuilFlux){
			$g.='       
			    <text
			       x="78"
			       y="77.901222"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textRouge'.$i.'-1">'.$tabFlux[1][1].'</text>';
		}
		
		$g.=' 
		    <!-- ligne2 -->
			
		    <path
		       style="opacity:0.8;fill:none;fill-opacity:1;stroke:#121600;stroke-width:0.20737766;stroke-opacity:1"
		       d="m 82.934249,97.423745 h 0.032 c 0.004,0.599397 0.01,1.198894 0.0243,1.798331 l 3.274199,-2.969822 -3.274189,-2.969315 c -0.007,0.599617 -0.0108,1.198754 -0.016,1.798331 h -0.0403 z"
		       id="path983-4-6-6"
		       inkscape:connector-curvature="0" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1158-7-1); ';
		
		if($tabFlux[2][4] > $seuilFlux){ //Flux repartition 2eme ligne
			$g.='fill-opacity:1;
			 stroke:#1217ed;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     
		       id="rect945-8-6-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="43.977058"
		       y="94.11647" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1160-9-7);';
		
		if($tabFlux[2][3] > $seuilFlux){ //Flux repartition 2eme ligne
			$g.='fill-opacity:1;
			stroke:#13a438;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.42745098" ';
		}else{
			$g.='fill-opacity:0;
			stroke:#fff;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.42745098" ';
		}
		 $g.=' 
		       width="9.7208214"
		       height="4.2257261"
		       x="53.735558"
		       y="94.11647" />
		    <rect
		         style="opacity:0.95999995;fill:url(#linearGradient1162-9-7);';
		if($tabFlux[2][2] > $seuilFlux){ //Flux repartition 2eme ligne
			$g.='fill-opacity:1;
			 stroke:#fc0000;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.=' 
		       id="rect945-4-0-1-9"
		       width="9.7208214"
		       height="4.2257261"
		        x="63.462654"
		       y="94.11647" />
		    <rect
		        style="opacity:0.8;fill:url(#linearGradient1164-1-1);';
		
		if($tabFlux[2][1] > $seuilFlux){ //Flux repartition 2eme ligne
			$g.='fill-opacity:1;
			fill-rule:nonzero;stroke:#fe493a;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			fill-rule:nonzero;stroke:#fff;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"';
		}
		 $g.='  fill-opacity:1;fill-rule:nonzero;stroke:#fe493a;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-99-8-1-7"
		       width="9.7208214"
		       height="4.2257261"
		       x="73.245697"
		       y="94.11647" />';
		if($tabFlux[2][4] > $seuilFlux){
			$g.='	       
			   
		    <text
		       x="48.5"
		       y="97.443016"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textBleu'.$i.'-2">'.$tabFlux[2][4].'</text> ';
		}      
		if($tabFlux[2][3] > $seuilFlux){
			$g.='	
		    <text
		       x="58.5"
		       y="97.451004"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textVert'.$i.'-2">'.$tabFlux[2][3].'</text>';
		}
		if($tabFlux[2][2] > $seuilFlux){
			$g.='	    
		    <text
		       x="68"
		       y="97.451004"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textJaune'.$i.'-2">'.$tabFlux[2][2].'</text>';
		}
		if($tabFlux[2][1] > $seuilFlux){
			$g.='	    
		    <text
		       x="78"
		       y="97.451004"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textRouge'.$i.'-2">'.$tabFlux[2][1].'</text>';
		}
			 
		$g.='	 
		<!-- ligne 3 -->	 
			 
		    <path
		       style="opacity:0.8;fill:none;fill-opacity:1;stroke:#121600;stroke-width:0.20737766;stroke-opacity:1"
		       d="m 82.934029,110.05337 h 0.032 c 0.004,0.5994 0.01,1.1989 0.0243,1.79834 l 3.274199,-2.96984 -3.274189,-2.96933 c -0.007,0.59962 -0.0108,1.19876 -0.016,1.79834 h -0.0403 z"
		       id="path983-4-6-6-0"
		       inkscape:connector-curvature="0" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1158-7-1-0); ';
		
		if($tabFlux[3][4] > $seuilFlux){ //Flux repartition 3eme ligne
			$g.='fill-opacity:1;
			 stroke:#1217ed;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     
		       id="rect945-8-6-1-9"
		       width="9.7208214"
		       height="4.2257261"
		       x="43.977058"
		       y="106.74582" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1160-9-7-7); ';
		
		if($tabFlux[3][3] > $seuilFlux){ //Flux repartition 3eme ligne
			$g.='fill-opacity:1;
			 stroke:#13a438;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     
		       id="rect945-9-9-4-0-3"
		       width="9.7208214"
		       height="4.2257261"
		       x="53.735558"
		       y="106.74582" />
		    <rect
			style="opacity:0.95999995;fill:url(#linearGradient1162-9-7-2); ';
		
		if($tabFlux[3][2] > $seuilFlux){ //Flux repartition 3eme ligne
			$g.='fill-opacity:1;
			 stroke:#fe493a;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     

		       id="rect945-4-0-1-9-8"
		       width="9.7208214"
		       height="4.2257261"
		       x="63.4627"
		       y="106.74582" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1164-1-1-8); ';
		
		if($tabFlux[3][1] > $seuilFlux){ //Flux repartition 3eme ligne
			$g.='fill-opacity:1;
			 stroke:#fc0000;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     
		       id="rect945-99-8-1-7-4"
		       width="9.7208214"
		       height="4.2257261"
		       x="73.245888"
		       y="106.74582" />';
		       
		if($tabFlux[3][4] > $seuilFlux){
			$g.='		       
			     <text
			       x="48.5"
			       y="110.0724"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textBleu'.$i.'-3">'.$tabFlux[3][4].'</text>';
		}       
		if($tabFlux[3][3] > $seuilFlux){
			$g.='	       
			    <text
			       x="58.5"
			       y="110.0724"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textVert'.$i.'-3">'.$tabFlux[3][3].'</text>';
		}
		if($tabFlux[3][2] > $seuilFlux){
			$g.='	       
			    <text
			       x="68"
			       y="110.0724"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textJaune'.$i.'-3">'.$tabFlux[3][2].'</text>';
		}
		if($tabFlux[3][1] > $seuilFlux){
			$g.='	       
			    <text
			       x="78"
			       y="110.0724"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textRouge'.$i.'-3">'.$tabFlux[3][1].'</text>';
		}
		
		$g.='
		     <!-- ligne 4 -->  
			
		    <path
		       style="opacity:0.8;fill:none;fill-opacity:1;stroke:#121600;stroke-width:0.20737766;stroke-opacity:1"
		       d="m 82.933809,129.96205 h 0.032 c 0.004,0.5994 0.01,1.1989 0.0243,1.79834 l 3.274199,-2.96984 -3.274189,-2.96933 c -0.007,0.59962 -0.0108,1.19876 -0.016,1.79834 h -0.0403 z"
		       id="path983-4-6-6-0-8"
		       inkscape:connector-curvature="0" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1158-7-1-0-2); ';
		
		if($tabFlux[4][4] > $seuilFlux){ //Flux repartition 4eme ligne
			$g.='fill-opacity:1;
			 stroke:#1217ed;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     
		       id="rect945-8-6-1-9-6"
		       width="9.7208214"
		       height="4.2257261"
		       x="43.977058"
		       y="126.6539" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1160-9-7-7-2); ';
		
		if($tabFlux[4][3] > $seuilFlux){ //Flux repartition 4eme ligne
			$g.='fill-opacity:1;
			 stroke:#13a438;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     
		       id="rect945-9-9-4-0-3-5"
		       width="9.7208214"
		       height="4.2257261"
		       x="53.735558"
		       y="126.6539" />
		    <rect
		       style="opacity:0.95999995;fill:url(#linearGradient1162-9-7-2-9); ';
		
		if($tabFlux[4][2] > $seuilFlux){ //Flux repartition 4eme ligne
			$g.='fill-opacity:1;
			 stroke:#fe493a;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     
 		       id="rect945-4-0-1-9-8-4"
		       width="9.7208214"
		       height="4.2257261"
		       x="63.462784"
		       y="126.6539" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1164-1-1-8-2); ';
		
		if($tabFlux[4][1] > $seuilFlux){ //Flux repartition 4eme ligne
			$g.='fill-opacity:1;
			 stroke:#fc0000;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}else{
			$g.='fill-opacity:0;
			 stroke:#fff;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8" ';
		}
		 $g.='     
		       id="rect945-99-8-1-7-4-4"
		       width="9.7208214"
		       height="4.2257261"
		       x="73.246101"
		       y="126.6539" />';
		       
		if($tabFlux[4][4] > $seuilFlux){
			$g.='		       	   
			     <text
			       x="48.5"
			       y="130"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textBleu'.$i.'-4">'.$tabFlux[4][4].'</text>';
		}
		if($tabFlux[4][3] > $seuilFlux){
			$g.='		       
			    <text
			       x="58.5"
			      y="130"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textVert'.$i.'-4">'.$tabFlux[4][3].'</text>';
		}
		if($tabFlux[4][2] > $seuilFlux){
			$g.='	
			    <text
			       x="68"
			       y="130"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textJaune'.$i.'-4">'.$tabFlux[4][2].'</text>';
		}
		if($tabFlux[4][1] > $seuilFlux){
			$g.='	
			    <text
			       x="78"
			       y="130"
			       style="font-size:3.5px;text-anchor:middle" 
			       id="textRouge'.$i.'-4">'.$tabFlux[4][1].'</text>';
		}
		
		$g.='      
		    <!-- Entrées --> ';
		    
		if($tabFlux[1][0] > $seuilES){ //Flux entrée rouge  
			  $g.=' 
			    <path
			       style="opacity:0.8;fill:url(#linearGradient854-5-9-2);fill-opacity:1;stroke:#fc0000;stroke-width:0.26458332;stroke-opacity:1"
			       d="m 73.221263,62.532663 v 4.799683 h -0.0315 v 2.657695 h 8.072325 c 0.004,0.680057 0.0137,1.360084 0.0346,2.040178 l 4.697873,-3.369279 -4.697873,-3.369292 c -0.009,0.680287 -0.0152,1.360464 -0.0227,2.040698 h -5.39499 v -4.799683 z"
			       id="rect3699-6-67-9-7"
			       inkscape:connector-curvature="0" />
			      <text
			       text-anchor="middle"
			       x="71"
			       y="65.851044"
			       style="font-size:3.5px"
			       id="textEntreeRouge'.$i.'">'.$tabFlux[1][0].'</text>';
		}
			    
		if($tabFlux[2][0] > $seuilES){ //Flux entrée jaune  
			  $g.=' 
			    <path
			       style="opacity:0.8;fill:url(#linearGradient1020-4-8-4);fill-opacity:1;stroke:#fe4939;stroke-width:0.26458332;stroke-opacity:1"
			       d="m 73.289423,82.621099 v 4.828613 h 0.0413 v 2.129058 h 8.072335 c 0.005,0.680047 0.0137,1.360084 0.0346,2.040178 l 4.697883,-3.369279 -4.697883,-3.369292 c -0.01,0.680277 -0.0152,1.360464 -0.0227,2.040688 h -5.46786 v -4.299966 z"
			       id="rect3699-6-6-3-2-0-01-7-1"
			       inkscape:connector-curvature="0" />
			       
		    <text
		       x="71"
		       y="85.90522"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textEntreeJaune'.$i.'">'.$tabFlux[2][0].'</text>';
		}
			    
		if($tabFlux[3][0] > $seuilES){ //Flux entrée vert  
			  $g.=' 
			    <path
			       style="opacity:0.8;fill:url(#linearGradient1124-7-3-6);fill-opacity:1;stroke:#12a439;stroke-width:0.26458332;stroke-opacity:1"
			       d="m 81.425528,112.98604 c -0.01,0.68028 -0.0152,1.36046 -0.0227,2.04069 h -8.084215 v 2.4882 h -0.0109 v 4.70409 h 2.657705 v -4.53459 h 5.42548 c 0.005,0.68005 0.0137,1.36008 0.0346,2.04017 l 4.697883,-3.36928 z"
			       id="rect3699-6-6-3-2-0-3-38-5-0"
			       inkscape:connector-curvature="0" />
			          <text
		       x="71"
		       y="121.83437"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textEntreeVerte'.$i.'">'.$tabFlux[3][0].'</text>';
		}
			    
		if($tabFlux[4][0] > $seuilES){ //Flux entrée bleu  
			  $g.=' 
			    <path
			       style="opacity:0.8;fill:url(#linearGradient1255-4-0-7);fill-opacity:1;stroke:#1216ee;stroke-width:0.26458332;stroke-opacity:1"
			       d="m 81.402608,133.3479 c -0.01,0.68029 -0.0152,1.36046 -0.0227,2.0407 h -8.084215 v 2.0164 h -0.0548 v 5.45235 h 2.658225 v -4.81106 h 5.46889 c 0.005,0.68007 0.0137,1.36009 0.0346,2.04018 l 4.697873,-3.36928 z"
			       id="rect3699-6-6-3-2-0-3-3-4-7-9"
			       inkscape:connector-curvature="0" />
			       
			       <text
		       x="71"
		       y="141.35919"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textEntreeBleu'.$i.'">'.$tabFlux[4][0].'</text>';
		}
		  
		 
		}       
		$g.='  
		  </g>';
		  
		  echo $g;

	} //calque
	
?>	
</svg>

</body> 
