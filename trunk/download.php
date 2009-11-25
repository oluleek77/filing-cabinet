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
    exit(0);
}
// redirect if user does not have permission to view the file
// (if the file is not public and they are not the owner of it)
if (mysql_result($qFileDataResult, 0, 'permissions') == 0 and mysql_result($qFileDataResult, 0, 'owner') != $user->get_property('username')) {
    header('Location: listview.php');
    exit(0);
}

// extract the file from the archive
exec("7za e -y -o$archive_dir $archive_path file_" . $_GET['id'], $output, $return_value);

if ($return_value > 1) {
    echo 'error extracting file from archive';
    foreach($output as $out) {
        echo $out . '</br>';
    }
    exit(1);
}

// let the user download the file
if (file_exists("$archive_dir/file_" . $_GET['id'])) {
    header('Content-disposition: attachment; filename=' . mysql_result($qFileDataResult, 0, 'filename'));
    header('Content-type: ' . mysql_result($qFileDataResult, 0, 'type'));
    readfile("$archive_dir/file_" . $_GET['id']);
    exec("rm $archive_dir/file_" . $_GET['id']);
    exit(0);
} else {
    echo 'unable to download file';
    exit(1);
}
?>
