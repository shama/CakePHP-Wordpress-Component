<?php
/**
 * CAKEPHP WORDPRESS COMPONENT v0.2
 * Connects Cakephp to the Wordpress API via a service file.
 * 
 * Copyright (C) 2010 Kyle Robinson Young
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * 
 * 
 * @author Kyle Robinson Young <kyle at kyletyoung.com>
 * @copyright 2010 Kyle Robinson Young
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 0.2
 * @link http://www.kyletyoung.com/code/cakephp_wordpress_component
 *
 */
class WordpressComponent extends Object
{
    var $settings = array(    
        /**
         * SET THIS PASSWORD TO SOMETHING UNIQUE
         * THEN SET THE SAME PASSWORD IN webroot/wordpress_service.php
         * 
         */
        'PASSWORD'		    => 'ChangeThisPassword',
    
        /**
         * LEAVE BLANK TO DEFAULT TO webroot/wp/
         * OTHERWISE SPECIFY AN ABSOLUTE PATH TO YOUR WORDPRESS FOLDER
         */
        'PATH_TO_WP'	    => '',
        
        /**
         * SET TO FALSE TO DISABLE CACHING WORDPRESS RESPONSES
         */
        'CACHE'			    => true,
    
        /**
         * SET TO RELATIVE URL OF WORDPRESS SERVICE FILE
         */
        'WP_SERVICE_URL'	=> '/wordpress_service.php'
        
    );
    
    /**
     * INITIALIZE
     * @param class $controller
     * @param array $settings
     */
    function initialize(&$controller, $settings=array())
    {
        $this->settings = array_merge($this->settings, (array)$settings);
        $this->settings['PASSWORD'] = md5($this->settings['PASSWORD']);
        $this->settings['WP_SERVICE_URL'] = Router::url($this->settings['WP_SERVICE_URL'], true);
        if (empty($this->settings['PATH_TO_WP']))
        {
            $this->settings['PATH_TO_WP'] = APP.WEBROOT_DIR.DS.'wp'.DS;
        } // empty
    } // initialize
    
    /**
     * __CALL
     * Connects to the wordpress service file and calls the wordpress api.
     * 
     * @param str $func
     * @param array $args
     * @return mixed
     */
    function __call($func=null, $args=null)
    {
        // CHECK CACHE FIRST
        if ($this->settings['CACHE'])
        {
            // BUILD CACHE NAME
            $cache_name = 'wordpress_'.$func.'_';
            if (is_array($args)) $cache_name .= preg_replace("/[^A-Za-z0-9]/", "", implode('', $args));
            else $cache_name .= preg_replace("/[^A-Za-z0-9]/", "", $args);
            $cache_name = substr($cache_name, 0, 255);
            
            if (Cache::read($cache_name) !== false)
            {
                return Cache::read($cache_name);
            } // if no read
        } // if CACHE
        
        // BUILD URL
        $url = $this->settings['WP_SERVICE_URL'].'?passwd='.$this->settings['PASSWORD'];
        $url .= '&path='.urlencode($this->settings['PATH_TO_WP']);
        $url .= '&func='.urlencode($func);
        if (!empty($args))
        {
            $args = json_encode($args);
            $url .= '&args='.urlencode($args);
        } // !empty
        
        // CALL WP SERVICE
        if (function_exists('curl_init'))
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_REFERER, Router::url('/', true));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $output = curl_exec($ch);
            curl_close($ch);
        } // function_exists
        else
        {
            $output = implode('', file($url));
        } // else
        
        // JSON DECODE
        if (!empty($output))
        {
            $output = json_decode($output);
            if (empty($output->output)) return false;
            else $output = $output->output;
        } // !empty
        
        // IF CACHING
        if ($this->settings['CACHE']) Cache::write($cache_name, $output);
        else Cache::delete($cache_name);
        
        // RETURN
        return $output;
    } // call
    
} // WordpressComponent
?>