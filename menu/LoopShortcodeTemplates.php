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

    public function set($template, $content, $options, $slug = null) {
        $slug  = ($slug == null) ? $this->generate_slug($template) : $slug;
        $this->templates[$slug] = array(
                'slug' => $slug,
                'name' => $template,
                'template' => $content
        );
        $query = $options['query'];
        $thumbnail_size = $options['thumbnail_size'];
        $nl2br = $options['nl2br'];
        $texturize = $options['texturize'];
        $sticky = $options['sticky'];
        $environment = $options['environment'];
        $recall_environment = $options['recall_environment'];
        $recall_environment_type = $options['recall_environment_type'];
        $this->templates[$slug]['options'] =
            array(
                'query' => $query,
                'thumbnail_size' => $thumbnail_size,
                'nl2br' => $nl2br,
                'texturize' => $texturize,
                'sticky' => $sticky,
                'environment' => $environment,
                'recall_environment' => $recall_environment,
                'recall_environment_type' => $recall_environment_type
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
