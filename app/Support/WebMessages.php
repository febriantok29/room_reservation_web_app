<?php

namespace App\Support;

final class WebMessages
{
    public const AUTH_INVALID_CREDENTIALS = 'Email/No. Induk Karyawan atau password salah';
    public const AUTH_NO_ADMIN_ACCESS = 'Akun Anda tidak memiliki akses admin';

    public const ROOM_CREATED_SUCCESS = 'Data ruangan berhasil ditambahkan.';
    public const ROOM_UPDATED_SUCCESS = 'Data ruangan berhasil diperbarui.';
    public const ROOM_DELETED_SUCCESS = 'Data ruangan berhasil dihapus.';
    public const ROOM_IMAGE_DELETED_SUCCESS = 'Foto ruangan berhasil dihapus.';

    public const FACILITY_CREATED_SUCCESS = 'Data fasilitas berhasil ditambahkan.';
    public const FACILITY_UPDATED_SUCCESS = 'Data fasilitas berhasil diperbarui.';
    public const FACILITY_DELETED_SUCCESS = 'Data fasilitas berhasil dihapus.';
    public const FACILITY_DUPLICATE_NAME = 'Nama fasilitas sudah digunakan.';

    public const USER_UPDATED_SUCCESS = 'Data karyawan berhasil diperbarui.';
    public const USER_DELETED_SUCCESS = 'Data karyawan berhasil dihapus.';
    public const USER_CANNOT_MODIFY_SELF = 'Anda tidak dapat menonaktifkan atau menghapus akun sendiri.';
    public const FACILITY_INVALID_NAME = 'Nama fasilitas tidak valid.';

    public const RESERVATION_END_AFTER_START = 'Jam selesai harus setelah jam mulai.';
    public const RESERVATION_START_AFTER_NOW = 'Waktu mulai harus lebih dari waktu saat ini.';
    public const RESERVATION_CREATED_SUCCESS = 'Reservasi berhasil ditambahkan.';
    public const RESERVATION_UPDATED_SUCCESS = 'Reservasi berhasil diperbarui.';
    public const RESERVATION_CANCELLED_SUCCESS = 'Reservasi berhasil dibatalkan.';
    public const RESERVATION_COMPLETED_SUCCESS = 'Reservasi berhasil diselesaikan.';
    public const RESERVATION_APPROVED_SUCCESS = 'Reservasi berhasil disetujui.';
    public const RESERVATION_REJECTED_SUCCESS = 'Reservasi berhasil ditolak.';
    public const RESERVATION_COMPLETE_INVALID_STATUS = 'Reservasi tidak dapat ditandai selesai.';
    public const RESERVATION_NOT_FINISHED = 'Reservasi belum berakhir.';
    public const RESERVATION_STORE_FAILED = 'Terjadi kesalahan saat menyimpan reservasi.';
    public const RESERVATION_UPDATE_FAILED = 'Terjadi kesalahan saat memperbarui reservasi.';
    public const RESERVATION_INVALID_DATA = 'Terjadi kesalahan pada data reservasi.';

    public const COMPLAINT_STATUS_IN_PROGRESS = 'Status komplain diperbarui: sedang dalam proses penanganan.';
    public const COMPLAINT_STATUS_RESOLVED = 'Komplain berhasil diselesaikan.';
    public const COMPLAINT_STATUS_REJECTED = 'Komplain telah ditolak.';
    public const COMPLAINT_ALREADY_CLOSED = 'Komplain ini sudah ditutup dan tidak dapat diubah.';
    public const COMPLAINT_DELETED_SUCCESS = 'Data komplain berhasil dihapus.';

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
