<?php
/**
 * Database Setup Script
 * This script creates the required database tables and initializes the admin user
 */

require_once __DIR__ . '/config/database.php';

try {
    echo "Starting database setup...\n";

    // Create admins table
    echo "Creating 'admins' table...\n";
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ');
    echo "✓ Admins table created/verified\n";

    // Create leads table
    echo "Creating 'leads' table...\n";
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(20),
            subject VARCHAR(255),
            message LONGTEXT,
            source VARCHAR(100) DEFAULT "contact_form",
            status VARCHAR(50) DEFAULT "new",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        )
    ');
    echo "✓ Leads table created/verified\n";

    // Create blogs table
    echo "Creating 'blogs' table...\n";
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS blogs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT NOT NULL,
            image VARCHAR(255),
            meta_title VARCHAR(255),
            meta_description TEXT,
            author VARCHAR(255) DEFAULT "Admin",
            status VARCHAR(50) DEFAULT "published",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        )
    ');
    echo "✓ Blogs table created/verified\n";

    // Check if admin user exists
    echo "\nSetting up admin user...\n";
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM admins WHERE username = ?');
    $stmt->execute(['admin']);
    $result = $stmt->fetch();

    if ($result['count'] == 0) {
        // Create default admin user
        $defaultPassword = 'admin123'; // Change this after first login!
        $passwordHash = password_hash($defaultPassword, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare('
            INSERT INTO admins (username, password_hash, email)
            VALUES (?, ?, ?)
        ');
        $stmt->execute(['admin', $passwordHash, 'admin@example.com']);
        
        echo "✓ Default admin user created\n";
        echo "  Username: admin\n";
        echo "  Password: {$defaultPassword}\n";
        echo "  ⚠️  IMPORTANT: Change this password immediately after first login!\n";
    } else {
        echo "✓ Admin user already exists\n";
    }

    echo "\n✅ Database setup completed successfully!\n";
    echo "\nYou can now:\n";
    echo "1. Go to /admin/login.php\n";
    echo "2. Login with your admin credentials\n";
    echo "3. Manage leads and blogs from the dashboard\n";

} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
}