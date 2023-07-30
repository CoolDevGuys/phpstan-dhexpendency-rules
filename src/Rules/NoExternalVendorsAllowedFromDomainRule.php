<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @implements \PHPStan\Rules\Rule<Node\Stmt\Use_>
 */
final class NoExternalVendorsAllowedFromDomainRule implements Rule
{

	public function getNodeType(): string
	{
		// TODO: Implement getNodeType() method.
	}

	public function processNode(Node $node, Scope $scope): array
	{
		// TODO: Implement processNode() method.
	}
}