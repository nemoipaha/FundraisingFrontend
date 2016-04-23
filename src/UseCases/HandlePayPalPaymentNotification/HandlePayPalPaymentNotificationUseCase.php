<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification;

use WMDE\Fundraising\Frontend\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandlePayPalPaymentNotificationUseCase {

	private $repository;
	private $authorizationService;

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService ) {
		$this->repository = $repository;
		$this->authorizationService = $authorizationService;
	}

	public function handleNotification( PayPalNotificationRequest $request ): bool {
		try {
			$donation = $this->repository->getDonationById( $request->getDonationId() );
		} catch ( GetDonationException $ex ) {
			return false;
		}

		if ( $donation === null ) {
			// TODO: create new donation
			return true;
		} else {
			if ( !$this->authorizationService->canModifyDonation( $request->getDonationId() ) ) {
				return false;
			}
		}

		try {
			$donation->addPayPalData(
				( new PayPalData() )
					->setPayerId( $request->getPayerId() )
					->setSubscriberId( $request->getSubscriberId() )
					->setPayerStatus( $request->getPayerStatus() )
					->setAddressStatus( $request->getPayerAddressStatus() )
					->setAmount( $request->getAmountGross() )
					->setCurrencyCode( $request->getCurrencyCode() )
					->setFee( $request->getTransactionFee() )
					->setSettleAmount( $request->getSettleAmount() )
					->setFirstName( $request->getPayerFirstName() )
					->setLastName( $request->getPayerLastName() )
					->setAddressName( $request->getPayerAddressName() )
			);
			$donation->confirmBooked();
			$this->repository->storeDonation( $donation );
		} catch ( \RuntimeException $ex ) {
			return false;
		}
		return true;
	}

}
