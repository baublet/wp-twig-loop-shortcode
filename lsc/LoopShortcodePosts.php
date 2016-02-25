<?php
/* The basic loop shortcodes for posts of all post types */
class LoopShortcodePosts extends LoopShortcodeBase {

	public $default_template = '<article class=">
		<h4><a href="{{ link }}" class="title" title="{{ excerpt|striptags|words(50) }}">{{ title|raw }}</a></h4>
		<p>{{ excerpt|raw }}</p>
		<div class="meta">
			<span class="author">By <a href="{{ author.page }}">{{ author.display_name }}</a><span>
			<span class="date"><span title="{{ date }}">{{ ago }}</span> ago</span>
			<span class="comments"><span>{{ comments }}</span> {{ comments == 1 ? \'comment\' : \'comments\' }}</span>
		</div>
	</article>';
	
	public $default_attributes = array(
		'query' => '',
		'thumbnail_size' => 'thumbnail',
		'content' => 0,
		'nl2br' => 0,
		'texturize' => 1,
		'environment' => 'loop_shortcode',
		'sticky' => 0,
		'recall_environment' => false,
		'recall_environment_type' => false
	);

	public function doLoop($query) {
		// Query the new posts
		$loopObject = new WP_Query($query);
		
		//if($this->environment == 'main-events') print_r($loopObject);

		$output = '';

		// Load the variables in the loop
		if ($loopObject->have_posts()):
			foreach($loopObject->posts as $post):
				$twig_vars = array();
				// Setup all the variables
				$twig_vars['query'] = $query;
				$twig_vars['id'] = $post->ID;
				$twig_vars['title'] = $post->post_title;
				$twig_vars['link'] = get_permalink($twig_vars['id']);
				$twig_vars['date'] = $post->post_date;
				$twig_vars['time'] = mysql2date('U', $twig_vars['date']);
				$twig_vars['age'] = abs(time() - $twig_vars['time']);
				$twig_vars['ago'] = human_time_diff($twig_vars['time'], time());
				$twig_vars['modified'] = $post->post_modified;
				$twig_vars['modified_time'] = mysql2date('U', $twig_vars['modified']);
				$twig_vars['modified_age'] = abs(time() - $twig_vars['modified_time']);
				$twig_vars['modified_ago'] = human_time_diff($twig_vars['modified_time'], time());
				if($load_content) {
					$twig_vars['content'] = $post->post_content;
					$twig_vars['content'] = apply_filters('the_content', $twig_vars['content']);
					$twig_vars['excerpt'] = FALSE;
				} else {
					$twig_vars['content'] = FALSE;
					$twig_vars['excerpt'] = get_extended($post->post_content);
					$twig_vars['excerpt'] = strip_shortcodes($twig_vars['excerpt']['main']);
				}
				$twig_vars['post_type'] = $post->post_type;
				$twig_vars['comments'] = $post->comment_count;
				$twig_vars['comments_s'] = ($twig_vars['comments'] == 1) ? '' : 's';
				
				// Author information
				$twig_vars['author']['id'] = $post->post_author;
				$twig_vars['author']['username'] = get_the_author_meta('user_login', $twig_vars['author']['id']);
				$twig_vars['author']['display_name'] = get_the_author_meta('display_name', $twig_vars['author']['id']);
				$twig_vars['author']['page'] = get_author_posts_url($twig_vars['author']['id']);
				$twig_vars['author']['link'] = get_the_author_meta('user_url', $twig_vars['author']['id']);
				$twig_vars['author']['email'] = get_the_author_meta('user_email', $twig_vars['author']['id']);
				
				// Thumbnail details
				$twig_vars['thumb'] = wp_get_attachment_image_src(get_post_thumbnail_id($twig_vars['id']), $this->thumbnail_size);
				// Setup the thumbnail full image
				$twig_vars['thumbnail'] = get_the_post_thumbnail($post->ID, $this->thumbnail_size);

				// Setup post classes
				$twig_vars['post_class'] = '';
				$classes = get_post_class();
				foreach ($classes as $classname) {
					$twig_vars['post_class'] .= $classname . ' ';
				}

				// Setup categories
				$twig_vars['categories'] = array();
				// For individual category styling
				$twig_vars['category_ids'] = array();
				$categories = get_the_category($twig_vars['id']);
				if($categories){
					foreach($categories as $category) {
						$buffer = array();
						$buffer['link'] = get_category_link($category->term_id);
						$buffer['name'] = $category->name;
						$buffer['id'] = $category->term_id;
						$twig_vars['category_ids'][] = $category->term_id;
						$twig_vars['categories'][] = $buffer;
					}
				}
				
				// Setup the custom fields
				$twig_vars['custom'] = array();
				$custom_buffer = get_post_meta($post->ID);
				foreach($custom_buffer as $key => $custom) {
					// Don't include internal meta values
					if(substr($key, 0, 1) == '_') continue;
					// This makes it so you don't have to use {{ custom.myfield.0 }} for keys that only ever have one value
					if(is_array($custom)) {
						if(count($custom) > 1) {
							$twig_vars['custom'][$key] = $custom;
						} else {
							$twig_vars['custom'][$key] = $custom[0];
						}
					} else {
						$twig_vars['custom'][$key] = $custom;
					}
				}

				// Load the twig template
				$output .= $this->processTemplate($twig_vars);
			endforeach;
		endif;
		
		// Return the final output!
		return $output;
	}
}