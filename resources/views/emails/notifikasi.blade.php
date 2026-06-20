<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:560px;margin:24px auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;">
            <h1 style="margin:0;font-size:18px;color:#4f46e5;">{{ \App\Models\Setting::getValue('nama_perusahaan', config('app.name', 'Netvia')) }}</h1>
        </div>
        <div style="padding:24px;font-size:14px;line-height:1.6;white-space:pre-line;">{{ $bodyText }}</div>
        <div style="padding:16px 24px;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
            {{ \App\Models\Setting::getValue('footer_invoice', 'Terima kasih telah berlangganan.') }}
        </div>
    </div>
</body>
</html>
