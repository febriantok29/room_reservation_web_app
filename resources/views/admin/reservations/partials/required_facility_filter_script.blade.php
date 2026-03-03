const initializeRequiredFacilityFilter = ({ allFacilities }) => {
const $requiredFacilities = $('#required_facilities');
const roomSelectEl = document.getElementById('room_id');

if (!$requiredFacilities.length || !roomSelectEl) {
console.error('Required elements not found:', {
hasSelect: $requiredFacilities.length > 0,
hasRoomSelect: !!roomSelectEl
});
return;
}

console.log('Initializing facility filter with:', {
allFacilitiesCount: allFacilities?.length,
allFacilities: allFacilities
});

// Prepare data for Select2
const selectData = Array.isArray(allFacilities) && allFacilities.length > 0
? allFacilities.map(facility => ({
id: facility.slug,
text: facility.name
}))
: [];

console.log('Select2 data prepared:', selectData.length, 'items');

// Initialize Select2
$requiredFacilities.select2({
theme: 'bootstrap4',
placeholder: 'Klik untuk memilih fasilitas...',
allowClear: true,
closeOnSelect: false,
width: '100%',
data: selectData
});

// Filter rooms based on selected facilities
const filterRoomsByFacilities = () => {
const selectedFacilities = $requiredFacilities.val() || [];
const roomOptions = Array.from(roomSelectEl.options).filter(option => option.value !== '');

let visibleRoomCount = 0;

roomOptions.forEach(option => {
const roomFacilities = String(option.dataset.facilities || '')
.split(',')
.map(item => item.trim().toLowerCase())
.filter(item => item !== '');

const matched = selectedFacilities.length === 0 ||
selectedFacilities.every(slug => roomFacilities.includes(slug));

option.hidden = !matched;
if (matched) visibleRoomCount++;
});

// Clear room selection if it's now hidden
const selectedOption = roomSelectEl.selectedOptions[0];
if (selectedOption && selectedOption.hidden) {
roomSelectEl.value = '';
}

console.log('Room filtering:', {
selectedFacilities,
visibleRoomCount,
totalRooms: roomOptions.length
});
};

// Listen for changes and filter rooms
$requiredFacilities.on('change', filterRoomsByFacilities);

// Initial filter
filterRoomsByFacilities();
};
