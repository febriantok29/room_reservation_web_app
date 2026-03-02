<form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-danger btn-sm">
        <i class="fas fa-sign-out-alt"></i> Keluar
    </button>
</form>
