<?php
// Include the library
namespace FusionExport;

require_once __DIR__ . '/../../vendor/shinsenter/defer.php/defer.php';
class MinifyConfig
{
    function minifyData($files)
    {

        $newPath = $files->externalPath;
        

        // Create a Defer object
        $defer = new \AppSeeds\Defer();

        // Library injection
        $defer->append_defer_js     = false;
        $defer->default_defer_time  = 10;

        // Add custom splash screen
        // $defer->custom_splash_screen = '<div id="splash">Loading</div>';

        // Page optimizations
        $defer->enable_preloading   = true;
        $defer->enable_dns_prefetch = true;
        $defer->fix_render_blocking = true;
        $defer->minify_output_html  = true;

        // Tag optimizations
        $defer->enable_defer_css        = true;
        $defer->enable_defer_scripts    = true;
        $defer->enable_defer_images     = true;
        $defer->enable_defer_iframes    = true;
        $defer->enable_defer_background = true;
        $defer->enable_defer_fallback   = true;

        // Web-font optimizations
        $defer->defer_web_fonts = true;

        // Image and iframe placeholders
        $defer->empty_gif               = '';
        $defer->empty_src               = '';
        $defer->use_color_placeholder   = true;
        $defer->use_css_fadein_effects  = true; // true or 'grey'

        // Blacklist
        $defer->do_not_optimize = [
            'document\.write\s*\(',
            '(jquery([-_][\d\.]+)?(\.min)?\.js|jquery-core)',
        ];

        // Then get the optimized output
        $result = $defer->fromHtml(file_get_contents($files->externalPath))->toHtml();
        //var_dump($result);
        $data_html = file_get_contents($files->externalPath);
        $files->data_html = $data_html;
        if (file_put_contents($newPath, $result) !== false) {
            echo "File created" ;
        } else {
            echo "Cannot create file (" . basename($newPath) . ")";
        }



       return $files ;
    }
}
