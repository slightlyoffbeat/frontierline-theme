<?php
if (! function_exists('frontierline_setup')):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * To override frontierline_setup() in a child theme, add your own frontierline_setup
 * function to your child theme's functions.php file.
 */
function frontierline_setup() {

  // Make the theme available for translation.
  // Translations can be added to the /languages/ directory.
  load_theme_textdomain('frontierline', get_template_directory() . '/languages');

  // This theme uses wp_nav_menu() in one location.
  // TODO: Commented out for now; I'll add this back later
  // register_nav_menu('primary', __('Primary Menu', 'frontierline'));

  // Add styles to post editor (editor-style.css)
  add_editor_style();

  // This theme uses Featured Images (also known as post thumbnails)
  add_theme_support('post-thumbnails');

  // Set up some custom image sizes
  add_image_size('post-full-size', 1400, 770, array('center', 'center')); // Full post image - used at the top of single articles
  add_image_size('post-large', 600, 330, array('center', 'center')); // Large post image - used in grid summary view
  add_image_size('post-thumbnail', 300, 165, array('center', 'center')); // Thumbnail post image - used in mini view
  add_image_size('extra-large', 1000, 0); // Extra large image - for big images embedded in posts

  $header_defaults = array(
    'header-text'            => false,
    'width'                  => 1600,
    'height'                 => 600,
  );
  add_theme_support('custom-header', $header_defaults);

  // Disable the header text and color options
  define('NO_HEADER_TEXT', true);
}
endif;
add_action('after_setup_theme', 'frontierline_setup');


/**
 * Do some stuff when the theme is activated.
 */
if (! function_exists('frontierline_activate')):
  function frontierline_activate() {
    // Set default media options
    update_option('thumbnail_size_w', 150, true);
    update_option('thumbnail_size_h', 150, true);
    update_option('thumbnail_crop', 1);
    update_option('medium_size_w', 300, true);
    update_option('medium_size_h', '', true);
    update_option('large_size_w', 600, true);
    update_option('large_size_h', '', true);
  }
endif;
add_action('after_switch_theme', 'frontierline_activate');


/**
 * Register and define the social sharing settings
 */
function frontierline_admin_init(){
  register_setting(
    'reading',
    'frontierline_share_posts'
  );
  add_settings_field(
    'share_posts',
    __('Social sharing for posts', 'frontierline'),
    'frontierline_settings_field_share_posts',
    'reading',
    'default'
  );

  register_setting(
    'reading',
    'frontierline_share_pages'
  );
  add_settings_field(
    'share_pages',
    __('Social sharing for Pages', 'frontierline'),
    'frontierline_settings_field_share_pages',
    'reading',
    'default'
  );

  register_setting(
    'reading',
    'frontierline_twitter_username'
  );
  add_settings_field(
    'twitter_username',
    __('Twitter username', 'frontierline'),
    'frontierline_settings_field_twitter_username',
    'reading',
    'default'
  );
}
add_action('admin_init', 'frontierline_admin_init');

/**
 * Renders the Add Sharing setting field for posts.
 */
function frontierline_settings_field_share_posts() { ?>
  <div class="layout share-posts">
  <label>
    <input type="checkbox" id="frontierline_share_posts" name="frontierline_share_posts" value="1" <?php checked( '1', get_option('frontierline_share_posts') ); ?>>
    <span>
      <?php _e('Add social sharing buttons to posts', 'frontierline'); ?>
    </span>
    <p class="description"><?php _e('Adds buttons for Twitter and Facebook to the top of blog articles.', 'frontierline'); ?></p>
  </label>
  </div>
  <?php
}

/**
 * Renders the Add Sharing setting field for pages.
 */
function frontierline_settings_field_share_pages() { ?>
  <div class="layout share-pages">
  <label>
    <input type="checkbox" id="frontierline_share_pages" name="frontierline_share_pages" value="1" <?php checked( '1', get_option('frontierline_share_pages') ); ?>>
    <span>
      <?php _e('Add social sharing buttons to Pages', 'frontierline'); ?>
    </span>
    <p class="description"><?php _e('Adds buttons for Twitter and Facebook to the top of static Pages.', 'frontierline'); ?></p>
  </label>
  </div>
  <?php
}

