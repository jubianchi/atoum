<?php

$report = new \mageekguy\atoum\reports\realtime\cli\travis();
$report->addWriter(new \mageekguy\atoum\writers\std\out());

$runner->addReport($report);
