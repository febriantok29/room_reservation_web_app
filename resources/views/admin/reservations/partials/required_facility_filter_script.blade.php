const initializeRequiredFacilityFilter = ({ allFacilities, prefillFromSelectedRoom = false }) => {
const requiredHiddenEl = document.getElementById('required_facilities_input');
const requiredChipListEl = document.getElementById('required-facility-chip-list');
const requiredToggleEl = document.getElementById('required-facility-toggle');
const requiredSelectorEl = document.getElementById('required-facility-selector');
const requiredOptionsEl = document.getElementById('required-facility-options');
const requiredWrapperEl = document.getElementById('required-facility-wrapper');
const requiredMetaEl = document.getElementById('required-facility-meta');
const roomSelectEl = document.getElementById('room_id');

if (!requiredHiddenEl || !requiredChipListEl || !requiredToggleEl || !requiredSelectorEl || !requiredOptionsEl ||
!requiredWrapperEl || !roomSelectEl) {
return;
}

const normalizeValue = (value) => String(value || '').replace(/\s+/g, ' ').trim();
const normalizeKey = (value) => normalizeValue(value).toLowerCase();
const escapeHtml = (value) => String(value)
.replace(/&/g, '&amp;')
.replace(/</g, '&lt;' ) .replace( />/g, '&gt;')
.replace(/"/g, '&quot;')
.replace(/'/g, '&#39;');

const facilityItems = (Array.isArray(allFacilities) ? allFacilities : [])
.map((facility) => {
if (typeof facility === 'string') {
const name = normalizeValue(facility);
const slug = normalizeKey(name);
if (name === '' || slug === '') {
return null;
}

return { slug, name };
}

const name = normalizeValue(facility?.name || facility?.slug || facility?.id || '');
const slug = normalizeKey(facility?.slug || name);
if (name === '' || slug === '') {
return null;
}

return { slug, name };
})
.filter((facility) => facility !== null);

const facilityBySlug = new Map(
facilityItems.map((facility) => [facility.slug, facility])
);

const currentRoomOption = roomSelectEl.selectedOptions[0] || null;
const selectedRoomFacilities = prefillFromSelectedRoom && currentRoomOption
? String(currentRoomOption.dataset.facilities || '')
.split(',')
.map(normalizeKey)
.filter((slug) => slug !== '' && facilityBySlug.has(slug))
: [];

let selectedFacilitySlugs = (requiredHiddenEl.value || selectedRoomFacilities.join(', '))
.split(',')
.map(normalizeKey)
.filter((slug) => slug !== '' && facilityBySlug.has(slug));
selectedFacilitySlugs = [...new Set(selectedFacilitySlugs)];

const syncHidden = () => {
requiredHiddenEl.value = selectedFacilitySlugs.join(', ');
};

const updateToggleText = () => {
const selectedCount = selectedFacilitySlugs.length;
requiredToggleEl.textContent = selectedCount > 0
? `Pilih Fasilitas (${selectedCount} dipilih)`
: 'Tampilkan Pilihan Fasilitas';
};

const filterRoomsByFacilities = () => {
const selectedFacilities = [...selectedFacilitySlugs];

const roomOptions = Array.from(roomSelectEl.options).filter((option) => option.value !== '');
let visibleRoomCount = 0;

roomOptions.forEach((option) => {
const facilities = String(option.dataset.facilities || '')
.split(',')
.map((item) => normalizeKey(item))
.filter((item) => item !== '');

const matched = selectedFacilities.every((facilitySlug) => facilities.includes(facilitySlug));
option.hidden = !matched;

if (matched) {
visibleRoomCount += 1;
}
});

const selectedOption = roomSelectEl.selectedOptions[0];
if (selectedOption && selectedOption.hidden) {
roomSelectEl.value = '';
}

if (requiredMetaEl) {
requiredMetaEl.textContent = selectedFacilities.length > 0
? `${selectedFacilities.length} fasilitas dipilih • ${visibleRoomCount} ruangan cocok`
: `Belum ada fasilitas dipilih • ${visibleRoomCount} ruangan tersedia`;
}
};

const renderChips = () => {
requiredChipListEl.innerHTML = selectedFacilitySlugs.map((slug) => {
const facility = facilityBySlug.get(slug);
const label = facility?.name || slug;

return `
<span class="badge badge-info mr-2 mb-2 d-inline-flex align-items-center" style="font-size: .875rem;">
    ${escapeHtml(label)}
    <button type="button" class="btn btn-link text-white p-0 ml-2 required-facility-remove"
        data-slug="${escapeHtml(slug)}" style="line-height: 1; font-size: 1rem; text-decoration: none;">&times;</button>
</span>
`;
}).join('');
};

const renderOptions = () => {
if (facilityItems.length === 0) {
requiredOptionsEl.innerHTML = '<div class="col-12 text-muted small">Data fasilitas belum tersedia.</div>';
return;
}

requiredOptionsEl.innerHTML = facilityItems.map((facility) => {
const checked = selectedFacilitySlugs.includes(facility.slug) ? 'checked' : '';

return `
<div class="col-md-6 mb-1">
    <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input required-facility-option"
            id="facility_option_${escapeHtml(facility.slug)}" data-slug="${escapeHtml(facility.slug)}" ${checked}>
        <label class="custom-control-label"
            for="facility_option_${escapeHtml(facility.slug)}">${escapeHtml(facility.name)}</label>
    </div>
</div>
`;
}).join('');
};

const render = () => {
syncHidden();
renderChips();
renderOptions();
updateToggleText();
filterRoomsByFacilities();
};

requiredToggleEl.addEventListener('click', () => {
requiredSelectorEl.classList.toggle('d-none');
});

requiredOptionsEl.addEventListener('change', (event) => {
const checkbox = event.target.closest('.required-facility-option');
if (!checkbox) {
return;
}

const slug = normalizeKey(checkbox.dataset.slug || '');
if (!slug || !facilityBySlug.has(slug)) {
return;
}

if (checkbox.checked) {
if (!selectedFacilitySlugs.includes(slug)) {
selectedFacilitySlugs.push(slug);
}
} else {
selectedFacilitySlugs = selectedFacilitySlugs.filter((item) => item !== slug);
}

render();
});

requiredChipListEl.addEventListener('click', (event) => {
const button = event.target.closest('.required-facility-remove');
if (!button) {
return;
}

const slug = normalizeKey(button.dataset.slug || '');
if (!slug) {
return;
}

selectedFacilitySlugs = selectedFacilitySlugs.filter((item) => item !== slug);
render();
});

document.addEventListener('click', (event) => {
if (!requiredWrapperEl.contains(event.target)) {
requiredSelectorEl.classList.add('d-none');
}
});

render();
};
