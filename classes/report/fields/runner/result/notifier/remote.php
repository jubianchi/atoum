<?php

    namespace mageekguy\atoum\report\fields\runner\result\notifier;

    use
        mageekguy\atoum,
        mageekguy\atoum\exceptions,
        mageekguy\atoum\report\fields\runner\result\notifier
        ;

    abstract class remote extends notifier
    {
        protected function getCommand()
        {
            return 'we do it in pure php';
        }
    }
