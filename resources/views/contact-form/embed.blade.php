<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    @include('contact-form.partials.head', ['title' => 'فرم تماس'])
</head>
<body class="bg-transparent p-0">
    <div id="ps-navigation-progress" role="progressbar" aria-hidden="true" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
    @include('contact-form._form')
</body>
</html>
