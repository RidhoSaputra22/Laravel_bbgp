<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
      <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <title>Portal Assessment BBGTK</title>


</head>

<body class="relative min-h-screen bg-gray-100">
    @yield('content')

    @if (session('assessment_portal_success'))
        <x-assessment::ui.alert type="success" class="mb-4">
            {{ session('assessment_portal_success') }}
        </x-assessment::ui.alert>
    @endif


    @if ($errors->has('portal'))
        <x-assessment::ui.alert type="danger" class="mb-4">
            {{ $errors->first('portal') }}
        </x-assessment::ui.alert>
    @endif


    @stack('scripts')
</body>

</html>
