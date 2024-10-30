<?php

/*
  Plugin Name: Import Tweets as WP Posts
  Plugin URI: http://www.enguerranweiss.fr
  Description: Get a tweets list from a request (hashtag, user, free request) and add their content to your own Wordpress :)
  Version: 1.3
  Author: Enguerran Weiss
  Author URI: http://www.enguerranweiss.fr
 */


/* -----------------------------------------------------------------------------
 *  Init functions
  ---------------------------------------------------------------------------- */


add_action( 'init', 'create_t2p_pt_rejects' );

add_action('admin_init', 'tweets_to_posts_feed_init');
add_action('admin_menu', 'tweets_to_posts_feed_menu');
add_action('admin_menu', 'tweets_to_posts_options_page');

add_filter('tweets_to_posts_options_page_render', 'tweets_to_posts_register_options');



/* -----------------------------------------------------------------------------
 *  WP Ajax stuff
  ---------------------------------------------------------------------------- */

add_action("wp_ajax_tweets_to_posts_insertPost", "tweets_to_posts_insertPost");
add_action("wp_ajax_nopriv_tweets_to_posts_insertPost", "tweets_to_posts_insertPost");

add_action("wp_ajax_tweets_to_posts_api_call", "tweets_to_posts_api_call");
add_action("wp_ajax_nopriv_tweets_to_posts_api_call", "tweets_to_posts_api_call");


add_action("wp_ajax_tweets_to_posts_rejectPost", "tweets_to_posts_rejectPost");
add_action("wp_ajax_nopriv_tweets_to_posts_rejectPost", "tweets_to_posts_rejectPost");

add_action("wp_ajax_tweets_to_posts_getAllPostSlug", "tweets_to_posts_getAllPostSlug");
add_action("wp_ajax_nopriv_tweets_to_posts_getAllPostSlug", "tweets_to_posts_getAllPostSlug");

add_action("wp_ajax_tweets_to_posts_getPostTypeCats", "tweets_to_posts_getPostTypeCats");
add_action("wp_ajax_nopriv_tweets_to_posts_getPostTypeCats", "tweets_to_posts_getPostTypeCats");


/* -----------------------------------------------------------------------------
 *  Declare functions
  ---------------------------------------------------------------------------- */


function tweets_to_posts_getAllPostSlug() { // Returns an array of all Tweets ID (to check dupes)

  $args = array( 'posts_per_page' => -1, "post_type" => array( get_option('tweets_to_posts_post_type'), 't2p_reject' ));
  $posts = get_posts($args);
  $ids = array();
  foreach ($posts as $post) {
      $ids[] = $post->post_name;
  }
  echo json_encode($ids);
  die();
}


function tweets_to_posts_check_api_settings() { // Check if Twitter API settings are filled

  if(get_option('tweets_to_posts_ck') === '' || get_option('tweets_to_posts_cs') === '' || get_option('tweets_to_posts_at') === '' || get_option('tweets_to_posts_as') === ''){
    return false;
  }
  else {
    return true;
  }
  die();
}
function tweets_to_posts_getPostTypeCats() { // Returns an array of all Youtube ID (to check dupes)
  $type = $_POST['post_type'];
  $taxs = get_object_taxonomies($type);
  $results = array();


  if(empty($taxs)){
    echo 'empty';
  }
  else {
    foreach ($taxs as $tax) {
      if($tax === "post_tag" || $tax === "post_format"){

      }
      else {
        $args = array(
            'orderby'           => 'name',
            'order'             => 'ASC',
            'hide_empty'        => false,
            'exclude'           => array(),
            'exclude_tree'      => array(),
            'include'           => array(),
            'number'            => '',
            'fields'            => 'all',
            'slug'              => '',
            'parent'            => '',
            'hierarchical'      => true,
            'child_of'          => 0,
            'get'               => '',
            'name__like'        => '',
            'description__like' => '',
            'pad_counts'        => false,
            'offset'            => '',
            'search'            => '',
            'cache_domain'      => 'core'
        );
        $tax_terms = get_terms($tax, $args);
       // var_dump($tax_terms);
        foreach ($tax_terms as $tax_term) {
          if($tax_term){
            $object = new stdClass();
            $object->id = $tax_term->term_id;
            $object->name = $tax_term->name;
            $results[] = $object;

          }

        }
      }


    }
    $results = json_encode($results);
      echo $results;
  }
  die();
}
function tweets_to_posts_insertPost(){ // Setting and calling wp_insert_post();

    // Getting tweet data from front
    $title = $_POST['title'];
    $content = $_POST['content'];
    $id = $_POST['id'];
    $author = $_POST['author'];
    $imgSrc = $_POST['imgSrc'];
    $postType = get_option('tweets_to_posts_post_type');
    $cat = intval(get_option('tweets_to_posts_cat'));
    $template = get_option('tweets_to_posts_title_template');
    $date = $_POST['date'];
    // If template selected, construct the title
    if($template !== ''){
        $templateParams = array('/%a%/', '/%d%/');
        $templateVals = array($author, $date);
        $customTitle = preg_replace( $templateParams, $templateVals, $template);





    }
    $title = $customTitle ? $customTitle : $title;

    // Creating new post
    $my_post = array(
      'post_name'     => $id,
      'post_title'    => $title,
      'post_content'  => $content,
      'post_status'   => 'publish',
      'tags_input'    => $author,
      'post_author'   => 1,
      'post_type'     => $postType,
      'post_category' => array($cat)
    );

    $post_ID = wp_insert_post($my_post);

    // updating post meta
    // if a media is detected, add its source url
    if($imgSrc){
      add_post_meta( $post_ID, 'media_url', $imgSrc, true ) || update_post_meta( $post_ID, 'media_url', $imgSrc );

      add_post_meta( $post_ID, 'tw_id', $id, true );
      // Create and upload thumbnail with $title as description
      $image = media_sideload_image($imgSrc, $title);

      // then find the last image added to the post attachments
      $attachments = get_posts(array('numberposts' => '1', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'DESC', 'orderby' => 'date'));
      // Set this attachment as post thumbnail

      if(sizeof($attachments) > 0){
          set_post_thumbnail($post_ID, $attachments[0]->ID);
      }
    }

    echo 'ok';
    die();
}
function tweets_to_posts_rejectPost(){ // Reject post : insert

    $title = $_POST['title'];
    $content = $_POST['content'];
    $id = $_POST['id'];
    $author = $_POST['author'];



    $my_post = array(
      'post_name'     => $id,
      'post_title'    => $title,
      'post_content'  => $content,
      'post_status'   => 'publish',
      'tags_input'    => $author,
      'post_author'   => 1,
      'post_type'     => 't2p_reject'
    );

    wp_insert_post($my_post);

    echo 'ok';
    die();
}

