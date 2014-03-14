<?php

namespace mageekguy\atoum\instrumentation\rules;

use
	mageekguy\atoum\instrumentation\rules,
	mageekguy\atoum\instrumentation\sequence\matching
;

class coverage extends rules
{
	public function __construct()
	{
		$coverageExport = array();
		$markerCount	= 0;

		$this
			->add(
				'method::start',
				array(
					array('{'),
					function (Array $variables) use (& $markerCount ) {
						$id = $variables['class']['name'] . '::' . $variables['method']['name'];

						return array(
							'{',
							'\mageekguy\atoum\instrumentation\coverage::mark(\'' .
							$id . '\', ' . $markerCount++ . ');'
						);
					}
				)
			)
		;

		$this
			->add(
				'method::end',
				array(
					array(),
					function ($variables) use (& $markerCount, & $coverageExport) {
						$id = $variables['class']['name'] . '::' . $variables['method']['name'];

						$coverageExport[$id] = $markerCount;
						$markerCount = 0;

						return array();
					}
				)
			)
			->add(
				'if::condition::start',
				$conditionStart = array(
					array('('),
					function ($variables) use (& $markerCount) {
						$id = $variables['class']['name'] . '::' . $variables['method']['name'];

						return array(
							'(\mageekguy\atoum\instrumentation\coverage::markCondition(' .
							'\'' . $id . '\', ' . $markerCount++ . ', '
						);
					},
					matching::SHIFT_REPLACEMENT_END
				)
			)
			->add('while::condition::start', $conditionStart)
			->add(
				'case::start',
				$caseStart = array(
					array(':'),
					function ($variables) use (& $markerCount) {
						$id = $variables['class']['name'] . '::' . $variables['method']['name'];

						return array(
							':',
							'\mageekguy\atoum\instrumentation\coverage::markCondition(' .
							'\'' . $id . '\', ' . $markerCount++ . ', true);'
						);
					},
					matching::SHIFT_REPLACEMENT_END
				)
			)
			->add('default::start', $caseStart)
			->add(
				'if::condition::end',
				$conditionEnd = array(
					array(')'),
					array('))'),
					matching::SHIFT_REPLACEMENT_END
				)
			)
			->add('while::condition::end', $conditionEnd)
			->add(
				'for::condition::end',
				$forConditionEnd = array(
					array(')', '{'),
					function ($variables) use (& $markerCount) {
						$id = $variables['class']['name'] . '::' . $variables['method']['name'];

						return array(
							')',
							'{',
							'\mageekguy\atoum\instrumentation\coverage::markCondition(' .
							'\'' . $id . '\', ' . $markerCount++ . ', true);'
						);
					},
					matching::SHIFT_REPLACEMENT_END
				)
			)
			->add('foreach::condition::end', $forConditionEnd)
			->add(
				'if::block::end',
				$blockEnd = array(
					array('}'),
					function ($variables) use (& $markerCount) {
						$id = $variables['class']['name'] . '::' . $variables['method']['name'];

						return array(
							'}',
							'\mageekguy\atoum\instrumentation\coverage::markJoin(' .
							'\'' . $id . '\', ' . $markerCount++ . ');'
						);
					},
					matching::SHIFT_REPLACEMENT_END
				)
			)
			->add('else::block::end', $blockEnd)
			->add('while::block::end', $blockEnd)
			->add('for::block::end', $blockEnd)
			->add('foreach::block::end', $blockEnd)
			->add(
				'else::block::start',
				array(
					array('{'),
					function ($variables) use (& $markerCount) {
						$id = $variables['class']['name'] . '::' . $variables['method']['name'];

						return array(
							'{',
							'\mageekguy\atoum\instrumentation\coverage::markCondition(' .
							'\'' . $id . '\', ' . $markerCount++ . ', true);'
						);
					},
					matching::SHIFT_REPLACEMENT_END
				)
			)
			->add(
				'file::end',
				array(
					array(),
					function ($variables) use (& $coverageExport) {
						return array(
							'\mageekguy\atoum\instrumentation\coverage::export(' . var_export($coverageExport, true) . ');'
						);
					}
				)
			)
		;
	}
}
