-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 05, 2026 at 06:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ncf_oilfield`
--

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `cadangan_mbbl` decimal(14,4) NOT NULL COMMENT 'Total cadangan minyak (Mbbl)',
  `harga_minyak` decimal(10,2) NOT NULL COMMENT 'Harga minyak asumsi ($/bbl)',
  `tahun_hitung` smallint(6) NOT NULL DEFAULT 10 COMMENT 'Jumlah tahun analisis NCF',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 51.00 COMMENT 'Tax rate (%)',
  `status_proyek` varchar(20) NOT NULL DEFAULT 'Direncanakan' COMMENT 'Status proyek',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Master data lapangan minyak';

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `nama`, `cadangan_mbbl`, `harga_minyak`, `tahun_hitung`, `tax_rate`, `status_proyek`, `created_at`, `updated_at`) VALUES
(4, 'Cepu Resevoir', 4320.0000, 32.00, 20, 51.00, 'Selesai', '2026-06-04 11:47:23', '2026-06-04 12:19:00'),
(7, 'Lilbah Supreme', 4320.0000, 32.00, 10, 51.00, 'Berjalan', '2026-06-05 03:34:29', '2026-06-05 03:43:26'),
(8, 'Lilbah Supreme', 4320.0000, 32.00, 10, 51.00, 'Direncanakan', '2026-06-05 04:02:01', '2026-06-05 04:02:01');

-- --------------------------------------------------------

--
-- Table structure for table `investasi`
--

CREATE TABLE `investasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `field_id` int(10) UNSIGNED NOT NULL,
  `capital` decimal(14,4) NOT NULL COMMENT 'Investasi capital ($M)',
  `non_capital` decimal(14,4) NOT NULL COMMENT 'Investasi non-capital ($M)',
  `total_investasi` decimal(14,4) DEFAULT NULL COMMENT 'Total investasi = capital + non_capital ($M)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Investasi capital & non-capital. Di dihitung dari total_investasi / tahun_hitung';

--
-- Dumping data for table `investasi`
--

INSERT INTO `investasi` (`id`, `field_id`, `capital`, `non_capital`, `total_investasi`) VALUES
(4, 4, 13000.0000, 8002.0000, NULL),
(7, 7, 13000.0000, 8000.0000, 21000.0000),
(8, 8, 0.0000, 0.0000, 20999.0000);

-- --------------------------------------------------------

--
-- Table structure for table `ncf_results`
--

