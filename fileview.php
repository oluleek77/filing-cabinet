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
$rAllFileData = mysql_query($qAllFileData);

// redirect if file does not exist
if (!$rAllFileData or (mysql_numrows($rAllFileData) != 1)) {
    header('Location: listview.php');
}
// redirect if user does not have permission to view the file
// (if the file is not public and they are not the owner of it)
if (mysql_result($rAllFileData, 0, 'permissions') == 0 and mysql_result($rAllFileData, 0, 'owner') != $user->get_property('username')) {
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
echo '<h2><img src="images/mimetypes/32/' . mimeFilename(mysql_result($rAllFileData, 0, 'type')) . '" />' . mysql_result($rAllFileData, 0, 'filename') . '</h2>';

// download and delete buttons
echo '<div><a href="download.php?id=' . $_GET['id'] . '"><img src="images/download-32.png" alt="Download file" /></a>
<a href="listview.php?action=delete&file_id=' . $_GET['id'] . '"><img src="images/delete-32.png" alt="Delete file" /></a></div>';

// display file data
echo '<p>MIME Type: ' . mysql_result($rAllFileData, 0, 'type') . '</p>';
echo '<p>File size: ' . mysql_result($rAllFileData, 0, 'size') . '</p>';
echo '<p>Owner: ' . mysql_result($rAllFileData, 0, 'owner') . '</p>';

// check if we've been asked to delete a label
if ($_GET['labeldel'])
{
    // user must be logged in and own the file to delete a label
    if ($user->is_loaded() and $user->is_active() and mysql_result($rAllFileData, 0, 'owner') == $user->get_property('username'))
    {
        $qDelLabel = 'DELETE FROM Labels WHERE file_id = ' . $_GET['id'] . ' AND label_name = "' . addslashes($_GET['labeldel']) . '"';
        //echo $qDelLabel;
        mysql_query($qDelLabel) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qDelLabel . "</p>\n");
    }
}

// get all labels for this file
$qFileLabels = 'SELECT label_name FROM Labels WHERE file_id = ' . $_GET['id'];
$rFileLabels = mysql_query($qFileLabels);
echo '<div id="filelabels">' . "\n";
echo "<table>\n<tr><th>Labels</th></tr>\n";
while ($row = mysql_fetch_assoc($rFileLabels))
{
    echo "<tr>\n";
    echo '<td>' . $row['label_name'] . "</td>\n";
    // if the user owns the file, show delete buttons for labels
    if ($user->is_loaded() and $user->is_active() and mysql_result($rAllFileData, 0, 'owner') == $user->get_property('username'))
    {
        echo '<td><a href="fileview.php?id=' . $_GET['id'] . '&labeldel=' . $row['label_name'] . "\"><img src=\"images/delete-32.png\" width=\"16\" height=\"16\" alt=\"Delete label\" /></a></td>\n";
    }
    echo "</tr>\n";
}
echo "\n</table>\n";

// if the user owns the file, show form for adding labels
if ($user->is_loaded() and $user->is_active() and mysql_result($rAllFileData, 0, 'owner') == $user->get_property('username'))
{
    echo '<form action="fileview.php" method="get">
    <input type="hidden" name="id" value="' . $_GET['id'] . '" />
    <input type="text" name="newlabels" />
    <input type="submit" value="Add Labels" />
    </form>';
}
echo "</div>\n";

// find previous file in sequence, if it exists.
$qPrevFile = 'SELECT id, filename FROM Files WHERE next_file_id = ' . $_GET['id'];
$rPrevFile = mysql_query($qPrevFile);
if (mysql_num_rows($rPrevFile) > 0) {
    echo 'Previous in sequence: <a href="fileview.php?id=' . mysql_result($rPrevFile, 0, 'id') . '">' . mysql_result($rPrevFile, 0, 'filename') . '</a>';
}

// get filename for next file in sequence if there is one.
if (mysql_result($rAllFileData, 0, 'next_file_id')) {
    $qNextFile = 'SELECT id, filename FROM Files WHERE id = ' . mysql_result($rAllFileData, 0, 'next_file_id');
    $rNextFile = mysql_query($qNextFile);
    echo 'Next in sequence: <a href="fileview.php?id=' . mysql_result($rNextFile, 0, 'id') . '">' . mysql_result($rNextFile, 0, 'filename') . '</a>';
}

echo foot();
mysql_close();
?>
