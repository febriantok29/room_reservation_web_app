@extends('adminlte::page')

@section('title', 'Laporan Status Maintenance Ruangan')

<x-admin.report-header title="Laporan Status Maintenance Ruangan" subtitle="Kondisi ruangan beserta riwayat komplain dan status maintenance." />

@section('content')
    @include('admin.partials.flash_message')

    {{-- Filter --}}
    <x-admin.report-date-filter action="{{ route('admin.reports.maintenance') }}" :date-from="$dateFrom ?? ''" :date-to="$dateTo ?? ''">
        <label class="small mb-1 d-block">Ruangan</label>
        @include('admin.reports.partials.room_filter_pills', [
            'rooms' => $allRoomsList,
            'selected' => $roomFilters ?? [],
        ])
    </x-admin.report-date-filter>

    {{-- Room Table --}}
    <div class="card card-admin">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Data Ruangan</h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ $rooms->count() }} ruangan</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ruangan</th>
                            <th>Lantai</th>
                            <th class="text-center">Kapasitas</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Total Komplain</th>
                            <th class="text-center">Terbuka</th>
                            <th class="text-center">Dikerjakan</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Ditolak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $room)
                            <tr>
                                <td class="font-weight-bold">{{ $room['name'] }}</td>
                                <td>{{ $room['floor'] }}</td>
                                <td class="text-center">{{ $room['capacity'] }}</td>
                                <td class="text-center">
                                    @if ($room['is_maintenance'])
                                        <span class="badge badge-danger"><i class="fas fa-tools mr-1"></i>Maintenance</span>
                                    @else
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Normal</span>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ $room['total_complaints'] }}</td>
                                <td class="text-center">
                                    @if ($room['open'] > 0)
                                        <span class="badge badge-warning">{{ $room['open'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($room['in_progress'] > 0)
                                        <span class="badge badge-info">{{ $room['in_progress'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($room['resolved'] > 0)
                                        <span class="badge badge-success">{{ $room['resolved'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($room['rejected'] > 0)
                                        <span class="badge badge-danger">{{ $room['rejected'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">Tidak ada data ruangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
