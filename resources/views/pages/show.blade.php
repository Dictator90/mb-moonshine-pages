<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->seo_title ?: $page->title }}</title>
    @if(! empty($page->seo_description))
        <meta name="description" content="{{ $page->seo_description }}">
    @endif
</head>
<body>
<main>
    <h1>{{ $page->title }}</h1>
    {!! $page->content !!}
</main>
</body>
</html>
