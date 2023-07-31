<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Shared;

final class ApplicationLayerName extends LayerName
{
	protected function getArchitectureLayer(): string
	{
		return Layers::APPLICATION;
	}
}