CREATE TABLE `ncf_results` (
  `id` int(10) UNSIGNED NOT NULL,
  `field_id` int(10) UNSIGNED NOT NULL,
  `tahun_ke` smallint(6) NOT NULL,
  `produksi_mbbl` decimal(14,4) NOT NULL,
  `cum_produksi` decimal(14,4) NOT NULL,
  `gross_revenue` decimal(14,4) NOT NULL COMMENT 'Produksi × Harga ($M)',
  `opex` decimal(14,4) NOT NULL COMMENT 'Biaya operasi tahun ini ($M)',
  `depresiasi_di` decimal(14,4) NOT NULL COMMENT 'Di = (Capital+NonCapital)/TahunHitung ($M)',
  `taxable_income` decimal(14,4) NOT NULL COMMENT 'Gross Rev − Opex − Di ($M)',
  `tax` decimal(14,4) NOT NULL COMMENT 'Taxable Income × Tax Rate ($M)',
  `ncf` decimal(14,4) NOT NULL COMMENT 'Gross Rev − Opex − Tax ($M)',
  `cum_ncf` decimal(14,4) NOT NULL COMMENT 'Kumulatif NCF s.d. tahun ini ($M)',
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Hasil kalkulasi NCF after-tax per tahun';

--
-- Dumping data for table `ncf_results`
--

INSERT INTO `ncf_results` (`id`, `field_id`, `tahun_ke`, `produksi_mbbl`, `cum_produksi`, `gross_revenue`, `opex`, `depresiasi_di`, `taxable_income`, `tax`, `ncf`, `cum_ncf`, `calculated_at`) VALUES
(101, 4, 1, 175.0000, 175.0000, 5600.0000, 182.0000, 1050.1000, 4367.9000, 2227.6290, 3190.3710, 3190.3710, '2026-06-04 12:21:44'),
(102, 4, 2, 201.0000, 376.0000, 6432.0000, 182.0000, 1050.1000, 5199.9000, 2651.9490, 3598.0510, 6788.4220, '2026-06-04 12:21:44'),
(103, 4, 3, 217.0000, 593.0000, 6944.0000, 182.0000, 1050.1000, 5711.9000, 2913.0690, 3848.9310, 10637.3530, '2026-06-04 12:21:44'),
(104, 4, 4, 198.0000, 791.0000, 6336.0000, 186.5500, 1050.1000, 5099.3500, 2600.6685, 3548.7815, 14186.1345, '2026-06-04 12:21:44'),
(105, 4, 5, 192.0600, 983.0600, 6145.9200, 191.2138, 1050.1000, 4904.6063, 2501.3492, 3453.3571, 17639.4916, '2026-06-04 12:21:44'),
(106, 4, 6, 186.2982, 1169.3582, 5961.5424, 195.9941, 1050.1000, 4715.4483, 2404.8786, 3360.6697, 21000.1612, '2026-06-04 12:21:44'),
(107, 4, 7, 180.7093, 1350.0675, 5782.6961, 200.8939, 1050.1000, 4531.7022, 2311.1681, 3270.6341, 24270.7953, '2026-06-04 12:21:44'),
(108, 4, 8, 175.2880, 1525.3554, 5609.2152, 205.9163, 1050.1000, 4353.1989, 2220.1315, 3183.1675, 27453.9628, '2026-06-04 12:21:44'),
(109, 4, 9, 170.0293, 1695.3848, 5440.9388, 211.0642, 1050.1000, 4179.7746, 2131.6850, 3098.1895, 30552.1523, '2026-06-04 12:21:44'),
(110, 4, 10, 164.9285, 1860.3132, 5277.7106, 216.3408, 1050.1000, 4011.2698, 2045.7476, 3015.6222, 33567.7745, '2026-06-04 12:21:44'),
(111, 4, 11, 159.9806, 2020.2938, 5119.3793, 221.7493, 1050.1000, 3847.5300, 1962.2403, 2935.3897, 36503.1642, '2026-06-04 12:21:44'),
(112, 4, 12, 155.1812, 2175.4750, 4965.7979, 227.2931, 1050.1000, 3688.4049, 1881.0865, 2857.4184, 39360.5826, '2026-06-04 12:21:44'),
(113, 4, 13, 150.5257, 2326.0008, 4816.8240, 232.9754, 1050.1000, 3533.7486, 1802.2118, 2781.6368, 42142.2194, '2026-06-04 12:21:44'),
(114, 4, 14, 146.0100, 2472.0107, 4672.3193, 238.7998, 1050.1000, 3383.4195, 1725.5439, 2707.9756, 44850.1950, '2026-06-04 12:21:44'),
(115, 4, 15, 141.6297, 2613.6404, 4532.1497, 244.7698, 1050.1000, 3237.2799, 1651.0128, 2636.3672, 47486.5621, '2026-06-04 12:21:44'),
(116, 4, 16, 137.3808, 2751.0212, 4396.1852, 250.8890, 1050.1000, 3095.1962, 1578.5501, 2566.7461, 50053.3083, '2026-06-04 12:21:44'),
(117, 4, 17, 133.2594, 2884.2806, 4264.2996, 257.1612, 1050.1000, 2957.0384, 1508.0896, 2499.0488, 52552.3571, '2026-06-04 12:21:44'),
(118, 4, 18, 129.2616, 3013.5422, 4136.3707, 263.5903, 1050.1000, 2822.6804, 1439.5670, 2433.2134, 54985.5705, '2026-06-04 12:21:44'),
(119, 4, 19, 125.3837, 3138.9259, 4012.2795, 270.1800, 1050.1000, 2691.9995, 1372.9198, 2369.1798, 57354.7502, '2026-06-04 12:21:44'),
(120, 4, 20, 121.6222, 3260.5481, 3891.9111, 276.9345, 1050.1000, 2564.8766, 1308.0871, 2306.8895, 59661.6398, '2026-06-04 12:21:44'),
(241, 7, 1, 175.0000, 175.0000, 5600.0000, 180.0000, 2100.0000, 3320.0000, 1693.2000, 3726.8000, 3726.8000, '2026-06-05 04:00:05'),
(242, 7, 2, 201.0000, 376.0000, 6432.0000, 180.0000, 2100.0000, 4152.0000, 2117.5200, 4134.4800, 7861.2800, '2026-06-05 04:00:05'),
(243, 7, 3, 214.0000, 590.0000, 6848.0000, 180.0000, 2100.0000, 4568.0000, 2329.6800, 4338.3200, 12199.6000, '2026-06-05 04:00:05'),
(244, 7, 4, 207.5800, 797.5800, 6642.5600, 184.5000, 2100.0000, 4358.0600, 2222.6106, 4235.4494, 16435.0494, '2026-06-05 04:00:05'),
(245, 7, 5, 201.3526, 998.9326, 6443.2832, 189.1125, 2100.0000, 4154.1707, 2118.6271, 4135.5436, 20570.5930, '2026-06-05 04:00:05'),
(246, 7, 6, 195.3120, 1194.2446, 6249.9847, 193.8403, 2100.0000, 3956.1444, 2017.6336, 4038.5108, 24609.1038, '2026-06-05 04:00:05'),
(247, 7, 7, 189.4527, 1383.6973, 6062.4852, 198.6863, 2100.0000, 3763.7988, 1919.5374, 3944.2614, 28553.3652, '2026-06-05 04:00:05'),
(248, 7, 8, 183.7691, 1567.4664, 5880.6106, 203.6535, 2100.0000, 3576.9571, 1824.2481, 3852.7090, 32406.0742, '2026-06-05 04:00:05'),
(249, 7, 9, 178.2560, 1745.7224, 5704.1923, 208.7448, 2100.0000, 3395.4475, 1731.6782, 3763.7693, 36169.8435, '2026-06-05 04:00:05'),
(250, 7, 10, 172.9083, 1918.6307, 5533.0665, 213.9634, 2100.0000, 3219.1031, 1641.7426, 3677.3605, 39847.2040, '2026-06-05 04:00:05'),
(291, 8, 1, 175.0000, 175.0000, 5600.0000, 180.0000, 0.0000, 5420.0000, 2764.2000, 2655.8000, 2655.8000, '2026-06-05 04:09:30'),
(292, 8, 2, 201.0000, 376.0000, 6432.0000, 180.0000, 0.0000, 6252.0000, 3188.5200, 3063.4800, 5719.2800, '2026-06-05 04:09:30'),
(293, 8, 3, 215.0000, 591.0000, 6880.0000, 180.0000, 0.0000, 6700.0000, 3417.0000, 3283.0000, 9002.2800, '2026-06-05 04:09:30'),
(294, 8, 4, 208.7650, 799.7650, 6680.4800, 184.5000, 0.0000, 6495.9800, 3312.9498, 3183.0302, 12185.3102, '2026-06-05 04:09:30'),
(295, 8, 5, 202.7108, 1002.4758, 6486.7461, 189.1125, 0.0000, 6297.6336, 3211.7931, 3085.8405, 15271.1507, '2026-06-05 04:09:30'),
(296, 8, 6, 196.8322, 1199.3080, 6298.6304, 193.8403, 0.0000, 6104.7901, 3113.4430, 2991.3472, 18262.4978, '2026-06-05 04:09:30'),
(297, 8, 7, 191.1241, 1390.4321, 6115.9702, 198.6863, 0.0000, 5917.2838, 3017.8148, 2899.4691, 21161.9669, '2026-06-05 04:09:30'),
(298, 8, 8, 185.5815, 1576.0136, 5938.6070, 203.6535, 0.0000, 5734.9535, 2924.8263, 2810.1272, 23972.0941, '2026-06-05 04:09:30'),
(299, 8, 9, 180.1996, 1756.2132, 5766.3874, 208.7448, 0.0000, 5557.6426, 2834.3977, 2723.2449, 26695.3390, '2026-06-05 04:09:30'),
(300, 8, 10, 174.9738, 1931.1870, 5599.1622, 213.9634, 0.0000, 5385.1988, 2746.4514, 2638.7474, 29334.0864, '2026-06-05 04:09:30');

-- --------------------------------------------------------

--
-- Table structure for table `opex_params`
--

CREATE TABLE `opex_params` (
  `id` int(10) UNSIGNED NOT NULL,
  `field_id` int(10) UNSIGNED NOT NULL,
  `base_usd_m` decimal(14,4) NOT NULL COMMENT 'Opex base ($M/tahun)',
  `base_hingga_thn` smallint(6) NOT NULL COMMENT 'Opex base berlaku s.d. tahun ke-N',
  `eskalasi_persen` decimal(6,3) NOT NULL DEFAULT 2.500 COMMENT 'Kenaikan Opex %/thn setelah base period'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Parameter biaya operasi tahunan';

--
-- Dumping data for table `opex_params`
--

INSERT INTO `opex_params` (`id`, `field_id`, `base_usd_m`, `base_hingga_thn`, `eskalasi_persen`) VALUES
(4, 4, 182.0000, 3, 2.500),
(7, 7, 180.0000, 3, 2.500),
(8, 8, 180.0000, 3, 2.500);

-- --------------------------------------------------------

--
-- Table structure for table `production_decline`
--

CREATE TABLE `production_decline` (
  `id` int(10) UNSIGNED NOT NULL,
  `field_id` int(10) UNSIGNED NOT NULL,
  `mulai_tahun_ke` smallint(6) NOT NULL COMMENT 'Produksi mulai decline pada tahun ke-N',
  `laju_persen` decimal(6,3) NOT NULL COMMENT 'Laju decline %/tahun'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Parameter decline produksi eksponensial setelah tahun manual';

--
-- Dumping data for table `production_decline`
--

INSERT INTO `production_decline` (`id`, `field_id`, `mulai_tahun_ke`, `laju_persen`) VALUES
(4, 4, 5, 3.000),
(7, 7, 4, 3.000),
(8, 8, 4, 2.900);

-- --------------------------------------------------------

--
-- Table structure for table `production_manual`
--

CREATE TABLE `production_manual` (
  `id` int(10) UNSIGNED NOT NULL,
  `field_id` int(10) UNSIGNED NOT NULL,
  `tahun_ke` smallint(6) NOT NULL COMMENT 'Urutan tahun operasi (1, 2, 3, ...)',
  `produksi` decimal(14,4) NOT NULL COMMENT 'Produksi Mbbl tahun tersebut'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Produksi manual per tahun (input langsung dari data lapangan)';

--
-- Dumping data for table `production_manual`
--

INSERT INTO `production_manual` (`id`, `field_id`, `tahun_ke`, `produksi`) VALUES
(43, 4, 1, 175.0000),
(44, 4, 2, 201.0000),
(45, 4, 3, 217.0000),
(46, 4, 4, 198.0000),
(62, 7, 1, 175.0000),
(63, 7, 2, 201.0000),
(64, 7, 3, 214.0000),
(65, 8, 1, 175.0000),
(66, 8, 2, 201.0000),
(67, 8, 3, 215.0000);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_investasi_di`
-- (See below for the actual view)
--
CREATE TABLE `v_investasi_di` (
`id` int(10) unsigned
,`nama` varchar(100)
,`tahun_hitung` smallint(6)
,`capital` decimal(14,4)
,`non_capital` decimal(14,4)
,`total_investasi` decimal(14,4)
,`di_per_tahun` decimal(15,4)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_ncf_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_ncf_summary` (
`field_id` int(10) unsigned
,`nama` varchar(100)
,`tahun_terakhir` smallint(6)
,`total_produksi_mbbl` decimal(36,4)
,`total_gross_revenue` decimal(36,4)
,`total_opex` decimal(36,4)
,`total_tax` decimal(36,4)
,`total_ncf_kumulatif` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Structure for view `v_investasi_di`
--
DROP TABLE IF EXISTS `v_investasi_di`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_investasi_di`  AS SELECT `f`.`id` AS `id`, `f`.`nama` AS `nama`, `f`.`tahun_hitung` AS `tahun_hitung`, `i`.`capital` AS `capital`, `i`.`non_capital` AS `non_capital`, `i`.`total_investasi` AS `total_investasi`, round(`i`.`total_investasi` / `f`.`tahun_hitung`,4) AS `di_per_tahun` FROM (`fields` `f` join `investasi` `i` on(`i`.`field_id` = `f`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_ncf_summary`
--
DROP TABLE IF EXISTS `v_ncf_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ncf_summary`  AS SELECT `r`.`field_id` AS `field_id`, `f`.`nama` AS `nama`, max(`r`.`tahun_ke`) AS `tahun_terakhir`, sum(`r`.`produksi_mbbl`) AS `total_produksi_mbbl`, sum(`r`.`gross_revenue`) AS `total_gross_revenue`, sum(`r`.`opex`) AS `total_opex`, sum(`r`.`tax`) AS `total_tax`, max(`r`.`cum_ncf`) AS `total_ncf_kumulatif` FROM (`ncf_results` `r` join `fields` `f` on(`f`.`id` = `r`.`field_id`)) GROUP BY `r`.`field_id`, `f`.`nama` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `investasi`
--
ALTER TABLE `investasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_field` (`field_id`);

--
-- Indexes for table `ncf_results`
--
ALTER TABLE `ncf_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_field_tahun` (`field_id`,`tahun_ke`);

--
-- Indexes for table `opex_params`
--
ALTER TABLE `opex_params`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `field_id` (`field_id`);

--
-- Indexes for table `production_decline`
--
ALTER TABLE `production_decline`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `field_id` (`field_id`);

--
-- Indexes for table `production_manual`
--
ALTER TABLE `production_manual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_field_year` (`field_id`,`tahun_ke`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `investasi`
--
ALTER TABLE `investasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ncf_results`
--
ALTER TABLE `ncf_results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;

--
-- AUTO_INCREMENT for table `opex_params`
--
ALTER TABLE `opex_params`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `production_decline`
--
ALTER TABLE `production_decline`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `production_manual`
--
ALTER TABLE `production_manual`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `investasi`
--
ALTER TABLE `investasi`
  ADD CONSTRAINT `investasi_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ncf_results`
--
ALTER TABLE `ncf_results`
  ADD CONSTRAINT `ncf_results_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `opex_params`
--
ALTER TABLE `opex_params`
  ADD CONSTRAINT `opex_params_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_decline`
--
ALTER TABLE `production_decline`
  ADD CONSTRAINT `production_decline_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_manual`
--
ALTER TABLE `production_manual`
  ADD CONSTRAINT `production_manual_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
