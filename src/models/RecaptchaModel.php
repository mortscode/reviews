<?php

namespace mortscode\reviews\models;

use yii\base\Model;

class RecaptchaModel extends Model
{
    /**
     * site key
     *
     * @var string
     */
    public $siteKey;

    /**
     * Define what is returned when model is converted to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->siteKey;
    }
}