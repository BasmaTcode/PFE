-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 11 juin 2026 à 04:08
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `rise_shine`
--

-- --------------------------------------------------------

--
-- Structure de la table `account`
--

CREATE TABLE `account` (
  `id` varchar(36) NOT NULL,
  `account` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('USER','ADMIN') NOT NULL DEFAULT 'USER',
  `status` enum('ACTIVE','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `avatarUrl` varchar(700) DEFAULT NULL,
  `displayName` varchar(100) DEFAULT NULL,
  `registeredAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `lastLoginAt` datetime(3) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `account`
--

INSERT INTO `account` (`id`, `account`, `password`, `email`, `role`, `status`, `avatarUrl`, `displayName`, `registeredAt`, `lastLoginAt`, `createdAt`, `updatedAt`) VALUES
('2e111f53-8154-4744-aac9-337250541f07', 'pierre_leroy', 'c50d1444b0d66012e228a70e9c9c4ff12adbb63ed6ab8a647cbf83296a94e125', 'pierre.leroy@yahoo.fr', 'USER', 'ACTIVE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/e534272eed6e424293ae7bd65d106277.png', 'Pierre Leroy - Amateur soins', '2026-04-13 14:54:12.000', '2026-06-05 18:00:16.000', '2026-04-13 14:54:12.000', '2026-06-05 18:00:16.000'),
('30de1cef-fbd2-4957-9a87-c07243543b24', 'claire_fontaine', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'claire.fontaine@riseshine.fr', 'USER', 'ACTIVE', 'https://www.autocoder.cc/background/zaki_prod/generated/15750ebcee8a4ab196d9d080e631783c.png', 'Claire Fontaine - Soin naturel', '2026-05-15 14:54:12.000', '2026-05-16 14:54:12.000', '2026-05-15 14:54:12.000', '2026-05-16 14:54:12.000'),
('42bc8b34-8f8a-4890-b892-84a566c950d9', 'nicolas.blanc', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'nicolas.blanc@yahoo.fr', 'USER', 'SUSPENDED', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/05c72422ad4649f5b73a6f44faba18a3.png', 'Nicolas Blanc - Client récent', '2026-05-13 14:54:12.000', '2026-05-14 14:54:12.000', '2026-05-13 14:54:12.000', '2026-05-14 14:54:12.000'),
('64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'luc_dubois', 'c50d1444b0d66012e228a70e9c9c4ff12adbb63ed6ab8a647cbf83296a94e125', 'luc.dubois@riseshine.fr', 'ADMIN', 'ACTIVE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/19abe62642ff49ea9a8f2c185e85a681.png', 'Luc Dubois - Rédacteur en chef', '2026-05-05 14:54:12.000', '2026-05-31 23:41:34.000', '2026-05-05 14:54:12.000', '2026-05-31 23:41:34.000'),
('70d7b7fa-f5a7-43b7-bdb8-6ffc65a580cb', 'sophie.martin', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'sophie.martin@riseshine.fr', 'ADMIN', 'ACTIVE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/5f1b15f500b844d5aa9488778fefd248.png', 'Sophie Martin - Experte Beauté', '2026-04-05 14:54:12.000', '2026-04-06 14:54:12.000', '2026-04-05 14:54:12.000', '2026-04-06 14:54:12.000'),
('80b5c850-3ec9-402f-8f99-236069936025', 'aymane11', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', 'ayman@gmail.com', 'ADMIN', 'ACTIVE', NULL, 'aymane11', '2026-06-08 01:12:01.000', '2026-06-08 01:12:11.000', '2026-06-08 01:12:01.000', '2026-06-08 01:12:01.000'),
('8452e45c-9951-46da-a734-faa5fc38038d', 'marie_jeanne', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'marie.jeanne@gmail.com', 'USER', 'ACTIVE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/0547454e92434663a0c78c9ff8a3aa67.png', 'Marie Jeanne - Cliente fidèle', '2026-04-10 14:54:12.000', '2026-04-11 14:54:12.000', '2026-04-10 14:54:12.000', '2026-04-11 14:54:12.000'),
('9274b05d-2b9a-4018-8408-63346c0bdcfa', 'testuser@example.com', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'testuser@example.com', 'USER', 'ACTIVE', NULL, 'Test User', '2026-06-08 20:33:03.000', NULL, '2026-06-08 20:33:03.000', '2026-06-08 20:33:03.000'),
('9da44ae2-e893-4737-a635-c99c5a152ca4', 'thomas_gauthier', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'thomas.gauthier@gmail.com', 'USER', 'ACTIVE', 'https://www.autocoder.cc/background/zaki_prod/generated/f92ee4cbb8c244999573d999264dcac3.png', 'Thomas Gauthier - Testeur', '2026-05-19 14:54:12.000', '2026-05-20 14:54:12.000', '2026-05-19 14:54:12.000', '2026-05-20 14:54:12.000'),
('bbc32287-fc99-4d0b-85fb-2d931fbb4164', 'testuser55@example.com', 'fd9fac835ca57cf2fffbaa2597ea0220d7fe6dda4ba2498896e108e96b153769', 'testuser55@example.com', 'USER', 'ACTIVE', NULL, 'Test User', '2026-06-08 01:09:58.000', NULL, '2026-06-08 01:09:58.000', '2026-06-08 01:09:58.000'),
('c87f26d6-acee-4b9e-aee2-8ff4b10822ed', 'emilie_roux', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'emilie.roux@gmail.com', 'USER', 'ACTIVE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/ae666a5a31e241338bfdf85d1c7652e8.png', 'Emilie Roux - Passion Makeup', '2026-05-10 14:54:12.000', '2026-05-11 14:54:12.000', '2026-05-10 14:54:12.000', '2026-05-11 14:54:12.000'),
('d49e3531-a067-4620-9abf-8d98f016f7fc', 'alice.dufour', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'alice.dufour@hotmail.fr', 'USER', 'ACTIVE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/3c96c97247134dbfa6d0f57a957c7819.png', 'Alice Dufour - Nouvelle abonnée', '2026-05-20 14:54:12.000', '2026-05-21 14:54:12.000', '2026-05-20 14:54:12.000', '2026-05-21 14:54:12.000'),
('e787014b-a794-48b6-8beb-c50410dd0b6b', 'laura_marchand', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'laura.marchand@yahoo.com', 'USER', 'ACTIVE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/01bcaa1cb84e4b58b4648f97e27e4d55.png', 'Laura Marchand - Exploratrice', '2026-05-23 14:54:12.000', '2026-05-24 14:54:12.000', '2026-05-23 14:54:12.000', '2026-05-24 14:54:12.000'),
('ef107bb2-12c3-466b-9cab-cd2efad8915d', 'julie.petit', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'julie.petit@hotmail.com', 'USER', 'SUSPENDED', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/a6f92da45cab4873932bc6fb7cbefaff.png', 'Julie Petit - Beauty Addict', '2026-04-27 14:54:12.000', '2026-04-28 14:54:12.000', '2026-04-27 14:54:12.000', '2026-04-28 14:54:12.000'),
('fcd616eb-143a-41e3-a5aa-b8e611023a2f', 'antoine_moreau', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'antoine.moreau@outlook.fr', 'USER', 'ACTIVE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/32e2de1d661940d0a88dbc028b4af72a.png', 'Antoine Moreau - Skincare Fan', '2026-04-30 14:54:12.000', '2026-05-01 14:54:12.000', '2026-04-30 14:54:12.000', '2026-05-01 14:54:12.000');

-- --------------------------------------------------------

--
-- Structure de la table `ai_look`
--

CREATE TABLE `ai_look` (
  `id` varchar(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `imageUrl` varchar(700) NOT NULL,
  `galleryJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`galleryJson`)),
  `style` varchar(60) NOT NULL,
  `occasion` varchar(60) DEFAULT NULL,
  `intensity` varchar(30) DEFAULT NULL,
  `inspirationText` text DEFAULT NULL,
  `styleTableJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`styleTableJson`)),
  `faceZonesJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faceZonesJson`)),
  `anonymizedGalleryJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`anonymizedGalleryJson`)),
  `tagsJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tagsJson`)),
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ai_look`
--

INSERT INTO `ai_look` (`id`, `name`, `slug`, `description`, `imageUrl`, `galleryJson`, `style`, `occasion`, `intensity`, `inspirationText`, `styleTableJson`, `faceZonesJson`, `anonymizedGalleryJson`, `tagsJson`, `status`, `createdAt`, `updatedAt`) VALUES
('0ae5f57e-9803-4157-91f8-d5f9217b4dae', 'Regard Félin Intense', 'regard-felin-intense', 'Mettez l\'accent sur vos yeux avec cet eyeliner graphique et structuré. Un look moderne qui allonge le regard et apporte une touche d\'assurance à votre quotidien professionnel ou de sortie.', 'https://www.autocoder.cc/background/zaki_prod/generated/950acc1993ca4579b91342fdfbf5c2f2.png', '[{\"alt\": \"Eyeliner graphique\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/67171e7ccb1b4b6ea0a2ae70050c5f7b.png\", \"sortOrder\": 1}, {\"alt\": \"Regard captivant\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/e287957901d6466fa33e63f5feda5802.png\", \"sortOrder\": 2}, {\"alt\": \"Visage de profil\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/2485b280bdd24b619b7046278eef31fe.png\", \"sortOrder\": 3}]', 'Moderne', 'Travail/Sortie', 'Modérée à Forte', 'L\'allure féline et confiante de la femme urbaine contemporaine, soulignant la force du regard par des lignes nettes.', '{\"finish\": \"Satiné\", \"palette\": [\"Noir\", \"Brun taupe\", \"Beige nude\"], \"skinTones\": [\"Clair\", \"Moyen\", \"Foncé\"], \"recommendedMoment\": \"Toute la journée\"}', '[{\"zone\": \"complexion\", \"description\": \"Teint satiné avec un blush pêche très discret pour ne pas voler la vedette aux yeux.\"}, {\"zone\": \"eyes\", \"description\": \"Eyeliner liquide noir étiré en virgule épaisse, cils recourbés à l\'extrême.\"}, {\"zone\": \"lips\", \"description\": \"Rouge à lèvres nude beige à fini crémeux.\"}]', '[{\"alt\": \"Essai virtuel yeux 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/33c563ef8447477c803a9d098515cdc8.png\"}, {\"alt\": \"Essai virtuel yeux 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/7db14a282bce4d5e88029c2da7f6bf5f.png\"}]', '[\"Eyeliner\", \"Moderne\", \"Urbain\", \"Confiance\"]', 'ACTIVE', '2026-05-10 14:54:13.000', '2026-05-15 14:54:13.000'),
('0c1009be-7cf5-4a4d-b065-3eefec70965f', 'Glamour Soirée Parisienne', 'glamour-soiree-parisienne', 'Un look sophistiqué et audacieux conçu pour les événements élégants. Caractérisé par des lèvres d\'un rouge profond et un regard intense pour captiver l\'attention toute la nuit.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/4137704ab31842ada4cc9b511854e861.png', '[{\"alt\": \"Regard intense\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/274c41cba12543cb9fc15f7b63d64cc2.png\", \"sortOrder\": 1}, {\"alt\": \"Lèvres rouges\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/f4e24a5178804075ae503b88ea5c0ef1.png\", \"sortOrder\": 2}, {\"alt\": \"Vue d\'ensemble\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/318daf35dcc94f70942fd290f0535c67.png\", \"sortOrder\": 3}]', 'Glamour', 'Soirée', 'Forte', 'L\'élégance intemporelle des nuits parisiennes, mêlant mystère et séduction avec des contrastes marquants et des textures riches.', '{\"finish\": \"Mat velouté\", \"palette\": [\"Rouge carmin\", \"Noir profond\", \"Or pâle\"], \"skinTones\": [\"Toutes teintes\"], \"recommendedMoment\": \"Nuit\"}', '[{\"zone\": \"complexion\", \"description\": \"Teint mat et unifié avec un léger contouring pour sculpter le visage.\"}, {\"zone\": \"eyes\", \"description\": \"Smoky eye subtil avec eyeliner noir dramatique et mascara volumisant.\"}, {\"zone\": \"lips\", \"description\": \"Rouge à lèvres liquide mat longue tenue teinte rouge carmin.\"}]', '[{\"alt\": \"Essai virtuel soirée 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/f5cfea32ac2f4f1e8c6aeaf5385713a8.png\"}, {\"alt\": \"Essai virtuel soirée 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/59556644e1104df487cf1f098eba8201.png\"}]', '[\"Soirée\", \"Rouge à lèvres\", \"Élégant\", \"Audacieux\"]', 'ACTIVE', '2026-05-05 14:54:13.000', '2026-05-07 14:54:13.000'),
('35fc61ee-705d-4e8b-a17e-b14a70d134ab', 'Romantisme Pastel', 'romantisme-pastel', 'Un camaïeu de teintes pastel douces, lilas et rose poudré. Ce look apporte une touche de poésie et de délicatesse, idéal pour un rendez-vous amoureux ou une cérémonie de jour.', 'https://www.autocoder.cc/background/zaki_prod/generated/1fa6528107c242689152afd7b684c46f.png', '[{\"alt\": \"Yeux pastel\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/6ccfa4895f294e53aa1c191a4977bc9d.png\", \"sortOrder\": 1}, {\"alt\": \"Joues roses\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/e4e94e9ede114a5e80dcf56a9df80d11.png\", \"sortOrder\": 2}, {\"alt\": \"Portrait doux\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/ff0a7dbb9e5f435caeab6c02bc92a872.png\", \"sortOrder\": 3}]', 'Romantique', 'Rendez-vous/Mariage', 'Légère à Modérée', 'La douceur des champs de lavande et des roseraies au crépuscule, évoquant tendresse et féminité assumée.', '{\"finish\": \"Frais poudré\", \"palette\": [\"Lilas\", \"Rose dragée\", \"Blanc nacré\"], \"skinTones\": [\"Clair\", \"Moyen\"], \"recommendedMoment\": \"Fin d\'après-midi\"}', '[{\"zone\": \"complexion\", \"description\": \"Blush rose dragée appliqué en pomme sur les joues.\"}, {\"zone\": \"eyes\", \"description\": \"Fard lilas pastel sur toute la paupière avec une touche de blanc nacré au coin interne.\"}, {\"zone\": \"lips\", \"description\": \"Rouge à lèvres crème rose tendre.\"}]', '[{\"alt\": \"Essai virtuel pastel 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/3dcd25594846484f989e8084e534608a.png\"}, {\"alt\": \"Essai virtuel pastel 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/64610987b81340fb8625aa97a32a74c0.png\"}]', '[\"Pastel\", \"Romantique\", \"Doux\", \"Rendez-vous\"]', 'ACTIVE', '2026-05-20 14:54:13.000', '2026-05-21 14:54:13.000'),
('4d35a094-209b-4e22-8db4-862ec03e0cd7', 'Chaleur d\'Automne Terracotta', 'chaleur-d-automne-terracotta', 'Incorporez les teintes riches et chaudes de l\'automne dans votre routine. Des oranges brûlés et des bruns chauds pour réchauffer le teint lorsque les jours raccourcissent.', 'https://www.autocoder.cc/background/zaki_prod/generated/784fd91b8bba49be95f291e583451d41.png', '[{\"alt\": \"Yeux terracotta\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/ff34fd2c5f33495eb7c85a955bd986cb.png\", \"sortOrder\": 1}, {\"alt\": \"Joues chaudes\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/94636e63ac234426bdbe436416f79a58.png\", \"sortOrder\": 2}, {\"alt\": \"Portrait automne\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/f07a51a39d0c4381b1c7090dd3e21570.png\", \"sortOrder\": 3}]', 'Chaud et Enveloppant', 'Quotidien Automnal', 'Modérée', 'Les feuilles tombantes et la lumière dorée de l\'automne, une célébration des teintes terreuses et réconfortantes.', '{\"finish\": \"Satiné velours\", \"palette\": [\"Terracotta\", \"Orange brûlé\", \"Brun chocolat\"], \"skinTones\": [\"Moyen\", \"Foncé\", \"Très foncé\"], \"recommendedMoment\": \"Journée\"}', '[{\"zone\": \"complexion\", \"description\": \"Blush terracotta chaud balayé haut sur les pommettes.\"}, {\"zone\": \"eyes\", \"description\": \"Dégradé de fards orange brûlé et brun chaud, crayon marron estompé au ras des cils.\"}, {\"zone\": \"lips\", \"description\": \"Rouge à lèvres brique ou rouille à fini satiné.\"}]', '[{\"alt\": \"Essai virtuel terracotta 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/17f9f418fe3e44a5bc6dec4499a47d2e.png\"}, {\"alt\": \"Essai virtuel terracotta 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/b614885474db424bbc04e0a5f2c867b7.png\"}]', '[\"Automne\", \"Terracotta\", \"Chaleur\", \"Terreux\"]', 'ACTIVE', '2026-01-25 14:54:13.000', '2026-01-30 14:54:13.000'),
('6d0ea68a-8c47-4a66-95b2-3a51a067c5a8', 'Gothique Chic Hivernal', 'gothique-chic-hivernal', 'Une interprétation luxueuse et moderne du style gothique. Des teintes sombres et profondes pour l\'hiver, associées à un teint de porcelaine immaculé pour un contraste saisissant.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/7a6a34094ca5497585a12fc27d3ed682.png', '[{\"alt\": \"Lèvres sombres\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/4320426a591b412a800c0cc7a4eb2707.png\", \"sortOrder\": 1}, {\"alt\": \"Regard ombré\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/ce0c8d1b396c4df9a05c69d9527d2d20.png\", \"sortOrder\": 2}, {\"alt\": \"Vue globale hiver\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/647ba6e6789d4261b2df15c893f43ad1.png\", \"sortOrder\": 3}]', 'Avant-garde', 'Événement spécial', 'Très Forte', 'L\'esthétique romantique sombre des contes d\'hiver, réinventée avec des textures luxueuses et une finition haute couture.', '{\"finish\": \"Mat absolu\", \"palette\": [\"Bordeaux profond\", \"Prune\", \"Gris anthracite\"], \"skinTones\": [\"Très clair\", \"Clair\"], \"recommendedMoment\": \"Soirée d\'hiver\"}', '[{\"zone\": \"complexion\", \"description\": \"Fond de teint mat couvrant et poudre fixatrice pour un effet porcelaine, sans blush.\"}, {\"zone\": \"eyes\", \"description\": \"Ombres à paupières prune et gris foncé estompées autour de l\'œil.\"}, {\"zone\": \"lips\", \"description\": \"Rouge à lèvres mat bordeaux très sombre, aux contours précis.\"}]', '[{\"alt\": \"Essai virtuel hiver 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/dec21057f4ba4de6be1523aba77396a5.png\"}, {\"alt\": \"Essai virtuel hiver 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/34bd42a0ebc14af0926cc8d4dc117cd6.png\"}]', '[\"Hiver\", \"Sombre\", \"Gothique\", \"Contraste\"]', 'INACTIVE', '2026-02-24 14:54:13.000', '2026-02-27 14:54:13.000'),
('725c71cf-10e4-4322-a3ec-56a54dcb4917', 'Extravagance Graphique Colorée', 'extravagance-graphique-coloree', 'Pour celles qui osent la couleur créative. Un look qui utilise des teintes néon ou primaires de manière graphique et inattendue, véritable déclaration artistique.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/b9cdd87bd0d4466e8ed3ce9620110835.png', '[{\"alt\": \"Liner néon\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/e810a8c705024528a1efae5f3f645f91.png\", \"sortOrder\": 1}, {\"alt\": \"Contraste fort\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/f8280c1aaa114257a59db6517aa4f571.png\", \"sortOrder\": 2}, {\"alt\": \"Look artistique\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/1f9dd74a6ab14c3b865e41b84ca798b3.png\", \"sortOrder\": 3}]', 'Artistique', 'Festival/Créatif', 'Très Forte', 'L\'art abstrait contemporain et la vibrance des couleurs néon, pour exprimer son individualité sans retenue.', '{\"finish\": \"Contraste (Mat et Brillant)\", \"palette\": [\"Bleu électrique\", \"Rose fluo\", \"Jaune citron\"], \"skinTones\": [\"Toutes teintes\"], \"recommendedMoment\": \"Événement\"}', '[{\"zone\": \"complexion\", \"description\": \"Teint frais et neutre pour laisser la vedette aux couleurs.\"}, {\"zone\": \"eyes\", \"description\": \"Eyeliner graphique flottant bleu électrique ou rose fluo au-dessus du pli de la paupière.\"}, {\"zone\": \"lips\", \"description\": \"Lèvres neutres ou avec une touche de gloss transparent.\"}]', '[{\"alt\": \"Essai virtuel graphique 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/e4977ec85a2e4b6ba397fc177195a869.png\"}, {\"alt\": \"Essai virtuel graphique 2\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/a41e59afb4004f9c9411d0ffba1d0bd8.png\"}]', '[\"Graphique\", \"Néon\", \"Artistique\", \"Couleur\"]', 'ACTIVE', '2025-12-26 14:54:13.000', '2025-12-27 14:54:13.000'),
('8669596c-8580-40fd-a5c7-4fb31071d6bd', 'Vintage Hollywoodien Rétro', 'vintage-hollywoodien-retro', 'Revivez l\'âge d\'or du cinéma avec ce grand classique indémodable. Un teint immaculé, un eyeliner parfaitement maîtrisé et la bouche rouge signature des icônes du passé.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/7be7a902e8c24024ad392dd1d2ee40af.png', '[{\"alt\": \"Bouche rouge classique\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/4a89726489314daa944072f52d5e10da.png\", \"sortOrder\": 1}, {\"alt\": \"Liner rétro\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/586c98c784ed43f0af6f573e6e7a28cf.png\", \"sortOrder\": 2}, {\"alt\": \"Glamour vintage\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/3fde13cae50048c88f4dacded9489dce.png\", \"sortOrder\": 3}]', 'Classique Rétro', 'Gala/Cérémonie', 'Forte', 'Le charme majestueux et l\'aura mystérieuse des grandes actrices d\'Hollywood des années 50.', '{\"finish\": \"Velours poudré\", \"palette\": [\"Rouge vif\", \"Noir intense\", \"Ivoire\"], \"skinTones\": [\"Clair\", \"Moyen\", \"Foncé\"], \"recommendedMoment\": \"Soirée\"}', '[{\"zone\": \"complexion\", \"description\": \"Teint velouté avec une poudre libre, léger blush rosé.\"}, {\"zone\": \"eyes\", \"description\": \"Paupières unifiées ivoire, eyeliner liquide noir classique et faux cils sur le coin externe.\"}, {\"zone\": \"lips\", \"description\": \"Rouge à lèvres rouge vif semi-mat parfaitement dessiné au crayon.\"}]', '[{\"alt\": \"Essai virtuel rétro 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/e48c80c2e1224710a18c15499a80472e.png\"}, {\"alt\": \"Essai virtuel rétro 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/62578f4c6842476e8c915afd34e7afbe.png\"}]', '[\"Vintage\", \"Rétro\", \"Hollywood\", \"Classique\"]', 'ACTIVE', '2026-04-30 14:54:13.000', '2026-05-02 14:54:13.000'),
('ab750666-0fdb-4a0e-a11f-8a97493d5e23', 'Douceur Estivale Nude', 'douceur-estivale-nude', 'Sublimez votre bronzage avec ce look nude chaleureux. Des teintes terreuses et un fini glowy pour refléter la chaleur de l\'été tout en restant subtil et raffiné.', 'https://www.autocoder.cc/background/zaki_prod/generated/676be8cea3d049fc914d5dcdcfb6236f.png', '[{\"alt\": \"Teint glowy\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/d69d8d57f23442308ca159cc965a842c.png\", \"sortOrder\": 1}, {\"alt\": \"Détail regard chaud\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/0e2d6cc934c548faa7ccc65bd9f51170.png\", \"sortOrder\": 2}, {\"alt\": \"Vue globale\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/f3cd92fcd1e44e898c5765c18179c1b7.png\", \"sortOrder\": 3}]', 'Nude', 'Vacances', 'Modérée', 'La chaleur des fins d\'après-midi sur la Côte d\'Azur, capturant les tons dorés et cuivrés du coucher de soleil.', '{\"finish\": \"Lumineux\", \"palette\": [\"Bronze\", \"Cuivre\", \"Nude\"], \"skinTones\": [\"Moyen\", \"Foncé\"], \"recommendedMoment\": \"Après-midi\"}', '[{\"zone\": \"complexion\", \"description\": \"Bronzer appliqué sur les zones d\'ombres et highlighter doré.\"}, {\"zone\": \"eyes\", \"description\": \"Fard cuivré irisé sur la paupière mobile, sans eyeliner.\"}, {\"zone\": \"lips\", \"description\": \"Gloss transparent ou légèrement abricoté.\"}]', '[{\"alt\": \"Essai virtuel été 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/d0c83776a67847dd95a1c81d65d74b3f.png\"}, {\"alt\": \"Essai virtuel été 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/2e57184c117a4a03a7166317ca4b0413.png\"}]', '[\"Été\", \"Nude\", \"Bronzage\", \"Glow\"]', 'ACTIVE', '2026-03-26 14:54:13.000', '2026-03-29 14:54:13.000'),
('b4ed122d-fdb0-4064-8d8b-d30807e3eed8', 'Monochrome Pêche', 'monochrome-peche', 'Utiliser une seule teinte pour les yeux, les joues et les lèvres crée une harmonie visuelle apaisante et moderne. Ce look pêche monochrome donne instantanément bonne mine.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/3ae3167c4401407f93ff167f24c47fa0.png', '[{\"alt\": \"Visage monochrome\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/f9d2881a6eeb42639947cb760fe797ae.png\", \"sortOrder\": 1}, {\"alt\": \"Détail harmonieux\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/4241878b21084a079941873f50a8fbd9.png\", \"sortOrder\": 2}, {\"alt\": \"Portrait lumineux\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/19a760d176e24a51901847bc16849725.png\", \"sortOrder\": 3}]', 'Monochrome', 'Bureau/Brunch', 'Légère', 'L\'harmonie parfaite et la simplicité sophistiquée du monochrome, rafraîchissant le visage avec une teinte fruitée et vibrante.', '{\"finish\": \"Lumineux naturel\", \"palette\": [\"Pêche corail\", \"Abricot\"], \"skinTones\": [\"Clair\", \"Moyen\"], \"recommendedMoment\": \"Matin\"}', '[{\"zone\": \"complexion\", \"description\": \"Blush crème pêche fondu sur les pommettes.\"}, {\"zone\": \"eyes\", \"description\": \"Le même blush ou fard pêche balayé sur les paupières, mascara brun léger.\"}, {\"zone\": \"lips\", \"description\": \"Gloss teinté pêche ou rouge à lèvres crème de la même couleur.\"}]', '[{\"alt\": \"Essai virtuel pêche 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/68442efc9b654369b8731fb71a05d4ae.png\"}, {\"alt\": \"Essai virtuel pêche 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/9d82302906b94b44b082fcd3012c65a0.png\"}]', '[\"Pêche\", \"Monochrome\", \"Frais\", \"Bonne mine\"]', 'ACTIVE', '2026-05-23 14:54:13.000', '2026-05-26 14:54:13.000'),
('b9358f0a-48db-4197-a6eb-c21d41e3fee6', 'Minimalisme Chrome', 'minimalisme-chrome', 'L\'art de l\'essentiel sublimé par des touches métalliques. Un maquillage très épuré avec un accent argenté ou chromé stratégiquement placé pour un effet futuriste chic.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/798eb32f9e5b46d89f5ae5b52d98f12b.png', '[{\"alt\": \"Détail chrome\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/d272b8e9bb4943b396edd3d259a8eb59.png\", \"sortOrder\": 1}, {\"alt\": \"Teint nu\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/2c007118bf034369b4e432965b3c2f3d.png\", \"sortOrder\": 2}, {\"alt\": \"Profil futuriste\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/457af93c577a42219df0bedc00729448.png\", \"sortOrder\": 3}]', 'Futuriste Chic', 'Soirée branchée', 'Modérée', 'Le design industriel et l\'architecture moderne, où la pureté des lignes rencontre l\'éclat des matériaux froids.', '{\"finish\": \"Mat et Métallique\", \"palette\": [\"Argent\", \"Gris froid\", \"Transparent\"], \"skinTones\": [\"Toutes teintes\"], \"recommendedMoment\": \"Soirée\"}', '[{\"zone\": \"complexion\", \"description\": \"Teint parfaitement unifié, zéro défaut, finition mate sans aucun contouring.\"}, {\"zone\": \"eyes\", \"description\": \"Trait fin de liner argenté métallique ou fard chrome au centre de la paupière.\"}, {\"zone\": \"lips\", \"description\": \"Baume à lèvres incolore matifiant.\"}]', '[{\"alt\": \"Essai virtuel chrome 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/a35e9ffdd22442169731ea5ea7b11ad9.png\"}, {\"alt\": \"Essai virtuel chrome 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/2d57daed5fd64ea284530871c41027f8.png\"}]', '[\"Minimaliste\", \"Argent\", \"Moderne\", \"Épuré\"]', 'ACTIVE', '2026-05-17 14:54:13.000', '2026-05-18 14:54:13.000'),
('bade2457-d713-4be6-b6df-1242a9021229', 'Éclat Naturel Printanier', 'eclat-naturel-printanier', 'Un maquillage léger et lumineux, parfait pour les journées de printemps. Mettant en valeur la beauté naturelle avec une touche de rosée sur les joues et des lèvres doucement teintées.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/bbd7753e3d344949a14b3bdc3a58e213.png', '[{\"alt\": \"Vue de face\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/32b3cd2ff79e4191bbe62c41e77c954f.png\", \"sortOrder\": 1}, {\"alt\": \"Détail des yeux\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/d367fefc8e9c4795ae90130f2cc7b23f.png\", \"sortOrder\": 2}, {\"alt\": \"Profil\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/a3951b30d9fe47febd5527b7b705f6ff.png\", \"sortOrder\": 3}]', 'Naturel', 'Quotidien', 'Légère', 'Inspiré par les premiers rayons de soleil printaniers et la fraîcheur matinale des jardins parisiens, idéal pour un teint frais.', '{\"finish\": \"Rosé\", \"palette\": [\"Pêche\", \"Rose pâle\", \"Beige clair\"], \"skinTones\": [\"Clair\", \"Moyen\"], \"recommendedMoment\": \"Matin\"}', '[{\"zone\": \"complexion\", \"description\": \"Fond de teint léger avec enlumineur liquide sur les pommettes.\"}, {\"zone\": \"eyes\", \"description\": \"Fard à paupières beige neutre avec une fine couche de mascara brun.\"}, {\"zone\": \"lips\", \"description\": \"Baume teinté rose pêche pour une finition hydratée.\"}]', '[{\"alt\": \"Essai virtuel visage clair\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/c51c9552d9a44e13a3279d0aa1b1cc54.png\"}, {\"alt\": \"Essai virtuel visage moyen\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/214a0c6ed9694420bde8d12658c284fb.png\"}]', '[\"Printemps\", \"Frais\", \"Léger\", \"Journée\"]', 'ACTIVE', '2026-04-10 14:54:13.000', '2026-04-12 14:54:13.000'),
('c932c44e-3534-4955-b4f2-d96ad466adba', 'Éclat Glacé Hivernal', 'eclat-glace-hivernal', 'Un look scintillant inspiré par la neige et le givre. Des reflets froids, bleutés et argentés pour illuminer le visage lors des froides journées d\'hiver, tout en délicatesse.', 'https://www.autocoder.cc/background/zaki_prod/generated/81d4fd392e10438ea0f566a88061aa15.png', '[{\"alt\": \"Reflets glacés\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/a40b6d4724b2470e936fa258ba6c3e27.png\", \"sortOrder\": 1}, {\"alt\": \"Regard scintillant\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/5b0447248a5343e08ae9e6d45f3978b5.png\", \"sortOrder\": 2}, {\"alt\": \"Portrait givré\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/95520ab95460403eb5e21178ed9e7915.png\", \"sortOrder\": 3}]', 'Lumineux Froid', 'Fêtes de fin d\'année', 'Modérée', 'La pureté cristalline des paysages enneigés et la lumière scintillante des matins d\'hiver glacés.', '{\"finish\": \"Irisé givré\", \"palette\": [\"Bleu glacier\", \"Argent étincelant\", \"Blanc givré\"], \"skinTones\": [\"Très clair\", \"Clair\", \"Foncé\"], \"recommendedMoment\": \"Journée ou Soirée\"}', '[{\"zone\": \"complexion\", \"description\": \"Highlighter liquide nacré aux reflets froids sur les points de lumière du visage.\"}, {\"zone\": \"eyes\", \"description\": \"Ombre à paupières bleu glacier irisé et coin interne illuminé d\'argent.\"}, {\"zone\": \"lips\", \"description\": \"Gloss légèrement bleuté ou transparent ultra-brillant.\"}]', '[{\"alt\": \"Essai virtuel givre 1\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/83eca3f43147416b86bc82048bf475b9.png\"}, {\"alt\": \"Essai virtuel givre 2\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/bcc547faae644e86b8778119ab34550b.png\"}]', '[\"Givré\", \"Hiver\", \"Scintillant\", \"Froid\"]', 'INACTIVE', '2026-03-06 14:54:13.000', '2026-03-09 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` varchar(36) NOT NULL,
  `categoryId` varchar(36) NOT NULL,
  `authorId` varchar(36) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `coverUrl` varchar(700) DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `contentJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`contentJson`)),
  `tagsJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tagsJson`)),
  `readingMinutes` int(11) DEFAULT NULL,
  `status` enum('DRAFT','PUBLISHED') NOT NULL DEFAULT 'DRAFT',
  `publishedAt` datetime(3) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `categoryId`, `authorId`, `title`, `slug`, `coverUrl`, `excerpt`, `contentJson`, `tagsJson`, `readingMinutes`, `status`, `publishedAt`, `createdAt`, `updatedAt`) VALUES
('20d2445f-e8ca-422d-bb4f-0ac1d81e3491', 'aa251023-f919-43cc-890b-fb3789e5bd09', '70d7b7fa-f5a7-43b7-bdb8-6ffc65a580cb', 'Les bienfaits de la vitamine C pour l\'éclat', 'bienfaits-vitamine-c-eclat', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/35975ea6296f4e249c8544543fdfe5e2.png', 'Puissant antioxydant, la vitamine C est l\'ingrédient star pour un teint lumineux. Découvrez comment l\'utiliser efficacement.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Routine du soir : les étapes clés\"}, {\"type\": \"image\", \"imageUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/0b27b6dc9901457ea639e09730865e75.png\"}, {\"type\": \"paragraph\", \"content\": \"Une bonne routine nocturne est essentielle pour permettre à votre peau de se régénérer pendant le sommeil. Commencez toujours par un double nettoyage méticuleux pour éliminer le maquillage, les filtres solaires et la pollution accumulée. Poursuivez avec une lotion tonique apaisante, puis appliquez vos sérums traitants. C\'est le moment idéal pour utiliser des actifs plus puissants comme le rétinol ou les AHA, car la peau n\'est pas exposée au soleil. Terminez par une crème riche ou un masque de nuit pour sceller l\'hydratation et favoriser la réparation cellulaire.\"}, {\"type\": \"tips\", \"content\": \"Massez délicatement votre visage lors de l\'application pour stimuler la microcirculation et détendre les traits.\"}]}', '[\"Ingrédients\", \"Éclat\", \"Soins\"]', 6, 'PUBLISHED', '2026-04-27 14:54:13.000', '2026-04-25 14:54:13.000', '2026-04-26 14:54:13.000'),
('2173a4ba-5af3-418d-b1ff-e92d9a46656e', 'aa251023-f919-43cc-890b-fb3789e5bd09', '64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'Hydratation vs Nutrition : comprendre les besoins de sa peau', 'hydratation-vs-nutrition-peau', 'https://www.autocoder.cc/background/zaki_prod/generated/41725e53949c4038a25a9f5aa9eb627c.png', 'Peau sèche ou déshydratée ? Apprenez à faire la différence pour choisir les soins adaptés et retrouver un confort optimal.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Routine du soir : les étapes clés\"}, {\"type\": \"image\", \"imageUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/0b27b6dc9901457ea639e09730865e75.png\"}, {\"type\": \"paragraph\", \"content\": \"Une bonne routine nocturne est essentielle pour permettre à votre peau de se régénérer pendant le sommeil. Commencez toujours par un double nettoyage méticuleux pour éliminer le maquillage, les filtres solaires et la pollution accumulée. Poursuivez avec une lotion tonique apaisante, puis appliquez vos sérums traitants. C\'est le moment idéal pour utiliser des actifs plus puissants comme le rétinol ou les AHA, car la peau n\'est pas exposée au soleil. Terminez par une crème riche ou un masque de nuit pour sceller l\'hydratation et favoriser la réparation cellulaire.\"}, {\"type\": \"tips\", \"content\": \"Massez délicatement votre visage lors de l\'application pour stimuler la microcirculation et détendre les traits.\"}]}', '[\"Hydratation\", \"Soins\", \"Peau Sèche\"]', 7, 'PUBLISHED', '2026-04-10 14:54:13.000', '2026-04-08 14:54:13.000', '2026-04-09 14:54:13.000'),
('21ddb46b-ebbf-4e54-b655-b59f6518e461', 'a5f3d748-7aed-438b-9b9c-96d2f5fcfd3a', '64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'Maquillage des yeux : réussir son smoky eye', 'reussir-son-smoky-eye', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/fe3beafcca7d4ee8a5da94f1f4cba096.png', 'Étape par étape, maîtrisez la technique du regard charbonneux pour vos soirées, du choix des couleurs à l\'estompage parfait.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Maîtriser le contouring naturel\"}, {\"type\": \"paragraph\", \"content\": \"Le contouring ne rime plus forcément avec des traits durs et marqués. La tendance est au « soft sculpting », une technique qui sculpte le visage tout en transparence. Utilisez des produits crèmes ou liquides, plus faciles à estomper pour un fini seconde peau. Appliquez l\'ombre légèrement au-dessus du creux des joues pour un effet liftant, et n\'oubliez pas d\'illuminer les points saillants avec un highlighter subtil. Le but est de rehausser votre structure osseuse naturelle, pas de transformer complètement vos traits. Estompez toujours vers le haut pour éviter d\'alourdir le visage.\"}, {\"type\": \"image\", \"imageUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/b405e9e78f0444b283ba02b2cac7bf09.png\"}]}', '[\"Tutoriel\", \"Yeux\", \"Soirée\"]', 5, 'PUBLISHED', '2026-05-13 14:54:13.000', '2026-05-11 14:54:13.000', '2026-05-12 14:54:13.000'),
('489f196b-125f-4d74-8ed3-5c0c0c2c8849', 'aa251023-f919-43cc-890b-fb3789e5bd09', '64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'Comment construire une routine anti-âge efficace', 'routine-anti-age-efficace', 'https://www.autocoder.cc/background/zaki_prod/generated/4975886e398d4ce986e7b92f3ec0f2c7.png', 'Un guide complet pour comprendre les actifs anti-âge et les intégrer intelligemment dans votre routine quotidienne de soins du visage.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Routine du soir : les étapes clés\"}, {\"type\": \"image\", \"imageUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/0b27b6dc9901457ea639e09730865e75.png\"}, {\"type\": \"paragraph\", \"content\": \"Une bonne routine nocturne est essentielle pour permettre à votre peau de se régénérer pendant le sommeil. Commencez toujours par un double nettoyage méticuleux pour éliminer le maquillage, les filtres solaires et la pollution accumulée. Poursuivez avec une lotion tonique apaisante, puis appliquez vos sérums traitants. C\'est le moment idéal pour utiliser des actifs plus puissants comme le rétinol ou les AHA, car la peau n\'est pas exposée au soleil. Terminez par une crème riche ou un masque de nuit pour sceller l\'hydratation et favoriser la réparation cellulaire.\"}, {\"type\": \"tips\", \"content\": \"Massez délicatement votre visage lors de l\'application pour stimuler la microcirculation et détendre les traits.\"}]}', '[\"Soins\", \"Anti-âge\", \"Routine\"]', 6, 'PUBLISHED', '2026-05-10 14:54:13.000', '2026-05-08 14:54:13.000', '2026-05-09 14:54:13.000'),
('6fa551d0-e6c6-4035-9e33-bae8449bb7b5', '94afe4d4-3b0c-433c-ad6a-47eca440a1fd', '70d7b7fa-f5a7-43b7-bdb8-6ffc65a580cb', 'Les 5 tendances maquillage qui vont marquer l\'année', '5-tendances-maquillage-annee', 'https://www.autocoder.cc/background/zaki_prod/generated/5ba46743144b4b3daa3cbce618e782c5.png', 'Découvrez les looks incontournables de cette saison, entre naturel lumineux et touches de couleurs audacieuses pour un style unique.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Les incontournables de la saison\"}, {\"type\": \"paragraph\", \"content\": \"Cette saison, les teintes pastel et les textures légères font leur grand retour. Le teint naturel et lumineux est au cœur de toutes les tendances, mettant en valeur la beauté authentique de chacune. Les produits hybrides, mi-soin mi-maquillage, sont particulièrement plébiscités pour leur capacité à sublimer tout en hydratant. N\'hésitez pas à expérimenter avec des touches de couleurs douces sur les paupières ou les lèvres pour un effet frais et printanier. Le secret réside dans l\'équilibre subtil entre éclat et simplicité.\"}, {\"type\": \"image\", \"imageUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/612daa2155ab456caab148e223cbf73d.png\"}, {\"type\": \"tips\", \"content\": \"N\'oubliez pas d\'appliquer une base hydratante avant votre fond de teint pour un résultat optimal et durable tout au long de la journée.\"}]}', '[\"Tendances\", \"Maquillage\", \"Printemps\"]', 4, 'PUBLISHED', '2026-05-23 14:54:13.000', '2026-05-21 14:54:13.000', '2026-05-22 14:54:13.000'),
('82eeb9a4-98f6-4d0b-8ef0-50a131ed6eae', '94afe4d4-3b0c-433c-ad6a-47eca440a1fd', '64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'Tendances sourcils : du fluffy au structuré', 'tendances-sourcils-fluffy-structure', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/c731b710ed2746709e5d7239171e5aed.png', 'Les sourcils encadrent le regard. Zoom sur les différentes manières de les mettre en forme selon votre style et la forme de votre visage.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Les incontournables de la saison\"}, {\"type\": \"paragraph\", \"content\": \"Cette saison, les teintes pastel et les textures légères font leur grand retour. Le teint naturel et lumineux est au cœur de toutes les tendances, mettant en valeur la beauté authentique de chacune. Les produits hybrides, mi-soin mi-maquillage, sont particulièrement plébiscités pour leur capacité à sublimer tout en hydratant. N\'hésitez pas à expérimenter avec des touches de couleurs douces sur les paupières ou les lèvres pour un effet frais et printanier. Le secret réside dans l\'équilibre subtil entre éclat et simplicité.\"}, {\"type\": \"image\", \"imageUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/612daa2155ab456caab148e223cbf73d.png\"}, {\"type\": \"tips\", \"content\": \"N\'oubliez pas d\'appliquer une base hydratante avant votre fond de teint pour un résultat optimal et durable tout au long de la journée.\"}]}', '[\"Sourcils\", \"Tendances\", \"Regard\"]', 3, 'PUBLISHED', '2026-03-31 14:54:13.000', '2026-03-29 14:54:13.000', '2026-03-30 14:54:13.000'),
('b12093d4-d99f-47d2-b1f7-c2119d227aa7', 'a5f3d748-7aed-438b-9b9c-96d2f5fcfd3a', '64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'Tutoriel : Le teint parfait en 5 minutes', 'tutoriel-teint-parfait-5-minutes', 'https://www.autocoder.cc/background/zaki_prod/generated/a9cba566f2a1414b819c8c23090cb1da.png', 'Pas le temps le matin ? Apprenez les gestes essentiels pour unifier et illuminer votre teint rapidement et sans effort.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Maîtriser le contouring naturel\"}, {\"type\": \"paragraph\", \"content\": \"Le contouring ne rime plus forcément avec des traits durs et marqués. La tendance est au « soft sculpting », une technique qui sculpte le visage tout en transparence. Utilisez des produits crèmes ou liquides, plus faciles à estomper pour un fini seconde peau. Appliquez l\'ombre légèrement au-dessus du creux des joues pour un effet liftant, et n\'oubliez pas d\'illuminer les points saillants avec un highlighter subtil. Le but est de rehausser votre structure osseuse naturelle, pas de transformer complètement vos traits. Estompez toujours vers le haut pour éviter d\'alourdir le visage.\"}, {\"type\": \"image\", \"imageUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/b405e9e78f0444b283ba02b2cac7bf09.png\"}]}', '[\"Tutoriel\", \"Teint\", \"Express\"]', 3, 'PUBLISHED', '2026-05-17 14:54:13.000', '2026-05-15 14:54:13.000', '2026-05-16 14:54:13.000'),
('b2846a3d-d7bc-4e04-a612-cd0a1036195c', 'a5f3d748-7aed-438b-9b9c-96d2f5fcfd3a', '64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'Comment cacher efficacement les cernes', 'cacher-efficacement-cernes', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/67a0ad52b5f348d88f2f7793b58fc616.png', 'Colorimétrie et choix de l\'anti-cernes : toutes nos astuces pour neutraliser la couleur et illuminer le dessous de l\'œil sans effet matière.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Maîtriser le contouring naturel\"}, {\"type\": \"paragraph\", \"content\": \"Le contouring ne rime plus forcément avec des traits durs et marqués. La tendance est au « soft sculpting », une technique qui sculpte le visage tout en transparence. Utilisez des produits crèmes ou liquides, plus faciles à estomper pour un fini seconde peau. Appliquez l\'ombre légèrement au-dessus du creux des joues pour un effet liftant, et n\'oubliez pas d\'illuminer les points saillants avec un highlighter subtil. Le but est de rehausser votre structure osseuse naturelle, pas de transformer complètement vos traits. Estompez toujours vers le haut pour éviter d\'alourdir le visage.\"}, {\"type\": \"image\", \"imageUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/b405e9e78f0444b283ba02b2cac7bf09.png\"}]}', '[\"Tutoriel\", \"Cernes\", \"Astuces\"]', 4, 'DRAFT', NULL, '2026-05-23 14:54:13.000', '2026-05-24 14:54:13.000'),
('b4461df8-3e5b-45a7-b01a-292cbdcb1ccc', '94afe4d4-3b0c-433c-ad6a-47eca440a1fd', '70d7b7fa-f5a7-43b7-bdb8-6ffc65a580cb', 'Le retour du gloss : comment l\'adopter sans coller', 'retour-du-gloss-astuces', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/a499ca3b685241169d41dfcf1a2290df.png', 'Fini l\'effet collant des années 2000, les nouvelles formules offrent brillance et confort extrême pour des lèvres sublimées.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"Les incontournables de la saison\"}, {\"type\": \"paragraph\", \"content\": \"Cette saison, les teintes pastel et les textures légères font leur grand retour. Le teint naturel et lumineux est au cœur de toutes les tendances, mettant en valeur la beauté authentique de chacune. Les produits hybrides, mi-soin mi-maquillage, sont particulièrement plébiscités pour leur capacité à sublimer tout en hydratant. N\'hésitez pas à expérimenter avec des touches de couleurs douces sur les paupières ou les lèvres pour un effet frais et printanier. Le secret réside dans l\'équilibre subtil entre éclat et simplicité.\"}, {\"type\": \"image\", \"imageUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/612daa2155ab456caab148e223cbf73d.png\"}, {\"type\": \"tips\", \"content\": \"N\'oubliez pas d\'appliquer une base hydratante avant votre fond de teint pour un résultat optimal et durable tout au long de la journée.\"}]}', '[\"Lèvres\", \"Gloss\", \"Tendances\"]', 4, 'PUBLISHED', '2026-05-03 14:54:13.000', '2026-05-01 14:54:13.000', '2026-05-02 14:54:13.000'),
('d5fe7905-585d-40ba-a6be-0322e6524bff', '87283e2d-c836-4476-9f5e-fecc3363d5d3', '70d7b7fa-f5a7-43b7-bdb8-6ffc65a580cb', 'L\'essai virtuel : trouvez votre rouge à lèvres idéal', 'essai-virtuel-rouge-a-levres', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/6f2353fdc0c3457f95f19d0c3d1760a4.png', 'Grâce à notre technologie de réalité augmentée, testez des dizaines de teintes instantanément pour trouver celle qui vous met en valeur.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"La révolution de l\'IA dans les soins\"}, {\"type\": \"paragraph\", \"content\": \"L\'intelligence artificielle transforme radicalement notre approche des soins de la peau. Grâce à des algorithmes sophistiqués, il est désormais possible d\'analyser précisément les besoins de votre épiderme à partir d\'un simple selfie. Ces technologies évaluent l\'hydratation, les rides, les taches et la texture pour formuler des recommandations ultra-personnalisées. Fini les essais hasardeux, place à une routine scientifique et ciblée qui évolue avec votre peau. Découvrez comment notre plateforme intègre ces innovations pour vous offrir le meilleur de la beauté connectée.\"}, {\"type\": \"quote\", \"content\": \"L\'avenir de la beauté sera personnalisé ou ne sera pas.\"}, {\"type\": \"paragraph\", \"content\": \"En combinant l\'expertise dermatologique et la puissance de calcul, nous ouvrons une nouvelle ère où chaque conseil est taillé sur mesure.\"}]}', '[\"IA\", \"Maquillage Virtuel\", \"Lèvres\"]', 4, 'PUBLISHED', '2026-03-26 14:54:13.000', '2026-03-24 14:54:13.000', '2026-03-25 14:54:13.000'),
('de419a62-92a1-45bd-99f6-0acfd7df7bcb', '87283e2d-c836-4476-9f5e-fecc3363d5d3', '70d7b7fa-f5a7-43b7-bdb8-6ffc65a580cb', 'Votre peau analysée par l\'intelligence artificielle', 'peau-analysee-intelligence-artificielle', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/33257981a26d47cd93d4887920edc131.png', 'Comprenez le fonctionnement de nos algorithmes de diagnostic cutané et comment ils permettent de créer des recommandations sur mesure.', '{\"blocks\": [{\"type\": \"heading\", \"level\": 1, \"content\": \"La révolution de l\'IA dans les soins\"}, {\"type\": \"paragraph\", \"content\": \"L\'intelligence artificielle transforme radicalement notre approche des soins de la peau. Grâce à des algorithmes sophistiqués, il est désormais possible d\'analyser précisément les besoins de votre épiderme à partir d\'un simple selfie. Ces technologies évaluent l\'hydratation, les rides, les taches et la texture pour formuler des recommandations ultra-personnalisées. Fini les essais hasardeux, place à une routine scientifique et ciblée qui évolue avec votre peau. Découvrez comment notre plateforme intègre ces innovations pour vous offrir le meilleur de la beauté connectée.\"}, {\"type\": \"quote\", \"content\": \"L\'avenir de la beauté sera personnalisé ou ne sera pas.\"}, {\"type\": \"paragraph\", \"content\": \"En combinant l\'expertise dermatologique et la puissance de calcul, nous ouvrons une nouvelle ère où chaque conseil est taillé sur mesure.\"}]}', '[\"IA\", \"Diagnostic\", \"Innovation\"]', 5, 'PUBLISHED', '2026-04-20 14:54:13.000', '2026-04-18 14:54:13.000', '2026-04-19 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `article_product`
--

CREATE TABLE `article_product` (
  `id` varchar(36) NOT NULL,
  `articleId` varchar(36) NOT NULL,
  `productId` varchar(36) NOT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `article_product`
--

INSERT INTO `article_product` (`id`, `articleId`, `productId`, `sortOrder`) VALUES
('1e7a8422-29be-40da-8880-d1e6d9a85e88', '2173a4ba-5af3-418d-b1ff-e92d9a46656e', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 2),
('3244ae6e-7565-4115-96e4-9d18c95c67b2', '2173a4ba-5af3-418d-b1ff-e92d9a46656e', '966d9c23-1234-4020-baf3-d0e306a25d2e', 1),
('3ba0c1b1-47ce-4ca4-a6fa-4b0417603853', '21ddb46b-ebbf-4e54-b655-b59f6518e461', 'f2546299-04ad-4979-a552-3b050c43c30f', 2),
('3bd2a9e4-8d5f-4e75-b452-13fcfdd6f737', 'de419a62-92a1-45bd-99f6-0acfd7df7bcb', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 1),
('3fc52587-c4ae-4b32-81fc-4cd1c148c9f3', '20d2445f-e8ca-422d-bb4f-0ac1d81e3491', '966d9c23-1234-4020-baf3-d0e306a25d2e', 2),
('43b16880-26ca-45a2-9ba7-dfbc18a4e261', '21ddb46b-ebbf-4e54-b655-b59f6518e461', '2043db86-f412-404d-98f5-0ac698706f06', 1),
('8f910091-e305-4123-81b9-9c5c8dbf9979', '489f196b-125f-4d74-8ed3-5c0c0c2c8849', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 2),
('b2689bcc-857e-4958-9532-d631af7ae1bf', '6fa551d0-e6c6-4035-9e33-bae8449bb7b5', 'f2546299-04ad-4979-a552-3b050c43c30f', 2),
('c29b2b97-3dd9-4bfc-a5a5-511fb1307c9f', '6fa551d0-e6c6-4035-9e33-bae8449bb7b5', '2043db86-f412-404d-98f5-0ac698706f06', 1),
('c945a15c-6a22-4a60-9aaf-38002e78bc49', '20d2445f-e8ca-422d-bb4f-0ac1d81e3491', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 1),
('d65ef101-a28f-4556-85a5-7ba283704b6f', '489f196b-125f-4d74-8ed3-5c0c0c2c8849', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 1),
('d6e7b6de-0d29-4c49-9300-171a5975bc4c', 'b12093d4-d99f-47d2-b1f7-c2119d227aa7', '966d9c23-1234-4020-baf3-d0e306a25d2e', 1);

-- --------------------------------------------------------

--
-- Structure de la table `blog_category`
--

CREATE TABLE `blog_category` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `blog_category`
--

INSERT INTO `blog_category` (`id`, `name`, `slug`, `description`, `status`, `sortOrder`, `createdAt`, `updatedAt`) VALUES
('1b3d157f-e545-4fec-974c-1a8638237793', 'Actualités Cosmétiques', 'actualites-cosmetiques', 'Restez informé des lancements de produits et des innovations dans le monde cosmétique.', 'INACTIVE', 5, '2026-03-06 14:54:13.000', '2026-03-06 14:54:13.000'),
('87283e2d-c836-4476-9f5e-fecc3363d5d3', 'Diagnostics IA', 'diagnostics-ia', 'Comprenez comment notre intelligence artificielle analyse votre visage et votre type de peau.', 'ACTIVE', 3, '2026-03-26 14:54:13.000', '2026-03-26 14:54:13.000'),
('94afe4d4-3b0c-433c-ad6a-47eca440a1fd', 'Tendances Beauté', 'tendances-beaute', 'Découvrez les dernières nouveautés en matière de maquillage et les looks en vogue.', 'ACTIVE', 1, '2026-04-10 14:54:13.000', '2026-04-10 14:54:13.000'),
('a5f3d748-7aed-438b-9b9c-96d2f5fcfd3a', 'Tutoriels Maquillage', 'tutoriels-maquillage', 'Apprenez pas à pas à reproduire des looks variés avec nos guides détaillés en ligne.', 'ACTIVE', 4, '2026-05-15 14:54:13.000', '2026-05-15 14:54:13.000'),
('aa251023-f919-43cc-890b-fb3789e5bd09', 'Soins de la Peau', 'soins-de-la-peau', 'Conseils d\'experts et routines personnalisées pour une peau éclatante de santé au quotidien.', 'ACTIVE', 2, '2026-05-10 14:54:13.000', '2026-05-10 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `contact_request`
--

CREATE TABLE `contact_request` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `status` enum('NEW','PROCESSING','CLOSED') NOT NULL DEFAULT 'NEW',
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `contact_request`
--

INSERT INTO `contact_request` (`id`, `name`, `email`, `subject`, `message`, `status`, `createdAt`, `updatedAt`) VALUES
('30669860-1089-4e46-89f7-b24bb28e0cb7', 'Emma Robert', 'emma.robert@example.fr', 'Suggestions d\'amélioration pour l\'application', 'J\'adore votre application ! Ce serait génial si on pouvait sauvegarder plusieurs profils de peau pour comparer l\'évolution dans le temps ou pour différents membres de la famille.', 'PROCESSING', '2026-05-15 14:54:13.000', '2026-05-16 14:54:13.000'),
('31ea91e8-235e-4a06-9cb1-06bba880e5c3', 'Chloe Petit', 'chloe.petit@example.fr', 'Remerciements pour les recommandations', 'Je voulais juste vous remercier pour les recommandations de soins. J\'ai suivi votre routine depuis un mois et ma peau est nettement plus lumineuse et hydratée.', 'CLOSED', '2026-04-20 14:54:13.000', '2026-04-21 14:54:13.000'),
('6e5c7311-aa82-4321-85c1-5ca8b9939520', 'Marie Martin', 'marie.martin@example.fr', 'Question sur les ingrédients d\'une crème', 'Je voudrais savoir si la crème hydratante de nuit contient des parabènes. J\'ai une peau très sensible et je dois faire attention à la composition des produits.', 'NEW', '2026-05-20 14:54:13.000', '2026-05-21 14:54:13.000'),
('97f5363c-733d-4099-b08d-8686fb55ad9b', 'Pierre Bernard', 'pierre.bernard@example.fr', 'Demande de partenariat commercial', 'Bonjour l\'équipe Rise & Shine, je suis gérant d\'un salon de beauté à Paris et je serais intéressé par un partenariat pour proposer vos diagnostics à mes clients.', 'NEW', '2026-05-24 14:54:13.000', '2026-05-25 14:54:13.000'),
('9ea7de9d-e7d5-459b-a60a-89d5cb1ff151', 'Hugo Richard', 'hugo.richard@example.fr', 'Demande de suppression de mes données', 'Conformément au RGPD, je souhaite que vous supprimiez l\'intégralité de mes données personnelles associées à ce compte, y compris les photos de diagnostic.', 'CLOSED', '2026-04-10 14:54:13.000', '2026-04-11 14:54:13.000'),
('a71e2e65-4ce2-446b-a2e6-3d32de26d70c', 'Lucas Thomas', 'lucas.thomas@example.fr', 'Problème de connexion à mon compte', 'Je n\'arrive plus à me connecter à mon compte depuis la dernière mise à jour. J\'ai essayé de réinitialiser mon mot de passe mais je ne reçois pas l\'email.', 'PROCESSING', '2026-05-10 14:54:13.000', '2026-05-11 14:54:13.000'),
('b3fe058c-9781-4d5d-be8e-aa27cb974682', 'Jean Dupont', 'jean.dupont@example.fr', 'Problème avec mon diagnostic de peau', 'Bonjour, je n\'arrive pas à accéder aux résultats de mon diagnostic de peau effectué hier. L\'application m\'affiche une erreur. Pourriez-vous m\'aider s\'il vous plaît ?', 'NEW', '2026-05-23 14:54:13.000', '2026-05-24 14:54:13.000'),
('dc006e9b-3d8d-4fcb-9d52-8d70b73a2ccc', 'Sophie Dubois', 'sophie.dubois@example.fr', 'Erreur lors de l\'essai virtuel de rouge à lèvres', 'L\'essai virtuel ne détecte pas correctement mes lèvres. J\'ai essayé avec plusieurs photos différentes mais le résultat est toujours décalé. Avez-vous une solution ?', 'NEW', '2026-05-05 14:54:13.000', '2026-05-06 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `diagnostic_recommendation`
--

CREATE TABLE `diagnostic_recommendation` (
  `id` varchar(36) NOT NULL,
  `diagnosticResultId` varchar(36) NOT NULL,
  `productId` varchar(36) NOT NULL,
  `priorityRank` int(11) NOT NULL,
  `reasonJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reasonJson`)),
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `diagnostic_recommendation`
--

INSERT INTO `diagnostic_recommendation` (`id`, `diagnosticResultId`, `productId`, `priorityRank`, `reasonJson`, `createdAt`) VALUES
('01e5c9e5-96a0-4d34-95ff-94abf154cd28', '251430a3-f22a-4a0b-94f4-e4b949835c8f', '966d9c23-1234-4020-baf3-d0e306a25d2e', 2, '{\"summary\": \"Une hydratation profonde nécessaire pour compenser la perte en eau tout en apaisant la peau.\", \"matchedNeeds\": [\"hydration\", \"soothing\"], \"matchedSkinTypes\": [\"sensitive\", \"dry\", \"combination\", \"normal\"]}', '2026-05-25 14:54:13.000'),
('053cff37-6eb7-4aba-89cc-088bd3f212ed', '9e9f1136-c863-4a70-9d90-e8259c46d18b', '966d9c23-1234-4020-baf3-d0e306a25d2e', 3, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:31:53.000'),
('080750de-2adf-4ae8-9e94-3090058a42d2', '0871a49a-adc3-468f-944b-38d999e51067', '2043db86-f412-404d-98f5-0ac698706f06', 2, '{\"summary\": \"La palette ombré majestueux permet de créer un regard captivant pour le soir.\", \"matchedNeeds\": [\"radiance\"], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('0a700a8e-c862-4bc9-844a-78387d94351b', 'ff0dc369-8fa4-4dcf-814b-5faed589e613', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 2, '{\"summary\": \"Ce sérum anti-âge agira sur le renouvellement cellulaire ralenti.\", \"matchedNeeds\": [\"anti_age\", \"hydration\", \"radiance\"], \"matchedSkinTypes\": [\"mature\", \"normal\", \"dry\", \"combination\"]}', '2026-05-25 14:54:13.000'),
('0c17947a-fa95-4861-9260-e0e3f1cc6c9e', '631fa1b1-beba-424c-b779-429a1140ff65', '966d9c23-1234-4020-baf3-d0e306a25d2e', 2, '{\"summary\": \"Le sérum à l\'acide hyaluronique apaisera immédiatement vos tiraillements.\", \"matchedNeeds\": [\"hydration\", \"soothing\"], \"matchedSkinTypes\": [\"sensitive\", \"dry\", \"combination\", \"normal\"]}', '2026-05-25 14:54:13.000'),
('12034240-baf3-43ab-95a9-a1cfdcb36d4d', '0871a49a-adc3-468f-944b-38d999e51067', '966d9c23-1234-4020-baf3-d0e306a25d2e', 4, '{\"summary\": \"Un apport hydratant léger pour la zone T tout en désaltérant la zone U.\", \"matchedNeeds\": [\"hydration\", \"soothing\"], \"matchedSkinTypes\": [\"sensitive\", \"dry\", \"combination\", \"normal\"]}', '2026-05-25 14:54:13.000'),
('13116e2b-4118-4765-aef6-2bd145f9add6', 'ae63e352-0493-4f34-a400-75618fe6ec84', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 1, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:49:37.000'),
('26b0e5ca-d089-4917-bb42-e2911f8e1354', '251430a3-f22a-4a0b-94f4-e4b949835c8f', '2043db86-f412-404d-98f5-0ac698706f06', 4, '{\"summary\": \"Une palette complète pour sublimer vos yeux avec des teintes adaptées à votre teint.\", \"matchedNeeds\": [\"radiance\"], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('2bf66251-7b1d-4b74-a7ff-05eb61e4a6f3', '2dc6851e-a534-42d0-94b3-fa4059f37244', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 1, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:34:30.000'),
('2bf6f5b1-9b00-416b-b4d8-9a40dfb3662d', 'd473dff4-ff13-4c6a-8ab1-560086a7a281', '966d9c23-1234-4020-baf3-d0e306a25d2e', 3, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:24:21.000'),
('3001fc98-f61d-409d-b314-f49c1df94531', '9e9f1136-c863-4a70-9d90-e8259c46d18b', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 1, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:31:53.000'),
('32432857-e10e-400b-b632-a51084dbffbe', '9e9f1136-c863-4a70-9d90-e8259c46d18b', '2043db86-f412-404d-98f5-0ac698706f06', 2, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:31:53.000'),
('34b02ca7-7042-4343-882f-74586fc51eef', 'd473dff4-ff13-4c6a-8ab1-560086a7a281', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 1, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:24:21.000'),
('431040ab-55ec-42cb-960e-a5ffaa5c4aed', '2dc6851e-a534-42d0-94b3-fa4059f37244', '966d9c23-1234-4020-baf3-d0e306a25d2e', 3, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:34:30.000'),
('481f4ff3-d6d7-4059-8664-b1736ae7db25', 'ff0dc369-8fa4-4dcf-814b-5faed589e613', '966d9c23-1234-4020-baf3-d0e306a25d2e', 3, '{\"summary\": \"Pour un boost d\'hydratation immédiat en complément de la nutrition.\", \"matchedNeeds\": [\"hydration\", \"soothing\"], \"matchedSkinTypes\": [\"sensitive\", \"dry\", \"combination\", \"normal\"]}', '2026-05-25 14:54:13.000'),
('60704456-2ec7-4d1d-96f3-64db5de91656', 'ff0dc369-8fa4-4dcf-814b-5faed589e613', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 1, '{\"summary\": \"La crème de nuit réparera la barrière lipidique et apportera la nutrition manquante.\", \"matchedNeeds\": [\"hydration\", \"soothing\", \"anti_age\"], \"matchedSkinTypes\": [\"dry\", \"mature\", \"sensitive\"]}', '2026-05-25 14:54:13.000'),
('6ab89b5c-bc59-4217-b8ba-2ee22ad5d140', '0871a49a-adc3-468f-944b-38d999e51067', 'f2546299-04ad-4979-a552-3b050c43c30f', 3, '{\"summary\": \"Le mascara volume absolu pour intensifier votre maquillage sans alourdir.\", \"matchedNeeds\": [], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('6e572444-a9be-4764-ba7a-577a54e4274f', '631fa1b1-beba-424c-b779-429a1140ff65', 'f2546299-04ad-4979-a552-3b050c43c30f', 4, '{\"summary\": \"Un mascara doux et facile à démaquiller pour préserver vos cils.\", \"matchedNeeds\": [], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('750164b0-9d5b-46de-9c65-29a800bca5a8', '14693f1b-2333-4277-a637-29e924a8d917', 'f2546299-04ad-4979-a552-3b050c43c30f', 2, '{\"summary\": \"Un mascara noir intense pour habiller le regard de façon spectaculaire.\", \"matchedNeeds\": [], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('7cd34908-b72d-46c7-b3e0-be77287b5ee0', 'ff0dc369-8fa4-4dcf-814b-5faed589e613', '2043db86-f412-404d-98f5-0ac698706f06', 4, '{\"summary\": \"La palette apportera de la lumière et réveillera votre teint terne.\", \"matchedNeeds\": [\"radiance\"], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('8d6d993b-1b1c-4fff-ae2d-82e34d1645a5', '14693f1b-2333-4277-a637-29e924a8d917', '966d9c23-1234-4020-baf3-d0e306a25d2e', 1, '{\"summary\": \"Le sérum d\'hydratation profonde compense le manque d\'eau sévère sans ajouter de corps gras.\", \"matchedNeeds\": [\"hydration\", \"soothing\"], \"matchedSkinTypes\": [\"sensitive\", \"dry\", \"combination\", \"normal\"]}', '2026-05-25 14:54:13.000'),
('9057b417-4308-4312-818d-9717a71a0ca8', '2dc6851e-a534-42d0-94b3-fa4059f37244', '2043db86-f412-404d-98f5-0ac698706f06', 2, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:34:30.000'),
('980901fc-1742-4baa-a219-0be72faccb16', 'cf1dc924-e03b-4884-8bb2-3eb81d4df3d8', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 1, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 01:00:29.000'),
('9e96fdb2-2773-4eec-97f9-eccab782ee14', '251430a3-f22a-4a0b-94f4-e4b949835c8f', 'f2546299-04ad-4979-a552-3b050c43c30f', 3, '{\"summary\": \"Ce mascara volumateur mettra en valeur votre regard sans compromettre l\'équilibre de la peau.\", \"matchedNeeds\": [], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('b20b6552-0f44-4289-b768-44c6006f5d25', 'cf1dc924-e03b-4884-8bb2-3eb81d4df3d8', '966d9c23-1234-4020-baf3-d0e306a25d2e', 3, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 01:00:29.000'),
('b4eefb7a-0566-48bf-9214-5daf4c50cac7', '631fa1b1-beba-424c-b779-429a1140ff65', '2043db86-f412-404d-98f5-0ac698706f06', 3, '{\"summary\": \"Les fards à paupières haute pigmentation n\'irriteront pas votre peau délicate.\", \"matchedNeeds\": [\"radiance\"], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('bbb6f3d4-4e3d-4c7c-a01f-ebd155c70ff4', '251430a3-f22a-4a0b-94f4-e4b949835c8f', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 1, '{\"summary\": \"Ce sérum anti-âge est idéal pour stimuler le renouvellement cellulaire et redonner de la fermeté.\", \"matchedNeeds\": [\"anti_age\", \"radiance\"], \"matchedSkinTypes\": [\"normal\", \"dry\", \"combination\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('c27a2ab0-3db4-421e-8ebd-ac97407747c5', 'd473dff4-ff13-4c6a-8ab1-560086a7a281', '2043db86-f412-404d-98f5-0ac698706f06', 2, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:24:21.000'),
('c4ac9bd5-3a00-4187-8ac2-a3e57dd3e9ad', '14693f1b-2333-4277-a637-29e924a8d917', '2043db86-f412-404d-98f5-0ac698706f06', 3, '{\"summary\": \"Des ombres à paupières à la tenue irréprochable même sur paupières mixtes.\", \"matchedNeeds\": [\"radiance\"], \"matchedSkinTypes\": [\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]}', '2026-05-25 14:54:13.000'),
('cfb55689-107e-4340-9744-d0fa37567b1f', 'cf1dc924-e03b-4884-8bb2-3eb81d4df3d8', '2043db86-f412-404d-98f5-0ac698706f06', 2, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 01:00:29.000'),
('d09bccdd-2bce-4e17-8360-fae85e5b1037', '14693f1b-2333-4277-a637-29e924a8d917', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 4, '{\"summary\": \"Sérum anti-âge à la texture fluide légère qui pénètre sans effet gras.\", \"matchedNeeds\": [\"anti_age\", \"hydration\", \"radiance\"], \"matchedSkinTypes\": [\"mature\", \"normal\", \"dry\", \"combination\"]}', '2026-05-25 14:54:13.000'),
('d60a2d29-faa0-4837-9649-181570bd9b32', '631fa1b1-beba-424c-b779-429a1140ff65', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 1, '{\"summary\": \"Cette crème de nuit riche nourrit intensément votre peau très sensible pendant le sommeil.\", \"matchedNeeds\": [\"hydration\", \"soothing\", \"anti_age\"], \"matchedSkinTypes\": [\"dry\", \"mature\", \"sensitive\"]}', '2026-05-25 14:54:13.000'),
('ebdf8735-2a1f-4c8e-b5e9-7eb31c4de186', 'ae63e352-0493-4f34-a400-75618fe6ec84', '2043db86-f412-404d-98f5-0ac698706f06', 2, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:49:37.000'),
('edc79d4f-3d94-4ad2-a6ba-d98040448074', 'ae63e352-0493-4f34-a400-75618fe6ec84', '966d9c23-1234-4020-baf3-d0e306a25d2e', 3, '{\"summary\":\"Recommand\\u00e9 sp\\u00e9cifiquement pour r\\u00e9guler votre profil Peau Normale\",\"matchedNeeds\":[\"hydration\",\"radiance\"],\"matchedSkinTypes\":[\"normal\"]}', '2026-06-08 20:49:37.000'),
('fd4d35b5-d816-4b05-b01e-f1d16cfb0489', '0871a49a-adc3-468f-944b-38d999e51067', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 1, '{\"summary\": \"Parfait pour lisser les rides d\'expression tout en respectant l\'équilibre de la zone U.\", \"matchedNeeds\": [\"anti_age\", \"hydration\", \"radiance\"], \"matchedSkinTypes\": [\"mature\", \"normal\", \"dry\", \"combination\"]}', '2026-05-25 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `diagnostic_result`
--

CREATE TABLE `diagnostic_result` (
  `id` varchar(36) NOT NULL,
  `userId` varchar(36) DEFAULT NULL,
  `sessionId` varchar(36) NOT NULL,
  `status` enum('RESULT_READY','SAVED','TEMPORARY') NOT NULL,
  `skinTypeCode` varchar(40) NOT NULL,
  `skinTypeLabel` varchar(80) NOT NULL,
  `confidencePercent` int(11) NOT NULL,
  `axisScoresJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`axisScoresJson`)),
  `expertAnalysisJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`expertAnalysisJson`)),
  `routineJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`routineJson`)),
  `usageAdviceJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`usageAdviceJson`)),
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `faceImageUrl` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `diagnostic_result`
--

INSERT INTO `diagnostic_result` (`id`, `userId`, `sessionId`, `status`, `skinTypeCode`, `skinTypeLabel`, `confidencePercent`, `axisScoresJson`, `expertAnalysisJson`, `routineJson`, `usageAdviceJson`, `createdAt`, `updatedAt`, `faceImageUrl`) VALUES
('0871a49a-adc3-468f-944b-38d999e51067', 'c87f26d6-acee-4b9e-aee2-8ff4b10822ed', '26eadf1b-2ad5-4140-9413-5ffc6bb47785', 'RESULT_READY', 'COMBINATION_AGING', 'Peau mixte avec signes de l\'âge', 95, '{\"aging\": 75, \"sebum\": 65, \"hydration\": 50, \"sensitivity\": 40}', '{\"warnings\": [\"Perte de fermeté naissante\", \"Manque d\'éclat périodique\"], \"strengths\": [\"Bonne résistance globale\", \"Zone U bien équilibrée\"], \"fragilities\": [\"Rides d\'expression marquées\", \"Brillance sur la zone T\"]}', '{\"evening\": [{\"step\": \"Nettoyage\", \"advice\": \"Nettoyant purifiant doux.\"}, {\"step\": \"Sérum\", \"advice\": \"Sérum au rétinol (commencer doucement).\"}, {\"step\": \"Crème nuit\", \"advice\": \"Crème raffermissante et repulpante.\"}], \"morning\": [{\"step\": \"Lotion\", \"advice\": \"Lotion tonique antioxydante.\"}, {\"step\": \"Sérum\", \"advice\": \"Sérum à la vitamine C pure.\"}, {\"step\": \"Protection\", \"advice\": \"Crème de jour avec SPF 30 minimum.\"}]}', '{\"tips\": [\"Massez votre visage lors de l\'application\", \"N\'oubliez pas le cou et le décolleté\"], \"frequency\": \"Routine anti-âge continue\", \"avoidCombinations\": [\"Rétinol et Vitamine C le même soir\"]}', '2026-05-10 14:54:13.000', '2026-05-10 17:18:13.000', NULL),
('14693f1b-2333-4277-a637-29e924a8d917', 'd49e3531-a067-4620-9abf-8d98f016f7fc', 'fbf3d696-59c0-4bd9-a2cf-848542f00889', 'SAVED', 'OILY_DEHYDRATED', 'Peau grasse mais déshydratée', 89, '{\"aging\": 45, \"sebum\": 80, \"hydration\": 30, \"sensitivity\": 60}', '{\"warnings\": [\"Risque d\'obstruction des pores\", \"Teint terne lié à la déshydratation\"], \"strengths\": [\"Bonne épaisseur cutanée\", \"Vieillissement ralenti par le sébum\"], \"fragilities\": [\"Manque d\'eau important\", \"Surproduction de sébum compensatoire\"]}', '{\"evening\": [{\"step\": \"Nettoyage\", \"advice\": \"Baume fondant pour dissoudre le sébum.\"}, {\"step\": \"Soin\", \"advice\": \"Masque hydratant intense ou sérum apaisant.\"}], \"morning\": [{\"step\": \"Nettoyant\", \"advice\": \"Nettoyant doux non moussant.\"}, {\"step\": \"Sérum\", \"advice\": \"Sérum hydratant à l\'acide hyaluronique.\"}, {\"step\": \"Crème\", \"advice\": \"Gel-crème désaltérant sans corps gras.\"}]}', '{\"tips\": [\"Évitez les douches trop chaudes\", \"Privilégiez les textures en gel\"], \"frequency\": \"Hydratation aqueuse quotidienne\", \"avoidCombinations\": [\"Produits matifiants contenant de l\'alcool\"]}', '2026-04-15 14:54:13.000', '2026-04-15 17:18:13.000', NULL),
('251430a3-f22a-4a0b-94f4-e4b949835c8f', '8452e45c-9951-46da-a734-faa5fc38038d', '0b616535-43be-4248-b1d3-0713ca0307bf', 'RESULT_READY', 'OILY_ACNE', 'Peau grasse à tendance acnéique', 92, '{\"aging\": 40, \"sebum\": 85, \"hydration\": 60, \"sensitivity\": 70}', '{\"warnings\": [\"Risque élevé d\'inflammation\", \"Tendance aux points noirs\"], \"strengths\": [\"Bonne élasticité globale\", \"Teint naturellement lumineux\"], \"fragilities\": [\"Production excessive de sébum\", \"Pores dilatés visibles\"]}', '{\"evening\": [{\"step\": \"Démaquillant\", \"advice\": \"Double nettoyage avec huile puis gel.\"}, {\"step\": \"Traitement\", \"advice\": \"Utilisez un soin à l\'acide salicylique.\"}], \"morning\": [{\"step\": \"Nettoyant\", \"advice\": \"Utilisez un gel moussant doux sans savon.\"}, {\"step\": \"Sérum\", \"advice\": \"Appliquez un sérum à la niacinamide.\"}, {\"step\": \"Hydratant\", \"advice\": \"Optez pour une texture fluide matifiante.\"}]}', '{\"tips\": [\"Changez votre taie d\'oreiller régulièrement\", \"Ne touchez pas vos imperfections\"], \"frequency\": \"Routine quotidienne stricte\", \"avoidCombinations\": [\"Vitamine C et Acides forts en même temps\"]}', '2026-05-23 14:54:13.000', '2026-05-23 17:18:13.000', NULL),
('2dc6851e-a534-42d0-94b3-fa4059f37244', NULL, 'c217ec41-fe93-4a70-b309-25deb4e4ce7e', 'TEMPORARY', 'normal', 'Peau Normale', 95, '{\"hydration\":3,\"sebum\":-3,\"sensitivity\":-6,\"aging\":2}', '{\"strengths\":[\"Peau globalement r\\u00e9sistante avec une faible tendance \\u00e0 la sensibilit\\u00e9 (score -6\\/100).\",\"Absence quasi totale de production de s\\u00e9bum (score -3\\/100), ce qui minimise les pr\\u00e9occupations li\\u00e9es aux imperfections et \\u00e0 la brillance.\",\"Peau d\'apparence jeune avec des signes de vieillissement minimes (score 2\\/100).\"],\"fragilities\":[\"D\\u00e9shydratation cutan\\u00e9e marqu\\u00e9e (score 3\\/100), n\\u00e9cessitant une attention particuli\\u00e8re pour restaurer la barri\\u00e8re cutan\\u00e9e et le confort.\",\"Tendance \\u00e0 une faible production de s\\u00e9bum, ce qui peut rendre la peau plus sujette \\u00e0 la s\\u00e9cheresse et \\u00e0 une barri\\u00e8re cutan\\u00e9e affaiblie si non compens\\u00e9e.\"],\"warnings\":[\"Surveillance accrue de la d\\u00e9shydratation : une peau s\\u00e8che peut devenir plus sensible et pr\\u00e9senter des ridules de d\\u00e9shydratation plus rapidement.\",\"Attention aux actifs potentiellement ass\\u00e9chants : certains ingr\\u00e9dients, bien que b\\u00e9n\\u00e9fiques, pourraient accentuer la s\\u00e9cheresse si utilis\\u00e9s sans pr\\u00e9caution.\"],\"keyIngredients\":{\"recommended\":[\"Acide Hyaluronique (Sodium Hyaluronate)\",\"C\\u00e9ramides\",\"Glyc\\u00e9rine\",\"Squalane\",\"Panth\\u00e9nol (Vitamine B5)\",\"Niacinamide (Vitamine B3) - \\u00e0 faible concentration si sensibilit\\u00e9, mais excellent pour la barri\\u00e8re cutan\\u00e9e.\"],\"avoid\":[\"Alcool d\\u00e9natur\\u00e9 (Alcohol Denat.)\",\"Parfums synth\\u00e9tiques forts\",\"Tensioactifs agressifs (Sodium Lauryl Sulfate - SLS)\"]}}', '{\"morning\":[{\"step\":\"Nettoyage\",\"advice\":\"Optez pour un nettoyant doux, cr\\u00e9meux ou lact\\u00e9, sans sulfates, sp\\u00e9cifiquement formul\\u00e9 pour les peaux s\\u00e8ches ou normales. Effectuez un nettoyage l\\u00e9ger sans frotter pour ne pas compromettre la barri\\u00e8re cutan\\u00e9e. Rincez \\u00e0 l\'eau ti\\u00e8de et tamponnez d\\u00e9licatement avec une serviette propre.\"},{\"step\":\"Soin cibl\\u00e9\",\"advice\":\"Appliquez un s\\u00e9rum hydratant concentr\\u00e9. Privil\\u00e9giez des formules contenant de l\'acide hyaluronique de diff\\u00e9rents poids mol\\u00e9culaires pour une hydratation en profondeur, de la glyc\\u00e9rine, ou des c\\u00e9ramides pour renforcer la barri\\u00e8re cutan\\u00e9e. Une l\\u00e9g\\u00e8re touche de vitamine C peut offrir une protection antioxydante sans risque d\'irritation.\"},{\"step\":\"Hydratation\",\"advice\":\"Utilisez une cr\\u00e8me hydratante riche mais non com\\u00e9dog\\u00e8ne. Recherchez des ingr\\u00e9dients \\u00e9mollients comme les huiles v\\u00e9g\\u00e9tales l\\u00e9g\\u00e8res (jojoba, squalane), le beurre de karit\\u00e9 (en petite quantit\\u00e9 si vous redoutez une sensation de lourdeur) et des agents humectants. Assurez-vous qu\'elle aide \\u00e0 sceller l\'hydratation de votre peau.\"},{\"step\":\"Protection solaire\",\"advice\":\"L\'application quotidienne d\'un \\u00e9cran solaire \\u00e0 large spectre (UVA\\/UVB) avec un SPF de 30 minimum, id\\u00e9alement 50, est indispensable. Choisissez une formule hydratante, sans alcool, et adapt\\u00e9e aux peaux s\\u00e8ches. Renouvelez l\'application toutes les deux heures en cas d\'exposition prolong\\u00e9e.\"}],\"evening\":[{\"step\":\"D\\u00e9maquillage\",\"advice\":\"Si vous portez du maquillage, commencez par une huile d\\u00e9maquillante ou un baume onctueux pour dissoudre efficacement les impuret\\u00e9s et le maquillage sans d\\u00e9caper la peau. Suivez avec votre nettoyant doux du matin pour un double nettoyage.\"},{\"step\":\"Exfoliation\",\"advice\":\"Une exfoliation douce 1 \\u00e0 2 fois par semaine maximum. Privil\\u00e9giez les exfoliants enzymatiques ou les acides doux comme l\'acide lactique (un AHA hydratant) ou des peelings chimiques l\\u00e9gers. \\u00c9vitez les gommages \\u00e0 grains qui peuvent \\u00eatre trop abrasifs pour une peau d\\u00e9j\\u00e0 d\\u00e9shydrat\\u00e9e. L\'objectif est de renouveler la peau sans l\'irriter.\"},{\"step\":\"Soin de nuit\",\"advice\":\"Appliquez un s\\u00e9rum nourrissant ou une cr\\u00e8me de nuit plus riche que celle du matin. Int\\u00e9grez des ingr\\u00e9dients r\\u00e9parateurs comme les peptides, les facteurs de croissance, ou des huiles v\\u00e9g\\u00e9tales riches pour favoriser la r\\u00e9g\\u00e9n\\u00e9ration cutan\\u00e9e pendant le sommeil. Un masque hydratant une fois par semaine peut \\u00eatre b\\u00e9n\\u00e9fique.\"},{\"step\":\"Contour des yeux\",\"advice\":\"Utilisez une cr\\u00e8me sp\\u00e9cifique pour le contour des yeux, plus riche et formul\\u00e9e pour cette zone d\\u00e9licate. Recherchez des actifs hydratants (acide hyaluronique, glyc\\u00e9rine) et \\u00e9ventuellement des agents apaisants (camomille, allanto\\u00efne) pour pr\\u00e9venir la s\\u00e9cheresse et l\'apparition de ridules.\"}]}', '{\"frequency\":\"Maintenez une routine quotidienne rigoureuse matin et soir, en adaptant la fr\\u00e9quence des soins sp\\u00e9cifiques comme l\'exfoliation. L\'hydratation doit \\u00eatre une priorit\\u00e9 constante.\",\"avoidCombinations\":[\"L\'utilisation simultan\\u00e9e d\'actifs potentiellement ass\\u00e9chants comme le r\\u00e9tinol \\u00e0 forte concentration et certains acides exfoliants sans un intervalle de temps ad\\u00e9quat ou une hydratation compensatoire.\",\"Les nettoyants agressifs (\\u00e0 base de sulfates forts, d\'alcool) qui peuvent aggraver la d\\u00e9shydratation.\"],\"tips\":[\"Buvez une quantit\\u00e9 d\'eau suffisante tout au long de la journ\\u00e9e pour soutenir l\'hydratation de l\'int\\u00e9rieur.\",\"\\u00c9vitez les douches et bains trop chauds qui peuvent d\\u00e9caper la peau de ses huiles naturelles.\",\"Utilisez un humidificateur d\'air dans votre chambre, surtout en hiver, pour contrer l\'air sec.\"]}', '2026-06-08 20:34:30.000', '2026-06-08 20:34:38.000', NULL),
('631fa1b1-beba-424c-b779-429a1140ff65', '2e111f53-8154-4744-aac9-337250541f07', '93a1e6f7-9f1d-4033-adf7-3dd55757ca10', 'SAVED', 'DRY_SENSITIVE', 'Peau sèche et très sensible', 88, '{\"aging\": 55, \"sebum\": 20, \"hydration\": 35, \"sensitivity\": 90}', '{\"warnings\": [\"Risque de rougeurs sévères\", \"Sensibilité aux changements de température\"], \"strengths\": [\"Grain de peau fin\", \"Peu de pores apparents\"], \"fragilities\": [\"Barrière cutanée altérée\", \"Tiraillements fréquents\"]}', '{\"evening\": [{\"step\": \"Démaquillant\", \"advice\": \"Baume démaquillant très doux.\"}, {\"step\": \"Soin\", \"advice\": \"Masque de nuit réparateur ou huile visage.\"}], \"morning\": [{\"step\": \"Nettoyant\", \"advice\": \"Lavage à l\'eau thermale ou lait doux.\"}, {\"step\": \"Crème\", \"advice\": \"Appliquez une crème riche apaisante.\"}]}', '{\"tips\": [\"Privilégiez l\'eau tiède\", \"Séchez par tapotements sans frotter\"], \"frequency\": \"Hydratation bi-quotidienne indispensable\", \"avoidCombinations\": [\"Gommages à grains\", \"Lotions astringentes\"]}', '2026-05-20 14:54:13.000', '2026-05-20 17:18:13.000', NULL),
('9e9f1136-c863-4a70-9d90-e8259c46d18b', NULL, '5083ea48-e500-4657-a7b9-1a55ff96eec3', 'TEMPORARY', 'normal', 'Peau Normale', 95, '{\"hydration\":7,\"sebum\":2,\"sensitivity\":-6,\"aging\":1}', '{\"strengths\":[\"Teint globalement sain et r\\u00e9actif aux soins\"],\"fragilities\":[\"Sensibilit\\u00e9 cutan\\u00e9e accrue selon les facteurs environnementaux\"],\"warnings\":[\"N\'oubliez pas d\'appliquer une cr\\u00e8me hydratante de jour et une protection solaire SPF.\"]}', '{\"morning\":[{\"step\":\"Nettoyage\",\"advice\":\"Nettoyant doux adapt\\u00e9 pour r\\u00e9veiller l\'\\u00e9clat sans agresser la barri\\u00e8re cutan\\u00e9e.\"},{\"step\":\"Hydratation\",\"advice\":\"Cr\\u00e8me de jour l\\u00e9g\\u00e8re hydratante et protectrice.\"}],\"evening\":[{\"step\":\"D\\u00e9maquillage\",\"advice\":\"Huile d\\u00e9maquillante ou eau micellaire douce.\"},{\"step\":\"Soin cibl\\u00e9\",\"advice\":\"S\\u00e9rum de nuit r\\u00e9parateur sp\\u00e9cifique \\u00e0 votre besoin principal.\"}]}', '{\"frequency\":\"Application quotidienne r\\u00e9guli\\u00e8re pour des r\\u00e9sultats optimaux sous 28 jours.\",\"avoidCombinations\":[\"\\u00c9vitez d\'associer le r\\u00e9tinol pur et la vitamine C acide le m\\u00eame matin.\"],\"tips\":[\"Tapotez vos soins d\\u00e9licatement sans frotter la peau.\",\"Appliquez les textures du plus fluide au plus \\u00e9pais.\"]}', '2026-06-08 20:31:53.000', '2026-06-08 20:31:53.000', NULL),
('ae4a6c4c-a039-4311-9093-1b394c98e894', '30de1cef-fbd2-4957-9a87-c07243543b24', '8dd29371-c090-4c86-bc7c-c1f12f92be03', 'TEMPORARY', 'NORMAL', 'Peau normale et équilibrée', 80, '{\"aging\": 30, \"sebum\": 50, \"hydration\": 85, \"sensitivity\": 20}', '{\"warnings\": [\"Maintenir l\'hydratation de base\"], \"strengths\": [\"Équilibre parfait\", \"Teint homogène et éclatant\"], \"fragilities\": [\"Légère déshydratation hivernale possible\"]}', '{\"evening\": [{\"step\": \"Démaquillant\", \"advice\": \"Huile ou lait démaquillant.\"}, {\"step\": \"Crème\", \"advice\": \"Soin de nuit régénérant basique.\"}], \"morning\": [{\"step\": \"Nettoyant\", \"advice\": \"Eau micellaire ou gel doux.\"}, {\"step\": \"Hydratant\", \"advice\": \"Crème de jour légère hydratante.\"}]}', '{\"tips\": [\"Une exfoliation douce une fois par semaine\", \"Buvez suffisamment d\'eau\"], \"frequency\": \"Entretien régulier\", \"avoidCombinations\": [\"Produits trop décapants\"]}', '2026-05-05 14:54:13.000', '2026-05-05 17:18:13.000', NULL),
('ae63e352-0493-4f34-a400-75618fe6ec84', NULL, '66c25fd7-19c1-48e1-bcd2-96511f9d5732', 'TEMPORARY', 'normal', 'Peau Normale', 95, '{\"hydration\":-3,\"sebum\":3,\"sensitivity\":-4,\"aging\":0}', '{\"strengths\":[\"Excellent potentiel de r\\u00e9silience cutan\\u00e9e (faible sensibilit\\u00e9)\",\"Absence de signes de vieillissement pr\\u00e9matur\\u00e9, indiquant une peau jeune et bien pr\\u00e9serv\\u00e9e\",\"Potentiel \\u00e9lev\\u00e9 pour une peau \\u00e9clatante gr\\u00e2ce \\u00e0 une bonne base\"],\"fragilities\":[\"D\\u00e9s\\u00e9quilibre hydrique majeur : peau significativement d\\u00e9shydrat\\u00e9e\",\"D\\u00e9ficit s\\u00e9v\\u00e8re en lipides : production de s\\u00e9bum tr\\u00e8s insuffisante, compromettant la barri\\u00e8re cutan\\u00e9e\"],\"warnings\":[\"Risque accru d\'irritations et de r\\u00e9actions cutan\\u00e9es dues \\u00e0 la d\\u00e9shydratation et au manque de lipides\",\"Apparition pr\\u00e9matur\\u00e9e de ridules et d\'une perte d\'\\u00e9lasticit\\u00e9 si l\'hydratation et la nutrition ne sont pas r\\u00e9tablies\",\"La peau est plus vuln\\u00e9rable aux agressions environnementales (pollution, UV)\"],\"keyIngredients\":{\"recommended\":[\"Acide Hyaluronique (multi-mol\\u00e9culaire)\",\"C\\u00e9ramides\",\"Glyc\\u00e9rine\",\"Panth\\u00e9nol (Vitamine B5)\",\"Huiles v\\u00e9g\\u00e9tales nobles (Jojoba, Argan, Squalane)\",\"Niacinamide\",\"Peptides\"],\"avoid\":[\"Sulfates (SLS, SLES)\",\"Alcool d\\u00e9natur\\u00e9\",\"Parfums synth\\u00e9tiques agressifs\",\"Savons\"]}}', '{\"morning\":[{\"step\":\"Nettoyage\",\"advice\":\"Optez pour un nettoyant doux, sans savon et sans sulfates, id\\u00e9alement une cr\\u00e8me nettoyante ou une huile d\\u00e9maquillante pour pr\\u00e9server le film hydrolipidique. Rincez \\u00e0 l\'eau ti\\u00e8de et s\\u00e9chez d\\u00e9licatement en tapotant avec une serviette propre.\"},{\"step\":\"Soin cibl\\u00e9\",\"advice\":\"Appliquez un s\\u00e9rum hydratant concentr\\u00e9 en acide hyaluronique multi-mol\\u00e9culaire et en c\\u00e9ramides pour renforcer la barri\\u00e8re cutan\\u00e9e et retenir l\'eau. Une touche de niacinamide peut \\u00e9galement aider \\u00e0 r\\u00e9guler le s\\u00e9bum et am\\u00e9liorer la fonction barri\\u00e8re.\"},{\"step\":\"Hydratation\",\"advice\":\"Utilisez une cr\\u00e8me hydratante riche et nourrissante, formul\\u00e9e avec des \\u00e9mollients (huiles v\\u00e9g\\u00e9tales nobles comme l\'huile d\'argan, de jojoba) et des humectants (glyc\\u00e9rine, panth\\u00e9nol). Recherchez des textures baume ou cr\\u00e8me onctueuse pour un confort optimal et une action protectrice.\"},{\"step\":\"Protection solaire\",\"advice\":\"Indispensable ! Appliquez un \\u00e9cran solaire \\u00e0 large spectre (UVA\\/UVB) avec un SPF 30 minimum, id\\u00e9alement 50. Privil\\u00e9giez les formules hydratantes et sans alcool pour ne pas accentuer la s\\u00e9cheresse. Renouvelez l\'application toutes les 2 heures en cas d\'exposition prolong\\u00e9e.\"}],\"evening\":[{\"step\":\"D\\u00e9maquillage\",\"advice\":\"Si vous portez du maquillage, commencez par une huile d\\u00e9maquillante ou un baume pour dissoudre efficacement les impuret\\u00e9s et le maquillage sans agresser la peau. Suivez avec votre nettoyant doux du matin.\"},{\"step\":\"Exfoliation\",\"advice\":\"Limitez l\'exfoliation \\u00e0 une fois par semaine, voire toutes les deux semaines. Utilisez un exfoliant enzymatique doux ou un peeling tr\\u00e8s l\\u00e9ger \\u00e0 base d\'acides de fruits (AHA) \\u00e0 faible concentration. \\u00c9vitez les gommages \\u00e0 grains qui peuvent \\u00eatre trop abrasifs pour une peau d\\u00e9shydrat\\u00e9e et peu s\\u00e9bac\\u00e9e.\"},{\"step\":\"Soin de nuit\",\"advice\":\"Optez pour un soin de nuit r\\u00e9parateur et nourrissant. Un s\\u00e9rum \\u00e0 base de r\\u00e9tinol encapsul\\u00e9 (\\u00e0 faible dose et \\u00e0 introduire progressivement) peut \\u00eatre b\\u00e9n\\u00e9fique pour la r\\u00e9g\\u00e9n\\u00e9ration cellulaire, mais assurez-vous qu\'il soit formul\\u00e9 avec des agents hydratants et apaisants. Sinon, une cr\\u00e8me de nuit riche en huiles v\\u00e9g\\u00e9tales et peptides est une excellente alternative.\"},{\"step\":\"Contour des yeux\",\"advice\":\"Appliquez une cr\\u00e8me sp\\u00e9cifique pour le contour des yeux, riche en agents hydratants, peptides et antioxydants. Massez d\\u00e9licatement du bout de l\'annulaire du coin interne vers le coin externe de l\'\\u0153il.\"}]}', '{\"frequency\":\"Adoptez une routine quotidienne matin et soir. L\'hydratation et la nutrition doivent \\u00eatre constantes pour r\\u00e9tablir l\'\\u00e9quilibre cutan\\u00e9. Les soins cibl\\u00e9s (s\\u00e9rums) peuvent \\u00eatre utilis\\u00e9s matin et soir.\",\"avoidCombinations\":[\"L\'utilisation simultan\\u00e9e d\'acides forts (AHA\\/BHA) et de r\\u00e9tinol le m\\u00eame soir peut provoquer une irritation excessive sur une peau d\\u00e9j\\u00e0 fragile et d\\u00e9shydrat\\u00e9e.\",\"Les nettoyants trop d\\u00e9capants (\\u00e0 base de sulfates) en association avec des traitements dess\\u00e9chants (certains r\\u00e9tinols, peroxyde de benzoyle) sont \\u00e0 proscrire absolument.\"],\"tips\":[\"Buvez suffisamment d\'eau tout au long de la journ\\u00e9e pour soutenir l\'hydratation de l\'int\\u00e9rieur.\",\"Utilisez un brumisateur d\'eau thermale ou florale (sans alcool) pendant la journ\\u00e9e pour un coup de fra\\u00eecheur hydratant.\",\"\\u00c9vitez les environnements trop secs (chauffage, climatisation) et prot\\u00e9gez votre peau du vent.\"]}', '2026-06-08 20:49:37.000', '2026-06-08 20:49:45.000', NULL),
('cf1dc924-e03b-4884-8bb2-3eb81d4df3d8', NULL, '349229b3-c9c2-4844-acac-bcd3592118de', 'TEMPORARY', 'normal', 'Peau Normale', 95, '{\"hydration\":-1,\"sebum\":3,\"sensitivity\":-4,\"aging\":1}', '{\"strengths\":[\"Teint globalement sain et r\\u00e9actif aux soins\"],\"fragilities\":[\"Sensibilit\\u00e9 cutan\\u00e9e accrue selon les facteurs environnementaux\"],\"warnings\":[\"N\'oubliez pas d\'appliquer une cr\\u00e8me hydratante de jour et une protection solaire SPF.\"]}', '{\"morning\":[{\"step\":\"Nettoyage\",\"advice\":\"Nettoyant doux adapt\\u00e9 pour r\\u00e9veiller l\'\\u00e9clat sans agresser la barri\\u00e8re cutan\\u00e9e.\"},{\"step\":\"Hydratation\",\"advice\":\"Cr\\u00e8me de jour l\\u00e9g\\u00e8re hydratante et protectrice.\"}],\"evening\":[{\"step\":\"D\\u00e9maquillage\",\"advice\":\"Huile d\\u00e9maquillante ou eau micellaire douce.\"},{\"step\":\"Soin cibl\\u00e9\",\"advice\":\"S\\u00e9rum de nuit r\\u00e9parateur sp\\u00e9cifique \\u00e0 votre besoin principal.\"}]}', '{\"frequency\":\"Application quotidienne r\\u00e9guli\\u00e8re pour des r\\u00e9sultats optimaux sous 28 jours.\",\"avoidCombinations\":[\"\\u00c9vitez d\'associer le r\\u00e9tinol pur et la vitamine C acide le m\\u00eame matin.\"],\"tips\":[\"Tapotez vos soins d\\u00e9licatement sans frotter la peau.\",\"Appliquez les textures du plus fluide au plus \\u00e9pais.\"]}', '2026-06-08 01:00:29.000', '2026-06-08 01:00:29.000', NULL),
('d473dff4-ff13-4c6a-8ab1-560086a7a281', NULL, '11f742f0-65f2-45f5-a5eb-29093e025a71', 'TEMPORARY', 'normal', 'Peau Normale', 95, '{\"hydration\":3,\"sebum\":-3,\"sensitivity\":-6,\"aging\":2}', '{\"strengths\":[\"Teint globalement sain et r\\u00e9actif aux soins\"],\"fragilities\":[\"Sensibilit\\u00e9 cutan\\u00e9e accrue selon les facteurs environnementaux\"],\"warnings\":[\"N\'oubliez pas d\'appliquer une cr\\u00e8me hydratante de jour et une protection solaire SPF.\"]}', '{\"morning\":[{\"step\":\"Nettoyage\",\"advice\":\"Nettoyant doux adapt\\u00e9 pour r\\u00e9veiller l\'\\u00e9clat sans agresser la barri\\u00e8re cutan\\u00e9e.\"},{\"step\":\"Hydratation\",\"advice\":\"Cr\\u00e8me de jour l\\u00e9g\\u00e8re hydratante et protectrice.\"}],\"evening\":[{\"step\":\"D\\u00e9maquillage\",\"advice\":\"Huile d\\u00e9maquillante ou eau micellaire douce.\"},{\"step\":\"Soin cibl\\u00e9\",\"advice\":\"S\\u00e9rum de nuit r\\u00e9parateur sp\\u00e9cifique \\u00e0 votre besoin principal.\"}]}', '{\"frequency\":\"Application quotidienne r\\u00e9guli\\u00e8re pour des r\\u00e9sultats optimaux sous 28 jours.\",\"avoidCombinations\":[\"\\u00c9vitez d\'associer le r\\u00e9tinol pur et la vitamine C acide le m\\u00eame matin.\"],\"tips\":[\"Tapotez vos soins d\\u00e9licatement sans frotter la peau.\",\"Appliquez les textures du plus fluide au plus \\u00e9pais.\"]}', '2026-06-08 20:24:21.000', '2026-06-08 20:24:21.000', NULL),
('ff0dc369-8fa4-4dcf-814b-5faed589e613', 'e787014b-a794-48b6-8beb-c50410dd0b6b', 'f42ddba6-dc47-4982-95ef-689c82d44aa6', 'RESULT_READY', 'DRY_DULL', 'Peau sèche et teint terne', 85, '{\"aging\": 60, \"sebum\": 25, \"hydration\": 40, \"sensitivity\": 50}', '{\"warnings\": [\"Apparition précoce de ridules\", \"Aspect rugueux au toucher\"], \"strengths\": [\"Absence d\'imperfections majeures\"], \"fragilities\": [\"Manque de lipides\", \"Renouvellement cellulaire ralenti\"]}', '{\"evening\": [{\"step\": \"Exfoliation\", \"advice\": \"Lotion exfoliante douce (AHA) 2x par semaine.\"}, {\"step\": \"Soin profond\", \"advice\": \"Crème de nuit très riche ou baume.\"}], \"morning\": [{\"step\": \"Nettoyant\", \"advice\": \"Lait nettoyant sans rinçage.\"}, {\"step\": \"Sérum\", \"advice\": \"Sérum éclat antioxydant.\"}, {\"step\": \"Crème\", \"advice\": \"Crème riche nourrissante et illuminatrice.\"}]}', '{\"tips\": [\"Ajoutez quelques gouttes d\'huile à votre crème\", \"Misez sur les acides de fruits doux\"], \"frequency\": \"Nutrition quotidienne\", \"avoidCombinations\": [\"Savons classiques\", \"Exfoliants physiques agressifs\"]}', '2026-04-20 14:54:13.000', '2026-04-20 17:18:13.000', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `faq`
--

CREATE TABLE `faq` (
  `id` varchar(36) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `status` enum('HIDDEN','VISIBLE') NOT NULL DEFAULT 'HIDDEN',
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`, `sortOrder`, `status`, `createdAt`, `updatedAt`) VALUES
('02466c29-9411-4669-a6f0-affdeb5f28eb', 'Comment puis-je créer un compte sur Rise & Shine ?', 'Pour créer un compte, cliquez sur l\'icône de profil en haut à droite, puis sélectionnez \'Créer un compte\'. Remplissez vos informations personnelles et validez pour commencer votre expérience beauté personnalisée.', 1, 'VISIBLE', '2026-04-10 14:54:13.000', '2026-04-10 14:54:13.000'),
('088ae31e-04e4-410e-a724-483f63c2ba08', 'Comment suivre l\'expédition de ma commande ?', 'Une fois votre commande expédiée, vous recevrez un email contenant un lien de suivi. Vous pouvez également consulter l\'état de votre livraison directement depuis la section \'Mes Commandes\' de votre compte utilisateur.', 6, 'HIDDEN', '2026-05-23 14:54:13.000', '2026-05-23 14:54:13.000'),
('16b94c51-2b8f-428d-b476-d9b786c9910a', 'Comment fonctionne l\'essai virtuel de maquillage ?', 'L\'essai virtuel utilise la réalité augmentée via votre webcam ou une photo. Il applique virtuellement les produits de maquillage sur votre visage, vous permettant de tester différentes teintes et looks avant l\'achat.', 3, 'VISIBLE', '2026-04-30 14:54:13.000', '2026-04-30 14:54:13.000'),
('746f99f6-d257-4306-8752-2676d6d2563c', 'Quels modes de paiement acceptez-vous sur le site ?', 'Nous acceptons les principales cartes de crédit (Visa, MasterCard, American Express), ainsi que PayPal et Apple Pay. Toutes les transactions sont sécurisées et cryptées pour garantir la protection de vos données.', 5, 'HIDDEN', '2026-05-20 14:54:13.000', '2026-05-20 14:54:13.000'),
('7995f869-c4cb-447a-ba91-560d0ef92d5d', 'Quels sont les avantages de l\'analyse de peau par IA ?', 'Notre analyse de peau par IA utilise une technologie avancée pour évaluer vos traits et votre type de peau avec précision. Elle vous permet d\'obtenir des recommandations de soins sur-mesure et un diagnostic complet en quelques secondes.', 2, 'VISIBLE', '2026-04-20 14:54:13.000', '2026-04-20 14:54:13.000'),
('bcd50524-8565-4f70-af3d-0b03f9800827', 'Puis-je retourner un produit s\'il ne me convient pas ?', 'Oui, vous disposez d\'un délai de 14 jours après réception pour retourner un produit non ouvert et dans son emballage d\'origine. Consultez notre politique de retour pour plus de détails et pour initier la procédure.', 4, 'VISIBLE', '2026-05-10 14:54:13.000', '2026-05-10 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `favorite`
--

CREATE TABLE `favorite` (
  `id` varchar(36) NOT NULL,
  `userId` varchar(36) NOT NULL,
  `targetType` enum('PRODUCT','LOOK') NOT NULL,
  `productId` varchar(36) DEFAULT NULL,
  `lookId` varchar(36) DEFAULT NULL,
  `status` enum('SAVED','REMOVED') NOT NULL DEFAULT 'SAVED',
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `favorite`
--

INSERT INTO `favorite` (`id`, `userId`, `targetType`, `productId`, `lookId`, `status`, `createdAt`, `updatedAt`) VALUES
('32a25918-efd9-4fed-9e22-5b6292de4aca', '2e111f53-8154-4744-aac9-337250541f07', 'PRODUCT', 'f2546299-04ad-4979-a552-3b050c43c30f', NULL, 'SAVED', '2026-05-05 14:54:13.000', '2026-05-06 14:54:13.000'),
('35d557b0-05dc-4b65-870a-03b15f7bfc1f', 'ef107bb2-12c3-466b-9cab-cd2efad8915d', 'LOOK', NULL, '35fc61ee-705d-4e8b-a17e-b14a70d134ab', 'SAVED', '2026-04-25 14:54:13.000', '2026-04-26 14:54:13.000'),
('3849591a-c6f9-4585-8476-2f14f3637ce5', '30de1cef-fbd2-4957-9a87-c07243543b24', 'PRODUCT', 'f2546299-04ad-4979-a552-3b050c43c30f', NULL, 'SAVED', '2026-05-21 14:54:13.000', '2026-05-22 14:54:13.000'),
('5b442bea-fe81-4f1b-be1c-f1d155babbf4', 'd49e3531-a067-4620-9abf-8d98f016f7fc', 'PRODUCT', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', NULL, 'SAVED', '2026-04-27 14:54:13.000', '2026-04-28 14:54:13.000'),
('5d79a90f-227f-4fc3-967a-6be9cf0ff91a', '64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'LOOK', NULL, '0c1009be-7cf5-4a4d-b065-3eefec70965f', 'SAVED', '2026-05-19 14:54:13.000', '2026-05-20 14:54:13.000'),
('7db398c3-cfeb-4c2e-a5ec-76806e5d5649', 'ef107bb2-12c3-466b-9cab-cd2efad8915d', 'PRODUCT', '966d9c23-1234-4020-baf3-d0e306a25d2e', NULL, 'SAVED', '2026-04-30 14:54:13.000', '2026-05-01 14:54:13.000'),
('838179d4-5e1d-4b45-b9c6-59158cbe1029', '70d7b7fa-f5a7-43b7-bdb8-6ffc65a580cb', 'LOOK', NULL, 'bade2457-d713-4be6-b6df-1242a9021229', 'SAVED', '2026-05-22 14:54:13.000', '2026-05-23 14:54:13.000'),
('8dfa190c-7893-41c6-ba0e-ab55d6d7c4a5', '8452e45c-9951-46da-a734-faa5fc38038d', 'PRODUCT', '2043db86-f412-404d-98f5-0ac698706f06', NULL, 'REMOVED', '2026-05-10 14:54:13.000', '2026-05-11 14:54:13.000'),
('8e9d0979-cd05-4e25-9374-e43da4440712', '9da44ae2-e893-4737-a635-c99c5a152ca4', 'PRODUCT', '966d9c23-1234-4020-baf3-d0e306a25d2e', NULL, 'SAVED', '2026-05-13 14:54:13.000', '2026-05-14 14:54:13.000'),
('9285efc6-9585-433d-8fb2-4fefe3e57f90', '8452e45c-9951-46da-a734-faa5fc38038d', 'LOOK', NULL, 'ab750666-0fdb-4a0e-a11f-8a97493d5e23', 'REMOVED', '2026-05-07 14:54:13.000', '2026-05-08 14:54:13.000'),
('93a91b98-c3c6-4c3f-b0ca-6f0c4dc22518', '42bc8b34-8f8a-4890-b892-84a566c950d9', 'PRODUCT', '2043db86-f412-404d-98f5-0ac698706f06', NULL, 'SAVED', '2026-03-26 14:54:13.000', '2026-03-27 14:54:13.000'),
('ad57f329-01ff-4fd9-a4d3-1d2aaf186157', 'fcd616eb-143a-41e3-a5aa-b8e611023a2f', 'PRODUCT', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', NULL, 'SAVED', '2026-04-15 14:54:13.000', '2026-04-16 14:54:13.000'),
('b0a4b3eb-2b98-4035-a7c9-a0c404347ad8', '64aeeec4-6b61-4f92-b896-8d7f6e0b207c', 'PRODUCT', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', NULL, 'SAVED', '2026-05-20 14:54:13.000', '2026-05-21 14:54:13.000'),
('c5c58433-3466-4fe1-97c9-d528116d9cbd', 'c87f26d6-acee-4b9e-aee2-8ff4b10822ed', 'PRODUCT', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', NULL, 'REMOVED', '2026-04-05 14:54:13.000', '2026-04-06 14:54:13.000'),
('c7927c51-d6cf-47b2-a123-b3eb76a19b8c', 'fcd616eb-143a-41e3-a5aa-b8e611023a2f', 'LOOK', NULL, 'b9358f0a-48db-4197-a6eb-c21d41e3fee6', 'SAVED', '2026-04-10 14:54:13.000', '2026-04-11 14:54:13.000'),
('d665034d-7020-44d8-bc75-a7bec41618eb', '2e111f53-8154-4744-aac9-337250541f07', 'LOOK', NULL, '0ae5f57e-9803-4157-91f8-d5f9217b4dae', 'SAVED', '2026-05-03 14:54:13.000', '2026-05-04 14:54:13.000'),
('db1de7f7-d91c-4da1-8925-03e5c7799868', 'e787014b-a794-48b6-8beb-c50410dd0b6b', 'PRODUCT', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', NULL, 'REMOVED', '2026-04-20 14:54:13.000', '2026-04-21 14:54:13.000'),
('de291f11-54b6-4247-a63c-e722feb92a0d', '70d7b7fa-f5a7-43b7-bdb8-6ffc65a580cb', 'PRODUCT', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', NULL, 'SAVED', '2026-05-23 14:54:13.000', '2026-05-24 14:54:13.000'),
('ef410c11-1936-45ba-9dd7-e8948dc57fde', 'c87f26d6-acee-4b9e-aee2-8ff4b10822ed', 'LOOK', NULL, '4d35a094-209b-4e22-8db4-862ec03e0cd7', 'SAVED', '2026-03-16 14:54:13.000', '2026-03-17 14:54:13.000'),
('f0f07a35-2752-4b4c-91bf-b69370025493', '9274b05d-2b9a-4018-8408-63346c0bdcfa', 'PRODUCT', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', NULL, 'SAVED', '2026-06-08 20:44:49.000', '2026-06-08 20:45:15.000'),
('f1e59fef-0abb-42ff-8c49-8888c015e588', '42bc8b34-8f8a-4890-b892-84a566c950d9', 'LOOK', NULL, 'b4ed122d-fdb0-4064-8d8b-d30807e3eed8', 'SAVED', '2026-05-15 14:54:13.000', '2026-05-16 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `ingredient`
--

CREATE TABLE `ingredient` (
  `id` varchar(36) NOT NULL,
  `name` varchar(120) NOT NULL,
  `aliasJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`aliasJson`)),
  `family` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `benefitsJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefitsJson`)),
  `precautionsJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`precautionsJson`)),
  `iconUrl` varchar(700) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ingredient`
--

INSERT INTO `ingredient` (`id`, `name`, `aliasJson`, `family`, `description`, `benefitsJson`, `precautionsJson`, `iconUrl`, `status`, `createdAt`, `updatedAt`) VALUES
('1ab65e67-9386-4ecd-b69e-3dadc49bb672', 'Niacinamide', '[\"Vitamine B3\", \"Nicotinamide\"]', 'Vitamines', 'Une vitamine hydrosoluble polyvalente qui agit sur de multiples préoccupations cutanées. Elle renforce la barrière cutanée, régule la production de sébum, atténue les taches pigmentaires et apaise les rougeurs.', '[{\"title\": \"Régulation du Sébum\", \"description\": \"Aide à contrôler l\'excès de sébum et à réduire l\'apparence des pores dilatés.\"}, {\"title\": \"Éclat et Unification\", \"description\": \"Atténue l\'hyperpigmentation et unifie le teint.\"}, {\"title\": \"Apaisant\", \"description\": \"Réduit les rougeurs et l\'inflammation associées à l\'acné ou à la rosacée.\"}]', '[\"Généralement bien toléré, mais des concentrations très élevées (plus de 10%) peuvent causer des irritations chez certaines personnes.\"]', 'https://www.autocoder.cc/background/zaki_prod/generated/e0079db9a2fa43e0875349e95570c081.png', 'ACTIVE', '2026-03-26 14:54:13.000', '2026-03-26 14:54:13.000'),
('3d05e7c1-72d8-469f-98bb-1578f394929c', 'Céramides', '[\"Ceramide NP\", \"Ceramide AP\", \"Ceramide EOP\"]', 'Lipides', 'Des lipides naturellement présents dans la couche cornée, formant le \'ciment\' intercellulaire. Ils sont essentiels pour maintenir une barrière cutanée saine, retenir l\'hydratation et protéger contre les agressions extérieures.', '[{\"title\": \"Restauration de la Barrière\", \"description\": \"Répare et renforce la barrière cutanée altérée.\"}, {\"title\": \"Hydratation Protectrice\", \"description\": \"Prévient la perte insensible en eau (PIE).\"}, {\"title\": \"Apaisement\", \"description\": \"Calme les peaux sèches, irritées ou atopiques.\"}]', '[\"Aucune précaution particulière. Convient à tous les types de peaux, y compris les plus sensibles.\"]', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/2edf40a6e77747488a3f811578d511d1.png', 'ACTIVE', '2026-01-25 14:54:13.000', '2026-01-25 14:54:13.000'),
('3d18307a-5ec2-46f3-bcba-580f55ae8e3e', 'Vitamine C', '[\"Acide L-Ascorbique\", \"Ascorbyl Glucoside\", \"Sodium Ascorbyl Phosphate\"]', 'Antioxydants', 'Un antioxydant puissant qui protège la peau des radicaux libres causés par les UV et la pollution. Elle est réputée pour illuminer le teint, estomper les taches brunes et stimuler la production de collagène.', '[{\"title\": \"Éclat\", \"description\": \"Illumine instantanément les teints ternes et fatigués.\"}, {\"title\": \"Anti-taches\", \"description\": \"Inhibe la production de mélanine pour réduire l\'hyperpigmentation.\"}, {\"title\": \"Protection Antioxydante\", \"description\": \"Neutralise les radicaux libres responsables du vieillissement prématuré.\"}]', '[\"L\'acide L-ascorbique pur est instable et peut s\'oxyder (brunir). À conserver à l\'abri de la lumière et de la chaleur.\", \"Peut picoter sur les peaux sensibles, surtout à haute concentration.\"]', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/92cd22ec7b534f17ae9c118daf62d69c.png', 'ACTIVE', '2026-05-20 14:54:13.000', '2026-05-20 14:54:13.000'),
('4a7c151d-35a7-49cc-b43c-ec52c6f07190', 'Acide Glycolique', '[\"AHA\", \"Alpha Hydroxy Acid\"]', 'Acides Exfoliants (AHA)', 'L\'AHA le plus connu et dont la taille moléculaire est la plus petite, ce qui lui permet de pénétrer profondément. Il exfolie chimiquement la surface de la peau pour un effet peau neuve spectaculaire.', '[{\"title\": \"Exfoliation de Surface\", \"description\": \"Élimine les cellules mortes pour un teint éclatant.\"}, {\"title\": \"Anti-taches\", \"description\": \"Aide à estomper les taches pigmentaires et les cicatrices d\'acné.\"}, {\"title\": \"Lissage\", \"description\": \"Diminue les ridules de déshydratation et améliore la texture de la peau.\"}]', '[\"Peut être irritant pour les peaux sensibles. Commencer par de faibles concentrations.\", \"Rend la peau plus sensible au soleil, utilisation obligatoire d\'un SPF en journée.\"]', 'https://www.autocoder.cc/background/zaki_prod/generated/f713e1480d0c40158f1d38f7f740d66c.png', 'INACTIVE', '2025-11-06 14:54:13.000', '2025-11-06 14:54:13.000'),
('740fe4b3-e92f-4495-8f51-63d90bc4ab8d', 'Acide Hyaluronique', '[\"Hyaluronane\", \"Sodium Hyaluronate\", \"Acide Hyaluronique Hydrolysé\"]', 'Humectants', 'Molécule naturellement présente dans la peau, capable de retenir jusqu\'à 1000 fois son poids en eau. C\'est un puissant agent hydratant et repulpant qui aide à lisser les ridules et à maintenir la souplesse de la peau.', '[{\"title\": \"Hydratation Intense\", \"description\": \"Attire et retient l\'humidité dans les couches supérieures de l\'épiderme.\"}, {\"title\": \"Effet Repulpant\", \"description\": \"Restaure le volume de la peau et réduit l\'apparence des rides.\"}, {\"title\": \"Élasticité\", \"description\": \"Améliore la fermeté et la souplesse de la peau.\"}]', '[\"Utiliser sur peau légèrement humide pour de meilleurs résultats.\", \"Peut causer une sensation de tiraillement si utilisé dans un environnement très sec sans crème occlusive par-dessus.\"]', 'https://www.autocoder.cc/background/zaki_prod/generated/f4f831b416fc4aa2a25e4467e23d646e.png', 'ACTIVE', '2026-04-10 14:54:13.000', '2026-04-10 14:54:13.000'),
('7a6b34ed-0962-4b21-9c8b-af85068b6a84', 'Panthénol', '[\"Pro-Vitamine B5\"]', 'Vitamines', 'Le panthénol se transforme en vitamine B5 une fois absorbé par la peau. C\'est un humectant exceptionnel qui attire l\'eau, mais c\'est surtout son pouvoir réparateur et apaisant qui en fait un actif phare pour les peaux irritées.', '[{\"title\": \"Apaisement Intense\", \"description\": \"Calme les irritations, les coups de soleil et les rougeurs.\"}, {\"title\": \"Cicatrisation\", \"description\": \"Favorise la réparation des tissus cutanés.\"}, {\"title\": \"Hydratation\", \"description\": \"Maintient un bon niveau d\'hydratation dans la peau.\"}]', '[\"Actif très doux, parfaitement toléré par les bébés et les peaux atopiques.\"]', 'https://www.autocoder.cc/background/zaki_prod/generated/15a421b237c342e8908a6bd68ab1523c.png', 'ACTIVE', '2026-04-05 14:54:13.000', '2026-04-05 14:54:13.000'),
('b0e425b3-760b-4dbb-8df2-34a85a2b1786', 'Centella Asiatica', '[\"Cica\", \"Herbe du Tigre\", \"Madecassoside\"]', 'Extraits Botaniques', 'Une plante médicinale reconnue depuis des millénaires pour ses formidables propriétés cicatrisantes et apaisantes. Elle aide à calmer l\'inflammation, à réparer la peau et à stimuler la microcirculation.', '[{\"title\": \"Cicatrisation\", \"description\": \"Accélère la réparation des peaux lésées ou fragilisées.\"}, {\"title\": \"Anti-rougeurs\", \"description\": \"Apaise instantanément les irritations et diminue les rougeurs.\"}, {\"title\": \"Soutien du Collagène\", \"description\": \"Stimule la synthèse de collagène pour une meilleure élasticité.\"}]', '[\"Très bien tolérée, excellente pour les peaux sensibles ou sensibilisées.\"]', 'https://www.autocoder.cc/background/zaki_prod/generated/917d7622899c4849997980392d37efa3.png', 'ACTIVE', '2026-05-05 14:54:13.000', '2026-05-05 14:54:13.000'),
('c9dd47ad-2703-460f-9457-6b7759b0872d', 'Acide Salicylique', '[\"BHA\", \"Beta Hydroxy Acid\"]', 'Acides Exfoliants (BHA)', 'Un acide liposoluble capable de pénétrer à l\'intérieur des pores pour dissoudre le sébum et les cellules mortes. Idéal pour les peaux mixtes à grasses et sujettes aux imperfections.', '[{\"title\": \"Exfoliation Profonde\", \"description\": \"Désobstrue les pores et prévient la formation de comédons.\"}, {\"title\": \"Anti-imperfections\", \"description\": \"Traite l\'acné et réduit l\'inflammation des boutons.\"}, {\"title\": \"Affinement du Grain\", \"description\": \"Lisse la texture de la peau pour un fini plus net.\"}]', '[\"Peut être asséchant. Éviter l\'utilisation simultanée avec d\'autres exfoliants puissants ou rétinoïdes.\", \"Augmenter la sensibilité au soleil, utiliser une protection solaire.\"]', 'https://www.autocoder.cc/background/zaki_prod/generated/d3527826cf2f4e8889784a9fa41d810a.png', 'ACTIVE', '2026-05-10 14:54:13.000', '2026-05-10 14:54:13.000'),
('cd43b9e9-356f-417b-b21b-af24d381301e', 'Bakuchiol', '[\"Extrait de Babchi\"]', 'Extraits Botaniques', 'Une alternative végétale au rétinol, extraite des graines de la plante Psoralea corylifolia. Il offre des résultats anti-âge similaires (stimulation du collagène, réduction des rides) mais sans les effets secondaires irritants.', '[{\"title\": \"Alternative Douce\", \"description\": \"Effets anti-âge comparables au rétinol, idéal pour les peaux sensibles.\"}, {\"title\": \"Anti-rides\", \"description\": \"Réduit l\'apparence des ridules et améliore la fermeté.\"}, {\"title\": \"Antioxydant\", \"description\": \"Protège la peau du stress oxydatif environnemental.\"}]', '[\"Contrairement au rétinol, il n\'est pas photosensibilisant et peut être utilisé le matin et l\'été sans risque.\", \"Considéré comme sûr pendant la grossesse, bien qu\'un avis médical reste recommandé.\"]', 'https://www.autocoder.cc/background/zaki_prod/generated/d40fa968d40c4264901654128dd47f9c.png', 'INACTIVE', '2025-11-26 14:54:13.000', '2025-11-26 14:54:13.000'),
('de543044-0fdd-4c26-9c69-284642604622', 'Rétinol', '[\"Vitamine A\", \"Rétinoïdes\"]', 'Dérivés de Vitamine A', 'L\'actif anti-âge de référence. Il stimule le renouvellement cellulaire et la production de collagène, lissant les rides, améliorant la fermeté et atténuant les taches pigmentaires.', '[{\"title\": \"Anti-âge Puissant\", \"description\": \"Réduit visiblement les rides et ridules.\"}, {\"title\": \"Fermeté\", \"description\": \"Stimule la synthèse de collagène pour une peau plus tonique.\"}, {\"title\": \"Renouvellement Cellulaire\", \"description\": \"Accélère le turnover cellulaire pour un teint plus lumineux et uniforme.\"}]', '[\"Peut causer des rougeurs, une desquamation et une sécheresse en début d\'utilisation. Introduire progressivement.\", \"Fortement photosensibilisant, appliquer uniquement le soir et utiliser un SPF la journée.\", \"Déconseillé aux femmes enceintes ou allaitantes.\"]', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/e088d0ed8eff4736bb76cd57331ac9a8.png', 'ACTIVE', '2026-02-24 14:54:13.000', '2026-02-24 14:54:13.000'),
('eeeb831c-23e4-439f-88ec-b93d16ac998e', 'Squalane', '[\"Squalane Végétal\"]', 'Émollients', 'Une version stable et végétale (souvent issue de l\'olive ou de la canne à sucre) du squalène naturellement présent dans le sébum. C\'est une huile légère, non comédogène, qui hydrate et protège sans fini gras.', '[{\"title\": \"Hydratation Légère\", \"description\": \"Nourrit la peau sans obstruer les pores.\"}, {\"title\": \"Protection\", \"description\": \"Aide à sceller l\'hydratation et à protéger la barrière cutanée.\"}, {\"title\": \"Souplesse\", \"description\": \"Rend la peau incroyablement douce et lisse.\"}]', '[\"Idéal pour tous les types de peaux, y compris les peaux grasses et sujettes à l\'acné fongique.\"]', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/a65e7a3264d74cdb85a1cd163b6be16a.png', 'ACTIVE', '2026-05-17 14:54:13.000', '2026-05-17 14:54:13.000'),
('f7ff5132-2c67-47b6-94bf-777e23d980e3', 'Peptides', '[\"Polypeptides\", \"Matrixyl\", \"Argireline\"]', 'Acides Aminés', 'De courtes chaînes d\'acides aminés qui agissent comme des messagers cellulaires. Ils \'indiquent\' à la peau de produire plus de collagène ou d\'élastine, offrant ainsi des bienfaits anti-âge ciblés.', '[{\"title\": \"Anti-rides\", \"description\": \"Diminue la profondeur et la longueur des rides.\"}, {\"title\": \"Fermeté\", \"description\": \"Améliore le rebond et la structure globale de la peau.\"}, {\"title\": \"Réparation\", \"description\": \"Aide à renforcer la barrière cutanée.\"}]', '[\"L\'efficacité dépend du type de peptide utilisé et de sa concentration. Souvent associés à d\'autres actifs pour une meilleure synergie.\"]', 'https://www.autocoder.cc/background/zaki_prod/generated/d237d1d7413f4df59ac4425ce0c73ec3.png', 'ACTIVE', '2026-04-20 14:54:13.000', '2026-04-20 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `look_product`
--

CREATE TABLE `look_product` (
  `id` varchar(36) NOT NULL,
  `lookId` varchar(36) NOT NULL,
  `productId` varchar(36) NOT NULL,
  `faceZone` varchar(30) DEFAULT NULL,
  `stepLabel` varchar(100) DEFAULT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `look_product`
--

INSERT INTO `look_product` (`id`, `lookId`, `productId`, `faceZone`, `stepLabel`, `sortOrder`, `createdAt`) VALUES
('13648e99-0033-4485-8e0d-1fcf66fdc112', '725c71cf-10e4-4322-a3ec-56a54dcb4917', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-03-26 14:54:13.000'),
('194d2d1f-a4f0-4e2a-b3ab-b23566073c19', 'c932c44e-3534-4955-b4f2-d96ad466adba', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-03-28 14:54:13.000'),
('1ddea88a-b7c7-4a9d-a289-c251248e83df', '0c1009be-7cf5-4a4d-b065-3eefec70965f', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-05-10 14:54:13.000'),
('20db1182-ba28-41b4-ac7d-46774809f77c', 'ab750666-0fdb-4a0e-a11f-8a97493d5e23', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-05-11 14:54:13.000'),
('24919428-c2ab-438f-9600-4cf6b76429c2', '35fc61ee-705d-4e8b-a17e-b14a70d134ab', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-03-28 14:54:13.000'),
('258b5a5a-4104-449b-96fa-51d796f61984', '6d0ea68a-8c47-4a66-95b2-3a51a067c5a8', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-05-10 14:54:13.000'),
('29925e2a-e82e-46e3-9e4b-518b6ce9ef4d', 'b9358f0a-48db-4197-a6eb-c21d41e3fee6', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-03-28 14:54:13.000'),
('3c08747f-7e79-4708-9503-f8d4df69a5e2', '0c1009be-7cf5-4a4d-b065-3eefec70965f', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-05-11 14:54:13.000'),
('3f8217b0-e7a2-480a-8255-68c397cffb82', '0ae5f57e-9803-4157-91f8-d5f9217b4dae', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-05-11 14:54:13.000'),
('4012ebb4-18b1-46a9-8da5-bba06d68111c', '0ae5f57e-9803-4157-91f8-d5f9217b4dae', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-05-12 14:54:13.000'),
('42af3cd7-b4d5-48cd-a7e5-fbaf43cec6dd', '35fc61ee-705d-4e8b-a17e-b14a70d134ab', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-03-26 14:54:13.000'),
('4b5d0e64-527b-47a1-98e3-b75783849035', 'b4ed122d-fdb0-4064-8d8b-d30807e3eed8', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-03-28 14:54:13.000'),
('4b86e7d8-a9d4-4b2f-a903-0d77b126f4a3', 'b9358f0a-48db-4197-a6eb-c21d41e3fee6', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-03-27 14:54:13.000'),
('4f20a06c-33d9-4abf-a56d-25b33ddde0fd', 'b4ed122d-fdb0-4064-8d8b-d30807e3eed8', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-03-26 14:54:13.000'),
('53aa734f-0d63-4a48-997b-d437872b6e35', 'ab750666-0fdb-4a0e-a11f-8a97493d5e23', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-05-12 14:54:13.000'),
('5610dcfc-0d3d-452d-a7a4-6aa6cafdb126', 'b9358f0a-48db-4197-a6eb-c21d41e3fee6', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-03-26 14:54:13.000'),
('59121b38-d567-4834-9910-b1099b58996d', '35fc61ee-705d-4e8b-a17e-b14a70d134ab', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-03-27 14:54:13.000'),
('5a89a10b-8b2c-4406-853c-982acc04e9bd', '4d35a094-209b-4e22-8db4-862ec03e0cd7', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-03-26 14:54:13.000'),
('5e8bfcb2-b96c-4d7e-988c-5df0d1e7b43c', '0ae5f57e-9803-4157-91f8-d5f9217b4dae', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-05-10 14:54:13.000'),
('63b1a9d6-46cf-49c3-bfd0-55d83e1bdd81', '8669596c-8580-40fd-a5c7-4fb31071d6bd', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-03-27 14:54:13.000'),
('6b32f841-88ec-4c29-9dc2-5d274709beab', '4d35a094-209b-4e22-8db4-862ec03e0cd7', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-03-27 14:54:13.000'),
('7026a5b6-dcfb-4a73-a6bd-aa728fca00a5', '8669596c-8580-40fd-a5c7-4fb31071d6bd', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-03-28 14:54:13.000'),
('7397b359-e065-4b4e-a2eb-93af727c8944', 'c932c44e-3534-4955-b4f2-d96ad466adba', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-03-26 14:54:13.000'),
('750e070e-4ee3-466f-945c-ab43d2b86709', 'ab750666-0fdb-4a0e-a11f-8a97493d5e23', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-05-10 14:54:13.000'),
('7cff0668-725d-4a9f-b9c7-069b801897a6', '8669596c-8580-40fd-a5c7-4fb31071d6bd', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-03-26 14:54:13.000'),
('7e68991a-0b9f-46f7-92f7-68b0460a422c', '725c71cf-10e4-4322-a3ec-56a54dcb4917', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-03-27 14:54:13.000'),
('8515e897-fd48-4242-b87c-d3780315e565', 'bade2457-d713-4be6-b6df-1242a9021229', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-05-11 14:54:13.000'),
('a6981d5c-c1e8-47d6-9577-9b85f950727b', 'b4ed122d-fdb0-4064-8d8b-d30807e3eed8', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-03-27 14:54:13.000'),
('aa8d2a07-36b1-4f2e-be74-4b33d7f1976e', '725c71cf-10e4-4322-a3ec-56a54dcb4917', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-03-28 14:54:13.000'),
('ac81ab08-cea8-4b82-9b69-6b9208efd67d', 'c932c44e-3534-4955-b4f2-d96ad466adba', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-03-27 14:54:13.000'),
('b20e6a7c-cca5-41e3-96e1-8bfe6da707b4', '0c1009be-7cf5-4a4d-b065-3eefec70965f', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-05-12 14:54:13.000'),
('d0745579-0455-472d-97d9-f1a4c130f53c', '6d0ea68a-8c47-4a66-95b2-3a51a067c5a8', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 'Appliquez les fards de la palette sur la paupière mobile en estompant vers l\'extérieur pour sculpter', 2, '2026-05-11 14:54:13.000'),
('d7c32e1f-9c07-47af-863b-1ecd1b67ddd5', 'bade2457-d713-4be6-b6df-1242a9021229', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 'Préparez votre peau avec ce sérum hydratant en l\'appliquant délicatement sur le visage pour un teint', 1, '2026-05-10 14:54:13.000'),
('e8981c85-c3c1-4f1d-898a-5737d71b836c', '4d35a094-209b-4e22-8db4-862ec03e0cd7', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-03-28 14:54:13.000'),
('ea0427e2-dd2e-4b8e-a5f7-81a155e40dac', '6d0ea68a-8c47-4a66-95b2-3a51a067c5a8', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-05-12 14:54:13.000'),
('fe62d933-507e-4185-902f-bf0466819db8', 'bade2457-d713-4be6-b6df-1242a9021229', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 'Finalisez le maquillage des yeux en appliquant généreusement le mascara de la racine aux pointes afi', 3, '2026-05-12 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `product`
--

CREATE TABLE `product` (
  `id` varchar(36) NOT NULL,
  `categoryId` varchar(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `shortDescription` text NOT NULL,
  `longDescription` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'MAD',
  `imageUrl` varchar(700) NOT NULL,
  `affiliateUrl` varchar(700) DEFAULT NULL,
  `galleryJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`galleryJson`)),
  `benefitsJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefitsJson`)),
  `expertSummaryJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`expertSummaryJson`)),
  `usageAdviceJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`usageAdviceJson`)),
  `skinTypesJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skinTypesJson`)),
  `needsJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`needsJson`)),
  `tagsJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tagsJson`)),
  `badgesJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`badgesJson`)),
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product`
--

INSERT INTO `product` (`id`, `categoryId`, `name`, `slug`, `brand`, `shortDescription`, `longDescription`, `price`, `currency`, `imageUrl`, `affiliateUrl`, `galleryJson`, `benefitsJson`, `expertSummaryJson`, `usageAdviceJson`, `skinTypesJson`, `needsJson`, `tagsJson`, `badgesJson`, `status`, `sortOrder`, `createdAt`, `updatedAt`) VALUES
('0f6cdac3-fd1b-49ab-944c-4f5d092288d6', '3c28553c-3171-4e84-aa2d-eabe7d8f5744', 'Crème Nuit Restructurante', 'creme-nuit-restructurante', 'Éclat Botanique', 'Crème de nuit riche et enveloppante qui répare et nourrit intensément pendant votre sommeil pour un réveil éclatant.', 'Profitez de la nuit pour régénérer votre peau avec cette crème somptueuse. Sa formule unique, riche en céramides et en extraits botaniques rares, travaille en synergie avec le rythme biologique nocturne de votre peau. Elle aide à restaurer la barrière cutanée, à compenser la perte d\'hydratation et à lisser les traits au réveil. Sa texture cocon offre un véritable moment de bien-être avant le coucher, laissant la peau douce, souple et revitalisée au petit matin.', 65.00, 'MAD', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/9d8ee484ffc64f3a987469be75bace71.png', 'https://www.amazon.fr/s?k=creme+nuit+restructurante&tag=riseandshine-21', '[{\"alt\": \"Texture riche\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/3ef885db116341d2999b181080dd72ed.png\", \"sortOrder\": 1}, {\"alt\": \"Application sur visage\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/a0c1ce25302e4c4981f383dee9b753de.png\", \"sortOrder\": 2}, {\"alt\": \"Ingrédients clés\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/e396fb83a7444e09ad91f55b5e1fecce.png\", \"sortOrder\": 3}]', '[{\"title\": \"Nutrition intense\", \"intensity\": \"high\", \"description\": \"Nourrit en profondeur les peaux déshydratées\"}, {\"title\": \"Régénération\", \"intensity\": \"high\", \"description\": \"Soutient le renouvellement cellulaire nocturne\"}, {\"title\": \"Apaisement\", \"intensity\": \"medium\", \"description\": \"Calme les sensations de tiraillement\"}]', '{\"finish\": \"Velouté et protecteur.\", \"promise\": \"Une peau réparée, nourrie et reposée dès le premier réveil.\", \"texture\": \"Crème riche, onctueuse et réconfortante.\", \"targetAudience\": \"Peaux sèches à très sèches, ou en quête de confort nocturne.\"}', '{\"evening\": \"Appliquer une noisette sur le visage et le cou préalablement nettoyés.\", \"morning\": \"Non recommandé pour le jour (texture trop riche).\", \"frequency\": \"Quotidien, le soir.\", \"routineOrder\": \"Dernière étape de la routine de soin nocturne.\", \"avoidCombinations\": []}', '[\"dry\", \"mature\", \"sensitive\"]', '[\"hydration\", \"soothing\", \"anti_age\"]', '[\"creme-de-nuit\", \"nutrition\", \"cocooning\"]', '[{\"type\": \"match\", \"label\": \"Match Parfait\"}]', 'ACTIVE', 2, '2026-04-17 14:54:13.000', '2026-04-22 14:54:13.000'),
('2043db86-f412-404d-98f5-0ac698706f06', 'e1b2a233-40fa-449b-ad93-d172df18a2ea', 'Palette Ombré Majestueux', 'palette-ombre-majestueux', 'Aura Paris', 'Palette de 12 fards à paupières hautement pigmentés, mêlant teintes mates et irisées pour des regards captivants.', 'Laissez libre cours à votre créativité avec la Palette Ombré Majestueux. Conçue pour offrir une infinité de looks, du plus naturel au plus dramatique, cette palette rassemble 12 teintes universellement flatteuses. Les poudres, d\'une finesse extrême, s\'estompent sans effort et garantissent une tenue irréprochable tout au long de la journée sans filer dans les plis. Enrichies en pigments purs, les couleurs révèlent toute leur intensité dès le premier passage. Parfait pour les essais virtuels sur notre plateforme.', 52.50, 'MAD', 'https://www.autocoder.cc/background/zaki_prod/generated/07a9cdf9595f4061bbc97e7e3882c38f.png', 'https://www.amazon.fr/s?k=palette+ombre+majestueux&tag=riseandshine-21', '[{\"alt\": \"Palette ouverte\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/ba45864e1ff64a1db0c78600ac40aec4.png\", \"sortOrder\": 1}, {\"alt\": \"Swatches sur le bras\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/b9c9fa7a4d844a488c3da61bb7fb9472.png\", \"sortOrder\": 2}, {\"alt\": \"Look maquillage réalisé\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/6c9ed0db18354cdd9ac149765b0203c2.png\", \"sortOrder\": 3}]', '[{\"title\": \"Haute pigmentation\", \"intensity\": \"high\", \"description\": \"Couleurs intenses en un seul passage\"}, {\"title\": \"Longue tenue\", \"intensity\": \"high\", \"description\": \"Ne coule pas et résiste à la transpiration\"}, {\"title\": \"Facilité d\'estompage\", \"intensity\": \"high\", \"description\": \"Textures crémeuses faciles à travailler\"}]', '{\"finish\": \"Mat, satiné ou métallique selon la teinte.\", \"promise\": \"Des regards sur mesure avec une tenue impeccable.\", \"texture\": \"Poudre pressée, fini velours et irisé.\", \"targetAudience\": \"Amateurs et professionnels de maquillage cherchant polyvalence et qualité.\"}', '{\"evening\": \"Intensifier avec les teintes sombres et irisées pour une soirée.\", \"morning\": \"Utiliser les teintes claires et mates pour un look de jour élégant.\", \"frequency\": \"Selon les envies.\", \"routineOrder\": \"Après la base à paupières, avant le mascara.\", \"avoidCombinations\": []}', '[\"oily\",\"combination\",\"dry\",\"sensitive\",\"normal\",\"mature\"]', '[\"radiance\"]', '[\"palette\",\"fard-a-paupieres\",\"maquillage\",\"yeux\"]', '[{\"type\": \"new\", \"label\": \"Nouveau\"}]', 'ACTIVE', 3, '2026-05-05 14:54:13.000', '2026-06-08 21:23:40.000'),
('43e9bd15-f5c8-413c-b732-18ba14fa4f97', 'c0fc75cd-ec72-450e-991d-10328bd38130', 'Fond de Teint Couvrance Invisible', 'fond-de-teint-couvrance-invisible', 'Perfect Skin', 'Fond de teint fluide unificateur au fini naturel, masquant les imperfections tout en légèreté.', 'Révolutionnez votre routine teint avec notre Fond de Teint Couvrance Invisible. Grâce à sa technologie de pigments bio-mimétiques, il fusionne avec la peau pour un fini indétectable, effet \'seconde peau\'. Il camoufle les rougeurs, taches et cernes sans effet masque. Sa formule non comédogène, enrichie en agents matifiants et hydratants, contrôle la brillance de la zone T tout en préservant le confort des zones sèches. Disponible en 40 teintes, trouvez la vôtre grâce à notre diagnostic IA.', 39.50, 'MAD', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/af43bb6f357a467fa501f84f61a56333.png', 'https://www.amazon.fr/s?k=fond+de+teint+couvrance+invisible&tag=riseandshine-21', '[{\"alt\": \"Texture fluide\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/4a36cb5d0b8e49718b5d3beb9a737a79.png\", \"sortOrder\": 1}, {\"alt\": \"Différentes teintes\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/fd820cea50f7447f8381d6728e203dd2.png\", \"sortOrder\": 2}, {\"alt\": \"Avant/Après couvrance\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/0526eecd57fd46819658747546a97504.png\", \"sortOrder\": 3}]', '[{\"title\": \"Couvrance modulable\", \"intensity\": \"medium\", \"description\": \"De légère à moyenne selon l\'application\"}, {\"title\": \"Fini naturel\", \"intensity\": \"high\", \"description\": \"Effet peau nue lumineuse\"}, {\"title\": \"Longue tenue\", \"intensity\": \"medium\", \"description\": \"Reste impeccable jusqu\'à 12 heures\"}]', '{\"finish\": \"Semi-mat lumineux.\", \"promise\": \"Un teint zéro défaut sans sensation de matière.\", \"texture\": \"Fluide ultra-léger et étirable.\", \"targetAudience\": \"Celles cherchant à unifier leur teint avec un fini naturel.\"}', '{\"evening\": \"Peut être retouché facilement.\", \"morning\": \"Appliquer aux doigts, au pinceau ou à l\'éponge sur le centre du visage puis étirer vers l\'extérieur.\", \"frequency\": \"Quotidien.\", \"routineOrder\": \"Après le soin de jour et la base, avant la poudre.\", \"avoidCombinations\": []}', '[\"normal\", \"combination\", \"oily\", \"dry\"]', '[\"radiance\"]', '[\"fond-de-teint\", \"teint\", \"couvrance-naturelle\"]', '[]', 'INACTIVE', 6, '2026-03-31 14:54:13.000', '2026-04-05 14:54:13.000'),
('966d9c23-1234-4020-baf3-d0e306a25d2e', 'cfe79223-3ac1-4d86-9f26-b4e124ce43ba', 'Sérum Hydratation Profonde', 'serum-hydratation-profonde', 'Eau Pure', 'Un bain d\'hydratation immédiat pour les peaux assoiffées et sensibles, à la texture gel ultra-fraîche.', 'Véritable concentré d\'hydratation, ce sérum désaltère instantanément les peaux les plus déshydratées. Formulé avec trois poids moléculaires d\'acide hyaluronique, il hydrate en surface et en profondeur, repulpant les ridules de déshydratation. Enrichi en eau thermale apaisante, il calme les irritations et rougeurs des peaux sensibles. Sa texture gel d\'eau pénètre instantanément sans effet collant, laissant la peau fraîche, rebondie et lumineuse pour toute la journée.', 45.00, 'MAD', 'https://www.autocoder.cc/background/zaki_prod/generated/f787a9505d1a4d828df359fa4ca5240f.png', 'https://www.amazon.fr/s?k=serum+hydratation+profonde&tag=riseandshine-21', '[{\"alt\": \"Goutte de sérum\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/29a4eb71e4424cd792f2d76af9925fae.png\", \"sortOrder\": 1}, {\"alt\": \"Femme souriante\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/9c02523d27944393a474a07d76acacdd.png\", \"sortOrder\": 2}, {\"alt\": \"Flacon pompe\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/41cf2c7fce3b42fc80a9e5fdd65cd482.png\", \"sortOrder\": 3}]', '[{\"title\": \"Hydratation 48h\", \"intensity\": \"high\", \"description\": \"Maintient un niveau d\'hydratation optimal\"}, {\"title\": \"Apaisement\", \"intensity\": \"high\", \"description\": \"Réduit les rougeurs et sensations d\'échauffement\"}, {\"title\": \"Repulpant\", \"intensity\": \"medium\", \"description\": \"Lisse les ridules de déshydratation\"}]', '{\"finish\": \"Frais et non collant.\", \"promise\": \"Une peau intensément hydratée, apaisée et repulpée.\", \"texture\": \"Gel aqueux léger et fondant.\", \"targetAudience\": \"Peaux déshydratées, sensibles, tiraillées.\"}', '{\"evening\": \"Même utilisation que le matin.\", \"morning\": \"1 à 2 pompes sur peau légèrement humide avant la crème.\", \"frequency\": \"Quotidien, matin et soir.\", \"routineOrder\": \"Première étape de soin après le nettoyage.\", \"avoidCombinations\": []}', '[\"sensitive\", \"dry\", \"combination\", \"normal\"]', '[\"hydration\", \"soothing\"]', '[\"serum\", \"hydratation\", \"acide-hyaluronique\"]', '[{\"type\": \"match\", \"label\": \"Recommandé par l\'IA\"}]', 'ACTIVE', 5, '2026-05-15 14:54:13.000', '2026-05-20 14:54:13.000'),
('b9c6b4c7-daa4-4a14-aa85-7457772a14f5', '3c28553c-3171-4e84-aa2d-eabe7d8f5744', 'Sérum Anti-Âge Révolution', 'serum-anti-age-revolution', 'Lumière Éternelle', 'Un sérum puissant formulé par IA pour cibler les rides profondes et redonner une fermeté spectaculaire à votre visage.', 'Découvrez notre innovation majeure en matière de soin anti-âge. Ce sérum concentré utilise des actifs bio-compatibles analysés par notre intelligence artificielle pour stimuler le renouvellement cellulaire nocturne. Enrichi en peptides raffermissants et en acide hyaluronique de bas poids moléculaire, il pénètre profondément pour repulper la peau de l\'intérieur. Idéal pour retrouver un teint éclatant et visiblement rajeuni après seulement 4 semaines d\'utilisation régulière.', 89.90, 'MAD', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/eb5fe8cc150b458283de8238d8db30b2.png', 'https://www.amazon.fr/s?k=serum+anti+age+revolution&tag=riseandshine-21', '[{\"alt\": \"Texture du sérum\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/d08e8c76c6844b21aa158a88c56059e0.png\", \"sortOrder\": 1}, {\"alt\": \"Avant/Après\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/b09f29ce74b2420ea123bb922156a181.png\", \"sortOrder\": 2}, {\"alt\": \"Packaging premium\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/6dac6ad9ab3548f8844f0a581ff73d70.png\", \"sortOrder\": 3}]', '[{\"title\": \"Réduction des rides\", \"intensity\": \"high\", \"description\": \"Diminue visiblement la profondeur des rides d\'expression\"}, {\"title\": \"Fermeté\", \"intensity\": \"high\", \"description\": \"Restaure l\'élasticité cutanée et redessine l\'ovale du visage\"}, {\"title\": \"Éclat\", \"intensity\": \"medium\", \"description\": \"Illumine le teint terne et fatigué\"}]', '{\"finish\": \"Naturel et lumineux.\", \"promise\": \"Une peau visiblement plus jeune et repulpée en 28 jours.\", \"texture\": \"Fluide léger et soyeux, absorption rapide sans fini gras.\", \"targetAudience\": \"Personnes à partir de 35 ans cherchant à prévenir ou corriger les signes de l\'âge.\"}', '{\"evening\": \"Appliquer 3-4 gouttes, masser délicatement en mouvements ascendants.\", \"morning\": \"Appliquer 2-3 gouttes sur peau propre avant la crème de jour.\", \"frequency\": \"Quotidien, matin et soir.\", \"routineOrder\": \"Après le nettoyage/lotion, avant la crème hydratante.\", \"avoidCombinations\": [\"Acides exfoliants forts (AHA/BHA) dans la même routine\"]}', '[\"mature\", \"normal\", \"dry\", \"combination\"]', '[\"anti_age\", \"hydration\", \"radiance\"]', '[\"anti-age\", \"serum\", \"premium\", \"innovation\"]', '[{\"type\": \"bestseller\", \"label\": \"Best-Seller\"}, {\"type\": \"editorial\", \"label\": \"Choix de la Rédaction\"}]', 'ACTIVE', 1, '2026-04-15 14:54:13.000', '2026-04-20 14:54:13.000'),
('f2546299-04ad-4979-a552-3b050c43c30f', 'e1b2a233-40fa-449b-ad93-d172df18a2ea', 'Mascara Volume Absolu', 'mascara-volume-absolu', 'Regard Infini', 'Mascara ultra-noir enrichi en fibres pour un volume spectaculaire et une longueur démesurée sans paquets.', 'Obtenez un effet faux-cils instantané avec le Mascara Volume Absolu. Sa brosse en silicone au design exclusif capture chaque cil, même les plus courts, pour les gainer d\'un noir intense et profond de la racine à la pointe. La formule innovante, enrichie en cires végétales et en provitamine B5, nourrit les cils tout en assurant une tenue 24h sans s\'effriter ni couler. Un indispensable pour intensifier le regard en un seul geste, facile à démaquiller.', 28.00, 'MAD', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/13c1b54ce977463c8de0aa4bf30830f5.png', 'https://www.amazon.fr/s?k=mascara+volume+absolu&tag=riseandshine-21', '[{\"alt\": \"Brosse du mascara\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/f501a791d7624c58834a184a66530cde.png\", \"sortOrder\": 1}, {\"alt\": \"Avant/Après application\", \"url\": \"https://www.autocoder.cc/background/zaki_prod/generated/ed918581aaf24e82a92104e6c3efbf23.png\", \"sortOrder\": 2}, {\"alt\": \"Design du tube\", \"url\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/ab4a8626e63c40e7b85fd920c20847be.png\", \"sortOrder\": 3}]', '[{\"title\": \"Volume extrême\", \"intensity\": \"high\", \"description\": \"Multiplie l\'épaisseur des cils par 3\"}, {\"title\": \"Longueur\", \"intensity\": \"high\", \"description\": \"Allonge visiblement grâce aux micro-fibres\"}, {\"title\": \"Soin\", \"intensity\": \"medium\", \"description\": \"Fortifie les cils au fil des utilisations\"}]', '{\"finish\": \"Noir mat intense.\", \"promise\": \"Des cils démultipliés, allongés et intensément noirs.\", \"texture\": \"Crème fluide qui ne sèche pas trop vite.\", \"targetAudience\": \"Celles et ceux qui recherchent du volume et de l\'intensité sans compromis.\"}', '{\"evening\": \"Une couche supplémentaire pour un effet plus dramatique.\", \"morning\": \"Appliquer en zigzag de la racine aux pointes pour un effet maximal.\", \"frequency\": \"Quotidien.\", \"routineOrder\": \"Dernière étape du maquillage des yeux.\", \"avoidCombinations\": []}', '[\"normal\", \"oily\", \"combination\", \"dry\", \"sensitive\", \"mature\"]', '[]', '[\"mascara\", \"volume\", \"cils\"]', '[{\"type\": \"bestseller\", \"label\": \"Best-Seller\"}]', 'ACTIVE', 4, '2026-05-03 14:54:13.000', '2026-05-08 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `product_category`
--

CREATE TABLE `product_category` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product_category`
--

