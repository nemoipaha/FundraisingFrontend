<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Domain\Model;

class Bucket {
	private string $name;
	private Campaign $campaign;
	private bool $defaultBucket;

	public const DEFAULT = true;
	public const NON_DEFAULT = false;

	public function __construct( string $name, Campaign $campaign, bool $defaultBucket ) {
		$this->name = $name;
		$this->campaign = $campaign;
		$this->defaultBucket = $defaultBucket;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getId(): string {
		return 'campaigns.' . $this->getCampaign()->getName() . '.' . $this->getName();
	}

	public function getCampaign(): Campaign {
		return $this->campaign;
	}

	public function isDefaultBucket(): bool {
		return $this->defaultBucket;
	}

	public function getParameters(): array {
		return [ $this->campaign->getUrlKey() => $this->campaign->getIndexByBucket( $this ) ];
	}

}
