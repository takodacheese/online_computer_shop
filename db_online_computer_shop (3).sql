

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";




CREATE TABLE `admin` (
  `Admin_ID` char(6) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `admin` (`Admin_ID`, `Username`, `Gender`, `Password`, `Department`, `Email`, `Phone`) VALUES
('ADM001', 'AhmadSalleh', 'Male', '123456', 'IT', 'ahmad.salleh@company.com', '60-123456789'),
('ADM003', 'RajeshKumar', 'Male', '123456', 'HR', 'rajesh.kumar@company.com', '60-1355667788'),
('ADM004', 'SarahTan', 'Female', '123456', 'Operations', 'sarah.tan@company.com', '60-1498765432'),
('ADM005', 'AliBaba', 'Male', '123456', 'Marketing', 'ali.baba@company.com', '60-1677889900'),
('ADM006', 'NurulHuda', 'Female', '123456', 'Customer Service', 'nurul.huda@company.com', '60-1588776655');

CREATE TABLE `brand` (
  `Brand_ID` char(4) NOT NULL,
  `Brand_Name` varchar(100) NOT NULL,
  `Brand_Info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `brand` (`Brand_ID`, `Brand_Name`, `Brand_Info`) VALUES
('B001', 'NVIDIA', 'Leading GPU manufacturer'),
('B002', 'AMD', 'CPU and GPU manufacturer'),
('B003', 'Intel', 'Processor technology company'),
('B004', 'Corsair', 'PC components and peripherals'),
('B005', 'ASUS', 'Motherboards and PC components'),
('B006', 'Samsung', 'Memory and storage solutions'),
('B007', 'Seagate', 'Hard drives and storage'),
('B008', 'MSI', 'Gaming hardware and laptops'),
('B009', 'Gigabyte', 'Motherboards and graphics cards'),
('B010', 'Thermaltake', 'PC cases and cooling');


CREATE TABLE `cart` (
  `Cart_ID` char(7) NOT NULL,
  `User_ID` char(6) NOT NULL,
  `Product_ID` char(4) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Total_Price_Cart` decimal(10,2) NOT NULL,
  `Added_Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `category` (
  `Category_ID` char(4) NOT NULL,
  `Category_Name` varchar(20) NOT NULL,
  `Category_Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `category` (`Category_ID`, `Category_Name`, `Category_Description`) VALUES
('C001', 'Graphics Cards', 'High-performance graphics cards for gaming and professional visualization'),
('C002', 'Processors', 'Central processing units for all computing needs from entry-level to extreme performance'),
('C003', 'Memory', 'RAM modules and memory kits for desktops, laptops, and servers'),
('C004', 'Storage', 'SSDs, HDDs, and hybrid storage solutions for all applications'),
('C005', 'Motherboards', 'Motherboards supporting various form factors and processor sockets'),
('C006', 'Power Supplies', 'Power supply units with different wattages and efficiency ratings'),
('C007', 'Cases', 'Computer cases and chassis in various sizes and designs'),
('C008', 'Cooling', 'Cooling solutions including air coolers, liquid coolers, and thermal compounds'),
('C009', 'Peripherals', 'Keyboards, mice, and other input devices'),
('C010', 'Networking', 'Network interface cards');



CREATE TABLE `delivery` (
  `Delivery_ID` char(7) NOT NULL,
  `Order_ID` char(7) NOT NULL,
  `Shipping_Address` text NOT NULL,
  `Delivery_Status` varchar(20) NOT NULL,
  `Tracking_Number` varchar(100) DEFAULT NULL,
  `Recipient_Name` varchar(20) NOT NULL,
  `Shipping_Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `orders` (
  `Order_ID` char(7) NOT NULL,
  `User_ID` char(6) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `Shipping_Cost` decimal(10,2) DEFAULT NULL,
  `Order_Quantity` varchar(10) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `order_cancellation` (
  `Cancellation_ID` char(10) NOT NULL,
  `Order_ID` char(7) NOT NULL,
  `Approve_Status` varchar(20) NOT NULL,
  `Cancellation_Reason` varchar(255) NOT NULL,
  `Cancellation_Date` datetime DEFAULT NULL,
  `Admin_Note` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `order_details` (
  `Order_Detail_ID` char(8) NOT NULL,
  `Order_ID` char(7) NOT NULL,
  `Product_ID` char(4) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `password_resets` (
  `id` char(8) NOT NULL,
  `user_id` char(6) NOT NULL,
  `token` varchar(64) NOT NULL,
  `reset_token_expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pc_build` (
  `PC_ID` char(6) NOT NULL,
  `User_ID` char(6) NOT NULL,
  `Build_Name` varchar(100) NOT NULL,
  `Purpose` text DEFAULT NULL,
  `Created_Date` datetime NOT NULL,
  `Last_Update_Date` datetime NOT NULL,
  `Total_Price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `product` (
  `Product_ID` char(4) NOT NULL,
  `Product_Name` varchar(100) NOT NULL,
  `Product_Description` text DEFAULT NULL,
  `Product_Price` decimal(10,2) NOT NULL,
  `Stock_Quantity` int(11) NOT NULL,
  `Category_ID` char(4) DEFAULT NULL,
  `Brand_ID` char(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `product` (`Product_ID`, `Product_Name`, `Product_Description`, `Product_Price`, `Stock_Quantity`, `Category_ID`, `Brand_ID`) VALUES
('P001', 'NVIDIA RTX 4090', 'Flagship 24GB GDDR6X GPU with advanced ray tracing, DLSS 3.0, and AI-powered rendering for ultimate 4K gaming and content creation workloads.', 7519.95, 16, 'C001', 'B001'),
('P002', 'AMD RX 7900 XTX', 'High-end 24GB GDDR6 graphics card built on RDNA 3 architecture, optimized for 4K gaming and intensive graphic workloads.', 4699.95, 12, 'C001', 'B002'),
('P003', 'NVIDIA RTX 4070 Ti', 'Powerful 12GB GDDR6X GPU with 7680 CUDA cores, delivering high frame rates and advanced visual fidelity at 1440p resolution.', 3759.95, 18, 'C001', 'B001'),
('P004', 'Intel Core i9-13900K', 'Flagship 24-core (8 performance + 16 efficiency cores) processor with up to 5.8GHz boost, ideal for gaming and professional content creation.', 2772.95, 20, 'C002', 'B003'),
('P005', 'AMD Ryzen 9 7950X', '16-core 32-thread processor with Zen 4 architecture, 5.7GHz boost clock, and PCIe 5.0 support, great for enthusiasts and creators.', 3289.95, 12, 'C002', 'B002'),
('P006', 'Intel Core i5-13600K', 'Mid-range 14-core (6 performance + 8 efficiency cores) processor with 5.1GHz boost, perfect for gaming and multitasking.', 1550.95, 25, 'C002', 'B003'),
('P007', 'Corsair Vengeance 32GB DDR5', 'High-speed DDR5 5600MHz memory kit (2x16GB) with low latency CL36 and support for Intel XMP 3.0.', 610.95, 30, 'C003', 'B004'),
('P008', 'G.Skill Trident Z 64GB DDR4', 'Premium DDR4 3600MHz (2x32GB) memory kit with CL16 latency and RGB lighting for enthusiast PC builds.', 1174.95, 15, 'C003', 'B005'),
('P009', 'Kingston Fury 16GB DDR4', 'Reliable 3200MHz (2x8GB) memory kit with CL16 latency and plug-and-play compatibility with Intel and AMD systems.', 281.95, 40, 'C003', 'B006'),
('P010', 'Samsung 980 Pro 1TB SSD', 'High-performance PCIe 4.0 NVMe SSD with read speeds up to 7000MB/s, suitable for gaming and professional workloads.', 610.95, 25, 'C004', 'B006'),
('P011', 'WD Black SN850X 2TB SSD', 'PCIe Gen4 NVMe SSD with speeds up to 7300MB/s, designed for gamers and creative professionals needing fast storage.', 1085.95, 10, 'C004', 'B007'),
('P012', 'Crucial MX500 1TB SSD', 'Reliable SATA III SSD with sequential read/write speeds up to 560/510MB/s, excellent for mainstream desktops and laptops.', 329.95, 35, 'C004', 'B008'),
('P013', 'ASUS ROG Strix Z790-E', 'Enthusiast-grade ATX motherboard for Intel 13th Gen CPUs, featuring DDR5, PCIe 5.0, and built-in WiFi 6E support.', 1875.95, 8, 'C005', 'B005'),
('P014', 'MSI MAG B650 Tomahawk', 'Durable AM5 motherboard with DDR5 support, PCIe 5.0, and a high-performance VRM design for Ryzen 7000 CPUs.', 1029.95, 12, 'C005', 'B008'),
('P015', 'Gigabyte B660 AORUS Pro', 'LGA1700 motherboard with DDR4 support, PCIe 4.0, and 12+1+1 phase digital power design for Intel 12th Gen processors.', 889.95, 15, 'C005', 'B009'),
('P016', 'Corsair RM850x PSU', '850W 80+ Gold certified fully modular power supply unit, silent operation with premium capacitors and fan design.', 699.95, 20, 'C006', 'B004'),
('P017', 'EVGA SuperNOVA 1000W PSU', '1000W 80+ Platinum fully modular PSU with 10-year warranty, designed for high-performance gaming and workstation PCs.', 1162.95, 5, 'C006', 'B010'),
('P018', 'Lian Li PC-O11 Dynamic', 'Premium mid-tower case with dual-chamber design, tempered glass panels, and wide liquid cooling support.', 699.95, 10, 'C007', 'B010'),
('P019', 'Fractal Design Meshify C', 'Compact ATX case with high airflow mesh front panel, tempered glass side, and excellent cable management.', 469.95, 15, 'C007', 'B004'),
('P020', 'Noctua NH-D15 Air Cooler', 'High-end dual-tower air CPU cooler with dual 140mm fans, excellent thermal performance, and low noise operation.', 515.95, 12, 'C008', 'B004'),
('P021', 'MSI GeForce RTX 4060 Ventus', 'Compact yet powerful 8GB GDDR6 graphics card with DLSS 3 and ray tracing support for smooth 1080p gaming.', 1499.00, 20, 'C001', 'B001'),
('P022', 'ASUS Dual Radeon RX 7600', 'A performance-focused GPU with 8GB GDDR6 for mid-range gaming and efficient cooling.', 1389.00, 18, 'C001', 'B002'),
('P028', 'Intel Core i7-13700KF', 'High-end 16-core (8P+8E) processor with up to 5.4GHz boost, ideal for gaming and productivity.', 2099.00, 22, 'C002', 'B003'),
('P029', 'AMD Ryzen 7 7700X', '8-core Zen 4 CPU with PCIe 5.0 support and impressive multitasking performance.', 1899.00, 6, 'C002', 'B002'),
('P030', 'Thermaltake TOUGHRAM RGB 32GB DDR4', 'RGB-lit memory kit (2x16GB) at 3600MHz for gamers and creators.', 589.00, 30, 'C003', 'B010'),
('P031', 'Corsair Dominator Platinum RGB 64GB DDR5', 'Premium memory kit (2x32GB) with customizable lighting and extreme performance.', 1399.00, 10, 'C003', 'B004'),
('P032', 'Seagate FireCuda 530 1TB', 'Ultra-fast PCIe 4.0 NVMe SSD with up to 7300MB/s for gamers and professionals.', 769.00, 15, 'C004', 'B007'),
('P033', 'Samsung 870 EVO 2TB', 'Reliable SATA SSD with long endurance and performance for everyday computing.', 899.00, 20, 'C004', 'B006'),
('P034', 'Gigabyte Z790 AORUS Elite AX', 'Feature-rich ATX board with DDR5 support, Wi-Fi 6E, and PCIe 5.0.', 1099.00, 12, 'C005', 'B009'),
('P035', 'MSI B650M Mortar WiFi', 'Compact AM5 micro-ATX motherboard with modern connectivity and cooling.', 799.00, 18, 'C005', 'B008'),
('P036', 'Thermaltake Toughpower GF1 750W', '750W 80+ Gold modular PSU designed for silent operation and durability.', 499.00, 5, 'C006', 'B010'),
('P037', 'ASUS ROG Thor 1200W Platinum II', 'Enthusiast-grade fully modular power supply with OLED panel and Aura Sync.', 1399.00, 8, 'C006', 'B005'),
('P038', 'Corsair 4000D Airflow', 'Mid-tower ATX case with optimized ventilation and minimalist design.', 379.00, 20, 'C007', 'B004'),
('P039', 'Thermaltake Core P3', 'Open-frame chassis with panoramic view and wall-mountable support.', 649.00, 7, 'C007', 'B010'),
('P040', 'MSI MAG CoreLiquid C360', '360mm AIO liquid cooler with vibrant ARGB and efficient heat dissipation.', 639.00, 15, 'C008', 'B008'),
('P041', 'Arctic Liquid Freezer II 240', 'Quiet 240mm AIO cooler with integrated VRM fan and outstanding thermal performance.', 449.00, 18, 'C008', 'B004'),
('P042', 'Razer BlackWidow V4 Pro', 'Mechanical RGB keyboard with macro controls and wrist rest for premium typing.', 799.00, 15, 'C009', 'B005'),
('P043', 'Logitech G502 X Lightspeed', 'Wireless gaming mouse with HERO 25K sensor and customizable buttons.', 599.00, 25, 'C009', 'B004'),
('P044', 'TP-Link Archer TXE75E', 'PCIe Wi-Fi 6E adapter with Bluetooth 5.3 support and low-latency gaming performance.', 229.00, 20, 'C010', 'B008'),
('P045', 'ASUS XG-C100C', '10-Gigabit Ethernet PCIe adapter with wide OS support for fast LAN setups.', 389.00, 10, 'C010', 'B005'),
('P046', 'PreSonus Eris 3.5 Studio Monitor', 'Versatile studio speakers for audio playback and mix monitoring\r\n50W performance delivers more than enough power for any desktop studio situation', 430.00, 5, 'C009', NULL),
('P047', 'Razer BlackShark V2 X Multi-Platform Headset', 'With the ability to tune high, mid and low audio frequencies individually,generating brighter sound with richer trebles and more powerful bass, with vocals that are clear and crisp.\r\n\r\n', 130.00, 19, 'C009', NULL);

CREATE TABLE `product_review` (
  `Review_ID` char(6) NOT NULL,
  `Order_ID` char(7) NOT NULL,
  `Product_ID` char(4) NOT NULL,
  `Rating` decimal(2,1) NOT NULL,
  `Comment` text DEFAULT NULL,
  `Review_Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `product_review` (`Review_ID`, `Order_ID`, `Product_ID`, `Rating`, `Comment`, `Review_Date`) VALUES
('RV0001', '5', 'P029', 3.0, 'asdsdasdas', '2025-05-07 14:54:25'),
('RV0002', '5', 'P029', 3.0, 'ad', '2025-05-07 15:33:23'),
('RV0003', '5', 'P029', 3.0, 'asd', '2025-05-07 15:35:38'),
('RV0004', '5', 'P029', 1.0, 'dasd', '2025-05-07 15:38:20'),
('RV0005', '5', 'P029', 5.0, 'ads', '2025-05-07 15:38:24');


CREATE TABLE `user` (
  `User_ID` char(6) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Birthday` date DEFAULT NULL,
  `Register_Date` datetime NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `user` (`User_ID`, `Username`, `Gender`, `Password`, `Birthday`, `Register_Date`, `Email`, `Address`, `Status`) VALUES
('U00001', 'John Doe', 'Male', '123456', '1990-05-15', '2025-01-10 09:30:00', 'john@example.com', 'B-8-12, Ivory Heights Condominium, Jalan Dato Ismail Hashim, 11900 Bayan Lepas, Pulau Pinang, Malaysia', 'Active'),
('U00002', 'Jane Smith', 'Female', '123456', '1985-08-22', '2025-01-05 14:15:00', 'jane@example.com', 'No. 45, Jalan Merdeka, Taman Melaka Raya, 75000 Melaka, Melaka, Malaysia', 'Active'),
('U00003', 'Robert Johnson', 'Male', '123456', '1988-11-30', '2025-02-18 16:45:00', 'robert@example.com', 'No. 22, Jalan Indah 15/3, Taman Bukit Indah, 81200 Johor Bahru, Johor, Malaysia', 'Active'),
('U00004', 'Emily Davis', 'Female', '123456', '1995-04-22', '2025-03-05 10:20:00', 'emily@example.com', 'Suite 12-03, Menara Panorama, Jalan Puncak, Seksyen 13, 40000 Shah Alam, Selangor, Malaysia', 'Active'),
('U00005', 'Michael Wilson', 'Male', '123456', '1992-07-14', '2025-03-20 13:10:00', 'michael@example.com', 'Unit 3201, Tower B, The Mews, Jalan Yap Kwan Seng, 50450 Kuala Lumpur, Wilayah Persekutuan, Malaysia', 'Active');


CREATE TABLE `wishlist` (
  `Wishlist_ID` char(6) NOT NULL,
  `Product_ID` char(4) NOT NULL,
  `User_ID` char(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `admin`
  ADD PRIMARY KEY (`Admin_ID`);


ALTER TABLE `brand`
  ADD PRIMARY KEY (`Brand_ID`);


ALTER TABLE `cart`
  ADD PRIMARY KEY (`Cart_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Product_ID` (`Product_ID`);


ALTER TABLE `category`
  ADD PRIMARY KEY (`Category_ID`);


ALTER TABLE `delivery`
  ADD PRIMARY KEY (`Delivery_ID`),
  ADD KEY `Order_ID` (`Order_ID`);


ALTER TABLE `orders`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `User_ID` (`User_ID`);


ALTER TABLE `order_cancellation`
  ADD PRIMARY KEY (`Cancellation_ID`),
  ADD KEY `Order_ID` (`Order_ID`);


ALTER TABLE `order_details`
  ADD PRIMARY KEY (`Order_Detail_ID`),
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`);


ALTER TABLE `pc_build`
  ADD PRIMARY KEY (`PC_ID`),
  ADD KEY `User_ID` (`User_ID`);


ALTER TABLE `product`
  ADD PRIMARY KEY (`Product_ID`),
  ADD KEY `Category_ID` (`Category_ID`),
  ADD KEY `Brand_ID` (`Brand_ID`);

ALTER TABLE `product_review`
  ADD PRIMARY KEY (`Review_ID`),
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`Wishlist_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
ALTER TABLE `cart` 
MODIFY `Cart_ID` CHAR(7) NOT NULL;

UPDATE `cart` 
SET `Cart_ID` = CONCAT('CRT', LPAD(`Cart_ID`, 3, '0'))
WHERE `Cart_ID` REGEXP '^[0-9]+$';

CREATE TRIGGER cart_before_insert
BEFORE INSERT ON `cart`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`Cart_ID`, 4) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `cart`;
    SET NEW.Cart_ID = CONCAT('CRT', LPAD(next_id, 3, '0'));
END;

--
-- AUTO_INCREMENT for table `delivery`
ALTER TABLE `delivery` 
MODIFY `Delivery_ID` CHAR(7) NOT NULL;

UPDATE `delivery` 
SET `Delivery_ID` = CONCAT('DLV', LPAD(CAST(`Delivery_ID` AS UNSIGNED), 4, '0'))
WHERE `Delivery_ID` REGEXP '^[0-9]+$';

CREATE TRIGGER delivery_before_insert
BEFORE INSERT ON `delivery`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`Delivery_ID`, 4) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `delivery`;
    SET NEW.Delivery_ID = CONCAT('DLV', LPAD(next_id, 4, '0'));
END;

--
-- AUTO_INCREMENT for table `orders`
ALTER TABLE `orders` 
MODIFY `Order_ID` CHAR(7) NOT NULL;

UPDATE `orders` 
SET `Order_ID` = CONCAT('ORD', LPAD(`Order_ID`, 4, '0'))
WHERE `Order_ID` REGEXP '^[0-9]+$';

CREATE TRIGGER orders_before_insert
BEFORE INSERT ON `orders`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`Order_ID`, 4) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `orders`;
    SET NEW.Order_ID = CONCAT('ORD', LPAD(next_id, 4, '0'));
END;
--
-- AUTO_INCREMENT for table `order_details`
ALTER TABLE `order_details` 
MODIFY `Order_Detail_ID` CHAR(8) NOT NULL;

UPDATE `order_details` 
SET `Order_Detail_ID` = CONCAT('ORDT', LPAD(SUBSTRING(`Order_Detail_ID`, 5), 4, '0'))
WHERE `Order_Detail_ID` REGEXP '^[0-9]+$';

CREATE TRIGGER order_details_before_insert
BEFORE INSERT ON `order_details`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`Order_Detail_ID`, 5) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `order_details`;
    SET NEW.Order_Detail_ID = CONCAT('ORDT', LPAD(next_id, 4, '0'));
END;

-- AUTO_INCREMENT for table `password_resets`
ALTER TABLE `password_resets` 
MODIFY `id` CHAR(8) NOT NULL;

UPDATE `password_resets` 
SET `id` = CONCAT('RESET', LPAD(`id`, 3, '0'))
WHERE `id` REGEXP '^[0-9]+$';

CREATE TRIGGER password_resets_before_insert
BEFORE INSERT ON `password_resets`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`id`, 6) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `password_resets`;
    SET NEW.id = CONCAT('RESET', LPAD(next_id, 3, '0'));
END;


-- AUTO_INCREMENT for table `order_cancellation`
ALTER TABLE `order_cancellation` 
MODIFY `Cancellation_ID` CHAR(10) NOT NULL;

UPDATE `order_cancellation` 
SET `Cancellation_ID` = CONCAT('CANCEL', LPAD(CAST(`Cancellation_ID` AS UNSIGNED), 4, '0'))
WHERE `Cancellation_ID` REGEXP '^[0-9]+$';

CREATE TRIGGER order_cancellation_before_insert
BEFORE INSERT ON `order_cancellation`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`Cancellation_ID`, 7) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `order_cancellation`;
    SET NEW.Cancellation_ID = CONCAT('CANCEL', LPAD(next_id, 4, '0'));
END;

-- AUTO_INCREMENT for table `pc_build`
ALTER TABLE `pc_build` 
MODIFY `PC_ID` CHAR(6) NOT NULL;

UPDATE `pc_build` 
SET `PC_ID` = CONCAT('BLD', LPAD(`PC_ID`, 3, '0'))
WHERE `PC_ID` REGEXP '^[0-9]+$';

CREATE TRIGGER pc_build_before_insert
BEFORE INSERT ON `pc_build`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`PC_ID`, 4) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `pc_build`;
    SET NEW.PC_ID = CONCAT('BLD', LPAD(next_id, 3, '0'));
END;
--ALTER TABLE `pc_build` ADD UNIQUE INDEX `PC_ID_UNIQUE` (`PC_ID`);

-- AUTO_INCREMENT for table `product_review`
ALTER TABLE `product_review` 
MODIFY `Review_ID` CHAR(6) NOT NULL;

UPDATE `product_review` 
SET `Review_ID` = CONCAT('RV', LPAD(CAST(`Review_ID` AS UNSIGNED), 4, '0'))
WHERE `Review_ID` REGEXP '^[0-9]+$';

CREATE TRIGGER product_review_before_insert
BEFORE INSERT ON `product_review`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`Review_ID`, 3) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `product_review`;
    SET NEW.Review_ID = CONCAT('RV', LPAD(next_id, 4, '0'));
END;
--ALTER TABLE `product_review` ADD UNIQUE INDEX `Review_ID_UNIQUE` (`Review_ID`);

-- AUTO_INCREMENT for table `wishlist`
ALTER TABLE `wishlist` 
MODIFY `Wishlist_ID` CHAR(6) NOT NULL;

UPDATE `wishlist` 
SET `Wishlist_ID` = CONCAT('WL', LPAD(CAST(`Wishlist_ID` AS UNSIGNED), 4, '0'))
WHERE `Wishlist_ID` REGEXP '^[0-9]+$';

CREATE TRIGGER wishlist_before_insert
BEFORE INSERT ON `wishlist`
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT IFNULL(MAX(CAST(SUBSTRING(`Wishlist_ID`, 3) AS UNSIGNED)), 0) + 1 
    INTO next_id FROM `wishlist`;
    SET NEW.Wishlist_ID = CONCAT('WL', LPAD(next_id, 4, '0'));
END;
--ALTER TABLE `wishlist` ADD UNIQUE INDEX `Wishlist_ID_UNIQUE` (`Wishlist_ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
