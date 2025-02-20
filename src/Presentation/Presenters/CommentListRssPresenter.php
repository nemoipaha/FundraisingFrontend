<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Repositories\CommentWithAmount;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\CommentList;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class CommentListRssPresenter {

	public function __construct( private readonly TwigTemplate $template ) {
	}

	public function present( CommentList $commentList ): string {
		return $this->template->render( [
			'rssPublicationDate' => $this->getPublicationTime( $commentList ),
			'comments' => $this->getCommentsViewModel( $commentList ),
		] );
	}

	private function getCommentsViewModel( CommentList $commentList ): array {
		return array_map(
			static function ( CommentWithAmount $comment ) {
				return [
					'amount' => $comment->getDonationAmount(),
					'author' => $comment->getAuthorName(),
					'text' => $comment->getCommentText(),
					'publicationDate' => $comment->getDonationTime()->format( 'r' ),
				];
			},
			$commentList->toArray()
		);
	}

	private function getPublicationTime( CommentList $commentList ): string {
		if ( !array_key_exists( 0, $commentList->toArray() ) ) {
			return '';
		}

		return $commentList->toArray()[0]->getDonationTime()->format( 'r' );
	}

}
