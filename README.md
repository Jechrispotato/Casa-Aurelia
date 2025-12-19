# Casa-Aurelia System Documentation

## 1. PROJECT PROPOSAL

### 1.1 Project Overview
*   **Brief Description**: Casa-Aurelia is a premium hotel management and booking system designed to provide a seamless digital experience for guests and administrative efficiency for hotel staff.
*   **Type of System**: Hotel Booking and Content Management System (CMS).
*   **Purpose**: To automate the reservation process for rooms, dining, and spa services while providing a modern interface for guests to explore luxury amenities.
*   **Problems Addressed**:
    *   Manual booking errors and double-bookings.
    *   Lack of a centralized platform for various hotel services (Rooms, Spa, Dining).
    *   Inefficient communication of availability to potential guests.
*   **Client-Server Architecture**: The system utilizes a traditional client-server model where the **Client** (web browser) makes HTTP requests to the **Server** (XAMPP/Apache). The server processes PHP logic, interacts with the **MySQL Database**, and returns HTML/CSS/JS responses to the client.

### 1.2 Objectives of the System
*   Provide a user-friendly 24/7 online booking platform.
*   Implement a secure administrative dashboard for managing hotel inventory (Rooms).
*   Ensure data integrity and security through modern web practices.
*   Offer real-time availability checks for rooms.

### 1.3 Target Users
*   **Guests**: Individuals looking to book stay, dining, or spa sessions.
*   **Administrators**: Hotel staff responsible for managing bookings, rooms, and monitoring system activities.

### 1.4 Scope and Limitations
*   **Scope**:
    *   User Authentication (Login, Register, Password Reset).
    *   Room Management CRUD (Admin).
    *   Booking System (Room, Dining, Spa).
    *   Notification System for booking status.
    *   Responsive Landing Page and Public Suites view.
*   **Limitations**:
    *   Does not include an integrated payment gateway (simulated confirmation).
    *   Does not include multi-language support.

---

## 2. SYSTEM DESIGN

### 2.1 System Architecture (Client-Server Workflow)
```mermaid
graph LR
    Client["Client (Browser)"] -- "HTTP Request (GET/POST)" --> Server["Server (PHP/Apache)"]
    Server -- "SQL Query" --> DB[("MySQL Database")]
    DB -- "Result Set" --> Server
    Server -- "HTML/CSS/JS Response" --> Client
```

### 2.2 Data Flow Diagram (DFD Level 1)
```mermaid
graph TD
    User((User)) -- "Login Credentials" --> Auth[Authentication Process]
    Auth -- "Valid Session" --> User
    User -- "Booking Details" --> Booking[Booking Management]
    Booking -- "Query Availability" --> DB[(Database)]
    DB -- "Room Status" --> Booking
    Booking -- "Confirmation" --> User
    Admin((Admin)) -- "Room Data" --> RoomMgmt[Room CRUD]
    RoomMgmt -- "Update/Insert" --> DB
```

### 2.3 System Sequence Diagram (Authentication)
```mermaid
sequenceDiagram
    participant U as User
    participant S as Server (PHP)
    participant D as Database (MySQL)

    U->>S: Submit Login (Username, Password, CSRF)
    S->>S: Verify CSRF & Rate Limit
    S->>D: SELECT user WHERE username = ?
    D-->>S: User Data (Hashed Password)
    S->>S: password_verify()
    alt Success
        S->>S: Start Session & Regenerate ID
        S-->>U: Redirect to Dashboard
    else Failure
        S-->>U: Error Message
    end
```

### 2.4 Database Design (ERD)
```mermaid
erDiagram
    USERS ||--o{ BOOKINGS : makes
    USERS ||--o{ DINING_RESERVATIONS : reserves
    USERS ||--o{ SPA_BOOKINGS : schedules
    ROOMS ||--o{ BOOKINGS : allocated-to
    ROOMS ||--o{ ROOM_REVIEWS : has
    USERS ||--o{ ROOM_REVIEWS : writes
    USERS ||--o{ NOTIFICATIONS : receives

    USERS {
        int id PK
        string username
        string email
        string password
        string role
        boolean is_verified
    }
    ROOMS {
        int id PK
        string room_name
        text description
        decimal price
        string room_image
    }
    BOOKINGS {
        int id PK
        int user_id FK
        int room_id FK
        datetime check_in_date
        datetime check_out_date
        decimal total_price
        string status
    }
```

---

## 3. DEVELOPMENT PHASE

### 3.1 Tools and Technologies Used
*   **Frontend**: HTML5, Tailwind CSS, JavaScript (ES6+), Font Awesome.
*   **Backend**: PHP 8.1+ (Object-Oriented & Procedural mix).
*   **Database**: MySQL (MariaDB).
*   **Tools**: XAMPP (Apache/MySQL), VS Code, Composer, NPM.

### 3.2 System Screenshots & Interface Summary

