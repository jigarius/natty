<?php

defined('NATTY') or die;

?>
<body class="<?php echo $flags; ?>">
    <div id="container">
        <div class="head-cont">
            <div class="container">
                <a href="<?php echo Natty::url(); ?>" title="<?php echo $system_siteCaption; ?>" id="logo"><?php echo $system_siteName; ?></a>
                <div class="n-fl-ri">
                    <?php echo natty_render($output['head-r']); ?>
                </div>
            </div>
        </div>
        <div class="showcase-t-cont">
            <?php echo natty_render($output['showcase-t']); ?>
        </div>
        <div class="body-cont">
            <div class="container">
                <div id="content">
                    <?php
                    echo Natty\Console::render();
                    echo '<h1 id="heading">' . $attributes['title'] . '</h1>';
                    echo natty_render($output['content']);
                    ?>
                </div>
            </div>
        </div>
        <div class="showcase-b-cont">
            <?php echo natty_render($output['showcase-b']); ?>
        </div>
        <div class="foot-cont">
            <div class="container">
                <?php echo natty_render($output['foot']); ?>
            </div>
        </div>
    </div>
</body>