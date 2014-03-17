<?php

namespace mageekguy\atoum\instrumentation\stream;

use
	mageekguy\atoum\test,
	mageekguy\atoum\exceptions,
	mageekguy\atoum\instrumentation\rules,
	mageekguy\atoum\instrumentation\sequence\matching
;

class filter extends \php_user_filter
{
	protected $name;
	protected $parameters = array();
	protected $rules;
	protected $stream = null;
	protected $buffer = null;

	function __construct()
	{
		$this->rules = new rules();
	}

	public function filter($in, $out, &$consumed, $closing)
	{
		$return = PSFS_FEED_ME;

		while ($iBucket = stream_bucket_make_writeable($in))
		{
			$this->buffer .= $iBucket->data;
			$consumed	  += $iBucket->datalen;
		}

		if (null !== $consumed)
		{
			$return = PSFS_PASS_ON;
		}

		if (true === $closing)
		{
			$this->compute();
			$bucket = stream_bucket_new($this->getStream(), $this->buffer);
			stream_bucket_make_writeable($out);
			stream_bucket_append($out, $bucket);

			$return = PSFS_PASS_ON;
			$this->buffer = null;
		}

		return $return;
	}

	public function onClose()
	{
		$this->stream = null;
		$this->buffer = null;
	}

	public function compute()
	{
		$matching = new matching(token_get_all($this->buffer));

		$matching->skip(array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT));
		$matching->match($this->rules);

		$buffer = null;

		foreach ($matching->getSequence() as $token)
		{
			if (is_array($token))
			{
				$buffer .= $token[$matching::TOKEN_VALUE];
			}
			else
			{
				$buffer .= $token;
			}
		}

		$this->buffer = $buffer;

		return;
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setParameters($parameters)
	{
		$this->parameters = $parameters;

		if (isset($parameters['moles']) === false || $parameters['moles'] === true)
		{
			$this->rules->merge(new rules\mole());
		}

		if (isset($parameters['coverage-transition']) === false || $parameters['coverage-transition'] === true)
		{
			$this->rules->merge(new rules\coverage());
		}

		return $this;
	}

	public function getParameters()
	{
		return $this->parameters;
	}

	public function getStream()
	{
		return $this->stream;
	}

	public function setStream($stream)
	{
		$this->stream = $stream;

		return $this;
	}
}
