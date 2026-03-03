@extends('adminlte::page')

@section('title', 'Ubah Fasilitas')

@section('content_header')
    <div>
        <h1 class="m-0">Ubah Fasilitas</h1>
        <div class="page-subtitle">Perbarui nama fasilitas agar konsisten dipakai di seluruh data ruangan.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.facilities.update', $facility->id) }}" method="PUT" loading-text="Memperbarui...">
        <x-form.section title="Informasi Fasilitas">
            <x-form.row>
                <x-form.field name="name" label="Nama Fasilitas" type="text" :value="old('name', $facility->name)" required />

                <x-form.field name="slug" label="Slug (Auto-generated)" type="text" :value="$facility->slug"
                    hint="Slug dihasilkan otomatis dari nama fasilitas." disabled />
            </x-form.row>

            @if ($facility->rooms->isNotEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Fasilitas ini digunakan di <strong>{{ $facility->rooms->count() }} ruangan</strong>. Perubahan nama
                    akan mempengaruhi tampilan di semua ruangan tersebut.
                </div>
            @endif
        </x-form.section>

        <x-form.actions back-url="{{ route('admin.facilities') }}" submit-text="Perbarui" />
    </x-form.card>
@stop

@section('js')
    @include('admin.partials.form_submit_guard_script')
@stop
