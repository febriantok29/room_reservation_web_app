<?php

use Illuminate\Support\Facades\Schedule;

// Wires app/Console/Commands/UpdateReservationStatuses.php into Laravel's scheduler.
// Laravel 11+/12 reads scheduled tasks from this file, not App\Console\Kernel::schedule()
// (that class was never bound in bootstrap/app.php, so the schedule silently never ran).
Schedule::command('reservations:update-status')->everyMinute();
