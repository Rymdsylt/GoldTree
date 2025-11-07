    <?php
    date_default_timezone_set('Asia/Manila');
    
    echo "Checking members table...\n";
    
    try {
        // First check if we can connect
        echo "Database URL: " . (getenv('DATABASE_URL') ? "Set" : "Not set") . "\n";
        $databaseUrl = getenv('DATABASE_URL');
        $db = $databaseUrl ? parse_url($databaseUrl) : false;

        if ($db && isset($db['host'], $db['path'])) {
            $pgsqlConfig = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                $db['host'],
                isset($db['port']) ? $db['port'] : 5432,
                ltrim($db['path'], '/')
            );

            $conn = new PDO(
                $pgsqlConfig,
                $db['user'] ?? '',
                $db['pass'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Successfully connected to PostgreSQL database\n";
            
            // Check members table
            $result = $conn->query("SELECT COUNT(*) as count FROM members");
            $count = $result->fetch(PDO::FETCH_ASSOC);
            echo "Total members: " . $count['count'] . "\n\n";
            
            if ($count['count'] > 0) {
                echo "Sample members:\n";
                $members = $conn->query("SELECT * FROM members ORDER BY id LIMIT 5");
                print_r($members->fetchAll(PDO::FETCH_ASSOC));
            }
            
            // Continue with regular table creation
            error_log("Creating PostgreSQL tables...");
            
            // Helper function to check if constraint exists
            $constraintExists = function($conn, $table, $constraintName) {
                try {
                    $stmt = $conn->prepare("
                        SELECT 1 FROM information_schema.table_constraints 
                        WHERE table_schema = 'public' 
                        AND table_name = ? 
                        AND constraint_name = ?
                    ");
                    $stmt->execute([$table, $constraintName]);
                    return $stmt->fetch() !== false;
                } catch(PDOException $e) {
                    error_log("Error checking constraint existence: " . $e->getMessage());
                    return false;
                }
            };
            
            // Create tables (no transaction needed - CREATE TABLE IF NOT EXISTS is safe)
            $conn->exec("CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                reset_password VARCHAR(255) DEFAULT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                member_id INTEGER,
                admin_status INTEGER DEFAULT 0,
                privacy_agreement BOOLEAN DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP DEFAULT NULL
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS members (
                id SERIAL PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                birthdate DATE,
                membership_date DATE DEFAULT CURRENT_DATE,
                gender VARCHAR(10),
                category VARCHAR(50) DEFAULT NULL,
                status VARCHAR(10) DEFAULT 'active',
                profile_image BYTEA,
                user_id INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Add foreign key constraints only if they don't exist
            if (!$constraintExists($conn, 'users', 'users_member_id_fk')) {
                try {
                    $conn->exec("ALTER TABLE users ADD CONSTRAINT users_member_id_fk FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE");
                } catch(PDOException $e) {
                    error_log("Error adding foreign key users_member_id_fk: " . $e->getMessage());
                }
            }
            
            if (!$constraintExists($conn, 'members', 'members_user_id_fk')) {
                try {
                    $conn->exec("ALTER TABLE members ADD CONSTRAINT members_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
                } catch(PDOException $e) {
                    error_log("Error adding foreign key members_user_id_fk: " . $e->getMessage());
                }
            }

            $conn->exec("CREATE TABLE IF NOT EXISTS donations (
                id SERIAL PRIMARY KEY,
                member_id INTEGER,
                donor_name VARCHAR(100),
                amount DECIMAL(10,2) NOT NULL,
                donation_type VARCHAR(10) NOT NULL,
                donation_date DATE NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT donations_member_id_fk FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS events (
                id SERIAL PRIMARY KEY,
                title VARCHAR(100) NOT NULL,
                description TEXT,
                start_datetime TIMESTAMP NOT NULL,
                end_datetime TIMESTAMP NOT NULL,
                event_type VARCHAR(10) NOT NULL,
                location VARCHAR(100),
                max_attendees INTEGER,
                registration_deadline TIMESTAMP,
                image BYTEA,
                status VARCHAR(10) DEFAULT 'upcoming',
                created_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_notification_sent TIMESTAMP,
                reminder_sent BOOLEAN DEFAULT FALSE,
                CONSTRAINT events_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS event_attendance (
                id SERIAL PRIMARY KEY,
                event_id INTEGER NOT NULL,
                member_id INTEGER NOT NULL,
                attendance_status VARCHAR(10) NOT NULL,
                attendance_date DATE DEFAULT CURRENT_DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT event_attendance_event_id_fk FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
                CONSTRAINT event_attendance_member_id_fk FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS notifications (
                id SERIAL PRIMARY KEY,
                notification_type VARCHAR(20) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                send_email BOOLEAN DEFAULT FALSE,
                status VARCHAR(10) DEFAULT 'pending',
                created_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT notifications_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS notification_recipients (
                id SERIAL PRIMARY KEY,
                notification_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                user_email VARCHAR(100),
                is_read BOOLEAN DEFAULT FALSE,
                email_sent BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT notification_recipients_notification_id_fk FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
                CONSTRAINT notification_recipients_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS member_notes (
                id SERIAL PRIMARY KEY,
                member_id INTEGER NOT NULL,
                note_text TEXT NOT NULL,
                note_type VARCHAR(20) NOT NULL,
                created_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT member_notes_member_id_fk FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
                CONSTRAINT member_notes_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS event_assignments (
                id SERIAL PRIMARY KEY,
                event_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT event_assignments_event_id_fk FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
                CONSTRAINT event_assignments_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE (event_id, user_id)
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS baptismal_records (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                parent1_name VARCHAR(255),
                parent1_origin VARCHAR(255),
                parent2_name VARCHAR(255),
                parent2_origin VARCHAR(255),
                address TEXT NOT NULL,
                birth_date DATE NOT NULL,
                birth_place VARCHAR(255) NOT NULL,
                gender VARCHAR(10) NOT NULL,
                baptism_date DATE NOT NULL,
                minister VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS baptismal_sponsors (
                id SERIAL PRIMARY KEY,
                baptismal_record_id INTEGER NOT NULL,
                sponsor_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT baptismal_sponsors_record_id_fk FOREIGN KEY (baptismal_record_id) REFERENCES baptismal_records(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS confirmation_records (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                parent1_name VARCHAR(255),
                parent1_origin VARCHAR(255),
                parent2_name VARCHAR(255),
                parent2_origin VARCHAR(255),
                address TEXT NOT NULL,
                birth_date DATE NOT NULL,
                birth_place VARCHAR(255) NOT NULL,
                gender VARCHAR(10) NOT NULL,
                baptism_date DATE NOT NULL,
                minister VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS confirmation_sponsors (
                id SERIAL PRIMARY KEY,
                confirmation_record_id INTEGER NOT NULL,
                sponsor_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT confirmation_sponsors_record_id_fk FOREIGN KEY (confirmation_record_id) REFERENCES confirmation_records(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS first_communion_records (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                parent1_name VARCHAR(255),
                parent1_origin VARCHAR(255),
                parent2_name VARCHAR(255),
                parent2_origin VARCHAR(255),
                address TEXT NOT NULL,
                birth_date DATE NOT NULL,
                birth_place VARCHAR(255) NOT NULL,
                gender VARCHAR(10) NOT NULL,
                baptism_date DATE NOT NULL,
                baptism_church VARCHAR(255) NOT NULL,
                church VARCHAR(255) NOT NULL,
                communion_date DATE NOT NULL,
                confirmation_date DATE NOT NULL,
                minister VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_records (
                id SERIAL PRIMARY KEY,
                matrimony_date DATE NOT NULL,
                church VARCHAR(255) NOT NULL,
                minister VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_couples (
                id SERIAL PRIMARY KEY,
                matrimony_record_id INTEGER NOT NULL,
                type VARCHAR(10) NOT NULL,
                name VARCHAR(255) NOT NULL,
                parent1_name VARCHAR(255),
                parent1_origin VARCHAR(255),
                parent2_name VARCHAR(255),
                parent2_origin VARCHAR(255),
                birth_date DATE NOT NULL,
                birth_place VARCHAR(255) NOT NULL,
                gender VARCHAR(10) NOT NULL,
                baptism_date DATE NOT NULL,
                baptism_church VARCHAR(255) NOT NULL,
                confirmation_date DATE NOT NULL,
                confirmation_church VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT matrimony_couples_record_id_fk FOREIGN KEY (matrimony_record_id) REFERENCES matrimony_records(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_sponsors (
                id SERIAL PRIMARY KEY,
                matrimony_record_id INTEGER NOT NULL,
                sponsor_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT matrimony_sponsors_record_id_fk FOREIGN KEY (matrimony_record_id) REFERENCES matrimony_records(id) ON DELETE CASCADE
            )");

            // Add default admin user if not exists (use transaction for this operation)
            try {
                $conn->beginTransaction();
                $checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'root'")->fetch();
                if (!$checkAdmin) {
                    $hashedPassword = password_hash('mdradmin', PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status) VALUES (?, ?, ?, ?)");
                    $stmt->execute(['root', $hashedPassword, 'admin@materdolorosa.com', 1]);
                }
                $conn->commit();
            } catch(PDOException $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                error_log("Error adding default admin user: " . $e->getMessage());
                // Don't throw - admin user might already exist
            }
            
            error_log("PostgreSQL tables created successfully");
            
        } else {
            // Local MySQL configuration
            error_log($databaseUrl ? "Invalid DATABASE_URL configuration, using local MySQL configuration" : "DATABASE_URL not set, using local MySQL configuration");
            define('DB_HOST', 'localhost');
            define('DB_USER', 'root');
            define('DB_PASS', '');
            define('DB_NAME', 'goldtree');
            
            $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
            $conn->exec("USE " . DB_NAME);
            
            // Create tables for MySQL
            $conn->exec("CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                reset_password VARCHAR(255) DEFAULT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                member_id INT,
                admin_status INT DEFAULT 0,
                privacy_agreement BOOLEAN DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL DEFAULT NULL
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                birthdate DATE,
                membership_date DATE DEFAULT CURRENT_DATE,
                gender VARCHAR(10) CHECK (gender IN ('male', 'female', 'other')),
                category VARCHAR(50) DEFAULT NULL,
                status VARCHAR(10) CHECK (status IN ('active', 'inactive')) DEFAULT 'active',
                profile_image BYTEA,
                user_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            try {
                $conn->exec("ALTER TABLE users ADD CONSTRAINT users_member_id_fk FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE");
                $conn->exec("ALTER TABLE members ADD CONSTRAINT members_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
            } catch(PDOException $e) {
                error_log("Error adding foreign keys: " . $e->getMessage());
            }

            $conn->exec("CREATE TABLE IF NOT EXISTS donations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                member_id INT,
                donor_name VARCHAR(100),
                amount DECIMAL(10,2) NOT NULL,
                donation_type VARCHAR(10) CHECK (donation_type IN ('tithe', 'offering', 'project', 'other')) NOT NULL,
                donation_date DATE NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS events (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(100) NOT NULL,
                description TEXT,
                start_datetime DATETIME NOT NULL,
                end_datetime DATETIME NOT NULL,
                event_type VARCHAR(10) CHECK (event_type IN ('worship', 'prayer', 'youth', 'outreach', 'special')) NOT NULL,
                location VARCHAR(100),
                max_attendees INT,
                registration_deadline DATETIME,
                image BYTEA,
                status VARCHAR(20) DEFAULT 'upcoming',
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_notification_sent TIMESTAMP NULL,
                reminder_sent BOOLEAN DEFAULT FALSE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS event_attendance (
                id INT PRIMARY KEY AUTO_INCREMENT,
                event_id INT NOT NULL,
                member_id INT NOT NULL,
                attendance_status VARCHAR(10) CHECK (attendance_status IN ('present', 'absent', 'late')) NOT NULL,
                attendance_date DATE DEFAULT CURRENT_DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
                FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS notifications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                notification_type VARCHAR(15) CHECK (notification_type IN ('announcement', 'event', 'donation', 'other')) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                send_email BOOLEAN DEFAULT FALSE,
                status VARCHAR(10) CHECK (status IN ('pending', 'sent', 'failed')) DEFAULT 'pending',
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS notification_recipients (
                id INT PRIMARY KEY AUTO_INCREMENT,
                notification_id INT NOT NULL,
                user_id INT NOT NULL,
                user_email VARCHAR(100),
                is_read BOOLEAN DEFAULT FALSE,
                email_sent BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS member_notes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                member_id INT NOT NULL,
                note_text TEXT NOT NULL,
                note_type VARCHAR(15) CHECK (note_type IN ('general', 'pastoral', 'counseling', 'other')) NOT NULL,
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS event_assignments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                event_id INT NOT NULL,
                user_id INT NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_assignment (event_id, user_id)
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS baptismal_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                parent1_name VARCHAR(255),
                parent1_origin VARCHAR(255),
                parent2_name VARCHAR(255),
                parent2_origin VARCHAR(255),
                address TEXT NOT NULL,
                birth_date DATE NOT NULL,
                birth_place VARCHAR(255) NOT NULL,
                gender ENUM('male', 'female', 'other') NOT NULL,
                baptism_date DATE NOT NULL,
                minister VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS baptismal_sponsors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                baptismal_record_id INT NOT NULL,
                sponsor_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (baptismal_record_id) REFERENCES baptismal_records(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS confirmation_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                parent1_name VARCHAR(255),
                parent1_origin VARCHAR(255),
                parent2_name VARCHAR(255),
                parent2_origin VARCHAR(255),
                address TEXT NOT NULL,
                birth_date DATE NOT NULL,
                birth_place VARCHAR(255) NOT NULL,
                gender ENUM('male', 'female', 'other') NOT NULL,
                baptism_date DATE NOT NULL,
                minister VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS confirmation_sponsors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                confirmation_record_id INT NOT NULL,
                sponsor_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (confirmation_record_id) REFERENCES confirmation_records(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS first_communion_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                parent1_name VARCHAR(255),
                parent1_origin VARCHAR(255),
                parent2_name VARCHAR(255),
                parent2_origin VARCHAR(255),
                address TEXT NOT NULL,
                birth_date DATE NOT NULL,
                birth_place VARCHAR(255) NOT NULL,
                gender ENUM('male', 'female', 'other') NOT NULL,
                baptism_date DATE NOT NULL,
                baptism_church VARCHAR(255) NOT NULL,
                church VARCHAR(255) NOT NULL,
                communion_date DATE NOT NULL,
                confirmation_date DATE NOT NULL,
                minister VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                matrimony_date DATE NOT NULL,
                church VARCHAR(255) NOT NULL,
                minister VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_couples (
                id INT AUTO_INCREMENT PRIMARY KEY,
                matrimony_record_id INT NOT NULL,
                type VARCHAR(10) CHECK (type IN ('bride', 'groom')) NOT NULL,
                name VARCHAR(255) NOT NULL,
                parent1_name VARCHAR(255),
                parent1_origin VARCHAR(255),
                parent2_name VARCHAR(255),
                parent2_origin VARCHAR(255),
                birth_date DATE NOT NULL,
                birth_place VARCHAR(255) NOT NULL,
                gender ENUM('male', 'female', 'other') NOT NULL,
                baptism_date DATE NOT NULL,
                baptism_church VARCHAR(255) NOT NULL,
                confirmation_date DATE NOT NULL,
                confirmation_church VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (matrimony_record_id) REFERENCES matrimony_records(id) ON DELETE CASCADE
            )");

            $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_sponsors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                matrimony_record_id INT NOT NULL,
                sponsor_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (matrimony_record_id) REFERENCES matrimony_records(id) ON DELETE CASCADE
            )");

            // Add default admin user if not exists
            $checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'root'")->fetch();
            if (!$checkAdmin) {
                $hashedPassword = password_hash('mdradmin', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status) VALUES (?, ?, ?, ?)");
                $stmt->execute(['root', $hashedPassword, 'admin@materdolorosa.com', 1]);
            }
        }
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Connection failed: " . $e->getMessage());
    }