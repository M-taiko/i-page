<?php
return [
    // Template keys are authoritative: a restaurant must not acquire hotel rooms
    // merely because both belong to the hospitality industry.
    'templates' => [
        'hotel' => ['bookings'],
        'restaurant' => ['offers', 'menu'],
        'retail' => ['offers', 'departments'],
        'hypermarket' => ['offers', 'departments'],
        'school' => ['timetable', 'evaluations'],
        'university' => ['timetable', 'evaluations'],
    ],
    'catalog' => [
        'bookings' => ['name' => 'Rooms & reservations', 'icon' => 'calendar', 'description' => 'Room availability, nightly rates, guest reservations and recorded payments.', 'ready' => true],
        'offers' => ['name' => 'Offers & discounts', 'icon' => 'tag', 'description' => 'Promotions, validity dates and discounted products.', 'ready' => false],
        'departments' => ['name' => 'Store departments', 'icon' => 'grid', 'description' => 'Organize products and offers by department.', 'ready' => false],
        'menu' => ['name' => 'Digital menu', 'icon' => 'book-open', 'description' => 'Menu sections, items and availability.', 'ready' => false],
        'timetable' => ['name' => 'Classes & timetable', 'icon' => 'clock', 'description' => 'Class sessions, teachers, rooms and schedule changes.', 'ready' => false],
        'evaluations' => ['name' => 'Evaluations', 'icon' => 'star', 'description' => 'Private student evaluations with controlled access.', 'ready' => false],
    ],
];
