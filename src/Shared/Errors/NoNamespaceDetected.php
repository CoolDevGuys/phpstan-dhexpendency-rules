<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Shared\Errors;

final class NoNamespaceDetected extends \Exception
{
	public function __construct()
	{
		parent::__construct("No namespace detected, make sure parameters are configured correctly");
	}
}