<?php

use Illuminate\Support\Facades\Schedule;

// Scheduler otomatis untuk reservasi DIHAPUS sengaja.
//
// Sebelumnya:
//   Schedule::command('reservations:update-status')->everyMinute();
//
// Transisi status (pending→cancelled, approved→completed) sekarang hanya
// berjalan saat halaman/API diakses (lihat ReservationService::autoTransition()
// yang dipanggil di dashboard, reservations, approvals, calendar, dan API list)
// ATAU via "Debug Feature" di web admin.
//
// Tujuannya agar alur auto-transition mudah dikontrol & dijelaskan saat sidang.
