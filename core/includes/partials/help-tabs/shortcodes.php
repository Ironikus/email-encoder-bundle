<h3><?php echo EEB()->helpers->translate( 'Shortcodes', 'help_tab-shortcodes' ); ?></h3>
<p><?php echo EEB()->helpers->translate( 'You can protect those addresses using the following shortcode. Everything you add within these both tags, will be checked for emails automatically.', 'help_tab-shortcodes' ); ?></p>
<p><code>[eeb_protect_emails protect_using="with_javascript"]…[/eeb_protect_emails]</code>
</p>
<p><?php echo EEB()->helpers->translate( 'Create a protected mailto link:', 'help_tab-shortcodes' ); ?></p>
<p><code>[eeb_mailto email="info@myemail.com" extra_attrs="target='_blank'" method="rot13" display="Custom Text"]</code>
</p>
<p><?php echo EEB()->helpers->translate( 'You can also protect phone numbers and any kind of text by using the following shortcode:', 'help_tab-shortcodes' ); ?></p>
<h4><code>[eeb_protect_content protection_text="I am a noscript text" method="rot13"]My Email[/eeb_protect_content]</code></h4>
<p><?php echo EEB()->helpers->translate( 'Display the encoder form', 'help_tab-shortcodes' ); ?></p>
<p><code>[eeb_form]</code>
</p>
