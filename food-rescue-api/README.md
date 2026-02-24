# 🍱 Food Rescue – Real-Time Food Donation Alert System
### Backend API | PHP + Oracle XE 21c | OCI8 | RESTful JSON

---

## 📁 Project Structure

```
food-rescue-api/
│
├── config/
│   └── db.php                  ← Oracle OCI8 connection + FCM config
│
├── auth/
│   ├── register.php            ← POST: User registration
│   └── login.php               ← POST: User login
│
├── alerts/
│   ├── create_alert.php        ← POST: Create food alert (DONOR only)
│   ├── get_nearby_alerts.php   ← GET:  Nearby alerts (Haversine formula)
│   ├── claim_alert.php         ← POST: Claim an alert (NGO/VOLUNTEER)
│   └── update_status.php       ← POST: Mark alert as COMPLETED/CANCELLED
│
├── notifications/
│   └── send_notification.php   ← FCM helper functions (single + bulk)
│
├── utils/
│   └── response.php            ← JSON response helpers + input validators
│
├── oracle_setup.sql            ← Full Oracle DB schema + sample data
└── README.md                   ← This file
```

---

## ⚙️ Prerequisites

| Requirement | Details |
|---|---|
| **XAMPP** | Version 8.x (includes Apache + PHP 8.x) |
| **Oracle XE 21c** | Free download from oracle.com |
| **OCI8 PHP Extension** | Must be enabled in `php.ini` |
| **Oracle Instant Client** | Required by OCI8 |
| **Postman** | For API testing |

---

## 🛠️ Step-by-Step Setup Guide

### Step 1 – Install Oracle XE 21c

1. Download **Oracle Database XE 21c** from:  
   👉 https://www.oracle.com/database/technologies/xe-downloads.html

2. Run the installer. During setup:
   - Set a **system password** (e.g., `oracle`)
   - Note the connection strings:
     - **Service Name (PDB):** `XEPDB1` ← use this for development
     - **Port:** `1521`

3. After install, verify Oracle is running:
   ```
   # In Windows Services (services.msc), check:
   OracleServiceXE          → Running
   OracleXETNSListener      → Running
   ```

---

### Step 2 – Install Oracle Instant Client + OCI8

1. Download **Oracle Instant Client Basic** (matching your OS/PHP bitness):  
   👉 https://www.oracle.com/database/technologies/instant-client/downloads.html

2. Extract to a folder, e.g.: `C:\oracle\instantclient_21_6`

3. Add to **Windows System PATH**:
   ```
   C:\oracle\instantclient_21_6
   ```

4. Download the **OCI8 PHP extension** DLL from PECL:  
   👉 https://pecl.php.net/package/oci8

   - Choose the version matching your PHP version and architecture (e.g., PHP 8.1 x64)
   - Copy `php_oci8_19.dll` (or newer) into `C:\xampp\php\ext\`

5. Edit **`C:\xampp\php\php.ini`** — find and uncomment (or add):
   ```ini
   extension=oci8_19   ; For PHP 8.x with Oracle 21c
   ```
   > If you see `extension=oci8`, change it to `extension=oci8_19`

6. **Restart Apache** via XAMPP Control Panel.

7. Verify OCI8 is loaded: Visit `http://localhost/phpinfo.php`  
   Look for the **oci8** section.

---

### Step 3 – Create phpinfo Test File (Optional)

Create `C:\xampp\htdocs\phpinfo.php`:
```php
<?php phpinfo(); ?>
```
Visit `http://localhost/phpinfo.php` → search for **"oci8"**.

---

### Step 4 – Run the Oracle SQL Setup Script

Open **Oracle SQL Developer** or **SQL*Plus** and connect as `SYSTEM`:

```
Username : system
Password : oracle (your XE install password)
Host     : localhost
Port     : 1521
Service  : XEPDB1
```

Then run the full script:
```sql
@C:\xampp\htdocs\food-rescue-api\oracle_setup.sql
```

Or paste the contents of `oracle_setup.sql` directly into SQL Developer's worksheet and click ▶ **Run Script**.

This will create:
- `USERS` table
- `FOOD_ALERTS` table
- `CLAIMS` table
- Indexes and foreign key constraints
- Sample test data (3 users + 2 food alerts)
- Auto-expire scheduled job (runs every 5 minutes)

---

### Step 5 – Deploy to XAMPP

Copy the entire `food-rescue-api/` folder to:
```
C:\xampp\htdocs\food-rescue-api\
```

---

### Step 6 – Configure Database Credentials

Edit **`config/db.php`** with your Oracle credentials:

