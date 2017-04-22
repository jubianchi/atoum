<?php

namespace mageekguy\atoum\report\fields\test;

use mageekguy\atoum;
use mageekguy\atoum\report;
use mageekguy\atoum\test;

class travisStop extends report\field
{
    public function __construct()
    {
        parent::__construct([test::runStop]);
    }

    public function __toString()
    {
        return 'travis_time:end:'.get_class($this->observable).':duration='.$this->observable->getScore()->getTotalDuration().PHP_EOL.'travis_fold:end:'.get_class($this->observable).PHP_EOL;
    }

    public function handleEvent($event, atoum\observable $observable)
    {
        $this->event = $event;
        $this->observable = $observable;

        return parent::handleEvent($event, $observable);
    }
}
