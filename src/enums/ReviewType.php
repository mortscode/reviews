<?php

namespace mortscode\reviews\enums;

/**
 * The ReviewType class is an abstract class that defines all of the review types that are available.
 * This class is a poor man's version of an enum, since PHP does not have support for native enumerations.
 *
 * @author mortscode
 * @since 1.0.0
 */
abstract class ReviewType
{
    public const Review = 'review';
    public const Question = 'question';
}