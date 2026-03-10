<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class UpdateReservationStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pending reservations and complete approved ones whose time has passed';

    private ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        parent::__construct();

        $this->reservationService = $reservationService;
    }

    public function handle(): int
    {
        $counts = $this->reservationService->autoTransition();
        $this->info("Auto-transition results: expired={$counts['expired']}, completed={$counts['completed']}");

        return 0;
    }
}
