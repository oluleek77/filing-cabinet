<?php

require 'environment.php';
require_once 'access.class.php';

// connect to the database
mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);

// get all the file data
$qAllFileData = 'SELECT * FROM Files WHERE id = ' . $_GET['id'];
$qFileDataResult = mysql_query($qAllFileData);

if (!$qFileDataResult or (mysql_numrows($qFileDataResult) != 1)) {
    header('Location: listview.php');
}
echo 'okay';

mysql_close();
?>
