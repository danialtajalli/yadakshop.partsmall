<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $part->name }} — {{ $car->name }} {{ $model->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            background: #f5f5f4;
            color: #1c1917;
            line-height: 1.7;
        }
        .container {
            max-width: 48rem;
            margin: 0 auto;
            padding: 1.5rem;
        }
        .breadcrumb {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            font-size: 0.875rem;
            color: #78716c;
            margin-bottom: 1.5rem;
        }
        .breadcrumb span { color: #d6d3d1; }
        .card {
            background: #fff;
            border: 1px solid #e7e5e4;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #44403c;
        }
        .meta {
            display: grid;
            gap: 0.5rem;
            font-size: 0.9375rem;
        }
        .meta dt {
            font-weight: 600;
            color: #57534e;
        }
        .meta dd {
            margin-bottom: 0.5rem;
        }
        .part-body {
            margin-top: 1rem;
            font-size: 0.9375rem;
        }
        .part-body :is(p, ul, ol) { margin-bottom: 0.75rem; }
        .badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.8125rem;
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="breadcrumb" aria-label="مسیر">
            <span>{{ $company->name }}</span>
            <span>/</span>
            <span>{{ $car->name }}</span>
            <span>/</span>
            <span>{{ $model->name }}</span>
            <span>/</span>
            <strong>{{ $part->name }}</strong>
        </nav>

        <article class="card">
            <h1>{{ $part->name }}</h1>

            @if ($part->partsCategory)
                <p><span class="badge">{{ $part->partsCategory->name }}</span></p>
            @endif

            @if ($part->description)
                <div class="part-body">
                    {!! $part->description !!}
                </div>
            @endif
        </article>

        <section class="card">
            <h2>خودرو و مدل</h2>
            <dl class="meta">
                <dt>شرکت</dt>
                <dd>{{ $company->name }}</dd>

                <dt>خودرو</dt>
                <dd>{{ $car->name }}</dd>

                <dt>مدل</dt>
                <dd>{{ $model->name }}</dd>
            </dl>
        </section>
    </div>
</body>
</html>
