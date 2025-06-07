-- Base tables (no dependencies)
CREATE TABLE `countries` (
  `id` CHAR(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(2) NOT NULL,
  `official_name` varchar(255) DEFAULT NULL,
  `native_name` varchar(255) DEFAULT NULL,
  `continent` varchar(255) DEFAULT NULL,
  `subregion` varchar(255) DEFAULT NULL,
  `emoji` varchar(10) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_code_unique` (`code`)
);

CREATE TABLE `sources` (
  `id` CHAR(36) NOT NULL,
  `source` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sources_source_unique` (`source`)
);

CREATE TABLE `statuses` (
  `id` CHAR(36) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `statuses_status_unique` (`status`)
);

CREATE TABLE `users` (
  `id` CHAR(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','super user','user') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `bio` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` bigint(20) unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
);

CREATE TABLE `genres` (
  `id` CHAR(36) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `genres_genre_unique` (`genre`)
);

-- Tables with single-level dependencies
CREATE TABLE `artists` (
  `id` CHAR(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `country_id` CHAR(36) DEFAULT NULL,
  `formed_year` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `source_id` CHAR(36) DEFAULT NULL,
  `status_id` CHAR(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artists_country_id_foreign` (`country_id`),
  KEY `artists_source_id_foreign` (`source_id`),
  KEY `artists_status_id_foreign` (`status_id`),
  CONSTRAINT `artists_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `artists_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`) ON DELETE SET NULL,
  CONSTRAINT `artists_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE SET NULL
);

CREATE TABLE `locations` (
  `id` CHAR(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `street` varchar(255) DEFAULT NULL,
  `house_number` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country_id` CHAR(36) NOT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `source_id` CHAR(36) NOT NULL,
  `status_id` CHAR(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `locations_country_id_foreign` (`country_id`),
  KEY `locations_source_id_foreign` (`source_id`),
  KEY `locations_status_id_foreign` (`status_id`),
  CONSTRAINT `locations_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `locations_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`),
  CONSTRAINT `locations_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`)
);

CREATE TABLE `events` (
  `id` CHAR(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('concert','festival','tour','clubnight','other') NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `source_id` CHAR(36) DEFAULT NULL,
  `status_id` CHAR(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_source_id_foreign` (`source_id`),
  KEY `events_status_id_foreign` (`status_id`),
  CONSTRAINT `events_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE SET NULL
);

-- Tables with multi-level dependencies
CREATE TABLE `artist_genres` (
  `id` CHAR(36) NOT NULL,
  `artist_id` CHAR(36) NOT NULL,
  `genre_id` CHAR(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artist_genres_artist_id_foreign` (`artist_id`),
  KEY `artist_genres_genre_id_foreign` (`genre_id`),
  CONSTRAINT `artist_genres_artist_id_foreign` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`),
  CONSTRAINT `artist_genres_genre_id_foreign` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`)
);

CREATE TABLE `concerts` (
  `id` CHAR(36) NOT NULL,
  `event_id` CHAR(36) NOT NULL,
  `location_id` CHAR(36) NOT NULL,
  `date` date NOT NULL,
  `source_id` CHAR(36) NOT NULL,
  `status_id` CHAR(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `concerts_event_id_foreign` (`event_id`),
  KEY `concerts_location_id_foreign` (`location_id`),
  KEY `concerts_source_id_foreign` (`source_id`),
  KEY `concerts_status_id_foreign` (`status_id`),
  CONSTRAINT `concerts_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  CONSTRAINT `concerts_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `concerts_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`),
  CONSTRAINT `concerts_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`)
);

CREATE TABLE `checkins` (
  `id` CHAR(36) NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `concert_id` CHAR(36) NOT NULL,
  `user_id` CHAR(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checkins_concert_id_foreign` (`concert_id`),
  KEY `checkins_user_id_foreign` (`user_id`),
  CONSTRAINT `checkins_concert_id_foreign` FOREIGN KEY (`concert_id`) REFERENCES `concerts` (`id`),
  CONSTRAINT `checkins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `checkins_rating_check` CHECK (`rating` is null or `rating` >= 0.5 and `rating` <= 5.0 and `rating` * 10 MOD 5 = 0)
);

-- Tables that depend on checkins
CREATE TABLE `checkin_photos` (
  `id` CHAR(36) NOT NULL,
  `checkin_id` CHAR(36) NOT NULL,
  `url` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checkin_photos_checkin_id_foreign` (`checkin_id`),
  CONSTRAINT `checkin_photos_checkin_id_foreign` FOREIGN KEY (`checkin_id`) REFERENCES `checkins` (`id`) ON DELETE CASCADE
);

CREATE TABLE `checkin_comments` (
  `id` CHAR(36) NOT NULL,
  `checkin_id` CHAR(36) NOT NULL,
  `user_id` CHAR(36) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checkin_comments_checkin_id_foreign` (`checkin_id`),
  KEY `checkin_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `checkin_comments_checkin_id_foreign` FOREIGN KEY (`checkin_id`) REFERENCES `checkins` (`id`),
  CONSTRAINT `checkin_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);

CREATE TABLE `checkin_likes` (
  `id` CHAR(36) NOT NULL,
  `checkin_id` CHAR(36) NOT NULL,
  `user_id` CHAR(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checkin_likes_checkin_id_user_id_unique` (`checkin_id`,`user_id`),
  KEY `checkin_likes_user_id_foreign` (`user_id`),
  CONSTRAINT `checkin_likes_checkin_id_foreign` FOREIGN KEY (`checkin_id`) REFERENCES `checkins` (`id`),
  CONSTRAINT `checkin_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);

-- Junction tables and final dependencies
CREATE TABLE `artist_checkins` (
  `id` CHAR(36) NOT NULL,
  `artist_id` CHAR(36) NOT NULL,
  `checkin_id` CHAR(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artist_checkins_artist_id_foreign` (`artist_id`),
  KEY `artist_checkins_checkin_id_foreign` (`checkin_id`),
  CONSTRAINT `artist_checkins_artist_id_foreign` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`),
  CONSTRAINT `artist_checkins_checkin_id_foreign` FOREIGN KEY (`checkin_id`) REFERENCES `checkins` (`id`)
);

CREATE TABLE `artist_concerts` (
  `id` CHAR(36) NOT NULL,
  `concert_id` CHAR(36) NOT NULL,
  `artist_id` CHAR(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artist_concerts_concert_id_foreign` (`concert_id`),
  KEY `artist_concerts_artist_id_foreign` (`artist_id`),
  CONSTRAINT `artist_concerts_artist_id_foreign` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `artist_concerts_concert_id_foreign` FOREIGN KEY (`concert_id`) REFERENCES `concerts` (`id`) ON DELETE CASCADE
);

CREATE TABLE `followers` (
  `id` CHAR(36) NOT NULL,
  `follower_id` CHAR(36) NOT NULL,
  `followed_id` CHAR(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `followers_follower_id_followed_id_unique` (`follower_id`,`followed_id`),
  KEY `followers_followed_id_foreign` (`followed_id`),
  CONSTRAINT `followers_followed_id_foreign` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `followers_follower_id_foreign` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
); 