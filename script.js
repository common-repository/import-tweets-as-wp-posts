jQuery(document).ready(function() {
    
    ajaxGetTweets.init(ajaxGetTweets.query);
    jQuery('#updateCatOnChange').on('change', function() {

        ajaxGetTweets.updateCategories('#updateCatOnChange');
    });
});

var ajaxGetTweets = {
    query: '',
    lastID: '',
    nbPage: 0,
    dating: function(twt_date) {
        var time = twt_date.split(' ');
        twt_date = new Date(Date.parse(time[1] + ' ' + time[2] + ', ' + time[5] + ' ' + time[3] + ' UTC'));

        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        var _date = {
            '%d': twt_date.getDate(),
            '%m': twt_date.getMonth() + 1,
            '%b': months[twt_date.getMonth()].substr(0, 3),
            '%B': months[twt_date.getMonth()],
            '%y': String(twt_date.getFullYear()).slice(-2),
            '%Y': twt_date.getFullYear()
        };

        var date = '%b/%d/%Y';
        var format = date.match(/%[dmbByY]/g);

        for (var i = 0, len = format.length; i < len; i++) {
            date = date.replace(format[i], _date[format[i]]);
        }

        return date;
    },
    linking: function(tweet) {
        var twit = tweet.replace(/(https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w\/_\.]*(\?\S+)?)?)?)/ig, '<a href="$1" target="_blank" title="Visit this link">$1</a>')
            .replace(/#([a-zA-Z0-9_]+)/g, '<a href="https://twitter.com/search?q=%23$1&amp;src=hash" target="_blank" title="Search for #$1">#$1</a>')
            .replace(/@([a-zA-Z0-9_]+)/g, '<a href="https://twitter.com/$1" target="_blank" title="$1 on Twitter">@$1</a>');

        return twit;
    },
    init: function(tag) {

        ajaxGetTweets.insertPost();
        ajaxGetTweets.rejectPost();
        ajaxGetTweets.getPostIdsAndRun('#results', tag);
        ajaxGetTweets.loadMore();

    },
    getPostIdsAndRun: function(container, tag, lastID) {
        var data = {
            'action': 'tweets_to_posts_getAllPostSlug'
        };
        var ids = '';
        jQuery.post(ajaxurl, data, function(response) {

            ids = response;


            ajaxGetTweets.getTagFeed(container, tag, ids, ajaxGetTweets.lastID);

        });
    },
    updateCategories: function(el) {
        var type = jQuery(el).val();
        var data = {
            'action': 'tweets_to_posts_getPostTypeCats',
            'post_type': type
        };

        jQuery.post(ajaxurl, data, function(response) {

            if (response === "empty") {
                jQuery('#catsSelect').addClass('hidden');
            } else {
                jQuery('#catsSelect').removeClass('hidden');
                jQuery('#catsSelect select').html(function() {
                    var html = "";
                    response = JSON.parse(response);

                    for (var i = 0; i < response.length; i++) {
                        if (ajaxGetYoutube.currentCat === response[i].id) {
                            html += '<option selected value="' + response[i].id + '" >' + response[i].name + '</option>';
                        } else {
                            html += '<option value="' + response[i].id + '" >' + response[i].name + '</option>';
                        }



                    }
                    return html;
                });
            }




        });
    },
    getTagFeed: function(container, tag, ids, lastID) {
        var args = {};
        if (!container) {
            container = '#results';
        }
        jQuery(container).addClass('loading');
        jQuery.ajax({
            type: "GET",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: 'tweets_to_posts_api_call',
                lastId: lastID
            }
        }).done(function(twt) {
            var latestId = 0;
            var that = jQuery(container);

            that.html('<ul></ul>');
            jQuery(container).removeClass('loading');
            for (var i = 0; i < ajaxGetTweets.number; i++) {
                var tweet = false;

                if (twt[i]) {
                    tweet = twt[i];
                    latestId = twt[twt.length - 1].id;
                } else if (twt.statuses !== undefined && twt.statuses[i]) {
                    tweet = twt.statuses[i];
                    latestId = twt.statuses[twt.statuses.length - 1].id;
                } else {
                    break;
                }
                var temp_data = {};
                if (ids.indexOf(tweet.id) > -1) {
                    // Dupe, do nothing
                } else {
                    temp_data = {
                        user_name: tweet.user.name,
                        id: tweet.id,
                        date: ajaxGetTweets.dating(tweet.created_at),
                        tweet: (tweet.retweeted) ? ajaxGetTweets.linking('RT @' + tweet.user.screen_name + ': ' + tweet.retweeted_status.text) :  ajaxGetTweets.linking(tweet.text),
                        avatar: '<img src="' + tweet.user.profile_image_url + '" />',
                        url: 'https://twitter.com/' + tweet.user.screen_name + '/status/' + tweet.id_str,
                        retweeted: tweet.retweeted,
                        media_url: tweet.entities.media ? tweet.entities.media[0].media_url : '',
                        media: tweet.entities.media ? '<img src="' + tweet.entities.media[0].media_url + '"/>' : '',
                        screen_name: ajaxGetTweets.linking('@' + tweet.user.screen_name),
                        twitter_name: '@' + tweet.user.screen_name
                    };

                    var html = '<div class="result" data-id="' + temp_data.id + '"><div class="thumb"><a href="' + temp_data.url + '" target="_blank" ><img src="' + temp_data.media_url + '" /></a></div><div>' + temp_data.twitter_name + ' (' + temp_data.screen_name + ')</div><div class="content">' + temp_data.tweet + '</div><div class="nbLikes">' + temp_data.date + '</div><div class="buttons"><a data-id="' + temp_data.id + '" data-src="' + temp_data.media_url + '" data-author="' + temp_data.user_name + '" data-content="  " class="btn-deny button button-secondary" href="#">' + ajaxGetTweets.trans.reject + '</a><a data-id="' + temp_data.id + '" data-src="' + temp_data.media_url + '" data-author="' + temp_data.user_name + '" data-account="' + temp_data.twitter_name + '" data-content="" data-date="' + temp_data.date + '" class="btn-approve button button-primary" href="#">' + ajaxGetTweets.trans.approve + '</a></div></div>';

                    that.find('ul').append('<li>' + html + '</li>');
                }



            }

            if (typeof callback === 'function') {
                callback();
            }

            ajaxGetTweets.lastID = latestId;
        }).error(function(err) {
            console.info(err);
        });;


    },
    insertPost: function() {
        jQuery(document).on('click', '.btn-approve', function(e) {
            e.preventDefault();
            var $that = jQuery(this);
            var id = $that.attr('data-id');
            var title = 'Twitter post ' + id;
            var content = $that.parents('.result').find('.content').html();
            var imgSrc = $that.attr('data-src');
            var author = $that.attr('data-author');
            var account = $that.attr('data-account');
            var date = $that.attr('data-date');
            var data = {
                'action': 'tweets_to_posts_insertPost',
                'id': id,
                'title': title,
                'content': content,
                'author': author,
                'imgSrc': imgSrc,
                'account': account,
                'postType': ajaxGetTweets.post_type,
                'date': date
            };
            jQuery('.updated-custom p').html(ajaxGetTweets.trans.loading).parent().fadeIn();
            jQuery.post(ajaxurl, data, function(response) {

                if (response === 'ok') {
                    $that.parents('.result').fadeOut();
                    ajaxGetTweets.showAlert('mediaAdded');
                }
            }); // wp_insert_post();
        });
    },
    showAlert: function(message) {
        var msg;
        if (message === "needUpdate") {
            msg = ajaxGetTweets.trans.needUpdate;
        } else if (message === "mediaAdded") {
            msg = ajaxGetTweets.trans.mediaAdded;
        } else if (message === "mediaRejected") {
            msg = ajaxGetTweets.trans.mediaRejected;
        }
        jQuery('.updated-custom p').html(msg).fadeIn(function() {
            jQuery('.updated-custom').fadeOut();
        });

    },
    rejectPost: function() {
        jQuery(document).on('click', '.btn-deny', function(e) {
            e.preventDefault();
            var $that = jQuery(this);
            var id = $that.attr('data-id');
            var title = 'Twitter post ' + id;
            var content = $that.attr('data-content');
            var imgSrc = $that.attr('data-src');
            var author = $that.attr('data-author');
            var data = {
                'action': 'tweets_to_posts_rejectPost',
                'id': id,
                'title': title,
                'content': content,
                'imgSrc': imgSrc
            };
            jQuery('.updated-custom p').html(ajaxGetTweets.trans.loading).parent().fadeIn();
            jQuery.post(ajaxurl, data, function(response) {
                if (response === 'ok') {
                    $that.parents('.result').fadeOut();
                    ajaxGetTweets.showAlert('mediaRejected');
                }
            }); // wp_insert_post();
        });
    },
    loadMore: function() {
        var loadButton = jQuery('#loadMore');
        var container = '#results';

        loadButton.on('click', function(e) {
            e.preventDefault();
            ajaxGetTweets.nbPage++;
            jQuery(container).after('<div class="results" id="results-' + ajaxGetTweets.nbPage + '" />');
            container = '#results-' + ajaxGetTweets.nbPage;
            ajaxGetTweets.getPostIdsAndRun(container, ajaxGetTweets.defaultTag, ajaxGetTweets.lastID + 1);
        });
    }
}