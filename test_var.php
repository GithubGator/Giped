<?php


$seuil = 100; // seuil en dessous duquel les valeurs ne sont pas affichées et que la couleur est grisée
$nb=18;
$i = 1;
//echo "Résultat = ".$tabFlux1[0];
$Stock = array(1=>0, 2=>0,3=>0,4=>0);

// initialisation
$FluxIni1 = array(0=>10,1=>0, 2=>0,3=>0,4=>0,99=>0);
$FluxIni2 = array(0=>20,1=>0, 2=>0,3=>0,4=>0,99=>0);
$FluxIni3 = array(0=>30,1=>0, 2=>0,3=>0,4=>0,99=>0);
$FluxIni4 = array(0=>40,1=>0, 2=>0,3=>0,4=>0,99=>0);
$tableauFlux[0] = array(1=>$FluxIni1,2=>$FluxIni2,3=>$FluxIni3,4=>$FluxIni4);


// Exemple de flux constaté qui vont constituer le stock à l'age de 2 ans

$Flux1 = array(0=>10,1=>11, 2=>12,3=>13,4=>14,99=>1);
$Flux2 = array(0=>20,1=>21, 2=>22,3=>23,4=>24,99=>2);
$Flux3 = array(0=>30,1=>31, 2=>32,3=>33,4=>34,99=>3);
$Flux4 = array(0=>40,1=>41, 2=>42,3=>43,4=>44,99=>4);


$tabFlux = array(1=>$Flux1,2=>$Flux2,3=>$Flux3,4=>$Flux4); // $tabFlux = tableaux des 4 flux à l'instant t considéré

// en test on va imaginer les même flux à chaque année sauf au départ évidemment

for ($i = 1; $i <= $nb; $i++) {
	$tableauFlux[$i]=$tabFlux;
}

echo "<pre>";
//print_r($tableauFlux);
print_r($tableauFlux[1][1]);
echo "</pre>";
MajStock ($tabFlux);
echo "<pre>";
//print_r($tableauFlux);
print_r($Stock);
echo "</pre>";
MajStock ($tabFlux);
echo "<pre>";
//print_r($tableauFlux);
print_r($Stock);
echo "</pre>";


function MajStock ($tabFlux){
	global $Stock ;
	for ($j=1; $j<=4;$j++){
		//$Stock[$j] += ${"Flux".$j}[0]+${"Flux".$j}[1]+${"Flux".$j}[2]+${"Flux".$j}[3]+${"Flux".$j}[4]+${"Flux".$j}[99];
		$Stock[$j] += $tabFlux[$j][0]+$tabFlux[$j][1]+$tabFlux[$j][2]+$tabFlux[$j][3]+$tabFlux[$j][4]-$tabFlux[$j][99];
	}
	/* Equivalent à :
	$tabStock[1] += $tabFlux1[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[2] += $tabFlux2[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[3] += $tabFlux3[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	$tabStock[4] += $tabFlux4[0]+$tabFlux1[1]+$tabFlux1[2]+$tabFlux1[3]+$tabFlux1[4]+$tabFlux1[99];
	*/
}

?>