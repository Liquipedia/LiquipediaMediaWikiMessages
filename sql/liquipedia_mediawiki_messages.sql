CREATE TABLE IF NOT EXISTS `liquipedia_mediawiki_messages` (
`id` int(11) NOT NULL,
  `messagename` varchar(255) NOT NULL,
  `messagevalue` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `liquipedia_mediawiki_messages`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `messagename` (`messagename`);

ALTER TABLE `liquipedia_mediawiki_messages`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
