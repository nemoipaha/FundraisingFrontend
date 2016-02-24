<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation\Content\DisplayPage;

use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\Presentation\Content\PageContentModifier;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\Content\PageContentModifier
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen
 * @author Christoph Fischer
 */
class PageContentModifierTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider stringSubstitutionProvider
	 */
	public function testWhenWikiEditSectionHtmlString_fetchPageReturnsEmpty( $stringToBeSubstituted, $expectedResult ) {
		$contentModifier = new PageContentModifier(
			new NullLogger(),
			[
				'!<span class="editsection">.*?</span>!' => ''
			]
		);

		$this->assertSame(
			$expectedResult,
			$contentModifier->getProcessedContent( $stringToBeSubstituted, 'foo' )
		);
	}

	public function stringSubstitutionProvider() {
		return [
			[
				'<span class="editsection">foobar</span>',
				''
			],
			[
				'foobar',
				'foobar'
			],
			[
				'<span class="editsection">foobar</span>~=[,,_,,]:3<span class="editsection">foobar</span>',
				'~=[,,_,,]:3'
			]
		];
	}

}
