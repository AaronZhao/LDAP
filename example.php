<?php
error_reporting(E_ALL);
include "cldap.php";
$host = "your host";
$port = 389;
$basedb = "DC=example,DC=com";
$user	= "***@example.com";
$pwd = "your password";

$obj = Ldap::getInstance($host,$port,$basedb,$errCode);
if( null === $obj ) {
	var_dump($errCode);
}
$res = $obj->login($user,$pwd,$errCode);
if( false === $res ) {
	var_dump( $errCode );
}
if (false === ($name = $obj->getName())) {
	echo "getName fail<br />";
} else {
	echo "name:$name<br />";
}
if (false === ($name = $obj->getTitle())) {
        echo "getTitle fail<br />";
} else {
        echo "Title:$name<br />";
}
 
if (false === ($name = $obj->getDepartment())) {
        echo "getDepartment fail<br />";
} else {
        echo "Department:$name<br />";
}
 

if (false === ($name = $obj->getFullDepartment())) {
        echo "getFullDepartment fail<br />";
} else {
        echo "FullDepartmen:$name<br />";
} 
