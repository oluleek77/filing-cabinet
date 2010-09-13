<?
/*
Basic login example with php user class
http://phpUserClass.com
*/
require 'common.php';
require_once 'access.class.php';

$user = new flexibleAccess();

if ( $_POST['logout'] == 1 ) {
	$user->logout();
	echo 'User logged out';
}

else if ( !$user->is_loaded() )
{
	//Login stuff:
	if ( isset($_POST['uname']) && isset($_POST['pwd'])){
	  if ( !$user->login($_POST['uname'],$_POST['pwd'],$_POST['remember'] )){// we don't have to use addslashes as access.class does the job
	    echo '<span class="failure">Login failed.</span>';
	  }else{
	    //user is now loaded
	    echo "<span class=\"success\">Login for <span class=\"uname\">{$user->get_property('username')}</span> succeeded.</span>";
	    if ( !$user->is_active() )
	    {
	      echo "<br />Your account has not yet been activated.";
	      echo "<br />Please follow the instructions emailed to {$user->get_property('email')} to activate the account.";
	      echo "<br />If you have trouble contact an administrator.";
	    }
	  }
	}

}
else if (!$user->is_active()){
    echo "<br />Your account has not yet been activated.";
    echo "<br />Please follow the instructions emailed to {$user->get_property('email')} to activate the account.";
    echo "<br />If you have trouble contact an administrator.";
} else {
    echo 'Already logged in!';
}
?>
