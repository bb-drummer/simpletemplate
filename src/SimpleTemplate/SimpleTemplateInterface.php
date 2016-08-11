<?php
/**
 * simple template engine class interface
 *
 * @package     SimpleTemplate
 * @author      Björn Bartels <coding@bjoernbartels.earth>
 * @link        https://gitlab.bjoernbartels.earth/groups/zf2
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright   copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace SimpleTemplate;

/**
 * light template mechanism interface
 *
 */
interface SimpleTemplateInterface
{

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
	 * @return void
	 */
	public function set($which = 's', $needle, $replacement);

	/**
	 * Iterate internal counter by one
	 *
	 * @return void
	 */
	public function next();

	/**
	 * Reset template data
	 *
	 * @return void
	 */
	public function reset();

	/**
	 * Generate the template and
	 * print/return it. (do translations sequentially to save memory!!!)
	 *
	 * @param $template string/file Template
	 * @param $return bool Return or print template
	 *
	 * @return string complete Template string
	 */
	public function generate($template, $return = 0);

	
}
