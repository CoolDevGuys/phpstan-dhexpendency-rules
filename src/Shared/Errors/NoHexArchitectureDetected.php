<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Shared\Errors;

final class NoHexArchitectureDetected extends \Exception
{
	public function __construct()
	{
		parent::__construct('No hexagonal architecture layers found, make sure parameters are configured correctly');
	}
}