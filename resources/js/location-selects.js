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
            '[data-listing-specialization]',
            '[data-repair-locator-state]',
            '[data-repair-locator-city]',
            '[data-shops-filter-state]',
            '[data-shops-filter-city]',
            '[data-vehicle-company]',
            '[data-vehicle-car]',
            '[data-vehicle-model]',
            '[data-vehicle-part]',
            '[data-shop-company-select]',
        ].join(', '),
    );
}

function resolveDropdownParent(select) {
    const shopsFilterMenu = select.closest('[data-shops-filter-menu]');

    if (shopsFilterMenu) {
        return jQuery(shopsFilterMenu);
    }

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

function isAllSelectOption(option) {
    if (option.id) {
        return false;
    }

    const label = String(option.text || '').trim();

    if (label === '') {
        return false;
    }

    const element = option.element;

    if (element?.hasAttribute('data-all-option')) {
        return true;
    }

    // Persian "all …" defaults used across listing / filter selects.
    return label.startsWith('همه');
}

function formatSelectAllOption(option) {
    const label = String(option.text || '').trim() || 'همه';
    const $row = jQuery('<span class="ps-select2-all-option"></span>');

    $row.append(
        jQuery('<span>', {
            class: 'ps-select2-all-option__icon',
            html: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <rect x="3.5" y="3.5" width="7" height="7" rx="1.5" />
                    <rect x="13.5" y="3.5" width="7" height="7" rx="1.5" />
                    <rect x="3.5" y="13.5" width="7" height="7" rx="1.5" />
                    <rect x="13.5" y="13.5" width="7" height="7" rx="1.5" />
                </svg>
            `,
        }),
    );

    $row.append(
        jQuery('<span>', {
            class: 'ps-select2-all-option__label',
            text: label,
        }),
    );

    return $row;
}

function formatSelectOptionWithLogo(option) {
    if (isAllSelectOption(option)) {
        return formatSelectAllOption(option);
    }

    if (! option.id) {
        return option.text;
    }

    const element = option.element;
    const logoUrl = element?.getAttribute('data-logo') || '';
    const label = option.text || '';
    const initial = label.trim().charAt(0) || '?';

    const $row = jQuery('<span class="ps-select2-company-option"></span>');

    if (logoUrl) {
        $row.append(
            jQuery('<img>', {
                class: 'ps-select2-company-option__logo',
                src: logoUrl,
                alt: '',
            }),
        );
    } else {
        $row.append(
            jQuery('<span>', {
                class: 'ps-select2-company-option__fallback',
                text: initial,
            }),
        );
    }

    $row.append(
        jQuery('<span>', {
            class: 'ps-select2-company-option__label',
            text: label,
        }),
    );

    return $row;
}

function formatSelectOption(option) {
    if (isAllSelectOption(option)) {
        return formatSelectAllOption(option);
    }

    return option.text;
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

    if (select.hasAttribute('data-option-logos')) {
        options.templateResult = formatSelectOptionWithLogo;
        options.templateSelection = formatSelectOptionWithLogo;
    } else if (keepEmptyOption) {
        options.templateResult = formatSelectOption;
        options.templateSelection = formatSelectOption;
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
            '[data-shops-filter-state]',
            '[data-shops-filter-city]',
            '[data-vehicle-company]',
            '[data-vehicle-car]',
            '[data-vehicle-model]',
            '[data-vehicle-part]',
            '[data-shop-company-select]',
        ].join(', '))) {
            return;
        }

        initSearchableSelect(select);
    });
}

function initShopsFilters() {
    document.querySelectorAll('[data-shops-filter]').forEach(function (root) {
        const stateSelect = root.querySelector('[data-shops-filter-state]');
        const citySelect = root.querySelector('[data-shops-filter-city]');

        if (! stateSelect || ! citySelect) {
            return;
        }

        bindStateCityGroup(
            stateSelect,
            citySelect,
            parseCitiesByState(root.dataset.citiesByState),
        );
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
        const partSelect = form.querySelector('[data-vehicle-part]');
        const submitButton = form.querySelector('[data-vehicle-filter-submit]');
        const submitLabel = form.querySelector('[data-vehicle-filter-submit-label]');

        if (! companySelect || ! carSelect || ! modelSelect) {
            return;
        }

        const carsByCompany = parseJsonDataset(form.dataset.carsByCompany);
        const modelsByCar = parseJsonDataset(form.dataset.modelsByCar);
        let parts = [];

        try {
            const parsedParts = JSON.parse(form.dataset.parts || '[]');
            parts = Array.isArray(parsedParts) ? parsedParts : [];
        } catch {
            parts = [];
        }

        const productUrlPrefix = (form.dataset.productUrlPrefix || '/product').replace(/\/$/, '');
        const labelParts = form.dataset.labelParts || 'مشاهده قطعات';
        const labelShops = form.dataset.labelShops || 'مشاهده فروشگاه‌ها';

        const $companySelect = initSearchableSelect(companySelect);
        initSearchableSelect(carSelect);
        initSearchableSelect(modelSelect);

        if (partSelect) {
            initSearchableSelect(partSelect);
            setSelectDisabled(partSelect, true);
        }

        setSelectDisabled(carSelect, true);
        setSelectDisabled(modelSelect, true);

        if (submitButton) {
            submitButton.disabled = true;
        }

        const setSubmitLabel = function (nextLabel, animate) {
            if (! submitLabel || submitLabel.textContent === nextLabel) {
                return;
            }

            if (! animate) {
                submitLabel.textContent = nextLabel;

                return;
            }

            submitLabel.classList.add('translate-y-1', 'opacity-0');
            submitButton?.classList.add('ring-2', 'ring-brand-soft/50', 'scale-[1.02]');

            window.setTimeout(function () {
                submitLabel.textContent = nextLabel;
                submitLabel.classList.remove('translate-y-1', 'opacity-0');
            }, 160);

            window.setTimeout(function () {
                submitButton?.classList.remove('ring-2', 'ring-brand-soft/50', 'scale-[1.02]');
            }, 420);
        };

        if (submitLabel) {
            submitLabel.classList.add('inline-block', 'transition', 'duration-200', 'ease-out');
        }

        const syncSubmit = function () {
            if (! submitButton) {
                return;
            }

            const hasModel = Boolean(jQuery(modelSelect).val());
            const hasPart = Boolean(partSelect && jQuery(partSelect).val());

            submitButton.disabled = ! hasModel;
            setSubmitLabel(hasPart ? labelShops : labelParts, hasModel);
        };

        const resetPartSelect = function () {
            if (! partSelect) {
                return;
            }

            setSelectDisabled(partSelect, true);
            replaceSelectOptions(partSelect, optionListFromItems(parts), '');
        };

        const handleCompanyChange = function () {
            const companySlug = jQuery(companySelect).val() || '';
            const cars = optionListFromItems(lookupByKey(carsByCompany, companySlug));

            setSelectDisabled(carSelect, companySlug === '');
            setSelectDisabled(modelSelect, true);
            replaceSelectOptions(carSelect, cars, '');
            replaceSelectOptions(modelSelect, [], '');
            resetPartSelect();
            syncSubmit();
        };

        const handleCarChange = function () {
            const companySlug = jQuery(companySelect).val() || '';
            const carSlug = jQuery(carSelect).val() || '';
            const key = companySlug && carSlug ? `${companySlug}|${carSlug}` : '';
            const models = optionListFromItems(lookupByKey(modelsByCar, key));

            setSelectDisabled(modelSelect, carSlug === '');
            replaceSelectOptions(modelSelect, models, '');
            resetPartSelect();
            syncSubmit();
        };

        const handleModelChange = function () {
            const hasModel = Boolean(jQuery(modelSelect).val());

            if (partSelect) {
                if (! hasModel) {
                    resetPartSelect();
                } else {
                    const currentPart = jQuery(partSelect).val() || '';
                    setSelectDisabled(partSelect, false);
                    replaceSelectOptions(partSelect, optionListFromItems(parts), currentPart);
                }
            }

            syncSubmit();
        };

        const handlePartChange = function () {
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

        if (partSelect) {
            jQuery(partSelect)
                .off('.vehicleFilter')
                .on('change.vehicleFilter select2:select.vehicleFilter select2:clear.vehicleFilter', handlePartChange);
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const companySlug = jQuery(companySelect).val() || '';
            const carSlug = jQuery(carSelect).val() || '';
            const modelSlug = jQuery(modelSelect).val() || '';
            const partSlug = partSelect ? (jQuery(partSelect).val() || '') : '';

            if (! companySlug || ! carSlug || ! modelSlug) {
                return;
            }

            if (partSlug) {
                window.location.assign(
                    `${productUrlPrefix}/${encodeURIComponent(companySlug)}/${encodeURIComponent(carSlug)}/${encodeURIComponent(modelSlug)}/${encodeURIComponent(partSlug)}`,
                );

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

function initShopCompanyFilters() {
    document.querySelectorAll('[data-shop-company-select]').forEach(function (select) {
        initSearchableSelect(select);

        const navigateToSelected = function () {
            const selected = select.options[select.selectedIndex];
            const url = selected?.getAttribute('data-url');

            if (! url) {
                return;
            }

            const nextUrl = new URL(url, window.location.origin);

            if (nextUrl.pathname === window.location.pathname) {
                return;
            }

            // Full navigation — separate from Alpine listing search.
            window.location.assign(nextUrl.href);
        };

        jQuery(select)
            .off('.shopCompanyFilter')
            .on('change.shopCompanyFilter select2:select.shopCompanyFilter select2:clear.shopCompanyFilter', navigateToSelected);
    });
}

export function initLocationSelects() {
    initListingFilters();
    initRepairLocators();
    initShopsFilters();
    initVehicleFilters();
    initShopCompanyFilters();
    initStandaloneSearchableSelects();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLocationSelects);
} else {
    initLocationSelects();
}
