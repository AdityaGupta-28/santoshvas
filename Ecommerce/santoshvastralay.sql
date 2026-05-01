-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 10:20 AM
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
-- Database: `santoshvastralay`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `full_name` varchar(250) NOT NULL,
  `email_id` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `full_name`, `email_id`, `password`) VALUES
(1, 'Haridwar Prasad', 'admin@gmail.com', 'e10adc3949ba59abbe56e057f20f883e');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_color`
--

CREATE TABLE `tbl_color` (
  `id` int(11) NOT NULL,
  `color_name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_color`
--

INSERT INTO `tbl_color` (`id`, `color_name`) VALUES
(1, 'Red'),
(2, 'Yellow'),
(3, 'Green'),
(4, 'Blue'),
(5, 'Black'),
(6, 'white'),
(7, 'Pink'),
(8, 'Orange'),
(9, 'Purple'),
(10, 'Maroon'),
(11, 'Sky'),
(12, 'Lagoon Blue');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_end_category`
--

CREATE TABLE `tbl_end_category` (
  `ecat_id` int(11) NOT NULL,
  `ecat_name` varchar(255) NOT NULL,
  `mcat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_end_category`
--

INSERT INTO `tbl_end_category` (`ecat_id`, `ecat_name`, `mcat_id`) VALUES
(1, 'Plan Shirts', 1),
(2, 'Check Shirts', 1),
(3, 'Cotton Shirts', 1),
(4, 'Nylon Shirts', 1),
(5, 'Plan Pants', 2),
(6, 'Check Pants', 2),
(7, 'White Dhoti', 7),
(8, 'Orange or Red Dhoti', 7),
(9, 'Rayon Coat', 3),
(10, 'Wedding Kurta', 4),
(11, 'Casual Kurta', 4),
(12, 'Printed Saree', 9),
(13, 'Work Saree', 9),
(14, 'Silk Saree', 9),
(15, 'Banarsi Sarees', 9),
(16, 'Bridal Lahenga', 12),
(17, 'Casual Lahenga', 12),
(18, 'Party Wear Lahenga', 12),
(19, 'Casual Wear', 10),
(20, 'Office Wear', 10),
(21, 'Party Wear', 10),
(22, 'Cotton Bedsheet', 13),
(23, 'Polyester Bedsheet', 13),
(24, 'Silk Bedsheet', 13),
(25, 'Door Curtains', 14),
(26, 'Window Curtains', 14),
(27, 'Winter Blanket', 15),
(28, 'Baby Blanket', 15),
(29, 'Travel Blanket', 15),
(30, 'Single Bed Net', 20),
(31, 'Double Bed Net', 20),
(32, 'Baby Cot Net', 20),
(33, 'Stole', 17);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mid_category`
--

CREATE TABLE `tbl_mid_category` (
  `mcat_id` int(11) NOT NULL,
  `mcat_name` varchar(255) NOT NULL,
  `tcat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_mid_category`
--

INSERT INTO `tbl_mid_category` (`mcat_id`, `mcat_name`, `tcat_id`) VALUES
(1, 'Shirt Fabrics', 1),
(2, 'Pant Fabrics', 1),
(3, 'Coat Fabrics', 1),
(4, 'Kurta Fabrics', 1),
(5, 'Pyjama fabrics', 1),
(6, 'Safari Fabrics', 1),
(7, 'Dhoti', 1),
(8, 'Warm Shirt Fabrics', 1),
(9, 'Saree', 2),
(10, 'Salwar Suit', 2),
(11, 'Kurti', 2),
(12, 'Lahenga', 2),
(13, 'Bedsheet', 3),
(14, 'Curtains', 3),
(15, 'Blanket', 3),
(16, 'Door Curtains', 3),
(17, 'shawl', 2),
(18, 'Mosquito net', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_orders`
--

CREATE TABLE `tbl_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(30) DEFAULT NULL,
  `payment_txn_id` varchar(100) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_orders`
--

INSERT INTO `tbl_orders` (`id`, `user_id`, `total_amount`, `status`, `payment_status`, `payment_method`, `payment_txn_id`, `paid_amount`, `paid_at`, `payment_notes`, `shipping_address`, `created_at`, `updated_at`) VALUES
(1, 2, 1399.00, 'processing', 'unpaid', NULL, NULL, NULL, NULL, NULL, 'Bishunpura, gopalganj, Bihar (841407)', '2025-04-17 19:40:57', '2025-04-18 03:38:14'),
(2, 1, 2999.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, 'Patna', '2025-04-18 05:15:46', '2025-04-18 05:15:46');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_order_items`
--

CREATE TABLE `tbl_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_order_items`
--

INSERT INTO `tbl_order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 13, 1, 1399.00, '2025-04-17 19:40:57'),
(2, 2, 25, 1, 2999.00, '2025-04-18 05:15:46');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product`
--

CREATE TABLE `tbl_product` (
  `p_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_old_price` varchar(10) NOT NULL,
  `p_current_price` varchar(10) NOT NULL,
  `p_qty` int(10) NOT NULL,
  `p_featured_photo` varchar(255) NOT NULL,
  `p_description` text NOT NULL,
  `p_short_description` text NOT NULL,
  `p_feature` text NOT NULL,
  `p_condition` text NOT NULL,
  `p_return_policy` text NOT NULL,
  `p_total_view` int(11) NOT NULL,
  `p_is_featured` int(1) NOT NULL,
  `p_is_active` int(1) NOT NULL,
  `ecat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_product`
--

INSERT INTO `tbl_product` (`p_id`, `p_name`, `p_old_price`, `p_current_price`, `p_qty`, `p_featured_photo`, `p_description`, `p_short_description`, `p_feature`, `p_condition`, `p_return_policy`, `p_total_view`, `p_is_featured`, `p_is_active`, `ecat_id`) VALUES
(1, 'Men\\\'s Regular Fit Ditsy Printed Casual Shirt', '1799', '999', 5, '6800d5d290eda.jpg', '0', 'Product Dimensions ? : ? 20 x 10 x 5 cm; 500 g', '', '', '', 0, 0, 1, 3),
(2, 'DAMENSCH Men\\\'s Cotton Blend Regular Fit Button Down Shirt ( Pack of 1 )', '789', '567', 8, '6800d82e30f77.jpg', 'Perfect Pique Fabric: Our shirt for men is made from a lightweight pique fabric, offering a comfortable and flattering fit on your body. The subtle shine finish adds a touch of sophistication to the design, making you look smart and stylish no matter where you go.', 'Care Instructions: Machine Wash\\r\\nFit Type: Regular Fit', '', '', '', 0, 0, 1, 3),
(3, 'DAMENSCH Men\\\'s Cotton Blend Regular Fit Button Down Shirt ( Pack of 1 )', '863', '565', 6, '6800d93e9b45b.jpg', '', 'Care Instructions: Machine Wash\\r\\nFit Type: Relaxed Fit', '', '', '', 0, 0, 1, 4),
(4, 'StitchX Men\\\'s Cotton Regular Fit Shirt ( Pack of 1 )', '1499', '689', 4, '6800daa92f0a4.jpg', 'This Shirt for Men Check Closure:- Button | Colour :-Cerulean Blue | Sleeve Type :- Full Sleeve | Occasion :- Casual Shirts| Fabric: Soft Touch Cotton\\r\\nSleeve & neck Type: This Mens Form', 'Check shirt for men is made with Pure 100% Cotton Check Shirts for Men material. This Stylish Checked Shirt for Men gives a comfortable and breathable fit', '', '', '', 0, 0, 1, 2),
(5, 'UNITED COLORS OF BENETTON Regular Spread Collar Solid Shirt', '2199', '1499', 5, '6800db53be3ec.jpg', '', '100% Cotton\\r\\nButton Closure', '', '', '', 0, 0, 1, 1),
(6, 'JHABAK\\\'S Unstitched Viscose Rayon Self Design Trouser Fabric - 1.30 Meters', '999', '499', 8, '6800dd2207806.jpg', '', 'Best in class \\\" Viscose Matty Fabric \\\"\\r\\nExquisitely tailored unstitched Viscose Matty piece that can be tailored to a perfect fit\\r\\nPant Length: 1.30 meters', '', '', '', 0, 0, 1, 5),
(7, 'Siyaram Men\\\'s Cotton Check Unstitched Trouser Fabric (blue, Free Size)', '1599', '1149', 20, '6800de56e7323.jpg', '', 'Breathable and soft fabric which can keep you looking smart in cool winters or hot humid tropical summer weather. It has amazing ability to absorb sweat and yet keep looking smart and fresh', '', '', '', 0, 0, 1, 6),
(8, 'RAMRAJ COTTON Men Solid Pure Cotton Dhoti', '579', '579', 20, '6800e08bce410.jpg', 'MATERIAL: RAMRAJ Dhotis are made from fine, high-quality cotton fabric, which provides comfort and durability.\\r\\nCOLOR: Classic White color dhoti pairs easily with various upper garments, making it a popular choice for many events.', 'DHOTI SIZE: 1.27 X2.00 Meter | Single Layered Dhoti.\\r\\nZARI BORDER: The 1/4 inch gold zari border adds elegance, making it perfect for formal occasions and ceremonies, with intricate patterns that bring a sophisticated, traditional touch.', '', '', '', 0, 0, 1, 7),
(9, 'PRAKASAM COTTON Men Dhoti', '350', '250', 11, '6800e11ecb62b.jpg', '', '', '', '', '', 0, 0, 1, 8),
(10, 'BIGREAMS Unstitched 70% Wool Three Piece Suit Fabric For Men\\\\\\\'s - Coat, Pants, Vest, Wrinkle Free Material for Nehru Jacket, Free Size, 0% color fade | 58\\\\\\\" WIDE', '3299', '2499', 4, '6800e20b41252.jpg', '0', '- *Premium Quality Material*: Crafted with 70% high-grade wool, ensuring a luxurious feel and excellent breathability for all-day comfort.', '', '', '', 0, 0, 1, 9),
(11, 'VASTRAMAY Men Kurta Fabric', '899', '649', 7, '6800e2f89c15f.jpg', '', '', '', '', '', 0, 0, 1, 11),
(12, 'Exporthub Set of 2 Piece 100% Room Darkening Thermal Insulated Noise Reducing Brown Color Eyelet Blackout Curtains - (EHSPR241) (Cream, ?Long Door- 8 Feet (Pack of 1)', '1399', '549', 10, '6800e3ec66459.jpg', '', '', '', '', '', 0, 0, 1, 25),
(13, 'Cloth Fusion Blackout Window Curtains 5 Feet Long Set of 2, Room Darkening Blackout Curtains 5 Feet for Window with Grommet Design for Home Decor (5x4 Feet, light sky)', '1199', '1399', 7, '6800e4c0ce945.jpg', '', '', '', '', '', 0, 0, 1, 26),
(14, 'Handloom Cotton Printed Fabric for Women – 2.5 Meters Unstitched | Breathable & Lightweight Dress Material for Kurta, Top, Suit & DIY Sewing | Natural Cotton Fabric for Tailoring & Crafting', '699', '499', 20, '6800e5dde5ab1.jpg', '', '', '', '', '', 0, 0, 1, 19),
(15, 'Women\\\'s Venpattu Silk Blouse Piece| Dress Material - 1 Meter| Multicolor Embroidery with Sequin Work Blouse piece| Premium Fabric for Custom Blouses', '1299', '899', 4, '6800e6bd45ce5.jpg', '', '', '', '', '', 0, 0, 1, 21),
(16, 'MIRCHI FASHION Women\\\'s Designer Chiffon Floral Prints Saree with Blouse Piece', '2199', '1699', 6, '6800e7d622710.jpg', '', '', '', '', '', 0, 1, 1, 12),
(17, 'MIRCHI FASHION Women\\\'s Designer Chiffon  Saree with Blouse Piece', '899', '899', 7, '6800e995bc5fa.jpg', '', '', '', '', '', 0, 1, 1, 12),
(18, 'Story@Home Door Curtains 7 Feet Long Set of 1 | Floral Printed Cotton Curtain | Light Filtering Curtains | Curtain for Living Room | (116 x 215 cm, White & Blue) | Perfect for Home Decor', '1599', '699', 3, '6800ea65289ab.jpg', '', '', '', '', '', 0, 0, 1, 25),
(19, 'PARTHVI Women\\\'s Printed Cotton Anarkali Kurta & Pant With Dupatta Set Fabric', '1999', '999', 5, '6800ebfb37e91.jpg', '', '', '', '', '', 0, 0, 1, 20),
(20, 'Krystle Poly Cotton Soft Mosquito Net for Single Bed|Size- 3 x 6,(Purple)', '525', '425', 8, '6800ecb06db42.jpg', '', '', '', '', '', 0, 0, 1, 31),
(21, 'Foldable Net for Baby Safe & Easy Use,Ensures Your Baby\\\'s Safe Sleep-135cmX65cmX65cm 0 to 3Years- Ocean Green', '999', '599', 6, '6800ed6ba6236.jpg', '', '', '', '', '', 0, 0, 1, 32),
(22, 'Florida All Season Ultra Soft Kids AC Blanket/Baby Wrapper/Flannel Baby Blanket for 0-5 Yrs Kids(110x150cm) Pack of 2, Sky Blue Grey', '1299', '649', 4, '6800eef71e5e8.jpg', '', '\\r\\nAll-Season Comfort: Designed to keep babies comfortable in any weather, offering warmth in winter and breathability in summer.\\r\\nSoft and Gentle: Made from ultra-soft materials and skin friendly to ensure maximum comfort for delicate baby skin.', '', '', '', 0, 0, 1, 28),
(23, 'Varni Fabrics Women\\\\\\\'s Semi-stitched Net Heavy Multi Embroidered Lehenga Choli With Dupatta', '4599', '3899', 4, '68010d93ae7c0.jpg', '0', 'Measurements: Lehenga(Semi-Stitched) Flair : 3.50mtr, waist-43\\\\\\\"; Blouse(Unstitched) :-(1.0 mtr)\\\\\\\"; Dupatta Length: (2.50 mtr)\\\\r\\\\nLehengha Fabric:-Net||Blouse Fabric :-Net with Embroidered work & Sleeves|| Dupata Fabric:-Net', '', '', '', 0, 1, 1, 17),
(24, 'SPREAD SPAIN Sherpa Blanket Cozy Knit Heaven | 100% Cotton Lightweight & Extra Warm Single Bed Quilt Blanket for Home Bedding | Anti-Bacterial & Ultimately Soft | Blankets for Winters - Grey', '4999', '4499', 5, '68010ed060ed8.jpg', '', 'Premium Material: Crafted from 100% premium cotton, this Sherpa blanket combines lightweight construction with exceptional warmth. The cotton material is gentle on the skin, making it perfect for people with sensitive skin or allergies. Its durable design ensures it withstands daily use without compromising comfort.', '', '', '', 0, 0, 1, 27),
(25, 'Cloth Fusion 500 GSM Sherpa Quilt Blanket for Winter Double Size, Perfect Heavy Winter Razai Quilt for Double Bed (88\\\" X 90\\\" inches, Navy & Grey)', '3549', '2999', 3, '68010f66ecb66.jpg', '', '', '', '', '', 0, 1, 1, 27),
(26, 'SIRIL Women\\\'s Georgette Hot Fixing Stone Work Saree with Unstitched Blouse Piece', '3842', '2199', 6, '680110933a18d.jpg', '', '', '', '', '', 0, 1, 1, 13),
(27, 'KD Women Floral Print Anarkali Kurta with Pant and Dupatta Set Anarkali Kurti for Women', '1999', '1599', 5, '680111badd468.jpg', '', '', '', '', '', 0, 0, 1, 19),
(28, 'Sugathari Women\\\'s Pure Kanjivaram Silk Saree Banarasi Silk Saree With Blouse Piece (SAM PARI S-7)', '3699', '2999', 3, '6801132c846aa.jpg', '', 'Fabric: this kanchipuram style saree have soft finished art silk fabric easy and comfortable to wear\\r\\nBlouse: this saree have beautiful blouse made from Art Silk fabric with Jecquard Woven with zari border. Includes: 1 saree unstitched blouse. Saree length- 5.5 metre, blouse length- 0.8 metre / size :free size', '', '', '', 0, 1, 1, 15),
(29, 'Men\\\\\\\\\\\\\\\'s Georgette Chikankari Work & Sequins Dupatta', '11999', '8999', 3, 'product_1744904227.jpg', '0', 'Package Contains: 1 Men\\\\\\\\\\\\\\\'s Dupatta\\\\\\\\r\\\\\\\\nDesign: Flaunt your traditional look with this exquisite warm white georgette Dupatta. Perfect to accessorize with your sherwani or kurta pajama at any festive occasion. Its rich color and soft texture will add a touch of sophistication and elegance to your attire. Combine it at haldi, mehndi, sangeet, or even at a grand festive gathering to look your regal best. ', '', '', '', 0, 1, 1, 10),
(30, 'Weavers Villa Women\\\'s Shawl', '999', '699', 8, '680119251e3c6.jpg', '', '', '', '', '', 0, 1, 1, 33),
(31, 'Women\\\\\\\'s Kanjivaram Soft Silk Saree With Blouse Piece', '2499', '1899', 4, '68011a327efc4.jpg', '', '', '', '', '', 0, 0, 1, 14),
(32, 'WINTAGE Men\\\'s Single Breasted Tailored Blazer', '5699', '3899', 2, '68011c1337d11.jpg', '', '', '', '', '', 0, 1, 1, 9),
(33, 'Zeel Clothing Women\\\'s Silk Semi-Stitched Lehenga Choli (7034-Wedding-Bridal-New)', '14999', '8999', 3, '68011d440fba1.jpg', '', 'Lehenga Work: Dori, Sequins And Multi Color Thread Embroidery Work, Blouse Work: Dori, Sequins And Multi Color Thread Embroidery Work , Dupatta Work: Dori, Sequins Embroidery Work\\r\\nMeasurements: Lehenga Flair : 3.50Mtr, Waist-44\\\", Height-44\\\"; Blouse Size: Up To 42\\\", Dupatta Length: 2.30 Mtr\\r\\nPackage Contains: 1 Semi-Stitched Lehenga With 2 Dupattas And Unstitched Blouse; Care Instructions: Dry Clean Only', '', '', '', 0, 1, 1, 16),
(34, '300 TC Cotton Feel Glace Cotton Elastic Fitted Printed King Size Double Bed Bedsheet with 2 Pillow Coverm Fits Upto 8 inches Mattress,Size- 72x78x10 Inches,Beige Anokhi', '899', '799', 5, '68013b67bca97.jpg', '', 'Elastic Fitted Bedsheet with 2 Pillow Covers Size :- 72\\\" X 78\\\" x 10\\\" Inch\\r\\nIdeal for Mattresses having height 4 to 8 inches and length 6 x 6.5 ft. Easy to tuck-in even on heavy mattress.Full elastic trim for perfect fit.\\r\\nColor:- Multi ,Material :- Glace Cotton', '', '', '', 0, 1, 1, 22);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_color`
--

