<?php

require 'common.php';
require_once 'access.class.php';

$user = new flexibleAccess();
// must be logged in to upload files
if (!($user->is_loaded() and $user->is_active())) {
  header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$rel_web_path.'/login.php?location='.urlencode('upload_files.php'));
}
echo head();
echo tabMenu(True, $user->get_property('username'));
?>

<form enctype="multipart/form-data" action="uploader.php" method="post">
<!-- <input type="hidden" name="MAX_FILE_SIZE" value="10485760" /> -->
<table id='upload'>
  <tr>
    <th></th>
    <th title='Choose a file to upload'>Files to upload</th>
    <th title='Rename the file (optional)'>Edit filename</th>
    <th title='Comma separated list of labels for the file'>Labels</th>
    <th title='Allow anyone to download the file?'>Public</th>
    <th title='File follows in sequence from previous file?'>Sequence</th>
  </tr>
<?php
// amount of files that can be uploaded in one go
$amount = 10;
for ($i = 1; $i <= $amount; ++$i):
?>
  <tr>
    <td>File <?php echo $i ?></td>
    <td><input title='Choose a file to upload' name='uploadedfile_<?php echo $i ?>' type='file' onchange="document.getElementById('name_<?php echo $i ?>').value = this.value" /></td>
    <td><input title='Rename the file (optional)' name='filename_<?php echo $i ?>' id='name_<?php echo $i ?>' type='text' /></td>
    <td><input title='Comma separated list of labels for the file' name='labels_<?php echo $i ?>' type='text' /></td>
    <td><input title='Allow anyone to download the file?' name='public_<?php echo $i ?>' type='checkbox' /></td>
    <?php if ($i > 1): // don't need sequence option on the first file ?> 
        <td><input title='File follows in sequence from File <?php echo $i - 1 ?>?' name='sequence_<?php echo $i ?>' type='checkbox' /></td>
    <?php endif; ?>
  </tr>
<?php
endfor;
?>
</table>
  <div>
    <input type='hidden' name='amount' value='<?php echo $amount ?>' />
    <input type='submit' value='Upload Files' />
  </div>
</form>
<div id="foot">
    <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10-blue.png"
        alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a>
</div>
</body>
</html>
