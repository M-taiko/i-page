<?php

namespace Database\Seeders\Demo;

/**
 * Hand-written demo content for three industries (ADR-016 hierarchy):
 * Tenant → Country channel → City branch channel → Department sub-channels.
 *
 * Tokens in titles/bodies: {brand}, {country}, {city}. Entries may carry a third element
 * 'ar' for Arabic posts. Kept free of Faker so seeding works without dev dependencies.
 */
final class DemoContent
{
    /** Country display names used in channel names (Hilton KSA, Carrefour UAE …). */
    public const COUNTRIES = [
        'Egypt' => ['code' => 'egypt', 'short' => 'Egypt'],
        'Saudi Arabia' => ['code' => 'ksa', 'short' => 'KSA'],
        'United Arab Emirates' => ['code' => 'uae', 'short' => 'UAE'],
        'Jordan' => ['code' => 'jordan', 'short' => 'Jordan'],
    ];

    public const TENANTS = [
        'hilton' => [
            'name' => 'Hilton', 'sector' => 'hotel', 'hq' => ['Cairo', 'Egypt'],
            'description' => 'Hilton hotels and resorts across Egypt, Saudi Arabia and the UAE.',
            'countries' => ['Egypt' => ['Hurghada', 'Sharm El Sheikh', 'Cairo'], 'Saudi Arabia' => ['Jeddah', 'Riyadh'], 'United Arab Emirates' => ['Dubai']],
        ],
        'marriott' => [
            'name' => 'Marriott', 'sector' => 'hotel', 'hq' => ['Dubai', 'United Arab Emirates'],
            'description' => 'Marriott hotels from the Nile to the Gulf.',
            'countries' => ['Egypt' => ['Cairo', 'Luxor'], 'United Arab Emirates' => ['Abu Dhabi'], 'Jordan' => ['Amman']],
        ],
        'carrefour' => [
            'name' => 'Carrefour', 'sector' => 'market', 'hq' => ['Dubai', 'United Arab Emirates'],
            'description' => 'Hypermarkets and supermarkets with fresh food, weekly offers and home delivery.',
            'countries' => ['Egypt' => ['Maadi', 'Alexandria'], 'Saudi Arabia' => ['Jeddah', 'Riyadh'], 'United Arab Emirates' => ['Abu Dhabi', 'Dubai']],
        ],
        'lulu' => [
            'name' => 'LuLu Hypermarket', 'sector' => 'market', 'hq' => ['Abu Dhabi', 'United Arab Emirates'],
            'description' => 'Hypermarkets serving families across the Gulf and Egypt.',
            'countries' => ['United Arab Emirates' => ['Abu Dhabi', 'Al Ain'], 'Saudi Arabia' => ['Riyadh'], 'Egypt' => ['New Cairo']],
        ],
        'gems' => [
            'name' => 'GEMS Education', 'sector' => 'school', 'hq' => ['Dubai', 'United Arab Emirates'],
            'description' => 'International schools with British, American and IB curricula.',
            'countries' => ['United Arab Emirates' => ['Dubai', 'Abu Dhabi'], 'Egypt' => ['New Cairo'], 'Saudi Arabia' => ['Riyadh']],
        ],
        'horizon' => [
            'name' => 'Horizon International Schools', 'sector' => 'school', 'hq' => ['Cairo', 'Egypt'],
            'description' => 'A family of bilingual international schools, KG to Grade 12.',
            'countries' => ['Egypt' => ['Maadi', 'Sheikh Zayed'], 'Saudi Arabia' => ['Jeddah'], 'United Arab Emirates' => ['Sharjah']],
        ],
    ];

