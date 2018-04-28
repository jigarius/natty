<?php defined('NATTY') or die; ?>
<!--DOCTYPE html-->
<html>
    <head>
        <title><?php echo \Natty::readSetting('system--siteName'); ?></title>
        <style type="text/css">
            
            body {
                color: #999;
                font-family: Arial;
            }
            #wrap {
                margin: 0 auto;
                max-width: 600px;
                padding-top: 30px;
                text-align: center;
            }
            
        </style>
        <script type="text/javascript">
            
            setInterval(function() {
                window.location.reload();
            }, 60 * 1000);
            
        </script>
    </head>
    <body>
        <div id="wrap">
            <?php
            
            $site_logo = \Natty::readSetting('system--siteRoot') . '/logo.png';
            if ( is_file($site_logo) ) {
                echo '<img src="' . NATTY_BASE . \Natty::readSetting('system--sitePath') . '/logo.png" alt="' . \Natty::readSetting('system--siteName') . '" />';
            }
            else {
                echo '<h1>' . \Natty::readSetting('system--siteName') . '</h1>';
            }
            ?>
            <p><?php echo \Natty::readSetting('system--offlineModeMessage'); ?></p>
        </div>
    </body>
</html>