<?php

/**
 * Handle the backups API calls
 *
 * @param string $call
 * @return mixed
 */
function _wprp_backups_api_call( $action ) {

	if ( ! class_exists( 'hm_backup' ) )
		return new WP_Error( 'Backups module not present' );

	switch( $action ) :
		
		// TODO in the future we should do some check here to make sure they do support backups
		case 'supports_backups' :
			return true;
			
		case 'do_backup' :

			$backup = new HM_Backup();

			$upload_dir = wp_upload_dir();

			// Store the backup file in the uploads dir
			$backup->path = $upload_dir['basedir'];

			// Set a random backup filename
			$backup->archive_filename = md5( time() ) . '.zip';

			$backup->backup();
			
			if ( $errors = $backup->errors() ) {
				$wp_error = new WP_Error;
				
				foreach ( $errors as $error )
					$wp_error->add( reset( $error ), reset( $error ) );
					
				return $wp_error;
			}
			

			return str_replace( ABSPATH, site_url( '/' ), $backup->archive_filepath() );

		case 'delete_backup' :

			$upload_dir = wp_upload_dir();

			if ( ! empty( $_REQUEST['backup'] ) && file_exists( $upload_dir['basedir'] . '/' . $_REQUEST['backup'] ) && substr( $_REQUEST['backup'], -4 ) == '.zip' )
				unlink( $upload_dir['basedir'] . '/' . $_REQUEST['backup'] );

		break;

	endswitch;

}