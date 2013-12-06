<?php
/*
Plugin Name: Media Ally
Plugin URI: http://stephanieleary.com
Version: 0.1
Author: Stephanie Leary
Author URI: http://stephanieleary.com
Description: Provides a report on the accessibility of your media files.
Tags: accessibility, a11y, media, images, video, audio, transcripts, alt
License: GPL2
*/


// Add alt column
add_filter('manage_media_columns', 'media_ally_columns');
add_action('manage_media_custom_column', 'media_ally_ally_column', 10, 2);

function media_ally_columns($columns) {
	$options = media_ally_get_options();
	if ($options['ally_column'])
		$columns['ally_column'] = __('Alt/Transcript');
	return $columns;
}

function media_ally_ally_column($column, $id) {
	if ( $column == 'ally_column' )
		$mime = get_post_mime_type( $id );
		switch ( $mime ) {	
			case 'image/jpeg':
		    case 'image/png':
		    case 'image/gif':
		      	$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
				if ( empty( $alt ) )
					echo '<a href="'.get_edit_post_link( $id ).'" class="add_alt">'.__('Add alt text').'</a>';
				else
					echo '<span class="ok">&#10003;</span>';
				break;
			case 'video/asf':
			case 'video/avi':
			case 'video/divx':
			case 'video/x-flv':
			case 'video/quicktime':
			case 'video/mpeg':
			case 'video/mp4':
			case 'video/ogg':
			case 'video/x-matroska':
				break;
			case 'audio/mpeg':
			case 'audio/x-realaudio':
			case 'audio/wav':
			case 'audio/ogg':
			case 'audio/midi':
			case 'audio/wma':
			case 'audio/x-matroska':
				break;
		    case 'application/pdf':
		    	break;
			default: break;
	}
}

// Display the options page
function media_ally_report_page() {
?>
<div class="wrap">
	<form method="post" id="media_ally_report" action="options.php">
		<?php 
		settings_fields('media_ally');
		$options = media_ally_get_options();
		
		if ( current_user_can( 'manage_options' ) ) {
		?>
 	<h2><?php _e('Accessibility Report' ); ?></h2>  

	<table class="form-table">
		<tr>
					
				<?php
				// TODO: cache results in a transient? Show time cached & secondary button to re-check, like core update page.
				
				$img_args = array(
					'post_type' => 'attachment',
				    'post_mime_type' => 'image', 
					'post_status' => 'inherit',
					'posts_per_page' => -1,
				);
				
				$empty_alt_args = array(
					'post_type' => 'attachment',
				    'post_mime_type' => 'image', 
					'post_status' => 'inherit',
					'posts_per_page' => -1,
				    'meta_query' => array( array(
			            'key' => '_wp_attachment_image_alt',
			            'compare' => 'NOT EXISTS'
			        ) )
				);
				$all_imgs = get_posts( $img_args );
				$empty_alts = get_posts( $empty_alt_args );
				
				// TODO: replace this table with progress bars.
				
				if ( !is_wp_error( $all_imgs ) && count( $all_imgs ) > 0 ) {
					echo '<tr><th>'.sprintf( _n( 'Found %d image', 'Found %d images', count( $all_imgs ) ), count( $all_imgs ) ).'</th></tr>';
				}
				if ( !is_wp_error( $empty_alts ) && count( $empty_alts ) > 0 ) {
					echo '<tr><th>'.sprintf( _n( 'Found %d image without alt text', 'Found %d images without alt text', count( $empty_alts ) ), count( $empty_alts ) ).'</th></tr>';
					foreach ( $empty_alts as $img ) {
						echo '<tr><td><a href="'.get_edit_post_link( $img->ID ).'">'.get_the_title( $img->ID ).'</a></td></tr>';
					}
				}
				
				/* TODO: Audio and video reports. 
				Get audio/video files whose parents have empty content? Get all audio/video post formats with empty content 
					other than the embed/shortcode?
				What about embedding YouTube videos? Should we prompt the user to include a link to the transcript?
					Would users even know how to find that?
				/**/
				
				?> 
		
		</tr>
	</table>
	
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e( 'Media Library' ); ?></th>
			<td>
			<p>	<label>
					<input name="media_ally[ally_column]" type="checkbox" value="1" <?php checked( $options['ally_column'], 1 ); ?>/>
					<?php _e( 'Display alt column in Media Library' ); ?>
				</label></p>
			</td>
		</tr>
	</table>

	<p class="submit">
	<input type="submit" value="<?php esc_attr_e( 'Update Options' ); ?>" class="button-primary" />
	</p>
		
	<?php } // if current_user_can() ?>	
	</form>
</div>
<?php
}

// Add menu page and register setting
add_action('admin_menu', 'media_ally_add_pages');
function media_ally_add_pages() {
	add_options_page( 'Accessibility Report', 'Accessibility Report', 'manage_options', 'media_ally_report', 'media_ally_report_page' );
	register_setting( 'media_ally', 'media_ally', 'media_ally_validate');
}

// when uninstalled, remove option
register_uninstall_hook( __FILE__, 'media_ally_delete_options' );

function media_ally_delete_options() {
	delete_option( 'media_ally' );
}


// set defaults
function media_ally_get_options() {
	$defaults = array(
			'ally_column'		=> 0,
	);
	$options = get_option( 'media_ally' );
	if ( !is_array( $options ) ) {
		add_option( 'media_ally', $defaults, '', 'yes' );
		$options = array();
	}
	return array_merge( $defaults, $options );
}

// Validation/sanitization
function media_ally_validate( $input ) {
	$options = media_ally_get_options();
	
	if ( !isset( $input['ally_column'] ) )
		$input['ally_column'] = $options['ally_column'];
	else
		$input['ally_column'] = intval( $input['ally_column'] );
	
	return $input;
}
?>