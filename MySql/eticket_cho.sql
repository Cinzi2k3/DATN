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
-- Table structure for table `cho`
--

DROP TABLE IF EXISTS `cho`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cho` (
  `macho` int NOT NULL AUTO_INCREMENT,
  `matoa` int DEFAULT NULL,
  `matau` int DEFAULT NULL,
  `sohieu` varchar(45) DEFAULT NULL,
  `trangthai` varchar(45) DEFAULT NULL,
  `tang` varchar(45) DEFAULT NULL,
  `khoang` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`macho`),
  KEY `a_idx` (`matoa`),
  KEY `b_idx` (`matau`),
  CONSTRAINT `a` FOREIGN KEY (`matoa`) REFERENCES `toa` (`matoa`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `b` FOREIGN KEY (`matau`) REFERENCES `tau` (`matau`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cho`
--

LOCK TABLES `cho` WRITE;
/*!40000 ALTER TABLE `cho` DISABLE KEYS */;
INSERT INTO `cho` VALUES (2,1,1,'S01','Trống',NULL,NULL),(3,1,1,'S02','Đã đặt',NULL,NULL),(4,1,1,'S03','Đã đặt',NULL,NULL),(13,1,1,'S04','Đã đặt',NULL,NULL),(14,1,1,'S05','Trống',NULL,NULL),(15,1,1,'S06','Trống',NULL,NULL),(16,1,1,'S07','Đã đặt',NULL,NULL),(17,1,1,'S08','Trống',NULL,NULL),(18,1,1,'S09','Trống',NULL,NULL),(19,1,1,'S10','Trống',NULL,NULL),(20,1,1,'S11','Trống',NULL,NULL),(21,1,1,'S12','Trống',NULL,NULL),(22,1,1,'S123','Trống',NULL,NULL),(23,1,1,'S14','Trống',NULL,NULL),(24,1,1,'S15','Trống',NULL,NULL),(25,1,1,'S16','Trống',NULL,NULL),(26,1,1,'S17','Trống',NULL,NULL),(27,1,1,'S18','Trống',NULL,NULL),(28,1,1,'S19','Trống',NULL,NULL),(29,1,1,'S20','Trống',NULL,NULL),(30,1,1,'S21','Trống',NULL,NULL),(31,1,1,'S22','Trống',NULL,NULL),(32,1,1,'S23','Trống',NULL,NULL),(33,1,1,'S24','Trống',NULL,NULL),(34,1,1,'S25','Trống',NULL,NULL),(35,1,1,'S26','Trống',NULL,NULL),(36,1,1,'S27','Trống',NULL,NULL),(37,1,1,'S28','Trống',NULL,NULL),(38,1,1,'S29','Trống',NULL,NULL),(39,1,1,'S30','Trống',NULL,NULL),(40,1,1,'S31','Trống',NULL,NULL),(41,1,1,'S32','Trống',NULL,NULL),(42,1,1,'S33','Trống',NULL,NULL),(43,1,1,'S34','Trống',NULL,NULL),(44,1,1,'S35','Trống',NULL,NULL),(45,1,1,'S36','Trống',NULL,NULL),(46,1,1,'S37','Trống',NULL,NULL),(47,1,1,'S38','Trống',NULL,NULL),(48,1,1,'S39','Trống',NULL,NULL),(49,1,1,'S40','Trống',NULL,NULL),(51,2,1,'S01','Trống',NULL,NULL),(52,2,1,'S02','Trống',NULL,NULL),(53,2,1,'S03','Trống',NULL,NULL),(54,2,1,'S04','Trống',NULL,NULL),(55,2,1,'S05','Trống',NULL,NULL),(56,2,1,'S06','Trống',NULL,NULL),(57,2,1,'S07','Trống',NULL,NULL),(58,2,1,'S08','Trống',NULL,NULL),(59,2,1,'S09','Trống',NULL,NULL),(60,2,1,'S10','Trống',NULL,NULL),(61,2,1,'S11','Trống',NULL,NULL),(62,2,1,'S12','Trống',NULL,NULL),(63,2,1,'S13','Trống',NULL,NULL),(64,2,1,'S14','Trống',NULL,NULL),(65,2,1,'S15','Trống',NULL,NULL),(66,2,1,'S16','Trống',NULL,NULL),(67,2,1,'S17','Trống',NULL,NULL),(68,2,1,'S18','Trống',NULL,NULL),(69,2,1,'S19','Trống',NULL,NULL),(70,2,1,'S20','Trống',NULL,NULL),(71,2,1,'S21','Trống',NULL,NULL),(72,2,1,'S22','Trống',NULL,NULL),(73,2,1,'S23','Trống',NULL,NULL),(74,2,1,'S24','Trống',NULL,NULL),(75,2,1,'S25','Trống',NULL,NULL),(76,2,1,'S26','Trống',NULL,NULL),(77,2,1,'S27','Trống',NULL,NULL),(78,2,1,'S28','Trống',NULL,NULL),(79,2,1,'S29','Trống',NULL,NULL),(80,2,1,'S30','Trống',NULL,NULL),(81,2,1,'S31','Trống',NULL,NULL),(82,2,1,'S32','Trống',NULL,NULL),(83,2,1,'S33','Trống',NULL,NULL),(84,2,1,'S34','Trống',NULL,NULL),(85,2,1,'S35','Trống',NULL,NULL),(86,2,1,'S36','Trống',NULL,NULL),(87,2,1,'S37','Trống',NULL,NULL),(88,2,1,'S38','Trống',NULL,NULL),(89,2,1,'S39','Trống',NULL,NULL),(90,2,1,'S40','Trống',NULL,NULL),(92,6,1,'B01','Trống','1','1'),(93,6,1,'B02','Trống','1','1'),(94,6,1,'B03','Trống','2','1'),(95,6,1,'B04','Trống','2','1'),(96,6,1,'B05','Trống','1','2'),(97,6,1,'B06','Trống','1','2'),(98,6,1,'B07','Trống','2','2'),(99,6,1,'B08','Trống','2','2'),(100,6,1,'B09','Trống','1','3'),(101,6,1,'B10','Trống','1','3'),(102,6,1,'B11','Trống','2','3'),(103,6,1,'B12','Trống','2','3'),(104,6,1,'B13','Trống','1','4'),(105,6,1,'B14','Trống','1','4'),(106,6,1,'B15','Trống','2','4'),(107,6,1,'B16','Trống','2','4'),(108,6,1,'B17','Trống','1','5'),(109,6,1,'B18','Trống','1','5'),(110,6,1,'B19','Trống','2','5'),(111,6,1,'B20','Trống','2','5');
/*!40000 ALTER TABLE `cho` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-28  0:25:09
