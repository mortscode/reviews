<?php
/**
 * Reviews plugin for Craft CMS 3.x
 *
 * An entry reviews plugin
 *
 * @link      https://github.com/mortscode
 * @copyright Copyright (c) 2020 Scot Mortimer
 */

namespace mortscode\reviews\services;

use mortscode\reviews\Reviews;
use mortscode\reviews\models\ReviewModel;
use mortscode\reviews\models\ReviewedEntryModel;
use mortscode\reviews\records\ReviewsRecord;

use Craft;
use craft\base\Component;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;

/**
 * ReviewsService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Scot Mortimer
 * @package   Reviews
 * @since     1.0.0
 */
class ReviewsService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * getReviewedEntries
     *
     * @return EntryQuery
     */
    public function getReviewedEntries(): EntryQuery
    {
        // get all entries that have reviews
        $reviewedEntriesRecords = ReviewsRecord::find()
            ->orderBy('dateUpdated desc')
            ->all();

        // create empty array for Entry Ids
        $entryIds = [];

        // loop over reviewed entries
        // and add id to $entryIds
        foreach ($reviewedEntriesRecords as $entryRecord) {
            $entryIds[] = $entryRecord->entryId;
        }

        // return entry query
        return Entry::find()
            ->id($entryIds)
            ->site('*')
            ->fixedOrder(true);
    }

    /**
     * getEntryReviews
     *
     * @param  mixed $entryId
     * @return array
     */
    public function getEntryReviews($entryId)
    {
        // get all records from DB related to entry
        $entryReviews = ReviewsRecord::find()
            ->where(['entryId' => $entryId])
            ->all();

        $reviewModels = [];

        foreach ($entryReviews as $entryReviewRecord)
        {
            $reviewModel = new ReviewModel();
            $reviewModel->setAttributes($entryReviewRecord->getAttributes(), false);

            $reviewModels[] = $reviewModel;
        }

        return $reviewModels;
    }
    
    /**
     * getEntryRatings
     *
     * @param  mixed $entryId
     * @return array [ReviewedEntryModel]
     */
    public function getEntryRatings($entryId)
    {
        // find all reviews related to $entryId
        $entryReviewRecords = ReviewsRecord::find()
            ->where(['entryId' => $entryId])
            ->all();

        // vars for total ratings
        $totalRatings = 0;
        $sumRatingsValue = 0;

        // loop over ratings
        foreach ($entryReviewRecords as $review) {
            // if the review has a rating, add to total and value
            if ($review->rating) {
                $totalRatings += 1;
                $sumRatingsValue += $review->rating;
            }
        }

        // calculate average rating
        $averageRating = $sumRatingsValue / $totalRatings;

        // create data object for ratings data
        $entryRatingsData = new ReviewedEntryModel();
        $entryRatingsData->totalRatings = $totalRatings;
        $entryRatingsData->averageRating = round($averageRating, 1);

        return $entryRatingsData;
    }
    
    /**
     * getReviewById
     *
     * @param  mixed $reviewId
     * @return ReviewModel
     */
    public function getReviewById($reviewId): ReviewModel
    {
        // get one record from DB related to entry
        $reviewRecord = ReviewsRecord::find()
            ->where(['id' => $reviewId])
            ->one();

        $reviewModel = new ReviewModel();
        $reviewModel->setAttributes($reviewRecord->getAttributes(), false);

        return $reviewModel;
    }

    /**
     * createReviewRecord
     *
     * @param  mixed $entryId
     * @return void
     */
    public function createReviewRecord($entryId, $attributes)
    {
        $reviewsRecord = new ReviewsRecord;
        $reviewsRecord->entryId = $entryId;
        $reviewsRecord->name = $attributes['name'];
        $reviewsRecord->email = $attributes['email'];
        $reviewsRecord->rating = $attributes['rating'];
        $reviewsRecord->comment = $attributes['comment'];
        $reviewsRecord->status = $attributes['status'];

        // save record in DB
        $reviewsRecord->save();
    }

    /**
     * updateReviewRecord
     *
     * @param  mixed $entryId
     * @return void
     */
    public function updateReviewRecord($reviewId, $attributes)
    {
        $reviewsRecord = ReviewsRecord::find()
            ->where(['id' => $reviewId])
            ->one();
        $reviewsRecord->response = $attributes['response'];
        $reviewsRecord->status = $attributes['status'];

        // save record in DB
        $reviewsRecord->save();
    }
}
