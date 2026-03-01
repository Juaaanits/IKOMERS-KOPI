USE ikomers_db;

-- Optional clean reset of orders only
TRUNCATE TABLE `orders`;

INSERT INTO `orders` (customer_name, items, total, status, ordered_at) VALUES
('Marcus Johnson', '1x Espresso Shot (PHP 110.00)', 110.00, 'Pending',    '2026-03-01 08:00:00'),
('William O''Connor', '1x Classic Black Coffee (PHP 120.00)', 120.00, 'Processing', '2026-03-01 08:10:00'),
('Maria Sanchez', '1x Iced Americano (PHP 130.00)', 130.00, 'Completed', '2026-03-01 08:20:00'),
('David Kim', '1x Brewed Black Tea (PHP 95.00)', 95.00, 'Cancelled', '2026-03-01 08:30:00'),
('Amanda Peterson', '1x Butter Croissant (PHP 85.00)', 85.00, 'Pending', '2026-03-01 08:40:00'),

('Rachel Foster', '1x Espresso Shot (PHP 110.00), 1x Butter Croissant (PHP 85.00)', 195.00, 'Processing', '2026-03-01 08:50:00'),
('James Kim', '1x Classic Black Coffee (PHP 120.00), 1x Brewed Black Tea (PHP 95.00)', 215.00, 'Completed', '2026-03-01 09:00:00'),
('Lisa Rodriguez', '1x Iced Americano (PHP 130.00), 1x Butter Croissant (PHP 85.00)', 215.00, 'Pending', '2026-03-01 09:10:00'),
('Michael Bennett', '1x Espresso Shot (PHP 110.00), 1x Iced Americano (PHP 130.00)', 240.00, 'Processing', '2026-03-01 09:20:00'),
('Sarah Collins', '1x Classic Black Coffee (PHP 120.00), 1x Butter Croissant (PHP 85.00)', 205.00, 'Completed', '2026-03-01 09:30:00'),

('Kevin Brooks', '2x Espresso Shot (PHP 110.00)', 220.00, 'Pending', '2026-03-01 09:40:00'),
('Olivia Martin', '2x Classic Black Coffee (PHP 120.00)', 240.00, 'Processing', '2026-03-01 09:50:00'),
('Noah Wilson', '2x Iced Americano (PHP 130.00)', 260.00, 'Completed', '2026-03-01 10:00:00'),
('Emma Taylor', '2x Brewed Black Tea (PHP 95.00)', 190.00, 'Cancelled', '2026-03-01 10:10:00'),
('Liam Thomas', '2x Butter Croissant (PHP 85.00)', 170.00, 'Pending', '2026-03-01 10:20:00'),

('Sophia Anderson', '1x Espresso Shot (PHP 110.00), 1x Classic Black Coffee (PHP 120.00), 1x Butter Croissant (PHP 85.00)', 315.00, 'Processing', '2026-03-01 10:30:00'),
('Ethan Moore', '1x Iced Americano (PHP 130.00), 1x Brewed Black Tea (PHP 95.00), 1x Butter Croissant (PHP 85.00)', 310.00, 'Completed', '2026-03-01 10:40:00'),
('Mia Jackson', '1x Espresso Shot (PHP 110.00), 1x Brewed Black Tea (PHP 95.00)', 205.00, 'Pending', '2026-03-01 10:50:00'),
('Logan White', '1x Classic Black Coffee (PHP 120.00), 1x Iced Americano (PHP 130.00)', 250.00, 'Processing', '2026-03-01 11:00:00'),
('Ava Harris', '1x Espresso Shot (PHP 110.00), 1x Butter Croissant (PHP 85.00), 1x Brewed Black Tea (PHP 95.00)', 290.00, 'Completed', '2026-03-01 11:10:00'),

('Benjamin Clark', '3x Espresso Shot (PHP 110.00)', 330.00, 'Pending', '2026-03-01 11:20:00'),
('Isabella Lewis', '3x Classic Black Coffee (PHP 120.00)', 360.00, 'Processing', '2026-03-01 11:30:00'),
('Mason Young', '3x Iced Americano (PHP 130.00)', 390.00, 'Completed', '2026-03-01 11:40:00'),
('Charlotte Hall', '3x Brewed Black Tea (PHP 95.00)', 285.00, 'Cancelled', '2026-03-01 11:50:00'),
('Lucas Allen', '3x Butter Croissant (PHP 85.00)', 255.00, 'Pending', '2026-03-01 12:00:00'),

('Marcus Johnson', '1x Espresso Shot (PHP 110.00), 1x Classic Black Coffee (PHP 120.00)', 230.00, 'Processing', '2026-03-01 12:10:00'),
('William O''Connor', '1x Iced Americano (PHP 130.00), 1x Butter Croissant (PHP 85.00), 1x Brewed Black Tea (PHP 95.00)', 310.00, 'Completed', '2026-03-01 12:20:00'),
('Maria Sanchez', '1x Espresso Shot (PHP 110.00), 1x Iced Americano (PHP 130.00), 1x Butter Croissant (PHP 85.00)', 325.00, 'Pending', '2026-03-01 12:30:00'),
('David Kim', '1x Classic Black Coffee (PHP 120.00), 1x Brewed Black Tea (PHP 95.00), 1x Butter Croissant (PHP 85.00)', 300.00, 'Processing', '2026-03-01 12:40:00'),
('Amanda Peterson', '1x Espresso Shot (PHP 110.00), 1x Classic Black Coffee (PHP 120.00), 1x Iced Americano (PHP 130.00), 1x Brewed Black Tea (PHP 95.00), 1x Butter Croissant (PHP 85.00)', 540.00, 'Completed', '2026-03-01 12:50:00');

-- Sync customers.orders_count based on actual seeded orders
UPDATE customers c
LEFT JOIN (
  SELECT customer_name, COUNT(*) AS cnt
  FROM `orders`
  GROUP BY customer_name
) o ON o.customer_name = c.name
SET c.orders_count = COALESCE(o.cnt, 0);
