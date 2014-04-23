<?php

/****************************************
Backend Functions
*****************************************/

/**
 * Customize Contact Methods
 * @since 1.0.0
 *
 * @author Bill Erickson
 * @link http://sillybean.net/2010/01/creating-a-user-directory-part-1-changing-user-contact-fields/
 *
 * @param array $contactmethods
 * @return array
 */
function mb_contactmethods( $contactmethods ) {
	unset( $contactmethods['aim'] );
	unset( $contactmethods['yim'] );
	unset( $contactmethods['jabber'] );

	return $contactmethods;
}


/**
 * Enqueue LiveReload if local install
 *
 * Assumes livereload.js is in the root folder of your local site
 *
 * This should be fairly reliable,
 * but may need to be changed for some environments
 */
if ( $_SERVER['REMOTE_ADDR'] === '127.0.0.1' ){
  add_action( 'wp_enqueue_scripts', 'enqueue_livereload' );
}

function enqueue_livereload(){
  wp_enqueue_script( 'livereload', site_url().'/livereload.js', '', NULL );
}


/**
 * Register Widget Areas
 */
function mb_widgets_init() {
	// Main Sidebar
	register_sidebar(array(
		'name'          => __( 'Main Sidebar', 'mb' ),
		'id'            => 'main-sidebar',
		'description'   => __( 'Widgets for Main Sidebar.', 'mb' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>'
	));

	// Footer
	register_sidebar(array(
		'name'          => __( 'Footer', 'mb' ),
		'id'            => 'footer-widgets',
		'description'   => __( 'Widgets for Footer.', 'mb' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>'
	));
}

/**
 * Don't Update Theme
 * @since 1.0.0
 *
 * If there is a theme in the repo with the same name,
 * this prevents WP from prompting an update.
 *
 * @author Mark Jaquith
 * @link http://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
 *
 * @param array $r, request arguments
 * @param string $url, request url
 * @return array request arguments
 */
function mb_dont_update_theme( $r, $url ) {
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/themes/update-check' ) )
		return $r; // Not a theme update request. Bail immediately.
	$themes = unserialize( $r['body']['themes'] );
	unset( $themes[ get_option( 'template' ) ] );
	unset( $themes[ get_option( 'stylesheet' ) ] );
	$r['body']['themes'] = serialize( $themes );
	return $r;
}

/**
 * Remove Dashboard Meta Boxes
 */
function mb_remove_dashboard_widgets() {
	global $wp_meta_boxes;
	// unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
	// unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
	// unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
	// unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

/**
 * Change Admin Menu Order
 */
function mb_custom_menu_order($menu_ord) {
	if (!$menu_ord) return true;
	return array(
		// 'index.php', // Dashboard
		// 'separator1', // First separator
		// 'edit.php?post_type=page', // Pages
		// 'edit.php', // Posts
		// 'upload.php', // Media
		// 'gf_edit_forms', // Gravity Forms
		// 'genesis', // Genesis
		// 'edit-comments.php', // Comments
		// 'separator2', // Second separator
		// 'themes.php', // Appearance
		// 'plugins.php', // Plugins
		// 'users.php', // Users
		// 'tools.php', // Tools
		// 'options-general.php', // Settings
		// 'separator-last', // Last separator
	);
}

/**
 * Hide Admin Areas that are not used
 */
function mb_remove_menu_pages() {
	// remove_menu_page('link-manager.php');
}

/**
 * Remove default link for images
 */
function mb_imagelink_setup() {
	$image_set = get_option( 'image_default_link_type' );
	if ($image_set !== 'none') {
		update_option('image_default_link_type', 'none');
	}
}

/**
 * Show Kitchen Sink in WYSIWYG Editor
 */
function mb_unhide_kitchensink($args) {
	$args['wordpress_adv_hidden'] = false;
	return $args;
}

/****************************************
Frontend
*****************************************/

/**
 * Enqueue scripts
 */
function mb_scripts() {
	global $wp_styles; // call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way

	// modernizr (without media query polyfill)
	wp_register_script( 'modernizr', get_stylesheet_directory_uri() . '/assets/bower_components/modernizr/modernizr.js', array(), '2.5.3', false );
	wp_enqueue_script( 'modernizr' );
	wp_register_style('mb_style', get_stylesheet_directory_uri().'/assets/stylesheets/style.css', null, '1.0', 'all' );
	wp_enqueue_style( 'mb_style' );

	wp_register_style( 'ie-only', get_stylesheet_directory_uri() . '/assets/stylesheets/ie.css', null, '1.0', 'all' );
	wp_enqueue_style('ie-only');
 	$wp_styles->add_data( 'ie-only', 'conditional', 'lt IE 9' ); // add conditional wrapper around ie stylesheet

	// JavaScript
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	if ( !is_admin() ) {
		wp_enqueue_script('customplugins', get_template_directory_uri() . '/assets/js/vendors.min.js', array('jquery'), NULL, true );
		wp_enqueue_script('customscripts', get_template_directory_uri() . '/assets/js/app.min.js', array('jquery'), NULL, true );
	}

}

/**
 * Remove Query Strings From Static Resources
 */
function mb_remove_script_version($src){
	$parts = explode('?', $src);
	return $parts[0];
}

/**
 * Remove Read More Jump
 */
function mb_remove_more_jump_link($link) {
	$offset = strpos($link, '#more-');
	if ($offset) {
		$end = strpos($link, '"',$offset);
	}
	if ($end) {
		$link = substr_replace($link, '', $offset, $end-$offset);
	}
	return $link;
}

// This removes the annoying […] to a Read More link
function custom_excerpt_more($more) {
	global $post;
	// edit here if you like
return '... <a class="excerpt-read-more no-margin" href="'. get_permalink($post->ID) . '" title="'. __('Read', 'netskopetheme') . get_the_title($post->ID).'">'. __('Read more &raquo;', 'netskopetheme') .'</a>';
}

function get_social_share($title, $twitter, $linkedin, $facebook, $email) {
	?>
	<ul class="social-list-container">
    <li class="twitter"><a href="http://twitter.com/share?text=<?php echo urlencode( sanitize_text_field( $twitter ) );?>" onclick="javascript:window.open(this.href, 'tweet', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">Tweeter</a></li>
    <li class="linkedin"> <a target='_blank' href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo urlencode( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ); ?>&amp;title= <?php urlencode( $title );?>&amp;summary=<?php echo urlencode( sanitize_text_field( $linkedin ) );?>&amp;source=<?php echo urlencode( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);?>" onclick="javascript:window.open(this.href, 'linkedin', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">Linkedin</a></li>
    <li class="facebook"><a target="_blank" href="http://www.facebook.com/sharer.php?u=<?php echo urlencode ( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ); ?>&amp;t=<?php echo urlencode( $facebook ); ?>." onclick="window.open('http://www.facebook.com/sharer.php?u=<?php echo urlencode ( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ); ?>&amp;t=<?php ?>', 'facebook_share', 'height=320, width=640, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no');return false;">Facebook</a></li>
    <li class="email"><a href="mailto:?subject=Check out <?php echo urlencode ( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]  ); ?>&amp;body=<?php echo sanitize_text_field( $email );?>" title="Share by Email"> Email</a></li>
  </ul>
<?php }

function get_social_share_blog($title, $twitter, $linkedin, $facebook, $email, $url) {
	?>
	<ul class="social-list-container">
    <li class="twitter"><a href="http://twitter.com/share?text=<?php echo urlencode( sanitize_text_field( $twitter ) );?>" onclick="javascript:window.open(this.href, 'tweet', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">Tweeter</a></li>
    <li class="linkedin"> <a target='_blank' href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo urlencode( $url ); ?>&amp;title= <?php urlencode( $title );?>&amp;summary=<?php echo urlencode( sanitize_text_field( $linkedin ) );?>&amp;source=<?php echo urlencode( $url );?>" onclick="javascript:window.open(this.href, 'linkedin', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">Linkedin</a></li>
    <li class="facebook"><a target="_blank" href="http://www.facebook.com/sharer.php?u=<?php echo urlencode ( $url ); ?>&amp;t=<?php echo urlencode( $facebook ); ?>." onclick="window.open('http://www.facebook.com/sharer.php?u=<?php echo urlencode ( $url ); ?>&amp;t=<?php ?>', 'facebook_share', 'height=320, width=640, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no');return false;">Facebook</a></li>
    <li class="email"><a href="mailto:?subject=Check out <?php echo urlencode ( $url  ); ?>&amp;body=<?php echo sanitize_text_field( $email );?>" title="Share by Email"> Email</a></li>
  </ul>
<?php }



/**
 * Showing Custom Post Type in Category & Tag
 */
function add_custom_types_to_tax( $query ) {
	if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {

	// Get all your post types
	$post_types = get_post_types();

	$query->set( 'post_type', $post_types );
	return $query;
	}
}
add_filter( 'pre_get_posts', 'add_custom_types_to_tax' );

// Get the parent page of a sub page
function getParent() {
    global $post;                              // load details about this page
    if ( is_page() && $post->post_parent ) {   // test to see if the page has a parent
        return $post->post_parent;             // return the ID of the parent post
    }
    return null;                          // ... the answer to the question is false
}

function get_the_author_posts_link() {
	global $authordata;
	if ( !is_object( $authordata ) )
		return false;
	$link = sprintf(
		'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
		get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
		esc_attr( sprintf( __( 'Posts by %s' ), get_the_author() ) ), // No further l10n needed, core will take care of this one
		get_the_author()
	);
	return $link;
}

/*
 * This is a modified the_author_posts_link() which just returns the link.
 *
 * This is necessary to allow usage of the usual l10n process with printf().
 */
function bones_get_the_author_posts_link() {
	global $authordata;
	if ( !is_object( $authordata ) )
		return false;
	$link = sprintf(
		'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
		get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
		esc_attr( sprintf( __( 'Posts by %s' ), get_the_author() ) ), // No further l10n needed, core will take care of this one
		get_the_author()
	);
	return $link;
}
