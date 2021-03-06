<?php
namespace Qazana\Template_Library;

use Qazana\Core\Base\Document;
use Qazana\DB;
use Qazana\Core\Settings\Page\Manager as PageSettingsManager;
use Qazana\Core\Settings\Manager as SettingsManager;
use Qazana\Core\Settings\Page\Model;
use Qazana\Editor;
use Qazana\Settings;
use Qazana\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Qazana template library local source.
 *
 * Qazana template library local source handler class is responsible for
 * handling local Qazana templates saved by the user locally on his site.
 *
 * @since 1.0.0
 */
class Source_Local extends Source_Base {

	/**
	 * Qazana template-library post-type slug.
	 */
	const CPT = 'qazana_library';

	/**
	 * Qazana template-library taxonomy slug.
	 */
	const TAXONOMY_TYPE_SLUG = 'qazana_library_type';

	/**
	 * Qazana template-library meta key.
	 */
	const TYPE_META_KEY = '_qazana_template_type';

	/**
	 * Qazana template-library temporary files folder.
	 */
	const TEMP_FILES_DIR = 'qazana/tmp';

	/**
	 * Qazana template-library bulk export action name.
	 */
	const BULK_EXPORT_ACTION = 'qazana_export_multiple_templates';

	/**
	 * Template types.
	 *
	 * Holds the list of supported template types that can be displayed.
	 *
	 * @access private
	 * @static
	 *
	 * @var array
	 */
	private static $_template_types = [];

	/**
	 * Post type object.
	 *
	 * Holds the post type object of the current post.
	 *
	 * @access private
	 *
	 * @var \WP_Post_Type
	 */
	private $post_type_object;

	/**
	 * Get local template type.
	 *
	 * Retrieve the template type from the post meta.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return mixed The value of meta data field.
	 */
	public static function get_template_type( $template_id ) {
		return get_post_meta( $template_id, self::TYPE_META_KEY, true );
	}

	/**
	 * Is base templates screen.
	 *
	 * Whether the current screen base is edit and the post type is template.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return bool True on base templates screen, False otherwise.
	 */
	public static function is_base_templates_screen() {
		global $current_screen;

		if ( ! $current_screen ) {
			return false;
		}

		return 'edit' === $current_screen->base && self::CPT === $current_screen->post_type;
	}

	/**
	 * Add template type.
	 *
	 * Register new template type to the list of supported local template types.
	 *
	 * @since 1.0.3
	 * @access public
	 * @static
	 *
	 * @param string $type Template type.
	 */
	public static function add_template_type( $type ) {
		self::$_template_types[ $type ] = $type;
	}

	/**
	 * Remove template type.
	 *
	 * Remove existing template type from the list of supported local template
	 * types.
	 *
	 * @since 1.8.0
	 * @access public
	 * @static
	 *
	 * @param string $type Template type.
	 */
	public static function remove_template_type( $type ) {
		if ( isset( self::$_template_types[ $type ] ) ) {
			unset( self::$_template_types[ $type ] );
		}
	}

	/**
	 * Get local template ID.
	 *
	 * Retrieve the local template ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The local template ID.
	 */
	public function get_id() {
		return 'local';
	}

