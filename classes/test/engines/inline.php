<?php

namespace mageekguy\atoum\test\engines;

use
	mageekguy\atoum,
	mageekguy\atoum\test
;

class inline extends test\engine
{
	protected $score = null;

	public function isAsynchronous()
	{
		return false;
	}

	public function __construct(atoum\test\score $score = null)
	{
		$this->setScore($score);
	}

	public function setScore(atoum\test\score $score = null)
	{
		$this->score = $score ?: new atoum\test\score();

		return $this;
	}

	public function getScore()
	{
		return $this->score;
	}

	public function run(atoum\test $test)
	{
		$currentTestMethod = $test->getCurrentMethod();

		if ($currentTestMethod !== null)
		{
			$this->score = $test->runTestMethod($currentTestMethod)->getScore();
		}

		return $this;
	}
}
