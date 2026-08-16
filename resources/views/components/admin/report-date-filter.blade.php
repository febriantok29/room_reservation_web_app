@props(['action', 'dateFrom' => '', 'dateTo' => ''])

<div class="card card-admin mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ $action }}">
            <div class="row align-items-end">
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                    <label class="small mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                    <label class="small mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                @if ($slot->isNotEmpty())
                    <div class="col-lg-4 col-md-4 mb-2 mb-lg-0">
                        {{ $slot }}
                    </div>
                @endif
                <div class="col-lg-{{ $slot->isNotEmpty() ? '4' : '8' }} col-md-12">
                    <div class="d-flex" style="gap:.5rem;">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ $action }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
