<?php
/* The basic loop shortcodes for posts of all post types */
class LoopShortcodeUsers extends LoopShortcodeBase {
	
	public $shortcode = 'userloop';

	public $default_template = '<div class="user">
	{{ name }}
	{{ authorpage }}
	{{ url }}
	{{ affiliation }}
	</div>';
	
	public $default_attributes = array(
		'query' => '',
		'avatar_size' => 32,
		'nl2br' => 0,
		'texturize' => 1,
		'environment' => 'userloop_shortcode'
	);

	public function doLoop($query) {
		$loopObject = new WP_User_Query($query);
		// Check for results
		$users = $loopObject->get_results();
		if (!empty($users)) {
		    // loop trough each author
			$output = '';
		    foreach ($users as $user) {
				$data = get_userdata($user->ID);
				$twig_vars = array();
				$twig_vars['id'] = (int) $user->ID;
		        $twig_vars['username'] = $data->user_login;
				$twig_vars['nicename'] = $data->user_nicename;
				$twig_vars['email'] = $data->user_email;
				$twig_vars['url'] = $data->user_url;
				$twig_vars['joined'] = $data->user_registered;
				$twig_vars['display_name'] = $data->display_name;
				$twig_vars['posts'] = count_user_posts($twig_vars['id']);
				if($this->avatar_size > 9) {
					$twig_vars['avatar'] = get_avatar($twig_vars['id'], $this->avatar_size);
				}
				$twig_vars['authorpage'] = get_author_posts_url($twig_vars['id']);
				// Meta data
				$meta = array_filter(
							array_map(
								function($a) {
									return $a[0];
								}
								, get_user_meta($twig_vars['id'])
							)
						);
	  			$twig_vars['meta'] = $meta;
				
				// Run that data through the template
				$output .= $this->processTemplate($twig_vars);
		    }
		}
		// Return the final output!
		return $output;
	}
}