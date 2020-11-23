<?php

/**
 * @copyright Copyright (c) Mortscode
 * note: NOT IN USE
 */

namespace mortscode\reviews\behaviors;

use yii\base\Behavior;

class ReviewsBehavior extends Behavior
{
    /** @var int|null */
    public $reviewsTotal;

    /** @var int|null */
    public $reviewsAverage;
}