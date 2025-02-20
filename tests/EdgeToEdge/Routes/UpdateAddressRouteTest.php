<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\AddressChange\UpdateAddressController
 */
class UpdateAddressRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	private const PATH = 'update-address';
	private const DUMMY_DONATION_ID = 0;

	public function testWhenInvalidDataIsSent_serverThrowsAnError(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$this->createEnvironment(
			function ( Client $client, FunFunFactory $factory ): void {
				$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

				$factory->getEntityManager()->persist( $addressChange );
				$factory->getEntityManager()->flush();

				$client->request(
					Request::METHOD_POST,
					self::PATH . '?addressToken=' . $addressChange->getCurrentIdentifier(),
					[]
				);

				$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
				$response = $client->getResponse();
				$this->assertTrue( $response->isOk() );
				$this->assertSame( 'Invalid value for field "Company".', $dataVars->message );
			}
		);
	}

	public function testWhenValidDataIsSent_serverShowsAConfirmationPage(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$this->createEnvironment(
			function ( Client $client, FunFunFactory $factory ): void {
				$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();
				$factory->getEntityManager()->persist( $addressChange );
				$factory->getEntityManager()->flush();

				$this->doRequestWithValidData( $client, $addressChange->getCurrentIdentifier()->__toString() );
				$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
				$response = $client->getResponse();

				$this->assertTrue( $response->isOk() );
				$this->assertNotTrue( isset( $dataVars->message ), 'No error message is sent.' );
			}
		);
	}

	private function doRequestWithValidData( Client $client, string $addressToken, array $additionalData = [] ): void {
		$client->request(
			Request::METHOD_POST,
			self::PATH . '?addressToken=' . $addressToken,
			array_merge(
				[
					'addressType' => 'person',
					'firstName' => 'Graf',
					'lastName' => 'Zahl',
					'salutation' => 'Herr',
					'street' => 'Zählerweg 5',
					'postcode' => '12345',
					'city' => 'Berlin-Zehlendorf',
					'country' => 'DE'
				],
				$additionalData
			)
		);
	}

	public function testUsersOptIntoReceiptByDefault(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$this->createEnvironment(
			function ( Client $client, FunFunFactory $factory ): void {
				$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();
				$entityManager = $factory->getEntityManager();
				$entityManager->persist( $addressChange );
				$entityManager->flush();

				$this->doRequestWithValidData( $client, $addressChange->getCurrentIdentifier()->__toString() );

				$entityManager->clear( AddressChange::class );
				$addressChangeAfterRequest = $entityManager->getRepository( AddressChange::class )->find( $addressChange->getId() );
				$this->assertTrue( $addressChangeAfterRequest->isOptedIntoDonationReceipt(), 'Donor should be opted into donation receipt' );
			}
		);
	}

	public function testUsersCanOptOutOfReceiptWhileStillProvidingAnAddress(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$this->createEnvironment(
			function ( Client $client, FunFunFactory $factory ): void {
				$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();
				$entityManager = $factory->getEntityManager();
				$entityManager->persist( $addressChange );
				$entityManager->flush();

				$this->doRequestWithValidData( $client, $addressChange->getCurrentIdentifier()->__toString(), [ 'receiptOptOut' => '1' ] );

				$entityManager->clear( AddressChange::class );
				$addressChangeAfterRequest = $entityManager->getRepository( AddressChange::class )->find( $addressChange->getId() );
				$this->assertFalse( $addressChangeAfterRequest->isOptedIntoDonationReceipt(), 'Donor should be opted out of donation receipt' );
			}
		);
	}

	public function testOptingOutWithEmptyFields_serverShowsAConfirmationPage(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$this->createEnvironment(
			function ( Client $client, FunFunFactory $factory ): void {
				$addressChange = AddressChangeBuilder::create()->forDonation( self::DUMMY_DONATION_ID )->forPerson()->build();

				$factory->getEntityManager()->persist( $addressChange );
				$factory->getEntityManager()->flush();

				$client->request(
					Request::METHOD_POST,
					self::PATH . '?addressToken=' . $addressChange->getCurrentIdentifier(),
					[
						'receiptOptOut' => '1'
					]
				);

				$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
				$response = $client->getResponse();
				$this->assertTrue( $response->isOk() );
				$this->assertNotTrue( isset( $dataVars->message ), 'No error message is sent.' );
			}
		);
	}
}
