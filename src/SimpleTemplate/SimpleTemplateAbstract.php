<?php
/**
 * simple template engine class abstract
 *
 * @package   SimpleTemplate
 * @author    Björn Bartels <coding@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace SimpleTemplate;

/**
 * light template mechanism abstract
 */
abstract class SimpleTemplateAbstract implements SimpleTemplateInterface
{

    /**
     * Needles (static)
     *
     * @var array
     */
    public $needles = array();

    /**
     * Replacements (static)
     *
     * @var array
     */
    public $replacements = array();

    /**
     * dynamicNeedles (dynamic)
     *
     * @var array
     */
    public $dynamicNeedles = array();

    /**
     * dynamicReplacements (dynamic)
     *
     * @var array
     */
    public $dynamicReplacements = array();

    /**
     * Dynamic counter
     *
     * @var int
     */
    public $dynamicContent = 0;

    /**
     * Tags array (for dynamic blocks);
     *
     * @var array
     */
    public $tags = array(
        'static' => '{%s}', 
        'start' => '<!-- BEGIN:BLOCK -->', 
        'end' => '<!-- END:BLOCK -->'
    );

    /**
     * gettext domain (default: 'default')
     *
     * @var string
     */
    public $_sDomain = "default";

    /**
     * text encoding (default: '')
     *
     * @var string
     */
    public $_encoding = "";

    /**
     * Constructor function
     * 
     * @param array|null $tags
     */
    public function __construct($tags = null)
    {
        if (is_array($tags)) {
            $this->tags = array_merge($this->tags, $tags);
        }
        
        $this->setEncoding("");
    } 
    
    /**
     * setDomain
     *
     * Sets the gettext domain to use for translations in a template
     *
     * @param  $sDomain    string    Sets the domain to use for template translations
     * @return self
     */    
    public function setDomain($sDomain)
    {
        $this->_sDomain = $sDomain;
        return $this;
    }
    
    /**
     * getDomain
     *
     * Gets the gettext domain to use for translations in a template
     *
     * @return string
     */    
    public function getDomain()
    {
        return $this->_sDomain;
    }
    
    /**
     * Set Templates placeholders and values
     *
     * With this method you can replace the placeholders
     * in the static templates with dynamic data.
     *
     * @param $which String 's' for Static or else dynamic
     * @param $needle String Placeholder
     * @param $replacement String Replacement String
     *
     * @return self
     */
    public function set($which = 's', $needle, $replacement)
    {
        if ($which == 's') { // static
            $this->needles[] = sprintf($this->tags['static'], $needle);
            $this->replacements[] = $replacement;

        } else
        { // dynamic
            $this->dynamicNeedles[$this->dynamicContent][] = sprintf($this->tags['static'], $needle);
            $this->dynamicReplacements[$this->dynamicContent][] = $replacement;

        }
        return $this;
    }

    /**
     * Sets an encoding for the template's head block.
     *
     * @param  $encoding string Encoding to set
     * @return self
     */    
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }
     
    /**
     * Iterate internal counter by one
     *
     * @return self
     */
    public function next()
    {
        $this->dynamicContent++;
        return $this;
    }

    /**
     * Reset template data
     *
     * @return self
     */
    public function reset()
    {
        $this->dynamicContent = 0;
        $this->needles = array();
        $this->replacements = array();
        $this->dynamicNeedles = array();
        $this->dynamicReplacements = array();
        return $this;
    }

    /**
     * Generate the template and print/return it.
     *
     * @param $template string/file Template
     * @param $return bool Return or print template
     *
     * @return string complete Template string
     */
    public function generate($template, $return = 0)
    {
        //check if the template is a file or a string
        if (!@ is_file($template)) {
            $content = & $template; //template is a string (it is a reference to save memory!!!)
        } else {
            $content = implode("", file($template)); //template is a file
        }

        $pieces = array();
        
        //replace i18n strings before replacing other placeholders 
        $this->replacei18n($content, "i18n"); 
        $this->replacei18n($content, "translate"); 

        //if content has dynamic blocks 
        if (preg_match("/^.*".preg_quote($this->tags['start'], "/").".*?".preg_quote($this->tags['end'], "/").".*$/s", $content)) { 
            //split everything into an array 
            preg_match_all("/^(.*)".preg_quote($this->tags['start'], "/")."(.*?)".preg_quote($this->tags['end'], "/")."(.*)$/s", $content, $pieces); 
            //safe some memory 
            array_shift($pieces); 
            $content = ""; 
            //now combine pieces together 

            //start block 
            $content .= str_replace($this->needles, $this->replacements, $pieces[0][0]); 
            unset($pieces[0][0]); 

            //generate dynamic blocks 
            for ($a = 0; $a < $this->dynamicContent; $a++) { 
                $content .= str_replace($this->dynamicNeedles[$a], $this->dynamicReplacements[$a], $pieces[1][0]); 
            } 
            unset($pieces[1][0]); 

            //end block 
            $content .= str_replace($this->needles, $this->replacements, $pieces[2][0]); 
            unset($pieces[2][0]);
        } else { 
            $content = str_replace($this->needles, $this->replacements, $content); 
        }
        
        if ($this->_encoding != "") {
            $content = '<meta http-equiv="Content-Type" content="text/html; charset='.$this->_encoding.'">'."\n".$content;
        }
          
        if ($return) {
            return $content;
        } else {
            echo $content;
        }
    }

    /** 
     * replacei18n() 
     * 
     * Replaces a named function with the translated variant 
     * 
     * @param  $template string Contents of the template to translate (it is reference to save memory!!!) 
     * @param  $functionName string Name of the translation function (e.g. i18n) 
     * @return self
     */ 
    public function replacei18n(& $template, $functionName) 
    { 

        // Be sure that php code stays unchanged 
        $php_matches = array();
        $container = array();
        if (preg_match_all('/<\?(php)?((.)|(\s))*?\?>/i', $template, $php_matches)) { 
            $x = 0; 
            foreach ($php_matches[0] as $php_match) { 
                $x++; 
                $template = str_replace($php_match, "{PHP#".$x."#PHP}", $template); 
                $container[$x] = $php_match; 
            }
        }

        // If template contains functionName + parameter store all matches 
        $matches = array();
        preg_match_all("/".preg_quote($functionName, "/")."\\(([\\\"\\'])(.*?)\\1\\)/s", $template, $matches); 

        $matches = array_values(array_unique($matches[2]));
        $matchcount = count($matches);
        for ($a = 0; $a < $matchcount; $a++) { 
            $template = preg_replace("/".preg_quote($functionName, "/")."\\([\\\"\\']".preg_quote($matches[$a], "/")."[\\\"\\']\\)/s", gettext($matches[$a]), $template);
        } 
        // , this->_sDomain
        // Change back php placeholder 
        if (is_array($container)) { 
            foreach ($container as $x => $php_match) { 
                $template = str_replace("{PHP#".$x."#PHP}", $php_match, $template); 
            } 
        }
        return $this;
    }
     
}
