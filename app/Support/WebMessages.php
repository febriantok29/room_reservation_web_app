<?php

namespace App\Support;

final class WebMessages
{
    public const AUTH_INVALID_CREDENTIALS = 'Email/employee_id atau password salah';
    public const AUTH_NO_ADMIN_ACCESS = 'Akun Anda tidak memiliki akses admin';

    public const ROOM_CREATED_SUCCESS = 'Data ruangan berhasil ditambahkan.';
    public const ROOM_UPDATED_SUCCESS = 'Data ruangan berhasil diperbarui.';
    public const ROOM_DELETED_SUCCESS = 'Data ruangan berhasil dihapus.';

    public const RESERVATION_END_AFTER_START = 'Jam selesai harus setelah jam mulai.';
    public const RESERVATION_START_AFTER_NOW = 'Waktu mulai harus lebih dari waktu saat ini.';
    public const RESERVATION_CREATED_SUCCESS = 'Reservasi berhasil ditambahkan.';
    public const RESERVATION_UPDATED_SUCCESS = 'Reservasi berhasil diperbarui.';
    public const RESERVATION_CANCELLED_SUCCESS = 'Reservasi berhasil dibatalkan.';
    public const RESERVATION_APPROVED_SUCCESS = 'Reservasi berhasil disetujui.';
    public const RESERVATION_REJECTED_SUCCESS = 'Reservasi berhasil ditolak.';
    public const RESERVATION_STORE_FAILED = 'Terjadi kesalahan saat menyimpan reservasi.';
    public const RESERVATION_UPDATE_FAILED = 'Terjadi kesalahan saat memperbarui reservasi.';
    public const RESERVATION_INVALID_DATA = 'Terjadi kesalahan pada data reservasi.';

    public const RESERVATION_CONSTRAINT_START_BEFORE_END = 'Waktu mulai harus sebelum waktu selesai.';
    public const RESERVATION_CONSTRAINT_PAST_TIME = 'Tidak bisa membuat reservasi pada waktu yang sudah lewat.';
    public const RESERVATION_CONSTRAINT_ROOM_NOT_FOUND = 'Ruangan tidak ditemukan atau sudah dihapus.';
    public const RESERVATION_CONSTRAINT_ROOM_MAINTENANCE = 'Ruangan sedang dalam maintenance.';
    public const RESERVATION_CONSTRAINT_CAPACITY_EXCEEDED = 'Jumlah pengunjung melebihi kapasitas ruangan.';
    public const RESERVATION_CONSTRAINT_SLOT_UNAVAILABLE = 'Slot waktu tidak tersedia karena bentrok dengan reservasi lain.';
    public const RESERVATION_CONSTRAINT_GENERIC = 'Data reservasi tidak memenuhi aturan penjadwalan.';

    public const RESERVATION_VALIDATION_MESSAGES = [
        'required' => ':attribute wajib diisi.',
        'exists' => ':attribute tidak ditemukan.',
        'date' => ':attribute harus berupa tanggal yang valid.',
        'date_format' => ':attribute harus menggunakan format HH:MM.',
        'integer' => ':attribute harus berupa angka bulat.',
        'min' => ':attribute minimal :min.',
        'max' => ':attribute maksimal :max.',
        'string' => ':attribute harus berupa teks.',
    ];

    public const RESERVATION_VALIDATION_ATTRIBUTES = [
        'user_id' => 'pegawai',
        'room_id' => 'ruangan',
        'reservation_date' => 'tanggal reservasi',
        'start_clock' => 'jam mulai',
        'end_clock' => 'jam selesai',
        'purpose' => 'tujuan',
        'visitor_count' => 'jumlah pengunjung',
    ];

    private function __construct()
    {
    }
}
