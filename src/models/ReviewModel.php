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

use Craft;
use craft\base\Model;

/**
 * ReviewModel Model
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
class ReviewModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null Entry ID
     */
    public $entryId;

    /**
     * @var \DateTime|null Date created
     */
    public $dateCreated;

    /**
     * @var \DateTime|null Date updated
     */
    public $dateUpdated;
    
    /**
     * name
     *
     * @var string
     */
    public $name;
    
    /**
     * email
     *
     * @var string
     */
    public $email;
    
    /**
     * rating
     *
     * @var int
     */
    public $rating = 5;
    
    /**
     * comment
     *
     * @var text
     */
    public $comment = null;
    
    /**
     * response
     *
     * @var text
     */
    public $response = null;

    /**
     * status
     *
     * @var enum
     */
    public $status = null;

    /**
     * ipAddress
     *
     * @var string
     */
    public $ipAddress = null;

    /**
     * userAgent
     *
     * @var string
     */
    public $userAgent = null;


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
            // the name, email attributes are required
            [['name', 'email'], 'required'],

            // the email attribute should be a valid email address
            ['email', 'email'],
        ];
    }

    /**
     * Define what is returned when model is converted to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->rating;
    }
}
