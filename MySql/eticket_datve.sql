-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eticket
-- ------------------------------------------------------
-- Server version	9.2.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `datve`
--

DROP TABLE IF EXISTS `datve`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datve` (
  `madatve` int NOT NULL AUTO_INCREMENT,
  `macho` int DEFAULT NULL,
  `malichtrinh` int DEFAULT NULL,
  `trangthai` enum('danggiu','dadat') DEFAULT NULL,
  `thoihan_giu` datetime DEFAULT NULL,
  PRIMARY KEY (`madatve`),
  KEY `ưrrr_idx` (`malichtrinh`),
  KEY `rưr_idx` (`macho`),
  CONSTRAINT `rưr` FOREIGN KEY (`macho`) REFERENCES `cho` (`macho`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ưrrr` FOREIGN KEY (`malichtrinh`) REFERENCES `lichtrinh` (`malichtrinh`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=597 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datve`
--

LOCK TABLES `datve` WRITE;
/*!40000 ALTER TABLE `datve` DISABLE KEYS */;
INSERT INTO `datve` VALUES (2,3,1,'dadat',NULL),(3,4,1,'dadat',NULL),(4,13,1,'dadat',NULL),(5,14,1,'dadat',NULL),(6,15,1,'dadat',NULL),(7,16,1,'dadat',NULL),(8,17,1,'dadat',NULL),(9,18,1,'dadat',NULL),(12,152,2,'dadat',NULL),(14,51,1,'dadat',NULL),(16,105,1,'dadat',NULL),(17,47,1,'dadat',NULL),(18,52,1,'dadat',NULL),(19,88,1,'dadat',NULL),(20,101,1,'dadat',NULL),(21,104,1,'dadat',NULL),(22,46,1,'dadat',NULL),(23,87,1,'dadat',NULL),(25,53,1,'dadat',NULL),(43,48,1,'dadat',NULL),(74,26,1,'dadat',NULL),(93,37,1,'dadat',NULL),(128,38,1,'dadat',NULL),(129,39,1,'dadat',NULL),(135,36,1,'dadat',NULL),(142,35,1,'dadat',NULL),(143,49,1,'dadat',NULL),(144,34,1,'dadat',NULL),(145,45,1,'dadat',NULL),(151,19,1,'dadat',NULL),(201,30,1,'dadat',NULL),(203,94,1,'dadat',NULL),(212,60,1,'dadat',NULL),(217,111,1,'dadat',NULL),(218,130,1,'dadat',NULL),(221,56,1,'dadat',NULL),(227,40,1,'dadat',NULL),(229,81,1,'dadat',NULL),(230,82,1,'dadat',NULL),(231,20,1,'dadat',NULL),(238,65,1,'dadat',NULL),(239,66,1,'dadat',NULL),(242,58,1,'dadat',NULL),(244,24,1,'dadat',NULL),(253,123,1,'dadat',NULL),(254,98,1,'dadat',NULL),(255,99,1,'dadat',NULL),(258,119,1,'dadat',NULL),(264,122,1,'dadat',NULL),(265,114,1,'dadat',NULL),(266,115,1,'dadat',NULL),(267,76,1,'dadat',NULL),(274,86,1,'dadat',NULL),(275,85,1,'dadat',NULL),(305,212,1,'dadat',NULL),(306,213,1,'dadat',NULL),(307,216,1,'dadat',NULL),(308,217,1,'dadat',NULL),(309,117,1,'dadat',NULL),(310,112,1,'dadat',NULL),(311,237,1,'dadat',NULL),(316,222,1,'dadat',NULL),(317,73,1,'dadat',NULL),(321,214,1,'dadat',NULL),(323,234,1,'dadat',NULL),(330,218,1,'dadat',NULL),(332,153,2,'dadat',NULL),(521,252,2,'dadat',NULL),(522,253,2,'dadat',NULL),(523,292,2,'dadat',NULL),(524,293,2,'dadat',NULL),(529,160,2,'dadat',NULL),(530,161,2,'dadat',NULL),(531,322,2,'dadat',NULL),(532,323,2,'dadat',NULL),(545,116,1,'dadat',NULL),(546,118,1,'dadat',NULL),(547,79,5,'dadat',NULL),(548,54,1,'dadat',NULL),(549,2,5,'dadat',NULL),(550,2,1,'dadat',NULL),(551,228,1,'dadat',NULL),(552,229,1,'dadat',NULL),(553,244,1,'dadat',NULL),(554,245,1,'dadat',NULL),(572,90,1,'dadat',NULL),(579,901,9,'dadat',NULL),(580,902,9,'dadat',NULL),(581,919,9,'dadat',NULL),(582,920,9,'dadat',NULL),(583,1374,10,'dadat',NULL),(590,5895,43,'dadat',NULL),(592,6885,73,'dadat',NULL),(593,2988,21,'dadat',NULL),(594,3029,21,'dadat',NULL),(595,8589,67,'dadat',NULL),(596,8628,67,'dadat',NULL);
/*!40000 ALTER TABLE `datve` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-08 17:08:11
