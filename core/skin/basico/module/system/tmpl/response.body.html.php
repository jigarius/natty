<?php

defined('NATTY') or die;

$user = \Natty::getUser();

?>
<body>
    <div id="container">
        <div class="head-cont">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <?php echo natty_render($output['head-left']); ?>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <?php echo natty_render($output['head-right']); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="body-cont">
            <div class="container">
                <div id="content">
                    <?php
                    echo Natty\Console::render();
                    echo natty_render($output['content']);
                    ?>
                </div>
            </div>
        </div>
        <div class="foot-cont">
            <div class="container">
                <?php if ( $user->can('system--administer') ):
                    $_memory_usage = (memory_get_peak_usage(TRUE)/(1024*1024));
                    $_render_started = number_format(NATTY_ETIME - NATTY_STIME, 3);
                    echo '<p class="n-ta-ce performance-stat">Rendering started in ' . $_render_started . ' sec; Peak memory usage: ' . $_memory_usage . ' Kb';
                endif; ?>
                <p class="copyright-notice n-ta-ce"><?php echo '&copy; ' . date('Y') . ' ' . Natty::readSetting('system--siteName'); ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>