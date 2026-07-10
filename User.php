<?php
/**
 * User class - handles user authentication with MySQL database
 * Uses PDO for secure database interactions
 */
class User {
    private PDO $db;
    private string $table = 'users';

    public function __construct() {
        // Database configuration
        $host = 'localhost';
        $dbname = 'login_system';
        $username = 'root';
        $password = '';

        try {
            $this->db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            $this->createTableIfNotExists();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Create users table if it doesn't exist
     */
    private function createTableIfNotExists(): void {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
    }

    /**
     * Check if a username or email already exists
     * @param string $username
     * @param string|null $email
     * @return bool
     */
    public function exists(string $username, ?string $email = null): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = :username";
        $params = [':username' => $username];

        if ($email) {
            $sql .= " OR email = :email";
            $params[':email'] = $email;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Create a new user
     * @param string $username
     * @param string $email
     * @param string $password (plain text, will be hashed)
     * @return bool
     */
    public function create(string $username, string $email, string $password): bool {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO {$this->table} (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword
        ]);
    }

    /**
     * Verify user credentials and return user data
     * @param string $username (can be username or email)
     * @param string $password (plain text)
     * @return array|false
     */
    public function verify(string $username, string $password): array|false {
        // Check if input is email or username
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'username';

        $sql = "SELECT * FROM {$this->table} WHERE $field = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }

        return false;
    }

    /**
     * Get user by ID
     * @param int $id
     * @return array|null
     */
    public function getUserById(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $user = $stmt->fetch();
        if ($user) {
            unset($user['password']);
        }
        return $user ?: null;
    }

    /**
     * Get user by username
     * @param string $username
     * @return array|null
     */
    public function getUserByUsername(string $username): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        
        $user = $stmt->fetch();
        if ($user) {
            unset($user['password']);
        }
        return $user ?: null;
    }

    /**
     * Update user password
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(int $userId, string $newPassword): bool {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $userId
        ]);
    }

    /**
     * Update user email
     * @param int $userId
     * @param string $newEmail
     * @return bool
     */
    public function updateEmail(int $userId, string $newEmail): bool {
        $sql = "UPDATE {$this->table} SET email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':email' => $newEmail,
            ':id' => $userId
        ]);
    }

    /**
     * Delete a user
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * Get total number of users
     * @return int
     */
    public function countUsers(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get all users (paginated)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllUsers(int $limit = 100, int $offset = 0): array {
        $sql = "SELECT id, username, email, created_at, updated_at FROM {$this->table} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
