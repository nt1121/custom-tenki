<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CustomTenki</title>
<meta name=”description” content=”表示する項目などがカスタマイズできる天気予報です。”>
<meta name=”keywords” content=”天気,天気予報”>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
@vite('resources/scss/app.scss')