	/**
	 * Get local template title.
	 *
	 * Retrieve the local template title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The local template title.
	 */
	public function get_title() {
		return __( 'Local', 'qazana' );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * Registers all the admin scripts and enqueues them.
	 *
	 * Fired by `admin_enqueue_scripts` action.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function admin_enqueue_scripts() {
		if ( in_array( get_current_screen()->id, [ 'qazana_library', 'edit-qazana_library' ], true ) ) {
			wp_enqueue_script( 'qazana-dialog' );
			add_action( 'admin_footer', [ $this, 'print_new_template_dialog' ] );
		}
	}

	/**
	 * Print new template dialog.
	 *
	 * Used to output the new template dialog.
	 *
	 * Fired by `admin_footer` action.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function print_new_template_dialog() {
		$document_types = qazana()->documents->get_document_types();
		$types = [];
		$selected = get_query_var( 'qazana_library_type' );

		foreach ( $document_types as $document_type ) {
			if ( $document_type::get_property( 'show_in_library' ) ) {
				/**
				 * @var Document $instance
				 */
				$instance = new $document_type();

				$types[ $instance->get_name() ] = $document_type::get_title();
			}
		}

		/**
		 * Create new template library dialog types.
		 *
		 * Filters the dialog types when printing new template dialog.
		 *
		 * @since 2.0.0
		 *
		 * @param array    $types          Types data.
		 * @param Document $document_types Document types.
		 */
		$types = apply_filters( 'qazana/template-library/create_new_dialog_types', $types, $document_types );
		?>
		<div id="qazana-new-template-dialog" style="display: none">
			<div class="qazana-templates-modal__header">
				<div class="qazana-templates-modal__header__logo-area">
					<div class="qazana-templates-modal__header__logo">
					<span class="qazana-templates-modal__header__logo__icon-wrapper">
						<i class="eicon-qazana"></i>
					</span>
					<span><?php _e( 'New Template', 'qazana' ); ?></span>
					</div>
				</div>
				<div class="qazana-templates-modal__header__items-area">
					<div class="qazana-templates-modal__header__close-modal qazana-templates-modal__header__item">
						<i class="eicon-close" aria-hidden="true" title="Close"></i>
						<span class="qazana-screen-only"><?php _e( 'Close', 'qazana' ); ?></span>
					</div>
				</div>
			</div>
			<div id="qazana-new-template-dialog-content">
				<div id="qazana-new-template__description">
					<div id="qazana-new-template__description__title"><?php _e( 'Templates Help You <span>Work Efficiently</span>', 'qazana' ); ?></div>
					<div id="qazana-new-template__description__content"><?php _e( 'Use templates to create the different pieces of your site, and reuse them with one click whenever needed.', 'qazana' ); ?></div>
					<?php
					/*
					<div id="qazana-new-template__take_a_tour">
						<i class="eicon-play-o"></i>
						<a href="#"><?php _e( 'Take The Video Tour', 'qazana' ); ?></a>
					</div>
                    */
					?>
				</div>
				<form id="qazana-new-template__form" action="<?php esc_url( admin_url( '/edit.php' ) ); ?>">
					<input type="hidden" name="post_type" value="qazana_library">
					<input type="hidden" name="action" value="qazana_new_post">
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'qazana_action_new_post' ); ?>">
					<div id="qazana-new-template__form__title"><?php _e( 'Choose Template Type', 'qazana' ); ?></div>
					<div id="qazana-new-template__form__template-type__wrapper" class="qazana-form-field">
						<label for="qazana-new-template__form__template-type" class="qazana-form-field__label"><?php _e( 'Select the type of template you want to work on', 'qazana' ); ?></label>
						<div class="qazana-form-field__select__wrapper">
							<select id="qazana-new-template__form__template-type" class="qazana-form-field__select" name="template_type" required>
								<option value=""><?php _e( 'Select', 'qazana' ); ?>...</option>
								<?php
								foreach ( $types as $value => $title ) {
									printf( '<option value="%1$s" %2$s>%3$s</option>', $value, selected( $selected, $value, false ), $title );
								}
								?>
							</select>
						</div>
					</div>
					<?php
					/**
					 * Template library dialog fields.
					 *
					 * Fires after Qazana template library dialog fields are displayed.
					 *
					 * @since 2.0.0
					 */
					do_action( 'qazana/template-library/create_new_dialog_fields' );
					?>

					<div id="qazana-new-template__form__post-title__wrapper" class="qazana-form-field">
						<label for="qazana-new-template__form__post-title" class="qazana-form-field__label">
							<?php _e( 'Name your template', 'qazana' ); ?>
						</label>
						<div class="qazana-form-field__text__wrapper">
							<input type="text" placeholder="<?php echo esc_attr__( 'Enter template name (optional)', 'qazana' ); ?>" id="qazana-new-template__form__post-title" class="qazana-form-field__text" name="post_data[post_title]">
						</div>
					</div>
					<button id="qazana-new-template__form__submit" class="qazana-button qazana-button-success"><?php _e( 'Create Template', 'qazana' ); ?></button>
				</form>
			</div>
		</div>
		<?php

	}

	/**
	 * Register local template data.
	 *
	 * Used to register custom template data like a post type, a taxonomy or any
	 * other data.
	 *
	 * The local template class registers a new `qazana_library` post type
	 * and an `qazana_library_type` taxonomy. They are used to store data for
	 * local templates saved by the user on his site.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_data() {
		$labels = [
			'name' => _x( 'My Templates', 'Template Library', 'qazana' ),
			'singular_name' => _x( 'Template', 'Template Library', 'qazana' ),
			'add_new' => _x( 'Add New', 'Template Library', 'qazana' ),
			'add_new_item' => _x( 'Add New Template', 'Template Library', 'qazana' ),
			'edit_item' => _x( 'Edit Template', 'Template Library', 'qazana' ),
			'new_item' => _x( 'New Template', 'Template Library', 'qazana' ),
			'all_items' => _x( 'All Templates', 'Template Library', 'qazana' ),
			'view_item' => _x( 'View Template', 'Template Library', 'qazana' ),
			'search_items' => _x( 'Search Template', 'Template Library', 'qazana' ),
			'not_found' => _x( 'No Templates found', 'Template Library', 'qazana' ),
			'not_found_in_trash' => _x( 'No Templates found in Trash', 'Template Library', 'qazana' ),
			'parent_item_colon' => '',
			'menu_name' => _x( 'My Templates', 'Template Library', 'qazana' ),
		];

		$args = [
			'labels' => $labels,
			'public' => true,
			'rewrite' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => [ 'title', 'thumbnail', 'author', 'qazana' ],
		];

		/**
		 * Register template library post type args.
		 *
		 * Filters the post type arguments when registering qazana template library post type.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Arguments for registering a post type.
		 */
		$args = apply_filters( 'qazana/template_library/sources/local/register_post_type_args', $args );

		$this->post_type_object = register_post_type( self::CPT, $args );

		$args = [
			'hierarchical' => false,
			'show_ui' => false,
			'show_in_nav_menus' => false,
			'show_admin_column' => true,
			'query_var' => is_admin(),
			'rewrite' => false,
			'public' => false,
			'label' => _x( 'Type', 'Template Library', 'qazana' ),
		];

		/**
		 * Register template library taxonomy args.
		 *
		 * Filters the taxonomy arguments when registering qazana template library taxonomy.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Arguments for registering a taxonomy.
		 */
		$args = apply_filters( 'qazana/template_library/sources/local/register_taxonomy_args', $args );

		register_taxonomy( self::TAXONOMY_TYPE_SLUG, self::CPT, $args );
	}

	/**
	 * Register admin menu.
	 *
	 * Add a top-level menu page for Qazana Template Library.
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_admin_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			add_submenu_page(
				qazana()->slug,
				_x( 'My Templates', 'Template Library', 'qazana' ),
				_x( 'Templates', 'Template Library', 'qazana' ),
				Editor::EDITING_CAPABILITY,
				'edit.php?post_type=' . self::CPT
			);
		} else {
			add_menu_page(
				__( 'Qazana', 'qazana' ),
				__( 'Qazana', 'qazana' ),
				Editor::EDITING_CAPABILITY,
				'edit.php?post_type=' . self::CPT,
				'',
				'',
				99
			);
		}
	}

	/**
	 * Get local templates.
	 *
	 * Retrieve local templates saved by the user on his site.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Optional. Filter templates list based on a set of
	 *                    arguments. Default is an empty array.
	 *
	 * @return array Local templates.
	 */
	public function get_items( $args = [] ) {
		$templates_query = new \WP_Query(
			[
				'post_type' => self::CPT,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_query' => [
					[
						'key' => self::TYPE_META_KEY,
						'value' => array_values( self::$_template_types ),
					],
				],
			]
		);

		$templates = [];

		if ( $templates_query->have_posts() ) {
			foreach ( $templates_query->get_posts() as $post ) {
				$templates[] = $this->get_item( $post->ID );
			}
		}

		if ( ! empty( $args ) ) {
			$templates = wp_list_filter( $templates, $args );
		}

		return $templates;
	}

	/**
	 * Save local template.
	 *
	 * Save new or update existing template on the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $template_data Local template data.
	 *
	 * @return \WP_Error|int The ID of the saved/updated template, `WP_Error` otherwise.
	 */
	public function save_item( $template_data ) {
		if ( ! isset( self::$_template_types[ $template_data['type'] ] ) ) {
			return new \WP_Error( 'save_error', sprintf( 'Invalid template type "%s".', $template_data['type'] ) );
		}

		if ( ! current_user_can( $this->post_type_object->cap->edit_posts ) ) {
			return new \WP_Error( 'save_error', __( 'Access denied.', 'qazana' ) );
		}

		$template_id = wp_insert_post( [
			'post_title' => ! empty( $template_data['title'] ) ? $template_data['title'] : __( '(no title)', 'qazana' ),
			'post_status' => current_user_can( 'publish_posts' ) ? 'publish' : 'pending',
			'post_type' => self::CPT,
		] );

		if ( is_wp_error( $template_id ) ) {
			return $template_id;
		}

		qazana()->db->set_is_qazana_page( $template_id );

		qazana()->db->save_editor( $template_id, $template_data['content'] );

		$this->save_item_type( $template_id, $template_data['type'] );

		if ( ! empty( $template_data['page_settings'] ) ) {
			SettingsManager::get_settings_managers( 'page' )->save_settings( $template_data['page_settings'], $template_id );
		}

		/**
		 * After template library save.
		 *
		 * Fires after Qazana template library was saved.
		 *
		 * @since 1.0.1
		 *
		 * @param int   $template_id   The ID of the template.
		 * @param array $template_data The template data.
		 */
		do_action( 'qazana/template-library/after_save_template', $template_id, $template_data );

		/**
		 * After template library update.
		 *
		 * Fires after Qazana template library was updated.
		 *
		 * @since 1.0.1
		 *
		 * @param int   $template_id   The ID of the template.
		 * @param array $template_data The template data.
		 */
		do_action( 'qazana/template-library/after_update_template', $template_id, $template_data );

		return $template_id;
	}

	/**
	 * Update local template.
	 *
	 * Update template on the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $new_data New template data.
	 *
	 * @return \WP_Error|true True if template updated, `WP_Error` otherwise.
	 */
	public function update_item( $new_data ) {
		if ( ! current_user_can( $this->post_type_object->cap->edit_post, $new_data['id'] ) ) {
			return new \WP_Error( 'save_error', __( 'Access denied.', 'qazana' ) );
		}

		qazana()->db->save_editor( $new_data['id'], $new_data['content'] );

		/**
		 * After template library update.
		 *
		 * Fires after Qazana template library was updated.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $new_data_id The ID of the new template.
		 * @param array $new_data    The new template data.
		 */
		do_action( 'qazana/template-library/after_update_template', $new_data['id'], $new_data );

		return true;
	}

	/**
	 * Get local template.
	 *
	 * Retrieve a single local template saved by the user on his site.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return array Local template.
	 */
	public function get_item( $template_id ) {
		$post = get_post( $template_id );

		$user = get_user_by( 'id', $post->post_author );

		$page = SettingsManager::get_settings_managers( 'page' )->get_model( $template_id );

		$page_settings = $page->get_data( 'settings' );

		$date = strtotime( $post->post_date );

		$data = [
			'template_id' => $post->ID,
			'source' => $this->get_id(),
			'type' => self::get_template_type( $post->ID ),
			'title' => $post->post_title,
			'thumbnail' => get_the_post_thumbnail_url( $post ),
			'date' => $date,
			'human_date' => date_i18n( get_option( 'date_format' ), $date ),
			'author' => $user->display_name,
			'hasPageSettings' => ! empty( $page_settings ),
			'categories' => [],
			'tags' => [],
			'keywords' => [],
			'export_link' => $this->get_export_link( $template_id ),
			'url' => get_permalink( $post->ID ),
		];

		/**
		 * Get template library template.
		 *
		 * Filters the template data when retrieving a single template from the
		 * template library.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data Template data.
		 */
		$data = apply_filters( 'qazana/template-library/get_template', $data );

		return $data;
	}

	/**
	 * Get template data.
	 *
	 * Retrieve the data of a single local template saved by the user on his site.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param array $args Custom template arguments.
	 *
	 * @return array Local template data.
	 */
	public function get_data( array $args ) {
		$db = qazana()->db;

		$template_id = $args['template_id'];

		// TODO: Validate the data (in JS too!).
		if ( ! empty( $args['display'] ) ) {
			$content = $db->get_builder( $template_id );
		} else {
			$content = $db->get_plain_editor( $template_id );
		}

		if ( ! empty( $content ) ) {
			$content = $this->replace_elements_ids( $content );
		}

		$data = [
			'content' => $content,
		];

		if ( ! empty( $args['page_settings'] ) ) {
			$page = SettingsManager::get_settings_managers( 'page' )->get_model( $args['template_id'] );

			$data['page_settings'] = $page->get_data( 'settings' );
		}

		return $data;
	}

	/**
	 * Delete local template.
	 *
	 * Delete template from the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return \WP_Post|\WP_Error|false|null Post data on success, false or null
	 *                                       or 'WP_Error' on failure.
	 */
	public function delete_template( $template_id ) {
		if ( ! current_user_can( $this->post_type_object->cap->delete_post, $template_id ) ) {
			return new \WP_Error( 'template_error', __( 'Access denied.', 'qazana' ) );
		}

		return wp_delete_post( $template_id, true );
	}

	/**
	 * Export local template.
	 *
	 * Export template to a file.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return array|\WP_Error WordPress error if template export failed.
	 */
	public function export_template( $template_id ) {
		$file_data = $this->prepare_template_export( $template_id );

		if ( is_wp_error( $file_data ) ) {
			return $file_data;
		}

		$this->send_file_headers( $file_data['name'], strlen( $file_data['content'] ) );

		// Clear buffering just in case.
		@ob_end_clean();

		flush();

		// Output file contents.
		echo $file_data['content'];

		die;
	}

	/**
	 * Export multiple local templates.
	 *
	 * Export multiple template to a ZIP file.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param array $template_ids An array of template IDs.
	 *
	 * @return \WP_Error WordPress error if export failed.
	 */
	public function export_multiple_templates( array $template_ids ) {

		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		$files = [];

		$wp_upload_dir = wp_upload_dir();

		$temp_path = $wp_upload_dir['basedir'] . '/' . self::TEMP_FILES_DIR;

		/*
		 * Create temp path if it doesn't exist
		 */
		wp_mkdir_p( $temp_path );

		/*
		 * Create all json files
		 */
		foreach ( $template_ids as $template_id ) {
			$file_data = $this->prepare_template_export( $template_id );

			if ( is_wp_error( $file_data ) ) {
				continue;
			}

			$complete_path = $temp_path . '/' . $file_data['name'];

			$put_contents = file_put_contents( $complete_path, $file_data['content'] );

			if ( ! $put_contents ) {
				return new \WP_Error( '404', sprintf( 'Cannot create file "%s".', $file_data['name'] ) );
			}

			$files[] = [
				'path' => $complete_path,
				'name' => $file_data['name'],
			];
		}

		// Create temporary .zip file
		$zip_archive_filename = 'qazana-templates-' . date( 'Y-m-d' ) . '.zip';

		$zip_archive = new \ZipArchive();

		$zip_complete_path = $temp_path . '/' . $zip_archive_filename;

		$zip_archive->open( $zip_complete_path, \ZipArchive::CREATE );

		foreach ( $files as $file ) {
			$zip_archive->addFile( $file['path'], $file['name'] );
		}

		$zip_archive->close();

		$this->send_file_headers( $zip_archive_filename, filesize( $zip_complete_path ) );

		@ob_end_flush();

		@readfile( $zip_complete_path );

		unlink( $zip_complete_path );
		$wp_filesystem->delete( $zip_complete_path, true );

		die;
	}

	function recursive_get_files($path) {

		$Directory = new \RecursiveDirectoryIterator($path);

		$Directory->setFlags(\RecursiveDirectoryIterator::SKIP_DOTS);

 		$Iterator = new \RecursiveIteratorIterator($Directory);

		$data = array();

		foreach( $Iterator as $name => $object ) {
		   $data[] = $name;
		}

	    return $data;
	}
	
	/**
	 * Import local template.
	 *
	 * Import template from a file.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $name - The file name
	 * @param string $path - The file path
	 *
	 * @return \WP_Error|array An array of items on success, 'WP_Error' on failure.
	 */

	public function import_template($name, $import_file ) {

		global $wp_filesystem;

        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
        }

		if ( ! class_exists( '\ZipArchive' ) ) {
			return new \WP_Error( 'zip_error', 'PHP Zip extension not loaded.' );
		}

	
		if ( empty( $import_file ) ) {
			return new \WP_Error( 'file_error', 'Please upload a file to import' );
		}

		$items = [];

		$zip = new \ZipArchive();

		/*
		 * Check if file is a json or a .zip archive
		 */
		if ( true === $zip->open( $import_file ) ) {
			$wp_upload_dir = wp_upload_dir();

			$temp_path = $wp_upload_dir['basedir'] . '/' . self::TEMP_FILES_DIR . '/' . uniqid();

			$zip->extractTo( $temp_path );

			$zip->close();

			$found_files = $this->recursive_get_files( $temp_path );

			foreach ( $found_files as $file ) {
				$items[] = $this->import_single_template( $file );
			}

			$wp_filesystem->delete( $temp_path, true );

		} else {
			$items[] = $this->import_single_template( $import_file );
		}

		return $items;
	}

	/**
	 * Post row actions.
	 *
	 * Add an export link to the template library action links table list.
	 *
	 * Fired by `post_row_actions` filter.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array    $actions An array of row action links.
	 * @param \WP_Post $post    The post object.
	 *
	 * @return array An updated array of row action links.
	 */
	public function post_row_actions( $actions, \WP_Post $post ) {
		if ( self::is_base_templates_screen() ) {
			if ( $this->is_template_supports_export( $post->ID ) ) {
				$actions['export-template'] = sprintf( '<a href="%1$s">%2$s</a>', $this->get_export_link( $post->ID ), __( 'Export Template', 'qazana' ) );
			}

			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	/**
	 * Admin import template form.
	 *
	 * The import form displayed in "My Library" screen in WordPress dashboard.
	 *
	 * The form allows the user to import template in json/zip format to the site.
	 *
	 * Fired by `admin_footer` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_import_template_form() {
		if ( ! self::is_base_templates_screen() ) {
			return;
		}
		?>
		<div id="qazana-hidden-area">
			<a id="qazana-import-template-trigger" class="page-title-action"><?php _e( 'Import Templates', 'qazana' ); ?></a>
			<div id="qazana-import-template-area">
				<div id="qazana-import-template-title"><?php _e( 'Choose an Qazana template JSON file or a .zip archive of Qazana templates, and add them to the list of templates available in your library.', 'qazana' ); ?></div>
				<form id="qazana-import-template-form" method="post" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" enctype="multipart/form-data">
					<input type="hidden" name="action" value="qazana_import_template">
					<input type="hidden" name="_nonce" value="<?php echo qazana()->editor->create_nonce( self::CPT ); ?>">
					<fieldset id="qazana-import-template-form-inputs">
						<input type="file" name="file" accept=".json,application/json,.zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed" required>
						<input type="submit" class="button" value="<?php echo esc_attr__( 'Import Now', 'qazana' ); ?>">
					</fieldset>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Block template frontend
	 *
	 * Don't display the single view of the template library post type in the
	 * frontend, for users that don't have the proper permissions.
	 *
	 * Fired by `template_redirect` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function block_template_frontend() {
		if ( is_singular( self::CPT ) && ! current_user_can( 'edit_posts' ) ) {
			wp_redirect( site_url(), 301 );
			die;
		}
	}

	/**
	 * Is template library supports export.
	 *
	 * whether the template library supports export.
	 *
	 * Template saved by the user locally on his site, support export by default
	 * but this can be changed using a filter.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return bool Whether the template library supports export.
	 */
	public function is_template_supports_export( $template_id ) {
		$export_support = true;

		/**
		 * Is template library supports export.
		 *
		 * Filters whether the template library supports export.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $export_support Whether the template library supports export.
		 *                             Default is true.
		 * @param int  $template_id    Post ID.
		 */
		$export_support = apply_filters( 'qazana/template_library/is_template_supports_export', $export_support, $template_id );

		return $export_support;
	}

	/**
	 * Remove Qazana post state.
	 *
	 * Remove the 'qazana' post state from the display states of the post.
	 *
	 * Used to remove the 'qazana' post state from the template library items.
	 *
	 * Fired by `display_post_states` filter.
	 *
	 * @since 1.8.0
	 * @access public
	 *
	 * @param array    $post_states An array of post display states.
	 * @param \WP_Post $post        The current post object.
	 *
	 * @return array Updated array of post display states.
	 */
	public function remove_qazana_post_state_from_library( $post_states, $post ) {
		if ( self::CPT === $post->post_type && isset( $post_states['qazana'] ) ) {
			unset( $post_states['qazana'] );
		}
		return $post_states;
	}

	/**
	 * Get template export link.
	 *
	 * Retrieve the link used to export a single template based on the template
	 * ID.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return string Template export URL.
	 */
	private function get_export_link( $template_id ) {
		return add_query_arg(
			[
				'action' => 'qazana_export_template',
				'source' => $this->get_id(),
				'_nonce' => qazana()->editor->create_nonce( self::CPT ),
				'template_id' => $template_id,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * On template save.
	 *
	 * Run this method when template is being saved.
	 *
	 * Fired by `save_post` action.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    The current post object.
	 */
	public function on_save_post( $post_id, \WP_Post $post ) {
		if ( self::CPT !== $post->post_type ) {
			return;
		}

		if ( self::get_template_type( $post_id ) ) { // It's already with a type
			return;
		}

		// Don't save type on import, the importer will do it.
		if ( did_action( 'import_start' ) ) {
			return;
		}

		$this->save_item_type( $post_id, 'page' );
	}

	/**
	 * Save item type.
	 *
	 * When saving/updating templates, this method is used to update the post
	 * meta data and the taxonomy.
	 *
	 * @since 1.0.1
	 * @access private
	 *
	 * @param int    $post_id Post ID.
	 * @param string $type    Item type.
	 */
	private function save_item_type( $post_id, $type ) {
		update_post_meta( $post_id, self::TYPE_META_KEY, $type );

		wp_set_object_terms( $post_id, $type, self::TAXONOMY_TYPE_SLUG );
	}

	/**
	 * Filter template types in admin query.
	 *
	 * Update the template types in the main admin query.
	 *
	 * Fired by `parse_query` action.
	 *
	 * @since 1.0.6
	 * @access public
	 *
	 * @param \WP_Query $query The `WP_Query` instance.
	 */
	public function admin_query_filter_types( \WP_Query $query ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$library_screen_id = 'edit-' . self::CPT;
		$current_screen = get_current_screen();

		if ( ! isset( $current_screen->id ) || $library_screen_id !== $current_screen->id || ! empty( $query->query_vars['meta_key'] ) ) {
			return;
		}

		$query->query_vars['meta_key'] = self::TYPE_META_KEY;
		$query->query_vars['meta_value'] = array_values( self::$_template_types );
	}

	/**
	 * Bulk export action.
	 *
	 * Adds an 'Export' action to the Bulk Actions drop-down in the template
	 * library.
	 *
	 * Fired by `bulk_actions-edit-qazana_library` filter.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param array $actions An array of the available bulk actions.
	 *
	 * @return array An array of the available bulk actions.
	 */
	public function admin_add_bulk_export_action( $actions ) {
		$actions[ self::BULK_EXPORT_ACTION ] = __( 'Export', 'qazana' );

		return $actions;
	}

	/**
	 * Add bulk export action.
	 *
	 * Handles the template library bulk export action.
	 *
	 * Fired by `handle_bulk_actions-edit-qazana_library` filter.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param string $redirect_to The redirect URL.
	 * @param string $action      The action being taken.
	 * @param array  $post_ids    The items to take the action on.
	 *
	 * @return string The redirect URL.
	 */
	public function admin_export_multiple_templates( $redirect_to, $action, $post_ids ) {
		if ( self::BULK_EXPORT_ACTION === $action ) {
			$this->export_multiple_templates( $post_ids );
		}

		return $redirect_to;
	}

	/**
	 * Print admin tabs.
	 *
	 * Used to output the template library tabs with their labels.
	 *
	 * Fired by `views_edit-qazana_library` filter.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $views An array of available list table views.
	 *
	 * @return array An updated array of available list table views.
	 */
	public function admin_print_tabs( $views ) {
		$current_type = '';
		$active_class = ' nav-tab-active';
		if ( ! empty( $_REQUEST[ self::TAXONOMY_TYPE_SLUG ] ) ) {
			$current_type = $_REQUEST[ self::TAXONOMY_TYPE_SLUG ];
			$active_class = '';
		}

		$baseurl = admin_url( 'edit.php?post_type=' . self::CPT );
		?>
		<div id="qazana-template-library-tabs-wrapper" class="nav-tab-wrapper">
			<a class="nav-tab<?php echo $active_class; ?>" href="<?php echo $baseurl; ?>"><?php _e( 'All', 'qazana' ); ?></a>
			<?php
			foreach ( self::$_template_types as $template_type ) :
				$active_class = '';

				if ( $current_type === $template_type ) {
					$active_class = ' nav-tab-active';
				}

				$type_url = add_query_arg( self::TAXONOMY_TYPE_SLUG, $template_type, $baseurl );
				$type_label = $this->get_template_label_by_type( $template_type );

				echo "<a class='nav-tab{$active_class}' href='{$type_url}'>{$type_label}</a>";
			endforeach;
			?>
		</div>
		<?php
		return $views;
	}

	/**
	 * Maybe render blank state.
	 *
	 * When the template library has no saved templates, display a blank admin page offering
	 * to create the very first template.
	 *
	 * Fired by `manage_posts_extra_tablenav` action.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
	 */
	public function maybe_render_blank_state( $which ) {
		global $post_type;

		if ( self::CPT !== $post_type || 'bottom' !== $which ) {
			return;
		}

		global $wp_list_table;

		$total_items = $wp_list_table->get_pagination_arg( 'total_items' );

		if ( ! empty( $total_items ) || ! empty( $_REQUEST['s'] ) ) {
			return;
		}

		$inline_style = '#posts-filter .wp-list-table, #posts-filter .tablenav.top, .tablenav.bottom .actions, .wrap .subsubsub { display:none;}';

		$current_type = get_query_var( 'qazana_library_type' );

		// TODO: Better way to exclude widget type.
		if ( 'widget' === $current_type ) {
			return;
		}

		if ( empty( $current_type ) ) {
			$counts = (array) wp_count_posts( self::CPT );
			unset( $counts['auto-draft'] );
			$count  = array_sum( $counts );

			if ( 0 < $count ) {
				return;
			}

			$current_type = 'template';

			$inline_style .= '#qazana-template-library-tabs-wrapper {display: none;}';
		}

		$current_type_label = $this->get_template_label_by_type( $current_type );
		?>
		<style type="text/css"><?php echo $inline_style; ?></style>
		<div class="qazana-template_library-blank_state">
			<div class="qazana-blank_state">
				<i class="eicon-folder"></i>
				<h2>
					<?php
					/* translators: %s: Template type label. */
					printf( __( 'Create Your First %s', 'qazana' ), $current_type_label );
					?>
				</h2>
				<p><?php _e( 'Add templates and reuse them across your website. Easily export and import them to any other project, for an optimized workflow.', 'qazana' ); ?></p>
				<a id="qazana-template-library-add-new" class="qazana-button qazana-button-success" href="">
					<?php
					/* translators: %s: Template type label. */
					printf( __( 'Add New %s', 'qazana' ), $current_type_label );
					?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Import single template.
	 *
	 * Import template from a file to the database.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param string $file_name File name.
	 *
	 * @return \WP_Error|int|array Local template array, or template ID, or
	 *                             `WP_Error`.
	 */
	private function import_single_template( $file_name ) {

		global $wp_filesystem;

        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
        }

		$data = json_decode( $wp_filesystem->get_contents( $file_name ), true );

		if ( empty( $data ) ) {
			return new \WP_Error( 'file_error', 'Invalid File' );
		}

		if ( ! is_array( $data['content'] ) ) {
			return new \WP_Error( 'file_error', 'Invalid File' );
		}

		$content = $this->process_export_import_content( $data['content'], 'on_import' );

		$page_settings = [];

		if ( ! empty( $data['page_settings'] ) ) {
			$page = new Model( [
				'id' => 0,
				'settings' => $data['page_settings'],
			] );

			$page_settings_data = $this->process_element_export_import_content( $page, 'on_import' );

			if ( ! empty( $page_settings_data['settings'] ) ) {
				$page_settings = $page_settings_data['settings'];
			}
		}

		$template_id = $this->save_item( [
            'content' => $content,
			'title' => $data['title'],
			'type' => $data['type'],
			'page_settings' => $page_settings,
		] );

		if ( is_wp_error( $template_id ) ) {
			return $template_id;
		}

		return $this->get_item( $template_id );
	}

	/**
	 * Prepare template to export.
	 *
	 * Retrieve the relevant template data and return them as an array.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return \WP_Error|array Exported template data.
	 */
	private function prepare_template_export( $template_id ) {
		$template_data = $this->get_data( [
			'template_id' => $template_id,
		] );

		if ( empty( $template_data['content'] ) ) {
			return new \WP_Error( '404', 'The template does not exist.' );
		}

		$template_data['content'] = $this->process_export_import_content( $template_data['content'], 'on_export' );

		if ( get_post_meta( $template_id, '_qazana_page_settings', true ) ) {
			$page = SettingsManager::get_settings_managers( 'page' )->get_model( $template_id );

			$page_settings_data = $this->process_element_export_import_content( $page, 'on_export' );

			if ( ! empty( $page_settings_data['settings'] ) ) {
				$template_data['page_settings'] = $page_settings_data['settings'];
			}
		}

		$export_data = [
			'version' => qazana_db_version(),
			'title' => get_the_title( $template_id ),
			'type' => self::get_template_type( $template_id ),
		];

		$export_data += $template_data;

		return [
			'name' => 'qazana-' . $template_id . '-' . date( 'Y-m-d' ) . '.json',
			'content' => wp_json_encode( $export_data ),
		];
	}

	/**
	 * Send file headers.
	 *
	 * Set the file header when export template data to a file.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param string $file_name File name.
	 * @param int    $file_size File size.
	 */
	private function send_file_headers( $file_name, $file_size ) {
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . $file_size );
	}

	/**
	 * Get template label by type.
	 *
	 * Retrieve the template label for any given template type.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $template_type Template type.
	 *
	 * @return string Template label.
	 */
	private function get_template_label_by_type( $template_type ) {
		$document_types = qazana()->documents->get_document_types();

		if ( isset( $document_types[ $template_type ] ) ) {
			$template_label = call_user_func( [ $document_types[ $template_type ], 'get_title' ] );
		} else {
			$template_label = ucwords( str_replace( [ '_', '-' ], ' ', $template_type ) );
		}

		if ( 'page' === $template_type ) {
			$template_label = __( 'Content', 'qazana' );
		}

		/**
		 * Template label by template type.
		 *
		 * Filters the template label by template type in the template library .
		 *
		 * @since 2.0.0
		 *
		 * @param string $template_label Template label.
		 * @param string $template_type  Template type.
		 */
		$template_label = apply_filters( 'qazana/template-library/get_template_label_by_type', $template_label, $template_type );

		return $template_label;
	}

	/**
	 * Add template library actions.
	 *
	 * Register filters and actions for the template library.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function add_actions() {
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 50 );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 11 );
			add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );
			add_action( 'admin_footer', [ $this, 'admin_import_template_form' ] );
			add_action( 'save_post', [ $this, 'on_save_post' ], 10, 2 );
			add_action( 'parse_query', [ $this, 'admin_query_filter_types' ] );
			add_filter( 'display_post_states', [ $this, 'remove_qazana_post_state_from_library' ], 11, 2 );

			// Template type column.
			add_action( 'manage_' . self::CPT . '_posts_columns', [ $this, 'admin_columns_headers' ] );
			add_action( 'manage_' . self::CPT . '_posts_custom_column', [ $this, 'admin_columns_content' ] , 10, 2 );

			// Template library bulk actions.
			add_filter( 'bulk_actions-edit-qazana_library', [ $this, 'admin_add_bulk_export_action' ] );
			add_filter( 'handle_bulk_actions-edit-qazana_library', [ $this, 'admin_export_multiple_templates' ], 10, 3 );

			// Print template library tabs.
			add_filter( 'views_edit-' . self::CPT, [ $this, 'admin_print_tabs' ] );

			// Show blank state.
			add_action( 'manage_posts_extra_tablenav', [ $this, 'maybe_render_blank_state' ] );
		}

		add_action( 'template_redirect', [ $this, 'block_template_frontend' ] );
	}

	public function admin_columns_content( $column_name, $post_id ) {
		if ( 'qazana_library_type' === $column_name ) {
			/** @var Document $document */
			$document = qazana()->documents->get( $post_id );

			if ( $document ) {
				$admin_filter_url = admin_url( '/edit.php?post_type=qazana_library&qazana_library_type=' . $document->get_name() );
				printf( '<a href="%s">%s</a>', $admin_filter_url, $document->get_title() );
			}
		}
	}

	public function admin_columns_headers( $posts_columns ) {
		// Replace original column that bind to the taxonomy - with another column.
		unset( $posts_columns['taxonomy-qazana_library_type'] );

		$offset = 2;

		$posts_columns = array_slice( $posts_columns, 0, $offset, true ) + [
				'qazana_library_type' => __( 'Type', 'qazana' ),
			] + array_slice( $posts_columns, $offset, null, true );

		return $posts_columns;
	}

	/**
	 * Template library local source constructor.
	 *
	 * Initializing the template library local source base by registering custom
	 * template data and running custom actions.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->add_actions();
	}
}
