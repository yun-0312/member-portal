<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel + Vue 3</title>

    {{-- Vite のアセット読み込み --}}
    @vite('resources/js/app.js')
</head>
<body>
    {{-- Vue がマウントされる要素 --}}
    <div id="app"></div>
</body>
</html>