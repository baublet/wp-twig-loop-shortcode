<?php
/*
Plugin Name:    Twig Loop Shortcode
Plugin URI:     http://www.ryanmpoe.com
Description:    A Twig-themeable shortcode wrapper for putting loops in pages and widgets
Version:        0.3
Author:         Ryan Poe
Author URI:     http://www.ryanmpoe.com
*/

// Set this to true if you need to log debug messages to the javasccript console
define('_LOOP_SHORTCODE_DEBUG', false);

require_once("twig-setup.php");

require_once("menu/LoopShortcodeTemplates.php");
require_once("lsc/LoopShortcode.php");
require_once("lsc/LoopShortcodeBase.php");
require_once("lsc/LoopShortcodePosts.php");
require_once("lsc/LoopShortcodeUsers.php");
require_once("lsc/LoopShortcodeTerms.php");
require_once("menu/LoopShortcodeMenu.php");

/**
 * Trying not to polute the global space here.
 */

$LoopShortcodeTemplates = new LoopShortcodeTemplates();
$LoopShortcodePosts = new LoopShortcodePosts($LoopShortcodeTwig, $LoopShortcodeTemplates);
$LoopShortcodeUsers = new LoopShortcodeUsers($LoopShortcodeTwig, $LoopShortcodeTemplates);
$LoopShortcodeTerms = new LoopShortcodeTerms($LoopShortcodeTwig, $LoopShortcodeTemplates);
$LoopShortcodeMenu = new LoopShortcodeMenu($LoopShortcodeTemplates);
