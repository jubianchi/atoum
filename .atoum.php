<?php

use mageekguy\atoum;

$runner
    ->enableInstrumentation()
        //->disableMoleInstrumentation()
        //->disableCoverageInstrumentation()
;

//$coverageHtmlField = new atoum\report\fields\runner\coverage\html('atoum', __DIR__ . DIRECTORY_SEPARATOR . 'coverage');
//$coverageHtmlField->setRootUrl('http://jubianchi.fr/');

$script
    ->addDefaultReport()
    //->addField($coverageHtmlField)
;
