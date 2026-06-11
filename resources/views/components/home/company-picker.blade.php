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

        <div class="max-h-[min(70vh,32rem)] overflow-y-auto px-5 py-5">
            <div id="company-picker-cars-step">
                <p class="mb-4 text-sm text-ink-muted">خودروی مورد نظر را انتخاب کنید.</p>
                <ul id="company-picker-cars-list" class="divide-y divide-line overflow-hidden rounded-xl border border-line"></ul>
            </div>

            <div id="company-picker-models-step" class="hidden">
                <button
                    type="button"
                    class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-brand transition hover:text-brand-dark"
                    data-company-picker-back
                >
                    <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                    بازگشت به لیست خودروها
                </button>
                <p id="company-picker-car-label" class="mb-4 text-sm text-ink-muted"></p>
                <div id="company-picker-models-list" class="flex flex-wrap gap-2"></div>
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
                const backButton = document.querySelector('[data-company-picker-back]');
                const closeButton = document.querySelector('[data-company-picker-close]');

                let activeCompany = null;

                const showCarsStep = function () {
                    carsStep.classList.remove('hidden');
                    modelsStep.classList.add('hidden');
                };

                const showModelsStep = function (car) {
                    carsStep.classList.add('hidden');
                    modelsStep.classList.remove('hidden');
                    carLabel.textContent = 'مدل ' + car.name + ' را انتخاب کنید:';
                    modelsList.innerHTML = '';

                    car.models.forEach(function (model) {
                        const link = document.createElement('a');
                        link.href = model.url;
                        link.className = 'rounded-xl border border-line bg-surface px-4 py-2.5 text-sm font-medium text-ink transition hover:border-brand/30 hover:bg-brand-soft hover:text-brand';
                        link.textContent = model.name;
                        modelsList.appendChild(link);
                    });
                };

                const openCompany = function (companySlug) {
                    activeCompany = pickerData.find(function (company) {
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
                            + car.models.length + ' مدل '
                            + '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>'
                            + '</span>';
                        button.addEventListener('click', function () {
                            showModelsStep(car);
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

                backButton?.addEventListener('click', showCarsStep);

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
