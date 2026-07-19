import jQuery from 'jquery';
import select2Factory from 'select2';

window.jQuery = window.$ = jQuery;

if (typeof select2Factory === 'function') {
    select2Factory(window, jQuery);
}

const select2Language = {
    noResults: () => 'نتیجه‌ای یافت نشد',
    searching: () => 'در حال جستجو...',
    inputTooShort: () => 'عبارت بیشتری وارد کنید',
};

function parseCitiesByState(raw) {
    if (! raw) {
        return {};
    }

    try {
        return JSON.parse(raw);
    } catch {
        return {};
    }
}

function placeholderFromSelect(select) {
    return select.querySelector('option[value=""]')?.textContent?.trim() || '';
}

function keepsEmptyOptionSelectable(select) {
    return select.matches(
        [
            '[data-listing-state]',
            '[data-listing-city]',
            '[data-repair-locator-state]',
            '[data-repair-locator-city]',
            '[data-shops-filter-state]',
            '[data-vehicle-company]',
            '[data-vehicle-car]',
            '[data-vehicle-model]',
        ].join(', '),
    );
}

function resolveDropdownParent(select) {
    const panel = select.closest('[data-ps-modal-panel]');

    if (panel) {
        return jQuery(panel);
    }

    const dialog = select.closest('dialog[data-ps-modal], dialog');

    if (dialog) {
        return jQuery(dialog);
    }

    return null;
}

function initSearchableSelect(select) {
    const $select = jQuery(select);

    if ($select.hasClass('select2-hidden-accessible')) {
        return $select;
    }

    const wrapper = select.closest('.ps-searchable-select');

    if (wrapper === null) {
        const container = document.createElement('div');
        container.className = 'ps-searchable-select';
        select.parentNode?.insertBefore(container, select);
        container.appendChild(select);
    }

    if (typeof $select.select2 !== 'function') {
        return $select;
    }

    const keepEmptyOption = keepsEmptyOptionSelectable(select);
    const dropdownParent = resolveDropdownParent(select);
    const options = {
        width: '100%',
        dir: 'rtl',
        allowClear: false,
        minimumResultsForSearch: 0,
        language: select2Language,
        dropdownAutoWidth: false,
        dropdownCssClass: 'ps-select2-dropdown',
    };

    if (! keepEmptyOption) {
        options.placeholder = placeholderFromSelect(select);
    }

    if (dropdownParent) {
        options.dropdownParent = dropdownParent;
    }

    $select.select2(options);

    return $select;
}

function lookupCities(citiesByState, stateId) {
    if (! stateId) {
        return [];
    }

    return citiesByState[stateId]
        ?? citiesByState[String(stateId)]
        ?? citiesByState[Number(stateId)]
        ?? [];
}

function setSelectDisabled(select, disabled) {
    const $select = jQuery(select);

    $select.prop('disabled', disabled);

    if (disabled) {
        select.setAttribute('disabled', 'disabled');
    } else {
        select.removeAttribute('disabled');
    }

    $select.trigger('change.select2');
}

function replaceSelectOptions(select, options, selectedValue = '') {
    const $select = jQuery(select);

    if (! $select.hasClass('select2-hidden-accessible')) {
        initSearchableSelect(select);
    }

    const placeholder = placeholderFromSelect(select);

    $select.empty();
    $select.append(new Option(placeholder, '', false, selectedValue === ''));

    options.forEach(function (option) {
        const value = String(option.id);
        $select.append(new Option(option.name, value, false, value === String(selectedValue)));
    });

    $select.trigger('change.select2');
}

function bindStateCityGroup(stateSelect, citySelect, citiesByState) {
    const $stateSelect = initSearchableSelect(stateSelect);

    initSearchableSelect(citySelect);
    setSelectDisabled(citySelect, ! $stateSelect.val());

    const handleStateChange = function () {
        const stateId = jQuery(stateSelect).val() || '';
        const cities = lookupCities(citiesByState, stateId);

        setSelectDisabled(citySelect, stateId === '');
        replaceSelectOptions(citySelect, cities, '');
    };

    $stateSelect.off('.locationSelects');
    $stateSelect.on('change.locationSelects select2:select.locationSelects select2:clear.locationSelects', handleStateChange);
}

function initStandaloneSearchableSelects() {
    document.querySelectorAll('[data-searchable-select]').forEach(function (select) {
        if (select.matches([
            '[data-listing-state]',
            '[data-listing-city]',
            '[data-repair-locator-state]',
            '[data-repair-locator-city]',
            '[data-vehicle-company]',
            '[data-vehicle-car]',
            '[data-vehicle-model]',
        ].join(', '))) {
            return;
        }

        initSearchableSelect(select);
    });
}

