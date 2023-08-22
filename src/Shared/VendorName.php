<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Shared;

use CoolDevGuys\PHPStan\Shared\Errors\InvalidVendorName;

final class VendorName implements \Stringable
{
    private string $value;

    public function __construct(string $name)
	{
		if (empty($name)) {
			throw new InvalidVendorName();
		}
		$this->value = $name;
	}

	public function equalTo(VendorName $other): bool
	{
		return $this->value === $other->value;
	}

	public function __toString(): string
	{
		return $this->value;
	}
}
