<?php

// connect to the database
$user = '<insert database username>';
$password = '<insert database password>';
$database = 'filingcabinet';
mysql_connect(localhost, $user, $password);
@mysql_select_db($database) or die( 'Unable to select database');

for ($i = 1; $i <= $_POST['amount']; ++$i) {

  if ($_FILES["uploadedfile_$i"]['error'] > 0)
  {
    echo 'Error: ' . $_FILES["uploadedfile_$i"]['error'] . '<br />';
  }
  else
  {
    echo 'Upload: ' . $_FILES["uploadedfile_$i"]['name'] . '<br />';
    echo 'Type: ' . $_FILES["uploadedfile_$i"]['type'] . '<br />';
    echo 'Size: ' . ($_FILES["uploadedfile_$i"]['size'] / 1024) . ' Kb<br />';
    echo 'Labels: ' . $_POST["labels_$i"] . '<br />';
  }

  // split and trim the labels
  $labels = explode(',', $_POST["labels_$i"]);
  foreach ($labels as $id => $label) {
    $labels[$id] = strtolower(trim($label));
  }

  // use semaphore locking
  while (file_exists('.lock_archiver') && ((time() - filemtime('.lock_archiver')) < 1800)) {
    sleep(50);
  }
  @touch('.lock_archiver');

  // get id of row we're about to insert
  $qShowStatus = "SHOW TABLE STATUS LIKE 'Files'";
  $qShowStatusResult = mysql_query($qShowStatus) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qShowStatus );
  $row = mysql_fetch_assoc($qShowStatusResult);
  $next_increment = $row['Auto_increment'];


  // make the file name correspond to the id in the database
  $target_path =  'file_' . $next_increment;

  if(move_uploaded_file($_FILES["uploadedfile_$i"]['tmp_name'], $target_path)) {
    // archive the file
    echo 'archiver says: ' . exec('7za a -mx=9 -y <insert archive path and filename> ' . $target_path, $output, $return_value) . '<br />';
    if ($return_value == 0) {
        // insert the file data into the database
        mysql_query(
          "INSERT INTO Files (filename, type, size, owner, permissions)
          VALUES('".addslashes($_FILES["uploadedfile_$i"]['name'])."', '".addslashes($_FILES["uploadedfile_$i"]['type'])."', ".$_FILES["uploadedfile_$i"]['size'].",
          'tobyandmg', 0)"
        );
        // now safe to unlock because the next increment will give a new file id.
        @unlink('.lock_cabinet');

        // insert the label data into the database
        foreach ($labels as $label) {
          mysql_query(
            "INSERT INTO Labels (file_id, label_name)
            VALUES(".$next_increment.", '".addslashes($label)."')"
          );
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

mysql_close();
?>
