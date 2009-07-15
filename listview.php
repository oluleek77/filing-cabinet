<?php

require 'environment.php';

// connect to the database
mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$qAllFiles = 'SELECT * FROM Files';
$qAllFilesResult = mysql_query($qAllFiles) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qAllFiles );

$num = mysql_numrows($qAllFilesResult);
echo "<p>Displaying $num files.</p>\n<table>\n";

for ($row = 0; $row < $num; ++$row) {
    echo '<tr><td>' . mysql_result($qAllFilesResult, $row, 'filename') . '</td></tr>';
}
echo '</table>';

mysql_close();
?>
