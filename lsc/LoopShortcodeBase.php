<?php
/*
	The basic class to work with Loop Shortcodes. Doesn't implement any
	logic functions. Leave that up to individual extensions of this.
*/
class LoopShortcodeBase implements LoopShortcode {
	public	$shortcode = 'loop';
	
	private $twig_environment;
			
	public	$sticky = 0,
			$sticky_posts = false,
	  		$query = '',
			$nl2br = false,
			$content = 0,
			$texturize = false,
			$thumbnail_size = 'thumbnail',
			$avatar_size = 32,
			$environment = 'loop_shortcode',
			$recall_environment = 'loop_shortcode',
			$recall_environment_type = false;
			
	// Special
	public	$template = '',
			$environments;
			
	
	// Requires a Twig Loader and Environment
	public function __construct(Twig_Environment $env) {
		if(!is_array($this->default_attributes)) {
			throw new Exception('LoopShortcode::$default_attributes must be an array of options that the shortcode can process as default attributes.');
		}
		$this->twig_environment = $env;
		$this->register();
	}
	
	// Resets all the variables for a new loop
	public function reset() {
		$sticky = 0;
		$sticky_posts = false;
  		$query = '';
		$nl2br = false;
		$content = 0;
		$texturize = false;
		$thumbnail_size = 'thumbnail';
		$avatar_size = 32;
		$template = '';
	}
	
	// Registers this class with WordPress's engine
	public function register() {
		add_shortcode($this->shortcode, array($this, 'processShortcode'));
		add_filter('no_texturize_shortcodes', array($this, 'noTexture'));
	}
	
	// Make sure WordPress doesn't texturize this shortcode
	public function noTexture($shortcodes) {
		$shortcodes[] = $this->shortcode;
		return $shortcodes;
	}
	
	// Extracts the default options and attributes
	public function extractAttributes($attributes) {
		if(!is_array($attributes)) {
			throw new Exception('LoopShortcode::extractAttributes($attributes) requires $attributes to be an array.');
		}
		$attributes = shortcode_atts($this->default_attributes, $attributes, $this->shortcode);
		foreach($attributes as $key => $value) {
			$this->$key = $value;
		}
	}

	// Cleans the template for correct processing
	public function prepareTemplate($template = null) {
		/* Because WordPress shortcodes aren't really made for Twig, we have to do some cleaning of the template so that when it does its own auto-p work, it doesn't mess up what's shown here. Also removes excess spaces for us poor designers... */
		if($template) {
			$this->template = $template;
		} else {
			$this->template = $this->default_template;
		}
		if(!$this->nl2br) {
			$this->template = str_replace(array("\r\n", "<br />\n"), "\n", $this->template);
			$template_lines = explode("\n", $this->template);
			$this->template = '';
			foreach($template_lines as $line) {
				$line = trim($line);
				if(!empty($line)) $this->template .= $line;
			}
		}
		$this->template = str_replace("\t", ' ', $this->template);
		// For some reason, WordPress sometimse texturizes and HTML entities shit for no apparent reason.
		// This replaces that with what is supposed to be there.
		$this->template = str_replace(array('&#8220;', '&#8221;'), '"' , $this->template);
		$this->template = str_replace(array('&#8216;', '&#8217;'), '\'' , $this->template);
		$this->template = preg_replace("/\s\s+/", " ", $this->template);
		
		// Finally, this tells the Twig loader to set this template as the one to use
		$this->twig_environment->getLoader()->setTemplate($this->environment, $this->template);
	}
	
	// Prepares the query for execution
	public function prepareQuery() {
		// de-funkify and parse the query
		// This allows you make date comparisons within meta values
		//	Can be used for, e.g., showing events that have already happened for a week
		//	after they have happened
		//echo $this->environment . '<br>';
		//echo 'Original Query: ' . $this->query.'<br>';
		$thetime = time();
		$this->query = str_replace('{{now}}', date('Ymd', $thetime), $this->query);
		$this->query = str_replace('{{tomorrow}}', date('Ymd', $thetime+86400), $this->query);
		$this->query = str_replace('{{yesterday}}', date('Ymd', $thetime-86400), $this->query);
		$this->query = str_replace('{{nextweek}}', date('Ymd', $thetime+604800), $this->query);
		$this->query = str_replace('{{lastweek}}', date('Ymd', $thetime-604800), $this->query);
		$this->query = str_replace('{{nextmonth}}', date('Ymd', $thetime+2592000), $this->query);
		$this->query = str_replace('{{lastmonth}}', date('Ymd', $thetime-2592000), $this->query);
		//echo 'Replacers: ' . $this->query.'<br>';
		if($this->sticky) {
			if($this->stick_posts === false) {
				$this->sticky_posts = get_option('sticky_posts');
			}
			if(count($this->sticky_posts)) {
				$this->query .= '&post__in{}=' . implode('&post__in{}=', (array) $this->sticky_posts);
			}
		}
		//echo 'Sticky: ' . $this->query.'<br>';
		// Setup the recalled environment ids
		$recalled_environments = '0';
		if($this->recall_environment) {
			if(strpos($this->recall_environment, ',') !== false) {
				$to_recall = explode(',', $this->recall_environment);
			} else {
				$to_recall = array($this->recall_environment);
			}
			foreach($to_recall as $key) {
				if(isset($this->environments[$key])) {
					$recalled_environments .= ','
							. implode(',', $this->environments[$key]);
				}
			}
		}
		$this->query = str_replace('{{environment}}', $recalled_environments, $this->query);
		if($this->recall_environment_type) {
			$environments = explode(',', $recalled_environments);
			foreach($environments as $id) {
				$this->query .= '&' . $this->recall_environment_type . '[]=' . $id;
			}
		}
		//echo 'Recalled Environments: ' . $this->query.'<br>';
		// Allows you to use URL encoding by surrounding those statements in {{stuffhere}}
		$this->query = preg_replace_callback(
							"/({{)([^}}]+)(}})/",
							function($matches) {
								return urlencode($matches[2]);
							},
							$this->query);
		//echo 'After Preg: ' . $this->query.'<br>';
		// This allows you to use brackets in your query by using curly braces instead
		$this->query = str_replace(array('{','}'),array('[',']'), $this->query);
		// This makes sure your &s don't get converted to hex, and that your query doesn't
		// get surrounded with quotes (as happens for mysterious WP reasons)
		$this->query = html_entity_decode($this->query);
		$this->query = str_replace(array('”', '″'), '', $this->query);
		//echo 'Final Query: ' . $this->query.'<hr>';
	}
	
	// This is the function that's called by WordPress when it wants this class to process a shortcode
	public function processShortcode($attributes, $template = null) {
		$this->reset();
		$this->attributes = $attributes;
		$this->extractAttributes($attributes);
		$this->prepareTemplate($template);
		$this->prepareQuery();
		$output = $this->doLoop($this->query);
		if($this->texturize) {
			$output = wptexturize($output);
		}
		return $output;
	}
	
	// This is called when we want to call up Twig to render our $twig_vars
	public function processTemplate($twig_vars) {
		// For the environmental options
		$this->environments[$this->environment][] = $twig_vars['id'];
		// Send it through Twig
		return $this->twig_environment->render($this->environment, $twig_vars);
	}
	
	// For the implementation. Does nothing by default
	public function doLoop($query) {}
}