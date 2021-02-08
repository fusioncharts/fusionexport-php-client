<?php
// Include the library
namespace FusionExport;

require_once __DIR__ . '/../../vendor/shinsenter/defer.php/defer.php';
class MinifyConfig
{
    function minifyData($files)
    {

        print_r($files);
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
        $filef = fopen('resources'.DIRECTORY_SEPARATOR."__".$files->internalPath,"w");
        // Then get the optimized output
        fwrite($filef,$result); 


         $files->internalPath = "__".$files->internalPath;
         $files->externalPath = 'C:\xampp\htdocs\fusionexport-php-client\example\resources\__dashboard-template.html';
        
         print_r($files);
       return $files ;
    }
}
