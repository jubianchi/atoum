<?php

namespace mageekguy\atoum\report\fields\test;

use mageekguy\atoum;
use mageekguy\atoum\report;
use mageekguy\atoum\test;

class travisStart extends report\field
{
    public function __construct()
    {
        parent::__construct([test::runStart]);
    }

    public function __toString()
    {
        return 'travis_time:start:'.get_class($this->observable).PHP_EOL.'travis_fold:start:'.get_class($this->observable).PHP_EOL;
    }

    public function handleEvent($event, atoum\observable $observable)
    {
        $this->event = $event;
        $this->observable = $observable;

        return parent::handleEvent($event, $observable);
    }
}
