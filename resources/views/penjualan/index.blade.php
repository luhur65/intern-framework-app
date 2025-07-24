@extends('layouts.app')
@section('title', 'Home')

@section('content')
  
  <h1>Penjualan</h1>

  {{-- <p class="lead">{{ $querySQL }}</p> --}}

  <!-- tabel penjualan -->
  <div class="table-responsive">
    <table id="jqGrid"></table>
    <div id="jqGridPager"></div>
  </div>

  <!-- master detail -->
  {{-- <h2 class="mt-3">Detail Penjualan</h2> --}}
  <div class="table-responsive pt-4">
    <table id="detailItem"></table>
    <div id="detailItemPager"></div>
  </div>


@endsection

@push('script')

  <script>

  const selectId = null;

  // Fungsi untuk Detail Item
  function detailTable(id) {

    const formatOpt = {
      prefix: '',
      thousandsSeparator: ',',
      decimalPlaces: 2,
      decimalSeparator: '.',
    }

    // Detail Table
    jQuery("#detailItem").jqGrid({
      mtype: "GET",
      // styleUI: 'Bootstrap4',
      iconSet: 'fontAwesome',
      shrinkToFit: true,
      autowidth: true,
      url: "penjualan/" + id + "/detail",
      datatype: "json",
      colNames: ['Nama Barang', 'Banyak Barang', 'Harga Satuan (Rp)', 'Total (Rp)'],
      colModel: [
        // { name: 'num', index: 'num', width: 55 },
        { name: 'nama_barang', index: 'nama_barang', width: 120 },
        { name: 'qty', index: 'qty', width: 120, align: "right" },
        { name: 'harga', index: 'harga', width: 120, align: "right", formatter: 'currency', formatoptions: formatOpt },
        {
          name: 'total',
          index: 'total',
          width: 120,
          align: "right",
          sortable: false,
          search: false,
          formatter: 'currency',
          formatoptions: formatOpt,
        },
      ],
      rowNum: 10,
      rowList: [5, 10, 20],
      pager: '#detailItemPager',
      sortname: 'penjualan_id',
      viewrecords: true,
      gridview: true,
      // width: 600,
      height: 'auto',
      sortorder: "asc",
      multiselect: false,
      rownumbers: true,
      caption: "Penjualan Detail",
      footerrow: true,
      userDataOnFooter: true,
      gridComplete: function () {

        const arrTotalHarga = $(this).jqGrid('getCol', 'total', false);
        const arrTotalBarang = $(this).jqGrid('getCol', 'qty', false);

        let totalHarga = 0;
        let totalBarang = 0;

        arrTotalHarga.forEach(function (val) {
          // Jika backend kirim angka, cukup parseFloat
          let num = typeof val === 'number' ? val : parseFloat(val);
          if (!isNaN(num)) totalHarga += num;
        });

        arrTotalBarang.forEach(function (val) {
          // Jika backend kirim angka, cukup parseFloat
          let num = typeof val === 'number' ? val : parseFloat(val);
          if (!isNaN(num)) totalBarang += num;
        });

        $("#detailItem").jqGrid('footerData', 'set', { nama_barang: 'Total:', total: totalHarga, qty: totalBarang });

        // Highlight pencarian
        higligthPencarian($(this));
      }
    }).navGrid('#detailItemPager', { add: false, edit: false, del: false, search: false, refresh: false });

    // Filter Bar untuk detail 
    // Filter Bar => Untuk mencari data
    $('#detailItem').jqGrid('filterToolbar', {
      autosearch: true,
      stringResult: true,
      searchOnEnter: false,
      defaultSearch: "cn",
      multipleSearch: true,
      beforeSearch: function () {
        const postData = $('#detailItem').getGridParam("postData");
        delete postData.global_search;

        $('#detailItem').setGridParam({
          search: true,
          page: 1,
          postData: {
            _search: true,
          }
        }).trigger('reloadGrid');

      }

    });

  }
  
  // MASTER GRID
  // Inisialisasi jqGrid untuk master
  $('#jqGrid').jqGrid({
    url: "/penjualan/master",
    mtype: "GET",
    // styleUI: 'Bootstrap4',
    iconSet: 'fontAwesome',
    datatype: "JSON",
    colModel: [
      {
        label: 'Id Bukti',
        name: 'id_penjualan',
        hidden: true,
        key: true,
        width: 30
      },
      {
        label: 'No Bukti',
        name: 'no_bukti',
        width: 100
      },
      {
        label: 'Tanggal Bukti',
        name: 'tgl_bukti',
        width: 100,
        // searchoptions: { 
        //   dataInit: function (el) { 
        //     $(el).datepicker({ dateFormat: 'dd-mm-yy' }); 
        //   } 
        // }
        // formatter: 'date',
        // formatoptions: { srcformat: 'Y-m-d', newformat: 'd-m-Y' } // format tanggal untuk backend yang menggunakan ISO date
      },
      {
        label: 'Nama Pelanggan',
        name: 'nama_pelanggan',
        width: 100
      }
    ],
    cmTemplate: { required: true },
    autowidth: true,
    height: 'auto',
    rowNum: 10,
    // mtype: "POST", // ini berpengaruh pada cara pengambilan parameter untuk jqGrid
    rowList: [10, 20, 30],
    rownumbers: true,
    sortname: 'no_bukti',
    viewrecords: true,
    gridview: true,
    // loadonce: true,
    sortorder: "asc",
    caption: "Data Penjualan",
    pager: "#jqGridPager",
    onSelectRow: function (id) {
      jQuery("#detailItem").jqGrid('setGridParam', { url: "penjualan/" + id + "/detail", page: 1 });
      jQuery("#detailItem").trigger('reloadGrid');

    },
    loadComplete: function (response) {
      const ids = $("#jqGrid").jqGrid('getDataIDs');

      // console.log(response);

      if (selectId) {
        selectRow(selectId);
        // $("#jqGrid").jqGrid('setSelection', selectId);
        // console.log(selectId);
        detailTable(selectId);
        console.log(selectId)

      } else {
        selectRow(ids[0]);
        detailTable(ids[0]);
        console.log(ids[0]);

      }


      // Highlight pencarian
      higligthPencarian($(this));
    }
  });


  // setting default untuk seluruh action bawaan jqgrid
  $("#jqGrid").jqGrid('navGrid', '#jqGridPager', { edit: false, add: false, del: false, search: false, refresh: false });

  // filter bar master 
  // Filter Bar => Untuk mencari data
  $('#jqGrid').jqGrid('filterToolbar', {
    autosearch: true,
    stringResult: true,
    searchOnEnter: false,
    defaultSearch: "cn",
    multipleSearch: true,
    beforeSearch: function () {
      const postData = $('#jqGrid').getGridParam("postData");
      delete postData.global_search;

      $('#jqGrid').setGridParam({
        search: true,
        page: 1,
        postData: {
          _search: true,
        }
      }).trigger('reloadGrid');

    }

  });

  // global search master
  // tombol button x 
  const buttonX = `<button id="reset_search" type="button" class="active:scale-75" title="Reset All Toolbar Search ">
    <span class="text-2xl text-red-500 font-bold bg-sky-50 px-1 rounded active:bg-sky-600 active:text-slate-50">X</span>
  </button>`;
  $('#gsh_jqGrid_rn').append(buttonX);
  $('#reset_search').click(function () {
    $('#gsearch').val('');
    resetSearch();

    // hapus data pencarian
    const postData = $('#jqGrid').getGridParam("postData");
    delete postData.global_search;
    delete postData.filters;

    // Reset postData dan search = false
    $('#jqGrid').setGridParam({
      search: false,
      postData: {
        _search: false,
      }
    }).trigger('reloadGrid', [{ page: 1 }]);

    // Bersihkan highlight pada semua cell
    $('#jqGrid').find('td').each(function () {
      let html = $(this).html();
      html = html.replace(/<span class="highlight">(.*?)<\/span>/gi, "$1");
      $(this).html(html);
    });

  });

  // Global Search
  const globalSearchElem = `
    <div class='ui-jqgrid-titlebar ui-widget-header'>
      Global Search : 
      <input type='text' name='gsearch' id='gsearch' size='20' class='bg-slate-50 rounded-md outline-none text-slate-700 indent-2'>
    </div>`;
  $('.ui-jqgrid-titlebar').after(globalSearchElem);

  $('#gsearch').on('keyup', function () {
    let text = $(this).val();

    resetSearch();

    //ada banyak parameter grid, salah satunya postData. untuk nngeliat bisa bikin getGridParam
    //untuk nambahin isi dari parameternya bisa dibuat pake setGridParam
    //jadi untuk search, set dulu data baru untuk param postData. lalu di trigger dengan reloadGrid
    //maka setelah itu, isi param postData bisa bertambah sesuai yg diinginkan
    $('#jqGrid').jqGrid('setGridParam', {
      search: false,
      page: 1,
      postData: {
        filters: {},
        _search: false,
        global_search: text
      }
    }).trigger('reloadGrid')

  });

  // navigasi 
  // navigasi user (custom)
  $(document).on("keydown", function(e) {
  // $(document).off('keydown.jqgrid').on('keydown.jqgrid', function(e) { // lebih bagus karna bisa autofocus
    // Cek apakah ada input yang sedang fokus
    // if ($('input:focus, textarea:focus').length > 0) {
    //   return;
    // }

    if (e.which == 38 || e.which == 40 || e.which == 33 || e.which == 34 || e.which == 35 || e.which == 36) {
      e.preventDefault();
    }

    // ambil id yang dipilih
    const barisTerpilih = getSelectedRowId();
    const ids = $("#jqGrid").jqGrid('getDataIDs'); // console.log("Total Index: ", ids.length - 1); = 9
    const indexSaatIni = ids.indexOf(barisTerpilih);

    // Page Saat ini
    const halamanSaatIni = $("#jqGrid").jqGrid('getGridParam', 'page');
    const halamanTerakhir = $("#jqGrid").jqGrid('getGridParam', 'lastpage');
    
    let indexBaru;

    switch (e.which) {
      case 38: // arrow up
        // e.preventDefault();
        if (indexSaatIni > 0) {
          indexBaru = ids[indexSaatIni - 1];
          selectRow(indexBaru);
          console.info("NAIK");
          console.log("Index sebelum naik: ", indexSaatIni);
          console.log("Naik ke index: ", indexSaatIni - 1);
        } 
        break;
      
      case 40: // arrow down
        // e.preventDefault();
        if (indexSaatIni < ids.length - 1) {
          indexBaru = ids[indexSaatIni + 1];
          selectRow(indexBaru);
          console.info("TURUN");
          console.log("Index sebelum turun: ", indexSaatIni);
          console.log("Turun ke index: ", indexSaatIni + 1);
        }
      break;

      case 33: // page up
        // e.preventDefault();
        if (halamanSaatIni > 1) {
          console.log("PAGE UP");
          console.info("Pindah halaman ke: ", halamanSaatIni - 1);
          $("#jqGrid").jqGrid('setGridParam', { page: halamanSaatIni - 1 }).trigger('reloadGrid');
        }
        break;

      case 34: // page down
        // e.preventDefault();
        if (halamanSaatIni < halamanTerakhir) {
          console.log("PAGE DOWN");
          console.info("Pindah halaman ke: ", halamanSaatIni + 1);
          $("#jqGrid").jqGrid('setGridParam', { page: halamanSaatIni + 1 }).trigger('reloadGrid');
        }
        break;

      case 36: // home
        // e.preventDefault();
        if (halamanSaatIni > 1) {
          console.log("HOME");
          console.info("Halaman PERTAMA");
          $("#jqGrid").jqGrid('setGridParam', { page: 1 }).trigger('reloadGrid');
        }
        break;

      case 35: // end
        // e.preventDefault();
        if (halamanSaatIni < halamanTerakhir) {
          console.log("END");
          console.info("Halaman TERAKHIR");
          $("#jqGrid").jqGrid('setGridParam', { page: halamanTerakhir }).trigger('reloadGrid');
        }
        break;

      default:
        break;
    }


  });

  </script>
@endpush