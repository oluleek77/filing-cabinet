<?php

require 'environment.php';
require 'common.php';
require_once 'access.class.php';

// connect to the database
mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);
echo head();

echo '<h1>Filing Cabinet</h1>';

// check if we've been asked to delete a file
if ($_GET['action'] == 'delete')
{
    // must be logged in to delete files
    if (!($user->is_loaded() and $user->is_active()))
    {
        echo "<p>You need to login to delete files.</p>\n";
    }
    else {
        // user must own the file to delete it
        $fileOwner = 'SELECT owner FROM Files WHERE id = ' . $_GET['file_id'];
        $fileOwnerResult = mysql_query($fileOwner) or die ('Query failed: ' . mysql_error() . '<br />' . $fileOwner);
        if (mysql_result($fileOwnerResult, 0, 'owner') != $user->get_property('username'))
        {
            echo "<p>You need to own the file to delete it.</p>\n";
        }
        else {
            echo "<p>Deleting file from archive<br />\n";
            echo 'archiver says: ' . exec("7za d -y $archive_path file_" . $_GET['file_id'], $output, $return_value) . "<br />\n";
            if ($return_value == 0)
            {
                $qDelFile = 'DELETE FROM Files WHERE id = ' . $_GET['file_id'];
                mysql_query($qDelFile) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qDelFile . "</p>\n");
                $qDelLabels = 'DELETE FROM Labels WHERE file_id = ' . $_GET['file_id'];
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
    echo tabMenu(True, $user->get_property('username'));
}
else {
    $qSelectedFiles .= ')';
    echo '<p>Only displaying public files. <a href="login.php">Login</a> to access your own private files.</p>'."\n";
    echo tabMenu(False);
}

// create a breadcrumb navigation for the labels
$crumbDelimiter = ',';
$breadcrumbs = array();
if ($_GET['crumbs'])
{
    $breadcrumbs = explode($crumbDelimiter, $_GET['crumbs']);
}
?>
<!-- css rounded corners, without images or javascript -->
<div id="breadcrumbs">
  <b class="spiffy">
  <b class="spiffy1"><b></b></b>
  <b class="spiffy2"><b></b></b>
  <b class="spiffy3"></b>
  <b class="spiffy4"></b>
  <b class="spiffy5"></b></b>

  <div class="spiffyfg">
<?php

echo '<a href="listview.php">All Files</a>';
foreach ($breadcrumbs as $num => $crumb)
{
    echo ' >> <a href="listview.php?crumbs=' . urlencode(implode($crumbDelimiter, array_slice($breadcrumbs, 0, $num+1))) . '">' . $crumb . '</a>';
    // while we are creating the breadcrumb navigation
    // also build the query that will fetch the files that match the selected labels.
    $qSelectedFiles .= " AND EXISTS(SELECT * FROM Labels WHERE Files.id = file_id AND label_name = '".addslashes($crumb)."')";
} 
?>
  </div>

  <b class="spiffy">
  <b class="spiffy5"></b>
  <b class="spiffy4"></b>
  <b class="spiffy3"></b>
  <b class="spiffy2"><b></b></b>
  <b class="spiffy1"><b></b></b></b>
</div>

<div id="labels">
  <b class="spiffy">
  <b class="spiffy1"><b></b></b>
  <b class="spiffy2"><b></b></b>
  <b class="spiffy3"></b>
  <b class="spiffy4"></b>
  <b class="spiffy5"></b></b>

  <div class="spiffyfg">
<?php

// count all the available labels i.e. labels that occur on the selected files
$qCountAvailableLabels = "SELECT COUNT(DISTINCT label_name) AS label_amount FROM Labels INNER JOIN ($qSelectedFiles) AS Selected ON Labels.file_id = Selected.id";
$rCountAvailableLabels = mysql_query($qCountAvailableLabels) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qCountAvailableLabels );
$labelCount = mysql_result($rCountAvailableLabels, 0, 'label_amount');

// if there are more than $show_common_limit labels present $show_common_amount most common up front
if ($labelCount > $show_common_limit)
{
    // must add length of breadcrumbs to show common amount since
    // labels that are in the breadcrumbs get included then ignored
    $common_amount_crumbs = $show_common_amount + count($breadcrumbs);
    $qCommonLabels = "SELECT COUNT(file_id) AS amount, label_name FROM Labels INNER JOIN ($qSelectedFiles) AS Selected ON Labels.file_id = Selected.id GROUP BY label_name ORDER BY amount DESC, label_name ASC LIMIT 0, $common_amount_crumbs";
    if ($rCommonLabels = mysql_query($qCommonLabels))
    {
        echo "<table>\n";
        echo '<tr><th>Most common labels:</th></tr>'; 
        while ($row = mysql_fetch_assoc($rCommonLabels))
        {
            // don't show labels that have already been selected
            if (!in_array($row['label_name'], $breadcrumbs) ) {
                echo "<tr>\n";
                echo '<td class="common_label"><a href="listview.php?crumbs=' . urlencode(implode($crumbDelimiter, array_merge($breadcrumbs, array($row['label_name'])))) . "\">{$row['label_name']}</a>({$row['amount']})</td>\n";
                echo "</tr>\n";
            }
        }
        echo "</table>\n";
    }
}

// show pages for label
$firstLabel = $_GET['labelpage'] * $labels_per_page or 0;
$lastLabel = $firstLabel + $labels_per_page;
if ($labelCount < $lastLabel) $lastLabel = $labelCount;

echo "<p>Displaying $firstLabel - $lastLabel of $labelCount labels";
// only show pages if there is more than one page of data
if ($labelCount > $labels_per_page)
{
    $href = 'listview.php?';
    if ($breadcrumbs) $href .= urlencode(implode($crumbDelimiter, $breadcrumbs)) . '&';
    if ($_GET['filepage']) $href .= 'filepage=' . $_GET['filepage'];
    echo ':<br /> Page';
    for ($i = 0; $i < $labelCount; $i += $labels_per_page)
    {
        // at this stage it's simplist to do pagination RMS style. May change this in future.
        $page = $i/$labels_per_page;
        if ($page == $_GET['labelpage']) echo " $page";
        else echo " <a href=\"${href}labelpage=$page\">$page</a>";
    }
}
echo "</p>\n";

// get current page of labels
$qAvailableLabels = "SELECT COUNT(file_id) AS amount, label_name FROM Labels INNER JOIN ($qSelectedFiles) AS Selected ON Labels.file_id = Selected.id GROUP BY label_name ORDER BY label_name ASC LIMIT $firstLabel, $labels_per_page";
$rAvailableLabels = mysql_query($qAvailableLabels) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qAvailableLabels );

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
?>
  </div>

  <b class="spiffy">
  <b class="spiffy5"></b>
  <b class="spiffy4"></b>
  <b class="spiffy3"></b>
  <b class="spiffy2"><b></b></b>
  <b class="spiffy1"><b></b></b></b>
