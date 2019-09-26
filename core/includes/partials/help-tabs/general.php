<?php $pluginData = get_plugin_data( EEB_PLUGIN_FILE ); ?>
<h3><i class="dashicons-before dashicons-email"></i>  <?php echo $pluginData['Name'] ?> - v<?php echo $pluginData['Version']; ?></h3>
<p>
    <?php echo EEB()->helpers->translate( 'The plugin works out-of-the-box to protect your email addresses. All settings are default set to protect your email addresses automatically.', 'help_tab-general' ); ?>
</p>
<p>
    <?php echo EEB()->helpers->translate( 'To report problems or bugs or for support, please use <a href="https://wordpress.org/support/plugin/email-encoder-bundle#postform" target="_new">the official forum</a>.', 'help_tab-general' ); ?>
</p>
<p>
    <?php echo EEB()->helpers->translate( 'You can now also check your website protection using our email checker tool: <a href="https://ironikus.com/email-checker/" target="_blank">https://ironikus.com/email-checker/</a>.', 'help_tab-general' ); ?>
</p>
<p>
    Visit us at <a href="https://ironikus.com" target="_blank" title="Visit us at https://ironikus.com" >https://ironikus.com</a>
    <i class="dashicons-before dashicons-universal-access"></i>
</p>