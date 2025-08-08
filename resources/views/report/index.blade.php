@extends('layouts.report')

@section('pdf')

<div id="viewerContent"></div>

@endsection

@push('custom-js')

<script>
    function onLoad() {
        var JSONData = {!! $laporanJSON !!}

        // License
        Stimulsoft.Base.StiLicense.loadFromFile("{{ asset('./2021.3.6/stimulsoft/license.php') }}");

        // Buat Option Viewer
        var viewerOptions = new Stimulsoft.Viewer.StiViewerOptions();
        viewerOptions.appearance.fullScreenMode = true;
        viewerOptions.appearance.scrollbarsMode = true;
        // viewerOptions.appearance.pageBorderColor = Stimulsoft.System.Drawing.Color.navy;
        // viewerOptions.toolbar.borderColor = Stimulsoft.System.Drawing.Color.navy;
        // viewerOptions.toolbar.showPrintButton = false;
        viewerOptions.toolbar.showViewModeButton = false;
        viewerOptions.appearance.viewMode = Stimulsoft.Viewer.StiWebViewMode.WholeReport;
        // viewerOptions.toolbar.zoom = 50;
        // viewerOptions.width = "1000px";
        viewerOptions.height = "100vh";

        // Buat Viewer
        var viewer = new Stimulsoft.Viewer.StiViewer(viewerOptions, "StiViewer", false);

        // Buat Objek report
        var report = new Stimulsoft.Report.StiReport();

        // 3. Muat file .mrt. Laporan ini sudah tahu dari mana harus mengambil datanya (dari URL).
        viewer.renderHtml("viewerContent");
        report.loadFile("{{ asset('/template/laporan_penjualan.mrt') }}");

        // Buat dataset
        var dataSet = new Stimulsoft.System.Data.DataSet("penjualan");

        // Menghapus semua koneksi database yang terdaftar dalam report.
        report.dictionary.databases.clear();

        // Menghapus semua tabel / view / query (alias DataSources) dari report.
        report.dictionary.dataSources.clear();

        // baca data JSON;
        dataSet.readJson(JSONData);

        // Membaca dataset
        report.regData("penjualan", "penjualan", dataSet);
        report.dictionary.synchronize();

        // setting file
        // report.dictionary.variables.add("ReportTitle", "Laporan Penjualan");
        // report.dictionary.variables.add("PrintDate", new Date().toLocaleDateString());
        // report.dictionary.variables.add("UserName", "Dharma Situmorang");

        // 4. Hubungkan report ke viewer dan tampilkan
        viewer.report = report;

        var options = new Stimulsoft.Designer.StiDesignerOptions();
        viewerOptions.appearance.fullScreenMode = true;

        // var designer = new Stimulsoft.Designer.StiDesigner(options, "Designer", false);

        // designer.renderHtml("viewerContent");
        // designer.report = report;
    }
</script>
    
@endpush