<?php 

    define('DB_HOST', 'localhost');
    define('DB_NAME', 'suggest-a-song');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    /* --- DATABASE SCRIPT ---
        
        CREATE DATABASE IF NOT EXISTS `suggest-a-song`;
        
        USE `suggest-a-song`;
        
        DROP TABLE IF EXISTS `suggested_songs`;
        
        CREATE TABLE IF NOT EXISTS `suggested_songs`
        (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `ts` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `video_code` VARCHAR(32) NOT NULL UNIQUE
        );
    
    */

    if($_SERVER['REQUEST_METHOD'] == 'GET' && (isset($_GET['get_list']) || isset($_GET['youtube_link'])))
    {
        $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
        
        if(isset($_GET['get_list']))
        {
            $links = array();
            $sql = 'SELECT * FROM `suggested_songs`;';
            
            $rows = $dbh->query($sql);
            
            foreach($rows as $row)
            {
                $links[] = 'http://www.youtube.com/watch?'.$row['video_code'];
            }
            
            echo json_encode($links);
        }
        else
        {
        $sql = 'INSERT INTO `suggested_songs`(`video_code`) VALUES(:video_code);';
        
        $sth = $dbh->prepare($sql);
        $sth->bindParam(':video_code', $_GET['youtube_link'], PDO::PARAM_STR);
        $sth->execute();
        }
        
        
        
        $dbh = null;
        
        exit(0);
    }
    
?>

<!DOCTYPE html>
<html>
    <head>
        <title>
            Suggest a song
        </title>
        
        <meta charset="UTF-8" />
        
        <script type="text/javascript" language="javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        
        <script type="text/javascript" language="javascript">
        
            function submitSuggestion()
            {
                var videoLink           = $('#youtube_link').val();
                var filteredVideoCode   = filterVideoCode(videoLink);
                
                if(filteredVideoCode)
                {
                    $.get(  '<?php echo $_SERVER['SCRIPT_NAME']; ?>',
                            {
                                'youtube_link': filteredVideoCode
                            },
                            function()
                            {
                                $('#suggested_message').fadeIn();
                                
                                setTimeout( function()
                                            {
                                                $('#suggested_message').fadeOut();
                                            },
                                            3000);
                            });
                }

                return false;
            }
            
            
            function filterVideoCode(videoUrl)
            {
                var matches = videoUrl.match(/v=[^&]*/);
                return matches[0];
            }
            
            
            function getList()
            {
                 $.getJSON( '<?php echo $_SERVER['SCRIPT_NAME']; ?>',
                        {
                            'get_list': true
                        },
                        function(data)
                        {
                            for(link in data)
                            {
                                $('#suggested_list').append('<a href="' + data[link] + '" target="_blank">' + data[link] + '</a>\n<br />');
                            }
                        });
            }
        
        </script>
        
        <style type="text/css">
        
            body
            {
                color:              #5dfc0a;
                background-color:   black;
                
                text-align:         center;
            }
            
            div
            {
                padding:            16px;
            }
            
            #suggested_message
            {
                display:            none;
            }
            
            #link_note
            {
                width:              50%;
                margin:             0px 25%;
            }
        
        </style>
    </head>
    <body>
        <object width="1280" height="720">
            <param name="movie" value="http://fpdownload.adobe.com/strobe/FlashMediaPlayback_101.swf">
            </param>
            <param name="flashvars" value="src=rtmp%3A%2F%2Fthedailyleaf.com%2Flive%2Fthedailyleaf&poster=https%3A%2F%2F1-media-cdn.foolz.us%2Fffuuka%2Fboard%2Fsp%2Fimage%2F1343%2F81%2F1343812923366.jpg&autoPlay=true">
            </param>
            <param name="allowFullScreen" value="true">
            </param>
            <param name="allowscriptaccess" value="always">
            </param>
            
            <embed src="http://fpdownload.adobe.com/strobe/FlashMediaPlayback_101.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="600" height="409" flashvars="src=rtmp%3A%2F%2Fthedailyleaf.com%2Flive%2Fthedailyleaf&poster=https%3A%2F%2F1-media-cdn.foolz.us%2Fffuuka%2Fboard%2Fsp%2Fimage%2F1343%2F81%2F1343812923366.jpg&autoPlay=true">
            </embed>
        </object>
        
        <hr />
        
        <div id="link_note">
            Paste a link from <a href="http://www.youtube.com/">YouTube</a> into the box below to suggest it for the stream. Make sure to include the "v=XXXXXXXXXXX" part of the URL where "XXXXXXXXXXX" is a jumble of numbers, letters, and maybe some characters thrown in there.
        </div>
        
        <form onsubmit="return submitSuggestion();">
            <input type="text" name="youtube_link" id="youtube_link" />
            <input type="submit" value="suggest it" />
        </form>
        
        <div id="suggested_message">
            Video suggested!
        </div>
        
        <div id="suggested_list">
            <input type="button" value="View all suggestions" onclick="javascript: getList();"/>
            <br />
        </div>
    </body>
</html> 