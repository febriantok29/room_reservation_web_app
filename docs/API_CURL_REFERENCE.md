# Room Reservation API - cURL Command Reference

**Base URL:** `http://127.0.0.1:8000`

## Test Credentials

| Role         | Email                        | Password    |
| ------------ | ---------------------------- | ----------- |
| Admin        | `admin@roomreservation.com`  | `Admin@123` |
| Staff        | `staff1@roomreservation.com` | `Staff@123` |
| Regular User | `sarah.brown@example.com`    | `User@123`  |

---

## 1. LOGIN ENDPOINTS

### 🔓 Login with Valid Credentials (Admin)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@roomreservation.com",
    "password": "Admin@123"
  }'
```

**Expected Response (200 OK):**

```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "Bearer",
        "expires_in": 900,
        "is_debug": false
    }
}
```

### 🔓 Login with Valid Credentials (Staff)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "staff1@roomreservation.com",
    "password": "Staff@123"
  }'
```

### 🔓 Login with Valid Credentials (Regular User)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "sarah.brown@example.com",
    "password": "User@123"
  }'
```

### ❌ Login with Invalid Credentials (Wrong Password)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@roomreservation.com",
    "password": "WrongPassword123"
  }'
```

**Expected Response (401 UNAUTHORIZED):**

```json
{
    "success": false,
    "error_code": "INVALID_CREDENTIALS",
    "message": "Email atau password salah"
}
```

### ❌ Login with Invalid Email

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "nonexistent@example.com",
    "password": "Admin@123"
  }'
```

**Expected Response (401 UNAUTHORIZED):**

```json
{
    "success": false,
    "error_code": "INVALID_CREDENTIALS",
    "message": "Email atau password salah"
}
```

### ❌ Login with Missing Email (Validation Error)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "password": "Admin@123"
  }'
```

**Expected Response (422 UNPROCESSABLE ENTITY):**

```json
{
    "success": false,
    "error_code": "VALIDATION_ERROR",
    "message": "Data yang dikirim tidak valid",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### ❌ Login with Missing Password

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@roomreservation.com"
  }'
```

### 🔧 Login with Debug Mode (Custom TTL)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@roomreservation.com",
    "password": "Admin@123",
    "is_debug": true,
    "access_token_ttl": 60,
    "refresh_token_ttl": 3600
  }'
```

**Expected Response (200 OK):**

```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "access_token": "...",
        "refresh_token": "...",
        "token_type": "Bearer",
        "expires_in": 60,
        "is_debug": true
    }
}
```

> **Note:** `expires_in` akan menjadi 60 detik (bukan default 900 detik)

### 🔧 Login with Debug Mode (Override Access Token Only)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@roomreservation.com",
    "password": "Admin@123",
    "is_debug": true,
    "access_token_ttl": 300
  }'
```

> **Note:** Access token expires in 300 seconds (5 minutes), refresh token uses default 7 days

## 2. TOKEN REFRESH ENDPOINTS

### 🔄 Refresh Access Token (Valid Refresh Token)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDAxMjM0NTUsImV4cCI6MTc0MDcyODI1NSwi..."
  }'
```

**Expected Response (200 OK):**

```json
{
    "success": true,
    "message": "Token refresh berhasil",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "Bearer",
        "expires_in": 900,
        "is_debug": false
    }
}
```

### 🔄 Refresh Token with Debug Mode (Override Access Token TTL)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDAxMjM0NTUsImV4cCI6MTc0MDcyODI1NSwi...",
    "is_debug": true,
    "access_token_ttl": 180
  }'
```

**Expected Response (200 OK):**

```json
{
    "success": true,
    "message": "Token refresh berhasil",
    "data": {
        "access_token": "...",
        "token_type": "Bearer",
        "expires_in": 180,
        "is_debug": true
    }
}
```

### ❌ Refresh Token with Invalid Token

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "invalid.jwt.token.not.valid"
  }'
```

**Expected Response (401 UNAUTHORIZED):**

```json
{
    "success": false,
    "error_code": "UNAUTHORIZED",
    "message": "Token tidak valid atau kadaluarsa"
}
```

### ❌ Refresh with Missing Refresh Token

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{}'
```

**Expected Response (422 UNPROCESSABLE ENTITY):**

```json
{
    "success": false,
    "error_code": "VALIDATION_ERROR",
    "message": "Data yang dikirim tidak valid",
    "errors": {
        "refresh_token": ["The refresh_token field is required."]
    }
}
```

---

## 3. PROTECTED ENDPOINTS (Require Valid Access Token)

### 👤 Get Current User Information (Me)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/auth/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDAxMjM0NTUsImV4cCI6MTc0MDEyMzPM..."
```

**Expected Response (200 OK):**

```json
{
    "success": true,
    "message": "Data pengguna berhasil diambil",
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Administrator",
        "email": "admin@roomreservation.com",
        "employee_id": "EMP-2026-00001",
        "role": "admin",
        "is_active": true
    }
}
```

### ❌ Get Current User without Token

```bash
curl -X GET http://127.0.0.1:8000/api/v1/auth/me
```

**Expected Response (401 UNAUTHORIZED):**

```json
{
    "success": false,
    "error_code": "UNAUTHORIZED",
    "message": "Token tidak valid atau kadaluarsa"
}
```

### ❌ Get Current User with Invalid Token

```bash
curl -X GET http://127.0.0.1:8000/api/v1/auth/me \
  -H "Authorization: Bearer invalid.token.here"
