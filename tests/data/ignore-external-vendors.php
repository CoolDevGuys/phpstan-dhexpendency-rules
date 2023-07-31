<?php

namespace CoolDevGuys\App;

use CoolDevGuys\Dom\Entity;
use IgnoredVendor\OtherTool;
use ExternalVendor\Tool;
use \Stringable;

class Test
{
	public function __toString(): string
	{
		return 'test';
	}
}