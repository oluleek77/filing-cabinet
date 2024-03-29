<?php

require 'environment.php';
require 'common.php';
require_once 'access.class.php';

// connect to the database
mysql_connect($db_host, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);

// process label filters that are already applied
$breadcrumbs = array();
if ($_GET['crumbs'])
{
    $breadcrumbs = explode($crumbDelimiter, $_GET['crumbs']);
}
if ($_GET['label_select'])
{
    array_push($breadcrumbs, $_GET['label_select']);
}
$autocomplete_src = 'labelserver.php';
if ($breadcrumbs)
{
    $autocomplete_src .= '?crumbs=' . urlencode(implode($crumbDelimiter, $breadcrumbs));
}

// start building query for selecting files from database
$qSelectedFiles = 'SELECT * FROM Files WHERE (permissions = 1';
// if the user is logged in their private files will be selected
if ($user->is_loaded() and $user->is_active())
{
    $qSelectedFiles .= " OR owner = '".addslashes($user->get_property('username'))."')";
    //echo tabMenu(True, $user->get_property('username'));
    
}
else {
    $qSelectedFiles .= ')';
    //echo '<p>Only displaying public files. <a href="login.php">Login</a> to access your own private files.</p>'."\n";
    //echo tabMenu(False);
}

echo headA();
?>

<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.4.custom.min.js"></script>
<script type="text/javascript" src="js/fileuploader.js"></script>
<script type="text/javascript" src="js/filingcabinet.js"></script>
<link type="text/css" href="css/fileuploader.css" rel="stylesheet" />
<link type="text/css" href="css/smoothness/jquery-ui-1.8.4.custom.css" rel="stylesheet" />

<?php
echo headB('Filing Cabinet');

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
            echo 'archiver says: ' . exec("$archiver d -y $archive_path file_" . $_GET['file_id'], $output, $return_value) . "<br />\n";
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

// make an ivisible div that feeds info to js about login status
echo '<span class="begin_hidden_css" id="login_status">';
  if ($user->is_loaded() and $user->is_active()) {
      echo 'is active <span class="uname">' . $user->get_property('username') . '</span>';
  } else if ($user->is_loaded() and !($user->is_active())) { // user has not yet activated account
      echo 'not active';
  } else { // user is not logged in
      echo 'not loaded';
  }
  echo '<span id="autocomplete_src">' . $autocomplete_src . '</span>';
echo "</span>\n";


?>
<!-- place to show info messages -->
<div id="info">Only displaying public files. <br />Login to access your own private files.</div>

<!-- panel for login, logout and account management -->
<!-- three different contents, jQuery with CSS should make sure only one is visible at a time -->
<div class="main main_toggle_panel" id="login_panel">
  <div class="toggle_panel_top" id="login_title">
  <?php if ($user->is_loaded()) {
      echo $user->get_property('username');
  } else {
      echo 'Login';
  } ?>
  </div>
  <div id="login_content" class="begin_hidden_js">
           
      <noscript>          
        <p>Please enable JavaScript to use login.</p>
      </noscript> 
  
    <div class="begin_hidden_css" id="login_content_A"> <!-- when user is properly logged in -->
	  <div class="button" id="submit_logout_A" />Logout</div>
    </div>
    
    <div class="begin_hidden_css" id="login_content_B"> <!-- when user is logged in but account is not active -->
      <div>Your account is not yet active.<div class="button" id="submit_logout_B" />Logout</div></div>
    </div>
    
    <div class="begin_hidden_css" id="login_content_C"> <!-- when user is not logged in -->
	    <label for="uname">username: </label><input id="uname" type="text" /><br />
	    <label for="pwd">password: </label><input id="pwd" type="password" /><br />
	  <div class="button" id="submit_login" />Login</div>
	  <div class="toggle_panel_top" id="register_title">Register new user</div>
	    <div class="toggle_panel_bottom begin_hidden_js" id="register_content">
	      <label for="reg_uname">username: </label><input id="reg_uname" type="text" /><br />
	      <label for="reg_pwd">password: </label><input id="reg_pwd" type="password" /><br />
	      <label for="reg_email">email: </label><input id="reg_email" type="text" /><br />
	      <div class="button" id="reg_submit">Register user</div>
	    </div>
    </div>
    
  </div>
