<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
        <style>
            .apexbg {
                background-color: #ccc;
                background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZlcnNpb249JzEuMScgd2lkdGg9JzQwMCcgaGVpZ2h0PSc0MDAnPgoJPGRlZnMgaWQ9J2RlZnM0Jz4KCQk8ZmlsdGVyIGNvbG9yLWludGVycG9sYXRpb24tZmlsdGVycz0nc1JHQicgaWQ9J2ZpbHRlcjMxMTUnPgoJCQk8ZmVUdXJidWxlbmNlIHR5cGU9J2ZyYWN0YWxOb2lzZScgbnVtT2N0YXZlcz0nMScgYmFzZUZyZXF1ZW5jeT0nMC45JyBpZD0nZmVUdXJidWxlbmNlMzExNycgLz4KCQkJPGZlQ29sb3JNYXRyaXggcmVzdWx0PSdyZXN1bHQ1JyB2YWx1ZXM9JzEgMCAwIDAgMCAwIDEgMCAwIDAgMCAwIDEgMCAwIDAgMCAwIDYgLTQuMzUwMDAwMDAwMDAwMDAwNSAnIGlkPSdmZUNvbG9yTWF0cml4MzExOScgLz4KCQkJPGZlQ29tcG9zaXRlIGluMj0ncmVzdWx0NScgb3BlcmF0b3I9J2luJyBpbj0nU291cmNlR3JhcGhpYycgcmVzdWx0PSdyZXN1bHQ2JyBpZD0nZmVDb21wb3NpdGUzMTIxJyAvPgoJCQk8ZmVNb3JwaG9sb2d5IGluPSdyZXN1bHQ2JyBvcGVyYXRvcj0nZGlsYXRlJyByYWRpdXM9JzE1JyByZXN1bHQ9J3Jlc3VsdDMnIGlkPSdmZU1vcnBob2xvZ3kzMTIzJyAvPgoJCTwvZmlsdGVyPgoJPC9kZWZzPgoJPHJlY3Qgd2lkdGg9JzEwMCUnIGhlaWdodD0nMTAwJScgeD0nMCcgeT0nMCcgaWQ9J3JlY3QyOTg1JyBmaWxsPScjY2NjY2NjJy8+ICAgICAKCTxyZWN0IHdpZHRoPScxMDAlJyBoZWlnaHQ9JzEwMCUnIHg9JzAnIHk9JzAnIGlkPSdyZWN0Mjk4NScgc3R5bGU9J2ZpbGw6I2ZmZmZmZjtmaWx0ZXI6dXJsKCNmaWx0ZXIzMTE1KScgLz4KPC9zdmc+);
                position: relative;
                z-index: 1;
            }
            .navbg{
                background-color: #f8f8f8;
                border-color: #e7e7e7;
            }
        </style>
    </head>
    <body class="apexbg font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.header')

            <!-- Page Content -->
            <main class="apexbg">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
