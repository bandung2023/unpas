<?php
$server='localhost';
$mysqluser='root';
$mysqlpswd='root';
$mysqldb='moviesender';

$mysqli = new mysqli(''.$server.'',''.$mysqluser.'',''.$mysqlpswd.'',''.$mysqldb.'');
if($mysqli->connect_errno)
 	 {
           echo "Can't Connect";
 	 }

?>
