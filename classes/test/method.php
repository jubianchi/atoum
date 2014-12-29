<?php

namespace mageekguy\atoum\test;

use
	mageekguy\atoum,
	mageekguy\atoum\test,
	mageekguy\atoum\annotations
;

class method
{
	const enginesNamespace = '\mageekguy\atoum\test\engines';
	const defaultEngine = 'concurrent';

	protected $method;
	protected $phpVersions = array();
	protected $dataProvider;
	protected $engineClass;
	protected $engine;
	protected $tags = array();
	protected $ignored = false;
	protected $void = false;
	protected $mandatoryExtensions = array();

	public function __construct(\reflectionMethod $method)
	{
		$this->method = $method;

		$this->setEngineClass();
	}

	public function __toString()
	{
		return $this->getName();
	}

	public function getName()
	{
		return $this->method->getName();
	}

	public function ignore($ignore = true)
	{
		$this->ignored = $ignore;

		return $this;
	}

	public function isIgnored()
	{
		return $this->ignored;
	}

	public function void($void = true)
	{
		$this->void = $void;

		return $this;
	}

	public function isVoid()
	{
		return $this->void;
	}

	public function addPhpVersion($version, $operator = null)
	{
		$this->phpVersions[$version] = $operator ?: '>=';

		return $this;
	}

	public function getPhpVersions()
	{
		return $this->phpVersions;
	}

	public function addMandatoryExtension($extension)
	{
		$this->mandatoryExtensions[] = $extension;

		return $this;
	}

	public function getMandatoryExtensions()
	{
		return $this->mandatoryExtensions;
	}

	public function setDataProvider(\reflectionMethod $provider)
	{
		$this->dataProvider = $provider;

		return $this;
	}

	public function getDataProvider()
	{
		return $this->dataProvider;
	}

	public function needsDataProvider()
	{
		return $this->method->getNumberOfParameters() > 0;
	}

	public function setEngineClass($engine = null)
	{
		$engine = $engine ?: self::defaultEngine;

		if (substr($engine, 0, 1) !== '\\')
		{
			$engine = self::enginesNamespace . '\\' . $engine;
		}

		$this->engine = null;
		$this->engineClass = $engine;

		return $this;
	}

	public function getEngineClass()
	{
		return $this->engineClass;
	}

	public function setTags(array $tags)
	{
		$this->tags = $tags;

		return $this;
	}

	public function getTags()
	{
		return $this->tags;
	}

	public function hasTags(array $tags)
	{
		return sizeof(array_intersect($tags, $this->tags)) > 0;
	}

	public function isAsynchronous()
	{
		return $this->makeEngine()->engine->isAsynchronous();
	}

	public function getScore()
	{
		return $this->makeEngine()->engine->getScore();
	}

	public function run(test $test)
	{
		$this->makeEngine()->engine->run($test);

		return $this;
	}

	public function extractAnnotation(atoum\annotations\extractor $extractor = null)
	{
		$extractor = $extractor ?: new atoum\annotations\extractor();

		$this->setMethodAnnotations($extractor);

		$extractor->extract($this->method->getDocComment());

		return $this;
	}

	protected function makeEngine()
	{
		if ($this->engine === null)
		{
			$engineClass = $this->getEngineClass();
			$this->engine = new $engineClass();

			if ($this->engine instanceof atoum\test\engine === false)
			{
				throw new exceptions\runtime('Test engine \'' . $engineClass . '\' is invalid for method \'' . $this->method->getDeclaringClass() . '::' . $this->method->getName() . '()\'');
			}
		}

		return $this;
	}

	protected function setMethodAnnotations(atoum\annotations\extractor $extractor = null)
	{
		$method = $this;
		$reflection = $this->method;
		$extractor = $extractor ?: new atoum\annotations\extractor();

		$extractor
			->resetHandlers()
			->setHandler('ignore', function($value) use ($method) { $method->ignore(annotations\extractor::toBoolean($value)); })
			->setHandler('tags', function($value) use ($method) { $method->setTags(annotations\extractor::toArray($value)); })
			->setHandler('dataProvider', function($value) use ($method, $reflection) { $method->setDataProvider(new \reflectionMethod($reflection->getDeclaringClass(), $value)); })
			->setHandler('engine', function($value) use ($method) { $method->setEngineClass($value); })
			->setHandler('isVoid', function($value) use ($method) { $method->void(); })
			->setHandler('isNotVoid', function($value) use ($method) { $method->void(false); })
			->setHandler('php', function($value) use ($method) {
					$value = annotations\extractor::toArray($value);

					if (isset($value[0]) === true)
					{
						$operator = null;

						if (isset($value[1]) === false)
						{
							$version = $value[0];
						}
						else
						{
							$version = $value[1];

							switch ($value[0])
							{
								case '<':
								case '<=':
								case '=':
								case '==':
								case '>=':
								case '>':
									$operator = $value[0];
							}
						}

						$method->addPhpVersion($version, $operator);
					}
				}
			)
			->setHandler('extensions', function($value) use ($method) {
					foreach (annotations\extractor::toArray($value) as $mandatoryExtension)
					{
						$method->addMandatoryExtension($mandatoryExtension);
					}
				}
			)
		;

		return $this;
	}
} 
