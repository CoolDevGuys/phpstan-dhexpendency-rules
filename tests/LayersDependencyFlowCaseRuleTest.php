<?php
declare(strict_types=1);

namespace CoolDevGuys\PHPStan\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

final class LayersDependencyFlowCaseRuleTest extends RuleTestCase
{
	public static function getAdditionalConfigFiles(): array
	{
		return array_merge(
			parent::getAdditionalConfigFiles(),
			[__DIR__ . '/../rules.neon', __DIR__.'/rules.neon'],
		);
	}

    public function test_rule_application(): void
    {
		$this->analyse(
			[__DIR__ . '/data/application-import-infrastructure.php'],
			[
				[
					'The layer <App> can not contain references to the layers: Infra. Use statement: <CoolDevGuys\Infra\Tool>',
					5,
				],
			]
		);
    }

	public function test_use_infra_from_domain(): void
	{
		$this->analyse(
			[__DIR__ . '/data/domain-import-infrastructure.php'],
			[
				[
					'The layer <Dom> can not contain references to the layers: Infra or App. Use statement: <CoolDevGuys\Shared\Infra\Tool>',
					5,
				],
			]
		);
	}

    protected function getRule(): Rule
    {
		return self::getContainer()->getService('LayersDependencyFlowRule');
    }
}