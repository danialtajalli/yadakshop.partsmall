@props(['companyPicker' => []])

@if ($companyPicker !== [])
    <dialog
        id="home-company-picker-modal"
        class="fixed inset-0 z-50 m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-line bg-white p-0 shadow-2xl backdrop:bg-ink/40"
    >
        <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
            <div class="min-w-0">
                <p class="text-xs font-medium text-brand">انتخاب خودرو</p>
                <h2 id="company-picker-title" class="truncate text-base font-bold text-ink"></h2>
            </div>
            <button
                type="button"
                class="flex size-8 shrink-0 items-center justify-center rounded-lg text-ink-muted transition hover:bg-surface hover:text-ink"
                data-company-picker-close
                aria-label="بستن"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="ps-scrollbar max-h-[min(70vh,32rem)] overflow-y-auto overscroll-contain px-5 py-5 pe-4">
            <div id="company-picker-cars-step">
                <p class="mb-4 text-sm text-ink-muted">خودروی مورد نظر را انتخاب کنید.</p>
                <ul id="company-picker-cars-list" class="divide-y divide-line overflow-hidden rounded-xl border border-line"></ul>
            </div>

            <div id="company-picker-models-step" class="hidden">
                <button
                    type="button"
                    class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-brand transition hover:text-brand-dark"
                    data-company-picker-back-cars
                >
                    <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                    بازگشت به لیست خودروها
                </button>
                <p id="company-picker-car-label" class="mb-3 text-sm text-ink-muted"></p>
                <div id="company-picker-models-list" class="grid gap-3 sm:grid-cols-2"></div>
            </div>
        </div>
    </dialog>

    @push('scripts')
        <script>
            (function () {
                const pickerData = @json($companyPicker);
                const modal = document.getElementById('home-company-picker-modal');

                if (!modal || pickerData.length === 0) {
                    return;
                }

                const title = document.getElementById('company-picker-title');
                const carsStep = document.getElementById('company-picker-cars-step');
                const modelsStep = document.getElementById('company-picker-models-step');
                const carsList = document.getElementById('company-picker-cars-list');
                const modelsList = document.getElementById('company-picker-models-list');
                const carLabel = document.getElementById('company-picker-car-label');
                const backToCarsButton = document.querySelector('[data-company-picker-back-cars]');
                const closeButton = document.querySelector('[data-company-picker-close]');

                const showCarsStep = function () {
                    carsStep.classList.remove('hidden');
                    modelsStep.classList.add('hidden');
                };

                const modelChipClass = 'inline-flex max-w-full items-center rounded-lg border border-line bg-white px-2.5 py-1 text-xs font-medium text-ink transition hover:border-brand/40 hover:bg-brand-soft/50 sm:px-3 sm:py-1.5 sm:text-sm';

                const showModelsForCar = function (car) {
                    carsStep.classList.add('hidden');
                    modelsStep.classList.remove('hidden');
                    carLabel.textContent = 'مدل ' + car.name + ' را انتخاب کنید:';
                    modelsList.innerHTML = '';

                    car.modelCategories.forEach(function (category) {
                        const section = document.createElement('section');
                        section.className = 'rounded-xl border border-line bg-surface/40 p-3';

                        const heading = document.createElement('h3');
                        heading.className = 'mb-2 text-xs font-bold text-brand';
                        heading.textContent = "انتخاب بر اساس " + category.label;
                        section.appendChild(heading);

                        const chips = document.createElement('div');
                        chips.className = 'flex flex-wrap gap-1.5';

                        category.models.forEach(function (model) {
                            const link = document.createElement('a');
                            link.href = model.url;
                            link.className = modelChipClass;
                            link.textContent = model.name;
                            chips.appendChild(link);
                        });

                        section.appendChild(chips);
                        modelsList.appendChild(section);
                    });
                };

                const openCompany = function (companySlug) {
                    const activeCompany = pickerData.find(function (company) {
                        return company.slug === companySlug;
                    });

                    if (!activeCompany) {
                        return;
                    }

                    title.textContent = activeCompany.name;
                    carsList.innerHTML = '';

                    activeCompany.cars.forEach(function (car) {
                        const item = document.createElement('li');
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'flex w-full items-center justify-between gap-3 px-4 py-3.5 text-start text-sm transition hover:bg-brand-soft/40';
                        button.innerHTML = '<span class="font-medium text-ink">' + car.name + '</span>'
                            + '<span class="inline-flex items-center gap-1 text-xs text-ink-muted">'
                            + '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>'
                            + '</span>';
                        button.addEventListener('click', function () {
                            showModelsForCar(car);
                        });
                        item.appendChild(button);
                        carsList.appendChild(item);
                    });

                    showCarsStep();
                    modal.showModal();
                };

                document.querySelectorAll('[data-company-picker-trigger]').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        openCompany(trigger.dataset.companySlug);
                    });
                });

                backToCarsButton?.addEventListener('click', showCarsStep);

                closeButton?.addEventListener('click', function () {
                    modal.close();
                });

                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        modal.close();
                    }
                });

                modal.addEventListener('close', showCarsStep);
            })();
        </script>
    @endpush
@endif
