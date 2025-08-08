<!doctype html>
<html lang="id">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    
    <link rel="stylesheet" href="{{ asset('jqgrid/css/jquery-ui.min.css') }}">
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('jqgrid/css/ui.jqgrid-bootstrap4.css') }}">

    <link rel="stylesheet" href="{{ asset('jqgrid/css/my-style.css') }}">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <title>@yield('title') | {{ env('APP_NAME') }}</title>

    <style>
      * {
        font-family: "DM Sans", sans-serif;
        font-size: 14px;
        text-transform: uppercase
      }

      input[type="text"] {
        text-transform: uppercase; !important
      }

      .highlight {
        background-color: yellow;
        transition: background-color 0.5s ease;
      }
      .ui-jqgrid-sortable {
          padding: 2px 0 !important; /
          text-align: center;
      }

      .ui-jqgrid .ui-search-toolbar th.jqgrid-rownumber,
      tbody tr td.jqgrid-rownum {
          padding: 2px 0 !important; 
          text-align: center;
          vertical-align: middle;
      }

      .clearsearchclass {
          margin-left: 10px !important;
      }

      /* Select2 */
      .select2-selection__rendered {
        line-height: 38px !important;
        height: 38px !important;
        text-transform: uppercase !important;
      }

      .select2-selection.select2-selection--single.select2-selection--clearable,
      .select2-selection__clear,
      .select2-selection__arrow {
        height: 38px !important;
      }

      .col-form-label {
        text-transform: uppercase !important;
      }
      .select2-container--default .select2-selection--single {
        height: 38px !important;
      }
      td.ui-search-clear {
        width: 30px !important;
      }

    </style>
    @stack('style')
  </head>
  <body class="pb-5">

    <nav class="navbar navbar-light bg-light">
      <div class="container">
        <span class="navbar-brand mb-0 h1">Laravel12 + JQGrid</span>
      </div>
    </nav>

    <main class="container mt-4">
      @yield('content')
    </main>

    <div id="notificationDialog" title="Notifikasi" style="display:none;">
      <p id="notificationMessage"></p>
    </div>

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    {{-- <script src="{{ asset('jqgrid/jquery-3.6.0.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <script src="{{ asset('jqgrid/js/jquery.jqGrid.min.js') }}"></script>
    <script src="{{ asset('jqgrid/js/i18n/grid.locale-id.js') }}"></script>

    <!-- inputmask -->
    <script src="{{ asset('inputmask/jquery.inputmask.js') }}"></script>
    <script src="{{ asset('inputmask/bindings/inputmask.binding.js') }}"></script>

    <!-- select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- AutoNUmeric -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.8.1/autoNumeric.min.js"></script>

    {{-- My Script --}}
    <script src="{{ asset('laravel-jqgrid.js') }}"></script>
    @stack('script')
  </body>
</html>