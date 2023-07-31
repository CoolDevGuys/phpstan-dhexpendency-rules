<?php

declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Rules;

use CoolDevGuys\PHPStan\Shared\Errors\NoHexArchitectureDetected;
use CoolDevGuys\PHPStan\Shared\Errors\NoNamespaceDetected;
use CoolDevGuys\PHPStan\Shared\Layers;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Infrastructure->Application->Domain Layer Flow Check
 * @implements \PHPStan\Rules\Rule<Node\Stmt\Use_>
 */
final class LayersDependencyFlowRule extends BaseArchHexRule
{
	public function __construct(string $myVendorName, string $infrastructureLayerName, string $applicationLayerName,
								string $domainLayerName, bool $vendorStrictMode = false,
								array  $ignoredExternalVendors = [])
	{
		parent::__construct($myVendorName, $infrastructureLayerName, $applicationLayerName, $domainLayerName,
			$vendorStrictMode, $ignoredExternalVendors);
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$result = [];

		try {
			$currentLayer = $this->getCurrentLayer($scope);
		} catch (NoNamespaceDetected|NoHexArchitectureDetected $e) {
			return [];
		}

		$forbiddenLayers = $this->getForbiddenLayersForCurrentLayer($currentLayer);

		/** @var Use_ $node */
		$uses = $node->uses;
		array_map(function (Node\Stmt\UseUse $use) use (&$result, $forbiddenLayers, $currentLayer) {
			if (!empty($forbiddenLayers) && !empty(array_intersect($use->name->getParts(), $forbiddenLayers))) {
				$reason = $this->parseErrorMessage($currentLayer, $forbiddenLayers, $use);
				$result[] = RuleErrorBuilder::message($reason)->build();
			}
		}, $uses);

		return $result;
	}

	private function getForbiddenLayersForCurrentLayer(string $currentLayer): array
	{
		$forbiddenLayers = [];
		if ($currentLayer === Layers::APPLICATION) {
			$forbiddenLayers[] = $this->translateLayerName(Layers::INFRASTRUCTURE);
		}
		if ($currentLayer === Layers::DOMAIN) {
			$forbiddenLayers[] = $this->translateLayerName(Layers::INFRASTRUCTURE);
			$forbiddenLayers[] = $this->translateLayerName(Layers::APPLICATION);
		}
		return $forbiddenLayers;
	}

	private function parseErrorMessage(string $currentLayer, array $forbiddenLayers, Node\Stmt\UseUse $use): string
	{
		return sprintf(
			'The layer <%s> can not contain references to the layers: %s. Use statement: <%s>',
			$this->translateLayerName($currentLayer),
			implode(' or ', $forbiddenLayers),
			$use->name->toString()
		);
	}
}
