<?php

use
	mageekguy\atoum,
	mageekguy\atoum\instrumentation\stream\cache
;

cache::setCacheDirectory(__DIR__ . '/cache');

$runner
	->disableXDebugCodeCoverage()
	->enableInstrumentation()
		//->disableMoleInstrumentation()
		//->disableCoverageInstrumentation()
;

$coverageHtmlField = new atoum\report\fields\runner\coverage\html('atoum', __DIR__ . DIRECTORY_SEPARATOR . 'coverage2');
$coverageHtmlField->setRootUrl('file://' . __DIR__);

$script
	->addDefaultReport()
	->addField($coverageHtmlField)
;