/**
* Renders the Twitter account setting field to share via.
*/
function frontierline_settings_field_twitter_username() { ?>
  <div class="layout twitter-username">
  <label>
    <input type="text" id="frontierline_twitter_username" name="frontierline_twitter_username" value="<?php echo get_option('frontierline_twitter_username'); ?>">
    <p class="description"><?php _e('The Twitter account for attribution when sharing. Appears as "via @username" at the end of the tweet, and Twitter may suggest related accounts to follow. Leave this blank for no attribution.', 'frontierline'); ?></p>
  </label>
  </div>
  <?php
}


/**
* Make custom image sizes available in admin
*/
function frontierline_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'extra-large' => __('Extra Large', 'frontierline'),
    ) );
}
add_filter('image_size_names_choose', 'frontierline_custom_sizes');


/**
* Use auto-excerpts for meta description if hand-crafted exerpt is missing
*/
function frontierline_meta_desc() {
  $post_desc_length  = 25; // auto-excerpt length in number of words

  global $cat, $cache_categories, $wp_query, $wp_version;
  if(is_single() || is_page()) {
    $post = $wp_query->post;
    $post_custom = get_post_custom($post->ID);

    if(!empty($post->post_excerpt)) {
      $text = $post->post_excerpt;
    } else {
      $text = $post->post_content;
    }
    $text = do_shortcode($text);
    $text = str_replace(array("\r\n", "\r", "\n", "  "), " ", $text);
    $text = str_replace(array("\""), "", $text);
    $text = trim(strip_tags($text));
    $text = explode(' ', $text);
    if(count($text) > $post_desc_length) {
      $l = $post_desc_length;
      $ellipsis = '...';
    } else {
      $l = count($text);
      $ellipsis = '';
    }
    $description = '';
    for ($i=0; $i<$l; $i++)
      $description .= $text[$i] . ' ';

    $description .= $ellipsis;
  }
  elseif(is_category()) {
    $category = $wp_query->get_queried_object();
    if (!empty($category->category_description)) {
      $description = trim(strip_tags($category->category_description));
    } else {
      $description = single_cat_title('Articles posted in ', 'frontierline');
    }
  }
  else {
    $description = trim(strip_tags(get_bloginfo('description')));
  }

  if($description) {
    echo $description;
  }
}


/**
* Disable the embedded styles when using [gallery] shortcode
*/
add_filter('use_default_gallery_style', '__return_false');


/**
* Disable comments on Pages by default
*
* This is a hack. WP doesn't currently make it possible to enable comments
* by default for Posts while disabling them for Pages; it's either comments on
* all or comments on none. But in most cases authors will prefer to turn off
* comments for Pages. This just unchecks those checkboxes automatically so authors
* don't need to remember. Comments can still be enabled for Pages on an individual
* basis.
*/
function frontierline_page_comments_off() {
  if(isset($_REQUEST['post_type'])) {
    if ( $_REQUEST['post_type'] == "page" ) {
      echo '<script>
          if (document.post) {
            var opt_comment = document.post.comment_status;
            var opt_ping = document.post.ping_status;
            if (the_comment && the_ping) {
              the_comment.checked = false;
              the_ping.checked = false;
            }
          }
      </script>';
    }
  }
}
add_action ('admin_footer', 'frontierline_page_comments_off');


/**
* Prints the page number currently being browsed, with a pipe before it.
* Used in header.php to add the page number to the <title>.
*/
if (! function_exists('frontierline_page_number')) :
function frontierline_page_number() {
  global $paged; // Contains page number.
  if ( $paged >= 2 )
    echo ' | ' . sprintf(__('Page %s', 'frontierline'), $paged);
}
endif;


/*********
* Load various JavaScripts
*/
function frontierline_load_scripts() {
  // Load the default jQuery
  wp_enqueue_script('jquery');

  // Load the global script
  wp_register_script('global', get_template_directory_uri() . '/js/global.js', 'jquery', '1.1', true);
  wp_enqueue_script('global');

  // Load the newsletter script
  wp_register_script('basket-client', get_template_directory_uri() . '/js/basket-client.js', '', '1.1', true);
  wp_enqueue_script('basket-client');

  // Load the threaded comment reply script
  if ( get_option('thread_comments') && is_singular() ) {
    wp_enqueue_script('comment-reply', true);
  }

  // Check required fields on comment form
  wp_register_script('checkcomments', get_template_directory_uri() . '/js/fc-checkcomment.js', 'jquery', '1.0', true);
  if (get_option('require_name_email') && is_singular() && comments_open()) {
    wp_enqueue_script('checkcomments');
  }
}
add_action( 'wp_enqueue_scripts', 'frontierline_load_scripts' );


