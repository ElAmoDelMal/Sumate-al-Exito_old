<?php
add_action('init', array(AUTHORSURE_ADMIN,'init'));

class authorsure_admin {

    private static $screen_id;
    
    static function get_screen_id() {
    	return self::$screen_id;
    }    

    static function set_screen_id($id) {
    	self::$screen_id = $id;
    }  
    
	static function init() {
		add_action('admin_menu', array(AUTHORSURE_ADMIN, 'admin_menu'));
	}
	
	static function admin_menu() {	
		self::set_screen_id(add_options_page('AuthorSure', 'AuthorSure', 'manage_options', AUTHORSURE_ADMIN, array(AUTHORSURE_ADMIN, 'options_panel')));	
		add_action('load-'.self::get_screen_id(), array(AUTHORSURE_ADMIN, 'load_page'));
		add_action('admin_footer-'.self::get_screen_id(), array(AUTHORSURE_ADMIN, 'toggle_postboxes'));	
	}

	static function load_style() {
    	wp_enqueue_style( 'AUTHORSURE_ADMIN', AUTHORSURE_PLUGIN_URL.'authorsure-admin.css',array(),AUTHORSURE_VERSION);
 	}
	
	static function load_script() {
    	wp_enqueue_script( 'AUTHORSURE_ADMIN', AUTHORSURE_PLUGIN_URL.'authorsure-admin.js',array('jquery'),AUTHORSURE_VERSION,true);
 	}
	
