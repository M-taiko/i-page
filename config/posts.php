<?php

/*
|--------------------------------------------------------------------------
| Post formats, card styles, badges and reactions (ADR-013)
|--------------------------------------------------------------------------
*/

return [
    'formats' => [
        'standard' => ['name' => 'Standard', 'icon' => 'file-text', 'description' => 'Title, text and an optional image.'],
        'gallery' => ['name' => 'Photo gallery', 'icon' => 'image', 'description' => 'Several photos; shown as a carousel.'],
        'poll' => ['name' => 'Poll', 'icon' => 'bar-chart-2', 'description' => 'A question with options people vote on.'],
        'event' => ['name' => 'Event', 'icon' => 'calendar', 'description' => 'Date, place and RSVP (going / maybe / not going).'],
        'rating' => ['name' => 'Rating', 'icon' => 'star', 'description' => 'Ask people to rate with 1-5 stars.'],
    ],

    'card_styles' => [
        'classic' => ['name' => 'Classic', 'description' => 'Plain card.'],
        'image' => ['name' => 'Large image', 'description' => 'Big cover image on top.'],
        'gradient' => ['name' => 'Gradient', 'description' => 'Text on a colored background.'],
        'spotlight' => ['name' => 'Spotlight', 'description' => 'Highlighted card with an accent edge.'],
    ],

    'priorities' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'],

    // key => [label, color (hex), icon]
    'badges' => [
        'new' => ['New', '#22c55e', 'zap'],
        'featured' => ['Featured', '#f59e0b', 'star'],
        'important' => ['Important', '#ef4444', 'alert-circle'],
        'urgent' => ['Urgent', '#dc2626', 'alert-triangle'],
        'update' => ['Update', '#0ea5e9', 'refresh-cw'],
        'offer' => ['Offer', '#ec4899', 'tag'],
        'event' => ['Event', '#8b5cf6', 'calendar'],
        'reminder' => ['Reminder', '#f97316', 'bell'],
        'tip' => ['Tip', '#14b8a6', 'help-circle'],
    ],

    // key => [label, emoji]
    'reactions' => [
        'like' => ['Like', '👍'],
        'love' => ['Love', '❤️'],
        'haha' => ['Haha', '😂'],
        'wow' => ['Wow', '😮'],
        'sad' => ['Sad', '😢'],
        'celebrate' => ['Celebrate', '🎉'],
    ],

    'limits' => [
        'gallery_images' => 8,
        'poll_options_min' => 2,
        'poll_options_max' => 8,
    ],

    'rsvp' => ['going' => 'Going', 'maybe' => 'Maybe', 'not_going' => 'Not going'],
];