````carousel
![Landing Page](file:///C:/Users/Lawrence/.gemini/antigravity/brain/6afefa33-2570-48ad-83d8-0a86ca02d8b1/landing_page_1766155400120.png)
<!-- slide -->
![Login Page](file:///C:/Users/Lawrence/.gemini/antigravity/brain/6afefa33-2570-48ad-83d8-0a86ca02d8b1/login_page_1766155459660.png)
<!-- slide -->
![Admin Dashboard](file:///C:/Users/Lawrence/.gemini/antigravity/brain/6afefa33-2570-48ad-83d8-0a86ca02d8b1/admin_dashboard_1766155598151.png)
<!-- slide -->
![Room Management](file:///C:/Users/Lawrence/.gemini/antigravity/brain/6afefa33-2570-48ad-83d8-0a86ca02d8b1/room_management_1766155613251.png)
````

### 3.3 PHP Code Structure and Explanation

#### 3.3.1 Database Connection
The system centralizes its database connection in `includes/db.php`.

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_booking";

// Connection creation
$conn = new mysqli($servername, $username, $password, $dbname);

// Error Handling
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
```
*   **Logic**: Uses the `mysqli` object to establish a bridge between the PHP server and the MySQL database.
*   **Error Handling**: The `connect_error` property is checked immediately after instantiation. If a connection fails, the `die()` function terminates the script execution and displays a descriptive error message to prevent cascading failures.

#### 3.3.2 Authentication Module
Located in `auth/process/process_login.php`.

*   **Login Process**: The system captures the username/email and password via POST. It then queries the database for the user record.
```php
$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();
```
*   **Session Handling**: Upon successful verification via `password_verify()`, the system initializes the `$_SESSION` superglobal with user identity data.
```php
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
session_regenerate_id(true); // Prevents session fixation attacks
```
*   **Access Restrictions**: Pages are protected by role checks.
```php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
```

#### 3.3.3 CRUD Operations (Room Module)
The Room Module demonstrates full Create, Read, Update, and Delete capabilities.

*   **Create (Insert)**: `admin/add_room.php`
```sql
INSERT INTO rooms (room_name, price, description, room_image) 
VALUES ('$room_name', $price, '$description', '$room_image')
```
*   **Read (Select)**: `admin/rooms.php` fetches all rooms to display them in a management grid.
```sql
SELECT * FROM rooms ORDER BY id DESC
```
*   **Update**: `admin/update_room.php` modifies existing records based on the room ID.
*   **Delete**: `admin/delete_room.php` removes a room from the database after confirmation.

#### 3.3.4 Form Validation
Validation is implemented in two layers to ensure both speed (client-side) and reliability (server-side).

*   **Client-side Validation (JavaScript)**:
Handles immediate feedback on the Login form without a page reload.
```javascript
form.addEventListener('submit', function (e) {
    if (!input.value.trim()) {
        isValid = false;
        showError(input, 'This field is required');
    }
});
```
*   **Server-side Validation (PHP)**:
Implemented in `includes/security.php` to perform deep checks on data types, lengths, and malicious patterns.
```php
function validate_username($username) {
    if (strlen($username) < 3) {
        return ['valid' => false, 'error' => 'Username too short'];
    }
}
```

#### 3.3.5 Security Implementations
*   **SQL Injection Prevention**: The system strictly uses **Prepared Statements**.
```php
$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
```
*   **XSS Protection**: All user-generated content is escaped using `htmlspecialchars()` before being rendered in the browser.
*   **Password Security**: The system utilizes `password_hash()` with the `PASSWORD_DEFAULT` algorithm for storage and `password_verify()` for login.
*   **Input Sanitization**: Global helper functions like `sanitize_input()` (using `trim`, `stripslashes`, and `htmlspecialchars`) are used on all incoming POST/GET data.

---

## 4. TESTING PHASE

### 4.1 Test Cases & Results

| Test Case ID | Feature | Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| TC-01 | Authentication | Login with invalid credentials | System shows "Invalid credentials" error | Error message displayed | PASS |
| TC-02 | Authentication | Login with valid admin credentials | Redirect to Admin Dashboard | Successfully redirected | PASS |
| TC-03 | Booking | Book room during overlapping dates | System prevents booking and shows error | Overlap detected, error shown | PASS |
| TC-04 | CRUD | Admin adds a new room | Room appears in the room management list | Room added successfully | PASS |
| TC-05 | Security | Submit form with missing CSRF token | Request is rejected with security error | 403/Error message shown | PASS |

### 4.2 Bug Fixes & Refinements
*   **Fixed**: Path calculation error in `paths.php` when the system was running in nested subdirectories.
*   **Refinement**: Optimized the total price calculation in `add_booking.php` to handle hourly overages correctly.

---

## 5. CONCLUSION
The development of Casa-Aurelia provided a deep dive into the complexities of **state management** and **secure web architecture**. By implementing rigorous security measures like CSRF protection and prepared statements, we ensured a robust system that protects user data. The most significant challenge was the logic for handling overlapping booking dates, which was solved using an efficient SQL `BETWEEN` and `DATE_ADD` logic. Future versions will integrate automated email notifications and an interactive floor plan for room selection.
