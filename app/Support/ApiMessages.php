<?php

namespace App\Support;

final class ApiMessages
{
    public const SUCCESS_GENERIC = 'Operasi berhasil';
    public const SUCCESS_DATA_RETRIEVED = 'Data berhasil diambil';
    public const SUCCESS_VALIDATION = 'Data yang dikirim tidak valid';

    public const AUTH_INVALID_CREDENTIALS = 'Email/No. Induk Karyawan atau password salah';
    public const AUTH_USER_INACTIVE = 'Akun Anda telah dinonaktifkan';
    public const AUTH_LOGIN_SUCCESS = 'Login berhasil';
    public const AUTH_REFRESH_SUCCESS = 'Token refresh berhasil';
    public const AUTH_LOGOUT_SUCCESS = 'Logout berhasil. Mohon hapus token dari perangkat Anda';
    public const AUTH_ME_SUCCESS = 'Data pengguna berhasil diambil';

    public const UNAUTHORIZED = 'Token tidak valid atau kadaluarsa';
    public const FORBIDDEN = 'Anda tidak memiliki akses ke resource ini';

    public const ROOM_NOT_FOUND = 'Ruangan tidak ditemukan';
    public const ROOM_LIST_SUCCESS = 'Data ruangan berhasil diambil';
    public const ROOM_DETAIL_SUCCESS = 'Detail ruangan berhasil diambil';
    public const ROOM_CREATED_SUCCESS = 'Ruangan berhasil ditambahkan';
    public const ROOM_UPDATED_SUCCESS = 'Ruangan berhasil diperbarui';
    public const ROOM_DELETED_SUCCESS = 'Ruangan berhasil dihapus';
    public const ROOM_AVAILABILITY_SUCCESS = 'Slot tersedia berhasil diambil';
    public const ROOM_UNDER_MAINTENANCE = 'Ruangan sedang dalam perawatan';

    public const FACILITY_NOT_FOUND = 'Fasilitas tidak ditemukan';
    public const FACILITY_LIST_SUCCESS = 'Data fasilitas berhasil diambil';
    public const FACILITY_DETAIL_SUCCESS = 'Detail fasilitas berhasil diambil';
    public const FACILITY_CREATED_SUCCESS = 'Fasilitas berhasil ditambahkan';
    public const FACILITY_UPDATED_SUCCESS = 'Fasilitas berhasil diperbarui';
    public const FACILITY_DELETED_SUCCESS = 'Fasilitas berhasil dihapus';

    public const RESERVATION_NOT_FOUND = 'Reservasi tidak ditemukan';
    public const RESERVATION_LIST_SUCCESS = 'Data reservasi berhasil diambil';
    public const RESERVATION_DETAIL_SUCCESS = 'Detail reservasi berhasil diambil';
    public const RESERVATION_CREATED_SUCCESS = 'Reservasi berhasil dibuat dan menunggu persetujuan';
    public const RESERVATION_UPDATED_SUCCESS = 'Reservasi berhasil diperbarui';
    public const RESERVATION_CANCELLED_SUCCESS = 'Reservasi berhasil dibatalkan';
    public const RESERVATION_APPROVED_SUCCESS = 'Reservasi berhasil disetujui';
    public const RESERVATION_REJECTED_SUCCESS = 'Reservasi berhasil ditolak';

    public const RESERVATION_CONSTRAINT_CREATE_FAILED = 'Reservasi tidak memenuhi aturan penjadwalan';
    public const RESERVATION_CONSTRAINT_UPDATE_FAILED = 'Perubahan reservasi tidak memenuhi aturan penjadwalan';
    public const RESERVATION_CONSTRAINT_APPROVE_FAILED = 'Reservasi tidak dapat disetujui karena melanggar aturan';
    public const RESERVATION_CONSTRAINT_START_BEFORE_END = 'Waktu mulai harus sebelum waktu selesai.';
    public const RESERVATION_CONSTRAINT_PAST_TIME = 'Tidak bisa membuat reservasi pada waktu yang sudah lewat.';
    public const RESERVATION_CONSTRAINT_ROOM_NOT_FOUND = 'Ruangan tidak ditemukan atau sudah dihapus.';
    public const RESERVATION_CONSTRAINT_ROOM_MAINTENANCE = 'Ruangan sedang dalam maintenance.';
    public const RESERVATION_CONSTRAINT_CAPACITY_EXCEEDED = 'Jumlah pengunjung melebihi kapasitas ruangan.';
    public const RESERVATION_CONSTRAINT_SLOT_UNAVAILABLE = 'Slot waktu tidak tersedia karena bentrok dengan reservasi lain.';
    public const RESERVATION_UPDATE_PENDING_ONLY = 'Hanya reservasi dengan status pending yang dapat diubah';
    public const RESERVATION_ALREADY_STARTED = 'Reservasi yang sudah dimulai tidak dapat diubah';
    public const RESERVATION_CANCEL_INVALID_STATUS = 'Status reservasi saat ini tidak dapat dibatalkan';
    public const RESERVATION_ALREADY_FINISHED = 'Reservasi yang sudah selesai tidak dapat dibatalkan';
    public const RESERVATION_APPROVE_PENDING_ONLY = 'Hanya reservasi dengan status pending yang dapat disetujui';
    public const RESERVATION_REJECT_PENDING_ONLY = 'Hanya reservasi dengan status pending yang dapat ditolak';
    public const RESERVATION_END_TIME_AFTER_START = 'Waktu selesai harus setelah waktu mulai';

    public const NO_UPDATE_PAYLOAD = 'Tidak ada data yang dikirim untuk diperbarui';

    private function __construct()
    {
    }
}
