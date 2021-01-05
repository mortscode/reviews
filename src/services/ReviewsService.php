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

use mortscode\reviews\models\RecaptchaModel;
use mortscode\reviews\Reviews;
use mortscode\reviews\models\ReviewModel;
use mortscode\reviews\models\ReviewedEntryModel;
use mortscode\reviews\records\ReviewsRecord;
use mortscode\reviews\enums\ReviewStatus;

use Craft;
use craft\base\Component;
use craft\elements\db\EntryQuery;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\CancelableEvent;

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
 *
 * @property-read array $statusOptions
 * @property-read EntryQuery $reviewedEntries
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
     * @param int $entryId
     * @param bool $getAllStatus
     * @return array
     */
    public function getEntryReviews(int $entryId, bool $getAllStatus): array
    {
        // get all records from DB related to entry
        if ($getAllStatus) {
            $entryReviews = ReviewsRecord::find()
                ->where(['entryId' => $entryId])
                ->all();
        } else {
            $entryReviews = ReviewsRecord::find()
                ->where(['entryId' => $entryId, 'status' => ReviewStatus::Approved])
                ->all();
        }

        $reviewModels = [];

        foreach ($entryReviews as $entryReviewRecord) {
            $reviewModel = new ReviewModel();
            $reviewModel->setAttributes($entryReviewRecord->getAttributes(), false);

            $reviewModels[] = $reviewModel;
        }

        return $reviewModels;
    }

    /**
     * getEntryRatings
     *
     * @param mixed $entryId
     * @return ReviewedEntryModel|void [ReviewedEntryModel]
     */
    public function getEntryRatings($entryId)
    {
        // find all reviews related to $entryId
        $entryReviewRecords = ReviewsRecord::find()
            ->where(['entryId' => $entryId])
            ->all();

        // Craft::dd(end($entryReviewRecords));

        // vars for total ratings
        $totalRatings = 0;
        $sumRatingsValue = 0;
        $approvedReviews = 0;
        $pendingReviews = 0;

        // loop over ratings
        foreach ($entryReviewRecords as $review) {
            // if the review has a rating, add to total and value
            if ($review->rating) {
                ++$totalRatings;
                $sumRatingsValue += $review->rating;
            }

            if ($review->status == ReviewStatus::Pending) {
                ++$pendingReviews;
            } else if ($review->status == ReviewStatus::Approved) {
                ++$approvedReviews;
            } else {
                return;
            }
        }

        // calculate average rating
        $averageRating = $sumRatingsValue / $totalRatings;

        // create data object for ratings data
        $entryRatingsData = new ReviewedEntryModel();
        $entryRatingsData->totalRatings = $totalRatings;
        $entryRatingsData->averageRating = round($averageRating, 1);
        $entryRatingsData->approvedReviews = $approvedReviews;
        $entryRatingsData->pendingReviews = $pendingReviews;

        return $entryRatingsData;
    }

    /**
     * getReviewById
     *
     * @param mixed $reviewId
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
     * @return array
     */
    public function getStatusOptions(): array
    {
        return [
            ReviewStatus::Approved,
            ReviewStatus::Pending,
            ReviewStatus::Spam,
            ReviewStatus::Trashed,
        ];
    }

    /**
     * @return RecaptchaModel
     */
    public function getRecaptchaKey(): RecaptchaModel
    {
        $settings = Reviews::$plugin->getSettings();

        $key = new RecaptchaModel();
        $key->siteKey = $settings->recaptchaSiteKey;

        return $key;
    }

    /**
     * @param int $reviewId
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteReview(int $reviewId) {
        // get record from DB
        $reviewRecord = ReviewsRecord::find()
            ->where(['id' => $reviewId])
            ->one();

        // if record exists then delete
        if ($reviewRecord) {
            // delete record from DB
            $reviewRecord->delete();
        }

        // log reset
        Craft::warning(Craft::t('reviews', 'Review with review ID {reviewId} reset by {username}', [
            'reviewId' => $reviewId,
            'username' => Craft::$app->getUser()->getIdentity()->username,
        ]), 'Reviews');
    }

    /**
     * Cleanup Entry Reviews
     * Deletes all trashed reviews for this entry
     *
     * @param int $entryId
     */
    public function cleanupEntry(int $entryId) {
        // get record from DB
        $trashedReviewsRecords = ReviewsRecord::find()
            ->where(['entryId' => $entryId, 'status' => ReviewStatus::Trashed])
            ->all();

        // if record exists then delete
        if ($trashedReviewsRecords) {

            // delete records from DB
            foreach ($trashedReviewsRecords as $record) {
                $record->delete();
            }
        }

        // log reset
        Craft::warning(Craft::t('reviews', 'Trashed reviews on Entry ID {entryId} deleted up by {username}', [
            'entryId' => $entryId,
            'username' => Craft::$app->getUser()->getIdentity()->username,
        ]), 'Reviews');
    }

    /**
     * createReviewRecord
     *
     * @param $review ReviewModel
     * @return bool
     */
    public function createReviewRecord(ReviewModel $review)
    {
        $reviewsRecord = new ReviewsRecord;
        $reviewsRecord->entryId = $review->entryId;
        $reviewsRecord->name = $review->name;
        $reviewsRecord->email = $review->email;
        $reviewsRecord->rating = $review->rating;
        $reviewsRecord->comment = $review->comment;
        $reviewsRecord->status = $review->status;

        // save record in DB
        return $reviewsRecord->save();
    }

    /**
     * updateReviewRecord
     *
     * @param $reviewId
     * @param $attributes
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