</div>

<div id="files">
  <b class="spiffy">
  <b class="spiffy1"><b></b></b>
  <b class="spiffy2"><b></b></b>
  <b class="spiffy3"></b>
  <b class="spiffy4"></b>
  <b class="spiffy5"></b></b>

  <div class="spiffyfg">
<?php

// count number of files selected
$qCountSelectedFiles = substr_replace($qSelectedFiles, 'COUNT(*) AS file_amount', 7, 1);
$rCountSelectedFiles = mysql_query($qCountSelectedFiles) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qSelectedFiles );
$fileCount = mysql_result($rCountSelectedFiles, 0, 'file_amount');

// show pages for files
$firstFile = $_GET['filepage'] * $files_per_page or 0;
$lastFile = $firstFile + $files_per_page;
if ($fileCount < $lastFile) $lastFile = $fileCount;

echo "<p>Displaying $firstFile - $lastFile of $fileCount files";
// only show pages if there is more than one page of data
if ($fileCount > $files_per_page)
{
    $href = 'listview.php?';
    if ($breadcrumbs) $href .= urlencode(implode($crumbDelimiter, $breadcrumbs)) . '&';
    if ($_GET['labelpage']) $href .= 'labelpage=' . $_GET['labelpage'];
    echo ':<br /> Page';
    for ($i = 0; $i < $fileCount; $i += $files_per_page)
    {
        // at this stage it's simplist to do pagination RMS style. May change this in future.
        $page = $i/$files_per_page;
        if ($page == $_GET['filepage']) echo " $page";
        else echo " <a href=\"${href}filepage=$page\">$page</a>";
    }
}
echo "</p>\n";

// get current page of the selected files
$qSelectedFiles .= " ORDER BY filename ASC LIMIT $firstFile, $files_per_page";
$rSelectedFiles = mysql_query($qSelectedFiles) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qSelectedFiles );

echo "<table>\n";
while ($row = mysql_fetch_assoc($rSelectedFiles))
{
    echo "<tr>\n";
    // show MIME icon
    echo '<td><img src="images/mimetypes/16/' . mimeFilename($row['type']) . "\" alt=\"{$row['type']}\" /></td>\n";
    // filename linked fo fileview
    echo "<td><a href=\"fileview.php?id={$row['id']}\">{$row['filename']}</a></td>\n";
    // provide a download button
    echo "<td><a href=\"download?id={$row['id']}\"><img src=\"images/download-32.png\" alt=\"Download\" /></a></td>\n";
    // provide a delete button for the file is user owns it.
    /*if ($user->is_loaded() and $user->is_active() and (mysql_result($rSelectedFiles, $row, 'owner') == $user->get_property('username')))
    {
        echo '<td><form action="listview.php" method="GET">';
        echo '<input type="hidden" name="action" value="delete" />';
        echo '<input type="hidden" name="file_id" value="' . mysql_result($rSelectedFiles, $row, 'id') . '" />';
        echo '<input type="submit" value="Delete" />';
        echo "</form></td>\n";
    }*/
    echo "</tr>\n"; 
}
echo "</table>\n";
?>
  </div>

  <b class="spiffy">
  <b class="spiffy5"></b>
  <b class="spiffy4"></b>
  <b class="spiffy3"></b>
  <b class="spiffy2"><b></b></b>
  <b class="spiffy1"><b></b></b></b>
</div>
<?php

mysql_close();
echo foot();
?>
