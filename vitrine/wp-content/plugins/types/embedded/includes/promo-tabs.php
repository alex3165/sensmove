<?php
/*
 * Promo tabs.
 * 
 * Showed in lower part on types admin screens.
 */

?>
<?php if (!function_exists('wprc_is_logged_to_repo') || !wprc_is_logged_to_repo(WPCF_REPOSITORY)) { ?>
<script type='text/javascript'>
/**
 * Help tabs.
 */
jQuery(function(){
 jQuery('.wpcf-contextual-help-tabs').delegate('a', 'click focus', function(e) {
	var link = jQuery(this),
		panel;

	e.preventDefault();

	// Don't do anything if the click is for the tab already showing.
	if ( link.is('.active a') )
		return false;

	// Links
	jQuery('.wpcf-contextual-help-tabs .active').removeClass('active');
	link.parent('li').addClass('active');

	panel = jQuery( link.attr('href') );

	// Panels
	jQuery('.wpcf-help-tab-content').not( panel ).removeClass('active').hide();
	panel.addClass('active').show();
});
});
</script>
    <div class="wpcf-promo-tabs">
        <div class="wpcf-contextual-help-wrap">
            <div class="wpcf-tab-content"></div>
            <div class="wpcf-contextual-help-columns">
                <div class="wpcf-contextual-help-tabs">
                    <ul>
                        <li<?php if ($selected == 0) echo " class='active'"; ?> id="tab-link-types-promo1-tab">
                            <a href="#tab-panel-types-promo1-tab"><?php
    _e('Types is part of the entire toolbox', 'wpcf');

    ?></a>
                        </li>
                        <li<?php if ($selected == 1) echo " class='active'"; ?> id="tab-link-types-promo2-tab">
                            <a href="#tab-panel-types-promo2-tab"><?php
                            _e('Discover what you can build with WordPress',
                                    'wpcf');

                            ?></a>
                        </li>
                        <li<?php if ($selected == 2) echo " class='active'"; ?> id="tab-link-types-promo3-tab">
                            <a href="#tab-panel-types-promo3-tab"><?php
                            _e('Learn how to make your sites interactive',
                                    'wpcf');

                            ?></a>
                        </li>
                        <li<?php if ($selected == 3) echo " class='active'"; ?> id="tab-link-types-promo4-tab">
                            <a href="#tab-panel-types-promo4-tab"><?php
                            _e('Learn how to display custom content anyway you choose',
                                    'wpcf');

                            ?></a>
                        </li>
                    </ul>
                </div>

                <div class="wpcf-contextual-help-tabs-wrap">
                    <div class="wpcf-help-tab-content<?php if ($selected == 0) echo ' active'; ?>" id="tab-panel-types-promo1-tab">
                        <strong><?php
                        _e('Types lets you customize the WordPress admin, but you can do a lot more with the entire package.',
                                'wpcf');

    ?></strong><br />
                        <ul>
                            <li><?php
                            _e('Views - display custom content anyway and anywhere you like',
                                    'wpcf');

                            ?></li>
                            <li><?php
                            _e('CRED - build forms that create and edit WordPress content',
                                    'wpcf');

                            ?></li>
                            <li><?php
                            _e('Access - control who can access different parts of the site',
                                    'wpcf');

    ?></li>
                        </ul>

                        <br /><br />
                        <?php
                        _e('The complete framework lets you build powerful, flexible and interactive WordPress sites.',
                                'wpcf');

                        ?>
                        <br /><br />
                        <a href="http://wp-types.com/?utm_source=typesplugin&utm_medium=plugin&utm_term=learnmore&utm_content=promobox1&utm_campaign=types" target="_blank" class="button-primary" title="<?php
                        _e('Learn more', 'wpcf')

                        ?>"><?php _e('Learn more', 'wpcf') ?></a>&nbsp;&nbsp;<a href="http://wp-types.com/free-trial/?utm_source=typesplugin&utm_medium=plugin&utm_term=freetrial&utm_content=promobox1&utm_campaign=types" target="_blank" class="button-secondary"><?php
                        _e('Free Trial', 'wpcf')

                        ?></a>&nbsp;&nbsp;<a href="http://wp-types.com/buy/?utm_source=typesplugin&utm_medium=plugin&utm_term=pricing&utm_content=promobox1&utm_campaign=types" target="_blank" class="button-secondary" title="<?php
                        _e('Pricing', 'wpcf')

                        ?>"><?php _e('Pricing', 'wpcf') ?></a>
                    </div>

                    <div class="wpcf-help-tab-content<?php if ($selected == 1) echo ' active'; ?>" id="tab-panel-types-promo2-tab">
                        <strong><?php
                    _e('You already know that WordPress can build a lot more than blogs, but just how much more?',
                            'wpcf');

                        ?></strong>
                        <br /><br />
    <?php
    _e('Learn how to build your own magazine designs, classifieds, listing, e-commerce sites and more.',
            'wpcf');

    ?>
                        <br /><br />
                            <?php
                            _e('Discover WP is a free online learning resource, where you can launch fully-functional test sites and experiment with complete sites.',
                                    'wpcf');

                            ?>
                        <br /><br />
                        <a href="http://wp-types.com/free-trial/?utm_source=typesplugin&utm_medium=plugin&utm_term=freelearningsite&utm_content=promobox2&utm_campaign=types" target="_blank" class="button-primary" title="<?php
                            _e('Get you FREE learning site', 'wpcf')

                            ?>"><?php _e('Get you FREE learning site', 'wpcf')

                            ?></a>
                    </div>

                    <div class="wpcf-help-tab-content<?php if ($selected == 2) echo ' active'; ?>" id="tab-panel-types-promo3-tab">
                        <strong><?php
                    _e('Need to build sites, where visitors can create and edit content?',
                            'wpcf');

                            ?></strong>
                        <br /><br />
                        <?php
                        _e('Meet CRED, our content-forms editor!', 'wpcf');

                        ?>
                        <br /><br />
                        <?php
                        _e('CRED lets you build forms that work directly on WordPress content. CRED forms include fields for standard fields, custom fields and taxonomy. Everything that you can build with Types, CRED can edit.',
                                'wpcf');

                        ?>
                        <br /><br />
                            <?php
                            _e("CRED is a lot more than a pretty forms plugin. It's a complete development framework that lets you build powerful web applications with WordPress.",
                                    'wpcf');

                            ?>
                        <br /><br />
                        <a href="http://wp-types.com/home/cred/?utm_source=typesplugin&utm_medium=plugin&utm_term=meetcred&utm_content=promobox3&utm_campaign=types" target="_blank" class="button-primary" title="<?php
                            _e('Meet CRED', 'wpcf')

                            ?>"><?php _e('Meet CRED', 'wpcf') ?></a>&nbsp;&nbsp;<a href="http://wp-types.com/free-trial/?utm_source=typesplugin&utm_medium=plugin&utm_term=freetrial&utm_content=promobox3&utm_campaign=types" target="_blank" class="button-secondary"><?php
                            _e('Free Trial', 'wpcf')

                            ?></a>&nbsp;&nbsp;<a href="http://wp-types.com/buy/?utm_source=typesplugin&utm_medium=plugin&utm_term=pricing&utm_content=promobox3&utm_campaign=types" target="_blank" class="button-secondary" title="<?php
                    _e('Pricing', 'wpcf')

                    ?>"><?php _e('Pricing', 'wpcf') ?></a>
                    </div>

                    <div class="wpcf-help-tab-content<?php if ($selected == 3) echo ' active'; ?>" id="tab-panel-types-promo4-tab">
                        <strong><?php
                       _e("Views is a powerful display engine, which lets you build WordPress sites from within the admin dashboard.",
                               'wpcf');

                            ?></strong>
                        <br /><br />
    <?php
    _e("You can create templates for single pages, query content from the database and display it with your design.",
            'wpcf');

    ?>
                        <br /><br />
                        <a href="http://wp-types.com/home/views-create-elegant-displays-for-your-content/?utm_source=typesplugin&utm_medium=plugin&utm_term=meetviews&utm_content=promobox4&utm_campaign=types" target="_blank" class="button-primary" title="<?php
    _e('Meet Views', 'wpcf')

    ?>"><?php _e('Meet Views', 'wpcf') ?></a>&nbsp;&nbsp;<a href="http://wp-types.com/free-trial/?utm_source=typesplugin&utm_medium=plugin&utm_term=freetrial&utm_content=promobox4&utm_campaign=types" target="_blank" class="button-secondary"><?php
    _e('Free Trial', 'wpcf')

    ?></a>&nbsp;&nbsp;<a href="http://wp-types.com/buy/?utm_source=typesplugin&utm_medium=plugin&utm_term=pricing&utm_content=promobox4&utm_campaign=types" target="_blank" class="button-secondary" title="<?php
    _e('Pricing', 'wpcf')

    ?>"><?php _e('Pricing', 'wpcf') ?></a>
                    </div>
                </div>
            </div>
        </div> 
    </div>

    <?php
}

if (function_exists('wprc_is_logged_to_repo') && !wprc_is_logged_to_repo(WPCF_REPOSITORY)) {
    echo '<p>' . sprintf(__('Already purchased our Toolset package? %sLog-in to wp-types.com%s to remove this information box.', 'wpcf'), '<a href="' . admin_url('options-general.php?page=installer/pages/repositories.php') . '">', '</a>') . '</p>';
} else {
    if (!function_exists('wprc_is_logged_to_repo')) {
        echo '<p>' . sprintf(__('Already purchased our Toolset package? Install our Installer plugin and login to wp-types to remove this information box. %sInstructions &raquo;%s', 'wpcf'), '<a href="http://wp-types.com/documentation/installation/#2" target="_blank">', '</a>'). '</p>';
    }
}