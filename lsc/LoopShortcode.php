<?php

/*
  The base interface for shortcode loopers
*/
interface LoopShortcode {
  /*
   * Registers this shortcode with WordPress
   */
  public function register();

  /*
   * Extracts the attributes passed via shortcode and attaches it to this class
   */
  public function extractAttributes($attributes);
  /*
   * Cleans up the passed template and prepares it to be run through Twig with
   * our variables passed to it.
   */
  public function prepareTemplate();
  /*
   * Prepares our query by processing the custom variables we want users to be
   * able to use in it. For more information on this and the custom variables
   * users can use in templates, see the documentation. This function, for example
   * allows users to use brackets in shortcodes by using curly braces instead.
   * We need this replacement because WordPress doesn't allow brackets within
   * shortcode parameters.
   */
  public function prepareQuery();

  /************************************************************************
   * The following three are more likely to be overwritten than the above *
   ************************************************************************/

  /*
   * Called when a shortcode is found and called to be processed by WordPress.
   */
  public function processShortcode($attributes, $template);

  /*
   * Our main function that kicks all of the processing, querying, and template
   * parsing into action. It calls processTemplate after setting up our variables.
   */
  public function doLoop($query);

  /*
   * Any time our doLoop() function has finished processing a particular post or
   * taxonomy (or whatever we can loop on in WP), call this function to pass the
   * variables to Twig. Returns a stringof the processed template.
   */
  // Call this in a loop to do everything you have processed and run $variables through Twig
  public function processTemplate($variables);
}