```php
define('DB_USERNAME',          'system');
define('DB_PASSWORD',          'oracle');        // ← Your XE password
define('DB_CONNECTION_STRING', 'localhost:1521/XEPDB1');
define('FCM_SERVER_KEY',       'YOUR_FCM_KEY');  // ← From Firebase Console
```

---

### Step 7 – Start XAMPP and Test

1. Open **XAMPP Control Panel**
2. Start **Apache**
3. Open Postman and run the API tests below

---

## 🔌 API Reference & Postman Test Data

> **Base URL:** `http://localhost/food-rescue-api`

---

### 1️⃣ Register User

**Endpoint:** `POST /auth/register.php`  
**Content-Type:** `application/json`

#### Request (Donor):
```json
{
  "name"      : "Rajesh Kumar",
  "email"     : "rajesh@restaurant.com",
  "password"  : "SecurePass@123",
  "role"      : "DONOR",
  "phone"     : "9876543210",
  "latitude"  : 12.9716,
  "longitude" : 77.5946
}
```

#### Request (NGO):
```json
{
  "name"      : "Helping Hands NGO",
  "email"     : "info@helpinghands.org",
  "password"  : "NGOPass@456",
  "role"      : "NGO",
  "phone"     : "9123456780",
  "latitude"  : 12.9725,
  "longitude" : 77.5900
}
```

#### Request (Volunteer):
```json
{
  "name"      : "Priya Sharma",
  "email"     : "priya.volunteer@gmail.com",
  "password"  : "VolPass@789",
  "role"      : "VOLUNTEER",
  "phone"     : "8899776655",
  "latitude"  : 12.9685,
  "longitude" : 77.6010
}
```

#### ✅ Success Response (201 Created):
```json
{
  "success"   : true,
  "message"   : "Registration successful. Welcome to Food Rescue!",
  "timestamp" : "2025-12-25 10:30:00",
  "data"      : {
    "user_id"    : 4,
    "name"       : "Rajesh Kumar",
    "email"      : "rajesh@restaurant.com",
    "role"       : "DONOR",
    "created_at" : "25-DEC-25"
  }
}
```

#### ❌ Error – Duplicate Email (409 Conflict):
```json
{
  "success"   : false,
  "message"   : "An account with this email already exists.",
  "timestamp" : "2025-12-25 10:30:05"
}
```

---

### 2️⃣ Login

**Endpoint:** `POST /auth/login.php`

#### Request:
```json
{
  "email"        : "rajesh@restaurant.com",
  "password"     : "SecurePass@123",
  "device_token" : "FCM_DEVICE_TOKEN_FROM_MOBILE_APP"
}
```

#### ✅ Success Response (200 OK):
```json
{
  "success"   : true,
  "message"   : "Login successful. Welcome back, Rajesh Kumar!",
  "timestamp" : "2025-12-25 10:31:00",
  "data"      : {
    "user_id"   : 4,
    "name"      : "Rajesh Kumar",
    "email"     : "rajesh@restaurant.com",
    "role"      : "DONOR",
    "phone"     : "9876543210",
    "latitude"  : 12.9716,
    "longitude" : 77.5946
  }
}
```

#### ❌ Error – Wrong Password (401 Unauthorized):
```json
{
  "success"   : false,
  "message"   : "Invalid email or password.",
  "timestamp" : "2025-12-25 10:31:05"
}
```

---

### 3️⃣ Create Food Alert (DONOR only)

**Endpoint:** `POST /alerts/create_alert.php`

#### Request:
```json
{
  "donor_id"    : 4,
  "food_type"   : "Chicken Biryani",
  "quantity"    : "30 portions",
  "expiry_time" : "2025-12-25 20:00:00",
  "latitude"    : 12.9716,
  "longitude"   : 77.5946
}
```

#### ✅ Success Response (201 Created):
```json
{
  "success"   : true,
  "message"   : "Food alert created successfully. NGOs and volunteers have been notified.",
  "timestamp" : "2025-12-25 15:00:00",
  "data"      : {
    "alert_id"    : 3,
    "donor_id"    : 4,
    "food_type"   : "Chicken Biryani",
    "quantity"    : "30 portions",
    "expiry_time" : "2025-12-25 20:00:00",
    "latitude"    : 12.9716,
    "longitude"   : 77.5946,
    "status"      : "AVAILABLE",
    "created_at"  : "25-DEC-25"
  }
}
```

#### ❌ Error – Wrong Role (403 Forbidden):
```json
{
  "success"   : false,
  "message"   : "Access denied. Only users with DONOR role can create food alerts.",
  "timestamp" : "2025-12-25 15:00:05"
}
```

---

### 4️⃣ Get Nearby Alerts

**Endpoint:** `GET /alerts/get_nearby_alerts.php`

