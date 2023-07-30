<?php

declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Rules;

use CoolDevGuys\PHPStan\Shared\Layers;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Infrastructure->Application->Domain Layer Flow Check
 * @implements \PHPStan\Rules\Rule<Node\Stmt\Use_>
 */
final class LayersDependencyFlowRule implements Rule
{
	private string $infrastructureLayerName;
	private string $applicationLayerName;
	private string $domainLayerName;
	private string $vendorName;
	private array $layersNameMapping;

	public function __construct(string $myVendorName, string $infrastructureLayerName, string $applicationLayerName,
								string $domainLayerName)
	{
		$this->vendorName = $myVendorName;
		$this->infrastructureLayerName = $infrastructureLayerName;
		$this->applicationLayerName = $applicationLayerName;
		$this->domainLayerName = $domainLayerName;
		$this->layersNameMapping = [
			Layers::INFRASTRUCTURE => $this->infrastructureLayerName,
			Layers::APPLICATION => $this->applicationLayerName,
			Layers::DOMAIN => $this->domainLayerName,
		];
	}

	public function getNodeType(): string
	{
		return Use_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$namespace = $scope->getNamespace();
		if (null === $namespace) {
			return [
				RuleErrorBuilder::message('No namespace detected, make sure parameters are configured correctly')
								->build()
			];
		}

		$currentLayer = $this->getFileLayer($namespace);
		if (null === $currentLayer) {
			return [
				RuleErrorBuilder::message('No hexagonal architecture layers found, make sure parameters are configured correctly')
								->build()
			];
		}
		/** @var Use_ $node */
		return $this->validateDependencyFlowRule($currentLayer, $node->uses);
	}

	private function getFileLayer(string $namespace): ?string
	{
		$infraRegex = '/^' . $this->vendorName . '[\\\\\w]*\\\\' . $this->infrastructureLayerName . '([\\\\]{1}|\z)/';
		$appRegex = '/^' . $this->vendorName . '[\\\\\w]*\\\\' . $this->applicationLayerName . '([\\\\]{1}|\z)/';
		$domainRegex = '/^' . $this->vendorName . '[\\\\\w]*\\\\' . $this->domainLayerName . '([\\\\]{1}|\z)/';

		if (preg_match($infraRegex, $namespace)) {
			return Layers::INFRASTRUCTURE;
		}
		if (preg_match($appRegex, $namespace)) {
			return Layers::APPLICATION;
		}

		if (preg_match($domainRegex, $namespace)) {
			return Layers::DOMAIN;
		}

		return null;
	}

	private function validateDependencyFlowRule(string $currentLayer, array $uses): array
	{
		$result = [];
		$forbiddenLayers = $this->getForbiddenLayersForCurrentLayer($currentLayer);

		/** @var Node\Stmt\UseUse $use */
		foreach ($uses as $use) {
			if (!empty($forbiddenLayers) && !empty(array_intersect($use->name->getParts(), $forbiddenLayers))) {
				$reason = $this->parseErrorMessage($currentLayer, $forbiddenLayers, $use);
				$result[] = RuleErrorBuilder::message($reason)->build();
			}
		}
		return $result;
	}

	public function getForbiddenLayersForCurrentLayer(string $currentLayer): array
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

	private function translateLayerName(string $layerName): string
	{
		return $this->layersNameMapping[$layerName];
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
