<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign-in problem — Apex Innovations</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f7fafc; color: #2d3748;
               display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.12);
                max-width: 28rem; padding: 2rem; text-align: center; }
        a { color: #3182ce; }
    </style>
</head>
<body>
    <div class="card">
        <h1>We couldn't sign you in</h1>
        <p>{{ $message }}</p>
        <p><a href="{{ url('/login') }}">Return to login</a></p>
    </div>
</body>
</html>
