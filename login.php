<?
/*
Basic login example with php user class
http://phpUserClass.com
*/
require 'common.php';
require_once 'access.class.php';

$user = new flexibleAccess();

if ( $_GET['logout'] == 1 ) 
	$user->logout('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);

if ( !$user->is_loaded() )
{
	//Login stuff:
	if ( isset($_POST['uname']) && isset($_POST['pwd'])){
	  if ( !$user->login($_POST['uname'],$_POST['pwd'],$_POST['remember'] )){// we don't have to use addslashes as access.class does the job
	    echo 'Wrong username and/or password';
	  }else{
	    //user is now loaded
	    header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	  }
	}
	if (!isset($_GET['location'])) $_GET['location'] = urlencode('listview.php');
	echo head();
	echo '<h1>Filing Cabinet</h1>';
	echo tabMenu(False);
	echo '<h2>Filing Cabinet Login</h2>
    <p>You do not have to login to <a href="listview.php">access public files</a>.</p>
	<p><form method="post" action="'.$_SERVER['PHP_SELF'].'" />
	 username: <input type="text" name="uname" /><br /><br />
	 password: <input type="password" name="pwd" /><br /><br />
	 <input type="hidden" name="location" value="'.$_GET['location'].'" />
	 <input type="submit" value="login" />
	</form>
	</p>
	<p>Or you may <a href="register.php">create a new account</a>.</p>';
	echo foot();
}
else if (!$user->is_active()){
    echo head();
    echo '<p>Your account is not active. When you registered you should have got an email giving you directions
    on how to activate your account. If there is a problem contat the administrator.</p>';
    echo '<p><a href="'.$_SERVER['PHP_SELF'].'?logout=1">logout</a></p>';
    echo foot();
} else {
    // User is loaded, so send them on to where they want to go.
    if (isset($_POST['location'])) {
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$rel_web_path.'/'.urldecode($_POST['location']));
    } else {
        // if no location is specified go to list view.
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$rel_web_path.'/'.'listview.php');
    }
}
?>
