<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Shared\Errors;

final class EmptyLayerName extends \Exception
{
	public function __construct(string $layer)
	{
		parent::__construct(sprintf("The %s layer parameter name can not be empty", $layer));
	}
}