<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Shared;

use CoolDevGuys\PHPStan\Shared\Errors\EmptyLayerName;

abstract class LayerName implements \Stringable
{
	private string $value;

	public function __construct(string $value)
	{
		$this->makeSureIsNotEmpty($value);
		$this->value = $value;
	}

	public function value(): string
	{
		return $this->value;
	}

	public function __toString(): string
	{
		return $this->value;
	}

	abstract protected function getArchitectureLayer(): string;

	private function makeSureIsNotEmpty(string $value): void
	{
		if (empty($value)) {
			throw new EmptyLayerName($this->getArchitectureLayer());
		}
	}
}