<?php
/*
Plugin Name: Network Latest Posts
Plugin URI: http://en.8elite.com/2012/02/27/network-latest-posts-wordpress-3-plugin/
Description: This plugin allows you to list the latest posts from the blogs in your network and display them in your site using shortcodes or as a widget. Based in the WPMU Recent Posts Widget by Angelo (http://bitfreedom.com/)
Version: 1.2
Author: L'Elite
Author URI: https://laelite.info/
*/
/*  Copyright 2012  L'Elite (email : opensource@laelite.info)

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
/*
Parameter explanations
$how_many: how many recent posts are being displayed
$how_long: time frame to choose recent posts from (in days), set to 0 to disable
$titleOnly: true (only title of post is displayed) OR false (title of post and name of blog are displayed)
$begin_wrap: customise the start html code to adapt to different themes
$end_wrap: customise the end html code to adapt to different themes
$blog_id: allows you to retrieve the data for a specific blog
$thumbnail: allows you to display the post's thumbnail (shortcode only)
$cpt: allows you to display a custom post type (post, page, etc) (shortcode and widget)
$ignore_blog: allows you to ignore one or various blogs using their ID numbers (shortcode and widget)

Sample call: network_latest_posts(5, 30, true, '<li>', '</li>'); >> 5 most recent entries over the past 30 days, displaying titles only

Sample Shortcode: [nlposts title='Latest Posts' number='2' days='30' titleonly=false wrapo='<div>' wrapc='</div>' blogid=null thumbnail=false cpt=post ignore_blog=null]
 * title = the section's title null by default
 * number = number of posts to display by blog 10 by default
 * days = time frame to choose recent posts from (in days) 0 by default
 * titleonly = if false it will display the title and the excerpt for each post true by default
 * wrapo = html opening tag to wrap the output (for styling purposes) null by default
 * wrapc = html closing tag to wrap the output (for styling purposes) null by default
 * blogid = the id of the blog for which you want to display the latest posts null by default
 * thumbnail = allows you to display the thumbnail (featured image) for each post it can be true or false (false by default)
 * cpt = custom post type, it allows you to display a specific post type (post, page, etc) (post by default)
 * ignore_blog = this parameter allows you to ignore one or a list of IDs separated by commas (null by default) 
*/
/*
 * cpt & ignore_blog parameters were proposed by John Hawkins (9seeds.com)
 * 
 * Thanks for the patches, I did some tweaks to your code but it's basically the same idea
 * I also could spot some bugs I didn't fix the last time I updated the plugin
 * and improved the Widget lists because the <ul></ul> tags where repeating, now it's finally fixed I think
 */
