<?php
/**
 * Reviews plugin for Craft CMS 3.x
 *
 * An entry reviews plugin
 *
 * @link      https://github.com/mortscode
 * @copyright Copyright (c) 2020 Scot Mortimer
 */

namespace mortscode\reviews\variables;

use mortscode\reviews\models\RecaptchaModel;
use mortscode\reviews\Reviews;
use mortscode\reviews\models\ReviewModel;
use mortscode\reviews\models\ReviewedEntryModel;
use mortscode\reviews\enums\ReviewStatus;


use Craft;

/**
 * Reviews Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.reviews }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Scot Mortimer
 * @package   Reviews
 * @since     1.0.0
 */
class ReviewsVariable
{
    // Public Methods
    // =========================================================================
    
    // /**
    //  * getEntryRatings
    //  *
    //  * @param  mixed $entryId
    //  * @return ReviewedEntryModel
    //  */
    // public function getEntryRatings($entryId): ReviewedEntryModel
    // {
    //     return Reviews::$plugin->reviewsService->getEntryRatings($entryId);
    // }
    
    /**
     * getReviewedEntries
     *
     * @return array[entryIds]
     */
    public function getReviewedEntries()
    {
        return Reviews::$plugin->reviewsService->getReviewedEntries();
    }

    /**
     * getEntryReviews
     *
     * @param int $entryId
     * @param bool $getAllStatus
     * @return array[ReviewModel]
     */
    public function getEntryReviews(int $entryId, bool $getAllStatus = false): array
    {
        return Reviews::$plugin->reviewsService->getEntryReviews($entryId, $getAllStatus);
    }

    /**
     * getReviewById
     *
     * @param  mixed $reviewId
     * @return ReviewModel
     */
    public function getReviewById($reviewId): ReviewModel
    {
        return Reviews::$plugin->reviewsService->getReviewById($reviewId);
    }
    
    /**
     * getEntryRatings
     *
     * @param  mixed $entryId
     * @return ReviewedEntryModel
     */
    public function getEntryRatings($entryId)
    {
        return Reviews::$plugin->reviewsService->getEntryRatings($entryId);
    }
    
    /**
     * getRatingById
     *
     * @param  mixed $ratingId
     * @return ReviewModel
     */
    public function getRatingById($ratingId): ReviewModel
    {
        return Reviews::$plugin->reviewsService->getEntryRatings($ratingId);
    }
    
    /**
     * getStatusOptions
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return Reviews::$plugin->reviewsService->getStatusOptions();
    }

    /**
     * getReCaptchaKey
     *
     * @return string
     */
    public function getRecaptchaKey(): RecaptchaModel
    {
        return Reviews::$plugin->reviewsService->getRecaptchaKey();
    }

    /**
     * getStatusValues
     *
     * @return array
     */
    public function getStatusValues(): array
    {
        $statusValues = [
            ReviewStatus::Approved => ucfirst(ReviewStatus::Approved),
            ReviewStatus::Pending => ucfirst(ReviewStatus::Pending),
            ReviewStatus::Spam => ucfirst(ReviewStatus::Spam),
            ReviewStatus::Trashed => ucfirst(ReviewStatus::Trashed),
        ];

        return $statusValues;
    }
}
