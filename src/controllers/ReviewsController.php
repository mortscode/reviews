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

use mortscode\reviews\Reviews;

use Craft;
use craft\web\Controller;

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
    protected $allowAnonymous = ['index', 'do-something'];

    // Public Methods
    // =========================================================================

    /**
     * Save Action
     *
     * @return mixed
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $entryId = Craft::$app->getRequest()->getRequiredParam('entryId');
        $settings = Reviews::$plugin->getSettings();

        $attributes[] = [
            'name' => Craft::$app->getRequest()->getRequiredParam('name'),
            'email' => Craft::$app->getRequest()->getRequiredParam('email'),
            'rating' => Craft::$app->getRequest()->getParam('rating'),
            'comment' => Craft::$app->getRequest()->getParam('comment'),
            'status' => $settings->defaultStatus,
            'response' => NULL,
        ];

        Reviews::$plugin->reviews->createReviewRecord($entryId, $attributes[0]);

        return $this->redirectToPostedUrl();
    }

    public function actionUpdate()
    {
        $this->requirePostRequest();
        
        $request = Craft::$app->getRequest();
        $entryId = Craft::$app->getRequest()->getRequiredParam('entryId');
        $reviewId = Craft::$app->getRequest()->getRequiredParam('reviewId');

        $attributes[] = [
            'response' => Craft::$app->getRequest()->getParam('response') ?? '',
            'status' => Craft::$app->getRequest()->getParam('status') ?? '',
        ];

        Reviews::$plugin->reviews->updateReviewRecord($reviewId, $attributes[0]);

        return $this->redirect('reviews/entries/' . $entryId);
    }
}