#### Request (Postman → Params tab):
```
latitude  = 12.9716
longitude = 77.5946
radius    = 10
```

**Full URL:**
```
http://localhost/food-rescue-api/alerts/get_nearby_alerts.php?latitude=12.9716&longitude=77.5946&radius=10
```

#### ✅ Success Response (200 OK):
```json
{
  "success"   : true,
  "message"   : "2 food alert(s) found within 10 km.",
  "timestamp" : "2025-12-25 15:05:00",
  "data"      : {
    "search_location" : {
      "latitude"  : 12.9716,
      "longitude" : 77.5946,
      "radius_km" : 10
    },
    "total_found" : 2,
    "alerts"      : [
      {
        "alert_id"    : 3,
        "donor_id"    : 4,
        "donor_name"  : "Rajesh Kumar",
        "donor_phone" : "9876543210",
        "food_type"   : "Chicken Biryani",
        "quantity"    : "30 portions",
        "expiry_time" : "2025-12-25 20:00:00",
        "latitude"    : 12.9716,
        "longitude"   : 77.5946,
        "status"      : "AVAILABLE",
        "created_at"  : "2025-12-25 15:00:00",
        "distance_km" : 0.0
      },
      {
        "alert_id"    : 1,
        "donor_id"    : 1,
        "donor_name"  : "John Donor",
        "donor_phone" : "9876543210",
        "food_type"   : "Rice and Dal",
        "quantity"    : "10 kg",
        "expiry_time" : "2025-12-25 22:00:00",
        "latitude"    : 12.9716,
        "longitude"   : 77.5946,
        "status"      : "AVAILABLE",
        "created_at"  : "2025-12-25 14:00:00",
        "distance_km" : 0.18
      }
    ]
  }
}
```

---

### 5️⃣ Claim a Food Alert (NGO / VOLUNTEER only)

**Endpoint:** `POST /alerts/claim_alert.php`

#### Request:
```json
{
  "alert_id"     : 3,
  "volunteer_id" : 5
}
```

#### ✅ Success Response (200 OK):
```json
{
  "success"   : true,
  "message"   : "Food alert claimed successfully. Please coordinate with the donor for pickup.",
  "timestamp" : "2025-12-25 16:00:00",
  "data"      : {
    "alert_id"       : 3,
    "volunteer_id"   : 5,
    "volunteer_name" : "Priya Sharma",
    "volunteer_role" : "VOLUNTEER",
    "food_type"      : "Chicken Biryani",
    "quantity"        : "30 portions",
    "donor_name"     : "Rajesh Kumar",
    "alert_status"   : "CLAIMED",
    "claim_status"   : "ACTIVE",
    "claimed_at"     : "2025-12-25 16:00:00"
  }
}
```

#### ❌ Error – Already Claimed (409 Conflict):
```json
{
  "success"   : false,
  "message"   : "You have already claimed this food alert.",
  "timestamp" : "2025-12-25 16:00:10"
}
```

---

### 6️⃣ Update Alert Status (COMPLETED / CANCELLED)

**Endpoint:** `POST /alerts/update_status.php`

#### Request – Mark as Completed:
```json
{
  "alert_id"     : 3,
  "volunteer_id" : 5,
  "new_status"   : "COMPLETED"
}
```

#### Request – Cancel a Claim:
```json
{
  "alert_id"     : 3,
  "volunteer_id" : 5,
  "new_status"   : "CANCELLED"
}
```

#### ✅ Success Response – Completed (200 OK):
```json
{
  "success"   : true,
  "message"   : "Status updated successfully. Alert is now marked as COMPLETED.",
  "timestamp" : "2025-12-25 18:00:00",
  "data"      : {
    "alert_id"       : 3,
    "claim_id"       : 1,
    "volunteer_id"   : 5,
    "volunteer_name" : "Priya Sharma",
    "food_type"      : "Chicken Biryani",
    "quantity"       : "30 portions",
    "claim_status"   : "COMPLETED",
    "alert_status"   : "COMPLETED",
    "updated_at"     : "2025-12-25 18:00:00"
  }
}
```

---

## 🧪 Quick Test Flow (Postman Collection Order)

Follow this sequence to test the full happy path:

```
1. POST  /auth/register.php       → Register a DONOR      (note user_id)
2. POST  /auth/register.php       → Register a VOLUNTEER   (note user_id)
3. POST  /auth/login.php          → Login as DONOR
4. POST  /alerts/create_alert.php → Create food alert      (note alert_id)
5. GET   /alerts/get_nearby_alerts.php?latitude=...        → See the alert
6. POST  /auth/login.php          → Login as VOLUNTEER
7. POST  /alerts/claim_alert.php  → Volunteer claims alert
8. POST  /alerts/update_status.php → Mark as COMPLETED
```

