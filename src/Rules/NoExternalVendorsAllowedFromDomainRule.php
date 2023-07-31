<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Rules;

use CoolDevGuys\PHPStan\Shared\Errors\NoHexArchitectureDetected;
use CoolDevGuys\PHPStan\Shared\Errors\NoNamespaceDetected;
use CoolDevGuys\PHPStan\Shared\Layers;
use CoolDevGuys\PHPStan\Shared\VendorName;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements \PHPStan\Rules\Rule<Node\Stmt\Use_>
 */
final class NoExternalVendorsAllowedFromDomainRule extends BaseArchHexRule
{
	public function processNode(Node $node, Scope $scope): array
	{
		$result = [];

		if (!$this->vendorStrictMode) {
			return $result;
		}

		try {
			$currentLayer = $this->getCurrentLayer($scope);
			if ($currentLayer === Layers::INFRASTRUCTURE) {
				return $result;
			}
		} catch (NoNamespaceDetected|NoHexArchitectureDetected $e) {
			return [];
		}

		/** @var Use_ $node */
		$uses = $node->uses;

		array_map(function (UseUse $use) use (&$result, $currentLayer) {
			if (!$this->shouldIgnore($use)) {
				$useVendor = new VendorName($use->name->getFirst());
				if (!$useVendor->equalTo($this->vendorName)){
					$reason = $this->parseErrorMessage($currentLayer, $use);
					$result[] = RuleErrorBuilder::message($reason)->build();
				}
			}
		}, $uses);

		return $result;
	}

	private function parseErrorMessage(string $currentLayer, UseUse $use): string
	{
		return sprintf(
			'The layer <%s> can not contain references to the external vendor: %s. Use statement: <%s>',
			$this->translateLayerName($currentLayer),
			$use->name->getFirst(),
			$use->name->toString()
		);
	}

	private function shouldIgnore(UseUse $use): bool
	{
		if (count($use->name->getParts()) > 1) {
			$useVendor = $use->name->getFirst();
			return in_array($useVendor, $this->ignoredExternalVendors, true);
		}

		return true;
	}
}
