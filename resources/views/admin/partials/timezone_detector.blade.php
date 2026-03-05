@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Detect user timezone and set in session
            const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

            // Only set if not already set or different
            const currentTimezone = @json(session('user_timezone'));
            if (!currentTimezone || currentTimezone !== userTimezone) {
                fetch('{{ route('admin.set-timezone') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') ||
                            '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        timezone: userTimezone
                    })
                }).then(response => {
                    if (response.ok) {
                        console.log('Timezone set to:', userTimezone);
                    }
                }).catch(console.error);
            }
        });
    </script>
@endpush
