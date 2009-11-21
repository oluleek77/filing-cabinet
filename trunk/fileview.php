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

// redirect if file does not exist
if (!$qFileDataResult or (mysql_numrows($qFileDataResult) != 1)) {
    header('Location: listview.php');
}
// redirect if user does not have permission to view the file
// (if the file is not public and they are not the owner of it)
if (mysql_result($qFileDataResult, 0, 'permissions') == 0 and mysql_result($qFileDataResult, 0, 'owner') != $user->get_property('username')) {
    header('Location: listview.php');
}

echo '<h1>'. mysql_result($qFileDataResult, 0, 'filename') . '</h1>';

// find previous file in sequence, if it exists.
$qPrevFile = 'SELECT id, filename FROM Files WHERE next_file_id = ' . $_GET['id'];
$qPrevFileResult = mysql_query($qPrevFile);
if (mysql_num_rows($qPrevFileResult) > 0) {
    echo 'Previous in sequence: <a href="fileview.php?id=' . mysql_result($qPrevFileResult, 0, 'id') . '">' . mysql_result($qPrevFileResult, 0, 'filename') . '</a>';
}

// get filename for next file in sequence if there is one.
if (mysql_result($qFileDataResult, 0, 'next_file_id')) {
    $qNextFile = 'SELECT id, filename FROM Files WHERE id = ' . mysql_result($qFileDataResult, 0, 'next_file_id');
    $qNextFileResult = mysql_query($qNextFile);
    echo 'Next in sequence: <a href="fileview.php?id=' . mysql_result($qNextFileResult, 0, 'id') . '">' . mysql_result($qNextFileResult, 0, 'filename') . '</a>';
}



mysql_close();
?>
