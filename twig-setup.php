<?php

/*
 * This file loads Twig and adds custom filters to it that interact with WordPress
 */

// Load Twig
if(!class_exists("Twig_Autoloader")) {
  require_once dirname(__FILE__) . '/Twig/lib/Twig/Autoloader.php';
  Twig_Autoloader::register();
}
// Initiate Twig by passing in an empty loader (allowing us to pass strings)
$LoopShortcodeTwigLoader = new Twig_Loader_Array(array());
$LoopShortcodeTwig = new Twig_Environment($LoopShortcodeTwigLoader);

/*******************************************************************************
 * Twig WordPress helper extensions
 ******************************************************************************/

/*
 * For word concatination. It uses a very simple algorithm that sees words as
 * spaces.
 *
 * Use:
 * 		{{ excerpt|words(50,'...') }}
 */
if(!function_exists("loop_shortcode_words")):
  function loop_shortcode_words($string, $words = 50, $append = '...', $trim_punctuation = true) {
    // Simple, quick way
    $return = implode(' ',
          array_slice(
            explode(' ', strip_tags($string)),
            0, $words)
          );
    // Remove trailing small punctuation
    if($trim_punctuation) {
      if(in_array(substr($return, -1),str_split('.,/@\\&-=+_|#$%^*;:'))) {
        $return = substr($return, 0, -1);
      }
    }
    $return .= $append;
    return $return;
  }
  $filter = new Twig_SimpleFilter('words', 'loop_shortcode_words');
  $LoopShortcodeTwig->addFilter($filter);
endif;

/*
 * For title concatenation. Returns everything before the first colon,
 * surrounded by the first and second arguments
 *
 * Use:
 * 		{{ excerpt|title('<span class="maintitle">','</span>') }}
 */

if(!function_exists("loop_shortcode_title")):
  function loop_shortcode_title($string, $prepend = '', $append = '') {
    if(strpos($string, ':') === FALSE) return $string;
    // Simple, quick way
    return	$prepend
        . trim(strstr($string, ':', true))
        . $append;
  }
  $filter = new Twig_SimpleFilter('title', 'loop_shortcode_title');
  $LoopShortcodeTwig->addFilter($filter);
endif;

/*
 * For subtitle concatenation. Returns everything after the first colon,
 * surrounded by the first and second arguments.
 *
 * Use:
 * 		{{ excerpt|subtitle('<span class="subtitle">','</span>') }}
 */

if(!function_exists("loop_shortcode_subtitle")):
  function loop_shortcode_subtitle($string, $prepend = '', $append = '') {
    if(strpos($string, ':') === FALSE) return '';
    // Simple, quick way
    return	$prepend
        . trim(substr($string, (-1 * (strlen($string) - strpos($string, ':') - 1))))
        . $append;
  }
  $filter = new Twig_SimpleFilter('subtitle', 'loop_shortcode_subtitle');
  $LoopShortcodeTwig->addFilter($filter);

  // For outputting text safe for use in title tags
  // Use: <a href="{{ link }}" title="View full post of {{ title|titlesafe }}">{{ title }}</a>
  function loop_shortcode_titlesafe($string) {
    return str_replace(array("\r\n","\r","\n"), ' ', htmlspecialchars(strip_tags($string)));
  }
  $filter = new Twig_SimpleFilter('titlesafe', 'loop_shortcode_titlesafe');
  $LoopShortcodeTwig->addFilter($filter);
endif;

/*
 * For getting author meta from an ID. For more information, see the WordPress
 * manual on get_the_author_meta.
 *
 * Use:
 * 		{{ otherauthor.id|wpauthormeta('display_name') }}
 */
if(!function_exists("loop_shortcode_wpauthormeta")):
  function loop_shortcode_wpauthormeta($id, $options = array()) {
    $id = (int) $id;
    $field = (is_array($options)) ? $options[0] : $options;
    return get_the_author_meta($field, $id);
  }
  $filter = new Twig_SimpleFilter('wpauthormeta', 'loop_shortcode_wpauthormeta');
  $LoopShortcodeTwig->addFilter($filter);

  // For getting the author posts page
  // use {{ otherauthor.id|wpgetauthorpage }}
  function loop_shortcode_wpgetauthorpage($id) {
    $id = (int) $id;
    return get_author_posts_url($id);
  }
  $filter = new Twig_SimpleFilter('wpgetauthorpage', 'loop_shortcode_wpgetauthorpage');
  $LoopShortcodeTwig->addFilter($filter);
endif;
