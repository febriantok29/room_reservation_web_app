{{--
    Delegated delete confirmation handler.
    Forms that should confirm before submit must have:
      data-confirm-delete="Pesan konfirmasi..."
    Message is read via dataset (safe, no JS interpolation).
--}}
<script>
    $(function() {
        $('form[data-confirm-delete]').on('submit', function(e) {
            if (!window.confirm(this.dataset.confirmDelete)) {
                e.preventDefault();
            }
        });
    });
</script>
