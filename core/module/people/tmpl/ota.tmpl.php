<?php defined('NATTY') or die; ?>
<p>Hello <?php echo $user->name; ?>.</p>
<p>Please click the button below to gain one-time-access to your account. Clicking the button, will take you to a page where you can change your password. However, remember to change your password before you sign out because the button below will only work once.</p>
<?php echo natty_render($form);