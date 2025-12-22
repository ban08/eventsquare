-- Ensure notification_message enum includes event canceled
ALTER TYPE lbaw2536.notification_message ADD VALUE IF NOT EXISTS 'event canceled';
