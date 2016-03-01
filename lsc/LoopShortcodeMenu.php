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
                $this->templates->set($_POST["name"], $_POST["template"]);
                break;
            case "delete":
                $this->templates->delete($_GET["slug"]);
                break;
            case "update":
                $this->templates->set($_POST["slug"], $_POST["template"]);
                break;
            case "clear":
                $this->templates->clear();
                break;
        }
    }

    public function create_menu() {
        add_submenu_page(   'themes.php',
                            'Loop Shortcode Settings',
                            'Loop Shorcode',
                            'manage_options',
                            'lsc-menu',
                            array($this, 'display_options_page')
                        );
    }

    public function display_options_page() {
        ?>
        <div class="wrap">
            <h2>Loop Shortcodes</h2>
            <hr>
                <a href="#new">New Template</a> |
                <a href="?page=lsc-menu&lsc-action=clear">Clear Templates</a>
            <hr>
            <?php foreach($this->templates->templates as $key => $template) {
                $this->display_template_form($template);
            } ?>
            <?=$this->display_template_form()?>
        </div>
        <?php
    }

    public function display_template_form($template = false) {
        ?>
        <div <?=(!$template) ? 'class="postbox" style="margin-top:24px;"' : 'id="new"' ?> id="template_container<?=(!$template)? 'new' : $template['name']?>">
            <?=($template === false)? "<h2 style=\"padding:0 12px\">New Template</h2><hr>" : '' ?>
            <div class="inside">
                <form method="post" action="?page=lsc-menu&lsc-action=<?=(!$template)? 'new' : 'update' ?>">
                    <input type="text" style="width:100%" name="name" placeholder="Template name, e.g. Main Site Magazine-Style Posts" value="<?=$template["name"]?>">
                    <?=($template !== false)? "<br><strong>Slug:</strong>" . $template['slug'] . " (slugs cannot be edited)<input type=\"hidden\" name=\"slug\" value=\"".$template['slug']."\">" : ''?>
                    <br>
                    <textarea name="template" id="template" cols="40" style="height: 250px; width:100%" placeholder="Put your template here, with any and all HTML you want!"><?=htmlspecialchars($template["template"])?></textarea>
                    <br>
                    <?php if($template !== false) { ?>
                        <a href="?page=lsc-menu&lsc-action=delete&slug=<?=$template["slug"]?>" style="float:right">Delete</a>
                    <?php } ?>
                    <input type="submit" value="<?=(!$template)? 'New Template' : 'Save Template'?>">
                </form>
            </div>
        </div>
        <?php
    }
}
