<?php

namespace mageekguy\atoum\instrumentation\rules;

use
	mageekguy\atoum\instrumentation\rules,
	mageekguy\atoum\instrumentation\sequence\matching
;

class mole extends rules
{
	public function get()
	{
		$this->add(
			'method::start',
			array(
				array(
					array('{'),
					function (array $variables) {
						$class = '\atoum\instrumentation\mole';

						if (true === $variables['method']['static'])
						{
							$callable = '\'' . $variables['class']['name'] . '\', \'' . $variables['method']['name'] . '\'';
						}
						else
						{
							$callable = '$this, \'' . $variables['method']['name'] . '\'';
						}

						$code = ' if(' . $class . '::exists(array(' . $callable . '))) ' .
							'return ' . $class . '::call(' .
							'array(' . $callable . '), func_get_args()' .
							');';

						return array('{', $code);
					},
					matching::SHIFT_REPLACEMENT_END
				)
			)
		);
	}
}