    /** Department sub-channels under every branch: [name, type, children]. */
    public const DEPARTMENTS = [
        'hotel' => [
            ['Front Desk', 'public', []],
            ['Restaurants', 'public', ['Italian Restaurant', 'Lobby Café']],
            ['Spa & Pool', 'public', []],
            ['Kids Club', 'public', []],
            ['Emergency & Safety', 'public', []],
            ['Housekeeping', 'private', []],
            ['Staff Room', 'private', []],
        ],
        'market' => [
            ['Weekly Offers', 'public', []],
            ['Fresh Food', 'public', ['Bakery', 'Butcher & Fish']],
            ['Electronics', 'public', []],
            ['Customer Service', 'public', []],
            ['Online Orders & Delivery', 'public', []],
            ['Store Operations', 'private', []],
            ['Staff Room', 'private', []],
        ],
        'school' => [
            ['Announcements', 'public', []],
            ['KG & Primary', 'public', ['KG2', 'Grade 4']],
            ['Secondary School', 'public', ['Grade 9', 'Grade 12']],
            ['Activities & Sports', 'public', []],
            ['School Buses', 'public', []],
            ['Teachers Room', 'private', []],
            ['Administration', 'private', []],
        ],
    ];

    /** The department the branch's posting staff member may post in. */
    public const FRONT_LINE = ['hotel' => 'Front Desk', 'market' => 'Customer Service', 'school' => 'Announcements'];

