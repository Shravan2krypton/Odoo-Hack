-- ============================================================
-- GlobeTrotter India — Seed Data
-- ============================================================

USE `globetrotter`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `community_posts`;
TRUNCATE TABLE `expenses`;
TRUNCATE TABLE `trip_activities`;
TRUNCATE TABLE `activities`;
TRUNCATE TABLE `itinerary_sections`;
TRUNCATE TABLE `trips`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `cities`;
TRUNCATE TABLE `countries`;
TRUNCATE TABLE `regions`;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. Regions of India
-- ============================================================
INSERT INTO `regions` (id, name, tagline, description, image_url) VALUES
(1, 'North India & Himalayas', 'Snow Peaks, Spiritual Rivers & Majestic Forts', 'From the snow-capped pinnacles of Ladakh and Himachal to the holy banks of the Ganges in Varanasi and Rishikesh.', 'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?w=800&auto=format&fit=crop&q=80'),
(2, 'South India & Coastal Tropics', 'Tranquil Backwaters, Ancient Temples & Spice Hills', 'Lush green tea estates of Munnar, serene houseboat cruises in Alleppey, and grand Dravidian architecture in Hampi.', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=800&auto=format&fit=crop&q=80'),
(3, 'Western India & Deserts', 'Palaces of Royalty, Golden Dunes & Vibrant Cities', 'The royal lakes of Udaipur, golden sandstone forts of Jaisalmer, and the buzzing metropolis of Mumbai.', 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=800&auto=format&fit=crop&q=80'),
(4, 'East & North-East Jewels', 'Living Root Bridges, Tea Valleys & Buddhist Monasteries', 'The rolling tea estates of Darjeeling, misty hills of Meghalaya, and mystical monasteries of Sikkim.', 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&auto=format&fit=crop&q=80'),
(5, 'Islands & Coral Paradises', 'Turquoise Waters, White Sands & Virgin Mangroves', 'World-class scuba diving, bio-luminescence, and tropical paradise in Andaman & Nicobar.', 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?w=800&auto=format&fit=crop&q=80'),
(6, 'Central Heritage Heartland', 'Temples of Love, Tiger Reserves & Historic Citadels', 'UNESCO World Heritage monuments of Khajuraho, Gwalior Fort, and pristine tiger reserves of Madhya Pradesh.', 'https://images.unsplash.com/photo-1564507592333-c60657eea523?w=800&auto=format&fit=crop&q=80');

-- ============================================================
-- 2. Countries (Primary India + International Options)
-- ============================================================
INSERT INTO `countries` (id, name, phone_code) VALUES
(1, 'India', '91'),
(2, 'United Arab Emirates', '971'),
(3, 'Thailand', '66'),
(4, 'Singapore', '65'),
(5, 'United Kingdom', '44'),
(6, 'United States', '1'),
(7, 'Maldives', '960'),
(8, 'Nepal', '977'),
(9, 'Sri Lanka', '94'),
(10, 'Australia', '61');

-- ============================================================
-- 3. Indian Destinations & Cities (18 Cities)
-- ============================================================
INSERT INTO `cities` (id, country_id, region_id, name, state, population, cost_index, popularity_score, avg_daily_cost, image_url, description, best_time_to_visit) VALUES
(1, 1, 3, 'Jaipur', 'Rajasthan', 3100000, 1.20, 96, 2800, 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=800&auto=format&fit=crop&q=80', 'The Pink City — magnificent Amber Fort, Hawa Mahal, bustling bazaars, and royal heritage.', 'Oct - Mar'),
(2, 1, 1, 'Leh Ladakh', 'Ladakh', 30000, 1.50, 94, 3800, 'https://images.unsplash.com/photo-1581793745862-99fde7fa73d2?w=800&auto=format&fit=crop&q=80', 'Land of High Passes — Pangong Tso crystal lake, rugged valleys, magnetic hill, and ancient gompas.', 'May - Sep'),
(3, 1, 3, 'Goa', 'Goa', 1500000, 1.40, 98, 3200, 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=800&auto=format&fit=crop&q=80', 'Sun, Sand and Spice — Golden beaches, Portuguese cathedrals, water sports, and sunset shacks.', 'Nov - Feb'),
(4, 1, 1, 'Varanasi', 'Uttar Pradesh', 1400000, 0.85, 92, 1800, 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?w=800&auto=format&fit=crop&q=80', 'The Spiritual Capital — Mesmerizing evening Ganga Aarti, timeless ghats, and silk weaving.', 'Oct - Mar'),
(5, 1, 2, 'Alleppey (Alappuzha)', 'Kerala', 175000, 1.30, 93, 3000, 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=800&auto=format&fit=crop&q=80', 'Venice of the East — Houseboat cruises along serene palm-fringed backwaters and Ayurvedic wellness.', 'Sep - Mar'),
(6, 1, 1, 'Manali', 'Himachal Pradesh', 35000, 1.15, 95, 2600, 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=800&auto=format&fit=crop&q=80', 'Valley of the Gods — Solang adventure sports, snow-covered Rohtang Pass, and scenic apple orchards.', 'All Year'),
(7, 1, 3, 'Udaipur', 'Rajasthan', 500000, 1.45, 95, 3500, 'https://images.unsplash.com/photo-1615836245337-f5b9b2303f10?w=800&auto=format&fit=crop&q=80', 'City of Lakes — Romantic boat rides on Lake Pichola, towering City Palace, and sunset rooftop cafes.', 'Sep - Mar'),
(8, 1, 1, 'Rishikesh', 'Uttarakhand', 105000, 0.95, 91, 2100, 'https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?w=800&auto=format&fit=crop&q=80', 'Yoga Capital & Adventure Hub — White-water Ganges rafting, cliff jumping, and spiritual ashrams.', 'Sep - Jun'),
(9, 1, 3, 'Mumbai', 'Maharashtra', 21000000, 1.80, 90, 4500, 'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?w=800&auto=format&fit=crop&q=80', 'City of Dreams — Gateway of India, Marine Drive Queen’s Necklace, Bollywood, and street food.', 'Oct - Mar'),
(10, 1, 1, 'Srinagar', 'Jammu & Kashmir', 1200000, 1.35, 92, 3100, 'https://images.unsplash.com/photo-1598091383021-15ddea10925d?w=800&auto=format&fit=crop&q=80', 'Paradise on Earth — Dal Lake shikara rides, Mughal Gardens, floating vegetable markets, and snow.', 'Apr - Oct'),
(11, 1, 2, 'Munnar', 'Kerala', 68000, 1.10, 89, 2400, 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?w=800&auto=format&fit=crop&q=80', 'Tea Country Haven — Rolling emerald tea plantations, misty mountain peaks, and cascading waterfalls.', 'Sep - May'),
(12, 1, 4, 'Darjeeling', 'West Bengal', 130000, 1.10, 88, 2300, 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&auto=format&fit=crop&q=80', 'Queen of the Hills — UNESCO Toy Train, Tiger Hill sunrise over Mt. Kanchenjunga, and tea estates.', 'Mar - Jun & Oct - Dec'),
(13, 1, 2, 'Hampi', 'Karnataka', 25000, 0.90, 90, 1900, 'https://images.unsplash.com/photo-1600100397608-f010f4439c2d?w=800&auto=format&fit=crop&q=80', 'Boulder Wonderland — Vijayanagara empire ruins, Virupaksha Temple, and hippie coracle boat rides.', 'Oct - Feb'),
(14, 1, 5, 'Andaman & Nicobar (Havelock)', 'Andaman Islands', 10000, 1.90, 93, 4800, 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?w=800&auto=format&fit=crop&q=80', 'Radhanagar Beach & Coral Reefs — World-class scuba diving, clear turquoise water, and sea walking.', 'Oct - May'),
(15, 1, 1, 'Agra', 'Uttar Pradesh', 1700000, 1.00, 97, 2200, 'https://images.unsplash.com/photo-1564507592333-c60657eea523?w=800&auto=format&fit=crop&q=80', 'Home of the Taj Mahal — Eternal monument of love, Agra Fort, and rich Mughal culinary flavours.', 'Oct - Mar'),
(16, 1, 1, 'Amritsar', 'Punjab', 1200000, 0.90, 91, 1900, 'https://images.unsplash.com/photo-1588096344356-9a25032338cf?w=800&auto=format&fit=crop&q=80', 'The Golden City — Divine Golden Temple Harmandir Sahib, community langar, and Wagah Border.', 'Oct - Mar'),
(17, 1, 4, 'Shillong', 'Meghalaya', 150000, 1.15, 87, 2400, 'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?w=800&auto=format&fit=crop&q=80', 'Scotland of the East — Living root bridges of Cherrapunji, crystal Umngot river in Dawki, and rock music.', 'Sep - May'),
(18, 1, 3, 'Jaisalmer', 'Rajasthan', 70000, 1.25, 91, 2700, 'https://images.unsplash.com/photo-1579606032834-0d86940a0c4f?w=800&auto=format&fit=crop&q=80', 'The Golden City — Thar Desert camel safaris, starlit camping, and the living fort of Jaisalmer.', 'Oct - Mar');

-- ============================================================
-- 4. Activities Across Indian Cities (36+ Curated Experiences)
-- ============================================================
INSERT INTO `activities` (id, city_id, name, category, cost, duration, image_url, description) VALUES
-- Jaipur (id=1)
(1, 1, 'Amber Fort Heritage Walk & Elephant View', 'Sightseeing', 500.00, 180, 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=500', 'Explore the grand Rajput architecture, mirror palace (Sheesh Mahal), and ramparts overlooking Maota Lake.'),
(2, 1, 'Hawa Mahal & Johari Bazaar Street Food Trail', 'Culture', 350.00, 150, 'https://images.unsplash.com/photo-1609137144813-7d9921338f24?w=500', 'Photograph the honeycomb facade and taste famous pyaz kachoris and lassi in the old city.'),

-- Leh Ladakh (id=2)
(3, 2, 'Pangong Tso Lake Day Trip & Stargazing', 'Adventure', 2500.00, 480, 'https://images.unsplash.com/photo-1581793745862-99fde7fa73d2?w=500', 'Drive through Chang La Pass to reach the world’s highest saltwater lake with color-shifting blue waters.'),
(4, 2, 'Nubra Valley Camel Safari & Diskit Monastery', 'Adventure', 1800.00, 360, 'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?w=500', 'Ride double-humped Bactrian camels among silver sand dunes beneath snowy Himalayan peaks.'),

-- Goa (id=3)
(5, 3, 'Scuba Diving & Dolphin Cruise at Grand Island', 'Adventure', 2200.00, 300, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=500', 'Dive into underwater coral reefs, spot playful dolphins, and enjoy a seaside BBQ on the island.'),
(6, 3, 'Old Goa Portuguese Heritage & Sunset Cruise', 'Culture', 600.00, 180, 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=500', 'Visit Basilica of Bom Jesus, Se Cathedral, followed by a Mandovi river sunset cruise with folk dance.'),

-- Varanasi (id=4)
(7, 4, 'Sunrise Boat Ride on River Ganga', 'Spiritual', 400.00, 120, 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?w=500', 'Glide past ancient ghats at dawn, witness morning rituals, and see the golden sun rise over the Ganges.'),
(8, 4, 'Grand Dashashwamedh Ghat Maha Aarti', 'Spiritual', 0.00, 90, 'https://images.unsplash.com/photo-1627894483216-2138af692e32?w=500', 'Experience the world-renowned synchronized evening fire prayer ceremony filled with chants and brass lamps.'),

-- Alleppey (id=5)
(9, 5, 'Luxury Overnight Houseboat Cruise in Vembanad Lake', 'Wellness', 4500.00, 720, 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=500', 'Sail through tranquil canals in a traditional Kettuvallam boat with authentic Kerala meals served on board.'),
(10, 5, 'Kayaking through Narrow Village Canals', 'Adventure', 800.00, 150, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=500', 'Paddle gently through hidden water channels, paddy fields, and observe local duck farming and coir making.'),

-- Manali (id=6)
(11, 6, 'Solang Valley Paragliding & Zorbing', 'Adventure', 1800.00, 180, 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=500', 'Soar high above pine forests and green meadows with tandem paragliding and giant zorbing balls.'),
(12, 6, 'Rohtang Pass Snow Scooter & Skiing Tour', 'Adventure', 2400.00, 360, 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=500', 'Ascend to 13,058 ft for panoramic Himalayan glaciers, skiing slopes, and pristine fresh snow.'),

-- Udaipur (id=7)
(13, 7, 'Lake Pichola Sunset Boat Ride & Jag Mandir Island', 'Sightseeing', 750.00, 120, 'https://images.unsplash.com/photo-1615836245337-f5b9b2303f10?w=500', 'Romantic cruise past the illuminated City Palace, Lake Palace, and marble pavilion gardens.'),
(14, 7, 'City Palace Museum & Bagore Ki Haveli Folk Dance', 'Culture', 450.00, 180, 'https://images.unsplash.com/photo-1609137144813-7d9921338f24?w=500', 'Explore 400-year-old royal royal apartments and witness the famous Rajasthani Dharohar puppet and fire dance.'),

-- Rishikesh (id=8)
(15, 8, 'Shivpuri to Marine Drive White Water River Rafting (16km)', 'Adventure', 900.00, 180, 'https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?w=500', 'Conquer Grade III & IV rapids like Roller Coaster and Golf Course with professional river guides.'),
(16, 8, 'Beatles Ashram Meditation & Parmarth Niketan Aarti', 'Spiritual', 300.00, 150, 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=500', 'Walk through the graffiti-covered Chaurasi Kutia ashram where the Beatles composed the White Album.'),

-- Mumbai (id=9)
(17, 9, 'South Mumbai Colonial Heritage & Marine Drive Walk', 'Sightseeing', 200.00, 180, 'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?w=500', 'Stroll past Gateway of India, Victoria Terminus, Colaba Causeway, and watch the sunset at Marine Drive.'),
(18, 9, 'Elephanta Island Rock-cut Caves Ferry Tour', 'Culture', 650.00, 240, 'https://images.unsplash.com/photo-1600100397608-f010f4439c2d?w=500', 'Take a 1-hour harbour boat to the UNESCO World Heritage 5th-century rock-cut Shiva sculptures.'),

-- Srinagar (id=10)
(19, 10, 'Dal Lake Gondola Shikara & Floating Garden Tour', 'Sightseeing', 600.00, 120, 'https://images.unsplash.com/photo-1598091383021-15ddea10925d?w=500', 'Glide across serene lotus-dotted waters, pass floating bazaars, and stay in traditional cedar wood houseboats.'),
(20, 10, 'Mughal Gardens (Shalimar & Nishat Bagh) Exploration', 'Culture', 100.00, 150, 'https://images.unsplash.com/photo-1588096344356-9a25032338cf?w=500', 'Terraced Persian garden landscaping with cascading fountains and centuries-old Chinar trees.'),

-- Munnar (id=11)
(21, 11, 'Kolukkumalai Tea Estate Sunrise Jeep Safari', 'Adventure', 1400.00, 240, 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?w=500', 'Off-road 4x4 drive to the world’s highest organic tea plantation for an unforgettable cloud-level sunrise.'),
(22, 11, 'Eravikulam National Park Nilgiri Tahr Trek', 'Sightseeing', 350.00, 180, 'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?w=500', 'Spot the endangered mountain goat species amidst rolling shola grasslands and Anamudi peak.'),

-- Darjeeling (id=12)
(23, 12, 'Tiger Hill Sunrise & Himalayan Toy Train Joy Ride', 'Sightseeing', 850.00, 210, 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=500', 'Witness golden sun rays illuminate Mount Kanchenjunga peak followed by the iconic steam engine ride.'),
(24, 12, 'Happy Valley Organic Tea Tasting & Factory Tour', 'Culture', 300.00, 90, 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?w=500', 'Learn the artisanal orthodox tea making process and sip fresh first-flush Darjeeling champagne tea.'),

-- Hampi (id=13)
(25, 13, 'Bouldering & Coracle Ride across Tungabhadra River', 'Adventure', 500.00, 120, 'https://images.unsplash.com/photo-1600100397608-f010f4439c2d?w=500', 'Spin across the river in circular reed boats and climb giant granite boulders to Matanga Hill.'),
(26, 13, 'Vijaya Vittala Stone Chariot & Musical Pillars Tour', 'Culture', 250.00, 180, 'https://images.unsplash.com/photo-1600100397608-f010f4439c2d?w=500', 'Marvel at the iconic monolithic stone chariot and the 56 resonant granite musical pillars.'),

-- Andaman (id=14)
(27, 14, 'Scuba Diving at Elephant Beach Coral Reef', 'Adventure', 3500.00, 240, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=500', 'Explore vibrant live coral reefs, sea anemones, clownfish, and sea turtles with PADI certified instructors.'),
(28, 14, 'Radhanagar Beach Sunset & Sea Walk', 'Wellness', 1200.00, 120, 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?w=500', 'Voted Asia’s best beach by Time Magazine — pristine soft white sand and crystal turquoise lagoons.'),

-- Agra (id=15)
(29, 15, 'Taj Mahal Sunrise Guided VIP Tour', 'Sightseeing', 500.00, 180, 'https://images.unsplash.com/photo-1564507592333-c60657eea523?w=500', 'Beat the crowds and witness the white marble mausoleum change color from soft pink to radiant pearl white.'),
(30, 15, 'Agra Red Fort & Mehtab Bagh Sunset View', 'Culture', 400.00, 150, 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=500', 'Explore Emperor Shah Jahan’s palace prison and gaze at the Taj Mahal reflection across the Yamuna River.'),

-- Amritsar (id=16)
(31, 16, 'Golden Temple Spiritual Darshan & Langar Kitchen Tour', 'Spiritual', 0.00, 180, 'https://images.unsplash.com/photo-1588096344356-9a25032338cf?w=500', 'Bow at the sanctum sanctorum covered in pure gold and witness the world’s largest free community kitchen.'),
(32, 16, 'Wagah Border Beating Retreat Ceremony VIP Gallery', 'Culture', 100.00, 180, 'https://images.unsplash.com/photo-1588096344356-9a25032338cf?w=500', 'Feel electrifying patriotic energy at the ceremonial border parade with high-kicking drill routines.'),

-- Shillong (id=17)
(33, 17, 'Double Decker Living Root Bridge Trek (Nongriat)', 'Adventure', 600.00, 360, 'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?w=500', 'Hike down 3,500 stone stairs into the rainforest to see the 150-year-old living bio-engineered root bridges.'),
(34, 17, 'Dawki Crystal Clear Umngot River Boating', 'Sightseeing', 500.00, 120, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=500', 'Float on water so transparent that boats appear to be flying in the air along the Indo-Bangladesh border.'),

-- Jaisalmer (id=18)
(35, 18, 'Sam Sand Dunes Camel Safari & Desert Camp Cultural Night', 'Adventure', 2200.00, 360, 'https://images.unsplash.com/photo-1579606032834-0d86940a0c4f?w=500', 'Sunset camel ride over golden ripples followed by Rajasthani Kalbelia dance, bonfire, and desert buffet.'),
(36, 18, 'Sonar Qila (Jaisalmer Living Fort) & Patwon Ki Haveli Walk', 'Culture', 200.00, 150, 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=500', 'Wander through the world’s only surviving living fort where 4,000 residents still reside in yellow sandstone palaces.');

-- ============================================================
-- 5. Indian Sample Users (password for all: Admin@1234 / password)
-- ============================================================
INSERT INTO `users` (id, first_name, last_name, email, password_hash, phone, country_id, city_id, extra_info, role, photo_url) VALUES
(1, 'Aarav', 'Sharma', 'admin@globetrotter.in',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- 'password'
 '+91-9876543210', 1, 9, 'Senior Travel Curator & Platform Administrator', 'admin',
 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200'),

(2, 'Priya', 'Patel', 'priya@example.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '+91-9820011223', 1, 1, 'Heritage explorer, foodie, and amateur photographer. Visited 14 states.', 'user',
 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200'),

(3, 'Rohan', 'Verma', 'rohan@example.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '+91-9711223344', 1, 8, 'Himalayan trekker, motorcyclist, and mountain lover.', 'user',
 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200'),

(4, 'Ananya', 'Iyer', 'ananya@example.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '+91-9444112233', 1, 5, 'Solo woman traveler, coffee enthusiast, and cultural storyteller.', 'user',
 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200');

-- ============================================================
-- 6. Sample Trips for Indian Travelers
-- ============================================================
INSERT INTO `trips` (id, user_id, country_id, city_id, name, description, notes, start_date, end_date, cover_photo, total_budget, status, is_public, share_slug) VALUES
(1, 2, 1, 1, 'Royal Rajasthan Heritage Expedition',
 'A 10-day majestic circuit through the palaces of Jaipur, the lakes of Udaipur, and the golden sand dunes of Jaisalmer.',
 'Remember to carry lightweight cottons, sunglasses, and camera batteries. Pre-booked desert camp in Sam.',
 '2025-11-10', '2025-11-20',
 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=800',
 45000.00, 'planned', 1, 'royal-rajasthan-priya'),

(2, 2, 1, 5, 'Kerala Backwaters & Mist Tea Retreat',
 'Relaxing sojourn through the emerald tea slopes of Munnar and private houseboat cruise in the Alleppey lagoons.',
 'Houseboat operator: Kumarakom luxury line. Ayurveda massage booked for day 3.',
 '2024-12-05', '2024-12-12',
 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=800',
 32000.00, 'completed', 1, 'kerala-retreat-priya'),

(3, 3, 1, 2, 'Great Himalayan Ladakh Bike Odyssey',
 'Conquering the highest motorable passes in the world from Leh to Nubra Valley and surreal Pangong Tso Lake.',
 'Royal Enfield Himalayan rented in Leh. Acclimatization rest mandatory for 48 hours.',
 '2026-06-15', '2026-06-25',
 'https://images.unsplash.com/photo-1581793745862-99fde7fa73d2?w=800',
 55000.00, 'planned', 1, 'ladakh-bike-odyssey-rohan'),

(4, 4, 1, 4, 'Spiritual Ganges & Yoga Awakening',
 'Finding inner peace along the sacred ghats of Varanasi and river rafting ashrams of Rishikesh.',
 'Evening Aarti seats reserved at Dashashwamedh. Yoga retreat at Parmarth Niketan.',
 '2025-02-14', '2025-02-21',
 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?w=800',
 22000.00, 'ongoing', 1, 'spiritual-ganges-ananya');

-- ============================================================
-- 7. Itinerary Sections (Multi-Destination Stops)
-- ============================================================
INSERT INTO `itinerary_sections` (id, trip_id, city_id, section_name, start_date, end_date, budget, order_index, notes) VALUES
-- Trip 1: Royal Rajasthan (trip_id=1)
(1, 1, 1, 'Jaipur — The Pink City Grandeur', '2025-11-10', '2025-11-13', 14000.00, 1, 'Stay near Hawa Mahal. Explore Amber Fort and local bazaars.'),
(2, 1, 7, 'Udaipur — Romantic Lakes & Palaces', '2025-11-13', '2025-11-17', 16000.00, 2, 'Lake Pichola sunset boat ride and City Palace visit.'),
(3, 1, 18,'Jaisalmer — Golden Fort & Desert Dunes', '2025-11-17', '2025-11-20', 15000.00, 3, 'Overnight stay in Swiss desert tents under star-studded skies.'),

-- Trip 2: Kerala Retreat (trip_id=2)
(4, 2, 11,'Munnar — Tea Estates & Mist Hills', '2024-12-05', '2024-12-08', 12000.00, 1, 'Trek to Kolukkumalai and visit tea factory.'),
(5, 2, 5, 'Alleppey — Private Houseboat Odyssey', '2024-12-08', '2024-12-12', 20000.00, 2, 'Full day leisurely backwater cruise with karimeen fry meals.'),

-- Trip 3: Ladakh Bike Odyssey (trip_id=3)
(6, 3, 2, 'Leh City & Acclimatization', '2026-06-15', '2026-06-18', 15000.00, 1, 'Shanti Stupa, Leh Palace, and bike inspection.'),
(7, 3, 2, 'Nubra Valley via Khardung La (17,982 ft)', '2026-06-18', '2026-06-21', 20000.00, 2, 'Highest motorable road, sand dunes, and Diskit monastery.'),
(8, 3, 2, 'Pangong Tso & Chang La Pass', '2026-06-21', '2026-06-25', 20000.00, 3, 'Camping on the shores of the iconic turquoise lake.');

-- ============================================================
-- 8. Trip Activities Linked to Stops
-- ============================================================
INSERT INTO `trip_activities` (id, stop_id, activity_id, scheduled_time, notes, cost, is_completed) VALUES
(1, 1, 1, '09:00 AM', 'Amber Fort morning walk', 500.00, 0),
(2, 1, 2, '04:30 PM', 'Lassi & Johari Bazaar trail', 350.00, 0),
(3, 2, 13, '05:00 PM', 'Lake Pichola sunset boat', 750.00, 0),
(4, 2, 14, '07:00 PM', 'Dharohar dance show', 450.00, 0),
(5, 3, 35, '04:00 PM', 'Desert camel safari', 2200.00, 0),
(6, 4, 21, '05:30 AM', 'Sunrise Kolukkumalai safari', 1400.00, 1),
(7, 5, 9,  '12:00 PM', 'Check-in to luxury houseboat', 4500.00, 1);

-- ============================================================
-- 9. Sample Expenses in INR (₹)
-- ============================================================
INSERT INTO `expenses` (id, trip_id, trip_stop_id, amount, category, expense_date, note) VALUES
(1, 2, 4, 3800.00, 'stay', '2024-12-05', 'Munnar Tea Valley Resort 3 Nights'),
(2, 2, 4, 2400.00, 'transport', '2024-12-05', 'Kochi Airport to Munnar Private Taxi'),
(3, 2, 4, 1400.00, 'activities', '2024-12-06', 'Kolukkumalai Sunrise 4x4 Jeep Safari'),
(4, 2, 4, 1850.00, 'meals', '2024-12-07', 'Authentic Kerala Sadya & Cardamom Cafe'),
(5, 2, 5, 12000.00,'stay', '2024-12-08', 'Deluxe AC Houseboat with 3 Meals on Board'),
(6, 2, 5, 1600.00, 'transport', '2024-12-10', 'Alleppey to Kochi Return Cab'),
(7, 2, 5, 2200.00, 'shopping', '2024-12-11', 'Kerala Spices & Homemade Chocolates'),
(8, 1, 1, 4500.00, 'stay', '2025-11-10', 'Advance booking at Heritage Haveli Jaipur'),
(9, 1, 1, 1200.00, 'transport', '2025-11-10', 'Jaipur sightseeing cab advance');

-- ============================================================
-- 10. Community Posts (Inspirational Travel Stories)
-- ============================================================
INSERT INTO `community_posts` (id, user_id, trip_id, title, content, image_url, likes_count) VALUES
(1, 2, 2, 'The Magic of Kerala: Waking Up to Misty Green Valleys',
 'Cruising through the palm-shaded canals of Alleppey while sipping tender coconut water is truly heaven on earth! If you plan a trip to Kerala, spend at least 2 nights in Munnar’s tea hills and 1 night on a private houseboat. Pro-tip: taste the freshly cooked pearl spot fish (Karimeen Pollichathu)! 🌴⛵',
 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=800', 48),

(2, 3, 3, 'Cruising Khardung La at 18,000 ft: Tips for First-Time Ladakh Bikers',
 'Ladakh tested my endurance and took my breath away in equal measure. Riding past snowbanks at Chang La to reach the electric blue Pangong Tso Lake was unforgettable. Always remember: take AMS symptoms seriously, carry diamox, drink plenty of water, and keep a spare clutch cable! 🏍️🏔️',
 'https://images.unsplash.com/photo-1581793745862-99fde7fa73d2?w=800', 65),

(3, 4, 4, 'Finding Serenity in Varanasi: 5 Ghats You Must Visit at Dawn',
 'Watching the morning mist lift over the holy Ganga at Assi Ghat while the bells chime for Subah-e-Banaras changed my perspective on life. Don’t miss Blue Lassi shop and the boat ride to Manikarnika. It is chaotic, spiritual, and deeply soulful all at once. ✨🪔',
 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?w=800', 39),

(4, 2, 1, 'Rajasthan Royal Circuit: Why Udaipur Captured My Heart',
 'While Jaipur has the forts and Jaisalmer the golden desert, Udaipur’s evening breeze on Lake Pichola surrounded by palaces lit up in gold is pure poetry. Stay at a rooftop haveli in Lal Ghat for unmatched morning views! 🏰👑',
 'https://images.unsplash.com/photo-1615836245337-f5b9b2303f10?w=800', 52);
