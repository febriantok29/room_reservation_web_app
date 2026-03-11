const initializeRequiredFacilityFilter = () => {
const filterGroup = document.getElementById('facility-filter-group');
const roomSelectEl = document.getElementById('room_id');
const countLabel = document.getElementById('room-count-label');

if (!filterGroup || !roomSelectEl) return;

const totalRooms = Array.from(roomSelectEl.options).filter(o => o.value !== '').length;

const filterRooms = () => {
const selected = Array.from(filterGroup.querySelectorAll('input[type="checkbox"]:checked'))
.map(cb => cb.dataset.slug);

let visibleCount = 0;

Array.from(roomSelectEl.options)
.filter(o => o.value !== '')
.forEach(option => {
const roomFacilities = String(option.dataset.facilities || '')
.split(',').map(s => s.trim()).filter(Boolean);

const hidden = selected.length > 0 &&
!selected.every(slug => roomFacilities.includes(slug));

option.hidden = hidden;
if (!hidden) visibleCount++;
});

// Hapus pilihan ruangan jika sekarang tersembunyi
const current = roomSelectEl.options[roomSelectEl.selectedIndex];
if (current && current.value && current.hidden) {
roomSelectEl.value = '';
}

// Update keterangan jumlah ruangan
if (countLabel) {
if (selected.length === 0) {
countLabel.textContent = '';
} else if (visibleCount === 0) {
countLabel.textContent = '— Tidak ada ruangan yang sesuai.';
countLabel.className = 'ml-1 font-weight-semibold text-danger';
} else {
countLabel.textContent = '— ' + visibleCount + ' ruangan tersedia.';
countLabel.className = 'ml-1 font-weight-semibold text-success';
}
}
};

filterGroup.addEventListener('change', filterRooms);
filterRooms(); // filter awal (kosong = tampilkan semua)
};
