<?php
require_once __DIR__ . '/../db/connection.php';

function migrateToPostgres($conn) {
    // Modify table structures for PostgreSQL
    $conn->exec("
        DO $$ 
        BEGIN
            -- Convert ENUM types to CHECK constraints
            ALTER TABLE users 
                ALTER COLUMN admin_status TYPE INTEGER USING admin_status::INTEGER;
                
            ALTER TABLE members
                ALTER COLUMN gender TYPE VARCHAR(10),
                ALTER COLUMN status TYPE VARCHAR(10),
                ADD CONSTRAINT gender_check CHECK (gender IN ('male', 'female', 'other')),
                ADD CONSTRAINT status_check CHECK (status IN ('active', 'inactive'));
                
            -- Convert LONGBLOB to BYTEA
            ALTER TABLE members
                ALTER COLUMN profile_image TYPE BYTEA USING profile_image::BYTEA;
                
            -- Update auto-increment columns to use SERIAL
            ALTER TABLE users
                ALTER COLUMN id DROP DEFAULT,
                ALTER COLUMN id SET DATA TYPE SERIAL;
                
            ALTER TABLE members
                ALTER COLUMN id DROP DEFAULT,
                ALTER COLUMN id SET DATA TYPE SERIAL;
        EXCEPTION
            WHEN others THEN
                -- Handle cases where changes are already applied
                NULL;
        END $$;
    ");
}

// Only run migrations if we're on Heroku with PostgreSQL
if (getenv('DATABASE_URL')) {
    try {
        migrateToPostgres($conn);
        echo "Migration to PostgreSQL completed successfully.\n";
    } catch (PDOException $e) {
        die("Migration failed: " . $e->getMessage());
    }
}
?>