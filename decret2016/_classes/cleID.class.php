<?php
/**
 * CLASS cleID
 * author Michel Roger <mroger@oned.gouv.fr
 * Génération de l'identifiant anonyme
 * basé sur l'algorithme sha1
 * dérive de la, classe phonex
 * author Johan Barbier <barbier_johan@hotmail.com>
 */
class cleID extends phonex {

    /**
     * Les variables  qui seront utilisées
     */
    public $sSHA1 = '';
    private $sPhonex='';
    /**
     * public function genereID ()
     * main method, construit le né anonymisé a partir du tableau passé en paramétre
     *
     */
    public function genereID($record) {
        //Chaque record contient PRENOM,NOMMERE,NOMUSAGE,NUMDO,JNAIS,MNAIS,ANAIS
        $sVar1="";
        $sVar2="";
        $sVar3="";

        // Clé provisoire a mettre a jour lors de la mise en production
        $cleSecrete="gr77b6xps1awn7cx6ixiev7c1ojx6hcd";

        // Si le prenom ou le nom  de jeune fille de la mére ou si la date de naissance de l'enfant est vide on utilise le né de dossier comme variable de substitution

        if(trim($record['PRENOM']!= '')){
            $sVar1 = $record['PRENOM'];
        }else{
            $sVar1 =$record['NUMDO'];
        }

        if(trim($record['NOMMEREJEUNE']!= '')){
            $sVar2 = $record['NOMMEREJEUNE'];
        }else{
            if(trim($record['NOMUSAGE']!= '')){
                $sVar2 = $record['NOMUSAGE'];
            }else{
                $sVar2 = $record['NUMDO'];
            }
        }
        if(trim($record['JNAIS'].$record['MNAIS'].$record['ANAIS']) != ''){
            $sVar3 = $record['JNAIS'].$record['MNAIS'].$record['ANAIS'];
        }else{
            $sVar3 = $record['NUMDO'];
        }
        $this->sPhonex = substr($this->getPhonex($this->formate($sVar1)),0,12). substr($this->getPhonex($this->formate($sVar2)),0,12);
        $this->sSHA1=substr(hash('sha1',$this->formate($sVar1.$sVar2.$sVar3).$cleSecrete),0,20). substr(hash('sha1',($this->sPhonex).$cleSecrete),0,20);

    }


    /**
     * private function formate ()
     * Pré-traitement des variables, suppression des accents, des espaces, des caractéres spéciaux et mise en majuscule
     */
    private function formate ($string) {

        $char_bad = array(
            '<',
            '>',
            '&',
            '-',
            '/',
            ' ',
            '\'',
            '.'
        );
        $char_good = array(
            '&lt;',
            '&gt;',
            '&amp;',
            '',
            '',
            '',
            '',
            ''
        );

        $string = str_replace($char_bad,$char_good,$string);

        return strtoupper(strtr($string,"  ","AAAAAAEEEEEEEEOOOOOIIIIUUUUYNCYƌ"));

    }
}
?>