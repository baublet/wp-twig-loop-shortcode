<?php

/*
    The menu class that sets up our menu, displays it, and keeps the
    options, which are just custom/global templates and queries updated
*/

class LoopShortcodeMenu {

    private $templates;

    public function __construct($templates) {
        add_action('admin_menu', array($this, 'create_menu'));
        $this->templates = $templates;
        switch($_GET["lsc-action"]) {
            case "new":
                if(isset($_POST["template"]) && isset($_POST["options"]))
                    $this->templates->set($_POST["name"], $_POST["template"], $_POST["options"]);
                break;
            case "delete":
                $this->templates->delete($_GET["slug"]);
                break;
            case "update":
                if(isset($_POST["template"]) && isset($_POST["options"]))
                    $this->templates->set($_POST["name"], $_POST["template"], $_POST["options"], $_POST["slug"]);
                break;
            case "clear":
                $this->templates->clear();
                break;
        }
    }

    public function create_menu() {
        add_submenu_page(   'themes.php',
                            'Loop Shortcode Settings',
                            'Loop Shortcode',
                            'manage_options',
                            'lsc-menu',
                            array($this, 'display_options_page')
                        );
    }

    public function display_options_page() {
        ?>
        <style type="text/css">
          .helper-input {display:none !important}
          .helper-input + .postbox {display:none !important}
          .helper-input:checked + .postbox,
          .helper-input + .postbox.new {display:block !important}
          .lsc-menu {display:flex;align-items:center;}
          .template-label {
            cursor: pointer;
            padding: .5rem;
            border: 1px solid rgba(0, 0, 0, .1);
            background: rgba(0, 0, 0, .01);
            border-radius: 5px;
            margin-left: .5rem;
          }
          .template-label:hover {
            border-color: rgba(0,0,0,.5);
            background: rgba(0,0,0,.1);
          }
          #new:target {
            -webkit-animation-name: blinker;
            -webkit-animation-duration: .5s;
            -webkit-animation-timing-function: linear;
            -webkit-animation-iteration-count: 2;

            -moz-animation-name: blinker;
            -moz-animation-duration: .5s;
            -moz-animation-timing-function: linear;
            -moz-animation-iteration-count: 2;

            animation-name: blinker;
            animation-duration: .5s;
            animation-timing-function: linear;
            animation-iteration-count: 2;
          }

          @-moz-keyframes blinker {
              0% { opacity: 1.0; }
              50% { opacity: 0.0; }
              100% { opacity: 1.0; }
          }

          @-webkit-keyframes blinker {
              0% { opacity: 1.0; }
              50% { opacity: 0.0; }
              100% { opacity: 1.0; }
          }

          @keyframes blinker {
              0% { opacity: 1.0; }
              50% { opacity: 0.0; }
              100% { opacity: 1.0; }
          }
        </style>
        <div class="wrap">
            <h2>Loop Shortcodes  <a href="#new" class="page-title-action">Add New</a></h2>
            <hr>
            <div class="lsc-menu">
              <span><strong>Templates</strong> <em>(click to edit)</em><strong>:</strong></span>
              <?php foreach($this->templates->templates as $key => $template): ?>
                <label for="<?=$template["slug"]?>" class="template-label"><?=$template["name"]?></label>
              <?php endforeach; ?>
            </div>
            <hr>
            <?php foreach($this->templates->templates as $key => $template) {
                $this->display_template_form($template);
            } ?>
          <?=$this->display_template_form()?>
        </div>
        <?php
    }

    public function display_template_form($template = false) {
        if ($template == false) {
            $query = '';
            $thumbnail_size = 'thumbnail';
            $nl2br = 0;
            $texturize = 1;
            $sticky = 0;
            $environment = 'loop_shortcode';
            $recall_environment = 0;
            $recall_environment_type = 'post__not_in';
        } else {
            $query = $template['options']['query'];
            $thumbnail_size = $template['options']['thumbnail_size'];
            $nl2br = $template['options']['nl2br'];
            $texturize = $template['options']['texturize'];
            $sticky = $template['options']['sticky'];
            $environment = $template['options']['environment'];
            $recall_environment = $template['options']['recall_environment'];
            $recall_environment_type = $template['options']['recall_environment_type'];
        }
        ?>
        <?php if($template): ?>
          <input type="radio" name="lsc-selector" id="<?=$template['slug']?>" class="helper-input">
          <div class="postbox" style="margin-top:24px;" id="<?=$template['name']?>">
        <?php endif; ?>
            <?= ($template === false) ? "<h2>New Template</h2>" : "" ?>
            <div class="inside"<?=(!$template)? ' id="new"' : '' ?>>
                <form method="post" action="?page=lsc-menu&lsc-action=<?=(!$template)? 'new' : 'update' ?>">
                    <div class="options" style="width:200px;float:right;">
                        <h3>Options</h3>

                        <label for="ts">Thumbnail Size</label><br><input type="text" name="options[thumbnail_size]" id="ts" value="<?=htmlspecialchars($thumbnail_size)?>"><br>

                        <br>

                        <input type="checkbox" name="options[nl2br]" id="nl2br" value="1"<?=($nl2br)? 'checked="checked"' : ''?>> <label for="nl2br">nl2br</label><br>

                        <input type="checkbox" id="texture" name="options[texturize]" value="1"<?=($texturize)? 'checked="checked"' : ''?>> <label for="texture">Texturize</label><br>

                        <input type="checkbox" name="options[sticky]" id="stick" value="1"<?=($sticky)? 'checked="checked"' : ''?>> <label for="stick">Sticky</label><br>

                        <br>

                        <input type="checkbox" name="options[recall_environment]" id="renv" value="1"<?=($recall_environment)? 'checked="checked"' : ''?>> <label for="renv">Recall Environment?</label><br>

                        <label for="env">Environment</label><br><input type="text" name="options[environment]" id="env" value="<?=htmlspecialchars($environment)?>"><br>

                        <label for="renvt">Recollection Type</label><br><input type="text" name="options[recall_environment_type]" id="renvt" value="<?=htmlspecialchars($recall_environment_type)?>">
                    </div>
                    <div class="main" style="margin-right: 210px;">
                        <input type="text" style="width:100%;font-size:1.5rem;" name="name" placeholder="Template name, e.g. Main Site Magazine-Style Posts" value="<?=$template["name"]?>">
                        <?=($template !== false)? "<br>Slug: <strong>" . $template['slug'] . "</strong> (slugs cannot be edited)<input type=\"hidden\" name=\"slug\" value=\"".$template['slug']."\">" : ''?><br>
                        <input type="text" style="width:100%" name="options[query]" placeholder="Query, e.g. posts_per_page=3&cat=5&date_query{}{after}=1 month ago" value="<?=$template['options']['query']?>">
                        <br>
                        <textarea name="template" id="template" cols="40" style="height: 250px; width:100%" placeholder="Put your template here, with any and all HTML you want!"><?=htmlspecialchars($template["template"])?></textarea>
                        <br>
                        <?php if($template !== false) { ?>
                            <a href="?page=lsc-menu&lsc-action=delete&slug=<?=$template["slug"]?>"
                              style="float:right" onclick="return confirm('Are you sure you want to delete this shortcode template? You cannot undo this action.')">Delete</a>
                        <?php } ?>
                        <input type="submit" class="button-primary" value="<?=(!$template)? 'New Template' : 'Save Template'?>">
                    </div>
                </form>
            </div>
        <?php if($template): ?></div><?php endif; ?>
        <?php
    }
}
