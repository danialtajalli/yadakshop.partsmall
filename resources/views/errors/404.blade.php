@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-6 py-10 text-center sm:px-10">
                <p class="text-6xl font-bold tracking-tight text-brand sm:text-7xl">۴۰۴</p>
                <h1 class="mt-4 text-xl font-bold text-ink sm:text-2xl">صفحه پیدا نشد</h1>
                <p class="mt-3 text-sm leading-7 text-ink-muted">
                    آدرسی که وارد کرده‌اید وجود ندارد، حذف شده یا آدرس آن تغییر کرده است.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3 px-6 py-8 sm:px-10">
                <a href="{{ route('home') }}" class="ps-btn-primary">
                    <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
                    بازگشت به خانه
                </a>
                <a href="{{ route('companies.index') }}" class="ps-btn-secondary">برندهای خودرو</a>
                <a href="{{ route('car.parts') }}" class="ps-btn-secondary">قطعات</a>
            </div>
        </div>
    </div>
@endsection