function initListingFilters() {
    document.querySelectorAll('[data-listing-state]').forEach(function (stateSelect) {
        const form = stateSelect.closest('form');

        if (! form) {
            return;
        }

        const citySelect = form.querySelector('[data-listing-city]');

        if (! citySelect) {
            return;
        }

        bindStateCityGroup(
            stateSelect,
            citySelect,
            parseCitiesByState(form.dataset.citiesByState),
        );
    });
}

function initRepairLocators() {
    document.querySelectorAll('[data-repair-locator-form]').forEach(function (form) {
        const stateSelect = form.querySelector('[data-repair-locator-state]');
        const citySelect = form.querySelector('[data-repair-locator-city]');

        if (! stateSelect || ! citySelect) {
            return;
        }

        bindStateCityGroup(
            stateSelect,
            citySelect,
            parseCitiesByState(form.dataset.citiesByState),
        );
    });
}

function parseJsonDataset(raw) {
    if (! raw) {
        return {};
    }

    try {
        return JSON.parse(raw);
    } catch {
        return {};
    }
}

function lookupByKey(map, key) {
    if (! key) {
        return [];
    }

    return map[key] ?? map[String(key)] ?? [];
}

function optionListFromItems(items, valueKey = 'slug', labelKey = 'name') {
    return items.map(function (item) {
        return {
            id: item[valueKey],
            name: item[labelKey],
            url: item.url ?? '',
        };
    });
}

function initVehicleFilters() {
    document.querySelectorAll('[data-vehicle-filter]').forEach(function (form) {
        const companySelect = form.querySelector('[data-vehicle-company]');
        const carSelect = form.querySelector('[data-vehicle-car]');
        const modelSelect = form.querySelector('[data-vehicle-model]');
        const submitButton = form.querySelector('[data-vehicle-filter-submit]');

        if (! companySelect || ! carSelect || ! modelSelect) {
            return;
        }

        const carsByCompany = parseJsonDataset(form.dataset.carsByCompany);
        const modelsByCar = parseJsonDataset(form.dataset.modelsByCar);

        const $companySelect = initSearchableSelect(companySelect);
        initSearchableSelect(carSelect);
        initSearchableSelect(modelSelect);

        setSelectDisabled(carSelect, true);
        setSelectDisabled(modelSelect, true);

        if (submitButton) {
            submitButton.disabled = true;
        }

        const syncSubmit = function () {
            if (! submitButton) {
                return;
            }

            submitButton.disabled = ! jQuery(modelSelect).val();
        };

        const handleCompanyChange = function () {
            const companySlug = jQuery(companySelect).val() || '';
            const cars = optionListFromItems(lookupByKey(carsByCompany, companySlug));

            setSelectDisabled(carSelect, companySlug === '');
            setSelectDisabled(modelSelect, true);
            replaceSelectOptions(carSelect, cars, '');
            replaceSelectOptions(modelSelect, [], '');
            syncSubmit();
        };

        const handleCarChange = function () {
            const companySlug = jQuery(companySelect).val() || '';
            const carSlug = jQuery(carSelect).val() || '';
            const key = companySlug && carSlug ? `${companySlug}|${carSlug}` : '';
            const models = optionListFromItems(lookupByKey(modelsByCar, key));

            setSelectDisabled(modelSelect, carSlug === '');
            replaceSelectOptions(modelSelect, models, '');
            syncSubmit();
        };

        const handleModelChange = function () {
            syncSubmit();
        };

        $companySelect.off('.vehicleFilter');
        $companySelect.on(
            'change.vehicleFilter select2:select.vehicleFilter select2:clear.vehicleFilter',
            handleCompanyChange,
        );

        jQuery(carSelect)
            .off('.vehicleFilter')
            .on('change.vehicleFilter select2:select.vehicleFilter select2:clear.vehicleFilter', handleCarChange);

        jQuery(modelSelect)
            .off('.vehicleFilter')
            .on('change.vehicleFilter select2:select.vehicleFilter select2:clear.vehicleFilter', handleModelChange);

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const companySlug = jQuery(companySelect).val() || '';
            const carSlug = jQuery(carSelect).val() || '';
            const modelSlug = jQuery(modelSelect).val() || '';

            if (! companySlug || ! carSlug || ! modelSlug) {
                return;
            }

            const models = lookupByKey(modelsByCar, `${companySlug}|${carSlug}`);
            const selected = models.find(function (model) {
                return String(model.slug) === String(modelSlug);
            });

            if (selected?.url) {
                window.location.assign(selected.url);
            }
        });
    });
}

export function initLocationSelects() {
    initListingFilters();
    initRepairLocators();
    initVehicleFilters();
    initStandaloneSearchableSelects();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLocationSelects);
} else {
    initLocationSelects();
}