    /** Posts per department: [title, body] or [title, body, 'ar']. */
    public const POSTS = [
        'hotel' => [
            'Front Desk' => [
                ['Express check-in is live', "Skip the queue at {brand} {city}: scan the QR code at the entrance and complete your check-in from your phone. Your key card will be waiting at the desk."],
                ['Late check-out on request', "Need a little more time? Late check-out until 2 PM is available on request, subject to availability. Just message us here or call extension 0."],
                ['تسجيل وصول سريع', "وفّر وقتك في {brand} {city}: امسح كود الـ QR عند المدخل وأكمل تسجيل الوصول من موبايلك، وكارت الغرفة هيكون جاهز على الريسبشن.", 'ar'],
            ],
            'Restaurants' => [
                ['Tonight\'s dinner buffet', "International dinner buffet from 7 PM to 11 PM with live cooking stations, a grill corner and a dessert table the kids will love."],
                ['Seafood night every Thursday', "Fresh catch of the day, grilled your way. Thursday from 7 PM — book a table at the restaurant reception."],
            ],
            'Italian Restaurant' => [
                ['Fresh pasta week', "Our chef rolls fresh pasta every evening this week: truffle tagliatelle, lobster ravioli and a classic lasagna. Reservations recommended."],
                ['Wood-fired pizza & live music', "Every Friday: wood-fired pizzas, live acoustic music on the terrace and a special kids menu."],
            ],
            'Lobby Café' => [
                ['New specialty coffee menu', "Try our new single-origin coffees and freshly baked croissants, served all day in the lobby."],
                ['قهوة الصباح علينا', "اطلب أي مشروب ساخن قبل الساعة 10 الصبح واحصل على كرواسون مجانًا طوال هذا الأسبوع.", 'ar'],
            ],
            'Spa & Pool' => [
                ['Pool hours & towels', "The main pool is open daily from 8 AM to 7 PM. Towels are available at the pool kiosk and lifeguards are on duty all day."],
                ['Spa weekday offer', "20% off all massages booked Sunday to Wednesday. Book at the spa reception or send us a message here."],
            ],
            'Kids Club' => [
                ['This week at the Kids Club', "Treasure hunt on Monday, pizza-making on Wednesday and a mini disco every evening at 8 PM. Ages 4–12, free for hotel guests."],
                ['Face painting & movie night', "Face painting from 5 PM and a family movie by the pool at 8 PM. Popcorn is on us!"],
            ],
            'Emergency & Safety' => [
                ['Emergency exits and assembly point', "Emergency exits are marked in green on every floor of {brand} {city}. In case of an alarm, use the stairs — never the elevators — and gather at the assembly point in front of the main entrance."],
                ['Fire drill on Wednesday', "A short fire drill will take place on Wednesday at 11 AM. The alarm will sound for about two minutes — please follow our staff's instructions."],
            ],
            'Housekeeping' => [
                ['Room inspection checklist', "The updated inspection checklist is in the housekeeping office. Please complete it for every check-out room before 3 PM."],
                ['VIP arrivals tomorrow', "Three VIP arrivals tomorrow on floors 7 and 8. Welcome amenities must be in place by 12 PM."],
            ],
            'Staff Room' => [
                ['Monthly staff meeting', "The monthly staff meeting is on Monday at 9 AM in the conference room. All department heads must attend."],
                ['Employee of the month', "Congratulations to our front office team lead for outstanding guest reviews this month! 🎉"],
            ],
        ],
        'market' => [
            'Weekly Offers' => [
                ['This week\'s hot deals', "Up to 40% off on rice, oil, pasta and detergents at {brand} {city}. Offers valid until Sunday or while stocks last."],
                ['Weekend family basket', "Save more on the weekend: buy 2 get 1 free on selected juices, snacks and dairy products."],
                ['عروض نهاية الأسبوع', "خصومات حتى 40% على الأرز والزيت والمكرونة والمنظفات في {brand} {city} — العرض ساري حتى الأحد أو نفاد الكمية.", 'ar'],
            ],
            'Fresh Food' => [
                ['Farm-fresh fruit this week', "Mangoes, strawberries and local grapes just arrived. Check our fresh section for daily prices."],
            ],
            'Bakery' => [
                ['Fresh bread every 2 hours', "Our bakery bakes fresh baguettes, pita and croissants every two hours, from 7 AM until closing."],
                ['Birthday cakes to order', "Order a custom birthday cake 24 hours in advance at the bakery counter or through this channel."],
            ],
            'Butcher & Fish' => [
                ['Fresh fish arrives daily', "Fresh fish arrives every morning at 8 AM. We clean and cut it for free — just ask our team."],
                ['Grill packs for the weekend', "Ready-to-grill marinated packs for 4 or 8 people, available Thursday to Saturday."],
            ],
            'Electronics' => [
                ['Back-to-school laptops', "Laptops and tablets for students with free accessories and 0% installments for 12 months."],
                ['Smart TV week', "Up to 30% off selected Smart TVs, plus free delivery and wall mounting."],
            ],
            'Customer Service' => [
                ['New opening hours', "{brand} {city} is now open daily from 8 AM to midnight. Customer service is available at the main entrance."],
                ['Easy returns', "Changed your mind? Return most items within 14 days with your receipt — no questions asked."],
                ['مواعيد العمل الجديدة', "{brand} {city} مفتوح يوميًا من 8 الصبح لحد 12 بالليل، وخدمة العملاء موجودة عند المدخل الرئيسي.", 'ar'],
            ],
            'Online Orders & Delivery' => [
                ['Delivery in 60 minutes', "Order online from {brand} {city} and get your groceries delivered in 60 minutes or less."],
                ['Free delivery this week', "Free delivery on all online orders above the minimum basket value, this week only."],
            ],
            'Store Operations' => [
                ['Inventory count on Sunday', "Full inventory count on Sunday night from 11 PM. All section supervisors must be on site."],
                ['New shelf layout', "The new planogram for dry food aisles starts on Tuesday. Please review it with your team."],
            ],
            'Staff Room' => [
                ['Ramadan shift schedule', "The Ramadan shift schedule is posted in the staff room. Please confirm your shifts with your supervisor by Thursday."],
                ['Safety training', "Mandatory forklift and fire safety training next Wednesday at 10 AM."],
            ],
        ],
        'school' => [
            'Announcements' => [
                ['Welcome back to school!', "We are excited to welcome all our students back to {brand} {city}. Classes start at 7:45 AM sharp — please be on time."],
                ['Parent–teacher conferences', "Parent–teacher conferences are on Thursday from 2 PM to 6 PM. Book your slot through the school app."],
                ['اجتماع أولياء الأمور', "اجتماع أولياء الأمور يوم الخميس من 2 الظهر لحد 6 المغرب، احجز ميعادك من خلال تطبيق المدرسة.", 'ar'],
            ],
            'KG & Primary' => [
                ['Reading week', "Primary students will celebrate Reading Week with book swaps, storytelling and a costume day on Thursday."],
            ],
            'KG2' => [
                ['Colors and shapes week', "This week KG2 is learning colors and shapes. Please send one empty cereal box for our art project."],
                ['Healthy snack reminder', "Please pack a healthy snack: fruits, vegetables or sandwiches. No sweets or fizzy drinks, thank you!"],
            ],
            'Grade 4' => [
                ['Science fair projects', "Grade 4 science fair projects are due on the 20th. Topics were shared in the homework folder."],
                ['Field trip to the museum', "Grade 4 will visit the science museum next Tuesday. Please sign and return the permission slip."],
            ],
            'Secondary School' => [
                ['Mock exams schedule', "Mock exams for secondary students start on Sunday. The full timetable is available on the school portal."],
            ],
            'Grade 9' => [
                ['Coding club starts', "A new after-school coding club for Grade 9 starts on Monday at 2:30 PM in the computer lab."],
            ],
            'Grade 12' => [
                ['University fair', "Representatives from 25 universities will visit our campus on Wednesday. Grade 12 students and parents are welcome."],
                ['Final exams timetable', "The final exams timetable for Grade 12 is now available. Revision sessions run every afternoon."],
            ],
            'Activities & Sports' => [
                ['Sports day is coming!', "Sports day is on Saturday at 9 AM: races, football and a family tug of war. Wear your house colors!"],
                ['Swimming team trials', "Swimming team trials for Grades 5–9 on Tuesday after school at the pool."],
            ],
            'School Buses' => [
                ['Bus route changes', "Buses 3 and 7 have new routes from Sunday. Updated pick-up times were sent to parents by SMS."],
                ['Live bus tracking', "You can now track your child's bus live from the school app."],
            ],
            'Teachers Room' => [
                ['Staff meeting on Wednesday', "All teachers: staff meeting on Wednesday at 2:15 PM in the library. Agenda: report cards and the inspection visit."],
                ['Report cards deadline', "Term report cards must be submitted on the portal by Sunday 8 PM."],
            ],
            'Administration' => [
                ['Fees reminder for Term 2', "Term 2 fee reminders will be sent to parents on Monday. Please update the finance tracker."],
            ],
        ],
    ];

