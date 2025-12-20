-- Adds missing notification column for application-related notifications.
SET search_path TO lbaw2536;
ALTER TYPE notification_message ADD VALUE IF NOT EXISTS 'application accepted';
ALTER TYPE notification_message ADD VALUE IF NOT EXISTS 'application rejected';
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = 'lbaw2536'
          AND table_name = 'notification'
          AND column_name = 'id_application'
    ) THEN
        ALTER TABLE notification
        ADD COLUMN id_application INTEGER REFERENCES application(id_application)
        ON UPDATE CASCADE ON DELETE SET NULL;
    END IF;
END $$;
