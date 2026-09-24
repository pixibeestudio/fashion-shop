-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 04:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fashion_shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `Category_ID` int(11) NOT NULL COMMENT 'Mã danh mục',
  `Category_Name` varchar(100) NOT NULL COMMENT 'Tên danh mục (Áo Nam, Quần Nữ...)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `Customer_ID` int(11) NOT NULL COMMENT 'Mã khách hàng',
  `Phone` varchar(15) NOT NULL COMMENT 'Số điện thoại',
  `Full_Name` varchar(100) NOT NULL COMMENT 'Tên khách',
  `Email` varchar(100) DEFAULT NULL,
  `Password_Hash` varchar(255) NOT NULL DEFAULT 'default_hashed_password',
  `Role_ID` int(11) NOT NULL DEFAULT 5,
  `Total_Points` int(11) DEFAULT 0 COMMENT 'Tổng điểm tích lũy hiện tại',
  `Tier_ID` int(11) NOT NULL COMMENT 'Hạng thẻ hiện tại'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_tiers`
--

CREATE TABLE `customer_tiers` (
  `Tier_ID` int(11) NOT NULL COMMENT 'Mã hạng',
  `Tier_Name` varchar(50) NOT NULL COMMENT 'Tên hạng (Đồng, Bạc, Vàng, VIP)',
  `Min_Points` int(11) DEFAULT 0 COMMENT 'Số điểm tối thiểu để đạt hạng này',
  `Discount_Percent` decimal(5,2) DEFAULT 0.00 COMMENT 'Phần trăm giảm giá mặc định cho hạng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `Employee_ID` int(11) NOT NULL COMMENT 'Mã nhân viên',
  `Role_ID` int(11) NOT NULL COMMENT 'Thuộc quyền nào',
  `Full_Name` varchar(100) NOT NULL COMMENT 'Họ tên',
  `Phone` varchar(15) NOT NULL COMMENT 'Số điện thoại (dùng để đăng nhập)',
  `Password_Hash` varchar(255) NOT NULL COMMENT 'Mật khẩu (đã mã hóa)',
  `Is_Active` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái nghỉ việc hay đang làm'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `Order_ID` int(11) NOT NULL COMMENT 'Mã hóa đơn',
  `Employee_ID` int(11) NOT NULL COMMENT 'Thu ngân tính tiền',
  `Customer_ID` int(11) DEFAULT NULL COMMENT 'Khách hàng (NULL nếu khách vãng lai)',
  `Promo_ID` int(11) DEFAULT NULL COMMENT 'Mã khuyến mãi đã dùng (nếu có)',
  `Order_Date` datetime DEFAULT current_timestamp() COMMENT 'Ngày giờ in bill',
  `Sub_Total` decimal(15,2) NOT NULL COMMENT 'Tổng tiền hàng',
  `Discount_Amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Tiền được giảm',
  `Final_Total` decimal(15,2) NOT NULL COMMENT 'Tiền khách thực trả',
  `Payment_Method` varchar(30) NOT NULL COMMENT 'Hình thức thanh toán',
  `Payment_Status` varchar(30) DEFAULT 'Unpaid' COMMENT 'Paid, Unpaid',
  `Delivery_Status` varchar(30) DEFAULT 'Pending' COMMENT 'Pending, Delivered, Canceled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `Order_Detail_ID` int(11) NOT NULL COMMENT 'Mã dòng bill',
  `Order_ID` int(11) NOT NULL COMMENT 'Thuộc bill nào',
  `Variant_ID` int(11) NOT NULL COMMENT 'Bán sản phẩm (SKU) nào',
  `Quantity` int(11) NOT NULL COMMENT 'Số lượng mua (>0)',
  `Unit_Price` decimal(15,2) NOT NULL COMMENT 'Giá bán tại thời điểm đó'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `Product_ID` int(11) NOT NULL COMMENT 'Mã sản phẩm',
  `Product_Name` varchar(255) NOT NULL COMMENT 'Tên sản phẩm',
  `Category_ID` int(11) NOT NULL COMMENT 'Thuộc danh mục nào',
  `Supplier_ID` int(11) NOT NULL COMMENT 'Nguồn nhập từ đâu',
  `Description` text DEFAULT NULL COMMENT 'Mô tả chung (Chất liệu vải...)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `Variant_ID` int(11) NOT NULL COMMENT 'Mã biến thể',
  `Product_ID` int(11) NOT NULL COMMENT 'Thuộc sản phẩm gốc nào',
  `SKU` varchar(50) NOT NULL COMMENT 'Mã vạch (Stock Keeping Unit)',
  `Color` varchar(30) NOT NULL COMMENT 'Màu sắc (Đỏ, Xanh, Trắng...)',
  `Size` varchar(10) NOT NULL COMMENT 'Kích cỡ (S, M, L, XL...)',
  `Cost_Price` decimal(15,2) NOT NULL COMMENT 'Giá vốn nhập vào',
  `Retail_Price` decimal(15,2) NOT NULL COMMENT 'Giá bán lẻ ra',
  `Stock_Quantity` int(11) DEFAULT 0 COMMENT 'Tồn kho thực tế'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `Promo_ID` int(11) NOT NULL COMMENT 'Mã khuyến mãi',
  `Promo_Code` varchar(20) DEFAULT NULL COMMENT 'Mã nhập (VD: BLACKFRIDAY)',
  `Discount_Type` varchar(20) NOT NULL COMMENT 'Loại giảm (Giảm %, hoặc Giảm tiền mặt)',
  `Discount_Value` decimal(15,2) NOT NULL COMMENT 'Giá trị giảm',
  `Start_Date` datetime NOT NULL COMMENT 'Thời điểm bắt đầu',
  `End_Date` datetime NOT NULL COMMENT 'Thời hạn kết thúc áp dụng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `PO_ID` int(11) NOT NULL COMMENT 'Mã phiếu nhập',
  `Supplier_ID` int(11) NOT NULL COMMENT 'Nhập từ ai',
  `Employee_ID` int(11) NOT NULL COMMENT 'Nhân viên nào nhận hàng',
  `Import_Date` datetime DEFAULT current_timestamp() COMMENT 'Ngày giờ nhập',
  `Total_Amount` decimal(15,2) NOT NULL COMMENT 'Tổng tiền phải trả cho xưởng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_details`
