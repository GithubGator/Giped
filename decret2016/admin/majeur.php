<!DOCTYPE html>
<html>
<body>

<?php
$date1=date_create("1995-03-15");
$date2=date_create("2013-03-14");
$diff=date_diff($date1,$date2);
var_dump($diff);
//echo $diff->format("%R%a ");
/*
echo $diff->y ;
*/
echo "<br />";
$date1=date_create("1995-03-15");
$date2=date_create("1971-01-17");
$diff=date_diff($date1,$date2);
var_dump($diff);



?>

</body>
</html>