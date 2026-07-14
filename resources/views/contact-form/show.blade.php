<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    @include('contact-form.partials.head', ['title' => 'فرم تماس'])
</head>
<body class="bg-surface p-4 sm:p-6">
    <div id="ps-navigation-progress" role="progressbar" aria-hidden="true" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
    <div class="mx-auto max-w-xl rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
        @include('contact-form._form')
    </div>
</body>
</html>
