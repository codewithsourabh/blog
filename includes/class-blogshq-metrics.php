<?php
/**
 * Metrics and performance tracking for BlogsHQ.
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/includes
 * @since      1.0.0
 */

class BlogsHQ_Metrics {
    private static $timers = array();
    
    public static function start_timer($name) {
        self::$timers[$name] = microtime(true);
    }
    
    public static function end_timer($name) {
        if (!isset(self::$timers[$name])) {
            return 0;
        }
        
        $elapsed = microtime(true) - self::$timers[$name];
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            BlogsHQ_Debug::log("Timer [{$name}]: {$elapsed}s");
        }
        
        return $elapsed;
    }
}

// Usage in TOC module:
BlogsHQ_Metrics::start_timer('toc_generation');
$toc = $this->generate_toc($post_id);
BlogsHQ_Metrics::end_timer('toc_generation');