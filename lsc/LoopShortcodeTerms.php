<?php
/* The basic loop shortcodes for posts of all post types */
class LoopShortcodeTerms extends LoopShortcodeBase {
	
	public $shortcode = 'termloop';

	public $default_template = '<span class="term"><a href="{{ link|raw }}">{{ title }}</a></span>';
	
	public $default_attributes = array(
		'query' => '',
		'nl2br' => 0,
		'texturize' => 1,
		'environment' => 'termloop_shortcode',
		'recall_environment' => false,
		'recall_environment_type' => false
	);
	
	public function parseQuery($query) {
		$elements = array();
		parse_str($query, $elements);
		// Separate the taxonomies requested from the rest of the arguments
		$taxonomies = array();
		if(isset($elements['taxonomies'])) {
			if(is_array($elements['taxonomies'])) {
				$taxonomies = $elements['taxonomies'];
			} elseif(strpos($elements['taxonomies'], ',') !== false) {
				$taxonomies = explode(',', $elements['taxonomies']);
			} else {
				$taxonomies = $elements['taxonomies'];
			}
			unset($elements['taxonomies']);
		} else {
			$taxonomies = 'category';
		}
		return array('taxonomies' => $taxonomies, 'arguments' => $elements);
	}

	public function doLoop($query) {
		// Load the query into an array
		$args = $this->parseQuery($query);

		// Load the variables in the loop
		$terms = get_terms($args['taxonomies'], $args['arguments']);
		if ($terms):
			foreach($terms as $term):
				$twig_vars = array();
				// Setup all the variables
				$twig_vars['id'] = $term->term_id;
				$twig_vars['title'] = $term->name;
				$twig_vars['name'] = $term->name;
				$twig_vars['slug'] = $term->slug;
				$twig_vars['group'] = $term->term_group;
				$twig_vars['taxonomy_id'] = $term->term_taxonomy_id;
				$twig_vars['taxonomy'] = $term->taxonomy;
				$twig_vars['description'] = $term->description;
				$twig_vars['parent'] = $term->parent;
				$twig_vars['count'] = $term->count;
				$twig_vars['link'] = get_term_link($term);
				// Load the twig template
				$output .= $this->processTemplate($twig_vars);
			endforeach;
		endif;
		
		// Return the final output!
		return $output;
	}
}