<?php
/*  Copyright 2012-2014  K.M. Hansen  (email : software@kmhcreative.com)

	This adds a custom taxonomy called "PR Categories" to each of the post types and
	also adds the regular post "tags" option to Press, News, and Events post-types.
	
	Get PR Shortcode:
	
	[getpr type="press-release" totalposts="3" category="interview" thumbnail="true" content="full" meta="true" ]
	
	Inserts a list of the Press, News & Events posts into another page or post.  Parameters:
		type 		= 	press-release | news | event (if omitted shows all three)
		class_name	=	optional class to apply to post
		totalposts	=	number of posts to display (omit to show all of them)
		category	=	comma-separated list of PR Category slugs to show (omit to show from all)
		thumbnail	= 	true | false (show post thumbnail or not?)
		content		=	link 	| (title as link)
						excerpt | (standard wp excerpt text)
						full	| (full post content)
						140		| (character limit of excerpt, no title or link)
		meta		=	true | false (display meta information too?)
		date		=	true | false (display date under title?)
		orderby		=	any legal orderby parameter (default is 'post_date')
		
	PR Filter Links Shortcode (requires WordPress 3.1 or later):
	
	[pr_filter type="news" category="radio,television,print" advanced="false"]
	
	By default it shows a horizontal list of links that let a user filter PR posts by a specific category.
	
		type		= (optional) limit results returned to ONE of the post-types (press-release, news, event)
		category	= (optional) comma-separated list of specific PR Category slugs to show, omit to show all of them.
		exclude		= (optional) comma-separated list of specific PR Category slugs to exclude (over-rides category)
					  if you include "children" in the list only top-level PR categories are displayed.
		advanced	= (optional) 	"false" - shows links to filter by one PR category
									"true"	- shows advanced search with checkboxes
									"expand"- advanced search that expands/collpases on click
		
*/

