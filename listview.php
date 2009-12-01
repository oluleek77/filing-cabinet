<?php

require 'environment.php';
require_once 'access.class.php';

// connect to the database
mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);

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
$qAllFiles = 'SELECT id, filename, owner FROM Files WHERE permissions = 1';
if ($user->is_loaded() and $user->is_active())
{
    $qAllFiles .= " OR owner = '".$user->get_property('username')."'";
    echo '<p>Welcome ' . $user->get_property('username') . '. <a href="login.php?logout=1">Logout</a>.';
    echo '<a href="upload_files.php">Upload files</a></p>'."\n";
}
else {
    echo '<p>Only displaying public files. <a href="login.php">Login</a> to access your own private files.</p>'."\n";
}
$qAllFilesResult = mysql_query($qAllFiles) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qAllFiles );

// create a breadcrumb navigation for the labels
$crumbDelimiter = ',';
$breadcrumbs = array();
if ($_GET['crumbs'])
{
    $breadcrumbs = explode($crumbDelimiter, $_GET['crumbs']);
}
echo '<div class="breadcrumbs">' . "\n";
echo '<a href="listview.php">All Files</a>';
foreach ($breadcrumbs as $num => $crumb)
{
    echo ' >> <a href="listview.php?crumbs=' . urlencode(implode($crumbDelimiter, array_slice($breadcrumbs, 0, $num+1))) . '">' . $crumb . '</a>';
} 
echo "</div>\n";

$num = mysql_numrows($qAllFilesResult);
echo "<p>Displaying $num files.</p>\n";

echo "<table>\n";
for ($row = 0; $row < $num; ++$row)
{
    echo "<tr>\n";
    echo '<td><a href="fileview.php?id=' . mysql_result($qAllFilesResult, $row, 'id') . '">' . mysql_result($qAllFilesResult, $row, 'filename') . "</a></td>\n";
    // provide a delete button for the file is user owns it.
    if ($user->is_loaded() and $user->is_active() and (mysql_result($qAllFilesResult, $row, 'owner') == $user->get_property('username')))
    {
        echo '<td><form action="listview.php" method="POST">';
        echo '<input type="hidden" name="action" value="delete" />';
        echo '<input type="hidden" name="file_id" value="' . mysql_result($qAllFilesResult, $row, 'id') . '" />';
        echo '<input type="submit" value="Delete" />';
        echo "</form></td>\n";
    }
    echo "</tr>\n";
}
echo "</table>\n";

mysql_close();
?>