</div>

<!-- panel for file upload -->
<div class="main main_toggle_panel" id="upload_panel">
  <div class="toggle_panel_top" id="upload_title">Upload Files</div>
  <div id="upload_content" class="begin_hidden_js">
    <div id="file-uploader">       
      <noscript>          
        <p>Please enable JavaScript to use file uploader.</p>
      </noscript>         
    </div>
    <div class="button begin_hidden_js" id="add_to_cabinet">Add Files to Cabinet</div>
  </div>
</div>

<!-- create a breadcrumb navigation for the labels -->
<div class="main" id="breadcrumbs">

<form id="label_select_form" action="listview.php" method="get"><div>
<div id="filters_title">Filters (<a href="listview.php">remove all</a>)</div>

<div>
<?php
foreach ($breadcrumbs as $num => $crumb)
{
    echo '<div class="filter"> <div class="filter_name">' . $crumb . '</div>';
    echo '<div class="filter_control"><a href="listview.php?crumbs=' . urlencode(implode($crumbDelimiter, array_merge(array_slice($breadcrumbs, 0, $num), array_slice($breadcrumbs, $num+1)))) . '">Remove</a><br />';
    echo '<a href="listview.php?crumbs=' . urlencode($crumb) . '">Keep only this</a></div></div>';
    // while we are creating the breadcrumb navigation
    // also build the query that will fetch the files that match the selected labels.
    $qSelectedFiles .= " AND EXISTS(SELECT * FROM Labels WHERE Files.id = file_id AND label_name = '".addslashes($crumb)."')";
}
// add a text field to allow user to manually add label filters
echo "    <div class=\"filter\"><input id=\"label_select\" name=\"label_select\" /></div>\n";

echo "</div>\n";

if ($breadcrumbs)
{
    $crumbs = implode($crumbDelimiter, $breadcrumbs);
    echo "<input type=\"hidden\" name=\"crumbs\" value=\"$crumbs\" />";
}
if ($_GET['labelpage'])
{
    echo "<input type=\"hidden\" name=\"labelpage\" value=\"{$_GET['labelpage']}\" />";
}
if ($_GET['filepage'])
{
    echo "<input type=\"hidden\" name=\"filepage\" value=\"{$_GET['filepage']}\" />";
}
?>
    <!-- <input type="submit" value="Go" /> -->
</div></form>
</div>



<div class="main" id="labels">
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
        echo "<div class=\"toggle_panel_top\" id=\"common_title\">Common labels</div>\n";
        echo "<div class=\"toggle_panel_bottom begin_hidden_js\" id=\"common_content\"><table>\n"; 
        while ($row = mysql_fetch_assoc($rCommonLabels))
        {
            // don't show labels that have already been selected
            if (!in_array($row['label_name'], $breadcrumbs) ) {
                echo "<tr>\n";
                echo '<td class="common_label"><a href="listview.php?crumbs=' . urlencode(implode($crumbDelimiter, array_merge($breadcrumbs, array($row['label_name'])))) . "\">{$row['label_name']}</a>({$row['amount']})</td>\n";
                echo "</tr>\n";
            }
        }
        echo "</table></div>\n";
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
    if ($breadcrumbs) $href .= 'crumbs=' . urlencode(implode($crumbDelimiter, $breadcrumbs)) . '&';
    if ($_GET['filepage']) $href .= 'filepage=' . $_GET['filepage'] . '&';
    echo ':<br /> Page';
    for ($i = 0; $i < $labelCount; $i += $labels_per_page)
    {
        // at this stage it's simplist to do pagination RMS style (first page is numbered 0). May change this in future.
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
        $fontsize = 'medium';
        if ($row['amount'] < 2) $fontsize = 'small';
        if ($row['amount'] >= $label_large_amount ) $fontsize = 'large';
        if ($row['amount'] >= $label_x_large_amount ) $fontsize = 'x-large';
        echo '<td  style="font-size:'.$fontsize.'"><a href="listview.php?crumbs=' . urlencode(implode($crumbDelimiter, array_merge($breadcrumbs, array($row['label_name'])))) . '">' . htmlspecialchars($row['label_name']) . '</a>(' . $row['amount'] . ")</td>\n";
        echo "</tr>\n";
    }
}
echo "</table>\n";

