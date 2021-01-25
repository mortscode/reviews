<?php
/**
 * Reviews plugin for Craft CMS 3.x
 *
 * An entry reviews plugin
 *
 * @link      https://github.com/mortscode
 * @copyright Copyright (c) 2020 Scot Mortimer
 */

namespace mortscode\reviews\models;

use mortscode\reviews\Reviews;
use mortscode\reviews\enums\ReviewStatus;

use Craft;
use craft\base\Model;

/**
 * Reviews Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Scot Mortimer
 * @package   Reviews
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * default status for new reviews
     *
     * @var string
     */
    public $defaultStatus = ReviewStatus::Pending;

    /**
     * Title heading for the entry column in the CP
     *
     * @var string
     */
    public $mainColumnTitle = "Entry";

    /**
     * ReCapcha Site Key
     *
     * @var string
     */
    public $recaptchaSiteKey = null;

    /**
     * ReCapcha Secret Key
     *
     * @var string
     */
    public $recaptchaSecretKey = null;
    
    /**
     * Discuss User Handle
     *
     * @var string
     */
    public $disqusUserHandle = null;
    
    /**
     * which sections are able to be reviewed
     *
     * @var array
     */
    public $reviewsSections = [];

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['defaultStatus', 'string'],
        ];
    }
}