    /** Country-level posts (cascade to every branch in the country). */
    public const COUNTRY_POSTS = [
        'hotel' => [
            ['Summer staycation offers in {country}', "Book two nights at any {brand} hotel in {country} and get the third night free, breakfast included."],
            ['Ramadan at {brand} {country}', "Iftar and suhoor buffets at every {brand} hotel in {country} throughout the holy month. Reserve early!"],
        ],
        'market' => [
            ['National day mega sale', "Celebrate with up to 50% off across all {brand} stores in {country} this weekend."],
            ['Ramadan baskets', "Ready Ramadan baskets are now available in every {brand} store in {country}, starting from great prices."],
        ],
        'school' => [
            ['Admissions open for next year', "Admissions are open at all {brand} campuses in {country}. Book a school tour on our website."],
            ['Term calendar for {country}', "The term calendar for all {brand} schools in {country} is now available, including holidays and exam weeks."],
        ],
    ];

    /** Tenant posts. */
    public const TENANT_POSTS = [
        'hotel' => [
            'page' => ['Welcome to {brand}', "Discover our hotels across the region. Scan the QR code at any {brand} hotel to follow its channels and get offers, events and updates."],
            'all' => ['{brand} Honors double points', "Members earn double points on every stay this month at all {brand} hotels."],
            'staff' => ['Thank you, team!', "Thanks to every team member for a record season. A performance bonus will be paid with this month's salary."],
        ],
        'market' => [
            'page' => ['Welcome to {brand}', "Fresh food, the best prices and fast delivery. Follow your nearest {brand} store to never miss an offer."],
            'all' => ['Loyalty points week', "Earn triple loyalty points on every purchase this week at all {brand} stores."],
            'staff' => ['New uniform from next month', "New uniforms will be distributed next month. Please confirm your size with your supervisor."],
        ],
        'school' => [
            'page' => ['Welcome to {brand}', "Inspiring students to learn, create and lead. Follow your child's school to stay updated with classes, trips and events."],
            'all' => ['Mid-term break', "All {brand} schools will close for mid-term break next week. Classes resume the following Sunday."],
            'staff' => ['Professional development day', "All staff: professional development day on Saturday. Sessions on assessment and well-being."],
        ],
    ];

