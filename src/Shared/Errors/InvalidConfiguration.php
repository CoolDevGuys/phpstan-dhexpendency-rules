<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Shared\Errors;

final class InvalidConfiguration extends \Exception
{
	public function __construct(string $message)
	{
		parent::__construct($message);
	}
}