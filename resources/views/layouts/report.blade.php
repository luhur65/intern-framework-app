<!DOCTYPE html>
<html>
<head>
    <title>Lihat Laporan</title>
    <link href="{{ asset('./2021.3.6/css/stimulsoft.viewer.office2013.whiteblue.css') }}" rel="stylesheet">
    <link href="{{ asset('./2021.3.6/css/stimulsoft.designer.office2013.whiteblue.css') }}" rel="stylesheet">
    <script src="{{ asset('./2021.3.6/scripts/stimulsoft.reports.js') }}" type="text/javascript"></script>
    <script src="{{ asset('./2021.3.6/scripts/stimulsoft.viewer.js') }}" type="text/javascript"></script>

    <script src="{{ asset('./2021.3.6/scripts/stimulsoft.dashboards.js') }}"></script>
    <script src="{{ asset('./2021.3.6/scripts/stimulsoft.designer.js') }}" type="text/javascript"></script>
    @stack('custom-js')
    
</head>

<body onload="onLoad()">
    @yield('pdf')
</body>

</html>