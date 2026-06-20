@props(['title', 'company' => []])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #111; margin: 0; }
        h1 { font-size: 15px; margin: 0 0 2px; }
        .muted { color: #666; font-size: 10px; }
        .head { border-bottom: 2px solid #2547f9; padding-bottom: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f1f4ff; font-size: 10px; text-transform: uppercase; }
        td.num, th.num { text-align: right; }
        tfoot td { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <div class="head">
        <h1>{{ $company['nama'] ?? config('app.name') }}</h1>
        @if (! empty($company['alamat']))
            <div class="muted">{{ $company['alamat'] }}</div>
        @endif
        <div class="muted">{{ $title }}</div>
    </div>

    {{ $slot }}
</body>
</html>
