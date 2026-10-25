<?php

/*
|--------------------------------------------------------------------------
| Organization design templates (ADR-012)
|--------------------------------------------------------------------------
| Designs are grouped by the organization template's `industry_key`.
| A design is an ordered list of blocks rendered from
| resources/views/guest/valex/blocks/{group}/{name}.blade.php (e.g. "hero.figure").
| `accent` is the default primary color, overridable via organizations.design_options.
*/

return [
    'blocks' => [
        'hero.figure', 'hero.profile',
        'channels.cards', 'channels.accordion', 'channels.carousel', 'channels.list',
        'posts.timeline', 'posts.blog', 'posts.list',
        'about.info',
    ],

    /*
    | Channel / sub-channel designs: industry-agnostic, built from the same blocks.
    | Hero blocks: channel.hero-figure, channel.hero-profile. A channel's sub-channels
    | (or its siblings, for a sub-channel) are rendered by the channels.* blocks.
    */
    'channels' => [
        'spotlight' => ['name' => 'Spotlight', 'description' => 'Cover header, sub-channels in a slider and blog-style posts.', 'icon' => 'sun', 'accent' => '#0ea5e9',
            'blocks' => ['channel.hero-figure', 'channels.carousel', 'posts.blog']],
        'notice-board' => ['name' => 'Notice Board', 'description' => 'Profile header, a clear sub-channel list and a timeline of posts.', 'icon' => 'clipboard', 'accent' => '#8b5cf6',
            'blocks' => ['channel.hero-profile', 'channels.list', 'posts.timeline']],
        'directory' => ['name' => 'Directory', 'description' => 'Profile header, expandable sub-channels and a compact post list.', 'icon' => 'list', 'accent' => '#059669',
            'blocks' => ['channel.hero-profile', 'channels.accordion', 'posts.list']],
        'magazine' => ['name' => 'Magazine', 'description' => 'Cover header, sub-channel cards and blog-style posts.', 'icon' => 'book-open', 'accent' => '#ec4899',
            'blocks' => ['channel.hero-figure', 'channels.cards', 'posts.blog']],
    ],

    'industries' => [
        'hospitality' => [
            'resort' => ['name' => 'Resort', 'description' => 'Large cover image, sliding channels and a news timeline.', 'icon' => 'sun', 'accent' => '#0ea5e9',
                'blocks' => ['hero.figure', 'channels.carousel', 'posts.timeline', 'about.info']],
            'concierge' => ['name' => 'Concierge', 'description' => 'Personal profile card, expandable services and blog-style updates.', 'icon' => 'bell', 'accent' => '#8b5cf6',
                'blocks' => ['hero.profile', 'channels.accordion', 'posts.blog']],
            'boutique' => ['name' => 'Boutique', 'description' => 'Cover image, clean channel cards and a compact news list.', 'icon' => 'star', 'accent' => '#ec4899',
                'blocks' => ['hero.figure', 'channels.cards', 'posts.list', 'about.info']],
        ],
        'education' => [
            'campus' => ['name' => 'Campus', 'description' => 'Cover image, faculties as an accordion and a timeline of announcements.', 'icon' => 'book-open', 'accent' => '#2563eb',
                'blocks' => ['hero.figure', 'channels.accordion', 'posts.timeline']],
            'faculty-hub' => ['name' => 'Faculty Hub', 'description' => 'Profile header, channel cards and news cards.', 'icon' => 'users', 'accent' => '#059669',
                'blocks' => ['hero.profile', 'channels.cards', 'posts.blog', 'about.info']],
            'student-life' => ['name' => 'Student Life', 'description' => 'Cover image, a channel slider and blog cards.', 'icon' => 'award', 'accent' => '#f59e0b',
                'blocks' => ['hero.figure', 'channels.carousel', 'posts.blog']],
        ],
        'healthcare' => [
            'care' => ['name' => 'Care', 'description' => 'Profile header, a clear departments list and an updates timeline.', 'icon' => 'heart', 'accent' => '#0d9488',
                'blocks' => ['hero.profile', 'channels.list', 'posts.timeline', 'about.info']],
            'clinic-guide' => ['name' => 'Clinic Guide', 'description' => 'Cover image, expandable clinics and contact details first.', 'icon' => 'plus-square', 'accent' => '#2563eb',
                'blocks' => ['hero.figure', 'channels.accordion', 'about.info']],
            'wellness' => ['name' => 'Wellness', 'description' => 'Profile header, soft channel cards and health articles.', 'icon' => 'activity', 'accent' => '#16a34a',
                'blocks' => ['hero.profile', 'channels.cards', 'posts.blog']],
        ],
        'retail' => [
            'storefront' => ['name' => 'Storefront', 'description' => 'Cover banner, sliding departments and offer cards.', 'icon' => 'shopping-bag', 'accent' => '#ef4444',
                'blocks' => ['hero.figure', 'channels.carousel', 'posts.blog']],
            'offers' => ['name' => 'Offers', 'description' => 'Profile header, department cards and a quick offers list.', 'icon' => 'tag', 'accent' => '#f97316',
                'blocks' => ['hero.profile', 'channels.cards', 'posts.list', 'about.info']],
            'mall-directory' => ['name' => 'Mall Directory', 'description' => 'Cover image, a collapsible directory and a news timeline.', 'icon' => 'map', 'accent' => '#7c3aed',
                'blocks' => ['hero.figure', 'channels.accordion', 'posts.timeline']],
        ],
        'corporate' => [
            'intranet' => ['name' => 'Intranet', 'description' => 'Profile header, departments list and company timeline.', 'icon' => 'briefcase', 'accent' => '#334155',
                'blocks' => ['hero.profile', 'channels.list', 'posts.timeline']],
            'newsroom' => ['name' => 'Newsroom', 'description' => 'Cover image, channel cards and news cards.', 'icon' => 'file-text', 'accent' => '#2563eb',
                'blocks' => ['hero.figure', 'channels.cards', 'posts.blog']],
            'departments' => ['name' => 'Departments', 'description' => 'Profile header, expandable departments and contact details.', 'icon' => 'grid', 'accent' => '#0f766e',
                'blocks' => ['hero.profile', 'channels.accordion', 'about.info']],
        ],
    ],
];
