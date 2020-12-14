<?php
/**
 * Reviews plugin for Craft CMS 3.x
 *
 * An entry reviews plugin
 *
 * @link      https://github.com/mortscode
 * @copyright Copyright (c) 2020 Scot Mortimer
 */

namespace mortscode\reviews\controllers;

use craft\errors\MissingComponentException;
use mortscode\reviews\models\ReviewModel;
use mortscode\reviews\Reviews;

use Craft;
use craft\web\Controller;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Reviews Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Scot Mortimer
 * @package   Reviews
 * @since     1.0.0
 */
class ReviewsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['save'];

    // Public Methods
    // =========================================================================

    /**
     * Save Action
     *
     * @return mixed
     * @throws BadRequestHttpException|MissingComponentException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $review = $this->_setReviewFromPost();
        $isValid = $review->validate();

        if ($isValid) {
            // review is valid, let's create the record
            $createReview = Reviews::$plugin->reviews->createReviewRecord($review);

            // attempt to create review
            if (!$createReview) {
                // set error if save isn't successful
                Craft::$app->getSession()->setError('Your review could not be saved. Please try again.');
                // pass review back to template
                Craft::$app->getUrlManager()->setRouteParams([
                    'review' => $review
                ]);

                return null;
            }
        } else {
            // review is not valid
            Craft::$app->getSession()->setError('Please check for errors.');
            // pass review back to template
            Craft::$app->getUrlManager()->setRouteParams([
                'review' => $review
            ]);

            return null;
        }

        // Ok, definitely valid + saved!
        return $this->redirectToPostedUrl();
    }

    /**
     * Update Action
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionUpdate()
    {
        $this->requirePostRequest();
        
        $request = Craft::$app->getRequest();
        $entryId = $request->getRequiredParam('entryId');
        $reviewId = $request->getRequiredParam('reviewId');

        $attributes[] = [
            'response' => Craft::$app->getRequest()->getParam('response') ?? '',
            'status' => Craft::$app->getRequest()->getParam('status') ?? '',
        ];

        Reviews::$plugin->reviews->updateReviewRecord($reviewId, $attributes[0]);

        Craft::$app->getSession()->setNotice('Review updated');

        return $this->redirect('reviews/entries/' . $entryId);
    }

    /**
     * Delete Review
     *
     * @return mixed
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionDelete()
    {
        $request = Craft::$app->getRequest();
        $entryId = $request->getRequiredParam('entryId');
        $reviewId = $request->getRequiredParam('reviewId');

        Reviews::$plugin->reviews->deleteReview($reviewId);

        Craft::$app->getSession()->setNotice(Craft::t('reviews', 'Review reset.'));

        return $this->redirect('reviews/entries/' . $entryId);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionCleanup(): Response
    {
        $request = Craft::$app->getRequest();
        $entryId = $request->getRequiredParam('entryId');

        Reviews::$plugin->reviews->cleanupEntry($entryId);

        Craft::$app->getSession()->setNotice(Craft::t('reviews', 'Entry Id {entryId} cleaned up.', [
            'entryId' => $entryId
        ]));

        return $this->redirect('reviews/entries/' . $entryId);
    }

    // PRIVATE METHODS
    // =========================

    /**
     * @return ReviewModel
     * @throws BadRequestHttpException
     */
    private function _setReviewFromPost(): ReviewModel
    {
        $request = Craft::$app->getRequest();
        $settings = Reviews::$plugin->getSettings();

        $review = new ReviewModel();

        // get IP and User Agent
        $review->ipAddress = $request->getUserIP();
        $review->userAgent = $request->getUserAgent();

        // get form fields
        $review->entryId = $request->getRequiredParam('entryId', $review->entryId);
        $review->name = $request->getRequiredParam('name', $review->name);
        $review->email = $request->getRequiredParam('email', $review->email);
        $review->rating = $request->getParam('rating', $review->rating);
        $review->comment = $request->getParam('comment', $review->comment);
        $review->status = $settings->defaultStatus;

        return $review;
    }
}
