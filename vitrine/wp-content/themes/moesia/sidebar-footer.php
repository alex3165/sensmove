<?php
/**
 *
 * @package Moesia
 */
?>

	<div id="sidebar-footer" class="footer-widget-area clearfix" role="complementary">
		<div class="container">
			<?php do_action( 'before_sidebar' ); ?>
			<?php if ( is_active_sidebar( 'sidebar-3' ) ) { ?>
				<div class="sidebar-column col-md-4 col-sm-4"> <?php
					dynamic_sidebar( 'sidebar-3'); 
				?> </div> <?php	
			}
			if ( is_active_sidebar( 'sidebar-4' ) ) { ?>
				<div class="sidebar-column col-md-4 col-sm-4"> <?php
					dynamic_sidebar( 'sidebar-4'); 
				?> </div> <?php	
			}
			if ( is_active_sidebar( 'sidebar-5' ) ) { ?>
				<div class="sidebar-column col-md-4 col-sm-4"> <?php
					dynamic_sidebar( 'sidebar-5'); 
				?> </div> <?php	
			}		
			?>
		</div>	
	</div>