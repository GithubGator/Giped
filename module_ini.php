<?xml version="1.0" encoding="UTF-8" standalone="no"?>
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
	#intro {
		width: 800px;
		border: 1px solid black;
		margin-left: 100px;
		padding:10px;
	}
	-->
	</style>
<body> 
<h2 >Parcours simplifi&eacute; des enfants</h2>
<div id="intro">
Cette repr&eacute;sentation des parcours est construite dynamiquement sur la base du document fourni par Vincent Spiesser, avec quelques variantes.<br />
Il s'agit d'un premier jet construit à partir de valeurs fixes utilis&eacute;es ici pour en v&eacute;rifier le positonnement et le rendu graphique.<br />
Dans cet exemple, l'ensemble des parcours sur les 18 ans est propos&eacute; en scrollant.<br/>
Evidemment la lisibilit&eacute; de ce diagramme sera grandement am&eacute;lior&eacute;e quand un filtrage des donn&eacute;es sera en place.
L'id&eacute;e est de "dynamiser" cette repr&eacute;sentation en masquant ou en adaptant certaines zones. <br />
Je ferai des propositions quand j'aurai finalis&eacute; l'injection des valeurs (il me faudra un peu de temps).<br />
En l'&eacute;tat, toute remarque/critique est bienvenue.<br /><br />

Il ne s'agit pas d'une image mais d'une représentation vectorielle recalculée à chaque fois, <br /> il est possible de zoomer Ctrl + ou de réduire Ctrl -

