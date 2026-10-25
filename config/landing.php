<?php

/*
|--------------------------------------------------------------------------
| Product landing page content (`/`)
|--------------------------------------------------------------------------
| Everything the landing page says lives here, in Arabic and English.
| "features" must describe things that exist today; "roadmap" comes from the
| documented plans (docs/ENTERPRISE_REVIEW.md, docs/27_FEATURE_SPECIFICATIONS.md).
| Icons are emoji so the page needs no icon font.
*/

return [
    'ar' => [
        'dir' => 'rtl',
        'seo' => [
            'title' => 'i-Page | البوابة الرقمية لكل منظمة — تواصل بـ QR',
            'description' => 'منصة تواصل مؤسسي: امسح QR واحد لتصلك أخبار وإعلانات واستطلاعات وفعاليات منظمتك من غير تطبيق. للفنادق والجامعات والمستشفيات والشركات.',
            'image_alt' => 'i-Page — البوابة الرقمية لكل منظمة',
            'locale' => 'ar_AR',
        ],
        'brand_tag' => 'البوابة الرقمية لكل منظمة',
        'nav' => ['how' => 'كيف يعمل', 'features' => 'المميزات', 'roles' => 'الأدوار', 'designs' => 'التصميمات', 'roadmap' => 'خارطة الطريق', 'browse' => 'تصفّح المنظمات', 'signin' => 'تسجيل الدخول', 'workspace' => 'افتح مساحة العمل'],
        'hero' => [
            'eyebrow' => 'منصة تواصل مؤسسي مبنية على QR',
            'title_before' => 'البوابة الرقمية لـ',
            'title_after' => 'كل منظمة',
            'words' => ['الفنادق', 'الجامعات', 'المستشفيات', 'المتاجر', 'الشركات', 'الجهات الحكومية', 'المطاعم', 'النوادي'],
            'sub' => 'امسح QR واحد، وخلال ثوانٍ يصلك كل ما يخصّك: أخبار، إعلانات، فعاليات، استطلاعات ودعم، من غير تحميل تطبيق ومن غير مكالمات.',
            'cta_primary' => 'تصفّح المنظمات',
            'cta_secondary' => 'اعرف أكتر',
            'phone_org' => 'فندق النيل',
            'phone_post' => 'عرض خاص على الإقامة هذا الأسبوع',
            'phone_poll' => 'أي وجبة تفضّل في الإفطار؟',
            'scan' => 'امسح للانضمام',
        ],
        'stats' => ['organizations' => 'منظمة', 'channels' => 'قناة عامة', 'posts' => 'منشور', 'scans' => 'عملية مسح QR', 'live' => 'أرقام حيّة من المنصة'],
        'how' => [
            'title' => 'كيف يعمل؟',
            'sub' => 'ثلاث خطوات، من غير تعقيد.',
            'steps' => [
                ['icon' => '📱', 'title' => 'امسح الـ QR', 'text' => 'كل منظمة وقناة لها كود QR خاص، يُطبع أو يُعرض في المكان المناسب، ويفتح الصفحة مباشرة من المتصفح.'],
                ['icon' => '✨', 'title' => 'انضم في ثوانٍ', 'text' => 'تسجيل سريع بالبريد أو الجوال مع رمز تحقق، وتتابع المنظمة وتشترك في القنوات اللي تهمّك.'],
                ['icon' => '🔔', 'title' => 'ابقَ على اطلاع', 'text' => 'أخبار وإعلانات واستطلاعات وفعاليات تصلك حسب اهتماماتك وجمهورك، وتتفاعل معها بتقييم أو تصويت أو حضور.'],
            ],
        ],
        'industries' => [
            'title' => 'تصلح لأي منظمة',
            'sub' => 'ليست نظام فنادق، بل منصة عامة بمصطلحات عامة: منظمة، فرع، قسم، قناة، زائر، عضو.',
            'items' => ['🏨 الفنادق', '🎓 الجامعات', '🏥 المستشفيات', '🛒 المتاجر', '🏢 الشركات', '🏛️ الجهات الحكومية', '🏦 البنوك', '🍽️ المطاعم', '🛍️ المولات', '🏭 المصانع', '⛽ النفط والغاز', '🏋️ النوادي والجيم', '🎯 مراكز التدريب', '🏫 المدارس'],
        ],
        'features' => [
            'title' => 'ما هو موجود اليوم',
            'sub' => 'كل ده شغّال فعلاً في المنصة الحالية.',
            'items' => [
                ['icon' => '🏢', 'title' => 'منظمات متعددة (Multi-tenant)', 'text' => 'كل منظمة معزولة بالكامل: علامات تجارية، مواقع، أقسام، قنوات وقنوات فرعية، وحدّ أقصى للقنوات لكل منظمة.', 'tag' => 'أساس'],
                ['icon' => '🔳', 'title' => 'منصة QR', 'text' => 'أكواد QR لكل قناة مع رسالة ترحيب مخصصة، طباعة وتنزيل، ومتابعة عدد ونوع أجهزة المسح.', 'tag' => 'أساس'],
                ['icon' => '📣', 'title' => 'نشر المحتوى', 'text' => 'بوستات بأولوية وبدج ومراجعة واعتماد، وجدولة، وجمهور مستهدف، وإيصالات تسليم وقراءة وإقرار.', 'tag' => 'محتوى'],
                ['icon' => '🧩', 'title' => '5 أنواع بوستات تفاعلية', 'text' => 'بوست عادي، معرض صور بكاروسيل، استطلاع رأي، فعالية مع تأكيد حضور، وتقييم بالنجوم، مع تفاعلات (قلب، ضحك، إعجاب…) بدل اللايك.', 'tag' => 'جديد'],
                ['icon' => '🎨', 'title' => 'قوالب تصميم للمنظمات والقنوات', 'text' => '15 تصميماً للمنظمات حسب نوعها (فندق، جامعة، مستشفى، تجزئة، شركات) و4 تصميمات للقنوات، وكاروسيل للغلاف بتحكم كامل.', 'tag' => 'جديد'],
                ['icon' => '🧭', 'title' => 'قوالب المنظمات', 'text' => 'إنشاء منظمة جاهزة بأقسامها وقنواتها من قالب لكل صناعة، بدل البدء من الصفر.', 'tag' => 'أساس'],
                ['icon' => '📊', 'title' => 'لوحات تحليلات', 'text' => 'لوحة للسوبر أدمن ولوحة لكل منظمة: مؤشرات مع نسبة التغيّر، رسوم بيانية للنمو والـ QR والأجهزة وقمع التفاعل، بنطاق 7 / 30 / 90 يوم.', 'tag' => 'تحليلات'],
                ['icon' => '🗂️', 'title' => 'CRM على مستويين', 'text' => 'CRM للمنصة (Organizations 360 وPeople وسجل المراجعة) وCRM لكل منظمة (جهات الاتصال وسجل النشاط) مع تقييم صحة المنظمة.', 'tag' => 'جديد'],
                ['icon' => '🎫', 'title' => 'تذاكر الدعم', 'text' => 'شكاوى واقتراحات وطلبات بأولويات وتصنيفات وقواعد SLA وتتبع للردود والإغلاق.', 'tag' => 'دعم'],
                ['icon' => '👥', 'title' => 'شرائح الجمهور', 'text' => 'استهدف البوستات حسب اللغة والدور والعلامة والموقع والقسم.', 'tag' => 'محتوى'],
                ['icon' => '🔐', 'title' => 'أدوار وصلاحيات دقيقة', 'text' => 'سوبر أدمن، أدمن منظمة، مدير، مشرف، موظف، وزائر، مع صلاحيات إضافية لكل عضو وسجل مراجعة لكل عملية.', 'tag' => 'أمان'],
                ['icon' => '✅', 'title' => 'موافقات وتحقق', 'text' => 'رمز تحقق (OTP) عند التسجيل، ومحرك سير عمل لاعتماد الانضمام للقنوات الخاصة.', 'tag' => 'أمان'],
                ['icon' => '🌍', 'title' => 'للجميع وعلى أي جهاز', 'text' => 'عربي وإنجليزي بدعم كامل لـ RTL، وضع داكن، تصميم للموبايل أولاً، وتطبيق ويب قابل للتثبيت (PWA).', 'tag' => 'تجربة'],
                ['icon' => '⭐', 'title' => 'تجربة الزائر', 'text' => 'متابعة المنظمات، الاشتراك في القنوات، مجموعات شخصية ومفضلة، وإشعارات.', 'tag' => 'تجربة'],
            ],
        ],
        'roles' => [
            'title' => 'ثلاث طبقات، كل واحدة بمسؤولياتها',
            'sub' => 'السوبر أدمن مسؤول عن المنصة، وأدمن المنظمة عن منظمته فقط، والزائر عن تجربته.',
            'items' => [
                ['icon' => '🛡️', 'title' => 'مالك المنصة (Super Admin)', 'points' => ['إنشاء المنظمات وإدارة اشتراكاتها (إيقاف / تفعيل / إلغاء)', 'لوحة تحليلات وCRM يرى كل ما يحدث على المنصة', 'سجل مراجعة كامل لكل العمليات', 'لا يتدخل في محتوى المنظمات اليومي']],
                ['icon' => '🏢', 'title' => 'المنظمة (Admin / Manager / Moderator / Staff)', 'points' => ['القنوات والعلامات والمواقع والفريق', 'نشر البوستات بأنواعها وإدارة التذاكر والـ SLA', 'تصميم صفحتها العامة وألوانها وغلافها', 'CRM خاص بجهات الاتصال ونشاط المنظمة']],
                ['icon' => '🙋', 'title' => 'الزائر / العضو', 'points' => ['تصفّح بلا تسجيل، وتسجيل سريع عند الحاجة', 'متابعة المنظمات والاشتراك في القنوات', 'التصويت والتقييم وتأكيد الحضور والتفاعل', 'مجموعات ومفضلة وإشعارات']],
            ],
        ],
        'designs' => [
            'title' => 'كل منظمة بشكلها',
            'sub' => 'الفندق غير الجامعة غير المستشفى. اختر قالباً وغيّر الألوان والغلاف، والزائر يرى صفحة مصمّمة لنشاطك.',
            'cards' => [
                ['icon' => '🏨', 'name' => 'فنادق', 'text' => 'Resort · Concierge · Boutique'],
                ['icon' => '🎓', 'name' => 'جامعات', 'text' => 'Campus · Faculty Hub · Student Life'],
                ['icon' => '🏥', 'name' => 'مستشفيات', 'text' => 'Care · Clinic Guide · Wellness'],
                ['icon' => '🛒', 'name' => 'تجزئة', 'text' => 'Storefront · Offers · Mall Directory'],
                ['icon' => '🏢', 'name' => 'شركات', 'text' => 'Intranet · Newsroom · Departments'],
            ],
            'formats_title' => 'أنواع المحتوى',
            'formats' => [['📝', 'عادي'], ['🖼️', 'معرض صور'], ['📊', 'استطلاع'], ['📅', 'فعالية'], ['⭐', 'تقييم']],
            'styles_title' => 'أشكال الكروت',
            'styles' => ['Classic', 'Large image', 'Gradient', 'Spotlight'],
        ],
        'roadmap' => [
            'title' => 'خارطة الطريق',
            'sub' => 'اللي اتبنى، واللي جاي.',
            'phases' => [
                ['key' => 'now', 'label' => 'متاح الآن', 'items' => ['منظمات متعددة بقنوات وقنوات فرعية', 'منصة QR وتحليلاتها', 'بوستات تفاعلية بخمسة أنواع', 'قوالب تصميم للمنظمات والقنوات', 'لوحات تحليلات وCRM على مستويين', 'تذاكر وSLA وشرائح جمهور', 'OTP وسير عمل لاعتماد الانضمام']],
                ['key' => 'next', 'label' => 'قريباً', 'items' => ['هيكل تنظيمي ديناميكي (وحدات تنظيمية) واستهداف بها', 'أنواع قنوات متقدمة: إدارات، فرق، مشاريع، طوارئ، مجتمع', 'مركز إشعارات موحّد: SMS وPush وSlack وTeams وWebhook', 'اعتماد البوستات عبر محرك سير العمل', 'دردشة لحظية داخل القنوات (WebSockets)', 'تقارير امتثال وتصدير سجلات المراجعة (GDPR / HIPAA)']],
                ['key' => 'later', 'label' => 'لاحقاً', 'items' => ['ملخصات وتوصيات بالذكاء الاصطناعي', 'API موثّق وتطبيقات موبايل أصلية', 'White-label: دومين وهوية خاصة لكل منظمة', 'باقات اشتراك وفوترة (SaaS)', 'استبيانات متقدمة وتحليل نتائجها', 'تشفير بيانات حسّاسة للقطاعات المنظَّمة', 'تكاملات مع الأنظمة الخارجية عبر API']],
            ],
            'note' => 'الخارطة قابلة للتغيير حسب احتياج العملاء.',
        ],
        'principles' => [
            'title' => 'مبادئ المنتج',
            'items' => ['QR أولاً', 'الموبايل أولاً', 'صديق للزائر', 'متعدد المنظمات', 'قابل للتخصيص بالإعدادات', 'جاهز للذكاء الاصطناعي', 'API أولاً', 'آمن وقابل للمراجعة'],
            'tech_title' => 'التقنية',
            'tech' => ['Laravel 12', 'PHP 8', 'MySQL', 'Blade', 'Bootstrap', 'Chart.js', 'PWA', 'Redis Ready', 'Reverb Ready'],
        ],
        'cta' => ['title' => 'جاهز تفتح بوابتك الرقمية؟', 'text' => 'تصفّح المنظمات الحالية، أو ادخل لمساحة عملك.', 'browse' => 'تصفّح المنظمات', 'signin' => 'تسجيل الدخول', 'register' => 'إنشاء حساب'],
        'footer' => 'جميع الحقوق محفوظة',
    ],

    'en' => [
        'dir' => 'ltr',
        'seo' => [
            'title' => 'i-Page | The Digital Front Door for Every Organization',
            'description' => 'i-Page connects organizations with their people through QR codes: news, announcements, polls, events and support in one scan. No app needed.',
            'image_alt' => 'i-Page — The Digital Front Door for Every Organization',
            'locale' => 'en_US',
        ],
        'brand_tag' => 'The Digital Front Door for Every Organization',
        'nav' => ['how' => 'How it works', 'features' => 'Features', 'roles' => 'Roles', 'designs' => 'Designs', 'roadmap' => 'Roadmap', 'browse' => 'Browse organizations', 'signin' => 'Sign in', 'workspace' => 'Open my workspace'],
        'hero' => [
            'eyebrow' => 'QR-powered organizational communication',
            'title_before' => 'The digital front door for',
            'title_after' => 'every organization',
            'words' => ['hotels', 'universities', 'hospitals', 'stores', 'companies', 'government', 'restaurants', 'clubs'],
            'sub' => 'Scan one QR code and within seconds get everything that matters to you: news, announcements, events, polls and support. No app to install, no calls to make.',
            'cta_primary' => 'Browse organizations',
            'cta_secondary' => 'Learn more',
            'phone_org' => 'Nile Hotel',
            'phone_post' => 'Special offer on stays this week',
            'phone_poll' => 'Which breakfast do you prefer?',
            'scan' => 'Scan to join',
        ],
        'stats' => ['organizations' => 'Organizations', 'channels' => 'Public channels', 'posts' => 'Posts', 'scans' => 'QR scans', 'live' => 'Live numbers from the platform'],
        'how' => [
            'title' => 'How it works',
            'sub' => 'Three steps. No friction.',
            'steps' => [
                ['icon' => '📱', 'title' => 'Scan the QR', 'text' => 'Every organization and channel has its own QR code, printed or displayed where it matters, opening the page straight from the browser.'],
                ['icon' => '✨', 'title' => 'Join in seconds', 'text' => 'Quick sign-up by email or mobile with a verification code. Follow the organization and subscribe to the channels you care about.'],
                ['icon' => '🔔', 'title' => 'Stay informed', 'text' => 'News, announcements, polls and events reach you based on your interests and audience, and you respond with a rating, a vote or an RSVP.'],
            ],
        ],
        'industries' => [
            'title' => 'Built for any organization',
            'sub' => 'Not a hotel system. A generic platform with generic terms: Organization, Branch, Department, Channel, Visitor, Member.',
            'items' => ['🏨 Hotels', '🎓 Universities', '🏥 Hospitals', '🛒 Retail', '🏢 Corporate offices', '🏛️ Government', '🏦 Banks', '🍽️ Restaurants', '🛍️ Shopping malls', '🏭 Factories', '⛽ Oil & gas', '🏋️ Clubs & gyms', '🎯 Training centers', '🏫 Schools'],
        ],
        'features' => [
            'title' => 'What exists today',
            'sub' => 'All of this already works in the current platform.',
            'items' => [
                ['icon' => '🏢', 'title' => 'Multi-tenant organizations', 'text' => 'Every organization is fully isolated: brands, locations, departments, channels and sub-channels, with a channel limit per organization.', 'tag' => 'Core'],
                ['icon' => '🔳', 'title' => 'QR platform', 'text' => 'A QR code per channel with a custom welcome message, print and download, and tracking of scan counts and device types.', 'tag' => 'Core'],
                ['icon' => '📣', 'title' => 'Content publishing', 'text' => 'Posts with priority, badges, review and approval, scheduling, targeted audiences, and delivery / read / acknowledgment receipts.', 'tag' => 'Content'],
                ['icon' => '🧩', 'title' => '5 interactive post formats', 'text' => 'Standard, photo gallery carousel, poll, event with RSVP, and star rating, plus reactions (love, haha, like...) instead of a single like.', 'tag' => 'New'],
                ['icon' => '🎨', 'title' => 'Design templates for pages and channels', 'text' => '15 organization designs by industry (hotel, university, hospital, retail, corporate) and 4 channel designs, plus a cover carousel with full control.', 'tag' => 'New'],
                ['icon' => '🧭', 'title' => 'Organization templates', 'text' => 'Create a ready organization with its departments and channels from a per-industry template instead of starting from scratch.', 'tag' => 'Core'],
                ['icon' => '📊', 'title' => 'Analytics dashboards', 'text' => 'A super admin dashboard and one per organization: KPIs with change vs. the previous period, charts for growth, QR, devices and the engagement funnel, over 7 / 30 / 90 days.', 'tag' => 'Analytics'],
                ['icon' => '🗂️', 'title' => 'Two-level CRM', 'text' => 'Platform CRM (Organizations 360, People, audit log) and a CRM per organization (contacts and activity), with an organization health score.', 'tag' => 'New'],
                ['icon' => '🎫', 'title' => 'Support tickets', 'text' => 'Complaints, suggestions and requests with priorities, categories, SLA rules, and tracking of replies and closure.', 'tag' => 'Support'],
                ['icon' => '👥', 'title' => 'Audience segments', 'text' => 'Target posts by language, role, brand, location and department.', 'tag' => 'Content'],
                ['icon' => '🔐', 'title' => 'Precise roles and permissions', 'text' => 'Super admin, organization admin, manager, moderator, staff and visitor, with extra per-member permissions and an audit log for every action.', 'tag' => 'Security'],
                ['icon' => '✅', 'title' => 'Approvals and verification', 'text' => 'OTP verification at sign-up and a workflow engine for approving joins to private channels.', 'tag' => 'Security'],
                ['icon' => '🌍', 'title' => 'For everyone, on any device', 'text' => 'Arabic and English with full RTL, dark mode, mobile-first design and an installable web app (PWA).', 'tag' => 'Experience'],
                ['icon' => '⭐', 'title' => 'Visitor experience', 'text' => 'Follow organizations, subscribe to channels, personal collections and favorites, and notifications.', 'tag' => 'Experience'],
            ],
        ],
        'roles' => [
            'title' => 'Three layers, each with its own responsibility',
            'sub' => 'The super admin runs the platform, an organization admin runs only their organization, and a visitor owns their own experience.',
            'items' => [
                ['icon' => '🛡️', 'title' => 'Platform owner (Super Admin)', 'points' => ['Create organizations and manage subscriptions (suspend / activate / cancel)', 'Analytics and CRM showing everything that happens on the platform', 'A full audit trail of every action', 'Stays out of organizations\' day-to-day content']],
                ['icon' => '🏢', 'title' => 'Organization (Admin / Manager / Moderator / Staff)', 'points' => ['Channels, brands, locations and team', 'Publish every post format, handle tickets and SLA', 'Design the public page, colors and cover', 'A CRM for the organization\'s contacts and activity']],
                ['icon' => '🙋', 'title' => 'Visitor / Member', 'points' => ['Browse without signing in, quick sign-up when needed', 'Follow organizations and subscribe to channels', 'Vote, rate, RSVP and react', 'Collections, favorites and notifications']],
            ],
        ],
        'designs' => [
            'title' => 'Every organization, its own look',
            'sub' => 'A hotel is not a university is not a hospital. Pick a template, tune the colors and cover, and visitors see a page designed for your business.',
            'cards' => [
                ['icon' => '🏨', 'name' => 'Hotels', 'text' => 'Resort · Concierge · Boutique'],
                ['icon' => '🎓', 'name' => 'Universities', 'text' => 'Campus · Faculty Hub · Student Life'],
                ['icon' => '🏥', 'name' => 'Hospitals', 'text' => 'Care · Clinic Guide · Wellness'],
                ['icon' => '🛒', 'name' => 'Retail', 'text' => 'Storefront · Offers · Mall Directory'],
                ['icon' => '🏢', 'name' => 'Corporate', 'text' => 'Intranet · Newsroom · Departments'],
            ],
            'formats_title' => 'Content formats',
            'formats' => [['📝', 'Standard'], ['🖼️', 'Gallery'], ['📊', 'Poll'], ['📅', 'Event'], ['⭐', 'Rating']],
            'styles_title' => 'Card styles',
            'styles' => ['Classic', 'Large image', 'Gradient', 'Spotlight'],
        ],
        'roadmap' => [
            'title' => 'Roadmap',
            'sub' => 'What is built, and what is coming.',
            'phases' => [
                ['key' => 'now', 'label' => 'Available now', 'items' => ['Multi-tenant organizations with channels and sub-channels', 'QR platform and analytics', 'Interactive posts in five formats', 'Design templates for organizations and channels', 'Analytics dashboards and two-level CRM', 'Tickets, SLA and audience segments', 'OTP and workflow approval for joining']],
                ['key' => 'next', 'label' => 'Coming next', 'items' => ['Dynamic organizational units and targeting by unit', 'Advanced channel types: departments, teams, projects, emergency, community', 'Unified notification center: SMS, push, Slack, Teams, webhooks', 'Post approvals on the workflow engine', 'Real-time chat inside channels (WebSockets)', 'Compliance reports and audit-log exports (GDPR / HIPAA)']],
                ['key' => 'later', 'label' => 'Later', 'items' => ['AI summaries and recommendations', 'Documented API and native mobile apps', 'White-label: own domain and identity per organization', 'SaaS plans and billing', 'Advanced surveys and result analysis', 'Encryption of sensitive data for regulated sectors', 'Integrations with external systems through the API']],
            ],
            'note' => 'The roadmap can change based on customer needs.',
        ],
        'principles' => [
            'title' => 'Product principles',
            'items' => ['QR first', 'Mobile first', 'Guest friendly', 'Multi-tenant', 'Configuration driven', 'AI ready', 'API first', 'Secure and auditable'],
            'tech_title' => 'Technology',
            'tech' => ['Laravel 12', 'PHP 8', 'MySQL', 'Blade', 'Bootstrap', 'Chart.js', 'PWA', 'Redis ready', 'Reverb ready'],
        ],
        'cta' => ['title' => 'Ready to open your digital front door?', 'text' => 'Browse the organizations already on the platform, or sign in to your workspace.', 'browse' => 'Browse organizations', 'signin' => 'Sign in', 'register' => 'Create an account'],
        'footer' => 'All rights reserved',
    ],
];
