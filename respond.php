<?php
/*
Plugin Name: Respond (advertising and social sharing with Facebook, Twitter and LinkedIn)
Plugin URI: http://www.respondhq.com
Description: Respond gives WordPress publishers a simple way to earn extra revenue through unobtrusive advertising. Respond also gives your visitors an easy way share your content through Facebook, Twitter and LinkedIn.
Version: 3.0.2
Author: Respond
Author URI: http://www.respondhq.com
License: GPL2
*/

/* Plugin Authored by Aidan Watt aidan@hotfootdesign.co.uk on behalf of Azullo Respond */

/*  Copyright 2012  Respond  (email : team@respondhq.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function azullo_respond_buttons($content) {
	$options = get_option('azullo_respond');
	
	$display_respond = FALSE;
	
	//Display where checking
	if ((is_home() || is_front_page()) && $options['display_home'] == 1)  {
		$display_respond = TRUE;
	} elseif(is_archive() && !is_category() && $options['display_archives'] == 1) {
		$display_respond = TRUE;
	} elseif(is_category() && $options['display_categories'] == 1) {
		$display_respond = TRUE;
	} elseif(is_page() && $options['display_page'] == 1) {
		$display_respond = TRUE;
	} elseif (is_single()) {
        $display_respond = TRUE;
	}
	
	//If user_id in options add the button
	if(!empty($options['user_id']) && $display_respond) {
		
		$categories = '';
		
		foreach((get_the_category()) as $category) { 
    		$categories .= $category->cat_name.' '; 
		} 
		
		$attributes = 'title="'.$categories.get_the_title().'" keyword="'.get_the_title().'" category="'.$categories.'"';
		
		//Check Style
		if($options['style'] == 'button') {
			$button = '<respond_button '.$attributes.'></respond_button>';
		} else {
			$button = '<respond_social '.$attributes.' url="'.get_permalink().'"></respond_social>';
		}
	
		//Display at top of content
		if($options['position_top'] == 1) {
			$content = $button."\n".$content;
		}
		
		//Display at bottom of content
		if($options['position_bottom'] == 1) {
			$content .= "\n".$button;
		}
	}
	
	return $content;
}

function azullo_respond_javascript() {
	$options = get_option('azullo_respond');
	
	echo '<script src="https://services.respondhq.com/Scripts/Azullo.Respond.UI.V3.0.js" type="text/javascript"></script>';
	echo '<script type="text/javascript">Respond.AdBuilder(0, '.$options['user_id'].');</script>';
}

function azullo_respond_activate() {
	//If the plugin hasn't been activated before set some defaults
	if(!get_option('azullo_respond')) {
		$options = array(
			'user_id' => '',
			'user_error' => '',
			'style' => 'bar',
			'position_top' => 1,
			'position_bottom' => 1,
			'display_home' => 1,
			'display_archives' => 1,
			'display_categories' => 1,
			'display_page' => 1
		);
		
		add_option('azullo_respond', $options);
	}
}

function azullo_respond_admin() {
	add_options_page('Respond', 'Respond', 'manage_options', 'azullo-respond-settings', 'azullo_respond_admin_page');
}

function azullo_respond_admin_init(){
	register_setting('azullo_respond_options', 'azullo_respond', 'azullo_respond_options_sanitize');
}

function azullo_respond_options_sanitize($options) {
	//Get current options to re-include if nessecery
	$current_options = get_option('azullo_respond');
	
	//Set id as these won't be submitted most of the time
	$options_new['user_id'] = $current_options['user_id'];
	
	//If id is submitted check it is an interger
	if(isset($options['user_submit'])) {
		if(!empty($options['user_id'])) {
			//Prep id	
			$options['user_id'] = trim($options['user_id']);
			
			//if the id is an interger it will do!
			if(absint($options['user_id'])) {
				$options_new['user_id'] = $options['user_id'];
			} else {
				add_settings_error('azullo_respond', 'azullo_respond', 'The ID should be a number.');
			}
		} else {
			add_settings_error('azullo_respond', 'azullo_respond', 'The ID is required.');
		}
	}
	
	if(!empty($current_options['user_id'])) {
		$options_new['style'] = $options['style'];
	} else {
		$options_new['style'] = $current_options['style'];//If current email is empty first time submission so maintain defaults
	}
	
	//If option submitted or first time submission set to true
    if(isset($options['position_top']) || empty($current_options['user_id'])) {
		$options_new['position_top'] = 1;
	} else {
		$options_new['position_top'] = '';
	}
	
	//If option submitted or first time submission set to true
    if(isset($options['position_bottom']) || empty($current_options['user_id'])) {
		$options_new['position_bottom'] = 1;
	} else {
		$options_new['position_bottom'] = '';
	}
	
	//If no position is selected throw error if not first time submission and keep current
	if(!isset($options['position_top']) && !isset($options['position_bottom'])) {
		
		if(!empty($current_options['user_id'])) {
			add_settings_error('azullo_respond', 'azullo_respond', 'A least one position needs to be selected.');
		}
		
		$options_new['position_top'] = $current_options['position_top'];
		$options_new['position_bottom'] = $current_options['position_bottom'];
	}
	
	//If option submitted or first time submission set to true
	if(isset($options['display_home']) || empty($current_options['user_id'])) {
		$options_new['display_home'] = 1;
	} else {
		$options_new['display_home'] = '';
	}
	
	//If option submitted or first time submission set to true
	if(isset($options['display_archives']) || empty($current_options['user_id'])) {
		$options_new['display_archives'] = 1;
	} else {
		$options_new['display_archives'] = '';
	}
	
	//If option submitted or first time submission set to true
	if(isset($options['display_categories']) || empty($current_options['user_id'])) {
		$options_new['display_categories'] = 1;
	} else {
		$options_new['display_categories'] = '';
	}
	
	//If option submitted or first time submission set to true
    if(isset($options['display_page']) || empty($current_options['user_id'])) {
		$options_new['display_page'] = 1;
	} else {
		$options_new['display_page'] = '';
	}
	
	return $options_new;
}

function azullo_respond_admin_page() { ?>
    
	<div class="wrap">
		<h2>Respond (advertising and social sharing with Facebook, Twitter and LinkedIn).</h2>
        
        	<p>Respond is a simple way to earn extra revenue through relevant 'call to action' buttons, which are contextually matched to your content. Respond also offers an easy way for your visitors to share your content through the most popular social networks.</p>
        
            <form method="post" action="options.php">
        
            <?php settings_fields('azullo_respond_options'); ?>
            
            <?php do_settings_sections('azullo_respond_options'); ?>
            
            <?php $options = get_option('azullo_respond'); ?>
            
            <h3 id="azullo_respond_header">Your Account</h3>
            
            <div id="azullo_respond_user">
            	<p><strong>Get started:</strong> To use Respond please enter your Respond ID number below. You can find your Respond ID by signing into your account <a href="https://publishers.respondhq.com/sign-in" target="_blank">here</a> - it's displayed at the top of the column on the right side of the page</p>
                
                <table class="form-table">
                    <tr>
                        <th>Respond ID Number:</th>
                        <td><input name="azullo_respond[user_id]" id="azullo_respond_user_id" type="text" value="<?php echo $options['user_id']; ?>" autofill='off' autocomplete='off'/><input name="azullo_respond[user_submit]" id="azullo_respond_hidden" type="hidden" value="1"/></td>
                    </tr>
                </table>
                
                <p>Don't have a Respond account? <a href="https://publishers.respondhq.com/sign-up" target="_blank">Register free</a> - it only takes a minute.</p>
            </div>
            
            <?php if(!empty($options['user_id'])) {//Don't show if first time submission ?>
            <script type="text/javascript">  
				<?php if(!empty($options['user_id'])) { ?>
				var azullo_respond_user_id = '<?php echo $options['user_id']; ?>';
				
				jQuery('#azullo_respond_header').after('<p>You are logged in with <strong>Id: <?php echo $options['user_id']; ?></strong>&nbsp; <a id="azullo_respond_account" style="cursor:pointer">Change</a></p>');
				jQuery('#azullo_respond_user_id').val('');
				jQuery('#azullo_respond_user').hide();
				jQuery('#azullo_respond_hidden').attr('disabled', true);
				
				jQuery('#azullo_respond_account').click(function() {
					jQuery('#azullo_respond_user').toggle();
					
					if(jQuery('#azullo_respond_user').css('display') == 'none') {
						jQuery('#azullo_respond_hidden').attr('disabled', true);
					} else {
						jQuery('#azullo_respond_hidden').attr('disabled', false);
					}
					
					return false;					  
				});
				<?php } ?>
			</script>
            
            <h3>Which Respond button would you like to use?</h3>
            <table class="form-table">
            	<tr>
					<th style="width:270px"><input name="azullo_respond[style]" type="radio" value="bar" <?php checked('bar', $options['style']); ?> /> Respond with Social Sharing</th>
					<td><respond_social url="<?php echo site_url(); ?>" title="<?php echo get_bloginfo('name'); ?>"></respond_social></td> 
                </tr>
                <tr>
					<th style="width:270px"><input name="azullo_respond[style]" type="radio" value="button" <?php checked('button', $options['style']); ?>/> Respond without Social Sharing</th> 
					<td><respond_button></respond_button></td> 
                </tr>
            </table>
            
            <style type="text/css">.azullo_buttonDisplay { margin:0 !important; }</style>
            
            <script src="https://services.respondhq.com/Scripts/Azullo.Respond.UI.V3.0.js" type="text/javascript"></script>
			<script type="text/javascript">
				Respond.AdBuilder(0, <?php echo $options['user_id']; ?>);  
            </script>
            
            <h3>Where would you like the button to be placed?</h3>
            <table class="form-table">
                <tr>
					<th><input name="azullo_respond[position_top]" type="checkbox" value="1" <?php checked(1, $options['position_top']); ?> /> Above your content/posts</th> 
                </tr>
                         
                <tr>
					<th><input name="azullo_respond[position_bottom]" type="checkbox" value="1" <?php checked(1, $options['position_bottom']); ?> /> Below your content/posts</th>  
                </tr>
            </table>
            
            <h3>Which parts of your site would you like to display the button?</h3>
            <table class="form-table">
                <tr>
					<th><input name="azullo_respond[display_home]" type="checkbox" value="1" <?php checked(1, $options['display_home']); ?> /> Show on the home page (also known as the index page)</th> 
                </tr>
                <tr>
					<th><input name="azullo_respond[display_page]" type="checkbox" value="1" <?php checked(1, $options['display_page']); ?> /> Show on pages</th>  
                </tr>
                <tr>
					<th><input name="azullo_respond[display_archives]" type="checkbox" value="1" <?php checked(1, $options['display_archives']); ?> /> Show on archives</th> 
                </tr>
                <tr>
					<th><input name="azullo_respond[display_categories]" type="checkbox" value="1" <?php checked(1, $options['display_categories']); ?> /> Show on categories</th> 
                </tr>                       
            </table>
            <?php } ?>
    
            <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"  /></p>
            
            <p><em>If you would like help or have questions about Respond please visit <a href="http://www.respondhq.com">www.respondhq.com</a>. You can contact us by emailing <a href="mailto:team@respondhq.com">team@respondhq.com</a> or by calling +44 (0) 1524 509018.</em></p>
		</form>
	</div>
<?php } 

add_filter('the_content', 'azullo_respond_buttons');
add_filter('wp_footer', 'azullo_respond_javascript');

register_activation_hook(__FILE__, 'azullo_respond_activate');

if (is_admin()) {
	add_action('admin_menu', 'azullo_respond_admin');
	add_action('admin_init', 'azullo_respond_admin_init');
}

?>