function tweets_to_posts_feed_init() {

    load_plugin_textdomain('tweets-to-posts', false, basename( dirname( __FILE__ ) ) . '/i18n' );
    wp_enqueue_script('tweets_to_posts_feed_tweetie', plugins_url('/tweetie.min.js', __FILE__));
    wp_register_script('tweets_to_posts_feed_tweetie', plugins_url('/tweetie.min.js', __FILE__), 'jquery');

}



function tweets_to_posts_feed_menu() {

    $page = add_menu_page(
            __('Twitter Importer', 'tweets_to_posts'), // The Menu Title
            __('Twitter Importer', 'tweets_to_posts'), // The Page title
            'manage_options', // The capability required for access to this item
            'tweets_to_posts_feed', // the slug to use for the page in the URL
            'tweets_to_posts_feed_admin',  // The function to call to render the page
            "dashicons-format-gallery",59 // Position
    );

    /* Using registered $page handle to hook script load */
    add_action('admin_print_styles-' . $page, 'tweets_to_posts_feed_styles');
    add_action( 'admin_init', 'tweets_to_posts_register_options' );
}

function tweets_to_posts_feed_styles() {
    /*
     * It will be called only on your plugin admin page, enqueue our script here
     */

    wp_enqueue_script('tweets_to_posts_feed_script', plugins_url('/script.js', __FILE__));

}


/* -----------------------------------------------------------------------------
 *  Render the admin page
  ---------------------------------------------------------------------------- */

function tweets_to_posts_feed_admin() {

    wp_enqueue_script('tweets_to_posts_feed_script', plugins_url('/script.js', __FILE__));
    wp_register_script('tweets_to_posts_feed_script', plugins_url('/script.js', __FILE__), 'jquery');
    wp_enqueue_style('tweets_to_posts_feed_styles', plugins_url('/styles.css', __FILE__));
    require_once(dirname(__FILE__) . '/admin.php');

}


/* -----------------------------------------------------------------------------
 *  Call to the Tweet API file
  ---------------------------------------------------------------------------- */


function tweets_to_posts_api_call(){

    require_once(dirname(__FILE__) ."/tweets.php"); // Path to twitteroauth library
    die();
}


/* -----------------------------------------------------------------------------
 *  Plugin Page options
  ---------------------------------------------------------------------------- */


function tweets_to_posts_options_page() {
  add_options_page('Twitter Importer', 'Twitter Importer', 'manage_options', 'tweets-to-posts', 'tweets_to_posts_options_page_render');


}
function tweets_to_posts_options_page_render(){
  require_once(dirname(__FILE__) . '/options.php');

}

function tweets_to_posts_register_options() { //register our settings

  register_setting( 'tweets_to_posts-admin-settings-group', 'tweets_to_posts_query_type' );
  register_setting( 'tweets_to_posts-admin-settings-group', 'tweets_to_posts_query' );
  register_setting( 'tweets_to_posts-admin-settings-group', 'tweets_to_posts_exclude_replies' );
  register_setting( 'tweets_to_posts-admin-settings-group', 'tweets_to_posts_number' );
  register_setting( 'tweets_to_posts-admin-settings-group', 'tweets_to_posts_only_images' );
  register_setting( 'tweets_to_posts-admin-settings-group', 'tweets_to_posts_post_type' );
  register_setting( 'tweets_to_posts-admin-settings-group', 'tweets_to_posts_cat' );

  register_setting( 'tweets_to_posts-settings-group', 'tweets_to_posts_title_template' );
  register_setting( 'tweets_to_posts-settings-group', 'tweets_to_posts_ck' );
  register_setting( 'tweets_to_posts-settings-group', 'tweets_to_posts_cs' );
  register_setting( 'tweets_to_posts-settings-group', 'tweets_to_posts_at' );
  register_setting( 'tweets_to_posts-settings-group', 'tweets_to_posts_as' );


}


/* -----------------------------------------------------------------------------
 *  Register private post type for rejects
  ---------------------------------------------------------------------------- */

function create_t2p_pt_rejects() {
  register_post_type( 't2p_reject',
    array(
      'labels' => array(
        'name' => __( 'T2P rejects' ),
        'singular_name' => __( 'T2P rejects' )
      ),
      'public' => false,
      'has_archive' => false
    )
  );
}
