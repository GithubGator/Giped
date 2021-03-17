<?php

$ch = "2090";

if (preg_match("/^19|^20/", $ch))
{
    echo 'VRAI';
}
else
{
    echo 'FAUX';
}
?>
