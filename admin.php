<script type="text/javascript">
  var pluginDirectory = '<?php echo plugins_url('', __FILE__); ?>';
  ajaxGetTweets.query = '<?php echo get_option( 'tweets_to_posts_query', '' ) ?>';
  ajaxGetTweets.query_type = '<?php echo get_option( 'tweets_to_posts_query_type', '' ) ?>';
  ajaxGetTweets.post_type = '<?php echo get_option( 'tweets_to_posts_post_type', '' ) ?>';
  ajaxGetTweets.trans = {};
  ajaxGetTweets.trans.loading = '<?php _e('Loading...', 'tweets-to-posts') ?>';
  ajaxGetTweets.trans.mediaAdded = '<?php _e('Post added !', 'tweets-to-posts') ?>';
  ajaxGetTweets.trans.mediaRejected = '<?php _e('Tweet rejected !', 'tweets-to-posts') ?>';
  ajaxGetTweets.trans.approve = '<?php _e('Approve', 'tweets-to-posts') ?>';
  ajaxGetTweets.trans.reject = '<?php _e('Deny', 'tweets-to-posts') ?>';
  ajaxGetTweets.exclude_replies = '<?php echo get_option( 'tweets_to_posts_exclude_replies', '' ) ?>';
  ajaxGetTweets.number = '<?php echo get_option( 'tweets_to_posts_number', '' ) ?>';
  ajaxGetTweets.only_images = '<?php echo get_option( 'tweets_to_posts_only_images', '' ) ?>';
</script>


<div class="wrap">
  <h2 class="align-center">Import Tweets as WP Posts</h2>
  <?php if(tweets_to_posts_check_api_settings()){ ?>


  <h3><?php _e('Tweets query options', 'tweets-to-posts') ?></h3>


  <form method="post" action="options.php" class="options-custom" id="queryOptions">
    
    <?php
      settings_fields( 'tweets_to_posts-admin-settings-group' ); ?>
    <?php do_settings_sections( 'tweets_to_posts-admin-settings-group' ); ?>
    <div class="option">
      <label><?php _e('Query type:', 'tweets-to-posts') ?></label>
      <select name="tweets_to_posts_query_type">
        <option value="free" <?php if(esc_attr( get_option('tweets_to_posts_query_type') ) === 'free'){ echo 'selected'; } ?>><?php _e('Query (will search in tweets content)', 'tweets-to-posts') ?></option>
        <option value="user" <?php if(esc_attr( get_option('tweets_to_posts_query_type') ) === 'user'){ echo 'selected'; } ?> ><?php _e('User (find tweets from an user)', 'tweets-to-posts') ?></option>
        <option value="hash" <?php if(esc_attr( get_option('tweets_to_posts_query_type') ) === 'hash'){ echo 'selected'; } ?> ><?php _e('Hashtag (find tweets with a hashtag)', 'tweets-to-posts') ?></option>
      </select>
    </div>
    <div class="option">
      <label><?php _e('Query text:', 'tweets-to-posts') ?></label>
      <input type="text" placeholder="<?php _e('Don\'t type # or @, just the query.', 'tweets-to-posts') ?>" name="tweets_to_posts_query" value="<?php echo esc_attr( get_option('tweets_to_posts_query') ); ?>" />
    </div>
    <div class="option">
      <label><?php _e('Only tweets with image?', 'tweets-to-posts') ?></label>
      <select name="tweets_to_posts_only_images">
        
        <option value="true" <?php if(esc_attr( get_option('tweets_to_posts_only_images') ) === true){ echo 'selected'; } ?> ><?php _e('Yes', 'tweets-to-posts') ?></option>
        <option value="false" <?php if(esc_attr( get_option('tweets_to_posts_only_images') ) === false){ echo 'selected'; } ?> ><?php _e('No', 'tweets-to-posts') ?></option>
      </select>
    </div>
    <div class="option">
      <label><?php _e('Exclude replies?', 'tweets-to-posts') ?></label>
      
      <select name="tweets_to_posts_exclude_replies">
        
        <option value="true" <?php if(esc_attr( get_option('tweets_to_posts_exclude_replies') ) === true){ echo 'selected'; } ?> ><?php _e('Yes', 'tweets-to-posts') ?></option>
        <option value="false" <?php if(esc_attr( get_option('tweets_to_posts_exclude_replies') ) === false){ echo 'selected'; } ?> ><?php _e('No', 'tweets-to-posts') ?></option>
      </select>
    </div>
    <div class="option">
      <label><?php _e('Tweets number per page?', 'tweets-to-posts') ?></label>
      <input type="number" placeholder="<?php _e('Default: 30', 'tweets-to-posts') ?>" name="tweets_to_posts_number" value="<?php echo esc_attr( get_option('tweets_to_posts_number') ); ?>" /></td>
    </div>
    <div class="option">
      <label><?php _e('Post type to feed:', 'tweets-to-posts') ?></label>
      <select id="updateCatOnChange" name="tweets_to_posts_post_type">
        <?php 
          $types = get_post_types(array('public'   => true));
          foreach ($types as $type) { 
            if($type === 'page' || $type === 'attachment'){
              // do nothing
              }
              else {
            ?>
            <option value="<?php echo $type; ?>" <?php if(esc_attr( get_option('tweets_to_posts_post_type') ) === $type){ echo 'selected'; } ?> ><?php echo $type; ?></option>
          <?php 
          }
        }
        ?>
      </select>
    </div>
    <div class="option" id="catsSelect">
        
      <label><?php _e('Category to feed:', 'tweets-to-posts') ?></label>
        <select name="tweets_to_posts_cat">
          <?php 
              
            $args = array(
             
              'hide_empty'               => 0

            ); 
            $terms = get_categories($args);
            
            foreach ($terms as $term) { 
              
              ?>
              <option value="<?php echo $term->term_id; ?>" <?php if(intval(esc_attr( get_option('tweets_to_posts_cat') )) === $term->term_id){ echo 'selected'; } ?> ><?php echo $term->name; ?></option>
            <?php }
          ?>
          </select>
    </div>


<?php submit_button(); ?>
</form>

<h3><?php _e('List of last Twitter posts for the query:', 'tweets-to-posts') ?> "<?php echo get_option( 'tweets_to_posts_query', '' ) ?>"</h3>
<div class="updated updated-custom">
  <p></p>
</div>
<div id="headResults">
<div class="manage-column column-image"><strong><?php _e('Preview/thumbnail', 'tweets-to-posts') ?></strong></div>
<div class="manage-column column-auteur"><strong><?php _e('Author username', 'tweets-to-posts') ?></strong></div>
<div class="manage-column column-texte"><strong><?php _e('Tweet text', 'tweets-to-posts') ?></strong></div>
<div class="manage-column column-date"><strong><?php _e('Date', 'tweets-to-posts') ?></strong></div>
<div class="manage-column column-action"><strong><?php _e('Action', 'tweets-to-posts') ?></strong></div>
</div>
<div id="results" class="results">
</div>

<a href="#" class="button button-primary" id="loadMore"><?php _e('Load more tweets', 'tweets-to-posts') ?></a>
<?php
} else { ?>
<p><?php _e('Your Twitter API access is not defined, go set it on the settings page.', 'tweets-to-posts') ?></p>
<?php
}?>

</div>