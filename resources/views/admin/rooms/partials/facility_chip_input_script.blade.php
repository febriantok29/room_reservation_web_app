const initializeRoomFacilityChipInput = ({ allFacilities }) => {
const hiddenInput = document.getElementById('facility_ids_input');
const textInput = document.getElementById('facility_input');
const chipList = document.getElementById('facility-chip-list');
const suggestionMenu = document.getElementById('facility-suggestion-menu');
const wrapper = document.getElementById('facility-chip-wrapper');

if (!hiddenInput || !textInput || !chipList || !suggestionMenu || !wrapper) {
return;
}

const normalize = (value) => String(value || '').replace(/\s+/g, ' ').trim();
const normalizeKey = (value) => normalize(value).toLowerCase();
const escapeHtml = (value) => String(value)
.replace(/&/g, '&amp;')
.replace(/</g, '&lt;' ) .replace( />/g, '&gt;')
.replace(/"/g, '&quot;')
.replace(/'/g, '&#39;');

const facilityNames = (Array.isArray(allFacilities) ? allFacilities : [])
.map((facility) => {
if (typeof facility === 'string') {
return normalize(facility);
}

return normalize(facility?.name || facility?.slug || facility?.id || '');
})
.filter((name) => name !== '');

let suggestionItems = [];
let activeSuggestionIndex = -1;
let hasInteracted = false;

let selected = (hiddenInput.value || '')
.split(',')
.map(normalize)
.filter((value) => value.length > 0);

const syncHidden = () => {
hiddenInput.value = selected.join(', ');
};

const removeChip = (index) => {
selected.splice(index, 1);
render();
};

const addChip = (value) => {
const cleaned = normalize(value);
if (!cleaned) {
return;
}

const exists = selected.some((item) => normalizeKey(item) === normalizeKey(cleaned));
if (exists) {
return;
}

selected.push(cleaned);
textInput.value = '';
render();
};

const hideSuggestions = () => {
suggestionMenu.classList.add('d-none');
suggestionMenu.innerHTML = '';
suggestionItems = [];
activeSuggestionIndex = -1;
};

const setActiveSuggestion = (index) => {
activeSuggestionIndex = index;

const buttons = suggestionMenu.querySelectorAll('button[data-name]');
buttons.forEach((button, buttonIndex) => {
if (buttonIndex === activeSuggestionIndex) {
button.classList.add('active');
button.scrollIntoView({
block: 'nearest',
});
} else {
button.classList.remove('active');
}
});
};

const selectActiveSuggestion = () => {
if (activeSuggestionIndex < 0 || activeSuggestionIndex>= suggestionItems.length) {
    return false;
    }

    addChip(suggestionItems[activeSuggestionIndex]);
    return true;
    };

    const highlightMatch = (name, query) => {
    if (!query) {
    return escapeHtml(name);
    }

    const source = String(name);
    const sourceLower = source.toLowerCase();
    const start = sourceLower.indexOf(query);

    if (start < 0) { return escapeHtml(source); } const end=start + query.length; const
        before=escapeHtml(source.slice(0, start)); const matched=escapeHtml(source.slice(start, end)); const
        after=escapeHtml(source.slice(end)); return `${before}<mark class="p-0 bg-warning text-dark">
        ${matched}</mark>${after}`;
        };

        const showSuggestions = () => {
        const query = normalizeKey(textInput.value);

        if (query.length === 0) {
        hideSuggestions();
        return;
        }

        suggestionItems = facilityNames.filter((name) => {
        const nameKey = normalizeKey(name);
        const isSelected = selected.some((item) => normalizeKey(item) === nameKey);
        if (isSelected) {
        return false;
        }

        return nameKey.includes(query);
        });

        if (suggestionItems.length === 0) {
        suggestionMenu.innerHTML =
                '<div class="list-group-item text-muted small">Tidak ada suggestion fasilitas</div>';
        suggestionMenu.classList.remove('d-none');
        activeSuggestionIndex = -1;
        return;
        }

        const summary =
        `<div class="list-group-item text-muted small py-1">${suggestionItems.length} suggestion ditemukan</div>`;

        suggestionMenu.innerHTML = summary + suggestionItems.map((name) => {
        const label = highlightMatch(name, query);
        return `<button type="button" class="list-group-item list-group-item-action"
            data-name="${escapeHtml(name)}">${label}</button>`;
        }).join('');

        suggestionMenu.classList.remove('d-none');
        setActiveSuggestion(0);
        };

        const openSuggestions = () => {
        hasInteracted = true;
        showSuggestions();
        };

        const render = () => {
        chipList.innerHTML = selected.map((name, index) => `
        <span class="badge badge-primary mr-2 mb-2 d-inline-flex align-items-center" style="font-size: .875rem;">
            ${escapeHtml(name)}
            <button type="button" class="btn btn-link text-white p-0 ml-2 facility-chip-remove" data-index="${index}"
                style="line-height: 1; font-size: 1rem; text-decoration: none;">&times;</button>
        </span>
        `).join('');

        syncHidden();
        if (hasInteracted) {
        showSuggestions();
        } else {
        hideSuggestions();
        }
        };

        textInput.addEventListener('input', openSuggestions);

        textInput.addEventListener('keydown', (event) => {
        const isSuggestionOpen = !suggestionMenu.classList.contains('d-none');

        if (event.key === 'ArrowDown' && isSuggestionOpen && suggestionItems.length > 0) {
        event.preventDefault();
        const nextIndex = activeSuggestionIndex < suggestionItems.length - 1 ? activeSuggestionIndex + 1 : 0;
            setActiveSuggestion(nextIndex); return; } if (event.key === 'ArrowUp' && isSuggestionOpen &&
            suggestionItems.length> 0) {
            event.preventDefault();
            const nextIndex = activeSuggestionIndex > 0 ? activeSuggestionIndex - 1 : suggestionItems.length - 1;
            setActiveSuggestion(nextIndex);
            return;
            }

            if (event.key === 'Enter' || event.key === ',') {
            event.preventDefault();

            if (event.key === 'Enter' && isSuggestionOpen && selectActiveSuggestion()) {
            return;
            }

            addChip(textInput.value);
            return;
            }

            if (event.key === 'Escape' && isSuggestionOpen) {
            event.preventDefault();
            hideSuggestions();
            }
            });

            textInput.addEventListener('focus', openSuggestions);

            suggestionMenu.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-name]');
            if (!button) {
            return;
            }

            addChip(button.dataset.name || '');
            textInput.focus();
            });

            suggestionMenu.addEventListener('mouseover', (event) => {
            const button = event.target.closest('button[data-name]');
            if (!button) {
            return;
            }

            const buttons = Array.from(suggestionMenu.querySelectorAll('button[data-name]'));
            const hoverIndex = buttons.indexOf(button);
            if (hoverIndex >= 0) {
            setActiveSuggestion(hoverIndex);
            }
            });

            chipList.addEventListener('click', (event) => {
            const button = event.target.closest('.facility-chip-remove');
            if (!button) {
            return;
            }

            const index = Number(button.dataset.index);
            if (!Number.isNaN(index)) {
            removeChip(index);
            }
            });

            document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) {
            hideSuggestions();
            }
            });

            render();
            };