	static function load_page() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		add_action ('admin_enqueue_scripts',array(AUTHORSURE_ADMIN, 'load_style'));
		add_action ('admin_enqueue_scripts',array(AUTHORSURE_ADMIN, 'load_script'));
		add_meta_box('authorsure-help', __('Help',AUTHORSURE), array(AUTHORSURE_ADMIN, 'help_panel'), self::get_screen_id(), 'side', 'core');
		add_meta_box('authorsure-terminology', __('Google+ Terminology',AUTHORSURE), array(AUTHORSURE_ADMIN, 'google_panel'), self::get_screen_id(), 'side', 'core');
		add_meta_box('authorsure-rel-author', __('AuthorSure Post Settings (rel="author")',AUTHORSURE), array(AUTHORSURE_ADMIN, 'post_panel'), self::get_screen_id(), 'normal', 'core');
		add_meta_box('authorsure-archive', __('AuthorSure Archive Settings (rel="author")',AUTHORSURE), array(AUTHORSURE_ADMIN, 'archive_panel'), self::get_screen_id(), 'normal', 'core');
		add_meta_box('authorsure-rel-publisher', __('AuthorSure Home Page Settings (rel="publisher")',AUTHORSURE), array(AUTHORSURE_ADMIN, 'publisher_panel'), self::get_screen_id(), 'normal', 'core');
		add_meta_box('authorsure-rel-me', __('AuthorSure Author Page Settings (rel="me")',AUTHORSURE), array(AUTHORSURE_ADMIN, 'author_panel'), self::get_screen_id(), 'normal', 'core');
		add_meta_box('authorsure-advanced', __('AuthorSure Author Page Advanced Settings (rel="me")',AUTHORSURE), array(AUTHORSURE_ADMIN, 'advanced_panel'), self::get_screen_id(), 'normal', 'core');
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {
    		$current_screen->add_help_tab( array(
        		'id'	=> 'authorsure_instructions_tab',
        		'title'	=> __('AuthorSure Instructions'),
        		'content'	=> '<h3>AuthorSure Administration Instructions</h3>
<ol>
<li>Tweak the Authorsure options to work best with your WordPress theme.</li>
<li>Run through the process yourself by setting up your own Google profile following the instructions in the Help on the profile page.</li>
<li>For the other authors on the blog you can set up their profiles for them using the Edit User page or you can let them make the updates themselves on the Your Profile page</li>
<li>To use the rel="publisher" then create a Google Page (not a Profile) and then create a link to the home page of the blog.</li>
</ol>') );
    		$current_screen->add_help_tab( array(
        		'id'	=> 'authorsure_options_tab',
        		'title'	=> __('AuthorSure Options'),
        		'content' => '<h3>AuthorSure Options</h3>
<p>On the Options page you can specify:</p> 
<ol>
<li>How the rel="author" link to the author page is created on each post/page.</li>
<li>How the rel="me" profile links appear on the author pages.</li>
<li>How the rel="publisher" link can be applied to the home page.</li>
</ol>') );
		}
	}

    static function toggle_postboxes() {
    	$hook = self::$screen_id;
    	print <<< TOGGLE_POSTBOXES
<script type="text/javascript">
//<![CDATA[
		jQuery(document).ready( function($) {
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			postboxes.add_postbox_toggles('{$hook}');
		});
//]]>
</script>
TOGGLE_POSTBOXES;
    }

	static function save() {
 		$profiles = authorsure::get_pro_options(false); 
		foreach ($profiles as $profile => $labels) { //update any profiles labels
       		$post_key = AUTHORSURE.'_'.$profile;
       		if (array_key_exists($post_key,$_POST)) $profiles[$profile] = array($_POST[$post_key],$labels[1]); //update label
    	} //end for	
  		$updateoptions = authorsure::save_pro_options($profiles);
		$options = authorsure::get_options(false);
    	foreach ($options as $option => $old_value) {
       		$post_key = AUTHORSURE.'_'.$option;
         	$options[$option] = array_key_exists($post_key,$_POST) ? trim(stripslashes($_POST[$post_key])) : false;
    	} //end for
  		$updateoptions = authorsure::save_options($options);
  		return sprintf('<div id="message" class="updated fade">%1$s</div>', __(
  			 $updateoptions  ?
  			 	"AuthorSure Settings saved." : "No AuthorSure settings were changed since last update.", AUTHORSURE_ADMIN));
	}

	static function post_panel($post, $metabox) {		
		$options = authorsure::get_options();
		$footnote_show_updated_date = $options['footnote_show_updated_date'] ? ' checked="checked"' : '';
		$menu = $options['author_rel']=="menu"?'checked="checked"':'';
		$byline = $options['author_rel']=="byline"?'checked="checked"':'';
		$footnote = $options['author_rel']=="footnote"?'checked="checked"':'';
		$box = $options['author_rel']=="box"?'checked="checked"':'';
		$hide_box_on_pages = $options['hide_box_on_pages'] ? ' checked="checked"' : '';
		$hide_box_on_front_page = $options['hide_box_on_front_page'] ? ' checked="checked"' : '';
		$box_nofollow_links = $options['box_nofollow_links'] ? ' checked="checked"' : '';
		$authors = wp_dropdown_users(array('who' => 'authors', 
			'selected' => $options['menu_primary_author'], 'name' => 'authorsure_menu_primary_author', 'show_option_none' => __('(not selected)'),
			'sort_column'=> 'display_name', 'echo' => 0));
		$about_page = wp_dropdown_pages(array('post_type' => 'page', 
			'selected' => $options['menu_about_page'], 'name' => 'authorsure_menu_about_page', 'show_option_none' => __('(not selected)'),
			'sort_column'=> 'menu_order, post_title', 'echo' => 0));

		print <<< AUTHORSURE_POST_PANEL
<pThis section deals with linking the post/page to an author profile page on this site.</p>
<p>You can leave the default settings here if your WordPress theme puts a link with rel=author to the author page in the post 'byline'.</p>
<h4>Author Indicator:</h4>
<fieldset><legend class="screen-reader-text"><span>Author Indicator</span></legend>
<label for="authorsure_byline"><input class="valinp" type="radio" name="authorsure_author_rel" id="authorsure_byline"  {$byline} 
value="byline" /> Byline - choose this option if you have a theme which indicates the author in the byline with rel="author".</label>
<label for="authorsure_footnote"><input class="valinp" type="radio" name="authorsure_author_rel" id="authorsure_footnote"  {$footnote} 
value="footnote" /> FootNote - choose this option if you want to create a rich snippet at the foot of the post/page that indicates when it was last updated.</label>
<label for="authorsure_box"><input class="valinp" type="radio" name="authorsure_author_rel" id="authorsure_box"  {$box} 
value="box" /> Author Box - choose this option if you want to show the author photo and bio after each post</label>
<label for="authorsure_menu"><input class="valinp" type="radio" name="authorsure_author_rel" id="authorsure_menu" {$menu} 
value="menu" /> Menu - choose this option if you have a single author on the site and you are using WordPress menus which allow you to specify rel="author" on the "About" page menu link.</label>
</fieldset>
<div id="author_footnote">
<h4><label>Author Prefix:</label><input name="authorsure_footnote_last_updated_by" id="authorsure_footnote_last_updated_by" type="text" value="{$options['footnote_last_updated_by']}" /></h4>
<p>The word or words that precede the author name in the footnote. (e.g. "By" or "Last updated by")</p>
<h4><label>Show Updated Date:</label><input name="authorsure_footnote_show_updated_date" id="authorsure_footnote_show_updated_date" type="checkbox" {$footnote_show_updated_date} value="1" /></h4>
<p>Check the box to show the date the post was last updated.</p>
<h4><label>Date Prefix:</label><input name="authorsure_footnote_last_updated_at" id="authorsure_footnote_last_updated_at" type="text" value="{$options['footnote_last_updated_at']}" /></h4>
<p>The word or words that precede the date in the footnote. (e.g. "at","on", etc.)</p>
</div>
<div id="author_box">
<h4><label>About:</label><input name="authorsure_box_about" id="authorsure_box_about" type="text" size="15" value="{$options['box_about']}" /></h4>
<p>The word or words that precede the author name in the title of the Author Box.</p>
<h4><label>Photo Size:</label><input name="authorsure_box_gravatar_size" id="authorsure_box_gravatar_size" type="text" width="5" value="{$options['box_gravatar_size']}" /></h4>
<p>Enter the author photo (avatar) size in pixels. The Avatar photo is the one you have set up at <a href="http://gravatar.com">gravatar.com</a>.</p>
<h4><label>Hide On Pages?:</label><input class="valinp" type="checkbox" name="authorsure_hide_box_on_pages" id="authorsure_hide_box_on_pages" {$hide_box_on_pages} value="1" /></h4>
<p>Check the box above to hide the author box on all your pages by default. If you want to show specific pages then can set the Authorsure 
author box settings by editing the individual page.</p>
<h4><label>Hide On Front Page?:</label><input class="valinp" type="checkbox" name="authorsure_hide_box_on_front_page" id="authorsure_hide_box_on_front_page" {$hide_box_on_front_page} value="1" /></h4>
<p>Check the box above to hide the author box on your front page. This option may be required to hide an unwanted author box for some WordPress themes that build the front page using snippets from other pages.</p>
<h4><label>Nofollow Links In Bio?:</label><input class="valinp" type="checkbox" name="authorsure_box_nofollow_links" id="authorsure_box_nofollow_links" {$box_nofollow_links} value="1" /></h4>
<p>Check the box above to ensure that any links that appear in the bio in the author box are made rel=nofollow.</p>
</div>
<div id="author_menu">
<h4><label>Primary Author:</label>{$authors}</h4>
<p>Choose the primary author of the site.</p>
<h4><label>About Page:</label>{$about_page}</h4>
<p>Choose the about page which appears in the menu. AuthorSure will add the link to the primary author's Google Plus profile at the foot of the selected page.</p>
</div>
AUTHORSURE_POST_PANEL;
	}


	function archive_panel ($post, $metabox) {
		$options = authorsure::get_options();
		$publisher = $options['archive_link']=="publisher"?'checked="checked"':'';
		$top = $options['archive_link']=="top"?'checked="checked"':'';
		$bottom = $options['archive_link']=="bottom"?'checked="checked"':'';
		$archive_intro_enabled = $options['archive_intro_enabled']?' checked="checked"':'';
		$authors = wp_dropdown_users(array('who' => 'authors', 
			'selected' => $options['archive_author_id'], 'name' => 'authorsure_archive_author_id',
			'sort_column'=> 'display_name', 'echo' => 0));		
		print <<< ARCHIVE_PANEL
<h4>Category and Tag Archives Settings:</h4>
<p>These settings determine how you manage authorship of your category, tag and any other taxonomy archive pages.</p>
<fieldset><legend class="screen-reader-text"><span>Author Link Position</span></legend>
<label for="archive_author_link_publisher"><input class="valinp" type="radio" name="authorsure_archive_link" id="archive_author_link_publisher" {$publisher} 
value="publisher" /> None - choose this option if you don't want an author to appear in the search results for category and tag archive pages. Instead the publisher logo will be displayed if you have set up one.</label>
<label for="archive_author_link_top"><input class="valinp" type="radio" name="authorsure_archive_link" id="archive_author_link_top" {$top} 
value="top" /> Top - choose this option to have the author credit appear at the top of category/tag archive pages.</label>
<label for="archive_author_link_bottom"><input class="valinp" type="radio" name="authorsure_archive_link" id="archive_author_link_bottom" {$bottom} 
value="bottom" /> Bottom - choose this option to have the author credit appear at the bottom of category/tag archive pages.</label>
</fieldset>
<div id="archive_settings">
<h4><label>Author Name Prefix:</label><input name="authorsure_archive_last_updated_by" id="authorsure_archive_last_updated_by" type="text" value="{$options['archive_last_updated_by']}" /></h4>
<p>The word or words that precede the author name on the archive pages. (e.g. "Author:")</p>
<h4><label>Primary Author:</label>{$authors}</h4>
<p>Choose the default author to be used on the category and tag archive pages. Note that you can override this setting on individual archive pages.</p>
<p>For example, John may be the primary author for the site, but Jane is the chosen author for the psychology category and Rachael for the the Chemistry category.</p>
</div>
<h4><label>Show Archive Intro?:</label><input class="valinp" type="checkbox" name="authorsure_archive_intro_enabled" id="authorsure_archive_intro_enabled" {$archive_intro_enabled} value="1" /></h4>
<p>Check the box above if you want the plugin to give you the opportunity to include a paragraph at the top of the archive page that precedes the list of posts in this archive.
Your WordPress theme may already give you this facility but if it does not then please use this option as it can help you improve your archive pages from both
human readership and SEO standpoints. After you home page, your category and tag pages can be the next most visited pages on your site so its pays dividends to put some work into them.</p>
ARCHIVE_PANEL;
	}

	static function author_panel($post, $metabox) {		
		$options = authorsure::get_options();
		$pro_options = authorsure::get_pro_options();
		$nofollow = $options['author_bio_nofollow_links'] ? ' checked="checked"' : '';
		$show_title = $options['author_show_title'] ? ' checked="checked"' : '';
		$show_avatar = $options['author_show_avatar'] ? ' checked="checked"' : '';
		$hide_labels = $options['author_profiles_no_labels'] ? ' checked="checked"' : '';
		$summary = $options['author_bio']=='summary'?' checked="checked"':'';
		$extended = $options['author_bio']=='extended'?' checked="checked"':'';
		$none = $options['author_bio']=='none'?' checked="checked"':'';
		$size_16 = $options['author_profiles_image_size']=='16'?' checked="checked"':'';
		$size_24 = $options['author_profiles_image_size']=='24'?' checked="checked"':'';
		$size_32 = $options['author_profiles_image_size']=='32'?' checked="checked"':'';
		$icon_16 = AUTHORSURE::get_icon('googleplus', '', 16 );
		$icon_24 = AUTHORSURE::get_icon('googleplus', '', 24 );
		$icon_32 = AUTHORSURE::get_icon('googleplus', '', 32 );
		$labels='';
		foreach ($pro_options as $profile => $label) {
			$labels .= '<label><span>'.$profile.':</label><input type="text" size="30" name="authorsure_'.$profile.'" value="'.$label[0].'"/><br/>';
		}
		print <<< AUTHORSURE_PAGE_PANEL
<p>These settings control how your author pages link back to Google. You may need to tweak the settings here depending on how your WordPress theme displays author pages.</p>
<h4><label>Show Title?:</label><input class="valinp" type="checkbox" name="authorsure_author_show_title" id="authorsure_author_show_title" {$show_title} value="1" /></h4>
<p>Check the box above if you want to show the author name as a title to the Author page. If your WordPress theme does this already then clear the checkbox.</p>
<h4><label>Title Text:</label><input name="authorsure_author_about" id="authorsure_author_about" type="text" size="15" value="{$options['author_about']}" /></h4>
<p>The word or words that precede the author name in the title of the Author Page. (e.g About)</p>
<h4><label>Show Avatar?:</label><input class="valinp" type="checkbox" name="authorsure_author_show_avatar" id="authorsure_author_show_avatar" {$show_avatar} value="1" /></h4>
<p>Check the box above if you want to show the avatar beside the bio on the Author page.</p>
<h4><label>Author Bio:</label></h4>
<fieldset><legend class="screen-reader-text"><span>Author Bio</span></legend>
<label for="authorsure_bio_summary"><input class="valinp" type="radio" name="authorsure_author_bio" id="authorsure_bio_summary"  {$summary} 
value="summary" /> Summary - choose this option if you want to show the standard short author bio at the top of the author page.</label>
<label for="authorsure_bio_extended"><input class="valinp" type="radio" name="authorsure_author_bio" id="authorsure_bio_extended"  {$extended} 
value="extended" /> Extended - choose this option if you want to show an extended author bio at the top of the author page.</label>
<label for="authorsure_bio_none"><input class="valinp" type="radio" name="authorsure_author_bio" id="authorsure_bio_none" {$none} 
value="none" /> None - choose this option if you do not want to show the author bio at the top of the author page.</label>
</fieldset>
<p>Choose whether you want a short, extended or no bio at the top of the author pages</p>
<h4><label>Nofollow Links In Bio?:</label><input class="valinp" type="checkbox" name="authorsure_author_bio_nofollow_links" id="authorsure_author_bio_nofollow_links" {$nofollow} value="1" /></h4>
<p>Check the box above in you want any external links that your authors enter in their bios to be rel=nofollow on their author pages.</p>
<h4><label>Contact Methods sub-title:</label><input name="authorsure_author_find_more" id="authorsure_author_find_more" type="text" size="30" value="{$options['author_find_more']}" /></h4>
<p>Enter your prefered text that precedes the list of contact methods. For example: "Find out more about me at:"</p>
<h4><label>Profile Icon Size:</label></h4>
<fieldset><legend class="screen-reader-text"><span>Icon size</span></legend>
<label for="image_size_16"><input class="valinp" type="radio" name="authorsure_author_profiles_image_size" id="image_size_16"  {$size_16} 
value="16" /> 16px {$icon_16}</label>
<label for="image_size_24"><input class="valinp" type="radio" name="authorsure_author_profiles_image_size" id="image_size_24"  {$size_24} 
value="24" /> 24px {$icon_24}</label>
<label for="image_size_32"><input class="valinp" type="radio" name="authorsure_author_profiles_image_size" id="image_size_32"  {$size_32} 
value="32" /> 32px {$icon_32}</label>
</fieldset>
<p>Choose the size of the social media icons.</p>
<h4><label>Shows Icons Only?:</label><input class="valinp" type="checkbox" name="authorsure_author_profiles_no_labels" id="authorsure_author_profiles_no_labels" {$hide_labels} value="1" /></h4>
<p>Check the box above if you want to show the social media icons on a single line.</p>
<h4>Contact Method Labels</h4>
{$labels}
<p>Enter the text you want to appear as the text on the link to the profile page. For example, "Google+", "GooglePlus", etc.</p>
<h4><label>Post List Heading:</label><input name="authorsure_author_archive_heading" id="authorsure_author_archive_heading" type="text" size="40" value="{$options['author_archive_heading']}" /></h4>
<p>The heading that precede the list of posts (or post excerpts) by the author. (e.g. Here are my most recent posts). Leave blank if you do not want a heading</p>
AUTHORSURE_PAGE_PANEL;
	}
	
	static function advanced_panel($post, $metabox) {		
		$options = authorsure::get_options();
		$hook = AUTHORSURE::get_author_page_hook();
		$hook_index = AUTHORSURE::get_author_page_hook_index();
		$hook_instances = '';
		for ($i=1; $i<11; $i++ ) {
			$checked =  $hook_index==$i ? ' selected="selected"': '';
			$hook_instances .= '<option'.$checked.' value="'.$i.'">'.$i.'</option>'; 
		}
		$filter_bio = $options['author_page_filter_bio'] ? ' checked="checked"' : '';
		print <<< AUTHORSURE_ADVANCED_PANEL
<p>Only change the settings below if Authorsure is adding the author profile in the incorrect location, such as in the header, footer or in a sidebar, 
or the profile is missing completely form the author page.  This is because your WordPress theme is doing something more on the author page than just 
dispaying the list of posts by that author. You can adjust the settings below on a trial and error basis to see if they improve the situation.</p>
<p>If your theme has its own 'hooks' then you can configure AuthorSure to use one of those hooks.</p>
<h4><label>Hook:</label><input name="authorsure_author_page_hook" id="authorsure_author_page_hook" type="text" size="15" value="{$hook}" /></h4>
<p>The name of the hook where AuthorSure runs in order to add the profile information and the link back to Google+. (e.g loop_start is the default hook)</p>
<h4><label>Hook Instance:</label><select name="authorsure_author_page_hook_index">{$hook_instances}</select></h4>
<p>Select on which instance of the hook to you want AuthorSure to run. The default is the first instance. In other words AuthorSure will run just before the 
first loop which fetches all the posts by that author. See the links in the sidebar for more information on changing these settings.</p>
<h4><label>Append Profile Links to Author Bio inserted by your theme?</label><input class="valinp" type="checkbox" name="authorsure_author_page_filter_bio" id="authorsure_author_page_filter_bio" {$filter_bio} value="1" /></h4>
<p>This is the option of last resort where your WordPress theme has an author template and is already inserting author information at the
top of the page. Your goal is to inject the profile links to Google+ etc, in the correct place so you will typically have unchecked 
"Show Title" and checked "Author Bio=none" and "Show Icons only" in the Author Page setting above.</p>
<p>Only check the box above if you have tried the various hook settings and you cannot make the profile links appear in the correct place. 
If your theme is using the WordPress function <i>get_the_author_meta</i> to fetch the bio then clicking this option will display your Google Plus and other profile links 
immediately below the bio that is inserted by the theme.</p>
<p>If this approach fails then a couple of lines of custom PHP code or a tweak to the theme author template will be required: 
though we have not come across a WordPress theme yet where we have had to resort to custom PHP code.</p>
AUTHORSURE_ADVANCED_PANEL;
	}

	static function publisher_panel($post, $metabox) {		
		$publisher = authorsure::get_publisher();
		$homepage = site_url();
		print <<<AUTHORSURE_PUBLISHER_PANEL
<h4><label>Google Page:</label>https://plus.google.com/<input name="authorsure_publisher_rel" id="authorsure_publisher_rel" type="text" value="{$publisher}" /></h4>
<p>Enter your Google Plus page ID here if your have set up a "Google+ Page" for your organization or product, and AuthorSure will put a rel="publisher" link to the specified Google+ page on your home page, {$homepage} .</p>
AUTHORSURE_PUBLISHER_PANEL;
	}	

	function help_panel() {
		$home = AUTHORSURE_HOME;
		$images = AUTHORSURE_IMAGES_URL;
		$domain = parse_url(site_url(),PHP_URL_HOST);		
		print <<< HELP_PANEL
<ul>
<li><a rel="external" href="{$home}">Plugin Home Page</a></li>
<li><a rel="external" href="{$home}category/features/">How To Use The Plugin</a></li>
<li><a rel="external" href="{$home}help/">Get Help</a></li>
<li><a rel="external" href="{$home}category/themes/">Theme Specific Setup Instructions</a></li>
<li><a rel="external" href="{$home}free-video-tutorials/">Get FREE Video Tutorials</a></li>
</ul>
<p><img src="{$images}free-video-tutorials-banner.png" alt="AuthorSure Tutorials" /></p>
<form class="signup" method="post" action="{$home}" onsubmit="return authorsure_validate_form(this)">
<fieldset>
<input type="hidden" name="form_storm" value="submit"/>
<input type="hidden" name="destination" value="authorsure"/>
<input type="hidden" name="domain" value="{$domain}" />
<label for="firstname">First Name
<input id="firstname" name="firstname" type="text" value="" /></label><br/>
<label for="email">Email
<input id="email" name="email" type="text" /></label><br/>
<label id="lsubject" for="subject">Subject
<input id="subject" name="subject" type="text" /></label>
<input type="submit" value="" />
</fieldset>
</form>
HELP_PANEL;
	}

	function google_panel() {
		$images = AUTHORSURE_IMAGES_URL;
		print <<< GOOGLE_PANEL
<ul>
<li><b>Google+ Profiles</b> provide people with an identity and presence on Google+. By linking each author to their profile on 
Google and then placing a link on the Google Profile page back to the author page, Google is able to verify the authorship of posts
and hence show a photo of the author in the search results.</li>
<li><b>Google+ Pages</b> provide businesses, products, brands, entertainment and organizations with an identity and presence on Google+. 
If you’ve created a Google+ page, Google strongly recommend linking from that page to your website and vice versa.</li>
</ul>
<ul>
<li><a rel="external" href="http://support.google.com/plus/bin/answer.py?hl=en&answer=1713824&topic=1710599&ctx=topic">Differences between Google+ Pages and Google+ Profiles</a></li>
<li><a rel="external" href="http://support.google.com/plus/bin/answer.py?hl=en&answer=1713327&topic=1710599&ctx=topic">How Google+ pages and Google+ profiles can interact with one another</a></li>
<li><a rel="external" href="http://www.google.com/webmasters/tools/richsnippets">Google Rich Snippets Testing Tool</a></li>
</ul>
GOOGLE_PANEL;
	}

	function options_panel() {
 		global $screen_layout_columns;
 		$this_url = $_SERVER['REQUEST_URI'];
?>
<div class="wrap">
    <?php screen_icon(AUTHORSURE); ?><h2>AuthorSure Options</h2>
 	<?php if (isset($_POST['options_update'])) echo self::save(); ?>
    <div id="poststuff" class="metabox-holder has-right-sidebar">
        <div id="side-info-column" class="inner-sidebar">
		<?php do_meta_boxes(self::get_screen_id(), 'side', null); ?>
        </div>
        <div id="post-body" class="has-sidebar">
            <div id="post-body-content" class="has-sidebar-content">
			<form id="authorsure_options" method="post" action="<?php echo $this_url; ?>">
			<?php do_meta_boxes(self::get_screen_id(), 'normal', null); ?>
			<p class="submit">
			<input type="submit"  class="button-primary" name="options_update" value="Save Changes" />
			<?php wp_nonce_field(AUTHORSURE_ADMIN); ?>
			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
			</p>
			</form>
 			</div>
        </div>
        <br class="clear"/>
    </div>
</div>
<?php
	}  

}
?>