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
('C010', 'Networking', 'Network interface cards'),
('C011', 'Operating Systems', 'Operating system software for computers');

-- Add Microsoft as a brand for operating systems
INSERT INTO `brand` (`Brand_ID`, `Brand_Name`, `Brand_Info`) VALUES
('B011', 'Microsoft', 'Software company known for Windows operating system');

-- Add operating system products
INSERT INTO `product` (`Product_ID`, `Product_Name`, `Product_Description`, `Product_Price`, `Stock_Quantity`, `Category_ID`, `Brand_ID`) VALUES
('P048', 'Windows 11 Home', 'Windows 11 Home is the latest Windows operating system, featuring a modern interface, enhanced security, and improved performance. Includes all essential features for home users.', 199.99, 100, 'C011', 'B011'),
('P049', 'Windows 10 Pro', 'Windows 10 Pro offers advanced security and management features for professionals and businesses. Includes BitLocker, Remote Desktop, and other professional tools.', 149.99, 100, 'C011', 'B011'); 