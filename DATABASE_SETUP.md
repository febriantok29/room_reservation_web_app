# Room Reservation System - Database Setup

## Overview

Sistem reservasi ruang meeting dengan implementasi **Constraint Satisfaction Problem (CSP)** untuk mencegah konflik waktu booking.

## Database Tables

### 1. s_users (System Users)

Tabel pengguna sistem dengan 3 role: `admin`, `staff`, dan `user`.

**Columns:**

- `id`: Primary key (auto-increment)
- `employee_id`: Employee ID (unique, indexed)
- `email`: Email address (unique, indexed)
- `password`: Hashed password
- `first_name`, `last_name`: User name
- `date_of_birth`: Date of birth
- `role`: Enum (`user`, `staff`, `admin`)
- Audit fields: `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`

### 2. m_rooms (Master Rooms)

Tabel master ruang meeting (4 lantai, 1 ruang per lantai).

**Columns:**

- `id`: Primary key (auto-increment)
- `name`: Room name
- `location`: Location (e.g., "Lantai 1")
- `description`: Room description
- `capacity`: Maximum capacity (smallint)
- `is_maintenance`: Maintenance status (boolean)
- Audit fields: `created_at`, `created_by`, etc.

### 3. t_reservations (Transaction Reservations)

Tabel transaksi reservasi dengan custom ID format.

**Columns:**

- `id`: Primary key (string) - Format: `RSV-YYYYMMDD-XX`
- `user_id`: Foreign key to s_users
- `room_id`: Foreign key to m_rooms
- `start_time`: Reservation start (timestamp UTC)
- `end_time`: Reservation end (timestamp UTC)
- `purpose`: Purpose of reservation
- `visitor_count`: Number of visitors
- `status`: Enum (`pending`, `approved`, `rejected`, `completed`, `cancelled`)
- Audit fields with soft deletes

**Critical Indexes for CSP:**

- `idx_csp_conflict_check`: Composite index on `(room_id, start_time, end_time, status, deleted_at)`
- `idx_user_reservations`: Index on `(user_id, status, deleted_at)`
- `idx_status_time`: Index on `(status, start_time, deleted_at)`

## Setup Instructions

### 1. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=room_reservation
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Seed Database

```bash
php artisan db:seed
```

## Default Users

After seeding, you can login with:

**Admin:**

- Email: `admin@roomreservation.com`
- Password: `Admin@123`

**Staff:**

- Email: `staff1@roomreservation.com`
- Password: `Staff@123`

**User:**

- Email: `sarah.brown@example.com`
- Password: `User@123`

## CSP Implementation

### Constraint Satisfaction Problem (CSP)

CSP Service (`App\Services\CSPService`) implements the following constraints:

1. **Time Range Validation**: Start time < End time
2. **No Past Bookings**: Cannot book in the past
3. **Room Existence**: Room must exist and not deleted
4. **Maintenance Check**: Room must not be in maintenance
5. **Capacity Check**: Visitor count ≤ Room capacity
6. **Time Conflict Prevention**: No overlapping reservations (MAIN CSP CONSTRAINT)

### Time Overlap Detection

A conflict exists when: `(start1 < end2) AND (start2 < end1)`

The system checks 4 overlap scenarios:

1. New reservation starts during existing reservation
2. New reservation ends during existing reservation
3. New reservation completely contains existing reservation
4. Existing reservation completely contains new reservation

### Usage Example

```php
use App\Services\CSPService;
use Carbon\Carbon;

$csp = new CSPService();

// Check if room is available
$available = $csp->isRoomAvailable(
    roomId: 1,
    startTime: Carbon::parse('2026-02-15 09:00:00'),
    endTime: Carbon::parse('2026-02-15 11:00:00')
);

// Validate reservation with all constraints
$validation = $csp->validateReservation(
    roomId: 1,
    startTime: '2026-02-15 09:00:00',
    endTime: '2026-02-15 11:00:00',
    visitorCount: 10
);

if ($validation['valid']) {
    // Proceed with reservation
} else {
    // Show errors: $validation['errors']
}

// Get conflicting reservations
$conflicts = $csp->getConflictingReservations(
    roomId: 1,
    startTime: '2026-02-15 09:00:00',
    endTime: '2026-02-15 11:00:00'
);

// Get available time slots
$slots = $csp->getAvailableTimeSlots(
    roomId: 1,
    date: '2026-02-15',
    intervalMinutes: 30
);
```

## Reservation ID Generator

The `ReservationIdGenerator` service generates unique IDs with format: `RSV-YYYYMMDD-XX`

```php
use App\Services\ReservationIdGenerator;

$id = ReservationIdGenerator::generate(); // RSV-20260213-01
```

## Timezone Considerations

- All timestamps are stored in **UTC (UTC+0)**
- Application should handle timezone conversion for display
- Use Carbon for timezone management:

```php
// Store in UTC
$startTime = Carbon::parse('2026-02-15 09:00:00', 'Asia/Jakarta')->utc();

// Display in local timezone
$displayTime = Carbon::parse($reservation->start_time)->timezone('Asia/Jakarta');
```

## Performance Optimization

1. **Database Indexes**: Multiple indexes for fast CSP conflict checking
2. **Composite Indexes**: Optimized for common query patterns
3. **Query Optimization**: Uses efficient overlap detection algorithm
4. **Soft Deletes**: Indexed for filtering active records

## Next Steps

1. Create Models with relationships
2. Create Controllers for CRUD operations
3. Implement API endpoints for mobile app
4. Create web admin interface
5. Add authentication middleware
6. Implement approval workflow for staff/admin

## License

Educational Project - Thesis/Skripsi