---

## 🔒 Security Features

| Feature | Implementation |
|---|---|
| Password Hashing | `password_hash()` with `PASSWORD_BCRYPT` |
| Password Verification | `password_verify()` |
| SQL Injection Prevention | OCI8 `oci_bind_by_name()` (prepared statements) |
| Input Validation | `requireFields()`, type casting, format checks |
| Role-Based Access | Every endpoint checks the user's role |
| Double-Claim Prevention | DB UNIQUE constraint + application-level check |
| Race Condition Handling | `UPDATE ... WHERE status = 'AVAILABLE'` + row count check |
| CORS Headers | `Access-Control-Allow-Origin: *` for mobile apps |
| No Password in Response | Login response explicitly excludes the password field |
| Oracle Transactions | OCI_NO_AUTO_COMMIT + oci_commit/oci_rollback |

---

## 🛠️ Common Errors & Fixes

### ❌ `OCI8 extension not loaded`
```
Solution: Uncomment extension=oci8_19 in php.ini and restart Apache
```

### ❌ `Oracle Connection Error: ORA-12541: No listener`
```
Solution: Start OracleXETNSListener service in Windows Services (services.msc)
```

### ❌ `ORA-01017: invalid username/password`
```
Solution: Update DB_PASSWORD in config/db.php to match your Oracle XE install password
```

### ❌ `ORA-12514: TNS:listener does not know of service requested`
```
Solution: Change DB_CONNECTION_STRING to 'localhost:1521/XE' (for older Oracle XE)
         or keep 'localhost:1521/XEPDB1' (for Oracle XE 21c plug-in database)
```

### ❌ `Call to undefined function oci_connect()`
```
Solution: OCI8 DLL not loaded. Check php.ini and ensure the DLL file exists in the ext/ folder.
         Also confirm the Oracle Instant Client path is in the Windows PATH variable.
```

### ❌ `ORA-00955: name is already used by an existing object`
```
Solution: The tables already exist. Drop them first — the oracle_setup.sql script includes
         DROP TABLE ... CASCADE CONSTRAINTS statements at the top for a clean re-run.
```

### ❌ FCM Notifications not sending
```
Solution:
  1. Confirm FCM_SERVER_KEY is correct in config/db.php
  2. Ensure php_curl extension is enabled in php.ini: extension=curl
  3. Check PHP error_log for "FCM cURL error" or "FCM notification failed" messages
  4. The Legacy FCM HTTP API requires a real device token from a registered Firebase app
```

---

## 📌 Oracle DB Quick Reference

```sql
-- View all registered users
SELECT user_id, name, email, role, created_at FROM users;

-- View all food alerts with donor name
SELECT fa.alert_id, u.name AS donor, fa.food_type, fa.quantity,
       fa.expiry_time, fa.status
FROM   food_alerts fa JOIN users u ON fa.donor_id = u.user_id
ORDER BY fa.created_at DESC;

-- View all claims with volunteer and food details
SELECT c.claim_id, u.name AS volunteer, u.role,
       fa.food_type, fa.quantity, c.status AS claim_status, c.claimed_at
FROM   claims c
JOIN   users       u  ON c.volunteer_id = u.user_id
JOIN   food_alerts fa ON c.alert_id     = fa.alert_id
ORDER BY c.claimed_at DESC;

-- Manually expire old alerts
UPDATE food_alerts SET status = 'EXPIRED'
WHERE  status = 'AVAILABLE' AND expiry_time < SYSDATE;
COMMIT;

-- Reset all test data (without dropping tables)
DELETE FROM claims;
DELETE FROM food_alerts;
DELETE FROM users;
COMMIT;
```

---

## 🔥 Firebase FCM Setup (for Push Notifications)

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project → "Food Rescue"
3. Go to **Project Settings** → **Cloud Messaging** tab
4. Copy the **Server Key** (Legacy)
5. Paste it into `config/db.php`:
   ```php
   define('FCM_SERVER_KEY', 'AAAAxxx...your_key_here');
   ```
6. In your mobile app (Flutter/React Native/Android), initialize Firebase and retrieve the device token
7. Pass the `device_token` during Login or Registration to store it in the DB

---

## 👨‍💻 Author Notes

- **Project Type:** Academic / Portfolio
- **Backend:** Core PHP (no frameworks)
- **Database:** Oracle XE 21c
- **Driver:** OCI8 (PHP Oracle extension)
- **API Style:** RESTful JSON
- **Authentication:** Stateless (user_id passed per request — extend with JWT for production)

---

*Food Rescue – Because good food deserves a second chance.* 🌱