</div>
<?php
if(isset($_GET['nb']) && $nb >= 0){
	$nb=$_GET['nb'];
}else{
	$nb=4;
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
  8 => "A 9 ans",
  10 => "A 10 ans",
  11 => "A 11 ans",
  12 => "A 12 ans",
  13 => "A 13 ans",
  14 => "A 14 ans",
  15 => "A 15 ans",
  16 => "A 16 ans",
  17 => "A 17 ans",
  18 => "A 18 ans"
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
 
$Stock = array(1=>1, 2=>2,3=>3,4=>4);

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
$Flux2 = array(0=>20,1=>21, 2=>22,3=>23,4=>24,99=>2);
$Flux3 = array(0=>30,1=>31, 2=>32,3=>33,4=>34,99=>3);
$Flux4 = array(0=>40,1=>41, 2=>42,3=>43,4=>44,99=>4);


$tabFlux = array(1=>$Flux1,2=>$Flux2,3=>$Flux3,4=>$Flux4); // $tabFlux = tableaux des 4 flux à l'instant t considéré

$seuil = 100; // seuil en dessous duquel les valeurs ne sont pas affichées et que la couleur est grisée


for ($i = 0; $i <= $nb; $i++) {
		Calque($i,+28,-30);

}

function MajStock ($tabFlux){
	global $Stock ;
	for ($j=1; $j<=4;$j++){
		$Stock[$j] += ${"Flux".$j}[0]+${"Flux".$j}[1]+${"Flux".$j}[2]+${"Flux".$j}[3]+${"Flux".$j}[4]+${"Flux".$j}[99];
		$Stock[$j] += $tabFlux[$j][0]+$tabFlux[$j][1]+$tabFlux[$j][2]+$tabFlux[$j][3]+$tabFlux[$j][4]+$tabFlux[$j][99];
	}
	/* Equivalent à :
	$tabStock[1] += $tabFlux1[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[2] += $tabFlux2[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[3] += $tabFlux3[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[4] += $tabFlux4[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	*/
	
	
}

  function Calque ($i,$x=0,$y=0){
	global $legendeX,$legendeY_0,$legendeY_1;
		$offsetX = (($i)*70)+$x;
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
		       x="22.5"
		       y="148"
		       style="font-size:3.5px;"
		       id="legende'.$i.'">'.$legendeX[$i].'</text>'; 
		  }
		if($i == 0){
		
			for ($k=1;$k<=2;$k++){
				$yDecalage = 50 + $k*35.2;
				
				if(isset ($legendeY_0[$k])){  
				    $g.='    <!--Légendes axe des y -->
				      <text
				       text-anchor="end"
				       x="2"
				       y="'.$yDecalage.'"
				       style="font-size:3.5px;font-weight:bold;"
				       id="legende0-'.$k.'">'.$legendeY_0[$k].'</text>'; 
				       
				}
				
				/*
				if(isset ($legendeY_0[$k])){  
				    $g.='    <!--Légendes axe des y -->
				    <rect 
					x="2"
					y="'.$yDecalage.'"
					width="20"
					height= "10" 
					 style="fill:none;stroke:black; stroke-width:1px; stroke-dasharray:2 5;" />
					<textArea
				       text-anchor="end"
				       x="2"
				       y="'.$yDecalage.'"
				       width="50"
				       height= "40"
				       style="font-size:3.5px;font-weight:bold;"
				       id="legende0-'.$k.'">'.$legendeY_0[$k].'</textArea>'; 
				       
				}
				*/
			}
			for ($k=1;$k<=4;$k++){
				$yDecalage = 60.3 + $k*17.6;
				if(isset ($legendeY_1[$k])){  
				    $g.='    <!--Légendes axe des y -->
				      <text
				       text-anchor="end"
				       x="16"
				       y="'.$yDecalage.'"
				       style="font-size:3.5px"
				       id="legende1-'.$k.'">'.$legendeY_1[$k].'</text>'; 
				}
			}
			
			
		}
		  $g.='   
		    <!-- Bargraph -->
		    <rect
		       style="opacity:0.95999995;fill:#ff4939;fill-opacity:0.50196078;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barRouge'.$i.'"
		       width="7.7593513"
		       height="17.759354"
		       x="18.609825"
		       y="66.915871" />
		    <rect
		       style="opacity:0.95999995;fill:#f89107;fill-opacity:0.44705882;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barJaune'.$i.'"
		       width="7.7593513"
		       height="17.759354"
		       x="18.609825"
		       y="84.916145" />
		    <rect
		       style="opacity:0.95999995;fill:#349108;fill-opacity:0.48235294;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barVert-5"
		       width="7.7593513"
		       height="17.759354"
		       x="18.609825"
		       y="102.91663" />
		    <rect
		       style="opacity:0.95999995;fill:#3468f5;fill-opacity:0.70980392;fill-rule:nonzero;stroke:#121600;stroke-width:0.24064864;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
		       id="barBleu'.$i.'"
		       width="7.7593513"
		       height="17.759354"
		       x="18.609825"
		       y="120.91687" />
		    <text
		       text-anchor="middle"
		       x="22.5"
		       y="77.886238"
		       style="font-size:3.5px"
		       id="textRouge'.$i.'">001</text>
		    <text
		       text-anchor="middle"
		       x="22.5"
		       y="95.345848"
		       style="font-size:3.5px"
		       id="textJaune'.$i.'">001</text>
		    <text
		       text-anchor="middle"
		       x="22.5"
		       y="113.12086"
		       style="font-size:3.5px"
		       id="textVert'.$i.'">001</text>
		    <text
		       text-anchor="middle"
		       x="22.5"
		       y="131.00429"
		       style="font-size:3.5px"
		       id="textBleu'.$i.'">236</text>
		    <!-- Bifurcations-->
		    <path
		       style="opacity:0.8;fill:url(#linearGradient854-4-2);fill-opacity:1;stroke:#fc0000;stroke-width:0.26458332;stroke-opacity:1"
		       d="M 41.544553,76.655185 36.84665,73.285883 c -0.01,0.680287 -0.0152,1.360471 -0.0227,2.040698 h -8.084225 v 2.657725 h 8.072345 c 0.004,0.680057 0.0137,1.360084 0.0346,2.040178 z"
		       id="flecheHtRouge'.$i.'"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1391-2);fill-opacity:1;stroke:#fc0000;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 33.381718,67.333736 -3.369279,4.697903 c 0.680084,0.0209 1.360121,0.0296 2.040168,0.0346 v 3.196702 h 2.657705 v -3.208592 c 0.680217,-0.007 1.360391,-0.0127 2.040678,-0.0227 z"
		       id="flecheHzRouge'.$i.'"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1020-8-1);fill-opacity:1;stroke:#fe4939;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 41.544622,96.378711 -4.69789,-3.369279 c -0.01,0.680274 -0.0152,1.360461 -0.0227,2.040688 h -8.084225 v 2.657705 h 8.072335 c 0.005,0.680047 0.0137,1.360071 0.0346,2.040168 z"
		       id="flecheHtJaune'.$i.'"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1451-0);fill-opacity:1;stroke:#fe4939;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 33.445262,87.296755 -3.369291,4.697873 c 0.680281,0.01 1.360461,0.0152 2.040691,0.0227 v 2.983782 h 2.6577 v -2.971892 c 0.68005,-0.005 1.36008,-0.0137 2.04018,-0.0346 z"
		       id="flecheHzJaune'.$i.'"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1124-48-7);fill-opacity:1;stroke:#12a439;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 41.809204,108.69884 -4.69788,-3.36931 c -0.01,0.68028 -0.0152,1.36047 -0.0227,2.0407 h -8.084236 v 2.65773 h 8.072346 c 0.005,0.68004 0.0137,1.36008 0.0346,2.04019 z"
		       id="flecheHtVert'.$i.'"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1124-4-3-0-0-8);fill-opacity:1;stroke:#12a439;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 32.26227,110.06689 v 3.48142 c -0.680064,0.005 -1.360081,0.0137 -2.040178,0.0346 l 3.369292,4.69788 3.36928,-4.69788 c -0.68027,-0.01 -1.36046,-0.0152 -2.04069,-0.0227 v -3.4933 z"
		       id="flecheHzVert'.$i.'"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1255-2-0);fill-opacity:1;stroke:#1216ee;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 41.544614,128.70434 -4.69788,-3.3693 c -0.01,0.68028 -0.0152,1.36047 -0.0227,2.04071 h -8.084227 v 2.65771 h 8.072337 c 0.005,0.68006 0.0137,1.36009 0.0346,2.04019 z"
		       id="flecheHtBleu'.$i.'"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1569-0);fill-opacity:1;stroke:#1216ee;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 32.040944,130.05482 v 3.17551 c -0.680057,0.005 -1.360074,0.0137 -2.040178,0.0346 l 3.369278,4.69788 3.36929,-4.69788 c -0.68029,-0.01 -1.36045,-0.0152 -2.04068,-0.0227 v -3.18739 z"
		       id="flecheHzBleu'.$i.'"
		       inkscape:connector-curvature="0" />
		    <text
		       text-anchor="middle"
		       x="33.5"
		       y="65.851044"
		       style="font-size:3.5px"
		       id="textSortieRouge'.$i.'">9</text>
		    <text
		       x="33.5"
		       y="85.90522"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textSortieJaune'.$i.'">9</text>
		    <text
		       x="33.5"
		       y="121.83437"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textSortieVerte'.$i.'">9</text>
		    <text
		       x="33.5"
		       y="141.35919"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textSortieBleu'.$i.'">9</text>
		       
		    <!-- Répartition -->
		    <!-- ligne 1-->
		    <path
		       style="opacity:0.8;fill:none;fill-opacity:1;stroke:#121600;stroke-width:0.20737766;stroke-opacity:1"
		       d="m 82.934459,77.874176 h 0.032 c 0.004,0.599397 0.01,1.198894 0.0243,1.798331 l 3.274199,-2.969822 -3.274189,-2.969315 c -0.007,0.599617 -0.0108,1.198754 -0.016,1.798331 h -0.0403 z"
		       id="flecheVide'.$i.'-1"
		       inkscape:connector-curvature="0" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1158-7);fill-opacity:1;stroke:#1217ed;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rectBleu'.$i.'-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="43.977058"
		       y="74.566689" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1160-9);fill-opacity:1;stroke:#13a438;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.42745098"
		       id="rectVert'.$i.'-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="53.735558"
		       y="74.566689" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1164-1);fill-opacity:1;stroke:#fc0000;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rectJaune'.$i.'-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="73.245537"
		       y="74.566689" />
		    <rect
		       style="opacity:0.95999995;fill:url(#linearGradient1162-9);fill-opacity:1;fill-rule:nonzero;stroke:#fe493a;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rectRouge'.$i.'-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="63.462585"
		       y="74.566689" />
		       
		    <text
		       x="48.5"
		       y="77.893211"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textBleu'.$i.'-1">998</text>
		       
		    <text
		       x="58.5"
		       y="77.901222"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textVert'.$i.'-1">456</text>
		       
		    <text
		       x="68"
		       y="77.901222"
		       style="font-size:3.5px;text-anchor:middle"
		       id="texJaune'.$i.'-1">000</text>
		       
		    <text
		       x="78"
		       y="77.901222"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textRouge'.$i.'-1">000</text>

		    <!-- ligne2 -->
			
		    <path
		       style="opacity:0.8;fill:none;fill-opacity:1;stroke:#121600;stroke-width:0.20737766;stroke-opacity:1"
		       d="m 82.934249,97.423745 h 0.032 c 0.004,0.599397 0.01,1.198894 0.0243,1.798331 l 3.274199,-2.969822 -3.274189,-2.969315 c -0.007,0.599617 -0.0108,1.198754 -0.016,1.798331 h -0.0403 z"
		       id="path983-4-6-6"
		       inkscape:connector-curvature="0" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1158-7-1);fill-opacity:1;stroke:#1217ed;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-8-6-1"
		       width="9.7208214"
		       height="4.2257261"
		       x="43.977058"
		       y="94.11647" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1160-9-7);fill-opacity:1;stroke:#13a438;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.42745098"
		       id="rect945-9-9-4-0"
		       width="9.7208214"
		       height="4.2257261"
		       x="53.735558"
		       y="94.11647" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1164-1-1);fill-opacity:1;stroke:#fc0000;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-4-0-1-9"
		       width="9.7208214"
		       height="4.2257261"
		       x="73.245697"
		       y="94.11647" />
		    <rect
		       style="opacity:0.95999995;fill:url(#linearGradient1162-9-7);fill-opacity:1;fill-rule:nonzero;stroke:#fe493a;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-99-8-1-7"
		       width="9.7208214"
		       height="4.2257261"
		       x="63.462654"
		       y="94.11647" />
		       
			   
		    <text
		       x="48.5"
		       y="97.443016"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textBleu'.$i.'-2">222</text>
		       
		    <text
		       x="58.5"
		       y="97.451004"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textVert'.$i.'-2">222</text>
		    <text
		       x="68"
		       y="97.451004"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textJaune'.$i.'-2">222</text>
		    <text
		       x="78"
		       y="97.451004"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textRouge'.$i.'-2">222</text>
			 
			 
		<!-- ligne 3 -->	 
			 
		    <path
		       style="opacity:0.8;fill:none;fill-opacity:1;stroke:#121600;stroke-width:0.20737766;stroke-opacity:1"
		       d="m 82.934029,110.05337 h 0.032 c 0.004,0.5994 0.01,1.1989 0.0243,1.79834 l 3.274199,-2.96984 -3.274189,-2.96933 c -0.007,0.59962 -0.0108,1.19876 -0.016,1.79834 h -0.0403 z"
		       id="path983-4-6-6-0"
		       inkscape:connector-curvature="0" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1158-7-1-0);fill-opacity:1;stroke:#1217ed;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-8-6-1-9"
		       width="9.7208214"
		       height="4.2257261"
		       x="43.977058"
		       y="106.74582" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1160-9-7-7);fill-opacity:1;stroke:#13a438;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.42745098"
		       id="rect945-9-9-4-0-3"
		       width="9.7208214"
		       height="4.2257261"
		       x="53.735558"
		       y="106.74582" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1164-1-1-8);fill-opacity:1;stroke:#fc0000;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-4-0-1-9-8"
		       width="9.7208214"
		       height="4.2257261"
		       x="73.245888"
		       y="106.74582" />
		    <rect
		       style="opacity:0.95999995;fill:url(#linearGradient1162-9-7-2);fill-opacity:1;fill-rule:nonzero;stroke:#fe493a;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-99-8-1-7-4"
		       width="9.7208214"
		       height="4.2257261"
		       x="63.4627"
		       y="106.74582" />
		       
		     <text
		       x="48.5"
		       y="110.0724"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textBleu'.$i.'-3">222</text>
		       
		    <text
		       x="58.5"
		       y="110.0724"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textVert'.$i.'-3">222</text>
		    <text
		       x="68"
		       y="110.0724"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textJaune'.$i.'-3">222</text>
		    <text
		       x="78"
		       y="110.0724"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textRouge'.$i.'-3">222</text>
			   
		     <!-- ligne 4 -->  
			
		    <path
		       style="opacity:0.8;fill:none;fill-opacity:1;stroke:#121600;stroke-width:0.20737766;stroke-opacity:1"
		       d="m 82.933809,129.96205 h 0.032 c 0.004,0.5994 0.01,1.1989 0.0243,1.79834 l 3.274199,-2.96984 -3.274189,-2.96933 c -0.007,0.59962 -0.0108,1.19876 -0.016,1.79834 h -0.0403 z"
		       id="path983-4-6-6-0-8"
		       inkscape:connector-curvature="0" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1158-7-1-0-2);fill-opacity:1;stroke:#1217ed;stroke-width:0.27917877;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-8-6-1-9-6"
		       width="9.7208214"
		       height="4.2257261"
		       x="43.977058"
		       y="126.6539" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1160-9-7-7-2);fill-opacity:1;stroke:#13a438;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.42745098"
		       id="rect945-9-9-4-0-3-5"
		       width="9.7208214"
		       height="4.2257261"
		       x="53.735558"
		       y="126.6539" />
		    <rect
		       style="opacity:0.8;fill:url(#linearGradient1164-1-1-8-2);fill-opacity:1;stroke:#fc0000;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-4-0-1-9-8-4"
		       width="9.7208214"
		       height="4.2257261"
		       x="73.246101"
		       y="126.6539" />
		    <rect
		       style="opacity:0.95999995;fill:url(#linearGradient1162-9-7-2-9);fill-opacity:1;fill-rule:nonzero;stroke:#fe493a;stroke-width:0.27917874;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8"
		       id="rect945-99-8-1-7-4-4"
		       width="9.7208214"
		       height="4.2257261"
		       x="63.462784"
		       y="126.6539" />
			   
		     <text
		       x="48.5"
		       y="130"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textBleu'.$i.'-4">222</text>
		       
		    <text
		       x="58.5"
		      y="130"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textVert'.$i.'-4">222</text>
		    <text
		       x="68"
		       y="130"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textJaune'.$i.'-4">222</text>
		    <text
		       x="78"
		       y="130"
		       style="font-size:3.5px;text-anchor:middle" 
		       id="textRouge'.$i.'-4">222</text>
		       
		    <!-- Entrées -->
		    
		    <path
		       style="opacity:0.8;fill:url(#linearGradient854-5-9-2);fill-opacity:1;stroke:#fc0000;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 73.221263,62.532663 v 4.799683 h -0.0315 v 2.657695 h 8.072325 c 0.004,0.680057 0.0137,1.360084 0.0346,2.040178 l 4.697873,-3.369279 -4.697873,-3.369292 c -0.009,0.680287 -0.0152,1.360464 -0.0227,2.040698 h -5.39499 v -4.799683 z"
		       id="rect3699-6-67-9-7"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1020-4-8-4);fill-opacity:1;stroke:#fe4939;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 73.289423,82.621099 v 4.828613 h 0.0413 v 2.129058 h 8.072335 c 0.005,0.680047 0.0137,1.360084 0.0346,2.040178 l 4.697883,-3.369279 -4.697883,-3.369292 c -0.01,0.680277 -0.0152,1.360464 -0.0227,2.040688 h -5.46786 v -4.299966 z"
		       id="rect3699-6-6-3-2-0-01-7-1"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1124-7-3-6);fill-opacity:1;stroke:#12a439;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 81.425528,112.98604 c -0.01,0.68028 -0.0152,1.36046 -0.0227,2.04069 h -8.084215 v 2.4882 h -0.0109 v 4.70409 h 2.657705 v -4.53459 h 5.42548 c 0.005,0.68005 0.0137,1.36008 0.0346,2.04017 l 4.697883,-3.36928 z"
		       id="rect3699-6-6-3-2-0-3-38-5-0"
		       inkscape:connector-curvature="0" />
		    <path
		       style="opacity:0.8;fill:url(#linearGradient1255-4-0-7);fill-opacity:1;stroke:#1216ee;stroke-width:0.26458332;stroke-opacity:1"
		       d="m 81.402608,133.3479 c -0.01,0.68029 -0.0152,1.36046 -0.0227,2.0407 h -8.084215 v 2.0164 h -0.0548 v 5.45235 h 2.658225 v -4.81106 h 5.46889 c 0.005,0.68007 0.0137,1.36009 0.0346,2.04018 l 4.697873,-3.36928 z"
		       id="rect3699-6-6-3-2-0-3-3-4-7-9"
		       inkscape:connector-curvature="0" />
		       
		       <text
		       text-anchor="middle"
		       x="71"
		       y="65.851044"
		       style="font-size:3.5px"
		       id="textEntreeRouge'.$i.'">9</text>
		    <text
		       x="71"
		       y="85.90522"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textEntreeJaune'.$i.'">9</text>
		    <text
		       x="71"
		       y="121.83437"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textEntreeVerte'.$i.'">9</text>
		    <text
		       x="71"
		       y="141.35919"
		       style="font-size:3.5px;text-anchor:middle"
		       id="textEntreeBleu'.$i.'">9</text>
		  </g>';
		  echo $g;
	} //calque
	
?>	
</svg>

</body> 