CREATE TABLE `tbl_product_color` (
  `id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_product_color`
--

INSERT INTO `tbl_product_color` (`id`, `color_id`, `p_id`) VALUES
(2, 6, 1),
(3, 10, 2),
(4, 12, 3),
(5, 6, 4),
(6, 4, 6),
(7, 4, 7),
(8, 6, 8),
(9, 8, 9),
(10, 9, 11);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_photo`
--

CREATE TABLE `tbl_product_photo` (
  `pp_id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `p_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_product_photo`
--

INSERT INTO `tbl_product_photo` (`pp_id`, `photo`, `p_id`) VALUES
(1, '6800d5d291585.jpg', 1),
(2, '6800d5d291ab9.jpg', 1),
(3, '6800d5d291f60.jpg', 1),
(4, '6800d82e31659.jpg', 2),
(5, '6800d82e31c60.jpg', 2),
(6, '6800d82e32582.jpg', 2),
(7, '6800d82e32f77.jpg', 2),
(8, '6800d82e337d9.jpg', 2),
(9, '6800d93e9b857.jpg', 3),
(10, '6800d93e9bb06.jpg', 3),
(11, '6800d93e9c062.jpg', 3),
(12, '6800d93e9c82b.jpg', 3),
(13, '6800d93e9d0dd.jpg', 3),
(14, '6800daa92f551.jpg', 4),
(15, '6800daa92fc33.jpg', 4),
(16, '6800daa9305fa.jpg', 4),
(17, '6800daa931019.jpg', 4),
(18, '6800db53be9b7.jpg', 5),
(19, '6800db53bf654.jpg', 5),
(20, '6800db53c03d4.jpg', 5),
(21, '6800db53c1000.jpg', 5),
(22, '6800dd2207eaa.jpg', 6),
(23, '6800dd22084d9.jpg', 6),
(24, '6800dd2208d1b.jpg', 6),
(25, '6800de56e7a1c.jpg', 7),
(26, '6800de56e83ff.jpg', 7),
(27, '6800e08bce867.jpg', 8),
(28, '6800e08bcec98.jpg', 8),
(29, '6800e08bcf2df.jpg', 8),
(30, '6800e08bcfbe0.jpg', 8),
(31, '6800e11ecbc68.jpg', 9),
(32, '6800e20b415d9.jpg', 10),
(33, '6800e20b418fc.jpg', 10),
(34, '6800e20b472b5.jpg', 10),
(35, '6800e20b479e9.jpg', 10),
(36, '6800e2f89c5b5.jpg', 11),
(37, '6800e2f89ccf2.jpg', 11),
(38, '6800e2f89d602.jpg', 11),
(39, '6800e2f89de3f.jpg', 11),
(40, '6800e3ec66a97.jpg', 12),
(41, '6800e3ec66db9.jpg', 12),
(42, '6800e3ec670ba.jpg', 12),
(43, '6800e3ec6732e.jpg', 12),
(44, '6800e3ec6759e.jpg', 12),
(45, '6800e4c0cf067.jpg', 13),
(46, '6800e4c0cfc91.jpg', 13),
(47, '6800e4c0d06b9.jpg', 13),
(48, '6800e5dde5e80.jpg', 14),
(49, '6800e5dde63e8.jpg', 14),
(50, '6800e5dde6e80.jpg', 14),
(51, '6800e5dde77a5.jpg', 14),
(52, '6800e6bd46063.jpg', 15),
(53, '6800e6bd4630c.jpg', 15),
(54, '6800e6bd46cd2.jpg', 15),
(55, '6800e6bd478af.jpg', 15),
(56, '6800e7d6230ec.jpg', 16),
(57, '6800e7d62399b.jpg', 16),
(58, '6800e7d624262.jpg', 16),
(59, '6800e7d624a66.jpg', 16),
(60, '6800e995bc9bd.jpg', 17),
(61, '6800e995bcc9e.jpg', 17),
(62, '6800e995bd2d8.jpg', 17),
(63, '6800e995bdbea.jpg', 17),
(64, '6800ea6528d3d.jpg', 18),
(65, '6800ea6528fc6.jpg', 18),
(66, '6800ea6529977.jpg', 18),
(67, '6800ea652a2e8.jpg', 18),
(68, '6800ebfb3824c.jpg', 19),
(69, '6800ebfb384fe.jpg', 19),
(70, '6800ebfb38bd2.jpg', 19),
(71, '6800ebfb39786.jpg', 19),
(72, '6800ecb06e130.jpg', 20),
(73, '6800ed6ba68a5.jpg', 21),
(74, '6800ed6ba7204.jpg', 21),
(75, '6800ed6ba7e2b.jpg', 21),
(76, '6800eef71eca6.jpg', 22),
(77, '6800eef71f5d2.jpg', 22),
(78, '6800eef72001c.jpg', 22),
(79, '68010d93aeb82.jpg', 23),
(80, '68010d93af13a.jpg', 23),
(81, '68010d93afa48.jpg', 23),
(82, '68010d93b03ad.jpg', 23),
(83, '68010ed061341.jpg', 24),
(84, '68010ed061a2e.jpg', 24),
(85, '68010ed062352.jpg', 24),
(86, '68010ed062b6b.jpg', 24),
(87, '68010ed0632ae.jpg', 24),
(88, '68010f66ed304.jpg', 25),
(89, '68010f66ed985.jpg', 25),
(90, '68010f66ee17e.jpg', 25),
(91, '68010f66ee8b0.jpg', 25),
(92, '68010f66eefaf.jpg', 25),
(93, '680110933a616.jpg', 26),
(94, '680110933ab4b.jpg', 26),
(95, '680110933b0dc.jpg', 26),
(96, '680111badda9d.jpg', 27),
(97, '680111bade13f.jpg', 27),
(98, '680111badea81.jpg', 27),
(99, '6801132c84d56.jpg', 28),
(100, '6801132c855bb.jpg', 28),
(101, '6801132c85ce7.jpg', 28),
(102, '6801132c86416.jpg', 28),
(107, '680119251eec4.jpg', 30),
(108, '680119251f8c6.jpg', 30),
(109, '680119252057d.jpg', 30),
(110, '68011a327fbe0.jpg', 31),
(111, '68011a3280a77.jpg', 31),
(112, '68011a328179d.jpg', 31),
(113, '68011a32825d6.jpg', 31),
(114, '68011a3283209.jpg', 31),
(115, '68011c1338350.jpg', 32),
(116, '68011c1338b1f.jpg', 32),
(117, '68011d4410362.jpg', 33),
(118, '68011d4410e0e.jpg', 33),
(119, '68011d44117c0.jpg', 33),
(120, 'product_1744904227_0.jpg', 29),
(121, 'product_1744904227_1.jpg', 29),
(122, 'product_1744904227_2.jpg', 29),
(123, '68013b67bd4a1.jpg', 34),
(124, '68013b67bdca2.jpg', 34);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_size`
--

CREATE TABLE `tbl_product_size` (
  `id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_product_size`
--

INSERT INTO `tbl_product_size` (`id`, `size_id`, `p_id`) VALUES
(4, 2, 1),
(5, 4, 1),
(6, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_size`
--

CREATE TABLE `tbl_size` (
  `id` int(11) NOT NULL,
  `size_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_size`
--

INSERT INTO `tbl_size` (`id`, `size_name`) VALUES
(1, 'XS'),
(2, 'S'),
(3, 'M'),
(4, 'L'),
(5, 'XL'),
(6, 'XXL'),
(7, '36'),
(8, '38'),
(10, '40');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_todo`
--

CREATE TABLE `tbl_todo` (
  `id` int(11) NOT NULL,
  `task` varchar(255) NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_todo`
--

INSERT INTO `tbl_todo` (`id`, `task`, `status`, `created_at`, `admin_id`) VALUES
(5, 'ffg', 'pending', '2025-04-17 23:25:33', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_top_category`
--

CREATE TABLE `tbl_top_category` (
  `tcat_id` int(11) NOT NULL,
  `tcat_name` varchar(255) NOT NULL,
  `show_on_menu` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_top_category`
--

INSERT INTO `tbl_top_category` (`tcat_id`, `tcat_name`, `show_on_menu`) VALUES
(1, 'Men', 1),
(2, 'Women', 1),
(3, 'Home Textiles', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `name`, `email`, `password`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Aditya Gupta', 'adityagupta112040@gmail.com', '$2y$10$qeMgLRrsmawoL6etdIHEte5a09ZeOQ/kDQV.OUoPHn1dQdTCcZ9di', '8235779190', 'Patna', '2025-04-16 03:47:19', '2025-04-16 09:00:47'),
(2, 'Aman', 'aditya12315413@gmail.com', '$2y$10$NAKO5SD45cVD02FPq..BWuNy1/rYb00pyajgEDWa33R5L3o/YXNe.', '9056824321', 'Mumbai', '2025-04-16 10:07:10', '2025-04-17 22:52:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_color`
--
ALTER TABLE `tbl_color`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_end_category`
--
ALTER TABLE `tbl_end_category`
  ADD PRIMARY KEY (`ecat_id`);

--
-- Indexes for table `tbl_mid_category`
--
ALTER TABLE `tbl_mid_category`
  ADD PRIMARY KEY (`mcat_id`);

--
-- Indexes for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD PRIMARY KEY (`p_id`);

--
-- Indexes for table `tbl_product_color`
--
ALTER TABLE `tbl_product_color`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_product_photo`
--
ALTER TABLE `tbl_product_photo`
  ADD PRIMARY KEY (`pp_id`);

--
-- Indexes for table `tbl_product_size`
--
ALTER TABLE `tbl_product_size`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_size`
--
ALTER TABLE `tbl_size`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_todo`
--
ALTER TABLE `tbl_todo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `tbl_top_category`
--
ALTER TABLE `tbl_top_category`
  ADD PRIMARY KEY (`tcat_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_color`
--
ALTER TABLE `tbl_color`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_end_category`
--
ALTER TABLE `tbl_end_category`
  MODIFY `ecat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `tbl_mid_category`
--
ALTER TABLE `tbl_mid_category`
  MODIFY `mcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_product`
--
ALTER TABLE `tbl_product`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tbl_product_color`
--
ALTER TABLE `tbl_product_color`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_product_photo`
--
ALTER TABLE `tbl_product_photo`
  MODIFY `pp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `tbl_product_size`
--
ALTER TABLE `tbl_product_size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_size`
--
ALTER TABLE `tbl_size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_todo`
--
ALTER TABLE `tbl_todo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_top_category`
--
ALTER TABLE `tbl_top_category`
  MODIFY `tcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD CONSTRAINT `tbl_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_order_items`
--
ALTER TABLE `tbl_order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `tbl_product` (`p_id`);

--
-- Constraints for table `tbl_todo`
--
ALTER TABLE `tbl_todo`
  ADD CONSTRAINT `tbl_todo_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
