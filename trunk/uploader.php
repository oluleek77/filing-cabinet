<?php

require_once 'environment.php';
require_once 'access.class.php';
require_once 'common.php';

// connect to the database
$db_link = mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);
// must be logged in to upload files
if (!($user->is_loaded() and $user->is_active())) {
  header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$rel_web_path.'/login.php?location=upload_files.php');
}

$fileID = array(); // for keeping track of which files have which ids
$sequence = array(); // for keeping track of which files are in sequence with which

echo head();
echo tabMenu(True);

for ($i = 1; $i <= (int)$_POST['amount']; ++$i) {

  if ($_FILES["uploadedfile_$i"]['error'] == 4)
  {
    continue; // this part of the form has not been filled in
  }
  else if ($_FILES["uploadedfile_$i"]['error'] > 0)
  {
    echo "<p>File $i Error: " . $_FILES["uploadedfile_$i"]['error'] . "</p>\n";
    continue;
  }
  else
  {
    echo '<p>Upload: ' . $_FILES["uploadedfile_$i"]['name'] . '<br />';
    //echo 'Type: ' . $_FILES["uploadedfile_$i"]['type'] . '<br />';
    //echo 'Size: ' . ($_FILES["uploadedfile_$i"]['size'] / 1024) . ' Kb<br />';
    //echo 'Labels: ' . $_POST["labels_$i"] . '</p>';
  }

  // split and trim the labels
  $labels = parseLabels($_POST["labels_$i"]);

  // use semaphore locking
  while (file_exists('.lock_cabinet') && ((time() - filemtime('.lock_cabinet')) < 1800)) {
    sleep(50);
  }
  @touch('.lock_cabinet');

  // get id of row we're about to insert
  $qShowStatus = "SHOW TABLE STATUS LIKE 'Files'";
  $qShowStatusResult = mysql_query($qShowStatus) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qShowStatus );
  $row = mysql_fetch_assoc($qShowStatusResult);
  $fileID["File $i"] = $row['Auto_increment'];


  // make the file name correspond to the id in the database
  $target_path =  'file_' . $fileID["File $i"];

  if(move_uploaded_file($_FILES["uploadedfile_$i"]['tmp_name'], $target_path)) {
    // archive the file
    echo 'archiver says: ' . exec("7za a -mx=9 -y $archive_path $target_path", $output, $return_value) . '<br />';
    if ($return_value == 0) {
        // insert the file data into the database
        mysql_query(
          "INSERT INTO Files (filename, type, size, owner, permissions)
          VALUES('".addslashes($_FILES["uploadedfile_$i"]['name'])."', '".addslashes($_FILES["uploadedfile_$i"]['type'])."', ".$_FILES["uploadedfile_$i"]['size'].",
          '".addslashes($user->get_property('username'))."', ".(($_POST["public_$i"] == 'on')?'1':'0').")"
        ); 
        // now safe to unlock because the next increment will give a new file id.
        @unlink('.lock_cabinet');

        // insert the label data into the database
        foreach ($labels as $label) {
          if ($label != '') {
            mysql_query(
              "INSERT INTO Labels (file_id, label_name)
              VALUES(".$fileID["File $i"].", '".addslashes($label)."')"
            );
          }
        }
        
        // record sequence data
        // if sequence number is the same as the file number then no sequence is recorded.
        if ($i != (int)$_POST["sequence_$i"]) {
            $sequence[$fileID["File $i"]] = 'File ' . $_POST["sequence_$i"];
        }
        
        // remove the file from the upload location
        exec('rm ' . $target_path);
    } else {
        @unlink('.lock_cabinet');
        echo 'There was an error archiving the file: ' . $_FILES["uploadedfile_$i"]['name'];
    }
  } else {
    @unlink('.lock_cabinet');
    echo 'There was an error uploading the file: ' . $_FILES["uploadedfile_$i"]['name'];
  }

}

//print_r($fileID);
//echo '<br />';
//print_r($sequence);

// put the sequence data into the database
foreach ($sequence as $file_ID => $target)
{
    mysql_query(
        "UPDATE Files
        SET next_file_id = " . $fileID[$target] . "
        WHERE id = $file_ID"
    );
}

echo foot();
mysql_close();
?>
