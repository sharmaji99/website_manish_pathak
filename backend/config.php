<?php

declare(strict_types=1);

/*
 * Copy this file to config.local.php and place real credentials there.
 * Keep config.local.php outside web root when possible.
 */
const DB_HOST = 'localhost';
const DB_PORT = 3306;
const DB_NAME = 'your_database_name';
const DB_USER = 'your_database_user';
const DB_PASS = 'your_database_password';

const APP_ENV = 'production';
const APP_TIMEZONE = 'Asia/Kolkata';
const APP_ALLOWED_ORIGIN = 'https://your-domain.com';

const CONTACT_MAX_NAME_LENGTH = 120;
const CONTACT_MAX_MESSAGE_LENGTH = 2000;
const CONTACT_RATE_LIMIT_SECONDS = 30;