/**
* Remove WP version from head (helps us evade spammers/hackers)
*/
remove_action('wp_head', 'wp_generator');


/**
* Catch spambots with a honeypot field in the comment form.
* It's hidden from view with CSS so most humans will leave it blank, but robots will kindly fill it in to alert us to their presence.
* The field has an innucuous name -- 'age' in this case -- likely to be autofilled by a robot.
*/
function frontierline_honeypot( array $data ){
  if( !isset($_POST['comment']) && !isset($_POST['content'])) { die("No Direct Access"); }  // Make sure the form has actually been submitted

  if($_POST['age']) {  // If the Honeypot field has been filled in
    $message = _e('Sorry, you appear to be a spamming robot because you filled in the hidden spam trap field. To show you are not a spammer, submit your comment again and leave the field blank.', 'frontierline');
    $title = _e('Spam Prevention', 'frontierline');
    $args = array('response' => 200);
    wp_die( $message, $title, $args );
    exit(0);
  } else {
     return $data;
  }
}
add_filter('preprocess_comment', 'frontierline_honeypot');


/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 */
function frontierline_remove_recent_comments_style() {
  add_filter('show_recent_comments_widget_style', '__return_false');
}
add_action('widgets_init', 'frontierline_remove_recent_comments_style');


/**
 * Set available formats for the visual editor.
 * This removes Heading 1 and Heading 2, which authors shouldn't use.
 */
function frontierline_post_formats($formats) {
  $formats['block_formats'] = "Paragraph=p; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6; Preformatted=pre; Code=code;";
  return $formats;
}
add_filter('tiny_mce_before_init', 'frontierline_post_formats');


/**
 * Sets the post excerpt length to 40 words.
 *
 * To override this length in a child theme, remove the filter and add your own
 * function tied to the excerpt_length filter hook.
 */
function frontierline_excerpt_length( $length ) {
  return 40;
}
add_filter('excerpt_length', 'frontierline_excerpt_length');


/**
 * Returns a "Read more" link for excerpts
 */
function frontierline_read_more_link() {
  return ' <a class="go" href="'. esc_url( get_permalink() ) . '">' . __('Read more', 'frontierline') . '</a>';
}


/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and frontierline_read_more_link().
 *
 * To override this in a child theme, remove the filter and add your own
 * function tied to the excerpt_more filter hook.
 */
function frontierline_auto_excerpt_more( $more ) {
  return ' &hellip;' . frontierline_read_more_link();
}
add_filter('excerpt_more', 'frontierline_auto_excerpt_more');


/**
 * Adds a pretty "Read more" link to custom post excerpts.
 *
 * To override this link in a child theme, remove the filter and add your own
 * function tied to the get_the_excerpt filter hook.
 */
function frontierline_custom_excerpt_more( $output ) {
  if ( has_excerpt() && ! is_attachment() ) {
    $output .= frontierline_read_more_link();
  }
  return $output;
}
add_filter('get_the_excerpt', 'frontierline_custom_excerpt_more');


/**
 * Register the widgetized sidebar.
 */
function frontierline_widgets_init() {
  register_sidebar( array(
    'name' => __('Sidebar Menu', 'frontierline'),
    'id' => 'sidebar',
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget' => "</aside>",
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>',
  ) );
}
add_action('widgets_init', 'frontierline_widgets_init');


