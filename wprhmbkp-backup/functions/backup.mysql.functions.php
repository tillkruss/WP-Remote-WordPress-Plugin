<?php

/**
 * Create the mysql backup
 *
 * Uses mysqldump if available, fallsback to PHP
 * if not.
 */
function wpr_hmbkp_backup_mysql() {

	// Use mysqldump if we can
	if ( wpr_hmbkp_mysqldump_path() )
		
		// Backup everything except whats in the exclude file
		shell_exec(
			escapeshellarg( wpr_hmbkp_mysqldump_path() )
			. ' --no-create-db '
			. ' -u ' . escapeshellarg( DB_USER )
			. ' -p'  . escapeshellarg( DB_PASSWORD )
			. ' -h ' . escapeshellarg( DB_HOST )
			. ' -r ' . escapeshellarg( wpr_hmbkp_path() . '/database_' . DB_NAME . '.sql' ) . ' ' . escapeshellarg( DB_NAME )
		);

	// Fallback to using PHP if not
	else
		wpr_hmbkp_backup_mysql_fallback();

}

/**
 * Attempt to work out the path to mysqldump
 *
 * Can be overridden by defining WPRP_HMBKP_MYSQLDUMP_PATH in
 * wp-config.php.
 *
 * @return string $path on success, empty string on failure
 */
function wpr_hmbkp_mysqldump_path() {

	if ( !wpr_hmbkp_shell_exec_available() || ( defined( 'WPRP_HMBKP_MYSQLDUMP_PATH' ) && !WPRP_HMBKP_MYSQLDUMP_PATH ) )
		return false;

	$path = '';

	// List of possible mysqldump locations
	$mysqldump_locations = array(
		'mysqldump',
		'/usr/local/bin/mysqldump',
		'/usr/local/mysql/bin/mysqldump',
		'/usr/mysql/bin/mysqldump',
		'/usr/bin/mysqldump',
		'/opt/local/lib/mysql6/bin/mysqldump',
		'/opt/local/lib/mysql5/bin/mysqldump',
		'/opt/local/lib/mysql4/bin/mysqldump',
		'\xampp\mysql\bin\mysqldump',
		'\Program Files\xampp\mysql\bin\mysqldump',
		'\Program Files\MySQL\MySQL Server 6.0\bin\mysqldump',
		'\Program Files\MySQL\MySQL Server 5.5\bin\mysqldump',
		'\Program Files\MySQL\MySQL Server 5.4\bin\mysqldump',
		'\Program Files\MySQL\MySQL Server 5.1\bin\mysqldump',
		'\Program Files\MySQL\MySQL Server 5.0\bin\mysqldump',
		'\Program Files\MySQL\MySQL Server 4.1\bin\mysqldump'
	);
	
	// Allow the path to be overridden
	if ( defined( 'WPRP_HMBKP_MYSQLDUMP_PATH' ) && WPRP_HMBKP_MYSQLDUMP_PATH )
		array_unshift( $mysqldump_locations, WPRP_HMBKP_MYSQLDUMP_PATH );

 	// If we don't have a path set
 	if ( !$path = get_option( 'wpr_hmbkp_mysqldump_path' ) ) :

		// Try to find out where mysqldump is
		foreach ( $mysqldump_locations as $location )
	 		if ( shell_exec( $location ) )
 				$path = $location;

		// Save it for later
 		if ( $path )
			update_option( 'wpr_hmbkp_mysqldump_path', $path );

	endif;

	// Check again in-case the saved path has stopped working for some reason
	if ( $path && !shell_exec( $path ) ) :
		delete_option( 'wpr_hmbkp_mysqldump_path' );
		return wpr_hmbkp_mysqldump_path();

	endif;

	return $path;

}