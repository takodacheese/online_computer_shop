SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `db_online_computer_shop`;
USE `db_online_computer_shop`;

CREATE DATABASE IF NOT EXISTS `db_online_computer_shop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_online_computer_shop`;

-- Table structure for table `User`
CREATE TABLE `User` (
  `User_ID` char(6) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Birthday` date DEFAULT NULL,
  `Register_Date` datetime NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data for table `User`
INSERT INTO `User` (`User_ID`, `Username`, `Gender`, `Password`, `Birthday`, `Register_Date`, `Email`, `Address`) VALUES
('U00001', 'John Doe', 'Male', '123456', '1990-05-15', '2025-01-10 09:30:00', 'john@example.com', 'B-8-12, Ivory Heights Condominium, Jalan Dato Ismail Hashim, 11900 Bayan Lepas, Pulau Pinang, Malaysia'),
('U00002', 'Jane Smith', 'Female', '123456', '1985-08-22', '2025-01-05 14:15:00', 'jane@example.com', 'No. 45, Jalan Merdeka, Taman Melaka Raya, 75000 Melaka, Melaka, Malaysia'),
('U00003', 'Robert Johnson', 'Male', '123456', '1988-11-30', '2025-02-18 16:45:00', 'robert@example.com', 'No. 22, Jalan Indah 15/3, Taman Bukit Indah, 81200 Johor Bahru, Johor, Malaysia'),
('U00004', 'Emily Davis', 'Female', '123456', '1995-04-22', '2025-03-05 10:20:00', 'emily@example.com', 'Suite 12-03, Menara Panorama, Jalan Puncak, Seksyen 13, 40000 Shah Alam, Selangor, Malaysia'),
('U00005', 'Michael Wilson', 'Male', '123456', '1992-07-14', '2025-03-20 13:10:00', 'michael@example.com', 'Unit 3201, Tower B, The Mews, Jalan Yap Kwan Seng, 50450 Kuala Lumpur, Wilayah Persekutuan, Malaysia');

-- Table structure for table `Brand`
CREATE TABLE `Brand` (
  `Brand_ID` char(4) NOT NULL,
  `Brand_Name` varchar(100) NOT NULL,
  `Brand_Info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `Brand`
INSERT INTO `Brand` (`Brand_ID`, `Brand_Name`, `Brand_Info`) VALUES
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

-- Table structure for table `category`
CREATE TABLE `category` (
  `Category_ID` char(4) NOT NULL,
  `Category_Name` varchar(20) NOT NULL,
  `Category_Description` text DEFAULT NULL,
  `Brand_ID` char(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `category`
INSERT INTO `category` (`Category_ID`, `Category_Name`, `Category_Description`, `Brand_ID`) VALUES
('C001', 'Graphics Cards', 'High-performance graphics cards for gaming and professional visualization', 'B001'),
('C002', 'Processors', 'Central processing units for all computing needs from entry-level to extreme performance', 'B002'),
('C003', 'Memory', 'RAM modules and memory kits for desktops, laptops, and servers', 'B004'),
('C004', 'Storage', 'SSDs, HDDs, and hybrid storage solutions for all applications', 'B006'),
('C005', 'Motherboards', 'Motherboards supporting various form factors and processor sockets', 'B005'),
('C006', 'Power Supplies', 'Power supply units with different wattages and efficiency ratings', 'B009'),
('C007', 'Cases', 'Computer cases and chassis in various sizes and designs', 'B010'),
('C008', 'Cooling', 'Cooling solutions including air coolers, liquid coolers, and thermal compounds', 'B004'),
('C009', 'Peripherals', 'Keyboards, mice, and other input devices', 'B005'),
('C010', 'Networking', 'Network interface cards', 'B008');

CREATE TABLE `product` (
  `Product_ID` char(4) NOT NULL,
  `Product_Name` varchar(100) NOT NULL,
  `Product_Description` text DEFAULT NULL,
  `Product_Price` decimal(10,2) NOT NULL,
  `Stock_Quantity` int(11) NOT NULL,
  `Rating_Avg` decimal(3,2) DEFAULT 0.00,
  `Category_ID` char(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `product`
INSERT INTO `product` (`Product_ID`, `Product_Name`, `Product_Description`, `Product_Price`, `Stock_Quantity`, `Rating_Avg`, `Category_ID`) VALUES
('P001', 'NVIDIA RTX 4090', 'Flagship 24GB GDDR6X GPU with DLSS 3.0', 1599.99, 15, 4.8, 'C001'),
('P002', 'AMD RX 7900 XTX', '24GB GDDR6 RDNA 3 architecture', 999.99, 12, 4.7, 'C001'),
('P003', 'NVIDIA RTX 4070 Ti', '12GB GDDR6X 7680 CUDA cores', 799.99, 18, 4.6, 'C001'),
('P004', 'Intel Core i9-13900K', '24-core (8P+16E) 5.8GHz boost', 589.99, 20, 4.7, 'C002'),
('P005', 'AMD Ryzen 9 7950X', '16-core 5.7GHz Zen 4 processor', 699.99, 15, 4.8, 'C002'),
('P006', 'Intel Core i5-13600K', '14-core (6P+8E) 5.1GHz boost', 329.99, 25, 4.5, 'C002'),
('P007', 'Corsair Vengeance 32GB DDR5', '5600MHz CL36 (2x16GB)', 129.99, 30, 4.6, 'C003'),
('P008', 'G.Skill Trident Z 64GB DDR4', '3600MHz CL16 (2x32GB)', 249.99, 15, 4.7, 'C003'),
('P009', 'Kingston Fury 16GB DDR4', '3200MHz CL16 (2x8GB)', 59.99, 40, 4.4, 'C003'),
('P010', 'Samsung 980 Pro 1TB', 'PCIe 4.0 NVMe SSD 7000MB/s', 129.99, 25, 4.8, 'C004'),
('P011', 'WD Black SN850X 2TB', 'PCIe 4.0 NVMe SSD 7300MB/s', 229.99, 10, 4.7, 'C004'),
('P012', 'Crucial MX500 1TB', 'SATA III SSD 560MB/s', 69.99, 35, 4.5, 'C004'),
('P013', 'ASUS ROG Strix Z790-E', 'LGA1700 DDR5 WiFi 6E', 399.99, 8, 4.6, 'C005'),
('P014', 'MSI MAG B650 Tomahawk', 'AM5 DDR5 PCIe 5.0', 219.99, 12, 4.5, 'C005'),
('P015', 'Gigabyte B660 AORUS Pro', 'LGA1700 DDR4 12+1+1 phase', 189.99, 15, 4.4, 'C005'),
('P016', 'Corsair RM850x', '850W 80+ Gold fully modular', 149.99, 20, 4.7, 'C006'),
('P017', 'EVGA SuperNOVA 1000W', '80+ Platinum fully modular', 249.99, 5, 4.8, 'C006'),
('P018', 'Lian Li PC-O11 Dynamic', 'Mid-tower tempered glass', 149.99, 10, 4.7, 'C007'),
('P019', 'Fractal Design Meshify C', 'Compact ATX airflow case', 99.99, 15, 4.6, 'C007'),
('P020', 'Noctua NH-D15', 'Dual-tower air cooler', 109.99, 12, 4.9, 'C008'),
('P021', 'Corsair iCUE H150i', '360mm RGB liquid cooler', 179.99, 8, 4.7, 'C008'),
('P022', 'Arctic Freezer 34', 'Budget air cooler', 39.99, 25, 4.3, 'C008'),
('P023', 'Logitech G Pro X Keyboard', 'Mechanical gaming keyboard with GX switches', 149.99, 25, 4.7, 'C009'),
('P024', 'Razer DeathAdder V2 Mouse', 'Ergonomic gaming mouse with 20K DPI', 69.99, 30, 4.6, 'C009'),
('P025', 'SteelSeries Arctis Pro Headset', 'High-fidelity gaming headset with DTS', 179.99, 15, 4.8, 'C009'),
('P026', 'ASUS RT-AX86U Router', 'WiFi 6 gaming router (5700Mbps)', 249.99, 10, 4.6, 'C010'),
('P027', 'TP-Link Archer AX50', 'WiFi 6 router with Intel chipset', 129.99, 18, 4.4, 'C010'),
('P028', 'NETGEAR Nighthawk S8000', '8-port gaming switch with QoS', 99.99, 12, 4.3, 'C010'),
('P029', 'HyperX Cloud II Headset', '7.1 virtual surround sound gaming headset', 99.99, 20, 4.5, 'C009'),
('P030', 'Corsair K100 RGB Keyboard', 'Mechanical keyboard with OPX switches', 199.99, 8, 4.7, 'C009'),
('P031', 'Ubiquiti UniFi U6-Pro', 'WiFi 6 access point for prosumers', 179.99, 5, 4.8, 'C010');

CREATE TABLE `Admin` (
  `Admin_ID` char(6) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `Admin`
INSERT INTO `Admin` (`Admin_ID`, `Username`, `Gender`, `Password`, `Department`, `Email`, `Phone`) VALUES
('ADM001', 'AhmadSalleh', 'Male', '123456', 'IT', 'ahmad.salleh@company.com', '60-123456789'),
('ADM003', 'RajeshKumar', 'Male', '123456', 'HR', 'rajesh.kumar@company.com', '60-1355667788'),
('ADM004', 'SarahTan', 'Female', '123456', 'Operations', 'sarah.tan@company.com', '60-1498765432'),
('ADM005', 'AliBaba', 'Male', '123456', 'Marketing', 'ali.baba@company.com', '60-1677889900'),
('ADM006', 'NurulHuda', 'Female', '123456', 'Customer Service', 'nurul.huda@company.com', '60-1588776655');

CREATE TABLE `PC_Build` (
  `PC_ID` char(5) NOT NULL,
  `User_ID` char(6) NOT NULL,
  `Build_Name` varchar(100) NOT NULL,
  `Purpose` text DEFAULT NULL,
  `Created_Date` datetime NOT NULL,
  `Last_Update_Date` datetime NOT NULL,
  `Total_Price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Orders` (
  `Order_ID` char(6) NOT NULL,
  `User_ID` char(6) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `Shipping_Cost` decimal(10,2) DEFAULT NULL,
  `Order_Quantity` varchar(10) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Order_Details` (
  `Order_Detail_ID` char(6) NOT NULL,
  `Order_ID` char(6) NOT NULL,
  `Product_ID` char(4) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Cart` (
  `Cart_ID` char(6) NOT NULL,
  `User_ID` char(6) NOT NULL,
  `Product_ID` char(4) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Total_Price_Cart` decimal(10,2) NOT NULL,
  `Added_Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `Product_Review`;
CREATE TABLE `Product_Review` (
  `Review_ID` char(6) NOT NULL,
  `Order_ID` char(6) NOT NULL,
  `Rating` decimal(2,1) NOT NULL,
  `Comment` text DEFAULT NULL,
  `Review_Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Delivery` (
  `Delivery_ID` char(6) NOT NULL,
  `Order_ID` char(6) NOT NULL,
  `Shipping_Address` text NOT NULL,
  `Delivery_Status` varchar(20) NOT NULL,
  `Tracking_Number` varchar(100) DEFAULT NULL,
  `Recipient_Name` varchar(20) NOT NULL,
  `Shipping_Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Wishlist` (
  `Wishlist_ID` char(6) NOT NULL,
  `Product_ID` char(4) NOT NULL,
  `User_ID` char(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Order_Cancellation` (
  `Cancellation_ID` char(6) NOT NULL,
  `Order_ID` char(6) NOT NULL,
  `Approve_Status` varchar(20) NOT NULL,
  `Cancellation_Reason` varchar(255) NOT NULL,
  `Cancellation_Date` datetime DEFAULT NULL,
  `Admin_Note` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Indexes for dumped tables
-- Indexes for table `Brand`
ALTER TABLE `Brand`
  ADD PRIMARY KEY (`Brand_ID`);

-- Indexes for table `category`
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_ID`),
  ADD KEY `Brand_ID` (`Brand_ID`);

-- Indexes for table `product`
ALTER TABLE `product`
  ADD PRIMARY KEY (`Product_ID`),
  ADD KEY `category_ID` (`category_ID`),

-- Indexes for table `User`
ALTER TABLE `User`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

-- Indexes for table `PC_Build`
ALTER TABLE `PC_Build`
  ADD PRIMARY KEY (`PC_ID`),
  ADD KEY `User_ID` (`User_ID`);

-- Indexes for table `Orders`
ALTER TABLE `Orders`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `User_ID` (`User_ID`);

-- Indexes for table `Order_Details`
ALTER TABLE `Order_Details`
  ADD PRIMARY KEY (`Order_Detail_ID`),
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

-- Indexes for table `Cart`
ALTER TABLE `Cart`
  ADD PRIMARY KEY (`Cart_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

-- Indexes for table `Product_Review`
ALTER TABLE `Product_Review`
  ADD PRIMARY KEY (`Review_ID`),
  ADD KEY `Order_ID` (`Order_ID`);

-- Indexes for table `Admin`
ALTER TABLE `Admin`
  ADD PRIMARY KEY (`Admin_ID`);

-- Indexes for table `Delivery`
ALTER TABLE `Delivery`
  ADD PRIMARY KEY (`Delivery_ID`),
  ADD KEY `Order_ID` (`Order_ID`);

-- Indexes for table `Wishlist`
ALTER TABLE `Wishlist`
  ADD PRIMARY KEY (`Wishlist_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

-- Indexes for table `Order_Cancellation`
ALTER TABLE `Order_Cancellation`
  ADD PRIMARY KEY (`Cancellation_ID`),
  ADD KEY `Order_ID` (`Order_ID`);

-- AUTO_INCREMENT for dumped tables
-- AUTO_INCREMENT for table `Brand`
ALTER TABLE `Brand` MODIFY `Brand_ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- AUTO_INCREMENT for table `category`
ALTER TABLE `category` MODIFY `Category_ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- AUTO_INCREMENT for table `product`
ALTER TABLE `product` MODIFY `Product_ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

-- AUTO_INCREMENT for table `User`
ALTER TABLE `User` MODIFY `User_ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- AUTO_INCREMENT for table `PC_Build`
ALTER TABLE `PC_Build` MODIFY `PC_ID` int NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `Orders`
ALTER TABLE `Orders` MODIFY `Order_ID` int NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `Order_Details`
ALTER TABLE `Order_Details` MODIFY `Order_Detail_ID` int NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `Cart`
ALTER TABLE `Cart` MODIFY `Cart_ID` int NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `Physical_Review`
ALTER TABLE `Product_Review` MODIFY `Review_ID` int NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `Admin`
ALTER TABLE `Admin` MODIFY `Admin_ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

-- AUTO_INCREMENT for table `Delivery`
ALTER TABLE `Delivery` MODIFY `Delivery_ID` int NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `Wishlist`
ALTER TABLE `Wishlist` MODIFY `Wishlist_ID` int NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `Order_Cancellation`
ALTER TABLE `Order_Cancellation` MODIFY `Cancellation_ID` int NOT NULL AUTO_INCREMENT;

-- Constraints for dumped tables
-- Constraints for table `category`
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`Brand_ID`) REFERENCES `Brand` (`Brand_ID`);

-- Constraints for table `product`
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `category` (`Category_ID`);

-- Constraints for table `PC_Build`
ALTER TABLE `PC_Build`
  ADD CONSTRAINT `pc_build_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `User` (`User_ID`);

-- Constraints for table `Orders`
ALTER TABLE `Orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `User` (`User_ID`);

-- Constraints for table `Order_Details`
ALTER TABLE `Order_Details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `Orders` (`Order_ID`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

-- Constraints for table `Cart`
ALTER TABLE `Cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `User` (`User_ID`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

-- Constraints for table `Physical_Review`
ALTER TABLE `Product_Review`
  ADD CONSTRAINT `product_review_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `Orders` (`Order_ID`);

-- Constraints for table `Delivery`
ALTER TABLE `Delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `Orders` (`Order_ID`);

-- Constraints for table `Wishlist`
ALTER TABLE `Wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `User` (`User_ID`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

-- Constraints for table `Order_Cancellation`
ALTER TABLE `Order_Cancellation`
  ADD CONSTRAINT `order_cancellation_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `Orders` (`Order_ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;