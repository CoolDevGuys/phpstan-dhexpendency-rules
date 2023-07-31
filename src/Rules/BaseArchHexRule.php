<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Rules;

use CoolDevGuys\PHPStan\Shared\ApplicationLayerName;
use CoolDevGuys\PHPStan\Shared\DomainLayerName;
use CoolDevGuys\PHPStan\Shared\Errors\InvalidConfiguration;
use CoolDevGuys\PHPStan\Shared\Errors\NoHexArchitectureDetected;
use CoolDevGuys\PHPStan\Shared\Errors\NoNamespaceDetected;
use CoolDevGuys\PHPStan\Shared\InfrastructureLayerName;
use CoolDevGuys\PHPStan\Shared\LayerName;
use CoolDevGuys\PHPStan\Shared\Layers;
use CoolDevGuys\PHPStan\Shared\VendorName;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

abstract class BaseArchHexRule implements Rule
{
	protected InfrastructureLayerName $infrastructureLayerName;
	protected ApplicationLayerName $applicationLayerName;
	protected DomainLayerName $domainLayerName;
	protected VendorName $vendorName;
	protected array $layersNameMapping;
	protected bool $vendorStrictMode;
	protected array $ignoredExternalVendors;

	public function __construct(string $myVendorName, string $infrastructureLayerName, string $applicationLayerName,
								string $domainLayerName, bool $vendorStrictMode, array $ignoredExternalVendors)
	{
		$this->validateStrictMode($vendorStrictMode, $ignoredExternalVendors);
		$this->vendorStrictMode = $vendorStrictMode;
		$this->ignoredExternalVendors = $ignoredExternalVendors;
		$this->vendorName = new VendorName($myVendorName);
		$this->infrastructureLayerName = new InfrastructureLayerName($infrastructureLayerName);
		$this->applicationLayerName = new ApplicationLayerName($applicationLayerName);
		$this->domainLayerName = new DomainLayerName($domainLayerName);
		$this->layersNameMapping = [
			Layers::INFRASTRUCTURE => $this->infrastructureLayerName,
			Layers::APPLICATION => $this->applicationLayerName,
			Layers::DOMAIN => $this->domainLayerName,
		];
	}

	protected function getCurrentLayer(Scope $scope): string
	{
		$namespace = $scope->getNamespace();
		if (null === $namespace) {
			throw new NoNamespaceDetected();
		}

		$currentLayer = $this->getFileLayer($namespace);
		if (null === $currentLayer) {
			throw new NoHexArchitectureDetected();
		}

		return $currentLayer;
	}

	public function getNodeType(): string
	{
		return Use_::class;
	}

	protected function getFileLayer(string $namespace): ?string
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

	protected function translateLayerName(string $layerName): string
	{
		/** @var LayerName $name */
		$name = $this->layersNameMapping[$layerName];

		return $name->value();
	}

	abstract public function processNode(Node $node, Scope $scope): array;

	private function validateStrictMode(bool $vendorStrictMode, array $ignoredExternalVendors): void
	{
		if (!empty($ignoredExternalVendors) && !$vendorStrictMode) {
			throw new InvalidConfiguration("VendorStrictMode needs to be set as true");
		}
	}
}