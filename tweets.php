<?php
    require "vendor/autoload.php";
    
    use Abraham\TwitterOAuth\TwitterOAuth;
    
    define('CONSUMER_KEY', get_option( 'tweets_to_posts_ck', '' ));
    define('CONSUMER_SECRET', get_option( 'tweets_to_posts_cs', '' ));
    define('ACCESS_TOKEN', get_option( 'tweets_to_posts_at', '' ));
    define('ACCESS_SECRET', get_option( 'tweets_to_posts_as', '' ));
    
     

    if (CONSUMER_KEY === '' || CONSUMER_SECRET === '' || CONSUMER_KEY === 'CONSUMER_KEY_HERE' || CONSUMER_SECRET === 'CONSUMER_SECRET_HERE') {
        echo 'You need a consumer key and secret keys. Get one from <a href="https://dev.twitter.com/apps">dev.twitter.com/apps</a>';
      
        exit;
    }

    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_SECRET);
    


    $query_type = get_option('tweets_to_posts_query_type', '');
    $query = get_option('tweets_to_posts_query', '');
    $number = intval(get_option('tweets_to_posts_number', ''));
    $onlyImages = (get_option('tweets_to_posts_only_images', '') === 'true' );
    $exclude_replies = (get_option('tweets_to_posts_exclude_replies', '') === 'true');

    if(isset($_GET['lastId'])){
        $lastID = intval($_GET['lastId']);
        
    }
    
    
    // $username = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    // 
    // $exclude_replies = filter_input(INPUT_GET, 'exclude_replies', FILTER_SANITIZE_SPECIAL_CHARS);
    // $list_slug = filter_input(INPUT_GET, 'list_slug', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // 
    // $onlyImages = filter_input(INPUT_GET, 'onlyImages', FILTER_SANITIZE_SPECIAL_CHARS);

    // Get Tweets
    if ($query_type == 'free') {
        $service = "search/tweets";
        $params = array(
        'count' => $number,
        'q' => $query,
        'include_entities' => true
        );

    } 

    else if($query_type == 'hash') {
        $service = "search/tweets";
        $params = array(
            'count' => $number,
            'q' => '#'.$query,
            'include_entities' => true
        );
      
    } else if($query_type == 'user') {
        $service = "statuses/user_timeline";
        $params = array(
            'screen_name' => $query,
            'count' => $number,
            'include_entities' => true
        );
        
    }
    if($lastID){
        $params['max_id'] = $lastID;
      }
    if($onlyImages){
      $params['q'] .= ' filter:twimg filter:images';
    }
    $content = $connection->get($service, $params);
    
    


    
    // Return JSON Object
    header('Content-Type: application/json');
    

    $tweets = json_encode( $content);
    //$twitter = json_encode($twitter);
    echo $tweets;
    
