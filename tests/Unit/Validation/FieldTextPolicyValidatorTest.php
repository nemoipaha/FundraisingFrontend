<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\FieldTextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\FieldTextPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class FieldTextPolicyValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenHarmlessText_itSucceeds(){
		$textPolicy = $this->createMock( TextPolicyValidator::class );
		$textPolicy->method( 'hasHarmlessContent' )->willReturn( true );
		$validator = new FieldTextPolicyValidator( $textPolicy, 0 );
		$this->assertTrue( $validator->validate( 'tiny cat' )->isSuccessful() );
	}

	public function testGivenHarmfulText_itFails(){
		$textPolicy = $this->createMock( TextPolicyValidator::class );
		$textPolicy->method( 'hasHarmlessContent' )->willReturn( false );
		$validator = new FieldTextPolicyValidator( $textPolicy, 0 );
		$this->assertFalse( $validator->validate( 'mean tiger' )->isSuccessful() );
	}

	public function testGivenHarmfulText_itProvidesAConstraintViolation(){
		$textPolicy = $this->createMock( TextPolicyValidator::class );
		$textPolicy->method( 'hasHarmlessContent' )->willReturn( false );
		$validator = new FieldTextPolicyValidator( $textPolicy, 0 );

		$this->assertInstanceOf(
			ConstraintViolation::class,
			$validator->validate( 'mean tiger' )->getViolations()[0]
		);
	}
}
