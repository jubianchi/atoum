<?php

namespace mageekguy\atoum\report\fields\test\run\cli;

use mageekguy\atoum\report;

class travis extends report\fields\test\run
{
    public function __toString()
    {
        if (null !== $this->testClass) {
            return 'travis_fold:start:' . $this->testClass . PHP_EOL;
        }

        return '';
    }
}
