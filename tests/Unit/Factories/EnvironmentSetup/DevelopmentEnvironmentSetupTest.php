<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories\EnvironmentSetup;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @covers \WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup
 */
class DevelopmentEnvironmentSetupTest extends TestCase {
	public function testEnvironmentSetsUpLoggingAndDoctrineConfiguration() {
		$expectedSetters = [
			'setPaypalLogger',
			'setSofortLogger',
			'setSofortLogger',
			'setDoctrineConfiguration',
			'setInternalErrorHtmlPresenter',
		];
		$supportingGetters = [ 'getLoggingPath', 'getDoctrineXMLMappingPaths' ];
		/** @var FunFunFactory&MockObject $factory */
		$factory = $this->createMock( FunFunFactory::class );
		foreach ( $expectedSetters as $setterName ) {
			$factory->expects( $this->once() )->method( $setterName );
		}
		$methodNameMatcher = '/^(?:' . implode( '|', array_merge( $expectedSetters, $supportingGetters ) ) . ')$/';
		$factory->expects( $this->never() )->method( $this->logicalNot( $this->matchesRegularExpression( $methodNameMatcher ) ) );

		$setup = new DevelopmentEnvironmentSetup();
		$setup->setEnvironmentDependentInstances( $factory );
	}
}
