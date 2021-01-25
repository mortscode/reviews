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

use craft\elements\Entry;
use craft\errors\MissingComponentException;
use mortscode\reviews\models\ReviewModel;
use mortscode\reviews\Reviews;
use mortscode\reviews\enums\ReviewStatus;
use mortscode\reviews\records\ReviewsRecord;

use Craft;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\captcha\CaptchaAction;

use GuzzleHttp\Client;
use mortscode\reviews\models\ImportedReviewModel;
use SimpleXMLElement;

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
        
        $validRecaptcha = $this->_verifyRecaptcha();

        if (!$validRecaptcha) {
            // error if ReCaptcha fails
            Craft::$app->getSession()->setError('Sorry, there was a problem. Please try again.');

            return null;
        }

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

        Craft::$app->getSession()->setNotice(Craft::t('reviews', 'Review deleted.'));

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

    /**
     * @return string[][]
     */
    public function actionCaptcha(): array
    {
        return [
            'captcha' => [
                'class' => CaptchaAction::class,
            ],
        ];
    }

    public function actionImportXml()
    {
        $user = Craft::$app->getUser()->getIdentity();
        
        if (!$user->admin) {
            return;
        }

        $request = Craft::$app->getRequest();
     
        $data = $request->getRequiredParam('xml');

        $comments = new SimpleXMLElement($data);

        $threads = [];
        $posts = [];

        foreach($comments->thread as $thread) {

            $thread = [
                'id' => (string)$thread->attributes('http://disqus.com/disqus-internals')['id'],
                'url' => (string)$thread->link,
                'post_id' => (string)$thread->id,
                'comments' => [],
            ];

            if ($thread['id']) {
                $threads[$thread['id']] = $thread;
            }
        }

        foreach($comments->post as $post) {

            $threadId = (string)$post->thread->attributes('http://disqus.com/disqus-internals')['id'];
            $postId = (string)$post->attributes('http://disqus.com/disqus-internals')['id'];
            
            if ($post->parent) {
                $parentId = (string)$post->parent->attributes('http://disqus.com/disqus-internals')['id'];
            } else {
                $parentId = null;
            }
          
            $posts[$postId] = [
                'id' => $postId,
                'created' => (string)$post->createdAt,
                'isDeleted' => (string)$post->isDeleted !== 'false',
                'isClosed' => (string)$post->isClosed !== 'false',
                'isSpam' => (string)$post->isSpam !== 'false',
                'name' => (string)$post->author->name,
                'user' => (string)$post->author->username,
                'message' => (string)$post->message,
                'children' => [],
            ];

            if ($parentId) {
                $posts[$parentId]['children'][] =& $posts[$postId];
            } else {
                $threads[$threadId]['comments'][] =& $posts[$postId];
            }
          
        }
        
        $threads = $this->_convertThreads($threads);

        $this->_createImportedRecords($threads);

        Craft::$app->getSession()->setNotice('Disqus XML imported');

        // Ok, definitely valid + saved!
        return $this->redirect('reviews');
    }

    // PRIVATE METHODS
    // =========================

    /**
     * _convertComments
     *
     * @param  mixed $comments
     * @return array
     */
    private function _convertComments(array $comments) {

        $result = [];
        foreach($comments as $comment) {
        
            if($comment['isDeleted']) {
                continue;
            }

            if($comment['isSpam']) {
                continue;
            }

            $messageLinks = preg_match('/(http|ftp|mailto)/', $comment['message']);
            
            if($messageLinks) {
                continue;
            }

            // clean up html from text
            $formattedMessage = strip_tags($comment['message']);
            $formattedMessage = htmlspecialchars_decode($formattedMessage);
        
            $newComment = [
                'created' => $comment['created'],
                'name' => $comment['name'],
                'disqusUser' => $comment['user'],
                'message' => $formattedMessage,
                'children' => $this->_convertComments($comment['children']),
            ];

            $result[] = $newComment;
      
        }

        return $result;
    }
        
    /**
     * _convertThreads
     *
     * @param  mixed $threads
     * @return array
     */
    private function _convertThreads(array $threads) {
      
        $result = [];
        foreach($threads as $thread) {

            if (!$thread['url']) {
                continue;
            }
            
            if (!$thread['comments']) {
                continue;
            }

            // compare url to tmp url
            $tmpUrl = preg_match('/https:\/\/themodernproper.com\//', $thread['url']);
            
            // drop all the non-tmp urls
            if (!$tmpUrl) {
                continue;
            }

            $newComments = $this->_convertComments($thread['comments']);
            $slug = $this->_getSlugFromUrl($thread['url']);

            $newThread = [
                'id' => $thread['id'],
                'slug' => $slug,
                'post_id' => $thread['post_id'],
                'comments' => $newComments,
            ];

            // skip thread if comments list is empty
            if (empty($newThread['comments'])) {
                continue;
            }

            $result[] = $newThread;
        }
        
        return $result;
    }
    
    /**
     * _getSlugFromUrl
     *
     * @param  mixed $url
     * @return void
     */
    private function _getSlugFromUrl(string $url)
    {
        $parsedUrl = parse_url($url);
        $slug = $parsedUrl['path'];
        // remove "/" from string
        $slug = substr($slug, 1);

        return $slug;
    }
    
    /**
     * _createImportedRecords
     *
     * @param  mixed $threads
     * @return void
     */
    private function _createImportedRecords(array $threads)
    {
        $settings = Reviews::$plugin->getSettings();

        // loop through threads and match the URL
        foreach ($threads as $thread) {
            // find an entry with a matching uri
            $entry = Entry::find()
                ->section($settings->reviewsSections)
                ->slug($thread['slug'])
                ->one();

            if (!$entry) {
                // if no matching url, move on ->
                continue;
            }
            
            // otherwise let's add the reviews comments
            foreach ($thread['comments'] as $comment) {
                $existingRecord = ReviewsRecord::find()
                    ->where(['comment' => $comment, 'entryId' => $entry['id']])
                    ->one();

                if ($existingRecord) {
                    continue;
                }
                
                $newReview = new ImportedReviewModel();
    
                $response = [];

                // look for response from admin
                if($settings->disqusUserHandle) {
                    if ($comment['children']) {
                        $children = $comment['children'];
    
                        foreach ($children as $child) {
                            if ($child['disqusUser'] === $settings['disqusUserHandle']) {
                                $response[] = $child['message'];
                            }
                        }
                    }
                }
                
                // get form fields
                $newReview->entryId = $entry->id ?? '';
                $newReview->name = $thread->name ?? $comment['name'];
                $newReview->email = $thread->email ?? '';
                $newReview->rating = $thread->rating ?? null;
                $newReview->comment = $comment['message'] ?? 'MESSAGE ERROR';
                $newReview->response = $response[0] ?? null;
                $newReview->status = ReviewStatus::Pending;
                
                // review is valid, let's create the record
                $createReview = Reviews::$plugin->reviews->createImportedReviewRecord($newReview);
                
                if (!$createReview) {
                    // set error if save isn't successful
                    Craft::$app->getSession()->setError('Your review could not be saved. Please try again.');
                    // pass review back to template
                    Craft::$app->getUrlManager()->setRouteParams([
                        'review' => $newReview
                    ]);
    
                    return null;
                }
            }
        }
    }

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
    
    /**
     * _verifyRecaptcha
     *
     * @return bool
     */
    private function _verifyRecaptcha()
    {
        $settings = Reviews::$plugin->getSettings();

        // if user has entered recaptcha keys, verify request
        if ($settings->recaptchaSecretKey) {
            $request = Craft::$app->getRequest();
            $recaptchaToken = $request->getParam('token');
            
            $url = 'https://www.google.com/recaptcha/api/siteverify';

            $client = new Client();

            $response = $client->post($url, [
                'form_params' => [
                    'secret'   => $settings->recaptchaSecretKey,
                    'response' => $recaptchaToken,
                    'remoteip' => $request->getUserIP(),
                ],
            ]);

            $result = json_decode((string)$response->getBody(), true);

            return $result['success'];
        } else {
            return true;
        }
    }
}