    /** One poll per branch: [department, question, options]. */
    public const POLLS = [
        'hotel' => [
            ['Restaurants', 'Which theme night should we add next?', ['Seafood night', 'Oriental night', 'BBQ by the pool', 'Asian night']],
            ['Kids Club', 'What should the kids do on Friday?', ['Treasure hunt', 'Pool games', 'Arts & crafts', 'Movie night']],
        ],
        'market' => [
            ['Weekly Offers', 'Which offer would you like next week?', ['Dairy & cheese', 'Meat & poultry', 'Cleaning products', 'Snacks & drinks']],
            ['Online Orders & Delivery', 'When do you prefer your delivery?', ['Morning', 'Afternoon', 'Evening', 'Late night']],
        ],
        'school' => [
            ['Activities & Sports', 'Which after-school club should we open?', ['Robotics', 'Drama', 'Chess', 'Football academy']],
            ['Announcements', 'Best time for parent workshops?', ['Weekday mornings', 'Weekday evenings', 'Saturday morning']],
        ],
    ];

    /** Visitor comments per sector (mixed English / Arabic, as real feeds are). */
    public const COMMENTS = [
        'hotel' => [
            'Amazing stay, the staff were so friendly 👏', 'Is the pool heated in winter?', 'Can I book a table for 6 people tonight?',
            'We loved the kids club, my daughter didn\'t want to leave 😄', 'Is late check-out available on Friday?', 'Best breakfast buffet in town!',
            'Do you have vegetarian options?', 'Booked! See you next week 🙌', 'What time does the spa close?', 'Thank you for the warm welcome 🌸',
            'المكان تحفة والخدمة ممتازة 👌', 'هل فيه عروض للعائلات؟', 'ينفع أحجز من هنا؟', 'أحلى إقامة قضيتها، شكرًا لكل الفريق', 'السعر شامل الفطار؟',
        ],
        'market' => [
            'Is this offer available online too?', 'Great prices this week 👍', 'Do you deliver to Zayed / outside the city?', 'The bakery bread is the best 🥖',
            'Please bring back the cheese offer!', 'Is the TV offer valid on all sizes?', 'Delivery came in 40 minutes, impressive 👏',
            'Can I pay by card on delivery?', 'Ordered already, thanks!', 'Do you have gluten-free products?',
            'العرض ده لحد إمتى؟', 'التوصيل سريع جدًا شكرًا ليكم', 'فيه نفس العرض في فرع تاني؟', 'الأسعار ممتازة الأسبوع ده 👏', 'ممكن أطلب أونلاين؟',
        ],
        'school' => [
            'Thank you for the update!', 'Will there be a bus for the field trip?', 'My son is so excited 😊', 'Can parents attend sports day?',
            'Where can we download the timetable?', 'Great initiative, thank you teachers 👏', 'Is the coding club free?', 'Noted, thank you!',
            'What time does it finish?', 'Proud of our kids! 🎉',
            'شكرًا على التنبيه 🙏', 'هل في مواصلات للرحلة؟', 'بنتي مبسوطة جدًا بالنشاط ده', 'ممكن نعرف الميعاد بالظبط؟', 'مجهود رائع من المدرسين 👏',
        ],
    ];

    /** Comments from staff in private channels. */
    public const STAFF_COMMENTS = ['Noted 👍', 'Will do.', 'Done ✅', 'Thanks for the reminder', 'تمام، تم', 'Received, thank you', 'On it!', 'Confirmed.'];

