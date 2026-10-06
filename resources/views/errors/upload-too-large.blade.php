<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Upload Too Large</title>
    <style>
        body {
            align-items: center;
            background: #f9fafb;
            color: #111827;
            display: flex;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            justify-content: center;
            margin: 0;
            min-height: 100vh;
            padding: 24px;
        }

        main {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(17, 24, 39, .08);
            max-width: 560px;
            padding: 28px;
        }

        h1 {
            font-size: 24px;
            line-height: 1.2;
            margin: 0 0 12px;
        }

        p {
            color: #4b5563;
            line-height: 1.6;
            margin: 0 0 16px;
        }

        dl {
            background: #f3f4f6;
            border-radius: 8px;
            display: grid;
            gap: 8px;
            grid-template-columns: max-content 1fr;
            margin: 0 0 20px;
            padding: 14px;
        }

        dt {
            color: #6b7280;
            font-size: 13px;
        }

        dd {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            margin: 0;
        }

        button {
            background: #231535;
            border: 0;
            border-radius: 8px;
            color: #ffffff;
            cursor: pointer;
            font: inherit;
            padding: 10px 16px;
        }
    </style>
</head>
<body>
    <main>
        <h1>Upload Too Large</h1>
        <p>{{ $message }}</p>
        <dl>
            <dt>post_max_size</dt>
            <dd>{{ $postMaxSize }}</dd>
            <dt>upload_max_filesize</dt>
            <dd>{{ $uploadMaxFilesize }}</dd>
        </dl>
        <button type="button" onclick="history.back()">Go Back</button>
    </main>
</body>
</html>
