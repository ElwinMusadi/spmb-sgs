<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @include('partials.head')
</head>

<body class="min-h-screen bg-surface text-on-surface font-body antialiased">
  {{ $slot }}

  @persist('toast')
    <flux:toast.group>
      <flux:toast />
    </flux:toast.group>
  @endpersist

  @fluxScripts
</body>

</html>
