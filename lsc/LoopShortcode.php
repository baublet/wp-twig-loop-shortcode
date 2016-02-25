<?php

/*
	The base interface for shortcode loopers
*/
interface LoopShortcode {
	public function register();
	public function extractAttributes($attributes);
	public function prepareTemplate();
	public function prepareQuery();
	
	// Called when a shortcode is found and called to be processed.
	public function processShortcode($attributes, $template);
	// This method does your loop, which then calls processTemplate after setting up variables
	public function doLoop($query);
	// Call this in a loop to do everything you have processed and run $variables through Twig
	public function processTemplate($variables);
}