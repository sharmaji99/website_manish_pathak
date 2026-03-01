CREATE TABLE IF NOT EXISTS contact_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    source_page VARCHAR(80) NOT NULL DEFAULT 'index-contact-form',
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contact_phone_submitted_at (phone, submitted_at),
    INDEX idx_contact_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
