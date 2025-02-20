<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\GetDonation\GetDonationRequest;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowDonationConfirmationController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$useCase = $ffFactory->newGetDonationUseCase( $request->get( 'accessToken', '' ) );

		$responseModel = $useCase->showConfirmation( new GetDonationRequest(
			(int)$request->get( 'id', '' )
		) );

		if ( !$responseModel->accessIsPermitted() ) {
			throw new AccessDeniedException( 'access_denied_donation_confirmation' );
		}
		$ffFactory->getDonationSubmissionRateLimiter()->setRateLimitCookie( $request->getSession() );

		$ffFactory->getTranslationCollector()->addTranslationFile( $ffFactory->getI18nDirectory() . '/messages/paymentTypes.json' );

		$donation = $responseModel->getDonation();
		$paymentData = $ffFactory->newGetPaymentUseCase()->getPaymentDataArray( $donation->getPaymentId() );

		return new Response(
			$ffFactory->newDonationConfirmationPresenter()->present(
				$donation,
				$paymentData,
				$responseModel->getUpdateToken(),
				$request->get( 'accessToken', '' ),
				array_merge(
					Routes::getNamedRouteUrls( $ffFactory->getUrlGenerator() ),
					[
						'updateDonor' => $ffFactory->getUrlGenerator()->generateAbsoluteUrl(
							Routes::UPDATE_DONOR,
							[
								'accessToken' => $request->get( 'accessToken', '' )
							]
						)
					]
				)
			)
		);
	}

}
