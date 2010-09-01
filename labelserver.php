<?php

require_once 'environment.php';
require_once 'common.php';
require_once 'access.class.php';

// connect to the database
mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

// need to check if user is logged in
$user = new flexibleAccess($db_link);
// if they're logged in they have access to their private files,
// so labels associated with these files should be included
$qSelectedFiles = 'SELECT * FROM Files WHERE (permissions = 1';
if ($user->is_loaded() and $user->is_active())
{
    $qSelectedFiles .= " OR owner = '".addslashes($user->get_property('username'))."')";
}
else {
    $qSelectedFiles .= ')';
}

// we only want to include labels associated with files that haven't already been filtered out
// so check for existing label filters
$filters = array();
if ($_GET['crumbs'])
{
    $filters = explode($crumbDelimiter, $_GET['crumbs']);
}
foreach ($filters as $filter)
{
    $qSelectedFiles .= " AND EXISTS(SELECT * FROM Labels WHERE Files.id = file_id AND label_name = '".addslashes($filter)."')";
}

// query the database
$qAvailableLabels = "SELECT DISTINCT label_name FROM Labels INNER JOIN ($qSelectedFiles) AS Selected ON Labels.file_id = Selected.id WHERE label_name LIKE '%{$_GET['term']}%'";
$rAvailableLabels = mysql_query($qAvailableLabels) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qAvailableLabels );

// store all returned labels in an array
$arrAvailableLabels = array();
while ($row = mysql_fetch_assoc($rAvailableLabels))
{
    // don't include labels that have already been selected
    if (!in_array($row['label_name'], $filters) ) {
         array_push($arrAvailableLabels, $row['label_name']);
    }
}

mysql_close();

// output the labels as a JSON encoded array
echo json_encode($arrAvailableLabels)
?>
