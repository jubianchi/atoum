<?php

namespace mageekguy\atoum\instrumentation;

class coverage
{
	const BUCKET_VALUE = 0;
	const BUCKET_LINE = 1;

	protected static $_scores = array();

	public static function export(array $export)
	{
		$scores = & static::$_scores;

		foreach ($export as $method => $markerCount)
		{
			$scores[$method] = new \SplFixedArray($markerCount);
			$scoresMethod = & $scores[$method];

			foreach ($scoresMethod as $index => $_)
			{
				$scoresMethod[$index] = new \ArrayObject(array(
					static::BUCKET_VALUE => false,
					static::BUCKET_LINE => -1
				));
			}
		}

		return;
	}

	public static function mark($id, $index, $line)
	{
		if (isset(static::$_scores) && isset(static::$_scores[$id]))
		{
			$bucket = & static::$_scores[$id][$index];
			$bucket[static::BUCKET_VALUE] = true;
			$bucket[static::BUCKET_LINE] = $line;
		}

		return;
	}

	public static function markCondition($id, $index, $line, $condition)
	{
		if (isset(static::$_scores) && isset(static::$_scores[$id]))
		{
			$bucket = & static::$_scores[$id][$index];
			$bucket[static::BUCKET_VALUE] = $bucket[static::BUCKET_VALUE] || true == $condition;
			$bucket[static::BUCKET_LINE] = $line;
		}

		return $condition;
	}

	public static function markJoin($id, $index, $line)
	{
		if(isset(static::$_scores[$id]))
		{
			$bucket = & static::$_scores[$id][$index];
			$bucket[static::BUCKET_VALUE] = true;
			$bucket[static::BUCKET_LINE] = $line;
		}

		return;
	}

	public static function getScore($id)
	{
		if(false === @preg_match($id, ''))
		{
			if(!isset(static::$_scores[$id]))
			{
				throw new \exception(sprintf('Method %s does not exist.', $id), 0);
			}

			$iterator = array(static::$_scores[$id]);
		}
		else
		{
			$iterator = new \RegexIterator(
				new \ArrayIterator(static::$_scores),
				$id,
				\RegexIterator::MATCH,
				\RegexIterator::USE_KEY
			);
		}

		$count = 0;
		$total = 0;

		foreach($iterator as $score)
		{
			foreach($score as $bucket)
			{
				$count += (int) (true === $bucket[static::BUCKET_VALUE]);
				++$total;
			}
		}

		if(0 === $total)
		{
			return 0;
		}

		return $count / $total;
	}

	public static function getRawScores()
	{
		return static::$_scores;
	}

	public static function reset($soft = true)
	{
		if(false === $soft)
		{
			static::$_scores = array();

			return;
		}

		foreach(static::$_scores as $score)
		{
			foreach($score as $bucket)
			{
				$bucket[static::BUCKET_VALUE] = false;
				$bucket[static::BUCKET_LINE] = -1;
			}
		}

		return;
	}
}
