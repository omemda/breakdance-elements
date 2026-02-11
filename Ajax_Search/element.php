<?php

namespace BreakdanceCustomElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

if(get_option('stormas_ajax_search_enable', 'true') == 'true'){
\Breakdance\ElementStudio\registerElementForEditing(
    "BreakdanceCustomElements\\AjaxSearch",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class AjaxSearch extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'SearchIcon';
    }

    static function tag()
    {
        return 'div';
    }

    static function tagOptions()
    {
        return [];
    }

    static function tagControlPath()
    {
        return false;
    }

    static function name()
    {
        return 'Ajax Search';
    }

    static function className()
    {
        return 'bce-woosearch';
    }

    static function category()
    {
        return 'stormas_elements';
    }

    static function badge()
    {
        return ['backgroundColor' => 'var(--purple900)', 'textColor' => 'var(--white)', 'label' => 'NEW'];
    }

    static function slug()
    {
        return __CLASS__;
    }

    static function template()
    {
        return file_get_contents(__DIR__ . '/html.twig');
    }

    static function defaultCss()
    {
        return file_get_contents(__DIR__ . '/default.css');
    }

    static function defaultProperties()
    {
        return ['content' => ['query' => ['query' => ['active' => 'custom', 'text' => 'post_type=post', 'custom' => ['source' => 'post_types', 'postsPerPage' => 8, 'conditions' => [[(object)[]]], 'totalPosts' => null, 'ignoreStickyPosts' => false, 'ignoreCurrentPost' => false, 'postTypes' => ['post'], 'orderBy' => 'date', 'order' => 'DESC', 'date' => 'all', 'beforeDate' => null, 'afterDate' => null, 'offset' => null, 'acfField' => null, 'metaboxField' => null], 'php' => 'return [\'post_type\' => \'post\'];']], 'options' => ['simulate_results_area' => false, 'placeholder' => 'Type to search...', 'show_categories' => true, 'show_excerpt' => true, 'show_featured_image' => true, 'loading_text' => 'Loading...', 'max_number_of_results_to_show' => null, 'hide_show_all_results_button' => false]], 'design' => ['design' => ['background_color' => null, 'padding' => ['padding' => ['breakpoint_base' => ['top' => null]]], 'borders' => null, 'width' => null], 'search_bar' => ['height' => null, 'typography' => ['color' => null, 'typography' => ['custom' => ['customTypography' => ['fontSize' => null]]]]], 'results' => ['background_color' => null, 'background_hover_color' => null, 'max_height' => null], 'all_results_button' => ['background_color' => null, 'background_color_hover' => null, 'typography' => null, 'borders' => ['border' => null, 'radius' => null], 'width' => null]]];
    }

    static function defaultChildren()
    {
        return false;
    }

    static function cssTemplate()
    {
        $template = file_get_contents(__DIR__ . '/css.twig');
        return $template;
    }

    static function designControls()
    {
        return [c(
        "design",
        "Design",
        [c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
        
      ), c(
        "background_color",
        "Background Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
        
      ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding",
      "padding",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     )],
        ['type' => 'section'],
        false,
        false,
        [],
        
      ), c(
        "search_bar",
        "Search Bar",
        [getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\typography",
      "Typography",
      "typography",
       ['type' => 'popout']
     ), c(
        "height",
        "Height",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
        
      ), getPresetSection(
      "EssentialElements\\typography",
      "Loading Typography",
      "loading_typography",
       ['type' => 'popout']
     )],
        ['type' => 'section'],
        false,
        false,
        [],
        
      ), c(
        "results",
        "Results",
        [c(
        "max_height",
        "Max Height",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
        
      ), getPresetSection(
      "EssentialElements\\typography",
      "Title Typography",
      "title_typography",
       ['type' => 'popout']
     ), c(
        "background_color",
        "Background Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
        
      ), c(
        "background_hover_color",
        "Background Hover Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        false,
        [],
        
      ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\typography",
      "Excerpt Typography",
      "excerpt_typography",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\borders",
      "Image Borders",
      "image_borders",
       ['type' => 'popout']
     ), c(
        "image_width",
        "Image Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
        
      )],
        ['type' => 'section'],
        false,
        false,
        [],
        
      ), c(
        "all_results_button",
        "All results Button",
        [c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        false,
        false,
        [],
        
      ), c(
        "background_color",
        "Background Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
        
      ), getPresetSection(
      "EssentialElements\\typography_with_hoverable_color",
      "Typography",
      "typography",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     )],
        ['type' => 'section'],
        false,
        false,
        [],
        
      )];
    }

    static function contentControls()
    {
        return [c(
        "query",
        "Query",
        [c(
        "query",
        "Query",
        [],
        ['type' => 'wp_query', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "options",
        "Options",
        [c(
        "max_number_of_results_to_show",
        "Max number of results to show",
        [],
        ['type' => 'number', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "simulate_results_area",
        "Simulate Results Area",
        [],
        ['type' => 'toggle', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "show_categories",
        "Show Categories",
        [],
        ['type' => 'toggle', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "show_excerpt",
        "Show Excerpt",
        [],
        ['type' => 'toggle', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "show_featured_image",
        "Show Featured Image",
        [],
        ['type' => 'toggle', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "hide_show_all_results_button",
        "Hide Show All Results Button",
        [],
        ['type' => 'toggle', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "placeholder",
        "Placeholder",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "loading_text",
        "Loading Text",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "all_categories_text",
        "All Categories Text",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "show_all_results_text",
        "Show All Results Text",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      )];
    }

    static function settingsControls()
    {
        return [];
    }

    static function dependencies()
    {
        return false;
    }

    static function settings()
    {
        return false;
    }

    static function addPanelRules()
    {
        return false;
    }

    static public function actions()
    {
        return false;
    }

    static function nestingRule()
    {
        return ['type' => 'final'];
    }

    static function spacingBars()
    {
        return false;
    }

    static function attributes()
    {
        return false;
    }

    static function experimental()
    {
        return false;
    }

    static function availableIn()
    {
        return ['breakdance'];
    }


    static function order()
    {
        return 0;
    }

    static function dynamicPropertyPaths()
    {
        return false;
    }

    static function additionalClasses()
    {
        return false;
    }

    static function projectManagement()
    {
        return false;
    }

    static function propertyPathsToWhitelistInFlatProps()
    {
        return ['content.options.show_featured_image', 'content.options.show_categories', 'content.options.show_excerpt', 'content.options.simulate_results_area'];
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return false;
    }
}
}
?>