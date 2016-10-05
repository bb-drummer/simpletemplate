<?php
/**
 * simple template engine class
 *
 * @package   SimpleTemplate
 * @author    Björn Bartels <coding@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace SimpleTemplate;

/**
 * light template mechanism
 */
class SimpleTemplate extends SimpleTemplateAbstract
{

    /**
     * Constructor function
     * 
     * @param array|null $tags
     */
    public function __construct($tags = null)
    {
        parent::__construct($tags);
    } 
    
} 
