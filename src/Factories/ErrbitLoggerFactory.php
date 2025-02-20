<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Airbrake\MonologHandler as AirbrakeHandler;
use Airbrake\Notifier;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\LogLevel;

class ErrbitLoggerFactory {

	/**
	 * Data from PayPal IPN we want to have in ErrBit for faster debugging.
	 *
	 * This is just for convenience/reference, you can find the complete data in the paypal log
	 *
	 */
	private const ALLOWED_PPL_PARAMS = [
		  "invoice",
		  "payment_date",
		  "payment_status",
		  "charset",
		  "notify_version",
		  "txn_id",
		  "payment_type",
		  "txn_type",
		  "item_name",
		  "ipn_track_id",
	];

	/**
	 * Regular expression that matches URL paths for PayPal IPN end points, {@see config/routing.yaml}
	 */
	private const PAYPAL_URL_MATCH = '!/handle-paypal-payment-notification!';

	public static function createErrbitHandler( string $projectId, string $projectKey, string $host, string $environment = 'dev', ?string $level = LogLevel::DEBUG, bool $bubble = true ): HandlerInterface {
		$notifier = new Notifier( [
			'projectId' => $projectId,
			'projectKey' => $projectKey,
			'host' => $host,
			'environment' => $environment
		] );

		$notifier->addFilter( static function ( $notice ) {
			$currentUrl = $notice['context']['url'] ?? '';
			if ( !preg_match( self::PAYPAL_URL_MATCH, $currentUrl ) ) {
				return $notice;
			}
			$newParams = [];
			foreach ( self::ALLOWED_PPL_PARAMS as $paramName ) {
				if ( !empty( $notice['params'][$paramName] ) ) {
					$newParams[$paramName] = $notice['params'][$paramName];
				}
			}
			$notice['params'] = $newParams;
			return $notice;
		} );

		return new AirbrakeHandler( $notifier, Logger::toMonologLevel( $level ?? LogLevel::DEBUG ), $bubble );
	}

}
