<?php
/*
Plugin Name:    Loop Shortcode
Plugin URI:     http://www.ryanmpoe.com
Description:    A Twig-themeable shortcode wrapper for putting loops in pages and widgets
Version:        0.1
Author:         Ryan Poe
Author URI:     http://www.ryanmpoe.com
*/ 


define('_LOOP_SHORTCODE_DEBUG', false);
require_once("twig-setup.php");
require_once("lsc/LoopShortcode.php");
require_once("lsc/LoopShortcodeBase.php");
require_once("lsc/LoopShortcodePosts.php");
require_once("lsc/LoopShortcodeUsers.php");
require_once("lsc/LoopShortcodeTerms.php");

$LoopShortcodePosts = new LoopShortcodePosts($loop_shortcode_twig);
$LoopShortcodeUsers = new LoopShortcodeUsers($loop_shortcode_twig);
$LoopShortcodeUsers = new LoopShortcodeTerms($loop_shortcode_twig);