<?php
/**
 * Main Template
 */

$currentScreen = get_current_screen();
$columnCount = (1 == $currentScreen->get_columns()) ? 1 : 2;
$mulsitie_slug = ( is_multisite() ) ? 'network/' : '';

?>

<div class="wrap">
    <h1><?php echo get_admin_page_title() ?></h1>

    <?php if( ! empty( $this->display_notices ) ) : ?>
        <div class="eeb-admin-notices">
            <?php foreach( $this->display_notices as $single_notice ) : ?>
                <?php echo $single_notice; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php settings_fields( $this->page_name ); ?>

        <input type="hidden" name="<?php echo $this->page_name; ?>_nonce" value="<?php echo wp_create_nonce( $this->page_name ) ?>">

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-<?php echo $columnCount; ?>">
                <?php include( 'widgets/main.php' ); ?>

                <div id="postbox-container-1" class="postbox-container">
                    <?php include( 'widgets/sidebar.php' ); ?>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes('', 'normal', ''); ?>
                </div>
            </div>
        </div>
    </form>
</div>
