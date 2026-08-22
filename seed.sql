-- ============================================================
-- GlobeTrotter Seed Data
-- Run AFTER schema.sql
-- ============================================================

USE `globetrotter`;

-- ============================================================
-- Sample Admin User  (password: Admin@1234)
-- ============================================================
INSERT INTO `users` (first_name, last_name, email, password_hash, phone, city, country, additional_info, role) VALUES
('Admin', 'GlobeTrotter', 'admin@globetrotter.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
 '+1-555-0100', 'San Francisco', 'USA', 'Platform administrator', 'admin'),
('Jane', 'Doe', 'jane@example.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '+44-7700-900123', 'London', 'United Kingdom', 'Travel enthusiast', 'user'),
('Arjun', 'Mehta', 'arjun@example.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '+91-9876543210', 'Mumbai', 'India', 'Backpacker and foodie', 'user');

-- ============================================================
-- 15 Cities
-- ============================================================
INSERT INTO `cities` (name, country, region, cost_index, popularity_score, image_url, description) VALUES
('Paris',        'France',      'Europe',        1.80, 95, 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=600', 'The City of Light, home to the Eiffel Tower and world-class cuisine.'),
('Tokyo',        'Japan',       'Asia',          1.60, 92, 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=600', 'A dazzling blend of ultra-modern and traditional Japanese culture.'),
('New York',     'USA',         'North America', 2.00, 90, 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=600', 'The city that never sleeps — skyscrapers, Broadway, and Central Park.'),
('Bali',         'Indonesia',   'Asia',          0.70, 88, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=600', 'Tropical paradise famous for its forested volcanic mountains and beaches.'),
('Rome',         'Italy',       'Europe',        1.50, 86, 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=600', 'The Eternal City packed with millennia of history and art.'),
('Dubai',        'UAE',         'Middle East',   1.90, 85, 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=600', 'Futuristic skyline meets desert luxury and world-record attractions.'),
('Barcelona',    'Spain',       'Europe',        1.40, 84, 'https://images.unsplash.com/photo-1583422409516-2895a77efded?w=600', 'Gaudí architecture, tapas culture, and Mediterranean beaches.'),
('Bangkok',      'Thailand',    'Asia',          0.65, 83, 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=600', 'Vibrant street life, ornate shrines, and sizzling street food.'),
('Cape Town',    'South Africa','Africa',        0.90, 80, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=600', 'Stunning coastline beneath Table Mountain with diverse culture.'),
('Sydney',       'Australia',   'Oceania',       1.75, 82, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600', 'Iconic Opera House, Harbour Bridge, and golden beaches.'),
('Istanbul',     'Turkey',      'Europe/Asia',   1.10, 78, 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?w=600', 'Where East meets West across the stunning Bosphorus strait.'),
('Machu Picchu', 'Peru',        'South America', 1.20, 87, 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=600', 'The Lost City of the Incas perched high in the Andes Mountains.'),
('Santorini',    'Greece',      'Europe',        1.85, 91, 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=600', 'Whitewashed clifftop villages overlooking a volcanic caldera.'),
('Kyoto',        'Japan',       'Asia',          1.40, 85, 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=600', 'Ancient temples, traditional geisha districts, and bamboo forests.'),
('Maldives',     'Maldives',    'Asia',          2.50, 89, 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=600', 'Overwater bungalows above crystal-clear turquoise lagoons.');

-- ============================================================
-- 30 Activities (2 per city)
-- ============================================================
INSERT INTO `activities` (city_id, name, category, cost, duration_minutes, description) VALUES
-- Paris (id=1)
(1, 'Eiffel Tower Summit Visit',    'Sightseeing', 35.00,  180, 'Ascend to the top floor of the iconic iron lattice tower for panoramic views of Paris.'),
(1, 'Louvre Museum Tour',           'Culture',     22.00,  240, 'Explore the world\'s largest art museum and see the Mona Lisa, Venus de Milo, and more.'),
-- Tokyo (id=2)
(2, 'Shibuya Crossing Experience',  'Sightseeing',  0.00,   60, 'Witness the world\'s busiest pedestrian crossing surrounded by neon billboards.'),
(2, 'Teamlab Borderless Digital Art','Culture',     32.00,  180, 'Immerse yourself in a boundless world of digital art spanning an entire building.'),
-- New York (id=3)
(3, 'Statue of Liberty Ferry',      'Sightseeing', 24.00,  240, 'Take the iconic ferry ride to Liberty Island and explore Lady Liberty up close.'),
(3, 'Central Park Bike Tour',       'Adventure',   30.00,  150, 'Cycle through 843 acres of urban greenery, lakes, and iconic landmarks.'),
-- Bali (id=4)
(4, 'Ubud Rice Terrace Trek',       'Adventure',   15.00,  180, 'Walk through the stunning emerald-green Tegalalang rice terraces at sunrise.'),
(4, 'Traditional Kecak Fire Dance', 'Culture',     12.00,   90, 'Watch the mesmerizing Balinese fire dance performance at Uluwatu Temple at sunset.'),
-- Rome (id=5)
(5, 'Colosseum Guided Tour',        'Culture',     20.00,  150, 'Step inside the ancient amphitheatre with an expert guide and discover gladiatorial history.'),
(5, 'Vatican Museums & Sistine Chapel','Culture',  20.00,  240, 'Marvel at Michelangelo\'s breathtaking Sistine Chapel ceiling and the vast Vatican collections.'),
-- Dubai (id=6)
(6, 'Burj Khalifa At The Top',      'Sightseeing', 45.00,   90, 'Ascend to the observation deck on the 148th floor of the world\'s tallest building.'),
(6, 'Desert Safari with BBQ Dinner','Adventure',   65.00,  360, 'Dune bashing, sandboarding, camel riding, and a traditional Bedouin BBQ under the stars.'),
-- Barcelona (id=7)
(7, 'Sagrada Família Interior Tour','Culture',     26.00,  120, 'Enter Gaudí\'s unfinished masterpiece and be dazzled by the kaleidoscopic stained glass.'),
(7, 'Gothic Quarter Walking Tour',  'Culture',      0.00,  120, 'Wander through labyrinthine medieval streets filled with Roman ruins and Gothic churches.'),
-- Bangkok (id=8)
(8, 'Grand Palace & Wat Phra Kaew', 'Culture',     15.00,  180, 'Visit the glittering Grand Palace complex and the sacred Temple of the Emerald Buddha.'),
(8, 'Floating Market Long-tail Boat','Adventure',  20.00,  180, 'Speed through canals on a traditional long-tail boat to vibrant floating markets.'),
-- Cape Town (id=9)
(9, 'Table Mountain Cable Car',     'Sightseeing', 28.00,  120, 'Ride the rotating cable car to the top of the iconic flat-topped mountain for 360° views.'),
(9, 'Cape of Good Hope Day Trip',   'Adventure',   40.00,  480, 'Drive along the scenic Cape Peninsula to the dramatic southwesternmost tip of Africa.'),
-- Sydney (id=10)
(10,'Sydney Opera House Backstage', 'Culture',     50.00,   90, 'Go behind the scenes of one of the world\'s most celebrated architectural masterpieces.'),
(10,'Bondi to Coogee Coastal Walk', 'Adventure',    0.00,  180, 'Hike the iconic 6km clifftop trail past stunning beaches, rock pools, and ocean views.'),
-- Istanbul (id=11)
(11,'Hagia Sophia & Blue Mosque',   'Culture',     15.00,  180, 'Explore two of the world\'s most magnificent sacred buildings side by side.'),
(11,'Bosphorus Sunset Cruise',      'Sightseeing', 25.00,  120, 'Sail between two continents as the sun sets behind the glittering city skyline.'),
-- Machu Picchu (id=12)
(12,'Inca Trail 2-Day Trek',        'Adventure',  200.00, 2880, 'Hike the ancient Inca Trail through cloud forest to arrive at the Sun Gate at sunrise.'),
(12,'Huayna Picchu Mountain Climb', 'Adventure',   20.00,  300, 'Climb the steep peak above Machu Picchu for jaw-dropping aerial views of the citadel.'),
-- Santorini (id=13)
(13,'Oia Sunset Viewing',           'Sightseeing',  0.00,  120, 'Watch the world-famous Santorini sunset from the cliffside village of Oia.'),
(13,'Caldera Sailing & Hot Springs','Adventure',   95.00,  480, 'Sail around the volcanic caldera and swim in natural hot springs on a catamaran.'),
-- Kyoto (id=14)
(14,'Arashiyama Bamboo Grove Walk', 'Sightseeing',  0.00,   90, 'Walk the iconic path through towering bamboo stalks in the tranquil Arashiyama district.'),
(14,'Fushimi Inari Thousand Gates', 'Culture',      0.00,  180, 'Hike the famous path lined with thousands of vermilion torii gates up Mount Inari.'),
-- Maldives (id=15)
(15,'Snorkeling the Coral Reef',    'Adventure',   40.00,  180, 'Dive into crystal-clear waters to discover vibrant coral reefs and marine life.'),
(15,'Overwater Spa Treatment',      'Wellness',   120.00,  120, 'Indulge in a signature spa treatment in a glass-floored pavilion suspended over the lagoon.');

-- ============================================================
-- Sample Trips for Jane (user_id=2)
-- ============================================================
INSERT INTO `trips` (user_id, name, description, start_date, end_date, is_public, share_slug, status) VALUES
(2, 'European Summer Dream',   'Exploring the best of Paris, Rome & Santorini', '2024-06-10', '2024-06-28', 1, 'european-summer-dream-jane', 'completed'),
(2, 'Southeast Asia Adventure','Island hopping across Bali & Bangkok', '2025-03-01', '2025-03-20', 1, 'sea-adventure-jane', 'completed'),
(2, 'Japan Cherry Blossom',    'Tokyo and Kyoto during sakura season', '2026-03-25', '2026-04-08', 0, NULL, 'upcoming');

-- ============================================================
-- Sample Trip Stops
-- ============================================================
INSERT INTO `trip_stops` (trip_id, city_id, order_index, start_date, end_date, budget) VALUES
-- European Summer Dream (trip_id=1)
(1, 1, 1, '2024-06-10', '2024-06-17', 1500.00),  -- Paris
(1, 5, 2, '2024-06-17', '2024-06-22', 1200.00),  -- Rome
(1, 13,3, '2024-06-22', '2024-06-28', 1800.00),  -- Santorini
-- Japan Cherry Blossom (trip_id=3)
(3, 2, 1, '2026-03-25', '2026-04-01', 2000.00),  -- Tokyo
(3, 14,2, '2026-04-01', '2026-04-08', 1500.00);  -- Kyoto

-- ============================================================
-- Sample Expenses
-- ============================================================
INSERT INTO `expenses` (trip_id, trip_stop_id, category, amount, expense_date, note) VALUES
(1, 1, 'transport',   250.00, '2024-06-10', 'Eurostar London to Paris'),
(1, 1, 'stay',        840.00, '2024-06-11', 'Hotel Lumiere 7 nights'),
(1, 1, 'activities',   57.00, '2024-06-12', 'Eiffel Tower + Louvre'),
(1, 1, 'meals',       280.00, '2024-06-13', 'Restaurants week 1'),
(1, 2, 'transport',    80.00, '2024-06-17', 'Paris to Rome flight'),
(1, 2, 'stay',        600.00, '2024-06-17', 'Airbnb Trastevere'),
(1, 2, 'activities',   40.00, '2024-06-18', 'Colosseum + Vatican'),
(1, 3, 'transport',   120.00, '2024-06-22', 'Rome to Santorini flight'),
(1, 3, 'stay',       1200.00, '2024-06-22', 'Caldera view villa'),
(1, 3, 'meals',       350.00, '2024-06-23', 'Fine dining Oia');

-- ============================================================
-- Sample Community Posts
-- ============================================================
INSERT INTO `community_posts` (user_id, trip_id, content) VALUES
(2, 1, 'Just got back from the most magical European summer! Paris in June is absolutely breathtaking — the Eiffel Tower at night, the croissants at dawn. If you haven\'t done the Paris-Rome-Santorini route, put it on your bucket list immediately! 🌅'),
(2, 2, 'Bali is a life-changing experience. The rice terraces at Tegalalang are unlike anything I\'ve ever seen. Budget tip: hire a local scooter for $5/day and explore on your own schedule! 🛵'),
(3, NULL, 'Planning my first solo trip to Japan next spring for cherry blossom season. Any recommendations for Kyoto beyond the obvious spots? Would love some hidden gems! 🌸'),
(2, 1, 'Pro tip for Santorini: book sunset-facing accommodations in Oia at least 6 months in advance. We got the last available villa and it was completely worth every penny. The caldera view at dusk is indescribable! 🌺');