```

**Expected Response (401 UNAUTHORIZED):**

```json
{
    "success": false,
    "error_code": "UNAUTHORIZED",
    "message": "Token tidak valid atau kadaluarsa"
}
```

### ❌ Get Current User with Missing 'Bearer' prefix

```bash
curl -X GET http://127.0.0.1:8000/api/v1/auth/me \
  -H "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDAxMjM0NTUsImV4cCI6MTc0MDEyMzPM..."
```

**Expected Response (401 UNAUTHORIZED):**

```json
{
    "success": false,
    "error_code": "UNAUTHORIZED",
    "message": "Token tidak valid atau kadaluarsa"
}
```

### 🚪 Logout (Client-side should delete tokens)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/logout \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDAxMjM0NTUsImV4cCI6MTc0MDEyMzPM..."
```

**Expected Response (200 OK):**

```json
{
    "success": true,
    "message": "Logout berhasil. Mohon hapus token dari perangkat Anda",
    "data": null
}
```

> **Note:** Dalam implementasi JWT stateless, logout hanya untuk konfirmasi. Client harus menghapus token dari local storage/secure storage.

---

## 4. Quick Reference - Bash Script untuk Testing

```bash
#!/bin/bash

# Login dan simpan tokens
echo "🔓 Logging in..."
RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@roomreservation.com",
    "password": "Admin@123"
  }')

ACCESS_TOKEN=$(echo $RESPONSE | jq -r '.data.access_token')
REFRESH_TOKEN=$(echo $RESPONSE | jq -r '.data.refresh_token')

echo "Access Token: $ACCESS_TOKEN"
echo "Refresh Token: $REFRESH_TOKEN"

# Get current user
echo -e "\n👤 Getting current user..."
curl -s -X GET http://127.0.0.1:8000/api/v1/auth/me \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq .

# Refresh token
echo -e "\n🔄 Refreshing token..."
NEW_RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d "{\"refresh_token\": \"$REFRESH_TOKEN\"}")

NEW_ACCESS_TOKEN=$(echo $NEW_RESPONSE | jq -r '.data.access_token')
echo "New Access Token: $NEW_ACCESS_TOKEN"

# Logout
echo -e "\n🚪 Logging out..."
curl -s -X POST http://127.0.0.1:8000/api/v1/auth/logout \
  -H "Authorization: Bearer $NEW_ACCESS_TOKEN" | jq .
```

---

## 5. Environment Variables (untuk Postman/Development)

| Variable             | Value                            | Description                            |
| -------------------- | -------------------------------- | -------------------------------------- |
| `base_url`           | `http://127.0.0.1:8000`          | Base URL untuk API                     |
| `access_token`       | (diisi otomatis setelah login)   | Access token untuk protected endpoints |
| `refresh_token`      | (diisi otomatis setelah login)   | Refresh token untuk refresh endpoint   |
| `staff_access_token` | (diisi saat login sebagai staff) | Staff's access token                   |
| `user_access_token`  | (diisi saat login sebagai user)  | User's access token                    |

---

## 6. Response Format Reference

### ✅ Success Response Format

```json
{
    "success": true,
    "message": "Pesan informatif dalam Bahasa Indonesia",
    "data": {
        /* response data */
    },
    "metadata": {
        /* optional: pagination, etc */
    }
}
```

### ❌ Error Response Format

```json
{
    "success": false,
    "error_code": "MACHINE_READABLE_CODE",
    "message": "Pesan error dalam Bahasa Indonesia",
    "errors": {
        /* optional: validation errors */
    }
}
```

### Common Error Codes

| Error Code            | HTTP Status | Message                               |
| --------------------- | ----------- | ------------------------------------- |
| `INVALID_CREDENTIALS` | 401         | Email atau password salah             |
| `UNAUTHORIZED`        | 401         | Token tidak valid atau kadaluarsa     |
| `FORBIDDEN`           | 403         | Tidak memiliki akses ke resource      |
| `VALIDATION_ERROR`    | 422         | Data yang dikirim tidak valid         |
| `NOT_FOUND`           | 404         | Resource tidak ditemukan              |
| `USER_INACTIVE`       | 403         | Akun telah dinonaktifkan              |
| `INVALID_TOKEN_TYPE`  | 401         | Token yang dikirim bukan access token |

---

## 7. Debugging Tips

- **💡 Decode JWT Token (online):** Visit https://jwt.io dan paste token Anda
- **💡 Pretty-print JSON in cURL:** Tambahkan `| jq .` di akhir curl command
- **💡 Check token expiration:**
    - Access token: 900 detik (15 menit)
    - Refresh token: 604800 detik (7 hari)
- **💡 Debug Mode Testing:** Set `is_debug=true` dan custom TTL values untuk test token refresh dengan cepat
- **💡 View full request-response:** Tambahkan flag `-v` ke curl: `curl -v ...`
