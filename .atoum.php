<?php

use
	mageekguy\atoum,
	mageekguy\atoum\instrumentation\stream\cache
;

cache::setCacheDirectory('/tmp');

$runner
	->disableXDebugCodeCoverage()
	->enableInstrumentation()
		->disableMoleInstrumentation()
	//	->disableCoverageInstrumentation()
;

//$coverageHtmlField = new atoum\report\fields\runner\coverage\html('atoum', __DIR__ . DIRECTORY_SEPARATOR . 'coverage');
//$coverageHtmlField->setRootUrl('http://jubianchi.fr/');

$script
	->addDefaultReport()
	//->addField($coverageHtmlField)
;