--

CREATE TABLE `purchase_order_details` (
  `PO_Detail_ID` int(11) NOT NULL COMMENT 'Mã dòng nhập',
  `PO_ID` int(11) NOT NULL COMMENT 'Thuộc phiếu nhập nào',
  `Variant_ID` int(11) NOT NULL COMMENT 'Nhập biến thể (SKU) nào',
  `Quantity` int(11) NOT NULL COMMENT 'Số lượng nhập (>0)',
  `Unit_Price` decimal(15,2) NOT NULL COMMENT 'Giá nhập tại thời điểm đó'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `Role_ID` int(11) NOT NULL COMMENT 'Mã quyền',
  `Role_Name` varchar(50) NOT NULL COMMENT 'Tên quyền (Admin, Quản lý, Thu ngân, Thủ kho)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `Supplier_ID` int(11) NOT NULL COMMENT 'Mã nhà cung cấp',
  `Supplier_Name` varchar(150) NOT NULL COMMENT 'Tên xưởng / hãng',
  `Phone` varchar(15) DEFAULT NULL COMMENT 'Số điện thoại liên hệ',
  `Address` varchar(255) DEFAULT NULL COMMENT 'Địa chỉ liên hệ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`Customer_ID`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD KEY `Tier_ID` (`Tier_ID`),
  ADD KEY `customers_ibfk_2` (`Role_ID`);

--
-- Indexes for table `customer_tiers`
--
ALTER TABLE `customer_tiers`
  ADD PRIMARY KEY (`Tier_ID`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`Employee_ID`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `Employee_ID` (`Employee_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Promo_ID` (`Promo_ID`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`Order_Detail_ID`),
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `Variant_ID` (`Variant_ID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`Product_ID`),
  ADD KEY `Category_ID` (`Category_ID`),
  ADD KEY `Supplier_ID` (`Supplier_ID`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`Variant_ID`),
  ADD UNIQUE KEY `SKU` (`SKU`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`Promo_ID`),
  ADD UNIQUE KEY `Promo_Code` (`Promo_Code`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`PO_ID`),
  ADD KEY `Supplier_ID` (`Supplier_ID`),
  ADD KEY `Employee_ID` (`Employee_ID`);

--
-- Indexes for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD PRIMARY KEY (`PO_Detail_ID`),
  ADD KEY `PO_ID` (`PO_ID`),
  ADD KEY `Variant_ID` (`Variant_ID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`Role_ID`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`Supplier_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã danh mục';

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã khách hàng';

--
-- AUTO_INCREMENT for table `customer_tiers`
--
ALTER TABLE `customer_tiers`
  MODIFY `Tier_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã hạng';

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `Employee_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã nhân viên';

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã hóa đơn';

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `Order_Detail_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã dòng bill';

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã sản phẩm';

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `Variant_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã biến thể';

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `Promo_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã khuyến mãi';

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `PO_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã phiếu nhập';

--
-- AUTO_INCREMENT for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  MODIFY `PO_Detail_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã dòng nhập';

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `Role_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã quyền';

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `Supplier_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Mã nhà cung cấp';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`Tier_ID`) REFERENCES `customer_tiers` (`Tier_ID`),
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`Role_ID`) REFERENCES `roles` (`Role_ID`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `roles` (`Role_ID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`Employee_ID`) REFERENCES `employees` (`Employee_ID`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`Customer_ID`) REFERENCES `customers` (`Customer_ID`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`Promo_ID`) REFERENCES `promotions` (`Promo_ID`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `orders` (`Order_ID`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`Variant_ID`) REFERENCES `product_variants` (`Variant_ID`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`Category_ID`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`Supplier_ID`) REFERENCES `suppliers` (`Supplier_ID`);

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`Supplier_ID`) REFERENCES `suppliers` (`Supplier_ID`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`Employee_ID`) REFERENCES `employees` (`Employee_ID`);

--
-- Constraints for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD CONSTRAINT `purchase_order_details_ibfk_1` FOREIGN KEY (`PO_ID`) REFERENCES `purchase_orders` (`PO_ID`),
  ADD CONSTRAINT `purchase_order_details_ibfk_2` FOREIGN KEY (`Variant_ID`) REFERENCES `product_variants` (`Variant_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