/**
* Comment Template
*/
if (! function_exists('frontierline_comment')) :
function frontierline_comment($comment, $args, $depth) {
  $GLOBALS['comment'] = $comment;
  $comment_type = get_comment_type();
  $date_format = get_option('date_format');
  $time_format = get_option('time_format');
?>

 <li id="comment-<?php comment_ID(); ?>" <?php comment_class('hentry'); ?>>
  <?php if ($comment_type == 'trackback') : ?>
    <h4 class="entry-title"><?php _e('Trackback from ', 'frontierline'); ?> <cite><?php esc_html(comment_author_link()); ?></cite>
      <?php /* L10n: Trackback headings read "Trackback from <Site> on <Date> at <Time>:" */ ?>
      <span class="comment-meta">
        <?php _e('on', 'frontierline'); ?>
        <a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>" rel="bookmark" title=" <?php _e('Permanent link to this comment by ','frontierline'); comment_author(); ?>">
          <time class="published" datetime="<?php comment_date('Y-m-d'); ?>" title="<?php comment_date('Y-m-d'); ?>">
          <?php /* L10n: Trackback headings read "Trackback from <Site> on <Date> at <Time>:" */ ?>
          <?php printf( __('%1$s at %2$s:','frontierline'), get_comment_date($date_format), get_comment_time($time_format) ); ?>
          </time>
        </a>
      </span>
    </h4>
  <?php elseif ($comment_type == 'pingback') : ?>
    <h4 class="entry-title"><?php _e('Ping from ', 'frontierline'); ?> <cite><?php esc_html(comment_author_link()); ?></cite>
      <?php /* L10n: Pingback headings read "Ping from <Site> on <Date> at <Time>:" */ ?>
      <span class="comment-meta">
        <?php _e('on', 'frontierline'); ?>
        <a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>" rel="bookmark" title="<?php _e('Permanent link to this comment by ','frontierline'); comment_author(); ?>">
          <time class="published" datetime="<?php comment_date('Y-m-d'); ?>" title="<?php comment_date('Y-m-d'); ?>">
          <?php /* L10n: Pingback headings read "Ping from <Site> on <Date> at <Time>:" */ ?>
          <?php printf( __('%1$s at %2$s:','frontierline'), get_comment_date($date_format), get_comment_time($time_format) ); ?>
          </time>
        </a>
      </span>
    </h4>
  <?php else : ?>
    <h4 class="entry-title vcard">
      <cite class="author fn"><?php esc_html(comment_author()); ?></cite>
      <?php if (function_exists('get_avatar')) : echo ('<span class="photo">'.get_avatar( $comment, 60 ).'</span>'); endif; ?>
      <span class="comment-meta">
        <?php _e('wrote on', 'frontierline'); ?>
        <a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>" rel="bookmark" title="<?php _e('Permanent link to this comment by ','frontierline'); comment_author(); ?>">
          <time class="published" datetime="<?php comment_date('Y-m-d'); ?>" title="<?php comment_date('Y-m-d'); ?>">
          <?php /* L10n: Comment headings read "<Name> wrote on <Date> at <Time>:" */ ?>
          <?php printf( __('%1$s at %2$s:','frontierline'), get_comment_date($date_format), get_comment_time($time_format) ); ?>
          </time>
        </a>
      </span>
    </h4>
  <?php endif; ?>

  <?php if ($comment->comment_approved == '0') : ?>
    <p class="mod-message">
      <strong><?php _e('Your comment is awaiting moderation.', 'frontierline'); ?></strong>
    </p>
  <?php endif; ?>

    <blockquote class="entry-content">
      <?php esc_html(comment_text()); ?>
    </blockquote>

  <?php if ((get_option('thread_comments') == true) || (current_user_can('edit_post', $comment->comment_post_ID))) : ?>
    <p class="comment-util">
      <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
      <?php if ( current_user_can('edit_post', $comment->comment_post_ID) ) : ?>
      <span class="edit"><?php edit_comment_link(__('Edit comment', 'frontierline'),'',''); ?></span>
      <?php endif; ?>
    </p>
  <?php endif; ?>
<?php
}
endif;


/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param    array  $plugins
 * @return   array  Difference betwen the two arrays
 */
function frontierline_disable_emojis_tinymce( $plugins ) {
  if (is_array($plugins)) {
    return array_diff($plugins, array('wpemoji'));
  } else {
    return array();
  }
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param  array  $urls          URLs to print for resource hints.
 * @param  string $relation_type The relation type the URLs are printed for.
 * @return array                 Difference betwen the two arrays.
 */
function frontierline_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
  if ( 'dns-prefetch' == $relation_type ) {
    // This filter is documented in wp-includes/formatting.php
    $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');

    $urls = array_diff( $urls, array( $emoji_svg_url ) );
  }
  return $urls;
}

/**
 * Disable the emoji scripts
 */
function frontierline_disable_emojis() {
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  add_filter( 'tiny_mce_plugins', 'frontierline_disable_emojis_tinymce' );
  add_filter( 'wp_resource_hints', 'frontierline_disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action('init', 'frontierline_disable_emojis');

?>