<?php

require 'common.php';
require_once 'access.class.php';

$user = new flexibleAccess();
// must be logged in to upload files
if (!($user->is_loaded() and $user->is_active())) {
  header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$rel_web_path.'/login.php?location='.urlencode('upload_files.php'));
}
echo head();
echo '<h1>Filing Cabinet</h1>';
echo tabMenu(True, $user->get_property('username'));
?>

<form enctype="multipart/form-data" action="uploader.php" method="POST">
<!-- <input type="hidden" name="MAX_FILE_SIZE" value="10485760" /> -->
<table>
  <tr>
    <th></th>
    <th>Files to upload</th>
    <th>Edit filename</th>
    <th>Comma separated list of labels</th>
    <th>Allow public access</th>
    <th>Sequence</th>
  </tr>
<?php
// amount of files that can be uploaded in one go
$amount = 10;
for ($i = 1; $i <= $amount; ++$i):
?>
  <tr>
    <td>File <?php echo $i ?></td>
    <td><input name='uploadedfile_<?php echo $i ?>' type='file' onchange="document.getElementById('name_<?php echo $i ?>').value = this.value" /></td>
    <td><input name='filename_<?php echo $i ?>' id='name_<?php echo $i ?>' type='text' /></td>
    <td><input name='labels_<?php echo $i ?>' type='text' /></td>
    <td><input name='public_<?php echo $i ?>' type='checkbox' /></td>
    <td>Followed in sequence by <select name='sequence_<?php echo $i ?>'>
    <?php
    for ($j = 1; $j <= $amount; ++$j){
      if ($j == $i) {
        echo "<option value='$j' selected>[Not followed]</option>\n";
      } else {
        echo "<option value='$j'>File $j</option>\n";
      }
    }
    ?>
    </select></td>
  </tr>
<?php
endfor;
?>
</table> <br />
<input type='hidden' name='amount' value='<?php echo $amount ?>' />
<input type='submit' value='Upload Files' />
</form>
</body>
</html>
