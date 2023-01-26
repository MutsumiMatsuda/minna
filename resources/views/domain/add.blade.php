
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Check</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <script src="/build/assets/app.2fa00421.js" defer></script>
  <!-- Fonts -->
  <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Styles -->
  <link href="/build/assets/app.67dcdfd2.css" rel="stylesheet">

  <style>
  body {
    font-family: 'Nunito', sans-serif;
  }

  .container {
    background-color: #dddddd;
  }
  </style>
</head>
<body class="antialiased">
  <div class="container">
    <div class="row">
      <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
        @if (Route::has('login'))
        <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
          @auth
          <a href="{{ url('/home') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Home</a>
          @else
          <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Log in</a>

          @if (Route::has('register'))
          <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 dark:text-gray-500 underline">Register</a>
          @endif
          @endauth
        </div>
        @endif

        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
          <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
            <div class="col-md-8 mx-auto">
              <div class="form-group row py-6">
                <div class="col-2 card" style="font-size: 20px; color: black">
                  現行URL：
                </div>
                <div class="col-6 card bg-info" style="font-size: 20px; color: white">
                  {{ url()->current() }}
                </div>
              </div>
              <div class="form-group row py-2">
                <h2 style="margin-bottom:30px;">新規ドメインの登録</h2>
              </div>
              <form action="{{ route('domain.create') }}" method="post" enctype="multipart/form-data">

                @if (count($errors) > 0)
                <ul>
                  @foreach($errors->all() as $e)
                  <li>{{ $e }}</li>
                  @endforeach
                </ul>
                @endif
                <div class="form-group row">
                  <label class="col-md-2">ドメイン</label>
                  <div class="input-group input-group-lg mb-3">
                    <input type="text" class="form-control" name="domain" value="{{ old('domain') }}">
                  </div>
                </div>
                {{ csrf_field() }}
                <input type="submit" class="btn btn-primary" value="登録">
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
          Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }}) {{ config('app.name', 'Laravel' )}}
        </div>
      </div>
    </div>
  </div>
</body>
</html>
