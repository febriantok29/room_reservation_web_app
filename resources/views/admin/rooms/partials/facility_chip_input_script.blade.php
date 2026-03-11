{{-- Script ini tidak lagi diperlukan.
     Pill checkboxes menggunakan data-toggle="buttons" Bootstrap 4 (jQuery bawaan). --}}
{{-- const initializeRoomFacilitySelect = ({ allFacilities, selectedFacilities }) => {
const $select = $('#facility_ids');

if (!$select.length) {
console.error('Select element #facility_ids not found');
return;
}

console.log('Initializing facility select with:', {
allFacilitiesCount: allFacilities?.length,
selectedFacilitiesCount: selectedFacilities?.length,
allFacilities: allFacilities
});

// Clear existing options
$select.empty();

// Add facilities as options
if (Array.isArray(allFacilities) && allFacilities.length > 0) {
allFacilities.forEach(facility => {
const name = typeof facility === 'string' ? facility : (facility.name || facility.slug || '');
const slug = typeof facility === 'string' ? facility : (facility.slug || facility.name || '');

if (name) {
const option = new Option(name, slug, false, false);
$select.append(option);
}
});
console.log('Added', allFacilities.length, 'facility options');
} else {
console.warn('No facilities data provided or allFacilities is not an array');
}

// Initialize Select2
$select.select2({
theme: 'bootstrap4',
placeholder: 'Pilih satu atau lebih fasilitas untuk ruangan ini.',
allowClear: true,
width: '100%',
closeOnSelect: false,
tags: true,
tokenSeparators: [','],
createTag: function (params) {
const term = $.trim(params.term);

if (term === '') {
return null;
}

return {
id: term,
text: term + ' (Baru)',
newOption: true
};
}
});

// Set selected values if provided
if (selectedFacilities && selectedFacilities.length > 0) {
const selectedValues = selectedFacilities.map(f =>
typeof f === 'string' ? f : (f.slug || f.name || '')
);
$select.val(selectedValues).trigger('change');
console.log('Set selected values:', selectedValues);
}
};
