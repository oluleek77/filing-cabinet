<?php

require 'environment.php';
require 'common.php';
require_once 'access.class.php';

// connect to the database
mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);
echo '
<head>
    <link rel="stylesheet" type="text/css" href="filingcabinet-default.css" />
    <title>Filing Cabinet</title>
</head>
';

// check if we've been asked to delete a file
if ($_POST['action'] == 'delete')
{
    // must be logged in to delete files
    if (!($user->is_loaded() and $user->is_active()))
    {
        echo "<p>You need to login to delete files.</p>\n";
    }
    else {
        // user must own the file to delete it
        $fileOwner = 'SELECT owner FROM Files WHERE id = ' . $_POST['file_id'];
        $fileOwnerResult = mysql_query($fileOwner) or die ('Query failed: ' . mysql_error() . '<br />' . $fileOwner);
        if (mysql_result($fileOwnerResult, 0, 'owner') != $user->get_property('username'))
        {
            echo "<p>You need to own the file to delete it.</p>\n";
        }
        else {
            echo "<p>Deleting file from archive<br />\n";
            echo 'archiver says: ' . exec("7za d -y $archive_path file_" . $_POST['file_id'], $output, $return_value) . "<br />\n";
            if ($return_value == 0)
            {
                $qDelFile = 'DELETE FROM Files WHERE id = ' . $_POST['file_id'];
                mysql_query($qDelFile) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qDelFile . "</p>\n");
                $qDelLabels = 'DELETE FROM Labels WHERE file_id = ' . $_POST['file_id'];
                mysql_query($qDelLabels) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qDelLabels . "</p>\n");
                echo "File deleted</p>\n";
            }
            else {
                echo "There was an error deleting the file.</p>\n";
            }
        }
    }
}

// show all puplic files and files own by this user (if logged in)
$qSelectedFiles = 'SELECT * FROM Files WHERE (permissions = 1';
if ($user->is_loaded() and $user->is_active())
{
    $qSelectedFiles .= " OR owner = '".addslashes($user->get_property('username'))."')";
    echo '<p>Welcome ' . $user->get_property('username') . '. <a href="login.php?logout=1">Logout</a>.';
    echo '<a href="upload_files.php">Upload files</a></p>'."\n";
}
else {
    $qSelectedFiles .= ')';
    echo '<p>Only displaying public files. <a href="login.php">Login</a> to access your own private files.</p>'."\n";
}

echo '<div id="tabs">
  <ul>
    <li></li>
    <li><a href="#"><span>List Files</span></a></li>
    <li><a href="upload_files.php"><span>Upload</span></a></li>
  </ul>
</div>';


// create a breadcrumb navigation for the labels
$crumbDelimiter = ',';
$breadcrumbs = array();
if ($_GET['crumbs'])
{
    $breadcrumbs = explode($crumbDelimiter, $_GET['crumbs']);
}
echo '<div id="breadcrumbs">' . "\n";
echo '<a href="listview.php">All Files</a>';
foreach ($breadcrumbs as $num => $crumb)
{
    echo ' >> <a href="listview.php?crumbs=' . urlencode(implode($crumbDelimiter, array_slice($breadcrumbs, 0, $num+1))) . '">' . $crumb . '</a>';
    // while we are creating the breadcrumb navigation
    // also build the query that will fetch the files that match the selected labels.
    $qSelectedFiles .= " AND EXISTS(SELECT * FROM Labels WHERE Files.id = file_id AND label_name = '".addslashes($crumb)."')";
} 
echo "</div>\n";

// list all the available labels
$qAvailableLabels = "SELECT COUNT(file_id) AS amount, label_name FROM Labels INNER JOIN ($qSelectedFiles) AS Selected ON Labels.file_id = Selected.id GROUP BY label_name ORDER BY amount DESC";
$rAvailableLabels = mysql_query($qAvailableLabels) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qAvailableLabels );

echo '<div id="labels">' . "\n";
echo "<table>\n";
while ($row = mysql_fetch_assoc($rAvailableLabels))
{
    // don't show labels that have already been selected
    if (!in_array($row['label_name'], $breadcrumbs) ) {
        echo "<tr>\n";
        echo '<td><a href="listview.php?crumbs=' . urlencode(implode($crumbDelimiter, array_merge($breadcrumbs, array($row['label_name'])))) . '">' . $row['label_name'] . '</a>(' . $row['amount'] . ")</td>\n";
        echo "</tr>\n";
    }
}
echo "</table>\n";
echo "</div>\n";

// list all the selected files
$rSelectedFiles = mysql_query($qSelectedFiles) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qSelectedFiles );

echo '<div id="files">' . "\n";
$num = mysql_numrows($rSelectedFiles);
echo "<p>Displaying $num files.</p>\n";

echo "<table>\n";
for ($row = 0; $row < $num; ++$row)
{
    echo "<tr>\n";
    echo '<td><img src="images/mimetypes/16/' . mimeFilename(mysql_result($rSelectedFiles, $row, 'type')) . "\" /></td>\n";
    echo '<td><a href="fileview.php?id=' . mysql_result($rSelectedFiles, $row, 'id') . '">' . mysql_result($rSelectedFiles, $row, 'filename') . "</a></td>\n";
    // provide a delete button for the file is user owns it.
    if ($user->is_loaded() and $user->is_active() and (mysql_result($rSelectedFiles, $row, 'owner') == $user->get_property('username')))
    {
        echo '<td><form action="listview.php" method="POST">';
        echo '<input type="hidden" name="action" value="delete" />';
        echo '<input type="hidden" name="file_id" value="' . mysql_result($rSelectedFiles, $row, 'id') . '" />';
        echo '<input type="submit" value="Delete" />';
        echo "</form></td>\n";
    }
    echo "</tr>\n";
}
echo "</table>\n";
echo "</div>\n";

mysql_close();
?>