INSERT INTO `product_category` (`id`, `name`, `slug`, `description`, `status`, `sortOrder`, `createdAt`, `updatedAt`) VALUES
('3c28553c-3171-4e84-aa2d-eabe7d8f5744', 'Soins du Visage Anti-Âge', 'soins-du-visage-anti-age', 'Découvrez notre gamme de soins visage anti-âge conçus par IA pour redonner éclat et fermeté à votre peau.', 'ACTIVE', 1, '2026-04-10 14:54:13.000', '2026-04-12 14:54:13.000'),
('43e9afc8-5114-47f4-a737-8bda841cb27f', 'Nettoyants Purifiants Doux', 'nettoyants-purifiants-doux', 'Gels, mousses et lotions nettoyantes pour éliminer les impuretés en douceur, idéals pour une routine quotidienne.', 'ACTIVE', 5, '2026-05-15 14:54:13.000', '2026-05-17 14:54:13.000'),
('c0fc75cd-ec72-450e-991d-10328bd38130', 'Teint Parfait Lissant', 'teint-parfait-lissant', 'Fonds de teint et correcteurs haute couvrance pour unifier votre teint tout en lissant les imperfections.', 'ACTIVE', 4, '2026-03-26 14:54:13.000', '2026-03-28 14:54:13.000'),
('cfe79223-3ac1-4d86-9f26-b4e124ce43ba', 'Hydratation Peau Sensible', 'hydratation-peau-sensible', 'Des crèmes et sérums ultra-hydratants, spécialement formulés pour apaiser et protéger les peaux les plus sensibles.', 'ACTIVE', 3, '2026-05-10 14:54:13.000', '2026-05-12 14:54:13.000'),
('e1b2a233-40fa-449b-ad93-d172df18a2ea', 'Maquillage Yeux Intense', 'maquillage-yeux-intense', 'Une sélection de fards, mascaras et eyeliners pour sublimer votre regard avec des couleurs intenses et durables.', 'ACTIVE', 2, '2026-04-30 14:54:13.000', '2026-05-02 14:54:13.000'),
('f80d7504-b2b3-4936-83f6-6451f728b03b', 'Rouges à Lèvres Essentiels', 'rouges-a-levres-essentiels', 'Collection de rouges à lèvres mats et brillants, essayables virtuellement pour trouver votre teinte idéale.', 'INACTIVE', 6, '2026-04-20 14:54:13.000', '2026-04-22 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `product_ingredient`
--

CREATE TABLE `product_ingredient` (
  `id` varchar(36) NOT NULL,
  `productId` varchar(36) NOT NULL,
  `ingredientId` varchar(36) NOT NULL,
  `displayName` varchar(120) DEFAULT NULL,
  `functionSummary` text DEFAULT NULL,
  `intensityLevel` varchar(30) DEFAULT NULL,
  `precautions` text DEFAULT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product_ingredient`
--

INSERT INTO `product_ingredient` (`id`, `productId`, `ingredientId`, `displayName`, `functionSummary`, `intensityLevel`, `precautions`, `sortOrder`, `createdAt`, `updatedAt`) VALUES
('028c7450-96da-4185-9661-f9915d354158', 'f2546299-04ad-4979-a552-3b050c43c30f', '3d05e7c1-72d8-469f-98bb-1578f394929c', 'Céramides Gaineurs', 'Enveloppe chaque cil pour un volume époustouflant.', 'Moyenne', 'Éviter le contact avec la muqueuse.', 2, '2026-05-08 14:54:13.000', '2026-05-08 14:54:13.000'),
('0f3dd373-1190-442e-8d82-e01a2c084fa3', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 'eeeb831c-23e4-439f-88ec-b93d16ac998e', 'Squalane Végétal', 'Nourrit en douceur sans obstruer les pores.', 'Douce', 'Convient à un usage quotidien régulier.', 2, '2026-04-26 14:54:13.000', '2026-04-26 14:54:13.000'),
('2ed96a62-0438-41ea-8529-90eb145c0510', '2043db86-f412-404d-98f5-0ac698706f06', '7a6b34ed-0962-4b21-9c8b-af85068b6a84', NULL, NULL, NULL, NULL, 0, '2026-06-08 21:23:40.000', '2026-06-08 21:23:40.000'),
('34bf9f5d-aea3-4d02-b229-9e5c92f04a79', '966d9c23-1234-4020-baf3-d0e306a25d2e', '7a6b34ed-0962-4b21-9c8b-af85068b6a84', 'Panthénol Apaisant', 'Calme rapidement les sensations d\'échauffement.', 'Moyenne', 'Masser délicatement lors de l\'application.', 2, '2026-05-18 14:54:13.000', '2026-05-18 14:54:13.000'),
('38e7f6f0-b721-4735-b1f0-73cbb11ce225', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 'de543044-0fdd-4c26-9c69-284642604622', 'Rétinol Encapsulé', 'Stimule le renouvellement cellulaire contre les rides.', 'Moyenne', 'Appliquer uniquement le soir, utiliser SPF.', 2, '2026-04-21 14:54:13.000', '2026-04-21 14:54:13.000'),
('3a60b206-2c1b-48fd-90ae-857075fc48b5', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', '7a6b34ed-0962-4b21-9c8b-af85068b6a84', 'Pro-Vitamine B5', 'Apaise les irritations et maintient l\'hydratation.', 'Douce', 'Bien refermer le pot après chaque usage.', 3, '2026-04-27 14:54:13.000', '2026-04-27 14:54:13.000'),
('58ec51a0-4cc6-44d9-9bad-0c5a200ab69d', '966d9c23-1234-4020-baf3-d0e306a25d2e', '740fe4b3-e92f-4495-8f51-63d90bc4ab8d', 'Acide Hyaluronique Multiple', 'Désaltère instantanément les peaux déshydratées.', 'Élevée', 'Appliquer sur une peau légèrement humide.', 1, '2026-05-17 14:54:13.000', '2026-05-17 14:54:13.000'),
('5c2c078f-34ba-4f1c-be5b-5570561c6e26', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', '3d18307a-5ec2-46f3-bcba-580f55ae8e3e', 'Vitamine C Stabilisée', 'Illumine le teint terne et offre un éclat naturel.', 'Moyenne', 'Conserver à l\'abri de la lumière directe.', 4, '2026-04-23 14:54:13.000', '2026-04-23 14:54:13.000'),
('5df45dd2-68c1-41fd-a22c-09418ba59b97', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', '1ab65e67-9386-4ecd-b69e-3dadc49bb672', 'Niacinamide', 'Unifie le teint et réduit les rougeurs visibles.', 'Moyenne', 'Peut causer des picotements au début.', 4, '2026-04-28 14:54:13.000', '2026-04-28 14:54:13.000'),
('5e3952d5-533c-4b69-9927-7899379baa14', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 'f7ff5132-2c67-47b6-94bf-777e23d980e3', 'Complexe de Peptides', 'Renforce la fermeté et redessine l\'ovale du visage.', 'Élevée', 'Tester sur une petite zone avant usage.', 3, '2026-04-22 14:54:13.000', '2026-04-22 14:54:13.000'),
('7dbcb30e-7632-4ecc-8169-609082cb38a7', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', '3d05e7c1-72d8-469f-98bb-1578f394929c', 'Céramides Essentiels', 'Répare la barrière cutanée pendant votre sommeil.', 'Élevée', 'Ne pas appliquer sur plaies ouvertes.', 1, '2026-04-25 14:54:13.000', '2026-04-25 14:54:13.000'),
('882f215d-8226-4864-9e70-c54b2573633f', '2043db86-f412-404d-98f5-0ac698706f06', 'eeeb831c-23e4-439f-88ec-b93d16ac998e', NULL, NULL, NULL, NULL, 1, '2026-06-08 21:23:40.000', '2026-06-08 21:23:40.000'),
('944c33e0-fc86-4edf-98be-f0888b6fd649', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', '740fe4b3-e92f-4495-8f51-63d90bc4ab8d', 'Acide Hyaluronique Pur', 'Hydrate intensément en profondeur et repulpe la peau.', 'Élevée', 'Éviter le contact direct avec les yeux.', 1, '2026-04-20 14:54:13.000', '2026-04-20 14:54:13.000'),
('a392ce93-46bf-4b44-9b25-b216356a738e', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'b0e425b3-760b-4dbb-8df2-34a85a2b1786', 'Extrait de Cica', 'Favorise la réparation des peaux sensibilisées.', 'Douce', 'Peut s\'utiliser matin et soir sans risque.', 3, '2026-05-19 14:54:13.000', '2026-05-19 14:54:13.000'),
('c6976189-39d2-42fe-94ad-22a0bc2af80b', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'eeeb831c-23e4-439f-88ec-b93d16ac998e', 'Squalane Léger', 'Scelle l\'hydratation sans fini gras ou collant.', 'Douce', 'Laisser pénétrer avant de maquiller.', 4, '2026-05-20 14:54:13.000', '2026-05-20 14:54:13.000'),
('eb888cd8-5d39-4b76-aa42-ebdc3d45b4b3', 'f2546299-04ad-4979-a552-3b050c43c30f', '7a6b34ed-0962-4b21-9c8b-af85068b6a84', 'Panthénol Nourrissant', 'Fortifie les cils au fil des applications régulières.', 'Douce', 'Démaquiller soigneusement chaque soir.', 1, '2026-05-07 14:54:13.000', '2026-05-07 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `product_relation`
--

CREATE TABLE `product_relation` (
  `id` varchar(36) NOT NULL,
  `fromProductId` varchar(36) NOT NULL,
  `toProductId` varchar(36) NOT NULL,
  `relationType` varchar(30) NOT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `product_relation`
--

INSERT INTO `product_relation` (`id`, `fromProductId`, `toProductId`, `relationType`, `sortOrder`, `createdAt`) VALUES
('0c70967a-c173-4b92-a18f-ee18aff2e030', 'f2546299-04ad-4979-a552-3b050c43c30f', '2043db86-f412-404d-98f5-0ac698706f06', 'COMPLEMENTARY', 3, '2026-05-07 14:54:13.000'),
('1a9faf60-d922-45b1-8370-342e3b485400', '2043db86-f412-404d-98f5-0ac698706f06', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 'COMPLEMENTARY', 0, '2026-06-08 21:23:40.000'),
('335000f2-a465-445b-9855-cf9b3175674d', '966d9c23-1234-4020-baf3-d0e306a25d2e', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 'COMPLEMENTARY', 2, '2026-05-17 14:54:13.000'),
('34137c4f-21f5-4b5c-b652-7abb0d6c1328', '2043db86-f412-404d-98f5-0ac698706f06', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 'COMPLEMENTARY', 2, '2026-06-08 21:23:40.000'),
('3ff3ed7e-6c1f-4e4c-a82c-de25d86e7d27', '2043db86-f412-404d-98f5-0ac698706f06', 'f2546299-04ad-4979-a552-3b050c43c30f', 'UPSELL', 5, '2026-05-15 14:54:13.000'),
('408fde82-cc9c-451c-9ed9-cb34386d39a9', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'UPSELL', 5, '2026-05-10 14:54:13.000'),
('55125b00-9369-4b25-a82b-eb5e3c08b3b8', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 'COMPLEMENTARY', 1, '2026-04-15 14:54:13.000'),
('67c562a4-c74a-4f44-bcf1-76a1e88c29d2', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'ALTERNATIVE', 4, '2026-04-30 14:54:13.000'),
('6ac91e95-862a-4af0-a0c5-3fc7b31f8805', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', '2043db86-f412-404d-98f5-0ac698706f06', 'COMPLEMENTARY', 2, '2026-04-20 14:54:13.000'),
('70989646-dbf5-431a-be2b-727f7020278e', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', '2043db86-f412-404d-98f5-0ac698706f06', 'COMPLEMENTARY', 2, '2026-04-25 14:54:13.000'),
('85f8c764-be37-4681-8407-a351e3f2ed69', 'f2546299-04ad-4979-a552-3b050c43c30f', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 'COMPLEMENTARY', 2, '2026-05-05 14:54:13.000'),
('96050963-c3ae-44a3-8a64-b52d868aa7d4', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 'f2546299-04ad-4979-a552-3b050c43c30f', 'COMPLEMENTARY', 3, '2026-04-30 14:54:13.000'),
('9862d17a-5b2f-4690-82e2-903fad7349e7', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 'f2546299-04ad-4979-a552-3b050c43c30f', 'COMPLEMENTARY', 3, '2026-04-25 14:54:13.000'),
('9f1d13fe-8a74-4f60-ade4-16c8d04e2a4b', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 'UPSELL', 5, '2026-05-24 14:54:13.000'),
('c18cf5ce-d6f8-4340-b20e-3a7e565a32b4', '2043db86-f412-404d-98f5-0ac698706f06', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'ALTERNATIVE', 4, '2026-05-15 14:54:13.000'),
('c97eda21-f2b3-4e75-a462-a31ab0840578', 'f2546299-04ad-4979-a552-3b050c43c30f', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'COMPLEMENTARY', 4, '2026-05-10 14:54:13.000'),
('c9961eff-4843-462e-89a7-f6cef813878c', 'f2546299-04ad-4979-a552-3b050c43c30f', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 'COMPLEMENTARY', 1, '2026-05-03 14:54:13.000'),
('d34813f7-4e35-4cf8-bbb6-37378f7af25e', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'f2546299-04ad-4979-a552-3b050c43c30f', 'COMPLEMENTARY', 4, '2026-05-23 14:54:13.000'),
('d48a47dc-77a3-4f88-8fc3-013dbc868d11', '966d9c23-1234-4020-baf3-d0e306a25d2e', '2043db86-f412-404d-98f5-0ac698706f06', 'ALTERNATIVE', 3, '2026-05-20 14:54:13.000'),
('db2f24f0-84c7-4523-acba-cb1ea7ba6a4c', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'COMPLEMENTARY', 4, '2026-05-05 14:54:13.000'),
('dfc222cf-3b07-4a5d-a7e2-5e6af99aad22', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'UPSELL', 5, '2026-05-13 14:54:13.000'),
('e03d8398-4889-479d-abe8-a51626ec94ed', '2043db86-f412-404d-98f5-0ac698706f06', 'f2546299-04ad-4979-a552-3b050c43c30f', 'COMPLEMENTARY', 1, '2026-06-08 21:23:40.000'),
('e996d195-1bfe-479a-814d-8ab79ed19d81', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 'ALTERNATIVE', 1, '2026-05-15 14:54:13.000'),
('f04ee58f-6c6d-4d24-8f6f-2979ff7560c0', '0f6cdac3-fd1b-49ab-944c-4f5d092288d6', 'b9c6b4c7-daa4-4a14-aa85-7457772a14f5', 'COMPLEMENTARY', 1, '2026-04-17 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `role_permission`
--

CREATE TABLE `role_permission` (
  `id` varchar(36) NOT NULL,
  `role` enum('USER','ADMIN') NOT NULL,
  `permissionKey` varchar(100) NOT NULL,
  `permissionLabel` varchar(150) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 0,
  `isSystemLocked` tinyint(1) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role_permission`
--

INSERT INTO `role_permission` (`id`, `role`, `permissionKey`, `permissionLabel`, `allowed`, `isSystemLocked`, `createdAt`, `updatedAt`) VALUES
('06106a27-c5a6-4e0c-aaae-cb75209dde85', 'USER', 'USE_AI_DIAGNOSTIC', 'Utiliser l\'outil de diagnostic IA', 1, 1, '2026-03-06 14:54:13.000', '2026-04-05 14:54:13.000'),
('2a0c2df3-cc9e-4d2f-a729-0c6ced5a114d', 'USER', 'MANAGE_CATALOG', 'Gérer les produits du catalogue', 0, 1, '2026-02-24 14:54:13.000', '2026-03-26 14:54:13.000'),
('517efa0f-60e1-448f-86d4-90e4a922c9ae', 'USER', 'VIEW_CATALOG', 'Consulter le catalogue de produits', 1, 1, '2026-03-16 14:54:13.000', '2026-04-15 14:54:13.000'),
('5f4b7807-5fa1-4e72-a9d1-705e740910d7', 'USER', 'MANAGE_SYSTEM', 'Gérer les paramètres du système', 0, 1, '2026-02-14 14:54:13.000', '2026-03-16 14:54:13.000'),
('6c152bb2-9045-4c7c-a800-8a941d01224a', 'ADMIN', 'MANAGE_SYSTEM', 'Gérer les paramètres du système', 1, 1, '2026-04-05 14:54:13.000', '2026-05-05 14:54:13.000'),
('70e04578-522b-4be4-a86f-ec3d573ab54a', 'ADMIN', 'VIEW_CATALOG', 'Consulter le catalogue de produits', 1, 1, '2026-02-04 14:54:13.000', '2026-03-06 14:54:13.000'),
('891f4a9b-7e74-41c2-88dc-93aef2291258', 'ADMIN', 'MANAGE_CATALOG', 'Gérer les produits du catalogue', 1, 0, '2026-04-15 14:54:13.000', '2026-05-15 14:54:13.000'),
('a5dab36c-880c-4958-b55c-c920034dbee7', 'ADMIN', 'USE_AI_DIAGNOSTIC', 'Utiliser l\'outil de diagnostic IA', 1, 1, '2026-04-20 14:54:13.000', '2026-05-20 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` varchar(36) NOT NULL,
  `siteName` varchar(120) NOT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `siteDescription` text DEFAULT NULL,
  `contactEmail` varchar(100) DEFAULT NULL,
  `socialLinksJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`socialLinksJson`)),
  `legalContentJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`legalContentJson`)),
  `globalImagesJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`globalImagesJson`)),
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `site_settings`
--

INSERT INTO `site_settings` (`id`, `siteName`, `tagline`, `siteDescription`, `contactEmail`, `socialLinksJson`, `legalContentJson`, `globalImagesJson`, `createdAt`, `updatedAt`) VALUES
('d04be0ac-8a55-4154-8312-ab3caf933669', 'Rise & Shine', 'Votre beauté assistée par l\'IA', 'Découvrez une nouvelle dimension de la beauté avec notre plateforme innovante. Nous combinons l\'intelligence artificielle pour analyser votre peau et vos traits, offrant des recommandations personnalisées et des essais virtuels.', 'contact@riseandshine.fr', '[{\"url\": \"https://instagram.com/riseandshine_fr\", \"platform\": \"instagram\"}, {\"url\": \"https://facebook.com/riseandshine.france\", \"platform\": \"facebook\"}, {\"url\": \"https://tiktok.com/@riseandshine_beauty\", \"platform\": \"tiktok\"}, {\"url\": \"https://twitter.com/rise_shine_fr\", \"platform\": \"twitter\"}]', '{\"version\": \"1.0.0\", \"termsOfUse\": \"L\'utilisation des outils d\'essai virtuel est soumise à l\'acceptation de nos CGU. Les résultats sont fournis à titre indicatif.\", \"legalNotice\": \"Rise & Shine est édité par la société RS Beauté SAS au capital de 10 000€, immatriculée au RCS de Paris.\", \"cookiePolicy\": \"Nous utilisons des cookies essentiels pour le fonctionnement du site et des cookies analytiques pour améliorer votre expérience.\", \"privacyPolicy\": \"Nous nous engageons à protéger vos données personnelles. Les données d\'analyse IA sont anonymisées et ne sont jamais revendues.\"}', '{\"aboutHeroUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/d3c8899b05c2454abd878021040e94e5.png\", \"defaultShareImageUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/b4332081c84c4946893bad454e40c95f.png\"}', '2026-05-10 14:54:13.000', '2026-05-15 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `skin_quiz_answer`
--

CREATE TABLE `skin_quiz_answer` (
  `id` varchar(36) NOT NULL,
  `sessionId` varchar(36) NOT NULL,
  `questionId` varchar(36) NOT NULL,
  `optionId` varchar(36) NOT NULL,
  `answeredAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `skin_quiz_answer`
--

INSERT INTO `skin_quiz_answer` (`id`, `sessionId`, `questionId`, `optionId`, `answeredAt`) VALUES
('02a130b1-cb2a-4a2f-afe8-f9f1a70cf039', '349229b3-c9c2-4844-acac-bcd3592118de', '62e3017a-47aa-4ffd-a97b-8141b07cd833', '0a491925-0ef0-469c-97d8-802d0a513b0f', '2026-06-08 01:00:14.000'),
('0847d501-40ce-4bb7-9656-0830c59a4b36', '26eadf1b-2ad5-4140-9413-5ffc6bb47785', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', '277d753b-a095-4ee3-a940-c4c04aee1f03', '2026-05-10 16:20:37.000'),
('0e43d185-da55-4445-9522-853e1cb1d8f8', '5083ea48-e500-4657-a7b9-1a55ff96eec3', '62e3017a-47aa-4ffd-a97b-8141b07cd833', '0a491925-0ef0-469c-97d8-802d0a513b0f', '2026-06-08 20:31:15.000'),
('11ffd318-1217-4a39-8945-3a45538600e8', '26eadf1b-2ad5-4140-9413-5ffc6bb47785', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', '4b278700-5099-4e87-b95a-7a4dd1470174', '2026-05-10 16:06:13.000'),
('16ee73d1-d84d-4609-b653-fc638b71e381', 'c217ec41-fe93-4a70-b309-25deb4e4ce7e', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'ef303f30-c7eb-4d04-a6c9-5dba6fec4af4', '2026-06-08 20:34:12.000'),
('17f5358d-818d-4cc4-b5a6-4c27471850f9', '30691b47-2e59-4e1c-8e15-51d682739fa1', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', '5970694b-d518-4766-9287-87b282cea55c', '2026-04-30 15:08:37.000'),
('1d2a44f1-d46d-4428-bda4-76daec49ff0e', '66c25fd7-19c1-48e1-bcd2-96511f9d5732', 'bab6de9b-04e5-4079-b848-571706e1f5a0', '4e8d1a4c-99fd-4c9f-9ce3-c11dee062dd4', '2026-06-08 20:49:30.000'),
('228d5025-035e-4ef7-a3e1-4ea7e915f71a', '11f742f0-65f2-45f5-a5eb-29093e025a71', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', '5970694b-d518-4766-9287-87b282cea55c', '2026-06-08 20:23:39.000'),
('30a2355b-d0eb-435d-840c-da1eb9f5f524', '30691b47-2e59-4e1c-8e15-51d682739fa1', '62e3017a-47aa-4ffd-a97b-8141b07cd833', 'f46a3179-ae6f-475d-94ba-96997ebaee80', '2026-04-30 15:23:01.000'),
('30cccddc-2c7b-4bbd-985b-bafbb166cbd1', '349229b3-c9c2-4844-acac-bcd3592118de', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', '4b278700-5099-4e87-b95a-7a4dd1470174', '2026-06-08 01:00:23.000'),
('35330b5c-3556-426f-b9cf-997b81a1c111', '11f742f0-65f2-45f5-a5eb-29093e025a71', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'c8074a95-f08c-4478-9139-740a3cf623b2', '2026-06-08 20:24:05.000'),
('3c07d4f7-6d05-44d4-af2a-5d55c1027fae', '0b616535-43be-4248-b1d3-0713ca0307bf', '53f1c25f-d8ff-4b0c-8395-538438143037', 'ad7c331c-1574-454b-b95a-319ca97e2735', '2026-05-23 15:37:25.000'),
('3cbbfe7f-28c1-4e76-a3cf-28bee22a795a', '36d3dbcd-126b-4207-865e-205bc99b6c7d', '62e3017a-47aa-4ffd-a97b-8141b07cd833', 'f46a3179-ae6f-475d-94ba-96997ebaee80', '2026-05-24 15:23:01.000'),
('4020ff3f-8a53-42fd-a2b3-2952bf13f09f', '93a1e6f7-9f1d-4033-adf7-3dd55757ca10', '62e3017a-47aa-4ffd-a97b-8141b07cd833', '7f1946a3-bc38-4970-9d45-e85cd48c3e3f', '2026-05-20 15:23:01.000'),
('404bf8ce-c303-41a9-a5bb-2b171f0c30c7', '5083ea48-e500-4657-a7b9-1a55ff96eec3', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'c8074a95-f08c-4478-9139-740a3cf623b2', '2026-06-08 20:31:41.000'),
('43ea8da9-39b0-4d69-9bb3-fe1c508df0a1', '349229b3-c9c2-4844-acac-bcd3592118de', 'bab6de9b-04e5-4079-b848-571706e1f5a0', 'be8efdbb-fdaf-4e2c-bb6e-ac8f8a8cf951', '2026-06-08 01:00:22.000'),
('459f861e-0084-4801-802c-1b8781e44e83', '8dd29371-c090-4c86-bc7c-c1f12f92be03', 'bab6de9b-04e5-4079-b848-571706e1f5a0', '553fb279-af56-4c2e-9d34-f9dbd67b7f4c', '2026-05-05 15:51:49.000'),
('48f9e165-fdf8-4967-b304-39a87dba52a1', '93a1e6f7-9f1d-4033-adf7-3dd55757ca10', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', '5970694b-d518-4766-9287-87b282cea55c', '2026-05-20 15:08:37.000'),
('4bb28353-8a77-4de6-89a9-61be41bd3c56', '66c25fd7-19c1-48e1-bcd2-96511f9d5732', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', '4b278700-5099-4e87-b95a-7a4dd1470174', '2026-06-08 20:49:31.000'),
('52f7c08a-3a92-4f46-9d90-1396f091d7ae', '26eadf1b-2ad5-4140-9413-5ffc6bb47785', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'a985d354-383b-4e04-a9d5-810f7671a48a', '2026-05-10 15:08:37.000'),
('5435c2cf-aab1-45f6-9202-12175a8756ef', '93a1e6f7-9f1d-4033-adf7-3dd55757ca10', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'b32206c4-d9e5-4f94-8676-80f126d2fd6e', '2026-05-20 16:06:13.000'),
('5ec80f60-90e6-4ecd-bf5e-052d51e45635', '11f742f0-65f2-45f5-a5eb-29093e025a71', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'ef303f30-c7eb-4d04-a6c9-5dba6fec4af4', '2026-06-08 20:24:00.000'),
('6440ed19-f1c7-4d0c-ba78-d09679a8d237', '5083ea48-e500-4657-a7b9-1a55ff96eec3', 'bab6de9b-04e5-4079-b848-571706e1f5a0', 'be8efdbb-fdaf-4e2c-bb6e-ac8f8a8cf951', '2026-06-08 20:31:31.000'),
('72c19e47-474a-46fa-ae3a-a78dd8c6764c', '0b616535-43be-4248-b1d3-0713ca0307bf', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'c8074a95-f08c-4478-9139-740a3cf623b2', '2026-05-23 16:20:37.000'),
('75cc70d1-fe46-4025-aeb5-3721cba2b6b2', '8dd29371-c090-4c86-bc7c-c1f12f92be03', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'fa991ca7-5985-40f2-a9b5-964e85d3b402', '2026-05-05 15:08:37.000'),
('7b010eec-dd0f-4205-aa85-d4c65c550df8', '26eadf1b-2ad5-4140-9413-5ffc6bb47785', 'bab6de9b-04e5-4079-b848-571706e1f5a0', '553fb279-af56-4c2e-9d34-f9dbd67b7f4c', '2026-05-10 15:51:49.000'),
('7c995176-d523-4fa1-b8ac-8d25ae993200', 'c217ec41-fe93-4a70-b309-25deb4e4ce7e', 'bab6de9b-04e5-4079-b848-571706e1f5a0', '4e8d1a4c-99fd-4c9f-9ce3-c11dee062dd4', '2026-06-08 20:34:05.000'),
('83262107-9605-4f82-9538-95f872265f4c', '5083ea48-e500-4657-a7b9-1a55ff96eec3', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', '5970694b-d518-4766-9287-87b282cea55c', '2026-06-08 20:31:20.000'),
('8383d3ab-ee08-409d-b5bd-71972877f8e3', '66c25fd7-19c1-48e1-bcd2-96511f9d5732', '53f1c25f-d8ff-4b0c-8395-538438143037', '4b4733d9-64ba-43dc-872d-d88ae9fc56f7', '2026-06-08 20:49:29.000'),
('83f20c28-0992-4f50-be62-85ee908774e6', '8dd29371-c090-4c86-bc7c-c1f12f92be03', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'fbba4c49-1a99-4bdc-9598-223b861f6b14', '2026-05-05 16:06:13.000'),
('84400d8f-cbcd-4e04-8f7f-1f9176d335a2', '11f742f0-65f2-45f5-a5eb-29093e025a71', '53f1c25f-d8ff-4b0c-8395-538438143037', 'ad7c331c-1574-454b-b95a-319ca97e2735', '2026-06-08 20:23:48.000'),
('8dcdd7fc-2093-40bf-bd34-4a50f380b2c9', '66c25fd7-19c1-48e1-bcd2-96511f9d5732', '62e3017a-47aa-4ffd-a97b-8141b07cd833', '0a491925-0ef0-469c-97d8-802d0a513b0f', '2026-06-08 20:49:27.000'),
('993255c0-b663-4a92-9a07-bc6fd6c0efc2', '11f742f0-65f2-45f5-a5eb-29093e025a71', '62e3017a-47aa-4ffd-a97b-8141b07cd833', '0a491925-0ef0-469c-97d8-802d0a513b0f', '2026-06-08 20:23:31.000'),
('a0e32457-fb0e-4021-9ee5-632ed49470d7', '93a1e6f7-9f1d-4033-adf7-3dd55757ca10', '53f1c25f-d8ff-4b0c-8395-538438143037', 'be62a538-2243-4b84-a13c-ca09eba8d62b', '2026-05-20 15:37:25.000'),
('a360bdc4-504f-4534-8949-6392f9d7ff64', '93a1e6f7-9f1d-4033-adf7-3dd55757ca10', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', '28cd1f5a-12ea-419e-891e-1d1230a1da57', '2026-05-20 16:20:37.000'),
('b1c44bb3-e2b3-42c5-b421-de8cbb4091c1', '5083ea48-e500-4657-a7b9-1a55ff96eec3', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', '4b278700-5099-4e87-b95a-7a4dd1470174', '2026-06-08 20:31:36.000'),
('b28932c9-b398-456b-a70a-e4962993c41c', '93a1e6f7-9f1d-4033-adf7-3dd55757ca10', 'bab6de9b-04e5-4079-b848-571706e1f5a0', 'be8efdbb-fdaf-4e2c-bb6e-ac8f8a8cf951', '2026-05-20 15:51:49.000'),
('b2dd276e-8215-4ec6-8e15-b54b8f5df435', '66c25fd7-19c1-48e1-bcd2-96511f9d5732', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'c8074a95-f08c-4478-9139-740a3cf623b2', '2026-06-08 20:49:32.000'),
('ba1415a2-108d-4070-862b-b225ee611605', 'c217ec41-fe93-4a70-b309-25deb4e4ce7e', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'c8074a95-f08c-4478-9139-740a3cf623b2', '2026-06-08 20:34:17.000'),
('bd5f5d9b-7a29-4183-87f5-b4d86a56e37b', '8dd29371-c090-4c86-bc7c-c1f12f92be03', '62e3017a-47aa-4ffd-a97b-8141b07cd833', '7f1946a3-bc38-4970-9d45-e85cd48c3e3f', '2026-05-05 15:23:01.000'),
('c8a3d603-2761-4ce6-9bc5-b7bd9f998ef1', '26eadf1b-2ad5-4140-9413-5ffc6bb47785', '62e3017a-47aa-4ffd-a97b-8141b07cd833', 'c37c0d83-bc0f-49e1-89a4-6addc0d1e902', '2026-05-10 15:23:01.000'),
('c9a77a41-84a0-419e-a76a-28b85477e775', '0b616535-43be-4248-b1d3-0713ca0307bf', '62e3017a-47aa-4ffd-a97b-8141b07cd833', '0a491925-0ef0-469c-97d8-802d0a513b0f', '2026-05-23 15:23:01.000'),
('cb514fb7-27cd-46cd-b814-e70df3cb0c72', '8dd29371-c090-4c86-bc7c-c1f12f92be03', '53f1c25f-d8ff-4b0c-8395-538438143037', '4b4733d9-64ba-43dc-872d-d88ae9fc56f7', '2026-05-05 15:37:25.000'),
('cdbbbe79-06cd-4c9f-93fb-5f28086debc1', 'c217ec41-fe93-4a70-b309-25deb4e4ce7e', '53f1c25f-d8ff-4b0c-8395-538438143037', 'ad7c331c-1574-454b-b95a-319ca97e2735', '2026-06-08 20:33:59.000'),
('d058e749-cd90-4c6b-a0d3-0a560d8c935d', '36d3dbcd-126b-4207-865e-205bc99b6c7d', 'bab6de9b-04e5-4079-b848-571706e1f5a0', 'b70e70be-1ab4-4b36-b7ef-4c458a2315cb', '2026-05-24 15:51:49.000'),
('d4145c6f-7d10-4c6a-ae2b-bfeea17387c3', 'c217ec41-fe93-4a70-b309-25deb4e4ce7e', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', '5970694b-d518-4766-9287-87b282cea55c', '2026-06-08 20:33:54.000'),
('d9c558ed-d37d-4e7a-b85a-3db548d41de3', '36d3dbcd-126b-4207-865e-205bc99b6c7d', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', '9c22a9fe-a65d-4bf2-a7fd-45a7ce752b5e', '2026-05-24 15:08:37.000'),
('daaf21da-8bf0-46eb-8590-dc9f1e9044af', 'fbf3d696-59c0-4bd9-a2cf-848542f00889', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', '9c22a9fe-a65d-4bf2-a7fd-45a7ce752b5e', '2026-04-15 15:08:37.000'),
('dcbbfdf7-2595-4117-9e95-e81908e3881e', '349229b3-c9c2-4844-acac-bcd3592118de', '53f1c25f-d8ff-4b0c-8395-538438143037', '4b4733d9-64ba-43dc-872d-d88ae9fc56f7', '2026-06-08 01:00:20.000'),
('e1015cee-2564-4ec9-bda7-3ead83687c2b', 'c217ec41-fe93-4a70-b309-25deb4e4ce7e', '62e3017a-47aa-4ffd-a97b-8141b07cd833', '0a491925-0ef0-469c-97d8-802d0a513b0f', '2026-06-08 20:33:48.000'),
('e90b5bbc-b3ca-42e9-93b7-7974e7dbf263', '0b616535-43be-4248-b1d3-0713ca0307bf', 'bab6de9b-04e5-4079-b848-571706e1f5a0', '4e8d1a4c-99fd-4c9f-9ce3-c11dee062dd4', '2026-05-23 15:51:49.000'),
('e9943b6a-07de-4165-a372-bc68106b20b5', '0b616535-43be-4248-b1d3-0713ca0307bf', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'ef303f30-c7eb-4d04-a6c9-5dba6fec4af4', '2026-05-23 16:06:13.000'),
('ea550013-b073-45f9-bb25-c9917dcd59ee', '5083ea48-e500-4657-a7b9-1a55ff96eec3', '53f1c25f-d8ff-4b0c-8395-538438143037', 'ad7c331c-1574-454b-b95a-319ca97e2735', '2026-06-08 20:31:26.000'),
('eb95e6e7-f75a-4699-afd4-4ee26c107a94', '349229b3-c9c2-4844-acac-bcd3592118de', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'fa991ca7-5985-40f2-a9b5-964e85d3b402', '2026-06-08 01:00:16.000'),
('ef6f6113-b5e5-4e4d-a442-8e7d13015ee8', '349229b3-c9c2-4844-acac-bcd3592118de', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'c8074a95-f08c-4478-9139-740a3cf623b2', '2026-06-08 01:00:25.000'),
('f4395ae6-b46f-4362-a1c6-3ce3d99d5769', '26eadf1b-2ad5-4140-9413-5ffc6bb47785', '53f1c25f-d8ff-4b0c-8395-538438143037', 'e525dc30-a771-46a9-a7c8-930c33e13ee0', '2026-05-10 15:37:25.000'),
('f5aa5c3f-b471-4931-8a94-5cd43ba127f8', '8dd29371-c090-4c86-bc7c-c1f12f92be03', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', '1545d414-de7a-48a3-8ffe-abe26571d492', '2026-05-05 16:20:37.000'),
('f819168f-63c9-4f63-9401-0acba0edf7f6', '0b616535-43be-4248-b1d3-0713ca0307bf', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'fa991ca7-5985-40f2-a9b5-964e85d3b402', '2026-05-23 15:08:37.000'),
('f8a42647-5993-4cfb-b977-1ad7bf8ff251', 'fbf3d696-59c0-4bd9-a2cf-848542f00889', '62e3017a-47aa-4ffd-a97b-8141b07cd833', 'c37c0d83-bc0f-49e1-89a4-6addc0d1e902', '2026-04-15 15:23:01.000'),
('faed3e45-071b-49e6-a891-39f2b1f64111', '11f742f0-65f2-45f5-a5eb-29093e025a71', 'bab6de9b-04e5-4079-b848-571706e1f5a0', '4e8d1a4c-99fd-4c9f-9ce3-c11dee062dd4', '2026-06-08 20:23:55.000'),
('fc052986-6a94-4d7f-b569-c857e6449ee8', '66c25fd7-19c1-48e1-bcd2-96511f9d5732', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'fa991ca7-5985-40f2-a9b5-964e85d3b402', '2026-06-08 20:49:28.000'),
('fef54973-9e6f-4661-b56b-b5b6551c2b38', '36d3dbcd-126b-4207-865e-205bc99b6c7d', '53f1c25f-d8ff-4b0c-8395-538438143037', '4b4733d9-64ba-43dc-872d-d88ae9fc56f7', '2026-05-24 15:37:25.000');

-- --------------------------------------------------------

--
-- Structure de la table `skin_quiz_option`
--

CREATE TABLE `skin_quiz_option` (
  `id` varchar(36) NOT NULL,
  `questionId` varchar(36) NOT NULL,
  `optionText` text NOT NULL,
  `imageUrl` varchar(700) DEFAULT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `scoreJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`scoreJson`)),
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `skin_quiz_option`
--

INSERT INTO `skin_quiz_option` (`id`, `questionId`, `optionText`, `imageUrl`, `sortOrder`, `scoreJson`, `createdAt`, `updatedAt`) VALUES
('00871e68-7b13-4543-9f1d-96cf5c1e90b1', '20938c91-4df4-47f8-9c5c-8bdcf3719ae3', 'Occasionnellement, le week-end ou pendant les vacances.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/8d69bff950a749b6a8c4dfc336dee1d4.png', 2, '{\"aging\": 1, \"sebum\": 1, \"hydration\": 0, \"sensitivity\": 0}', '2026-05-15 14:54:13.000', '2026-05-16 14:54:13.000'),
('0980699d-17ce-4138-bfec-2da9b3446fbf', '20938c91-4df4-47f8-9c5c-8bdcf3719ae3', 'Très souvent, je travaille ou fais du sport en extérieur.', 'https://www.autocoder.cc/background/zaki_prod/generated/ebfeb4abcec849a5a49267475ef6ac1c.png', 4, '{\"aging\": 3, \"sebum\": 3, \"hydration\": -3, \"sensitivity\": 2}', '2026-05-15 14:54:13.000', '2026-05-16 14:54:13.000'),
('0a491925-0ef0-469c-97d8-802d0a513b0f', '62e3017a-47aa-4ffd-a97b-8141b07cd833', 'Presque jamais, ma peau est très résistante.', 'https://www.autocoder.cc/background/zaki_prod/generated/ea834ce6777541ff967e948802cd5ab2.png', 1, '{\"aging\": 0, \"sebum\": 0, \"hydration\": 1, \"sensitivity\": -3}', '2026-04-15 14:54:13.000', '2026-04-16 14:54:13.000'),
('153825ee-568a-4e4e-b0c5-a808cd8a8a33', 'ae965074-fe74-4158-8aaf-99f24ee17c3d', 'Non, ma peau a toujours été relativement sans problème.', 'https://www.autocoder.cc/background/zaki_prod/generated/c13ca0f27ef0490ba29763936e91fdbd.png', 1, '{\"aging\": 0, \"sebum\": -1, \"hydration\": 1, \"sensitivity\": -2}', '2026-05-20 14:54:13.000', '2026-05-21 14:54:13.000'),
('1545d414-de7a-48a3-8ffe-abe26571d492', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'Je fais presque toujours une réaction allergique forte.', 'https://www.autocoder.cc/background/zaki_prod/generated/2d5c698ef7814ff0bd2a9167b3413ae3.png', 4, '{\"aging\": 2, \"sebum\": -1, \"hydration\": -2, \"sensitivity\": 3}', '2026-05-10 14:54:13.000', '2026-05-11 14:54:13.000'),
('277d753b-a095-4ee3-a940-c4c04aee1f03', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'Souvent des rougeurs ou des éruptions cutanées modérées.', 'https://www.autocoder.cc/background/zaki_prod/generated/fa35ee607736468cb1e0b9121c0427ae.png', 3, '{\"aging\": 1, \"sebum\": 1, \"hydration\": -1, \"sensitivity\": 2}', '2026-05-10 14:54:13.000', '2026-05-11 14:54:13.000'),
('28cd1f5a-12ea-419e-891e-1d1230a1da57', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'Quelques picotements légers qui disparaissent vite.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/07e963fd7ab7486ebbcb2dbda7c840c7.png', 2, '{\"aging\": 0, \"sebum\": 0, \"hydration\": 0, \"sensitivity\": 1}', '2026-05-10 14:54:13.000', '2026-05-11 14:54:13.000'),
('2a000f42-80a3-420f-b87f-9a8ce5045f3d', '20938c91-4df4-47f8-9c5c-8bdcf3719ae3', 'Rarement, je passe la plupart de mon temps à l\'intérieur.', 'https://images.pexels.com/photos/5158587/pexels-photo-5158587.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940', 1, '{\"aging\": -2, \"sebum\": 0, \"hydration\": 1, \"sensitivity\": -1}', '2026-05-15 14:54:13.000', '2026-05-16 14:54:13.000'),
('4b278700-5099-4e87-b95a-7a4dd1470174', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'Oui, principalement sur le front, le nez et le menton.', 'https://www.autocoder.cc/background/zaki_prod/generated/ea660ee50e8c4c83a2d3498b2b4fea8d.png', 3, '{\"aging\": -1, \"sebum\": 2, \"hydration\": 0, \"sensitivity\": 0}', '2026-05-05 14:54:13.000', '2026-05-06 14:54:13.000'),
('4b4733d9-64ba-43dc-872d-d88ae9fc56f7', '53f1c25f-d8ff-4b0c-8395-538438143037', 'Acné persistante, points noirs et pores dilatés.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/63ae3850e9e74f129d09eacbe66cd1a1.png', 3, '{\"aging\": 0, \"sebum\": 3, \"hydration\": -1, \"sensitivity\": 1}', '2026-04-20 14:54:13.000', '2026-04-21 14:54:13.000'),
('4e8d1a4c-99fd-4c9f-9ce3-c11dee062dd4', 'bab6de9b-04e5-4079-b848-571706e1f5a0', 'Je n\'utilise qu\'un nettoyant et une crème hydratante (1-2).', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/00f115500e944ded89c2d898c48f6c02.png', 1, '{\"aging\": 0, \"sebum\": 0, \"hydration\": -1, \"sensitivity\": 0}', '2026-04-25 14:54:13.000', '2026-04-26 14:54:13.000'),
('553fb279-af56-4c2e-9d34-f9dbd67b7f4c', 'bab6de9b-04e5-4079-b848-571706e1f5a0', 'Je change constamment, aucune routine fixe.', 'https://www.autocoder.cc/background/zaki_prod/generated/38a2f3902e3540eca2a10738ff69b3da.png', 4, '{\"aging\": 0, \"sebum\": 1, \"hydration\": -2, \"sensitivity\": 2}', '2026-04-25 14:54:13.000', '2026-04-26 14:54:13.000'),
('5970694b-d518-4766-9287-87b282cea55c', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'Confortable, ni trop sèche, ni grasse.', 'https://www.autocoder.cc/background/zaki_prod/generated/e9166c26f27240578961d27a0e77c601.png', 2, '{\"aging\": 0, \"sebum\": 0, \"hydration\": 2, \"sensitivity\": 0}', '2026-04-10 14:54:13.000', '2026-04-11 14:54:13.000'),
('7cce3939-45ba-4105-ae25-beaf0ca4b36a', 'ae965074-fe74-4158-8aaf-99f24ee17c3d', 'Seulement quelques imperfections occasionnelles liées au stress.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/fc02d0d7e35948f4833b1e20273982d8.png', 2, '{\"aging\": 0, \"sebum\": 1, \"hydration\": 0, \"sensitivity\": 0}', '2026-05-20 14:54:13.000', '2026-05-21 14:54:13.000'),
('7f1946a3-bc38-4970-9d45-e85cd48c3e3f', '62e3017a-47aa-4ffd-a97b-8141b07cd833', 'Parfois, après l\'utilisation de certains produits forts.', 'https://www.autocoder.cc/background/zaki_prod/generated/6a338f85a69345228c8852a9fb52afda.png', 2, '{\"aging\": 0, \"sebum\": 0, \"hydration\": 0, \"sensitivity\": 1}', '2026-04-15 14:54:13.000', '2026-04-16 14:54:13.000'),
('83004cfc-f9b1-45c7-978a-3c2274a55307', '20938c91-4df4-47f8-9c5c-8bdcf3719ae3', 'Quotidiennement pour de courtes périodes (trajets, pauses).', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/4154fa621806486dbd4fc277ff46b134.png', 3, '{\"aging\": 2, \"sebum\": 2, \"hydration\": -1, \"sensitivity\": 1}', '2026-05-15 14:54:13.000', '2026-05-16 14:54:13.000'),
('90a027bd-a130-45b1-ba90-9ca00e043f80', 'ae965074-fe74-4158-8aaf-99f24ee17c3d', 'Oui, de l\'eczéma, de la rosacée ou une dermatite diagnostiquée.', 'https://www.autocoder.cc/background/zaki_prod/generated/baf27d39150d4856b37628c1235e8dcb.png', 4, '{\"aging\": 2, \"sebum\": -2, \"hydration\": -3, \"sensitivity\": 3}', '2026-05-20 14:54:13.000', '2026-05-21 14:54:13.000'),
('9c22a9fe-a65d-4bf2-a7fd-45a7ce752b5e', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'Brillante sur la zone T, mais normale ailleurs.', 'https://www.autocoder.cc/background/zaki_prod/generated/b9e50204b2a4425eb6ba6d4556cef9de.png', 3, '{\"aging\": 0, \"sebum\": 2, \"hydration\": 1, \"sensitivity\": 0}', '2026-04-10 14:54:13.000', '2026-04-11 14:54:13.000'),
('a985d354-383b-4e04-a9d5-810f7671a48a', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'Brillante sur l\'ensemble du visage, sensation d\'excès de sébum.', 'https://www.autocoder.cc/background/zaki_prod/generated/810a92cc21ae4f76beee5abf1076e792.png', 4, '{\"aging\": -1, \"sebum\": 3, \"hydration\": 0, \"sensitivity\": -1}', '2026-04-10 14:54:13.000', '2026-04-11 14:54:13.000'),
('ad7c331c-1574-454b-b95a-319ca97e2735', '53f1c25f-d8ff-4b0c-8395-538438143037', 'Manque d\'éclat, teint terne et fatigue visible.', 'https://www.autocoder.cc/background/zaki_prod/generated/beadf399b1a1406abb26a153a8cb1e20.png', 1, '{\"aging\": 1, \"sebum\": 0, \"hydration\": 2, \"sensitivity\": 0}', '2026-04-20 14:54:13.000', '2026-04-21 14:54:13.000'),
('b32206c4-d9e5-4f94-8676-80f126d2fd6e', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'Légèrement en fin de journée, mais rien de gênant.', 'https://www.autocoder.cc/background/zaki_prod/generated/2c4b4cb230d0493f9306e98bb06c1265.png', 2, '{\"aging\": 0, \"sebum\": 1, \"hydration\": 1, \"sensitivity\": 0}', '2026-05-05 14:54:13.000', '2026-05-06 14:54:13.000'),
('b70e70be-1ab4-4b36-b7ef-4c458a2315cb', 'bab6de9b-04e5-4079-b848-571706e1f5a0', 'Routine élaborée incluant lotions et essences (5+).', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/5fe682f94311428b985a16c51264c580.png', 3, '{\"aging\": 2, \"sebum\": 1, \"hydration\": 3, \"sensitivity\": 1}', '2026-04-25 14:54:13.000', '2026-04-26 14:54:13.000'),
('be62a538-2243-4b84-a13c-ca09eba8d62b', '53f1c25f-d8ff-4b0c-8395-538438143037', 'Apparition de ridules et perte de fermeté.', 'https://www.autocoder.cc/background/zaki_prod/generated/07f59f9fc21344fba97d2f41029d462b.png', 2, '{\"aging\": 3, \"sebum\": 0, \"hydration\": 2, \"sensitivity\": 1}', '2026-04-20 14:54:13.000', '2026-04-21 14:54:13.000'),
('be8efdbb-fdaf-4e2c-bb6e-ac8f8a8cf951', 'bab6de9b-04e5-4079-b848-571706e1f5a0', 'Routine de base avec sérum et contour des yeux (3-4).', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/146b66de6bcc43a58442c436599f1252.png', 2, '{\"aging\": 1, \"sebum\": 0, \"hydration\": 1, \"sensitivity\": 0}', '2026-04-25 14:54:13.000', '2026-04-26 14:54:13.000'),
('c37c0d83-bc0f-49e1-89a4-6addc0d1e902', '62e3017a-47aa-4ffd-a97b-8141b07cd833', 'Très fréquemment, ma peau rougit au moindre contact.', 'https://www.autocoder.cc/background/zaki_prod/generated/38bde22ef3f14f7982ce050f64d163c5.png', 4, '{\"aging\": 1, \"sebum\": -1, \"hydration\": -2, \"sensitivity\": 3}', '2026-04-15 14:54:13.000', '2026-04-16 14:54:13.000'),
('c8074a95-f08c-4478-9139-740a3cf623b2', '14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'Aucune réaction négative, ma peau accepte tout.', 'https://www.autocoder.cc/background/zaki_prod/generated/55a25fb5f1ac47caad6735932bd49869.png', 1, '{\"aging\": 0, \"sebum\": 0, \"hydration\": 1, \"sensitivity\": -3}', '2026-05-10 14:54:13.000', '2026-05-11 14:54:13.000'),
('cdc8bc4b-03d6-4d50-98ab-57be600cfbea', 'ae965074-fe74-4158-8aaf-99f24ee17c3d', 'Oui, je souffre d\'acné hormonale ou kystique persistante.', 'https://www.autocoder.cc/background/zaki_prod/generated/439a5adc568b48069aef9ec4687aca00.png', 3, '{\"aging\": 1, \"sebum\": 3, \"hydration\": -1, \"sensitivity\": 1}', '2026-05-20 14:54:13.000', '2026-05-21 14:54:13.000'),
('e525dc30-a771-46a9-a7c8-930c33e13ee0', '53f1c25f-d8ff-4b0c-8395-538438143037', 'Déshydratation sévère et plaques de sécheresse.', 'https://www.autocoder.cc/background/zaki_prod/generated/0e70228f40194a1ca058f4e1b98de166.png', 4, '{\"aging\": 2, \"sebum\": -2, \"hydration\": -3, \"sensitivity\": 2}', '2026-04-20 14:54:13.000', '2026-04-21 14:54:13.000'),
('ef303f30-c7eb-4d04-a6c9-5dba6fec4af4', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'Non, elle reste plutôt mate ou devient sèche.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/8ba8c0e378194c51922fe5586f221dde.png', 1, '{\"aging\": 1, \"sebum\": -3, \"hydration\": -2, \"sensitivity\": 0}', '2026-05-05 14:54:13.000', '2026-05-06 14:54:13.000'),
('f46a3179-ae6f-475d-94ba-96997ebaee80', '62e3017a-47aa-4ffd-a97b-8141b07cd833', 'Souvent, surtout lors des changements de saison.', 'https://www.autocoder.cc/background/zaki_prod/generated/09feec7028404a64af464ff0487ff024.png', 3, '{\"aging\": 1, \"sebum\": 0, \"hydration\": -1, \"sensitivity\": 2}', '2026-04-15 14:54:13.000', '2026-04-16 14:54:13.000'),
('fa991ca7-5985-40f2-a9b5-964e85d3b402', '7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'Très tendue, sèche au toucher, inconfortable.', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/ef27a7862d814166b1cfd0e05fb92e7e.png', 1, '{\"aging\": 1, \"sebum\": -2, \"hydration\": -3, \"sensitivity\": 1}', '2026-04-10 14:54:13.000', '2026-04-11 14:54:13.000'),
('fbba4c49-1a99-4bdc-9598-223b861f6b14', 'a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'Oui, sur tout le visage très rapidement après le nettoyage.', 'https://www.autocoder.cc/background/zaki_prod/generated/3b28cf6dd6e34b59a811cb30d78e60ee.png', 4, '{\"aging\": -2, \"sebum\": 3, \"hydration\": -1, \"sensitivity\": 1}', '2026-05-05 14:54:13.000', '2026-05-06 14:54:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `skin_quiz_question`
--

CREATE TABLE `skin_quiz_question` (
  `id` varchar(36) NOT NULL,
  `questionText` text NOT NULL,
  `helpText` text DEFAULT NULL,
  `sortOrder` int(11) NOT NULL,
  `status` enum('DRAFT','ACTIVE') NOT NULL DEFAULT 'DRAFT',
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `skin_quiz_question`
--

INSERT INTO `skin_quiz_question` (`id`, `questionText`, `helpText`, `sortOrder`, `status`, `createdAt`, `updatedAt`) VALUES
('14baf5fe-4141-4b60-bd38-20a60aabd0d4', 'Quelle est votre réaction habituelle lors de l\'essai d\'un nouveau produit cosmétique ?', 'Aidez-nous à évaluer le niveau de tolérance de votre peau aux nouvelles formulations.', 6, 'ACTIVE', '2026-05-10 14:54:13.000', '2026-05-31 23:27:18.000'),
('20938c91-4df4-47f8-9c5c-8bdcf3719ae3', 'À quelle fréquence vous exposez-vous au soleil de manière prolongée ?', 'Nous ajusterons nos recommandations de protection solaire selon vos habitudes.', 7, 'DRAFT', '2026-05-15 14:54:13.000', '2026-05-31 23:27:18.000'),
('53f1c25f-d8ff-4b0c-8395-538438143037', 'Quelle est votre préoccupation principale concernant votre peau actuellement ?', 'Sélectionnez l\'objectif prioritaire pour vos soins (ex: hydratation, anti-âge, éclat, acné).', 3, 'ACTIVE', '2026-04-20 14:54:13.000', '2026-05-31 23:27:18.000'),
('62e3017a-47aa-4ffd-a97b-8141b07cd833', 'À quelle fréquence remarquez-vous des rougeurs ou des irritations sur votre visage ?', 'Indiquez si votre peau a tendance à être sensible ou réactive aux éléments extérieurs.', 1, 'ACTIVE', '2026-04-15 14:54:13.000', '2026-05-31 23:27:18.000'),
('7f257aea-6ef4-450e-89b3-5b5b1e06c528', 'Comment décririez-vous la sensation de votre peau au réveil, avant d\'appliquer quoi que ce soit ?', 'Cette question nous aide à déterminer votre type de peau de base (grasse, sèche, mixte ou normale).', 2, 'ACTIVE', '2026-04-10 14:54:13.000', '2026-05-31 23:27:18.000'),
('a8cb68bb-8ea5-4383-917b-e6d1845eba7b', 'Votre peau a-t-elle tendance à briller au cours de la journée, et si oui, dans quelles zones ?', 'Précisez si la brillance se situe sur la zone T ou sur l\'ensemble du visage.', 5, 'ACTIVE', '2026-05-05 14:54:13.000', '2026-05-31 23:27:18.000'),
('ae965074-fe74-4158-8aaf-99f24ee17c3d', 'Avez-vous des antécédents d\'acné sévère ou de problèmes dermatologiques spécifiques ?', 'Ces informations sont cruciales pour éviter de recommander des ingrédients irritants.', 8, 'DRAFT', '2026-05-20 14:54:13.000', '2026-05-31 23:27:18.000'),
('bab6de9b-04e5-4079-b848-571706e1f5a0', 'Combien de produits utilisez-vous habituellement dans votre routine de soins du matin ?', 'Cela nous permet d\'adapter la complexité des recommandations à vos habitudes.', 4, 'ACTIVE', '2026-04-25 14:54:13.000', '2026-05-31 23:27:18.000');

-- --------------------------------------------------------

--
-- Structure de la table `skin_quiz_session`
--

CREATE TABLE `skin_quiz_session` (
  `id` varchar(36) NOT NULL,
  `userId` varchar(36) DEFAULT NULL,
  `status` enum('IN_PROGRESS','COMPLETED') NOT NULL DEFAULT 'IN_PROGRESS',
  `startedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `completedAt` datetime(3) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `skin_quiz_session`
--

INSERT INTO `skin_quiz_session` (`id`, `userId`, `status`, `startedAt`, `completedAt`, `createdAt`, `updatedAt`) VALUES
('0b616535-43be-4248-b1d3-0713ca0307bf', '8452e45c-9951-46da-a734-faa5fc38038d', 'COMPLETED', '2026-05-23 14:54:13.000', '2026-05-23 16:06:13.000', '2026-05-23 14:54:13.000', '2026-05-23 16:06:13.000'),
('11f742f0-65f2-45f5-a5eb-29093e025a71', NULL, 'COMPLETED', '2026-06-08 20:23:21.000', '2026-06-08 20:24:21.000', '2026-06-08 20:23:21.000', '2026-06-08 20:24:21.000'),
('26eadf1b-2ad5-4140-9413-5ffc6bb47785', 'c87f26d6-acee-4b9e-aee2-8ff4b10822ed', 'COMPLETED', '2026-05-10 14:54:13.000', '2026-05-10 16:06:13.000', '2026-05-10 14:54:13.000', '2026-05-10 16:06:13.000'),
('30691b47-2e59-4e1c-8e15-51d682739fa1', '9da44ae2-e893-4737-a635-c99c5a152ca4', 'IN_PROGRESS', '2026-04-30 14:54:13.000', NULL, '2026-04-30 14:54:13.000', '2026-04-30 14:54:13.000'),
('349229b3-c9c2-4844-acac-bcd3592118de', NULL, 'COMPLETED', '2026-06-08 01:00:09.000', '2026-06-08 01:00:29.000', '2026-06-08 01:00:09.000', '2026-06-08 01:00:29.000'),
('36d3dbcd-126b-4207-865e-205bc99b6c7d', 'fcd616eb-143a-41e3-a5aa-b8e611023a2f', 'IN_PROGRESS', '2026-05-24 14:54:13.000', NULL, '2026-05-24 14:54:13.000', '2026-05-24 14:54:13.000'),
('5083ea48-e500-4657-a7b9-1a55ff96eec3', NULL, 'COMPLETED', '2026-06-08 20:31:05.000', '2026-06-08 20:31:53.000', '2026-06-08 20:31:05.000', '2026-06-08 20:31:53.000'),
('66c25fd7-19c1-48e1-bcd2-96511f9d5732', NULL, 'COMPLETED', '2026-06-08 20:49:20.000', '2026-06-08 20:49:37.000', '2026-06-08 20:49:20.000', '2026-06-08 20:49:37.000'),
('8dd29371-c090-4c86-bc7c-c1f12f92be03', '30de1cef-fbd2-4957-9a87-c07243543b24', 'COMPLETED', '2026-05-05 14:54:13.000', '2026-05-05 16:06:13.000', '2026-05-05 14:54:13.000', '2026-05-05 16:06:13.000'),
('93a1e6f7-9f1d-4033-adf7-3dd55757ca10', '2e111f53-8154-4744-aac9-337250541f07', 'COMPLETED', '2026-05-20 14:54:13.000', '2026-05-20 16:06:13.000', '2026-05-20 14:54:13.000', '2026-05-20 16:06:13.000'),
('c217ec41-fe93-4a70-b309-25deb4e4ce7e', NULL, 'COMPLETED', '2026-06-08 20:33:41.000', '2026-06-08 20:34:30.000', '2026-06-08 20:33:41.000', '2026-06-08 20:34:30.000'),
('f42ddba6-dc47-4982-95ef-689c82d44aa6', 'e787014b-a794-48b6-8beb-c50410dd0b6b', 'COMPLETED', '2026-04-20 14:54:13.000', '2026-04-20 16:06:13.000', '2026-04-20 14:54:13.000', '2026-04-20 16:06:13.000'),
('fbf3d696-59c0-4bd9-a2cf-848542f00889', 'd49e3531-a067-4620-9abf-8d98f016f7fc', 'COMPLETED', '2026-04-15 14:54:13.000', '2026-04-15 16:06:13.000', '2026-04-15 14:54:13.000', '2026-04-15 16:06:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `tryon_result`
--

CREATE TABLE `tryon_result` (
  `id` varchar(36) NOT NULL,
  `userId` varchar(36) DEFAULT NULL,
  `lookId` varchar(36) NOT NULL,
  `sourceImageUrl` varchar(700) NOT NULL,
  `usedDemoFace` tinyint(1) NOT NULL DEFAULT 0,
  `demoFaceCode` varchar(60) DEFAULT NULL,
  `resultImageUrl` varchar(700) DEFAULT NULL,
  `beforeAfterJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`beforeAfterJson`)),
  `lookBreakdownJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`lookBreakdownJson`)),
  `status` enum('GENERATED','FAILED') NOT NULL,
  `generatedAt` datetime(3) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tryon_result`
--

INSERT INTO `tryon_result` (`id`, `userId`, `lookId`, `sourceImageUrl`, `usedDemoFace`, `demoFaceCode`, `resultImageUrl`, `beforeAfterJson`, `lookBreakdownJson`, `status`, `generatedAt`, `createdAt`, `updatedAt`) VALUES
('06c24f4b-1cec-42bb-adab-7b27942f0f4c', 'e787014b-a794-48b6-8beb-c50410dd0b6b', 'b4ed122d-fdb0-4064-8d8b-d30807e3eed8', 'https://www.autocoder.cc/background/zaki_prod/generated/6d6e309966614af49ee78f113d6c8c64.png', 1, 'DEMO_F_03', 'https://www.autocoder.cc/background/zaki_prod/generated/716dafb95f014b1186bb7de3523eaf8b.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/0d072c3f9dec4a81b093781408eacbeb.png\", \"beforeUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/65ebb23f8f914a89a839175f8364fbeb.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Fard pêche balayé\", \"lips\": \"Gloss teinté pêche\", \"finish\": \"Lumineux naturel\", \"complexion\": \"Blush crème pêche fondu\"}', 'GENERATED', '2026-05-05 14:56:13.000', '2026-05-05 14:54:13.000', '2026-05-05 14:56:13.000'),
('102c5047-0355-41fa-96c1-8ab078bcd668', '2e111f53-8154-4744-aac9-337250541f07', '35fc61ee-705d-4e8b-a17e-b14a70d134ab', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=800&q=80', 1, 'demo_2', 'https://www.autocoder.cc/background/zaki_prod/generated/1fa6528107c242689152afd7b684c46f.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/1fa6528107c242689152afd7b684c46f.png\", \"beforeUrl\": \"https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=800&q=80\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Fard lilas pastel sur toute la paupière avec une touche de blanc nacré au coin interne.\", \"lips\": \"Rouge à lèvres crème rose tendre.\", \"complexion\": \"Blush rose dragée appliqué en pomme sur les joues.\"}', 'GENERATED', '2026-05-25 23:55:56.000', '2026-05-25 23:55:56.000', '2026-05-25 23:55:56.000'),
('22a92ce3-50e9-4222-b754-acc43d1fff1e', 'fcd616eb-143a-41e3-a5aa-b8e611023a2f', 'bade2457-d713-4be6-b6df-1242a9021229', 'https://www.autocoder.cc/background/zaki_prod/generated/32a0abf3bd2842beb5280da85b4428d1.png', 0, 'NONE', 'https://www.autocoder.cc/background/zaki_prod/generated/26c591401b2e424a91b7fca4441198ca.png', '{\"afterUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/10c9b032f50f49dbabfb922963caf581.png\", \"beforeUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/032229bd4f274aadae83f97803ccc5f6.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Fard neutre et mascara brun\", \"lips\": \"Baume teinté hydratant\", \"finish\": \"Rosé et frais\", \"complexion\": \"Fond de teint léger et lumineux\"}', 'GENERATED', '2026-04-10 14:56:13.000', '2026-04-10 14:54:13.000', '2026-04-10 14:56:13.000'),
('231f8251-bc72-44f0-98d0-8c4f8ce4dcfa', '8452e45c-9951-46da-a734-faa5fc38038d', 'bade2457-d713-4be6-b6df-1242a9021229', 'https://www.autocoder.cc/background/zaki_prod/generated/c7e231c86ba64520a676685df751960a.png', 0, 'NONE', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/8c110a00cfc64ef8ad0776720c710cde.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/b6cd086616f24d7da1a573cba3c913fb.png\", \"beforeUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/b0774d9b49e94aa5aa1fc431f7c603db.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Fard neutre et mascara brun\", \"lips\": \"Baume teinté hydratant\", \"finish\": \"Rosé et frais\", \"complexion\": \"Fond de teint léger et lumineux\"}', 'GENERATED', '2026-05-22 14:56:13.000', '2026-05-22 14:54:13.000', '2026-05-22 14:56:13.000'),
('2d8ec122-73e3-4374-95a4-7b0bbf84fc67', '9274b05d-2b9a-4018-8408-63346c0bdcfa', '0c1009be-7cf5-4a4d-b065-3eefec70965f', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&q=80', 1, 'demo_1', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/4137704ab31842ada4cc9b511854e861.png', '{\"beforeUrl\":\"https:\\/\\/images.unsplash.com\\/photo-1544005313-94ddf0286df2?w=800&q=80\",\"afterUrl\":\"https:\\/\\/productp.s3.us-west-2.amazonaws.com\\/background\\/zaki_pre\\/generated\\/4137704ab31842ada4cc9b511854e861.png\",\"sliderDefaultPercent\":50}', '{\"complexion\":\"Teint mat et unifi\\u00e9 avec un l\\u00e9ger contouring pour sculpter le visage.\",\"eyes\":\"Smoky eye subtil avec eyeliner noir dramatique et mascara volumisant.\",\"lips\":\"Rouge \\u00e0 l\\u00e8vres liquide mat longue tenue teinte rouge carmin.\"}', 'GENERATED', '2026-06-08 20:48:18.000', '2026-06-08 20:48:18.000', '2026-06-08 20:48:18.000'),
('381ed900-f75a-4086-ad74-b0c1e0ac97c6', 'fcd616eb-143a-41e3-a5aa-b8e611023a2f', 'ab750666-0fdb-4a0e-a11f-8a97493d5e23', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/be0506c09d5c41c197a19532784e2cdb.png', 0, 'NONE', 'https://www.autocoder.cc/background/zaki_prod/generated/238f2772f6a049528792ad4a6d0b845f.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/3e69483750b8434aa18d8dee9cf84bf6.png\", \"beforeUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/7024db20efa148be9246811cc10a3231.png\", \"sliderDefaultPercent\": 40}', '{\"eyes\": \"Fard cuivré irisé\", \"lips\": \"Gloss abricot brillant\", \"finish\": \"Glowy sun-kissed\", \"complexion\": \"Poudre bronzante, highlighter doré\"}', 'GENERATED', '2026-05-19 14:56:13.000', '2026-05-19 14:54:13.000', '2026-05-19 14:56:13.000'),
('3c7cc4bf-f8cd-49e7-ad57-969432a7f844', '8452e45c-9951-46da-a734-faa5fc38038d', '725c71cf-10e4-4322-a3ec-56a54dcb4917', 'https://www.autocoder.cc/background/zaki_prod/generated/c51b12ba44e143118f0068b014887018.png', 0, 'NONE', 'https://www.autocoder.cc/background/zaki_prod/generated/5b879218beee4d04864bf761d46cbb5c.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/7f70828cc75b4c74a175eedb72bdc119.png\", \"beforeUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/d480e514edcf48aea26642bdc78623df.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Eyeliner néon rose fluo\", \"lips\": \"Touche de gloss transparent\", \"finish\": \"Contraste fort\", \"complexion\": \"Teint neutre et frais\"}', 'GENERATED', '2026-04-30 14:56:13.000', '2026-04-30 14:54:13.000', '2026-04-30 14:56:13.000'),
('53f204ec-d8d6-4fa7-b4fe-f8839a786d6c', '30de1cef-fbd2-4957-9a87-c07243543b24', '35fc61ee-705d-4e8b-a17e-b14a70d134ab', 'https://www.autocoder.cc/background/zaki_prod/generated/18fe1c3e0d74451a83b16c8dcb40f034.png', 1, 'DEMO_F_02', 'https://www.autocoder.cc/background/zaki_prod/generated/dd5923d82d744894940da791cf5519f0.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/d41ad849dd8b4b219a851c90b0dc4f69.png\", \"beforeUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/b9d44a7427f24c138d580e089e3a9aef.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Ombres pastel lilas\", \"lips\": \"Rouge à lèvres crème rose tendre\", \"finish\": \"Doux poudré\", \"complexion\": \"Blush rose dragée, teint frais\"}', 'FAILED', NULL, '2026-05-23 14:54:13.000', '2026-05-23 14:59:13.000'),
('86b8dab4-810f-4fa7-b8d6-c0230796b656', NULL, 'b4ed122d-fdb0-4064-8d8b-d30807e3eed8', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&q=80', 1, 'demo_1', '/uploads/result_3894ce8f-6ded-4c8b-8298-1809d1a2998c.jpg', '{\"beforeUrl\":\"https:\\/\\/images.unsplash.com\\/photo-1544005313-94ddf0286df2?w=800&q=80\",\"afterUrl\":\"\\/uploads\\/result_3894ce8f-6ded-4c8b-8298-1809d1a2998c.jpg\",\"sliderDefaultPercent\":50}', '{\"complexion\":\"Blush cr\\u00e8me p\\u00eache fondu sur les pommettes.\",\"eyes\":\"Le m\\u00eame blush ou fard p\\u00eache balay\\u00e9 sur les paupi\\u00e8res, mascara brun l\\u00e9ger.\",\"lips\":\"Gloss teint\\u00e9 p\\u00eache ou rouge \\u00e0 l\\u00e8vres cr\\u00e8me de la m\\u00eame couleur.\"}', 'GENERATED', '2026-06-08 21:10:08.000', '2026-06-08 21:10:08.000', '2026-06-08 21:10:08.000'),
('ad723d88-11ad-418c-a484-071e870050bd', 'c87f26d6-acee-4b9e-aee2-8ff4b10822ed', '0c1009be-7cf5-4a4d-b065-3eefec70965f', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/c5a2523ade2f42a4be9e01c3feeb38c8.png', 0, 'NONE', 'https://www.autocoder.cc/background/zaki_prod/generated/4ca87ac0e7344cfd9e814aa1b3aeef33.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/d4f534b2615147ac916518e75709af60.png\", \"beforeUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/196858a9e5d0418c88baed666df6aefa.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Smoky eyes noir et gris, faux cils\", \"lips\": \"Rouge à lèvres liquide rouge intense\", \"finish\": \"Velours poudré\", \"complexion\": \"Teint mat couvrant, contouring structuré\"}', 'GENERATED', '2026-03-26 14:56:13.000', '2026-03-26 14:54:13.000', '2026-03-26 14:56:13.000'),
('baba36ec-eea8-4fef-b82b-616fe42bac27', '9da44ae2-e893-4737-a635-c99c5a152ca4', 'b9358f0a-48db-4197-a6eb-c21d41e3fee6', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_test/generated/337bacf1e32f40dc96c1f65a2446fc75.png', 0, 'NONE', 'https://www.autocoder.cc/background/zaki_prod/generated/8dcfa0f08d584c0ab0fad83af6c389b5.png', '{\"afterUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/047b9a410a754c00a5f4e0b2d204fc2c.png\", \"beforeUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/7b5df303ffdd42f1aadf57652835bc09.png\", \"sliderDefaultPercent\": 60}', '{\"eyes\": \"Liner argenté métallique\", \"lips\": \"Baume incolore mat\", \"finish\": \"Métallique froid\", \"complexion\": \"Teint zéro défaut ultra mat\"}', 'GENERATED', '2026-05-15 14:56:13.000', '2026-05-15 14:54:13.000', '2026-05-15 14:56:13.000'),
('bbbf73c2-b982-4ea8-8fca-78e76cba498c', '2e111f53-8154-4744-aac9-337250541f07', '0ae5f57e-9803-4157-91f8-d5f9217b4dae', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&q=80', 1, 'demo_1', 'https://www.autocoder.cc/background/zaki_prod/generated/950acc1993ca4579b91342fdfbf5c2f2.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/950acc1993ca4579b91342fdfbf5c2f2.png\", \"beforeUrl\": \"https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&q=80\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Eyeliner liquide noir étiré en virgule épaisse, cils recourbés à l\'extrême.\", \"lips\": \"Rouge à lèvres nude beige à fini crémeux.\", \"complexion\": \"Teint satiné avec un blush pêche très discret pour ne pas voler la vedette aux yeux.\"}', 'GENERATED', '2026-05-25 23:55:42.000', '2026-05-25 23:55:42.000', '2026-05-25 23:55:42.000'),
('c0e779c8-1ef7-4ef3-8a9f-5dc3955ff768', '2e111f53-8154-4744-aac9-337250541f07', '8669596c-8580-40fd-a5c7-4fb31071d6bd', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/7d56209ce250476e8bdb70f40ed41bad.png', 1, 'DEMO_F_04', 'https://www.autocoder.cc/background/zaki_prod/generated/4ebcfd442cdb4cff8a509f57ee45814b.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/b3c01dcb971f48408a00d7ff50a47a8e.png\", \"beforeUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/b8838c553e514a84bacc9b16353a5d95.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Eyeliner liquide noir classique\", \"lips\": \"Rouge à lèvres rouge vif semi-mat\", \"finish\": \"Velours poudré rétro\", \"complexion\": \"Teint velouté poudre libre\"}', 'FAILED', NULL, '2026-04-27 14:54:13.000', '2026-04-27 14:59:13.000'),
('c76493a8-728b-4c4c-a076-1d1a17d25caf', 'c87f26d6-acee-4b9e-aee2-8ff4b10822ed', '0ae5f57e-9803-4157-91f8-d5f9217b4dae', 'https://www.autocoder.cc/background/zaki_prod/generated/592b1fe2514a418f950c4652237101a9.png', 0, 'NONE', 'https://www.autocoder.cc/background/zaki_prod/generated/1ae2eb4ff7ca48089f2abd5d72868dd9.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/df278ada7f5a499bb181d215b7379101.png\", \"beforeUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/585b531e07b04b689ee8f5ef067cd9ae.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Eyeliner graphique noir intense\", \"lips\": \"Lèvres nude transparentes\", \"finish\": \"Satiné moderne\", \"complexion\": \"Teint unifié naturel\"}', 'GENERATED', '2026-05-18 14:56:13.000', '2026-05-18 14:54:13.000', '2026-05-18 14:56:13.000'),
('c9784d7e-e1b7-4237-8a4d-7d4f8478b4c9', 'd49e3531-a067-4620-9abf-8d98f016f7fc', '4d35a094-209b-4e22-8db4-862ec03e0cd7', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/883e88bed8cf4c5a8eae841b27208133.png', 0, 'NONE', 'https://www.autocoder.cc/background/zaki_prod/generated/d75111468a544a9db99c5d9c5cee359e.png', '{\"afterUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/4ff0ff84bced415aa7b8b756cd444af2.png\", \"beforeUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/0aea39d202c74c499170479e7f84ed57.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Dégradé orange brûlé\", \"lips\": \"Rouge à lèvres brique\", \"finish\": \"Satiné velours\", \"complexion\": \"Blush terracotta chaud\"}', 'GENERATED', '2026-05-10 14:56:13.000', '2026-05-10 14:54:13.000', '2026-05-10 14:56:13.000'),
('c9a163ad-5b3d-40f1-af67-3eda5292061c', '2e111f53-8154-4744-aac9-337250541f07', '0c1009be-7cf5-4a4d-b065-3eefec70965f', 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_dev/generated/32d94edd4364444f9cd3d7c259dd04b1.png', 1, 'DEMO_F_01', 'https://www.autocoder.cc/background/zaki_prod/generated/b49eedfb10fa4d958b15530c21c20f26.png', '{\"afterUrl\": \"https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/0f3c483ae49046a397241b958cac2353.png\", \"beforeUrl\": \"https://www.autocoder.cc/background/zaki_prod/generated/daeb8dcd3c8e46b6a3fd4cdf994b0192.png\", \"sliderDefaultPercent\": 50}', '{\"eyes\": \"Smoky eyes noir et gris, faux cils\", \"lips\": \"Rouge à lèvres liquide rouge intense\", \"finish\": \"Velours poudré\", \"complexion\": \"Teint mat couvrant, contouring structuré\"}', 'GENERATED', '2026-05-20 14:56:13.000', '2026-05-20 14:54:13.000', '2026-05-20 14:56:13.000');

-- --------------------------------------------------------

--
-- Structure de la table `tryon_result_product`
--

CREATE TABLE `tryon_result_product` (
  `id` varchar(36) NOT NULL,
  `tryonResultId` varchar(36) NOT NULL,
  `productId` varchar(36) NOT NULL,
  `faceZone` varchar(30) DEFAULT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tryon_result_product`
--

INSERT INTO `tryon_result_product` (`id`, `tryonResultId`, `productId`, `faceZone`, `sortOrder`) VALUES
('08cfc125-151d-492c-8a2d-bbbf10ebadc2', 'c9a163ad-5b3d-40f1-af67-3eda5292061c', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 2),
('0a3be8ba-4f0f-471e-86a6-cc7cbc8d234f', 'c76493a8-728b-4c4c-a076-1d1a17d25caf', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 1),
('0b527797-8276-4b6b-9110-9b1f1af051c0', 'c9784d7e-e1b7-4237-8a4d-7d4f8478b4c9', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('132a5e0a-7844-4eff-9fd3-550ac364a452', 'c9784d7e-e1b7-4237-8a4d-7d4f8478b4c9', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 2),
('1a1184f4-84cc-4b7d-9558-eb4149162b67', '381ed900-f75a-4086-ad74-b0c1e0ac97c6', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('20a6a496-202a-4a42-a00e-b6cd48c3856a', '06c24f4b-1cec-42bb-adab-7b27942f0f4c', '43e9bd15-f5c8-413c-b732-18ba14fa4f97', 'complexion', 2),
('311ceaf8-6c26-4e6a-9b6e-e90f0a5f9697', 'c0e779c8-1ef7-4ef3-8a9f-5dc3955ff768', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 2),
('35776979-a54f-47c2-a619-45ee8ae08b6c', 'c0e779c8-1ef7-4ef3-8a9f-5dc3955ff768', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('37405fe4-8101-499c-89c2-510d5965c772', '2d8ec122-73e3-4374-95a4-7b0bbf84fc67', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 3),
('394f8ad3-8892-42f1-8211-ca728f46bfd5', 'bbbf73c2-b982-4ea8-8fca-78e76cba498c', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 3),
('46356582-7c53-4b28-bca9-5d58b44978b6', '102c5047-0355-41fa-96c1-8ab078bcd668', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 2),
('483b5c94-463b-4b3d-b3c8-c9b13531773d', '231f8251-bc72-44f0-98d0-8c4f8ce4dcfa', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('69694548-b189-4c45-a8c4-b6bc25878d17', 'bbbf73c2-b982-4ea8-8fca-78e76cba498c', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 2),
('6bb6067f-29ef-4376-905b-69119af6dae4', 'baba36ec-eea8-4fef-b82b-616fe42bac27', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('72688b57-9898-4df4-98b3-562fb718880e', 'bbbf73c2-b982-4ea8-8fca-78e76cba498c', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 1),
('7cd70774-b712-408d-83c7-9aab81a421c4', 'c76493a8-728b-4c4c-a076-1d1a17d25caf', '43e9bd15-f5c8-413c-b732-18ba14fa4f97', 'complexion', 2),
('87831459-b029-478d-97a6-dddd2f6fb7e2', 'ad723d88-11ad-418c-a484-071e870050bd', '43e9bd15-f5c8-413c-b732-18ba14fa4f97', 'complexion', 3),
('8b70eb70-4591-4c00-8ff3-257be8fba70e', '86b8dab4-810f-4fa7-b8d6-c0230796b656', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 3),
('8d81cb0f-2016-4453-88ad-16f9d827e808', '231f8251-bc72-44f0-98d0-8c4f8ce4dcfa', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 2),
('92757e14-2bb2-4cce-ac30-b6ec051bc86e', '2d8ec122-73e3-4374-95a4-7b0bbf84fc67', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 2),
('9336918f-a490-495c-a35b-100ce118792e', '86b8dab4-810f-4fa7-b8d6-c0230796b656', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 2),
('a4199266-6d31-46b5-ac3c-30474ad21c8f', '231f8251-bc72-44f0-98d0-8c4f8ce4dcfa', '43e9bd15-f5c8-413c-b732-18ba14fa4f97', 'complexion', 3),
('a826613b-5c6e-45cc-9411-03728c54bbed', '22a92ce3-50e9-4222-b754-acc43d1fff1e', '43e9bd15-f5c8-413c-b732-18ba14fa4f97', 'complexion', 1),
('b061e189-6fb0-494f-8b52-9bd06966c2fb', '53f204ec-d8d6-4fa7-b4fe-f8839a786d6c', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('bfa14dab-9d60-4559-a3be-44eab88fdea5', 'ad723d88-11ad-418c-a484-071e870050bd', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 2),
('c24b5bbe-3410-4e69-9fa8-fc11ecb26666', '102c5047-0355-41fa-96c1-8ab078bcd668', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 1),
('c661679f-e6ac-4e85-9381-791e21e9db34', '3c7cc4bf-f8cd-49e7-ad57-969432a7f844', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 1),
('c977a16a-0ddf-4779-a8ae-e416d171338e', '53f204ec-d8d6-4fa7-b4fe-f8839a786d6c', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 2),
('e6e02e7f-a3eb-46e8-b350-2cd5023f7d1b', '2d8ec122-73e3-4374-95a4-7b0bbf84fc67', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 1),
('eba9ec6f-2290-484a-abef-7a092137664e', 'c9a163ad-5b3d-40f1-af67-3eda5292061c', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('f1f25472-0245-4d29-8c83-25a04fb71d50', 'ad723d88-11ad-418c-a484-071e870050bd', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('f4eda5f6-1ce2-4182-b8fc-ae089bca363a', '86b8dab4-810f-4fa7-b8d6-c0230796b656', '966d9c23-1234-4020-baf3-d0e306a25d2e', 'complexion', 1),
('f538abb9-8666-4e98-9780-3eede80b3f55', '06c24f4b-1cec-42bb-adab-7b27942f0f4c', '2043db86-f412-404d-98f5-0ac698706f06', 'eyes', 1),
('f929e308-0bad-486b-8519-92aef8bd8112', '102c5047-0355-41fa-96c1-8ab078bcd668', 'f2546299-04ad-4979-a552-3b050c43c30f', 'eyes', 3);

-- --------------------------------------------------------

--
-- Structure de la table `_prisma_migrations`
--

CREATE TABLE `_prisma_migrations` (
  `id` varchar(36) NOT NULL,
  `checksum` varchar(64) NOT NULL,
  `finished_at` datetime(3) DEFAULT NULL,
  `migration_name` varchar(255) NOT NULL,
  `logs` text DEFAULT NULL,
  `rolled_back_at` datetime(3) DEFAULT NULL,
  `started_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `applied_steps_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `_prisma_migrations`
--

INSERT INTO `_prisma_migrations` (`id`, `checksum`, `finished_at`, `migration_name`, `logs`, `rolled_back_at`, `started_at`, `applied_steps_count`) VALUES
('d8c8de57-47c3-4f0d-8e3b-de64bb67bf9c', '2e6bdb9d56b3b6bac5eddafc74efaf85ee5da301f9804671b0ed51af6895e2be', '2026-05-25 14:50:56.000', '20260525145048_init', NULL, NULL, '2026-05-25 14:50:49.000', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_account_key` (`account`),
  ADD UNIQUE KEY `account_email_key` (`email`);

--
-- Index pour la table `ai_look`
--
ALTER TABLE `ai_look`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ai_look_slug_key` (`slug`);

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `article_slug_key` (`slug`),
  ADD KEY `article_categoryId_fkey` (`categoryId`),
  ADD KEY `article_authorId_fkey` (`authorId`);

--
-- Index pour la table `article_product`
--
ALTER TABLE `article_product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `article_product_articleId_productId_key` (`articleId`,`productId`),
  ADD KEY `article_product_productId_fkey` (`productId`);

--
-- Index pour la table `blog_category`
--
ALTER TABLE `blog_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_category_name_key` (`name`),
  ADD UNIQUE KEY `blog_category_slug_key` (`slug`);

--
-- Index pour la table `contact_request`
--
ALTER TABLE `contact_request`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `diagnostic_recommendation`
--
ALTER TABLE `diagnostic_recommendation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `diagnostic_recommendation_diagnosticResultId_productId_key` (`diagnosticResultId`,`productId`),
  ADD KEY `diagnostic_recommendation_productId_fkey` (`productId`);

--
-- Index pour la table `diagnostic_result`
--
ALTER TABLE `diagnostic_result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diagnostic_result_userId_fkey` (`userId`),
  ADD KEY `diagnostic_result_sessionId_fkey` (`sessionId`);

--
-- Index pour la table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `favorite_userId_targetType_status_idx` (`userId`,`targetType`,`status`),
  ADD KEY `favorite_productId_fkey` (`productId`),
  ADD KEY `favorite_lookId_fkey` (`lookId`);

--
-- Index pour la table `ingredient`
--
ALTER TABLE `ingredient`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ingredient_name_key` (`name`);

--
-- Index pour la table `look_product`
--
ALTER TABLE `look_product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `look_product_lookId_productId_faceZone_key` (`lookId`,`productId`,`faceZone`),
  ADD KEY `look_product_productId_fkey` (`productId`);

--
-- Index pour la table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_slug_key` (`slug`),
  ADD KEY `product_categoryId_fkey` (`categoryId`);

--
-- Index pour la table `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_category_name_key` (`name`),
  ADD UNIQUE KEY `product_category_slug_key` (`slug`);

--
-- Index pour la table `product_ingredient`
--
ALTER TABLE `product_ingredient`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_ingredient_productId_ingredientId_key` (`productId`,`ingredientId`),
  ADD KEY `product_ingredient_ingredientId_fkey` (`ingredientId`);

--
-- Index pour la table `product_relation`
--
ALTER TABLE `product_relation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_relation_fromProductId_toProductId_relationType_key` (`fromProductId`,`toProductId`,`relationType`),
  ADD KEY `product_relation_toProductId_fkey` (`toProductId`);

--
-- Index pour la table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_role_permissionKey_key` (`role`,`permissionKey`);

--
-- Index pour la table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `skin_quiz_answer`
--
ALTER TABLE `skin_quiz_answer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `skin_quiz_answer_sessionId_questionId_key` (`sessionId`,`questionId`),
  ADD KEY `skin_quiz_answer_questionId_fkey` (`questionId`),
  ADD KEY `skin_quiz_answer_optionId_fkey` (`optionId`);

--
-- Index pour la table `skin_quiz_option`
--
ALTER TABLE `skin_quiz_option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `skin_quiz_option_questionId_fkey` (`questionId`);

--
-- Index pour la table `skin_quiz_question`
--
ALTER TABLE `skin_quiz_question`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `skin_quiz_session`
--
ALTER TABLE `skin_quiz_session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `skin_quiz_session_userId_fkey` (`userId`);

--
-- Index pour la table `tryon_result`
--
ALTER TABLE `tryon_result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tryon_result_userId_fkey` (`userId`),
  ADD KEY `tryon_result_lookId_fkey` (`lookId`);

--
-- Index pour la table `tryon_result_product`
--
ALTER TABLE `tryon_result_product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tryon_result_product_tryonResultId_productId_faceZone_key` (`tryonResultId`,`productId`,`faceZone`),
  ADD KEY `tryon_result_product_productId_fkey` (`productId`);

--
-- Index pour la table `_prisma_migrations`
--
ALTER TABLE `_prisma_migrations`
  ADD PRIMARY KEY (`id`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_authorId_fkey` FOREIGN KEY (`authorId`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `article_categoryId_fkey` FOREIGN KEY (`categoryId`) REFERENCES `blog_category` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `article_product`
--
ALTER TABLE `article_product`
  ADD CONSTRAINT `article_product_articleId_fkey` FOREIGN KEY (`articleId`) REFERENCES `article` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `article_product_productId_fkey` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `diagnostic_recommendation`
--
ALTER TABLE `diagnostic_recommendation`
  ADD CONSTRAINT `diagnostic_recommendation_diagnosticResultId_fkey` FOREIGN KEY (`diagnosticResultId`) REFERENCES `diagnostic_result` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_recommendation_productId_fkey` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `diagnostic_result`
--
ALTER TABLE `diagnostic_result`
  ADD CONSTRAINT `diagnostic_result_sessionId_fkey` FOREIGN KEY (`sessionId`) REFERENCES `skin_quiz_session` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_result_userId_fkey` FOREIGN KEY (`userId`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `favorite_lookId_fkey` FOREIGN KEY (`lookId`) REFERENCES `ai_look` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `favorite_productId_fkey` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `favorite_userId_fkey` FOREIGN KEY (`userId`) REFERENCES `account` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `look_product`
--
ALTER TABLE `look_product`
  ADD CONSTRAINT `look_product_lookId_fkey` FOREIGN KEY (`lookId`) REFERENCES `ai_look` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `look_product_productId_fkey` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_categoryId_fkey` FOREIGN KEY (`categoryId`) REFERENCES `product_category` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `product_ingredient`
--
ALTER TABLE `product_ingredient`
  ADD CONSTRAINT `product_ingredient_ingredientId_fkey` FOREIGN KEY (`ingredientId`) REFERENCES `ingredient` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `product_ingredient_productId_fkey` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `product_relation`
--
ALTER TABLE `product_relation`
  ADD CONSTRAINT `product_relation_fromProductId_fkey` FOREIGN KEY (`fromProductId`) REFERENCES `product` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `product_relation_toProductId_fkey` FOREIGN KEY (`toProductId`) REFERENCES `product` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `skin_quiz_answer`
--
ALTER TABLE `skin_quiz_answer`
  ADD CONSTRAINT `skin_quiz_answer_optionId_fkey` FOREIGN KEY (`optionId`) REFERENCES `skin_quiz_option` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `skin_quiz_answer_questionId_fkey` FOREIGN KEY (`questionId`) REFERENCES `skin_quiz_question` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `skin_quiz_answer_sessionId_fkey` FOREIGN KEY (`sessionId`) REFERENCES `skin_quiz_session` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `skin_quiz_option`
--
ALTER TABLE `skin_quiz_option`
  ADD CONSTRAINT `skin_quiz_option_questionId_fkey` FOREIGN KEY (`questionId`) REFERENCES `skin_quiz_question` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `skin_quiz_session`
--
ALTER TABLE `skin_quiz_session`
  ADD CONSTRAINT `skin_quiz_session_userId_fkey` FOREIGN KEY (`userId`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `tryon_result`
--
ALTER TABLE `tryon_result`
  ADD CONSTRAINT `tryon_result_lookId_fkey` FOREIGN KEY (`lookId`) REFERENCES `ai_look` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tryon_result_userId_fkey` FOREIGN KEY (`userId`) REFERENCES `account` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `tryon_result_product`
--
ALTER TABLE `tryon_result_product`
  ADD CONSTRAINT `tryon_result_product_productId_fkey` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tryon_result_product_tryonResultId_fkey` FOREIGN KEY (`tryonResultId`) REFERENCES `tryon_result` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
