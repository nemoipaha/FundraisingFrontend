<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetcher;

/**
 * @deprecated See https://phabricator.wikimedia.org/T254880
 */
class JsonStringReader {

	private string $json = '';

	public function __construct(
		private readonly string $file,
		private readonly FileFetcher $fileFetcher
	) {
	}

	private function getJsonFile(): string {
		return $this->fileFetcher->fetchFile( $this->file );
	}

	private function isJsonEmpty(): bool {
		return $this->json === '';
	}

	private function isJsonValid(): bool {
		json_decode( $this->json );
		return json_last_error() === JSON_ERROR_NONE;
	}

	public function readAndValidateJson(): string {
		$this->json = $this->getJsonFile();
		if ( $this->isJsonEmpty() || !$this->isJsonValid() ) {
			throw new \RuntimeException( 'error_invalid_json' );
		}
		return $this->json;
	}
}