function network_latest_posts($how_many=10, $how_long=0, $titleOnly=true, $begin_wrap="\n<li>", $end_wrap="</li>", $blog_id='null', $thumbnail=false, $cpt="post", $ignore_blog='null') {
	global $wpdb;
	global $table_prefix;
	$counter = 0;
        // Custom post type
        $cpt = htmlspecialchars($cpt);
        // Ignore blog or blogs
        // if the user passes one value
        if( !preg_match("/,/",$ignore_blog) ) {
            // Always clean this stuff ;)
            $ignore_blog = htmlspecialchars($ignore_blog);
            // Check if it's numeric
            if( is_numeric($ignore_blog) ) {
                // and put the sql
                $ignore = " AND blog_id != $ignore_blog ";
            }
        // if the user passes more than one value separated by commas
        } else {
            // create an array
            $ignore_arr = explode(",",$ignore_blog);
            // and repeat the sql for each ID found
            for($z=0;$z<count($ignore_arr);$z++){
                $ignore .= " AND blog_id != $ignore_arr[$z]";
            }
        }
	// get a list of blogs in order of most recent update. show only public and nonarchived/spam/mature/deleted
	if ($how_long > 0) {
                // Select by blog id
                if( !empty($blog_id) && $blog_id != 'null' ) {
                    $blog_id = htmlspecialchars($blog_id);
                    $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE
                    public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0'
                    AND blog_id = $blog_id $ignore AND last_updated >= DATE_SUB(CURRENT_DATE(), INTERVAL $how_long DAY)
                    ORDER BY last_updated DESC");
                } else {
                    $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE
                    public = '1' $ignore AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0'
                    AND last_updated >= DATE_SUB(CURRENT_DATE(), INTERVAL $how_long DAY)
                    ORDER BY last_updated DESC");                    
                }
	} else {
                if( !empty($blog_id) && $blog_id != 'null' ) {
                    $blog_id = htmlspecialchars($blog_id);
                    $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE
			public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' AND blog_id = $blog_id
			$ignore ORDER BY last_updated DESC");
                } else {
                    $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE
			public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0'
			$ignore ORDER BY last_updated DESC");                   
                }
	}

	if ($blogs) {
                // Count how many blogs we've found
                $nblogs = count($blogs);
                // Lets dig into each blog
		foreach ($blogs as $blog) {
			// we need _posts and _options tables for this to work
                        // Get the options table for each blog
			$blogOptionsTable = $wpdb->base_prefix.$blog."_options";
                        // Get the posts table for each blog
		    	$blogPostsTable = $wpdb->base_prefix.$blog."_posts";
                        // Get the saved options
			$options = $wpdb->get_results("SELECT option_value FROM
				$blogOptionsTable WHERE option_name IN ('siteurl','blogname') 
				ORDER BY option_name DESC");
		        // we fetch the title, excerpt and ID for the latest post
			if ($how_long > 0) {
				$thispost = $wpdb->get_results("SELECT ID, post_title, post_excerpt
					FROM $blogPostsTable WHERE post_status = 'publish'
					AND ID > 1
					AND post_type = '$cpt'
					AND post_date >= DATE_SUB(CURRENT_DATE(), INTERVAL $how_long DAY)
					ORDER BY id DESC LIMIT 0,$how_many");
			} else {
				$thispost = $wpdb->get_results("SELECT ID, post_title, post_excerpt
					FROM $blogPostsTable WHERE post_status = 'publish'
					AND ID > 1
					AND post_type = '$cpt'
					ORDER BY id DESC LIMIT 0,$how_many");
			}
			// if it is found put it to the output
			if($thispost) {
                                // Remember we are doing this for multiple blogs?, well we need to display
                                // the number of posts chosen for each of them
                                for($i=0; $i < count($thispost); $i++) {
                                    // get permalink by ID.  check wp-includes/wpmu-functions.php
                                    $thispermalink = get_blog_permalink($blog, $thispost[$i]->ID);
                                    // If we want to show the excerpt, we do this
                                    if ($titleOnly == false || $titleOnly == 'false') {
                                        // Widget list
                                        if( preg_match("/\bli\b/",$begin_wrap) ) { 
                                            echo $begin_wrap.'<div class="network-posts blog-'.$blog.'"><a href="'
                                            .$thispermalink.'">'.$thispost[$i]->post_title.'</a><span class="network-posts-source">'.__('published in','trans-nlp').' <a href="'
                                            .$options[0]->option_value.'">'
                                            .$options[1]->option_value.'</a></span><p class="network-posts-excerpt">'.$thispost[$i]->post_excerpt.'</p></div>'.$end_wrap;
                                        // Shortcode
                                        } else {
                                            // Display thumbnail
                                            if( $thumbnail ) {
                                                echo $begin_wrap.'<div class="network-posts blog-'.$blog.'"><h1 class="network-posts-title"><a href="'
                                                .$thispermalink.'">'.$thispost[$i]->post_title.'</a></h1><span class="network-posts-source">'.__('published in','trans-nlp').' <a href="'
                                                .$options[0]->option_value.'">'
                                                .$options[1]->option_value.'</a></span><a href="'
                                                .$thispermalink.'">'.the_post_thumbnail_by_blog($blog,$thispost[$i]->ID).'</a> <p class="network-posts-excerpt">'.$thispost[$i]->post_excerpt.'</p></div>'.$end_wrap;
                                            // Without thumbnail
                                            } else {
                                                echo $begin_wrap.'<div class="network-posts blog-'.$blog.'"><h1 class="network-posts-title"><a href="'
                                                .$thispermalink.'">'.$thispost[$i]->post_title.'</a></h1><span class="network-posts-source">'.__('published in','trans-nlp').' <a href="'
                                                .$options[0]->option_value.'">'
                                                .$options[1]->option_value.'</a></span><p class="network-posts-excerpt">'.$thispost[$i]->post_excerpt.'</p></div>'.$end_wrap;
                                            }
                                        }
                                    // Otherwise we just show the titles (useful when used as a widget)
                                    } else {
                                        // Widget list
                                        if( preg_match("/\bli\b/",$begin_wrap) ) { 
                                            echo $begin_wrap.'<div class="network-posts blog'.$blog.'"><a href="'.$thispermalink
                                            .'">'.$thispost[$i]->post_title.'</a></div>'.$end_wrap;
                                        // Shortcode
                                        } else {
                                            echo $begin_wrap.'<div class="network-posts blog'.$blog.'"><h1 class="network-posts-title"><a href="'.$thispermalink
                                            .'">'.$thispost[$i]->post_title.'</a></h1></div>'.$end_wrap;
                                        }
                                    }
                                }
                                // Count only when all posts has been displayed
                                $counter++;
			}
			// don't go over the limit of blogs
			if($counter >= $nblogs) {
				break; 
			}
		}
	}
}

// Widget options (under the widget's section)
function network_latest_posts_control() {
        // Get the stored options
	$options = get_option('network_latest_posts_widget');
        // If we couldn't find anything, set some default values
	if (!is_array( $options )) {
		$options = array(
			'title' => __('Latest Posts','trans-nlp'),
			'number' => '10',
			'days' => '-1',
                        'titleonly' => true,
                        'blogid' => 'null',
                        'cpt' => 'post',
                        'ignore_blog' => 'null'
		);
	}
        // Save changes
	if ($_POST['network_latest_posts_submit']) {
		$options['title'] = htmlspecialchars($_POST['network_latest_posts_title']);
		$options['number'] = intval($_POST['network_latest_posts_number']);
		$options['days'] = intval($_POST['network_latest_posts_days']);
                $options['titleonly'] = htmlspecialchars($_POST['network_latest_posts_titleonly']);
                $options['blogid'] = htmlspecialchars($_POST['network_latest_posts_blogid']);
                $options['cpt'] = htmlspecialchars($_POST['network_latest_posts_custompost']);
                $options['ignore_blog'] = htmlspecialchars($_POST['network_latest_posts_ignoreblog']);
                // Update hook
		update_option("network_latest_posts_widget", $options);
	}

?>

	<p>
	<label for="network_latest_posts_title"><?php echo __('Title','trans-nlp'); ?>: </label>
	<br /><input type="text" id="network_latest_posts_title" name="network_latest_posts_title" value="<?php echo $options['title'];?>" />
	<br /><label for="network_latest_posts_number"><?php echo __('Number of posts to show','trans-nlp'); ?>: </label>
	<input type="text" size="3" id="network_latest_posts_number" name="network_latest_posts_number" value="<?php echo $options['number'];?>" />
	<br /><label for="network_latest_posts_days"><?php echo __('Number of days to limit','trans-nlp'); ?>: </label>
	<input type="text" size="3" id="network_latest_posts_days" name="network_latest_posts_days" value="<?php echo $options['days'];?>" />
        <br /><label for="network_latest_posts_titleonly"><?php echo __('Titles Only','trans-nlp'); ?>: </label>
        <select name="network_latest_posts_titleonly" id="network_latest_posts_titleonly">
            <option value="true" <?php if($options['titleonly'] == 'true'){ echo "selected='selected'"; } ?>><?php echo __('True','trans-nlp'); ?></option>
            <option value="false" <?php if($options['titleonly'] == 'false'){ echo "selected='selected'"; } ?>><?php echo __('False','trans-nlp'); ?></option>
        </select>
        <br /><label for="network_latest_posts_blogid"><?php echo __('Blog ID','trans-nlp'); ?>: </label>
        <select name="network_latest_posts_blogid" id="network_latest_posts_blogid">
            <option value="null" <?php if($options['blogid'] == 'null'){ echo "selected='selected'"; } ?>><?php echo __('Display All','trans-nlp'); ?></option>
            <?php
                global $wpdb;
                $bids = $wpdb->get_results("SELECT blog_id, domain FROM $wpdb->blogs WHERE
			public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0'
			ORDER BY last_updated DESC");
                
                foreach ($bids as $tbid) {
            ?>
            <option value="<?php echo $tbid->blog_id; ?>" <?php if($tbid->blog_id == $options['blogid']){ echo "selected='selected'"; } ?>><?php echo $tbid->domain." (ID ".$tbid->blog_id.")"; ?></option>
            <?php
                }
            ?>
        </select>
        <br /><label for="network_latest_posts_custompost"><?php echo __('Custom Post Type','trans-nlp'); ?>: </label>
        <br /><input type="text" id="network_latest_posts_custompost" name="network_latest_posts_custompost" value="<?php echo $options['cpt'];?>" />
        <br /><label for="network_latest_posts_ignoreblog"><?php echo __('Blog(s) ID(s) to Ignore Separate by Commas','trans-nlp'); ?>: </label>
        <br /><input type="text" id="network_latest_posts_ignoreblog" name="network_latest_posts_ignoreblog" value="<?php echo $options['ignore_blog'];?>" />
	<input type="hidden" id="network_latest_posts_submit" name="network_latest_posts_submit" value="1" />
	</p>

<?php
}

// Widget function
function network_latest_posts_widget($args) {
        // Get the attributes
	extract($args);
        // Look for saved options
	$options = get_option("network_latest_posts_widget");
        // If we couldn't find anything, set some default values
	if (!is_array( $options )) {
		$options = array(
			'title' => __('Latest Posts','trans-nlp'),
			'number' => '10',
			'days' => '-1',
                        'titleonly' => true,
                        'blogid' => 'null',
                        'cpt' => 'post',
                        'ignore_blog' => 'null'
		);
	}
        // Display the widget
	echo $before_widget;
	echo "$before_title $options[title] $after_title <ul>";
	network_latest_posts($options['number'],$options['days'],$options['titleonly'],"\n<li>","</li>",$options['blogid'],null,$options['cpt'],$options['ignore_blog']);
	echo "</ul>".$after_widget;
}

// Init function
function network_latest_posts_init() {
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
        // Register functions
	register_sidebar_widget(__("Network Latest Posts"),"network_latest_posts_widget");
	register_widget_control(__("Network Latest Posts"),"network_latest_posts_control");
        register_uninstall_hook(__FILE__, 'network_latest_posts_uninstall');
        load_plugin_textdomain('trans-nlp', false, basename( dirname( __FILE__ ) ) . '/languages');
}

// Shortcode function
function network_latest_posts_shortcode($atts){
    // Constructor
    extract(shortcode_atts(array(
        'title' => null,
        'number' => '10',
        'days' => '0',
        'titleonly' => true,
        'wrapo' => null,
        'wrapc' => null,
        'blogid' => 'null',
        'thumbnail' => false,
        'cpt' => 'post',
        'ignore_blog' => 'null'
    ), $atts));
    // Avoid direct output to control the display position
    ob_start();
    // Check if we have set a title
    if( !empty( $title ) ) { echo "<div class='network-latest-posts-sectitle'><h1>".$title."</h1></div>"; }
    // Get the posts
    network_latest_posts($number,$days,$titleonly,$wrapo,$wrapc,$blogid,$thumbnail,$cpt,$ignore_blog);
    $output_string=ob_get_contents();;
    ob_end_clean();
    // Put the content where we want
    return $output_string;
}
// Add the shortcode
add_shortcode('nlposts','network_latest_posts_shortcode');

// Uninstall function
function network_latest_posts_uninstall(){
    // Delete widget options
    delete_option('network_latest_posts_widget');
    // Delete the shortcode hook
    remove_shortcode('nlposts');
}
// Execute this stuff
add_action("plugins_loaded","network_latest_posts_init");

// Functions to retrieve the post's thumbnail inside WordPress Multi-site
// This awesome piece of code was written by Curtiss
// Found here: http://www.htmlcenter.com/blog/wordpress-multi-site-get-a-featured-image-from-another-blog/
// I did some tweaks in order to adapt it to this plugin
function get_the_post_thumbnail_by_blog($blog_id=NULL,$post_id=NULL,$size='thumbnail',$attrs=NULL) {
    global $current_blog;
    $sameblog = false;

    if( empty( $blog_id ) || $blog_id == $current_blog->ID ) {
            $blog_id = $current_blog->ID;
            $sameblog = true;
    }
    if( empty( $post_id ) ) {
            global $post;
            $post_id = $post->ID;
    }
    if( $sameblog )
            return get_the_post_thumbnail( $post_id, $size, $attrs );

    if( !has_post_thumbnail_by_blog($blog_id,$post_id) )
            return false;

    global $wpdb;
    $oldblog = $wpdb->set_blog_id( $blog_id );

    $blogdetails = get_blog_details( $blog_id );
    $thumbcode = str_replace( $current_blog->domain . $current_blog->path, $blogdetails->domain . $blogdetails->path, get_the_post_thumbnail( $post_id, $size, $attrs ) );

    $wpdb->set_blog_id( $oldblog );
    return $thumbcode;
}
function has_post_thumbnail_by_blog($blog_id=NULL,$post_id=NULL) {
    if( empty( $blog_id ) ) {
            global $current_blog;
            $blog_id = $current_blog;
    }
    if( empty( $post_id ) ) {
            global $post;
            $post_id = $post->ID;
    }

    global $wpdb;
    $oldblog = $wpdb->set_blog_id( $blog_id );

    $thumbid = has_post_thumbnail( $post_id );
    $wpdb->set_blog_id( $oldblog );
    return ($thumbid !== false) ? true : false;
}
function the_post_thumbnail_by_blog($blog_id=NULL,$post_id=NULL,$size='thumbnail',$attrs=NULL) {
    return get_the_post_thumbnail_by_blog($blog_id,$post_id,$size,$attrs);
}
?>