// Add PR Categories to Press, News & Events
add_action( 'init', 'add_pr_cats',99 );
function add_pr_cats() {
	// Check if Press News & Events Plugin has activated
	if (class_exists('Press_News_Events')) {
		// Register PR Categories taxonomy to press-release post-type
		register_taxonomy('pr_category', array('press-release','news','event'),
			array(
			// Hierarchical taxonomy (like categories)
			'hierarchical' => true,
			'show_admin_column' => true,
			// This array of options controls the labels displayed in the WordPress Admin UI
			'labels' => array(
				'name' => _x( 'PR Categories', 'taxonomy general name' ),
				'singular_name' => _x( 'PR Category', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search PR Catagories' ),
				'all_items' => __( 'All PR Categories' ),
				'parent_item' => __( 'Parent PR Category' ),
				'parent_item_colon' => __( 'Parent PR Category:' ),
				'edit_item' => __( 'Edit PR Category' ),
				'update_item' => __( 'Update PR Category' ),
				'add_new_item' => __( 'Add New PR Category' ),
				'new_item_name' => __( 'New PR Category Name' ),
				'menu_name' => __( 'PR Categories' )
			),
			// Control the slugs used for this taxonomy
			'rewrite' => array(
				'slug' => 'pr', // This controls the base slug that will display before each term
				'with_front' => false, // Don't display the category base before
				'hierarchical' => true 
			),
		));
		// Now nail it to the Event and News post-types too
		register_taxonomy_for_object_type('pr_category', 'event');
		register_taxonomy_for_object_type('pr_category', 'news');
		// Regular Post-tags don't actually work for search/archive - disabled for now
/*		register_taxonomy_for_object_type('post_tag', 'press-release');
		register_taxonomy_for_object_type('post_tag', 'event');
		register_taxonomy_for_object_type('post_tag', 'news');	
*/				
		add_post_type_support('press-release','author');
		add_post_type_support('news','author');
		add_post_type_support('event','author');	
		
	/*
		[getpr totalposts="3" category="interview" thumbnail="true" content="full" meta="true" ]
	*/		
	// Taxonomy category shortcode
	function pr_cat_func($atts) {
		extract(shortcode_atts(array(
				'type'			=> '',
				'class_name'    => 'cat-post',
				'totalposts'    => '-1',
				'category'      => '',
				'thumbnail'     => 'false',
				'content'		=> 'excerpt',
				'meta'			=> 'false',
				'date'			=> 'false',
				'orderby'       => 'post_date'
				), $atts));

		$output = '<div class="'.$class_name.'">';
		global $post;
		if (!in_array($content, array('full','excerpt','link')) && !is_numeric($content)) {
			$content = 'excerpt';
		}
		if ($type == '' || $type == null && ($type != 'press-release' || $type != 'event' || $type != 'news') ) {
			$type = array('press-release','event','news');
		} else {
			$type = array($type);
		}
		if ($category == '' || $category == null) {
			$prcats = get_terms('pr_category');
			$allcats = array();
			$i = 1;
			foreach($prcats as $term) {
				array_push($allcats,$term->slug);
			}
			$category = $allcats;
		} else {
			$category = explode(',',$category); // convert string to array
		}
		$args = array(
			'posts_per_page' => $totalposts, 
			'orderby' => $orderby,
			'post_type' => $type,
			'tax_query' => array(
				array(
					'taxonomy' => 'pr_category',
					'field' => 'slug',
					'terms' => $category
				)
			));
		$myposts = NEW WP_Query($args);

		while($myposts->have_posts()) {
			$myposts->the_post();
			$output .= '<div class="cat-post-list">';
			if($thumbnail == 'true') {
			$output .= '<div class="cat-post-images">'.get_the_post_thumbnail($post->ID, 'thumbnail').'</div>';
			}
			if (in_array($content, array('full','excerpt','link')) && !is_numeric($content)) {

			
			if ($post->post_type == 'event') {
				$event_ends = get_post_meta($post->ID, '_ends',true);
				if (current_time('timestamp') - 43200 > $event_ends) {
					$event_over = ' event-over';
					$event_ended = ' <strong>This event has ended</strong><br/>';
				}
			} else {
					$event_over = '';
					$event_ended = '';
				}

			
			$output .= '<div class="cat-content"><strong><span class="cat-post-title'.$event_over.'"><a href="'.get_permalink().'">'.get_the_title().'</a></span></strong>';
				if ($content == 'full' || $content == 'excerpt') {
					$output .= '<br/>'.$event_ended;
				} else {
				$output .= ' ';
				}
			} else { $output .= '<div class="cat-content">'; }
			if ($date == 'true') {
			$output .= '<small>Posted: '.get_the_date().'</small><br/>';
			}
			if ($content == 'excerpt') {
				$output .= '<span class="cat-post-excerpt">'.get_the_excerpt().'</span>';
			}
			if ($content == 'full') {
				$output .= '<span class="cat-post-content">'.get_the_content().'</span>';
			}
			if (!in_array($content, array('full','excerpt','link'))) { // must be excerpt with limit
				$output .= '<span class="cat-post-excerpt">'.substr(get_the_excerpt(), 0,(1*$content)).'... <a href="'.get_permalink().'">read more</a></span>';
			}
			if ($meta == 'true' && ($content == 'full' || $content == 'excerpt') ) {
				$output .= '<br/><small class="metadata">Filed under: ';
				$taxonomies = get_object_taxonomies($post->post_type);
				foreach($taxonomies as $taxonomy){
					$terms = get_the_terms( $post->ID, $taxonomy);
					$i = 1;
					if (!empty( $terms )) {
						foreach($terms as $term) {
							$output .= '<a href="'.get_term_link( $term->slug, $taxonomy ).'">'.ucfirst($term->name).'</a>';
							if (count($terms) > 0 && $i < count($terms)) {
							$output .= ', ';
							}
							$i++;
						}
					}
				}
				$output .= ' | ';
				if (comments_open($post->ID)){
				$output .= '<a href="'.get_comments_link($post->ID).'">Comments('.get_comments_number($post->ID).')</a>';
				} else {
				$output .= 'Commenting disabled';
				}
				$output .= '</small>';
			}			
			$output .= '</div><br/>
				<div class="cat-clear"></div>
			</div>';
		};
		$output .= '</div>';
		wp_reset_query();
		return $output;
	}
	add_shortcode('getpr', 'pr_cat_func');	
	
	// Utility function to check if term is child
	function prcat_is_child($term) {
		$check = get_ancestors($term->term_id,'pr_category');
		if (!empty($check)) {
			return true;
		} else {
			return false;
		}
	}
	
	/* 	register styles and script for advanced search, but we enqueue within the
		shortcode itself to prevent adding them when no shortcode is in use
		but you can only do that in WP >= 3.3!
	*/
	function pnex_styles() {
		wp_register_style('pnex_css' , plugins_url('css/pnex.css', __FILE__) );
		wp_register_script('pnex_js' , plugins_url('js/pnex.js', __FILE__), array( 'jquery' ) );
	};
	add_action('wp_enqueue_scripts','pnex_styles');

	function pr_cat_filter($atts) {
		extract(shortcode_atts(array(
				'type'		=>	'',
				'category'  => 	'',
				'exclude'	=>	'',
				'advanced' 	=> 	false
				), $atts));
		$allcats = get_terms('pr_category');
		if ($category == '' || $category == null) {
			$category = array();
			foreach($allcats as $cat) {
				array_push($category,$cat->slug);
			}
		} else {
			$category = explode(',',$category); // convert string to array
		}
		if ($exclude != '' && $exclude != null) {
			$exclude = explode(',',$exclude);
			if (in_array('children',$exclude)) {
				foreach ($allcats as $cat) {
					if (prcat_is_child($cat)) {	// term is child, add to excluded
						array_push($exclude, $cat->slug);
					}
				}
			}
		} else {
			$exclude = array();
		}
	if ($advanced == false) {
		if ($type == 'press-release' || $type == 'event' || $type == 'news') {
			$obj = get_post_type_object($type);
			$label = $obj->labels->search_items;
			$type = '?post_type='.$type;
		} else { 
			$label = 'Show all';
		}
		$output = '<p class="pnex_filter">'.$label.' by:&nbsp;&nbsp;';
		$i = 1;
		foreach ($allcats as $prcat) {
			if(in_array($prcat->slug,$category) && !in_array($prcat->slug,$exclude) ) {
			$output .= '<a href="'.get_term_link( $prcat->slug, 'pr_category' ).''.$type.'">'.ucfirst($prcat->name).'</a>';
			if (count($category) > 1 && $i < count($category) ) {
			$output .=' | ';
			}
			$i++;
			} else { $i++;}
		}
		$output .= '</p>';
	} else {
		// Advanced Search //
		if ($advanced == 'expand') {
			$expand = true;
			wp_enqueue_script('pnex_js');	// can only do this here since WP 3.3
		} else { 
			$expand = false;
		};
		wp_enqueue_style('pnex_css');	// can only do this here since WP 3.3
		$types = array('press-release','event','news');
		if ( in_array($type,$types) ) {
			$obj = get_post_type_object($type);
			$label = $obj->labels->search_items;
			$label_class = ' class="hide" ';
			$input_type = 'hidden';
			$before = 'Advanced '.$obj->labels->name.' Search';
			$after = '';
		} else { 
			$label = 'Search';
			$label_class = '';
			$input_type = 'checkbox';
			$before = 'Advanced Search';
			$after = '<br/>';
		}
		$output .= '<div class="pnex_adv_search">';
		if ($expand == 'true') {
			$output .= '<a class="control" href="javascript:void(0);" onclick="pnex_adv_toggle(this);"><strong>'.$before.'</strong></a><br/>';
			$output .= '<form method="post" class="expand">';
		} else {
			$output .= '<strong>'.$before.'</strong><br/>';
			$output .= '<form method="post">';
		}	
		foreach($types as $pr_type) {
			$obj = get_post_type_object($pr_type);
			if ($input_type == 'hidden') {
				if ($type == $pr_type) {
					$value = $type;
				} else { 
					$value = ''; 
				};
				$check = '';
				$box_label = '';
			} else {
				$value = $pr_type;
				$check = 'checked';	// assume search all post_types
				$box_label = $obj->labels->name;
			};
			$output .= '<label for="pnex_type_'.$pr_type.'"'.$label_class.'><input type="'.$input_type.'" name="pnex_type_'.$pr_type.'" value="'.$value.'" '.$check.'/>'.$box_label.'</label> ';
		}
		$output .= $after;
		foreach ($allcats as $prcat) {
			if(in_array($prcat->slug,$category) && !in_array($prcat->slug,$exclude) ){			
			$output .= '<label for="pnex_'.$prcat->slug.'"><input type="checkbox" name="pnex_'.$prcat->slug.'" value="'.$prcat->slug.'"/>'.ucfirst($prcat->name).'</label> ';
			}
		};
		$output .= '<div class="submit_box"> <em>match</em> ';
		$output .= '<input type="radio" name="pnex_search_bool" value="0" checked/>all ';
		$output .= '<input type="radio" name="pnex_search_bool" value="1" /> any ';	
		$output .= '<input name="pnex_submit" type="submit" value="'.$label.'"/></div>';
	
	
		
		$output .= '</form></div>';		
	}	
		return $output;
	}
	add_shortcode('pr_filter', 'pr_cat_filter');
} else {
	/* 	Uncomment lines below to display messages on the front-end of the site
		These will only run if the Press-News-Events plugin is not activated.
	*/
	function pnex_hide_getpr_shortcode() {
		$output = '';
//		$output = '<p>Sorry, this content could not be displayed.  Please check again later.</p>';
		return $output;
	}
	function pnex_hide_pr_filter_shortcode() {
		$output = '';
//		$output = '<p>Sort options are temporarily unavailable.</p>';
		return $output;
	}
	add_shortcode('getpr', 'pnex_hide_getpr_shortcode');
	add_shortcode('pr_filter', 'pnex_hide_pr_filter_shortcode');
}

// Function for Advanced PNEX Search //
function pnex_search() {
	$category = get_terms('pr_category');
	$url = get_site_url().'/pr/';
	$types = array('press-release','news','event');
	// ANY or ALL terms? //
	if ($_POST['pnex_search_bool']=='1') {
		$x = ',';
	} else {
		$x = '+';
	}
	$selected = array();
	foreach($category as $prcat) {
		if (isset($_POST['pnex_'.$prcat->slug])) {
		array_push($selected,$prcat->slug);
		}
	}
	$i = 1;
	foreach($selected as $item) {
		$url .= $item;
		if (count($selected) > 1 && $i < count($selected) ) {
			$url .= $x;
		}
		$i++;
	}
	$url .= '/';
	// Now see which post-types to search
	$pr_types = array();
	foreach($types as $type) {
		if (isset($_POST['pnex_type_'.$type]) && $_POST['pnex_type_'.$type] == $type ) {
			array_push($pr_types, $type);
		}
	};

	$i = 1;
	if (count($pr_types) > 0) {
		$url .= '?post_type=';
	}
	foreach($pr_types as $type) {
		$url .= $type;
		if (count($pr_types) > 1 && $i < count($pr_types) ) {
			$url .= '+';
		}
		$i++;
	}
	wp_redirect( $url ); exit;
}

if (isset($_POST['pnex_submit'])) {
	pnex_search();
}

}
?>