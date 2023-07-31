<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

final class NoExternalVendorsAllowedFromDomainRuleTest extends RuleTestCase
{
	public static function getAdditionalConfigFiles(): array
	{
		return array_merge(
			parent::getAdditionalConfigFiles(),
			[__DIR__ . '/../rules.neon', __DIR__ . '/rules.neon'],
		);
	}

	public function test_rule_application(): void
	{
		$this->analyse(
			[__DIR__ . '/data/application-import-external-vendors.php'],
			[
				[
					'The layer <App> can not contain references to the external vendor: ExternalVendor. Use statement: <ExternalVendor\Tool>',
					6,
				],
			]
		);
	}

	public function test_rule_infrastructure(): void
	{
		$this->analyse(
			[__DIR__ . '/data/domain-import-external-vendors.php'],
			[
				[
					'The layer <Dom> can not contain references to the external vendor: ExternalVendor. Use statement: <ExternalVendor\Tool>',
					5,
				],
			]
		);
	}

	public function test_rule_ignore_vendors(): void
	{
		$this->analyse(
			[__DIR__ . '/data/ignore-external-vendors.php'],
			[
				[
					'The layer <App> can not contain references to the external vendor: ExternalVendor. Use statement: <ExternalVendor\Tool>',
					7,
				],
			]
		);
	}

	protected function getRule(): Rule
	{
		return self::getContainer()->getService('NoExternalVendorsAllowedFromDomainRule');
	}
}