    /** Replies from the branch team to visitor questions. */
    public const REPLIES = [
        'Thank you for your feedback! 🙏', 'Yes, absolutely — just message us and we\'ll arrange it.', 'You\'re welcome, see you soon!',
        'Great question — please contact customer service and we\'ll help right away.', 'شكرًا لحضرتك، يسعدنا خدمتك دايمًا 🌹', 'أكيد، ابعتلنا رسالة وهنرتب كل حاجة.',
    ];

    /** Visitors: [first, last, country]. */
    public const VISITORS = [
        ['Ahmed', 'Hassan', 'Egypt'], ['Sara', 'Mahmoud', 'Egypt'], ['Omar', 'Khaled', 'Egypt'], ['Mona', 'Adel', 'Egypt'], ['Youssef', 'Ali', 'Egypt'],
        ['Laila', 'Samir', 'Egypt'], ['Karim', 'Nabil', 'Egypt'], ['Nour', 'El-Sherif', 'Egypt'], ['Hany', 'Fouad', 'Egypt'], ['Dina', 'Ragab', 'Egypt'],
        ['Mostafa', 'Gamal', 'Egypt'], ['Salma', 'Tarek', 'Egypt'], ['Mariam', 'Ezzat', 'Egypt'], ['Tamer', 'Lotfy', 'Egypt'],
        ['Abdullah', 'Al-Qahtani', 'Saudi Arabia'], ['Reem', 'Al-Harbi', 'Saudi Arabia'], ['Faisal', 'Al-Otaibi', 'Saudi Arabia'], ['Noura', 'Al-Dosari', 'Saudi Arabia'],
        ['Khalid', 'Al-Ghamdi', 'Saudi Arabia'], ['Lama', 'Al-Shehri', 'Saudi Arabia'], ['Turki', 'Al-Mutairi', 'Saudi Arabia'], ['Hessa', 'Al-Zahrani', 'Saudi Arabia'],
        ['Saeed', 'Al-Mansoori', 'United Arab Emirates'], ['Fatima', 'Al-Hammadi', 'United Arab Emirates'], ['Rashid', 'Al-Ketbi', 'United Arab Emirates'],
        ['Aisha', 'Al-Suwaidi', 'United Arab Emirates'], ['Priya', 'Nair', 'United Arab Emirates'], ['James', 'Carter', 'United Arab Emirates'],
        ['Maria', 'Santos', 'United Arab Emirates'], ['Hamdan', 'Al-Falasi', 'United Arab Emirates'], ['Elena', 'Petrova', 'United Arab Emirates'],
        ['Zaid', 'Haddad', 'Jordan'], ['Rania', 'Khoury', 'Jordan'], ['Ibrahim', 'Nasser', 'Jordan'], ['Lina', 'Saleh', 'Jordan'],
    ];

    /** Names for team members (rotated). */
    public const STAFF_NAMES = [
        ['Mohamed', 'Fathy'], ['Hossam', 'Ibrahim'], ['Yasmin', 'Ashraf'], ['Amr', 'Shawky'], ['Heba', 'Kamal'], ['Walid', 'Saber'],
        ['Rana', 'Hegazy'], ['Sherif', 'Mounir'], ['Aya', 'Mostafa'], ['Bassem', 'Youssef'], ['Ola', 'Hamdy'], ['Ziad', 'Emad'],
        ['Huda', 'Al-Shammari'], ['Majed', 'Al-Anazi'], ['Sultan', 'Al-Rashid'], ['Maha', 'Al-Subaie'], ['Ali', 'Al-Nuaimi'], ['Shamma', 'Al-Mazrouei'],
        ['Rohit', 'Sharma'], ['Grace', 'Dela Cruz'], ['Tariq', 'Odeh'], ['Dana', 'Masri'], ['Nadia', 'Farouk'], ['Kareem', 'Ayman'],
    ];
}
