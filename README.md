# Reviews plugin for Craft CMS 3.x

An entry reviews plugin

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require mortscode/reviews

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Reviews.

## Reviews Overview

### Create Reviews
Add the following form to create a review:
```twig
<form role="form" method="post" accept-charset="UTF-8" action="">
    <input type="hidden" name="action" value="reviews/reviews/save">
    <input type="hidden" name="entryId" value="{{ entry.id }}">
    {{ csrfInput() }}

    <input type="text" name="name" placeholder="name">
    <input type="email" name="email" placeholder="email">
    <div>
        <input type="radio" id="rating-1" name="rating" value="1"><label for="rating-1">1</label>
        <input type="radio" id="rating-2" name="rating" value="2"><label for="rating-2">2</label>
        <input type="radio" id="rating-3" name="rating" value="3"><label for="rating-3">3</label>
        <input type="radio" id="rating-4" name="rating" value="4"><label for="rating-4">4</label>
        <input type="radio" id="rating-5" name="rating" value="5"><label for="rating-5">5</label>
    </div>
    <textarea name="comment" id="comment" cols="30" rows="10"></textarea>
    <button type="submit">Let's Go!</button>
</form>
```

## Configuring Reviews

### Default Status
Set the status that your Reviews will start with when they're added.

### Main Column Title
The heading above the 1st column on the main Reviews page in the CP. The default is "Entry".

### Reviewable Sections
Check the section or sections that will have Reviews 

## Using Reviews

### Available Variables
- name
- email
- rating
- comment
- response

### Templating

Render reviews in your templates like so:

```twig
{% set reviews = craft.reviews.getEntryReviews(entry.id) %}
<ol>
    {% for review in reviews %}
        <li>
            <h3>{{ review.name }}</h3>
            <p>{{ review.email }}</p>
            <p>Rating:
                {{ review.rating }}</p>
            <p>{{ review.comment }}</p>
            {% if review.response %}
                <p>{{ review.response }}</p>
            {% endif %}
        </li>
    {% endfor %}
</ol>
```
###

## Reviews Roadmap

Some things to do, and ideas for potential features:

* Release it

Brought to you by [Scot Mortimer](https://github.com/mortscode)
