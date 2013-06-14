<?php

namespace mageekguy\atoum\reports\asynchronous;

use
	mageekguy\atoum,
	mageekguy\atoum\exceptions,
	mageekguy\atoum\score
;

class coveralls extends atoum\reports\asynchronous
{
	const defaultServiceName = 'atoum';
	const defaultEvent = 'manual';

	protected $sourceDir = null;
	protected $repositoryToken = null;
	protected $score = null;
	protected $loc = 0;
	protected $coveredLoc = 0;
	protected $methods = 0;
	protected $coveredMethods = 0;
	protected $classes = 0;
	protected $package = '';

	public function __construct($sourceDir, $repositoryToken, atoum\adapter $adapter = null)
	{
		parent::__construct();

		$this->setAdapter($adapter);

		if ($this->adapter->extension_loaded('json') === false)
		{
			throw new exceptions\runtime('libxml PHP extension is mandatory for clover report');
		}

		$this->repositoryToken = $repositoryToken;
		$this->sourceDir = new atoum\fs\path($sourceDir);
		$this->sourceDir = $this->sourceDir->resolve();
	}

	public function handleEvent($event, atoum\observable $observable)
	{
		$this->score = ($event !== atoum\runner::runStop ? null : $observable->getScore());

		return parent::handleEvent($event, $observable);
	}

	public function build($event)
	{
		if ($event === atoum\runner::runStop)
		{
			$this->string = json_encode($this->makeJson($this->score->getCoverage()), defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0);

			$opts = array(
				'http' => array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => http_build_query(array('json_file' => $this->string))
				)
			);
			$context  = stream_context_create($opts);
			$result = file_put_contents('https://coveralls.io/api/v1/jobs', false, $context);
			var_dump($result);
		}

		return $this;
	}

	protected function makeJson(score\coverage $coverage)
	{
		$now = new \DateTime('now');

		return array(
			'service_name' => static::defaultServiceName,
			'service_event_type' => static::defaultEvent,
			'repo_token' => $this->repositoryToken,
			'git' => new \StdClass(),
			'run_at' => $now->format('Y-m-d H:i:s O'),
			'source_files' => $this->makeSourceElement($coverage),
			'environment' => new \StdClass()
		);
	}

	protected function makeSourceElement(score\coverage $coverage)
	{
		$sources = array();
		foreach ($coverage->getClasses() as $class => $file)
		{
			$path = new atoum\fs\path($file);
			$source = file_get_contents((string) $path->resolve());

			$sources[] = array(
				'name' => ltrim((string) $path->relativizeFrom($this->sourceDir), './'),
				'source' => $source,
				'coverage' => $this->makeCoverageElement($coverage->getCoverageForClass($class))
			);
		}

		return $sources;
	}

	protected function makeCoverageElement(array $coverage)
	{
		$cover = array();

		foreach ($coverage as $lines)
		{
			if (sizeof($lines) > 0)
			{
				foreach ($lines as $number => $line)
				{
					for($i = count($cover); $i < $number; $i++)
					{
						$cover[] = null;
					}

					if ($line === 1)
					{
						$cover[] = 1;
					}
					else
					{
						$cover[] = 0;
					}
				}
			}
		}

		return $cover;
	}
}
