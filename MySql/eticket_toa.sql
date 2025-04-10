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
-- Table structure for table `toa`
--

DROP TABLE IF EXISTS `toa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `toa` (
  `matoa` int NOT NULL AUTO_INCREMENT,
  `maloaitoa` int DEFAULT NULL,
  `matau` int DEFAULT NULL,
  `tentoa` varchar(45) DEFAULT NULL,
  `sotang` int DEFAULT NULL,
  `socho` int DEFAULT NULL,
  `sochocon` varchar(45) DEFAULT NULL,
  `sochodadat` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`matoa`),
  KEY `mlt_idx` (`maloaitoa`),
  KEY `sffff_idx` (`matau`),
  CONSTRAINT `mlt` FOREIGN KEY (`maloaitoa`) REFERENCES `loaitoa` (`maloaitoa`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sffff` FOREIGN KEY (`matau`) REFERENCES `tau` (`matau`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `toa`
--

LOCK TABLES `toa` WRITE;
/*!40000 ALTER TABLE `toa` DISABLE KEYS */;
INSERT INTO `toa` VALUES (1,1,1,'Toa 1',1,40,'36','4'),(2,1,1,'Toa 2',1,40,'40','0'),(6,2,1,'Toa 3',1,20,'20','0'),(7,2,1,'Toa 4',1,0,'0','0'),(8,2,1,'Toa 5',1,0,'0','0'),(9,2,1,'Toa 6',1,0,'0','0'),(10,1,4,'Toa 1',1,0,'0','0'),(11,1,4,'Toa 2',1,0,'0','0'),(12,2,4,'Toa 3',1,0,'0','0');
/*!40000 ALTER TABLE `toa` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-28  0:25:10
