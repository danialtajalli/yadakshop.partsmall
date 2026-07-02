@if ($errors->any())
    <div
        {{ $attributes->merge(['class' => 'ps-form-errors']) }}
        role="alert"
        aria-live="polite"
    >
        <p class="ps-form-errors__title">لطفاً موارد زیر را اصلاح کنید:</p>

        <ul class="ps-form-errors__list">
            @foreach ($errors->all() as $error)
                <li class="ps-form-errors__item">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
