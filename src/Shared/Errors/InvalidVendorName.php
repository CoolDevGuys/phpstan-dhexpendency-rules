<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Shared\Errors;

final class InvalidVendorName extends \Exception
{
	public function __construct()
	{
		parent::__construct("The myVendorName parameter is not set");
	}
}