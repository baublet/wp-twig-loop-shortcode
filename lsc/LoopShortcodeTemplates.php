<?php

/*
    Allows us to call and manipulate our custom templates.
*/

class LoopShortcodeTemplates {
    public $templates,
            $option_name = 'lsc_templates';

    public function __construct() {
        $this->templates = array();
        if(get_option($this->option_name) === false) {
            add_option($this->option_name, $this->templates);
        } else {
            $this->templates = get_option($this->option_name);
        }
    }

    public function get($template) {
        if (count($this->templates) && isset($this->templates[$template])) {
                return $this->templates[$template];
        } else {
            return "<strong>Loop Shorcode Error:</strong> template <em>" . $template . "</em> not set.";
        }
    }

    public function set($template, $content) {
        $slug  = $this->generate_slug($template);
        $this->templates[$slug] = array(
                'slug' => $slug,
                'name' => $template,
                'template' => $content
        );
        $this->save();
    }

    public function delete($template) {
        unset($this->templates[$template]);
        $this->save();
    }

    public function clear() {
        $this->templates = array();
        $this->save();
    }

    private function save() {
        update_option($this->option_name, $this->templates);
    }

    private function generate_slug($template_name) {
        $slug = sanitize_title($template_name, false);
        if(!$slug) {
            do {
                $slug = 'lsc-' . rand(1,999);
            } while(isset($this->templates[$slug]));
        }
        return $slug;
    }
}