echo "<p>Displaying $firstLabel - $lastLabel of $labelCount labels";
// only show pages if there is more than one page of data
if ($labelCount > $labels_per_page)
{
    $href = 'listview.php?';
    if ($breadcrumbs) $href .= 'crumbs=' . urlencode(implode($crumbDelimiter, $breadcrumbs)) . '&';
    if ($_GET['filepage']) $href .= 'filepage=' . $_GET['filepage'] . '&';
    echo ':<br /> Page';
    for ($i = 0; $i < $labelCount; $i += $labels_per_page)
    {
        // at this stage it's simplist to do pagination RMS style (first page is numbered 0). May change this in future.
        $page = $i/$labels_per_page;
        if ($page == $_GET['labelpage']) echo " $page";
        else echo " <a href=\"${href}labelpage=$page\">$page</a>";
    }
}
echo "</p>\n";

?>
</div>




<div class="main" id="files">

<?php

// count number of files selected
$qCountSelectedFiles = substr_replace($qSelectedFiles, 'COUNT(*) AS file_amount', 7, 1);
$rCountSelectedFiles = mysql_query($qCountSelectedFiles) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qSelectedFiles );
$fileCount = mysql_result($rCountSelectedFiles, 0, 'file_amount');

// if there are more than $show_common_limit files present $show_common_amount most popular and newest up front
if ($fileCount > $show_common_limit)
{
    
    $qPopularFiles = "$qSelectedFiles AND downloads > 0 ORDER BY downloads DESC, filename ASC LIMIT 0, $show_common_amount";
    if ($rPopularFiles = mysql_query($qPopularFiles))
    {
        echo "<div class=\"toggle_panel_top\" id=\"pop_title\">Popular Files</div>\n";
        echo "<div class=\"toggle_panel_bottom begin_hidden_js\" id=\"pop_content\"><table>\n"; 
        while ($row = mysql_fetch_assoc($rPopularFiles))
        {
            echo fileListing($row, True);
        }
        echo "</table></div>\n";
    }
    $qNewFiles = "$qSelectedFiles ORDER BY uploaded DESC LIMIT 0, $show_common_amount";
    if ($rNewFiles = mysql_query($qNewFiles))
    {
        echo "<div class=\"toggle_panel_top\" id=\"new_title\">New Files</div>\n";
        echo "<div class=\"toggle_panel_bottom begin_hidden_js\" id=\"new_content\"><table>\n"; 
        while ($row = mysql_fetch_assoc($rNewFiles))
        {
            echo fileListing($row, False);
        }
        echo "</table></div>\n";
    }
}

// show pages for files
$firstFile = $_GET['filepage'] * $files_per_page or 0;
$lastFile = $firstFile + $files_per_page;
if ($fileCount < $lastFile) $lastFile = $fileCount;

echo "<p>Displaying $firstFile - $lastFile of $fileCount files";
// only show pages if there is more than one page of data
if ($fileCount > $files_per_page)
{
    $href = 'listview.php?';
    if ($breadcrumbs) $href .= 'crumbs=' . urlencode(implode($crumbDelimiter, $breadcrumbs)) . '&';
    if ($_GET['labelpage']) $href .= 'labelpage=' . $_GET['labelpage'] . '&';
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
    echo fileListing($row);
}
echo "</table>\n";

echo "<p>Displaying $firstFile - $lastFile of $fileCount files";
// only show pages if there is more than one page of data
if ($fileCount > $files_per_page)
{
    $href = 'listview.php?';
    if ($breadcrumbs) $href .= 'crumbs=' . urlencode(implode($crumbDelimiter, $breadcrumbs)) . '&';
    if ($_GET['labelpage']) $href .= 'labelpage=' . $_GET['labelpage'] . '&';
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

?>

</div>
<div id="foot">
 <!--   <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10-blue.png"
        alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a> -->
</div>

<?php

mysql_close();
echo foot();
?>
