<?php

require 'environment.php';
require 'common.php';
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
echo head();
echo '<h1>Filing Cabinet</h1>';
if ($user->is_loaded() and $user->is_active())
{
    echo tabMenu(True, $user->get_property('username'));
} else {
    echo tabMenu(False);
}
echo '<h2>'. mysql_result($qFileDataResult, 0, 'filename') . '</h2>';

// display file data
echo '<p>MIME Type: ' . mysql_result($qFileDataResult, 0, 'type') . '<img src="images/mimetypes/32/' . mimeFilename(mysql_result($qFileDataResult, 0, 'type')) . '" /></p>';
echo '<p>File size: ' . mysql_result($qFileDataResult, 0, 'size') . '</p>';
echo '<p>Owner: ' . mysql_result($qFileDataResult, 0, 'owner') . '</p>';

echo '<div><a href="download.php?id=' . $_GET['id'] . '"><img src="images/download-32.png" alt="Download" /></a>
<a href="listview.php?action=delete&file_id=' . $_GET['id'] . '"><img src="images/delete-32.png" alt="Delete" /></a></div>';

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

echo foot();
mysql_close();
?>
