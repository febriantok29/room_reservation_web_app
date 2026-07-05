@extends('adminlte::page')

@section('title', 'Rekap Periodik Reservasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Rekap Periodik Reservasi</h1>
            <div class="page-subtitle">Tren reservasi harian, mingguan, atau bulanan.</div>
        </div>
        <div class="d-flex" style="gap:.5rem;">
            <a href="{{ request()->fullUrlWithQuery(['format' => 'excel']) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    {{-- Filter --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.reports.periodic') }}" id="periodicForm">
                <div class="row align-items-end">
                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Tipe Periode</label>
                        <select name="period" id="periodSelect" class="form-control form-control-sm"
                            onchange="toggleMonthField()">
                            <option value="monthly" @selected($period === 'monthly')>Bulanan</option>
                            <option value="weekly" @selected($period === 'weekly')>Mingguan</option>
                            <option value="daily" @selected($period === 'daily')>Harian</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Tahun</label>
                        <select name="year" class="form-control form-control-sm">
                            @for ($y = now()->year; $y >= now()->year - 4; $y--)
                                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0" id="monthField"
                        style="{{ $period === 'daily' ? '' : 'display:none;' }}">
                        <label class="small mb-1">Bulan</label>
                        <select name="month" class="form-control form-control-sm">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($month == $m)>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
                            <a href="{{ route('admin.reports.periodic') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer py-2 text-sm text-muted">
            @if ($period === 'daily')
                Data harian bulan {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}.
            @elseif($period === 'weekly')
                Data mingguan tahun {{ $year }}.
            @else
                Data bulanan tahun {{ $year }}.
            @endif
            &bull; {{ $grouped->count() }} periode ditampilkan.
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>
                                @if ($period === 'daily')
                                    Tanggal
                                @elseif($period === 'weekly')
                                    Minggu
                                @else
                                    Bulan
                                @endif
                            </th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Menunggu</th>
                            <th class="text-center">Disetujui</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Ditolak</th>
                            <th class="text-center">Dibatalkan</th>
                            <th class="text-center">Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $labelKey = match ($period) {
                                'daily' => 'date',
                                'weekly' => 'week',
                                default => 'month',
                            };
                        @endphp
                        @forelse($grouped as $row)
                            <tr>
                                <td class="font-weight-bold">
                                    @if ($period === 'monthly')
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $row[$labelKey])->translatedFormat('F Y') }}
                                    @elseif($period === 'daily')
                                        {{ \Carbon\Carbon::parse($row[$labelKey])->format('d/m/Y') }}
                                    @else
                                        {{ $row[$labelKey] }}
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ $row['total'] }}</td>
                                <td class="text-center">{{ $row['pending'] }}</td>
                                <td class="text-center">{{ $row['approved'] }}</td>
                                <td class="text-center">{{ $row['completed'] }}</td>
                                <td class="text-center">{{ $row['rejected'] }}</td>
                                <td class="text-center">{{ $row['cancelled'] }}</td>
                                <td class="text-center">{{ $row['visitors'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Tidak ada data untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function toggleMonthField() {
            var period = document.getElementById('periodSelect').value;
            document.getElementById('monthField').style.display = (period === 'daily') ? '' : 'none';
        }
        toggleMonthField();
    </script>
@stop
