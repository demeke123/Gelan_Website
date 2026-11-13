-- Gelan Boys Boarding School website — MySQL / MariaDB schema
-- Optional: admin/setup.php creates these tables automatically.
-- Use this file only if you prefer to import the tables with phpMyAdmin.

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'editor',   -- 'admin' or 'editor'
  active TINYINT NOT NULL DEFAULT 1,
  last_login DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(20) NOT NULL DEFAULT 'news',     -- 'news', 'notice' or 'event'
  title_en VARCHAR(255) NOT NULL DEFAULT '',
  title_am VARCHAR(255) NOT NULL DEFAULT '',
  body_en MEDIUMTEXT,
  body_am MEDIUMTEXT,
  publish_date DATE NOT NULL,
  event_date DATE NULL,
  is_published TINYINT NOT NULL DEFAULT 1,
  created_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gallery (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL,
  caption_en VARCHAR(255) NOT NULL DEFAULT '',
  caption_am VARCHAR(255) NOT NULL DEFAULT '',
  created_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  skey VARCHAR(100) NOT NULL PRIMARY KEY,
  svalue MEDIUMTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL DEFAULT '',
  email VARCHAR(190) NOT NULL DEFAULT '',
  subject VARCHAR(200) NOT NULL DEFAULT '',
  message MEDIUMTEXT,
  ip VARCHAR(45) NOT NULL DEFAULT '',
  is_read TINYINT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  attempted_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
