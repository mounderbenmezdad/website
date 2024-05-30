-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  jeu. 30 mai 2024 à 22:25
-- Version du serveur :  10.1.36-MariaDB
-- Version de PHP :  7.0.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `memoire`
--

-- --------------------------------------------------------

--
-- Structure de la table `chefservice`
--

CREATE TABLE `chefservice` (
  `id` varchar(15) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `familyName` varchar(255) DEFAULT NULL,
  `date_de_naissance` date DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `chefservice`
--

INSERT INTO `chefservice` (`id`, `name`, `familyName`, `date_de_naissance`, `adresse`, `email`, `telephone`, `password`) VALUES
('2003', 'hocine', 'chafai', '2003-07-10', 'constantine', 'houcinechafai3@gmail.com', '0657451106', 'hc');

-- --------------------------------------------------------

--
-- Structure de la table `dossiermedical`
--

CREATE TABLE `dossiermedical` (
  `ID_Dossier` int(11) NOT NULL,
  `idpatient` varchar(15) DEFAULT NULL,
  `idmedecin` varchar(15) DEFAULT NULL,
  `infirmierid` varchar(15) DEFAULT NULL,
  `antecedents` text,
  `stade` varchar(15) DEFAULT NULL,
  `Notes` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `formulaireinfirmier`
--

CREATE TABLE `formulaireinfirmier` (
  `IDFormulaireInfirmier` int(11) NOT NULL,
  `IDPatient` varchar(15) DEFAULT NULL,
  `Date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Temperature` decimal(5,2) DEFAULT NULL,
  `FrequenceCardiaque` int(11) DEFAULT NULL,
  `PressionArterielle` varchar(20) DEFAULT NULL,
  `Symptomes` text,
  `MedicamentsAdministres` text,
  `Alimentation` text,
  `Elimination` text,
  `ActivitePhysique` text,
  `Hydratation` text,
  `Douleur` text,
  `ObservationsSupplementaires` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `formulairemedecin`
--

CREATE TABLE `formulairemedecin` (
  `IDFormulaireMedecin` int(11) NOT NULL,
  `IDPatient` varchar(15) DEFAULT NULL,
  `DateExamen` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `NomMedecinTraitant` varchar(255) DEFAULT NULL,
  `TypeCancer` varchar(100) DEFAULT NULL,
  `StadeCancer` varchar(50) DEFAULT NULL,
  `BiomarqueursTumoraux` text,
  `Biopsie` text,
  `TraitementsAnterieurs` text,
  `TraitementActuel` text,
  `EffetsSecondaires` text,
  `Symptomes` text,
  `ResultatsExamensMedicaux` text,
  `EvaluationEtatGeneral` text,
  `PlanTraitementFutur` text,
  `RecommandationsProchainRdv` text,
  `poids` int(11) NOT NULL,
  `taille` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `infirmiers`
--

CREATE TABLE `infirmiers` (
  `id` varchar(15) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `familyName` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `phoneNumber` int(10) DEFAULT NULL,
  `bd` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sex` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `medecins`
--

CREATE TABLE `medecins` (
  `id` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `familyName` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `bd` date NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `position` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sex` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `medicament`
--

CREATE TABLE `medicament` (
  `examen_id` int(11) NOT NULL,
  `medicamentName` varchar(255) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `frequence` varchar(100) NOT NULL,
  `duree` int(11) NOT NULL,
  `effetSecondaire` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender` varchar(15) DEFAULT NULL,
  `reciver` varchar(15) DEFAULT NULL,
  `message` text,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `patient`
--

CREATE TABLE `patient` (
  `id` varchar(15) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `familyName` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `bd` date DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `position` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sex` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `patientsalle`
--

CREATE TABLE `patientsalle` (
  `id` int(11) NOT NULL,
  `id_patient` varchar(15) DEFAULT NULL,
  `id_salle` varchar(15) DEFAULT NULL,
  `id_medecin` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `rendezvous`
--

CREATE TABLE `rendezvous` (
  `id` int(11) NOT NULL,
  `patientid` varchar(15) DEFAULT NULL,
  `doctorid` varchar(15) DEFAULT NULL,
  `date` datetime NOT NULL,
  `sujet` text,
  `sender` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `salles`
--

CREATE TABLE `salles` (
  `id` varchar(15) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `type_salle` enum('salle de soins','examen','chirurgie','rendez-vous','imagerie','attenteexam','attenteimag','attenterdv') NOT NULL,
  `capacite` int(11) NOT NULL,
  `nbrpatients` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` varchar(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `familyName` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `bd` date DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `position` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sex` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `chefservice`
--
ALTER TABLE `chefservice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `dossiermedical`
--
ALTER TABLE `dossiermedical`
  ADD PRIMARY KEY (`ID_Dossier`),
  ADD KEY `dossiermedical_ibfk_1` (`idpatient`),
  ADD KEY `dossiermedical_ibfk_2` (`idmedecin`),
  ADD KEY `dossiermedical_ibfk_3` (`infirmierid`);

--
-- Index pour la table `formulaireinfirmier`
--
ALTER TABLE `formulaireinfirmier`
  ADD PRIMARY KEY (`IDFormulaireInfirmier`),
  ADD KEY `IDPatient` (`IDPatient`);

--
-- Index pour la table `formulairemedecin`
--
ALTER TABLE `formulairemedecin`
  ADD PRIMARY KEY (`IDFormulaireMedecin`),
  ADD KEY `IDPatient` (`IDPatient`);

--
-- Index pour la table `infirmiers`
--
ALTER TABLE `infirmiers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `medecins`
--
ALTER TABLE `medecins`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `medicament`
--
ALTER TABLE `medicament`
  ADD PRIMARY KEY (`examen_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phoneNumber` (`phoneNumber`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `patientsalle`
--
ALTER TABLE `patientsalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patientsalle_ibfk_1` (`id_patient`),
  ADD KEY `patientsalle_ibfk_2` (`id_salle`);

--
-- Index pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rendezvous_ibfk_1` (`patientid`),
  ADD KEY `rendezvous_ibfk_2` (`doctorid`);

--
-- Index pour la table `salles`
--
ALTER TABLE `salles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phoneNumber` (`phoneNumber`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `dossiermedical`
--
ALTER TABLE `dossiermedical`
  MODIFY `ID_Dossier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `formulaireinfirmier`
--
ALTER TABLE `formulaireinfirmier`
  MODIFY `IDFormulaireInfirmier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `formulairemedecin`
--
ALTER TABLE `formulairemedecin`
  MODIFY `IDFormulaireMedecin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `medicament`
--
ALTER TABLE `medicament`
  MODIFY `examen_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `patientsalle`
--
ALTER TABLE `patientsalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `dossiermedical`
--
ALTER TABLE `dossiermedical`
  ADD CONSTRAINT `dossiermedical_ibfk_1` FOREIGN KEY (`idpatient`) REFERENCES `patient` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dossiermedical_ibfk_2` FOREIGN KEY (`idmedecin`) REFERENCES `medecins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `dossiermedical_ibfk_3` FOREIGN KEY (`infirmierid`) REFERENCES `infirmiers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `formulaireinfirmier`
--
ALTER TABLE `formulaireinfirmier`
  ADD CONSTRAINT `formulaireinfirmier_ibfk_1` FOREIGN KEY (`IDPatient`) REFERENCES `patient` (`id`);

--
-- Contraintes pour la table `formulairemedecin`
--
ALTER TABLE `formulairemedecin`
  ADD CONSTRAINT `formulairemedecin_ibfk_1` FOREIGN KEY (`IDPatient`) REFERENCES `patient` (`id`);

--
-- Contraintes pour la table `medicament`
--
ALTER TABLE `medicament`
  ADD CONSTRAINT `medicament_ibfk_1` FOREIGN KEY (`examen_id`) REFERENCES `formulairemedecin` (`IDFormulaireMedecin`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `patientsalle`
--
ALTER TABLE `patientsalle`
  ADD CONSTRAINT `patientsalle_ibfk_1` FOREIGN KEY (`id_patient`) REFERENCES `patient` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `patientsalle_ibfk_2` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  ADD CONSTRAINT `rendezvous_ibfk_1` FOREIGN KEY (`patientid`) REFERENCES `patient` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rendezvous_ibfk_2` FOREIGN KEY (`doctorid`) REFERENCES `medecins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
