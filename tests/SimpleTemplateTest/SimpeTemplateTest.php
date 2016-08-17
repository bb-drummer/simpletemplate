<?php
/**
 * simple template class tests
 *
 * @package     SimpleTemplate
 * @author      Björn Bartels <coding@bjoernbartels.earth>
 * @link        https://gitlab.bjoernbartels.earth/groups/zf2
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright   copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace SimpleTemplateTest;

use PHPUnit_Framework_TestCase as TestCase;

use SimpleTemplate\SimpleTemplate;

class SimpleTemplateTest extends TestCase
{
    public function testInstantiateObject()
    {
    	try {
    		$tpl = new SimpleTemplate();
    		$className = get_class($tpl);
    	} catch (Exception $e) {
    		$tpl = null;
    		$className = null;
    	}

    	$this->assertNotNull($tpl);
    	$this->assertNotNull($className);

    	$this->assertInstanceOf("SimpleTemplate\SimpleTemplateAbstract", $tpl);
    	$this->assertInstanceOf("SimpleTemplate\SimpleTemplateInterface", $tpl);
    }

    public function testInstantiateObjectSetStaticTag()
    {
    	try {
    		$tpl = new SimpleTemplate(array("static" => "$[%s]"));
    	} catch (Exception $e) {
    		$tpl = null;
    	}

    	$this->assertNotNull($tpl);
    	$this->assertEquals("$[%s]", $tpl->tags["static"]);
    }

    public function testInstantiateObjectSetBlockTags()
    {
    	try {
    		$tpl = new SimpleTemplate(array("start" => "<templateblock:begin>", "end" => "<templateblock:end>"));
    	} catch (Exception $e) {
    		$tpl = null;
    	}

    	$this->assertNotNull($tpl);
    	$this->assertEquals("<templateblock:begin>", $tpl->tags["start"]);
    	$this->assertEquals("<templateblock:end>", $tpl->tags["end"]);
    }

    public function testSetGettextDomain()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->setDomain("my-translations");
    	 
    	$this->assertEquals("my-translations", $tpl->_sDomain);
    }

    public function testGetGettextDomain()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->setDomain("my-translations");
    	
    	$this->assertEquals("my-translations", $tpl->getDomain());
    }

    public function testSetStaticReplacement()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("s", "MY-VAR", "my-value");

    	$this->assertContains("{MY-VAR}", $tpl->needles);
    	$this->assertContains("my-value", $tpl->replacements);
    	$this->assertEquals("{MY-VAR}", $tpl->needles[0]);
    	$this->assertEquals("my-value", $tpl->replacements[0]);

    	$tpl->set("s", "MY-OTHER-VAR", "my-other-value");
    	
    	$this->assertContains("{MY-OTHER-VAR}", $tpl->needles);
    	$this->assertContains("my-other-value", $tpl->replacements);
    }
    
    public function testNextIncreasesInternalDynamicReplacementsCounter()
    {
    	$tpl = new SimpleTemplate();
    	$this->assertEquals(0, $tpl->dynamicContent);
    	
    	$tpl->next();
    	$this->assertEquals(1, $tpl->dynamicContent);
    }

    public function testSetFirstDynamicReplacementsCycle()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("d", "MY-VAR", "value-1");
    	
    	$this->assertContains("{MY-VAR}", $tpl->dynamicNeedles[$tpl->dynamicContent]);
    	$this->assertContains("value-1", $tpl->dynamicReplacements[$tpl->dynamicContent]);
    	$this->assertEquals("{MY-VAR}", $tpl->dynamicNeedles[$tpl->dynamicContent][0]);
    	$this->assertEquals("value-1", $tpl->dynamicReplacements[$tpl->dynamicContent][0]);

    	$tpl->set("d", "MY-OTHER-VAR", "other-value-1");
    	 
    	$this->assertContains("{MY-OTHER-VAR}", $tpl->dynamicNeedles[$tpl->dynamicContent]);
    	$this->assertContains("other-value-1", $tpl->dynamicReplacements[$tpl->dynamicContent]);
    	$this->assertEquals("{MY-OTHER-VAR}", $tpl->dynamicNeedles[$tpl->dynamicContent][1]);
    	$this->assertEquals("other-value-1", $tpl->dynamicReplacements[$tpl->dynamicContent][1]);
    }

    public function testSetFirstAndNextDynamicReplacementsCycle()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("d", "MY-VAR", "value-1");
    	
    	$this->assertContains("{MY-VAR}", $tpl->dynamicNeedles[$tpl->dynamicContent]);
    	$this->assertContains("value-1", $tpl->dynamicReplacements[$tpl->dynamicContent]);
    	$this->assertEquals("{MY-VAR}", $tpl->dynamicNeedles[$tpl->dynamicContent][0]);
    	$this->assertEquals("value-1", $tpl->dynamicReplacements[$tpl->dynamicContent][0]);
    	
    	$tpl->next();
    	$tpl->set("d", "MY-VAR", "value-2");
    	
    	$this->assertContains("{MY-VAR}", $tpl->dynamicNeedles[$tpl->dynamicContent]);
    	$this->assertEquals("{MY-VAR}", $tpl->dynamicNeedles[$tpl->dynamicContent][0]);
    	$this->assertContains("value-2", $tpl->dynamicReplacements[$tpl->dynamicContent]);
    	$this->assertEquals("value-2", $tpl->dynamicReplacements[$tpl->dynamicContent][0]);
    }

    public function testResetReplacements()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("s", "MY-VAR", "my-value");
    	$tpl->set("d", "MY-VAR", "value-1")
    	    ->next()
    	    ->set("d", "MY-VAR", "value-2")
    	;
    	
    	$tpl->reset();
    	$this->assertEquals(0, $tpl->dynamicContent);
    	$this->assertEmpty($tpl->needles);
    	$this->assertEmpty($tpl->replacements);
    	$this->assertEmpty($tpl->dynamicNeedles);
    	$this->assertEmpty($tpl->dynamicReplacements);
    }

    public function testReplaceStaticNeedles()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("s", "MY-VAR", "my-value");
    	
    	$template = "... {MY-VAR} ...";
    	$generatedContent = $tpl->generate($template, true);
    	$this->assertContains("my-value", $generatedContent);
    }
    

    public function testReplaceDynamicNeedles()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("d", "MY-VAR", "value-1")
    	    ->next()
    	    ->set("d", "MY-VAR", "value-2")
    	    ->next()
    	;
    	
    	$template = "...<!-- BEGIN:BLOCK --> {MY-VAR} <!-- END:BLOCK -->...";
    	$generatedContent = $tpl->generate($template, true);
    	$this->assertContains("value-1", $generatedContent);
    	$this->assertContains("value-2", $generatedContent);
    }
    
    public function testReplaceStaticAndDynamicNeedles()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("s", "MY-VAR", "my-value");
    	$tpl->set("d", "MY-DYN-VAR", "value-1")
    	    ->next()
    	    ->set("d", "MY-DYN-VAR", "value-2")
    	    ->next()
    	;

    	$template = "... {MY-VAR} <!-- BEGIN:BLOCK --> {MY-DYN-VAR} <!-- END:BLOCK -->...";
    	$generatedContent = $tpl->generate($template, true);
    	$this->assertContains("my-value", $generatedContent);
    	$this->assertContains("value-1", $generatedContent);
    	$this->assertContains("value-2", $generatedContent);
    	
    }
    
    public function testReplacingNeedlesPreservesPHPTags()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("s", "MY-VAR", "my-value");
    	$tpl->set("d", "MY-DYN-VAR", "value-1")
    	    ->next()
    	    ->set("d", "MY-DYN-VAR", "value-2")
    	    ->next()
    	;

    	$template = "... {MY-VAR} <?php echo 'hello world'; ?> <!-- BEGIN:BLOCK --> {MY-DYN-VAR} <!-- END:BLOCK -->...";
    	$generatedContent = $tpl->generate($template, true);
    	$this->assertContains("my-value", $generatedContent);
    	$this->assertContains("value-1", $generatedContent);
    	$this->assertContains("value-2", $generatedContent);
    	$this->assertContains("<?php echo 'hello world'; ?>", $generatedContent);
    }
    
    public function testApplyTranslationFunctionInTemplate()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("s", "MY-VAR", "my-value");
    	$tpl->set("d", "MY-DYN-VAR", "value-1")
    	    ->next()
    	    ->set("d", "MY-DYN-VAR", "value-2")
    	    ->next()
    	;

    	$template = "... {MY-VAR} ... i18n('hello world') ... <!-- BEGIN:BLOCK --> {MY-DYN-VAR} <!-- END:BLOCK -->  translate('good bye world') ...";
    	$generatedContent = $tpl->generate($template, true);
    	$this->assertContains("my-value", $generatedContent);
    	$this->assertContains("value-1", $generatedContent);
    	$this->assertContains("value-2", $generatedContent);
    	$this->assertContains("hello world", $generatedContent);
    	$this->assertContains("good bye world", $generatedContent);
    	$this->assertNotContains("i18n", $generatedContent);
    	$this->assertNotContains("translate", $generatedContent);
    }
    
    public function testEncodingSetsHTMLMETATag()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->setEncoding("utf-8");
    	$tpl->set("s", "MY-VAR", "my-value");
    	 
    	$template = "... {MY-VAR} ...";
    	$generatedContent = $tpl->generate($template, true);
    	$this->assertContains("my-value", $generatedContent);
    	$this->assertContains('<meta http-equiv="Content-Type" content="text/html; charset='.$tpl->_encoding.'">', $generatedContent);
    }
    
    public function testOutputToStdout()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("s", "MY-VAR", "my-value");
    
    	$template = "... {MY-VAR} ...";
    	ob_start();
    	$tpl->generate($template);
    	$generatedContent = ob_get_clean();
    	$this->assertContains("... my-value ...", $generatedContent);
    }
    
    public function testGenerateFromTemplateFile()
    {
    	$tpl = new SimpleTemplate();
    	$tpl->set("s", "MY-VAR", "my-value");
    	$tpl->set("d", "MY-DYN-VAR", "value-1")
    	->next()
    	->set("d", "MY-DYN-VAR", "value-2")
    	->next()
    	;
    	
    	$templateFilepath = "tests/data/file.tpl";
    	$generatedContent = $tpl->generate($templateFilepath, true);
    	$this->assertContains("my-value", $generatedContent);
    	$this->assertContains("value-1", $generatedContent);
    	$this->assertContains("value-2", $generatedContent);
    }
    
}