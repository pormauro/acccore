-- 001_phase1_identity_companies.sql
-- MySQL/MariaDB - InnoDB - utf8mb4
-- IDs: UUID CHAR(36) (portable)
-- Historial: tablas *_history append-only
-- Nota: triggers incluidos para historial mínimo.
SET NAMES utf8mb4;
SET time_zone = '+00:00';
-- =========================
-- Helpers / Convenciones
-- =========================
-- change_type: 'insert' | 'update' | 'delete'
-- request_id: correlación opcional (por ejemplo UUID) para trazabilidad
-- =========================
-- USERS
-- =========================
CREATE TABLE IF NOT EXISTS users (
  id              CHAR(36)     NOT NULL,
  name            VARCHAR(150) NOT NULL,
  email           VARCHAR(190) NOT NULL,
  email_verified_at DATETIME NULL,
  password        VARCHAR(255) NOT NULL,
  status          ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_at      DATETIME NOT NULL,
  created_by      CHAR(36) NULL,
  updated_at      DATETIME NULL,
  updated_by      CHAR(36) NULL,
  deleted_at      DATETIME NULL,
  deleted_by      CHAR(36) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_status (status),
  KEY idx_users_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS users_history (
  hid             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id              CHAR(36) NOT NULL,
  name            VARCHAR(150) NOT NULL,
  email           VARCHAR(190) NOT NULL,
  email_verified_at DATETIME NULL,
  password        VARCHAR(255) NOT NULL,
  status          ENUM('active','suspended') NOT NULL,
  created_at      DATETIME NOT NULL,
  created_by      CHAR(36) NULL,
  updated_at      DATETIME NULL,
  updated_by      CHAR(36) NULL,
  deleted_at      DATETIME NULL,
  deleted_by      CHAR(36) NULL,
  change_type     ENUM('insert','update','delete') NOT NULL,
  changed_at      DATETIME NOT NULL,
  changed_by      CHAR(36) NULL,
  request_id      CHAR(36) NULL,
  PRIMARY KEY (hid),
  KEY idx_users_history_id (id),
  KEY idx_users_history_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- =========================
-- COMPANIES
-- =========================
CREATE TABLE IF NOT EXISTS companies (
  id              CHAR(36)     NOT NULL,
  legal_name      VARCHAR(200) NOT NULL,
  trade_name      VARCHAR(200) NULL,
  tax_id          VARCHAR(50)  NULL,
  status          ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_at      DATETIME NOT NULL,
  created_by      CHAR(36) NULL,
  updated_at      DATETIME NULL,
  updated_by      CHAR(36) NULL,
  deleted_at      DATETIME NULL,
  deleted_by      CHAR(36) NULL,
  PRIMARY KEY (id),
  KEY idx_companies_status (status),
  KEY idx_companies_deleted_at (deleted_at),
  KEY idx_companies_created_by (created_by),
  CONSTRAINT fk_companies_created_by FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_companies_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_companies_deleted_by FOREIGN KEY (deleted_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS companies_history (
  hid             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id              CHAR(36) NOT NULL,
  legal_name      VARCHAR(200) NOT NULL,
  trade_name      VARCHAR(200) NULL,
  tax_id          VARCHAR(50)  NULL,
  status          ENUM('active','suspended') NOT NULL,
  created_at      DATETIME NOT NULL,
  created_by      CHAR(36) NULL,
  updated_at      DATETIME NULL,
  updated_by      CHAR(36) NULL,
  deleted_at      DATETIME NULL,
  deleted_by      CHAR(36) NULL,
  change_type     ENUM('insert','update','delete') NOT NULL,
  changed_at      DATETIME NOT NULL,
  changed_by      CHAR(36) NULL,
  request_id      CHAR(36) NULL,
  PRIMARY KEY (hid),
  KEY idx_companies_history_id (id),
  KEY idx_companies_history_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- =========================
-- MEMBERSHIPS
-- =========================
CREATE TABLE IF NOT EXISTS company_memberships (
  id              CHAR(36) NOT NULL,
  company_id      CHAR(36) NOT NULL,
  user_id         CHAR(36) NOT NULL,
  role            ENUM('owner','admin','member') NOT NULL DEFAULT 'member',
  status          ENUM('invited','active','suspended') NOT NULL DEFAULT 'active',
  invited_email   VARCHAR(190) NULL, -- si se invita por mail antes de tener user
  invited_at      DATETIME NULL,
  accepted_at     DATETIME NULL,
  created_at      DATETIME NOT NULL,
  created_by      CHAR(36) NULL,
  updated_at      DATETIME NULL,
  updated_by      CHAR(36) NULL,
  deleted_at      DATETIME NULL,
  deleted_by      CHAR(36) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_membership_company_user (company_id, user_id),
  KEY idx_membership_company (company_id),
  KEY idx_membership_user (user_id),
  KEY idx_membership_role (role),
  KEY idx_membership_status (status),
  KEY idx_membership_deleted_at (deleted_at),
  CONSTRAINT fk_membership_company FOREIGN KEY (company_id) REFERENCES companies(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_membership_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_membership_created_by FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_membership_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_membership_deleted_by FOREIGN KEY (deleted_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS company_memberships_history (
  hid             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id              CHAR(36) NOT NULL,
  company_id      CHAR(36) NOT NULL,
  user_id         CHAR(36) NOT NULL,
  role            ENUM('owner','admin','member') NOT NULL,
  status          ENUM('invited','active','suspended') NOT NULL,
  invited_email   VARCHAR(190) NULL,
  invited_at      DATETIME NULL,
  accepted_at     DATETIME NULL,
  created_at      DATETIME NOT NULL,
  created_by      CHAR(36) NULL,
  updated_at      DATETIME NULL,
  updated_by      CHAR(36) NULL,
  deleted_at      DATETIME NULL,
  deleted_by      CHAR(36) NULL,
  change_type     ENUM('insert','update','delete') NOT NULL,
  changed_at      DATETIME NOT NULL,
  changed_by      CHAR(36) NULL,
  request_id      CHAR(36) NULL,
  PRIMARY KEY (hid),
  KEY idx_memberships_history_id (id),
  KEY idx_memberships_history_company (company_id),
  KEY idx_memberships_history_user (user_id),
  KEY idx_memberships_history_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- =========================
-- AUDIT LOG (global append-only)
-- =========================
CREATE TABLE IF NOT EXISTS audit_log (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  occurred_at     DATETIME NOT NULL,
  actor_user_id   CHAR(36) NULL,
  company_id      CHAR(36) NULL,
  action          VARCHAR(120) NOT NULL, -- e.g. 'auth.login', 'company.create'
  entity_type     VARCHAR(80)  NULL,     -- e.g. 'company'
  entity_id       CHAR(36)     NULL,     -- uuid
  severity        ENUM('info','warning','security','error') NOT NULL DEFAULT 'info',
  ip              VARCHAR(64)  NULL,
  user_agent      VARCHAR(255) NULL,
  request_id      CHAR(36) NULL,
  metadata_json   JSON NULL,
  PRIMARY KEY (id),
  KEY idx_audit_occurred_at (occurred_at),
  KEY idx_audit_actor (actor_user_id),
  KEY idx_audit_company (company_id),
  KEY idx_audit_action (action),
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_audit_company FOREIGN KEY (company_id) REFERENCES companies(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- =========================
-- HISTORY TRIGGERS (mínimos)
-- =========================
DELIMITER $$
CREATE TRIGGER trg_users_ai AFTER INSERT ON users
FOR EACH ROW
BEGIN
  INSERT INTO users_history (
    id,name,email,email_verified_at,password,status,
    created_at,created_by,updated_at,updated_by,deleted_at,deleted_by,
    change_type,changed_at,changed_by,request_id
  ) VALUES (
    NEW.id,NEW.name,NEW.email,NEW.email_verified_at,NEW.password,NEW.status,
    NEW.created_at,NEW.created_by,NEW.updated_at,NEW.updated_by,NEW.deleted_at,NEW.deleted_by,
    'insert',UTC_TIMESTAMP(),NEW.created_by,NULL
  );
END$$
CREATE TRIGGER trg_users_au AFTER UPDATE ON users
FOR EACH ROW
BEGIN
  INSERT INTO users_history (
    id,name,email,email_verified_at,password,status,
    created_at,created_by,updated_at,updated_by,deleted_at,deleted_by,
    change_type,changed_at,changed_by,request_id
  ) VALUES (
    NEW.id,NEW.name,NEW.email,NEW.email_verified_at,NEW.password,NEW.status,
    NEW.created_at,NEW.created_by,NEW.updated_at,NEW.updated_by,NEW.deleted_at,NEW.deleted_by,
    'update',UTC_TIMESTAMP(),NEW.updated_by,NULL
  );
END$$
CREATE TRIGGER trg_companies_ai AFTER INSERT ON companies
FOR EACH ROW
BEGIN
  INSERT INTO companies_history (
    id,legal_name,trade_name,tax_id,status,
    created_at,created_by,updated_at,updated_by,deleted_at,deleted_by,
    change_type,changed_at,changed_by,request_id
  ) VALUES (
    NEW.id,NEW.legal_name,NEW.trade_name,NEW.tax_id,NEW.status,
    NEW.created_at,NEW.created_by,NEW.updated_at,NEW.updated_by,NEW.deleted_at,NEW.deleted_by,
    'insert',UTC_TIMESTAMP(),NEW.created_by,NULL
  );
END$$
CREATE TRIGGER trg_companies_au AFTER UPDATE ON companies
FOR EACH ROW
BEGIN
  INSERT INTO companies_history (
    id,legal_name,trade_name,tax_id,status,
    created_at,created_by,updated_at,updated_by,deleted_at,deleted_by,
    change_type,changed_at,changed_by,request_id
  ) VALUES (
    NEW.id,NEW.legal_name,NEW.trade_name,NEW.tax_id,NEW.status,
    NEW.created_at,NEW.created_by,NEW.updated_at,NEW.updated_by,NEW.deleted_at,NEW.deleted_by,
    'update',UTC_TIMESTAMP(),NEW.updated_by,NULL
  );
END$$
CREATE TRIGGER trg_memberships_ai AFTER INSERT ON company_memberships
FOR EACH ROW
BEGIN
  INSERT INTO company_memberships_history (
    id,company_id,user_id,role,status,invited_email,invited_at,accepted_at,
    created_at,created_by,updated_at,updated_by,deleted_at,deleted_by,
    change_type,changed_at,changed_by,request_id
  ) VALUES (
    NEW.id,NEW.company_id,NEW.user_id,NEW.role,NEW.status,NEW.invited_email,NEW.invited_at,NEW.accepted_at,
    NEW.created_at,NEW.created_by,NEW.updated_at,NEW.updated_by,NEW.deleted_at,NEW.deleted_by,
    'insert',UTC_TIMESTAMP(),NEW.created_by,NULL
  );
END$$
CREATE TRIGGER trg_memberships_au AFTER UPDATE ON company_memberships
FOR EACH ROW
BEGIN
  INSERT INTO company_memberships_history (
    id,company_id,user_id,role,status,invited_email,invited_at,accepted_at,
    created_at,created_by,updated_at,updated_by,deleted_at,deleted_by,
    change_type,changed_at,changed_by,request_id
  ) VALUES (
    NEW.id,NEW.company_id,NEW.user_id,NEW.role,NEW.status,NEW.invited_email,NEW.invited_at,NEW.accepted_at,
    NEW.created_at,NEW.created_by,NEW.updated_at,NEW.updated_by,NEW.deleted_at,NEW.deleted_by,
    'update',UTC_TIMESTAMP(),NEW.updated_by,NULL
  );
END$$
DELIMITER ;
