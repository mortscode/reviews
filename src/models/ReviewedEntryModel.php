<?php
/**
 * Reviews plugin for Craft CMS 3.x
 *
 * Get aggregate ratings on your entries
 *
 * @link      https://github.com/mortscode
 * @copyright Copyright (c) 2020 Mortscode
 */

namespace mortscode\reviews\models;

use mortscode\reviews\Reviews;

use Craft;
use craft\base\Model;

/**
 * ReviewedEntryModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Mort
 * @package   Reviews
 * @since     1.0.0
 */
class ReviewedEntryModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var int|null Total Ratings
     */
    public $totalRatings;

    /**
     * @var int|null Average Rating
     */
    public $averageRating;

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
            // required attributes
            [['totalRatings', 'AverageRating'], 'required'],
        ];
    }

    /**
     * Define what is returned when model is converted to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->averageRating;
    }
}
