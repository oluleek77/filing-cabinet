<?php

require 'environment.php';
require_once 'access.class.php';

// connect to the database
mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);

// check if we've been asked to delete a file
if ($_POST['action'] == 'delete') {
    // must be logged in to delete files
    if (!($user->is_loaded() and $user->is_active())) {
        "<p>You need to login to delete files</p>\n";
    } else {
        echo "<p>Deleting file from archive<br />\n";
        echo 'archiver says: ' . exec("7za d -y $archive_path file_" . $_POST['file_id'], $output, $return_value) . "<br />\n";
        if ($return_value == 0) {
            $qDelFile = 'DELETE FROM Files WHERE id = ' . $_POST['file_id'];
            mysql_query($qDelFile) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qDelFile . "</p>\n");
            $qDelLabels = 'DELETE FROM Labels WHERE file_id = ' . $_POST['file_id'];
            mysql_query($qDelLabels) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qDelLabels . "</p>\n");
            echo "File deleted</p>\n";
        } else {
            echo "There was an error deleting the file.</p>\n";
        }
    }
}

// show all files
$qAllFiles = 'SELECT id, filename FROM Files';
$qAllFilesResult = mysql_query($qAllFiles) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qAllFiles );

$num = mysql_numrows($qAllFilesResult);
echo "<p>Displaying $num files.</p>\n";

echo "<table>\n";
for ($row = 0; $row < $num; ++$row) {
    echo "<tr>\n";
    echo '<td>' . mysql_result($qAllFilesResult, $row, 'filename') . "</td>\n";
    echo '<td><form action="listview.php" method="POST">';
    echo '<input type="hidden" name="action" value="delete" />';
    echo '<input type="hidden" name="file_id" value="' . mysql_result($qAllFilesResult, $row, 'id') . '" />';
    echo '<input type="submit" value="Delete" />';
    echo "</form></td>\n";
    echo "</tr>\n";
}
echo "</table>\n";

mysql_close();
?>
