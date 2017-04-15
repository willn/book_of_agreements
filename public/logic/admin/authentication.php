<?php
	if ( $_SESSION['boa-admin-passwd'] === $admin_string ) {
		$_SESSION['admin'] = true;
	}
	elseif ( !isset( $_SESSION ) || 
		$_SESSION['boa-admin-passwd'] !== $admin_string ) { 
		if ( isset( $_POST['pw'] )) {
			$_SESSION['boa-admin-passwd'] = sha1( $_POST['pw'] );
		}

		if ( $_SESSION['boa-admin-passwd'] != $admin_string ) { 
			echo '<form action="" method="post">
				admin password: <input type="password" name="pw" value="">
				</form>';
			exit( 0 );
		} 
		$_SESSION['admin'] = true;
	}
?>
