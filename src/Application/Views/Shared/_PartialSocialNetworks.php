<?php

#region Instagram
// Authorization URL: https://instagram.com/oauth/authorize/?client_id=93357bc33c034ede89e87b7642d90c4f&redirect_uri=http://www.confetimail.net/&response_type=token
$Instagram_Access_Token = "";
$Instagram_Self_Feed_Url = "https://api.instagram.com/v1/users/self/media/recent?count=2&access_token=".$Instagram_Access_Token;

$instagram_json = file_get_contents($Instagram_Self_Feed_Url);
$instagram_object = JsonHandler::NormalDecode($instagram_json);
#endregion

#region Facebook
// Test URL: /posts?fields=message,picture,created_time,caption,source&limit=10
/*
$Facebook_Access_Token = "";
$Facebook_Confeti_Page_Id = "258907884293773";
$Facebook_Self_Feed_Url = sprintf("https://graph.facebook.com/v2.4/%s/posts?fields=message,picture,created_time&limit=10&access_token=%s", $Facebook_Confeti_Page_Id, $Facebook_Access_Token);

$facebook_json = file_get_contents($Facebook_Self_Feed_Url);
$facebook_object = JsonHandler::NormalDecode($facebook_json);
 */
#endregion

?>

<div class="row social">
    <div class="col-md-3">
        <a class="twitter-timeline" style="height: 397px;" href="https://twitter.com/confetimail" data-widget-id="622440497966129152">Tuits de @confetimail</a>
        <script>
            !function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                if (!d.getElementById(id)) {
                    js = d.createElement(s);
                    js.id = id;
                    js.src = p + "://platform.twitter.com/widgets.js";
                    fjs.parentNode.insertBefore(js, fjs);
                }
            }(document, "script", "twitter-wjs");
        </script>
    </div>

    <div class="col-md-3">
        <script src="http://snapwidget.com/js/snapwidget.js"></script>
        <iframe src="http://snapwidget.com/in/?u=Y29uZmV0aW1haWx8aW58MTcwfDJ8M3x8bm98NXxub25lfG9uU3RhcnR8eWVzfHllcw==&ve=180715" title="Instagram Widget" class="snapwidget-widget" allowTransparency="true" frameborder="0" scrolling="no" style="border:none; overflow:hidden; width:100%;"></iframe>
    </div>

    <div class="col-md-3">
        <a data-pin-do="embedUser" href="https://www.pinterest.com/confetimail/" data-pin-scale-width="80" data-pin-scale-height="290" data-pin-board-width="280">    Visita el perfil de Confeti de Pinterest.</a>
        <!-- Please call pinit.js only once per page -->
        <script type="text/javascript" async src="//assets.pinterest.com/js/pinit.js"></script>
    </div>

    <div class="col-md-3">
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/es_ES/sdk.js#xfbml=1&version=v2.4";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>

        <div class="fb-page" data-href="https://www.facebook.com/confetimail/" data-width="250" data-height="400" data-small-header="true" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true" data-show-posts="true"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/confetimail/"><a href="https://www.facebook.com/confetimail/">Confeti Mail</a></blockquote></div></div>
    </div>
</div>