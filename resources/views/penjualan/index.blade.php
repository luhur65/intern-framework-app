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

  <!-- Modal -->
  <div class="modal fade" id="formModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="formModalLabel">
            Tambah Data Penjualan
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="modal-body">
            <form id="penjualanForm">
              <input type="hidden" name="id" id="formId">

  <div class="form-group row">
    <label for="no_bukti" class="col-sm-2 col-form-label">No Bukti</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="no_bukti" name="no_bukti" required>
    </div>
  </div>

  <div class="form-group row">
    <label for="tgl_bukti" class="col-sm-2 col-form-label">Tanggal Bukti</label>
    <div class="col-sm-10">
      <input type="date" class="form-control" id="tgl_bukti" name="tgl_bukti" required>
    </div>
  </div>

  <div class="form-group row">
    <label for="nama_pelanggan" class="col-sm-2 col-form-label">Nama Pelanggan</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan" required>
    </div>
  </div>
              {{-- <input type="hidden" name="id" id="formId">
              <div class="form-group">
                <label for="no_bukti">No Bukti</label>
                <input type="text" class="form-control" id="no_bukti" name="no_bukti" required>
              </div>
              <div class="form-group">
                <label for="tgl_bukti">Tanggal Bukti</label>
                <input type="date" class="form-control" id="tgl_bukti" name="tgl_bukti" required>
              </div>
              <div class="form-group">
                <label for="nama_pelanggan">Nama Pelanggan</label>
                <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan" required>
              </div> --}}
              <div class="form-group">
                <label>Daftar Barang</label>
                <table class="table table-bordered" id="barangTable">
                  <thead>
                    <tr>
                      <th>Nama Barang</th>
                      <th>Qty</th>
                      <th>Harga</th>
                      <th>Total</th>
                      <th>
                        <button type="button" class="btn btn-success btn-sm" id="addBarangRow">+</button>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><input type="text" name="nama_barang[]" class="form-control" required></td>
                      <td><input type="number" name="qty[]" class="form-control" min="1" required></td>
                      <td><input type="number" name="harga[]" class="form-control" min="0" required></td>
                      <td><input type="text" name="total[]" class="form-control" readonly></td>
                      <td>
                        <button type="button" class="btn btn-danger btn-sm removeBarangRow">-</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Understood</button>
        </div>
      </div>
    </div>
  </div>


@endsection

@push('style')
<style>
    .ui-jqgrid .ui-jqgrid-titlebar {
      background-color: #26915c;
      color: white;
      /* border-bottom: 1px solid #dee2e6; */
    }
    .ui-jqgrid .ui-jqgrid-pager {
      background-color: #26915c;
      /* border-top: 1px solid #dee2e6; */
    }

    .ui-jgrid .ui-jqgrid-labels {
      border-radius: 0px;
    }
    
    .ui-jqgrid .ui-jqgrid-labels th {
      background-color: #3c8f65;
      color: white;
      font-weight: bold;
      text-align: center;
      vertical-align: middle;
      padding: 5px 0;
    }

    .table-active {
      background-color: #7be7a5 !important;
    }

    .ui-jqgrid .ui-jqgrid-pager .ui-paging-info,
    .ui-jqgrid .ui-jqgrid-pager #input_jqGridPager,
    .ui-jqgrid .ui-jqgrid-pager #input_detailItemPager {
      color: white;
    }

    .ui-jqgrid .ui-jqgrid-pager .ui-pg-button {
      color: white;
      background-color: #26915c;
      border: none;
    }

    .ui-search-toolbar input[type="text"] {
      width: 100%;
      height: 30px;
      padding: 0 10px;
      border-radius: 4px;
      border: 2px solid #7fd391;
      outline: none
    }

    #gsh_jqGrid_rn > div {
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      box-sizing: border-box;
    }

    .reset-search-btn {
      border-radius: 4px;
      border: 1px solid #c00;
      outline: none;
      cursor: pointer;
      background-color: #e40606;
      color: white;
      font-weight: bold;
      transition: background-color 0.3s ease;
      font-size: 16px;
    }

    .reset-search-btn:hover {
      background-color: #c00;

    }

    .ui-pg-table #AddHeader { 
      padding: 5px;
      background-color: #125824;
      border-radius: 5px;
    }
</style>
@endpush

@push('script')

  <script>

  const selectId = null;
  const detailGrid = "#detailItem";
  const masterGrid = "#jqGrid";

  // Fungsi navigasi untuk grid detail
  // function setupKeydown(gridId, callback) {
  //   $(document).off(callback).on(callback, function(e) {
  //     if (e.which == 38 || e.which == 40 || e.which == 33 || e.which == 34 || e.which == 35 || e.which == 36) {
  //       e.preventDefault();
  //     }

  //     const barisTerpilih = $(gridId).jqGrid('getGridParam', 'selrow');
  //     const ids = $(gridId).jqGrid('getDataIDs');
  //     const indexSaatIni = ids.indexOf(barisTerpilih);

  //     const halamanSaatIni = $(gridId).jqGrid('getGridParam', 'page');
  //     const halamanTerakhir = $(gridId).jqGrid('getGridParam', 'lastpage');
  //     let indexBaru;

  //     switch (e.which) {
  //       case 38: // up
  //         if (indexSaatIni > 0) {
  //           indexBaru = ids[indexSaatIni - 1];
  //           $(gridId).jqGrid('setSelection', indexBaru);
  //         }
  //         break;
  //       case 40: // down
  //         if (indexSaatIni < ids.length - 1) {
  //           indexBaru = ids[indexSaatIni + 1];
  //           $(gridId).jqGrid('setSelection', indexBaru);
  //         }
  //         break;
  //       case 33: // page up
  //         if (halamanSaatIni > 1) {
  //           $(gridId).jqGrid('setGridParam', { page: halamanSaatIni - 1 }).trigger('reloadGrid');
  //         }
  //         break;
  //       case 34: // page down
  //         if (halamanSaatIni < halamanTerakhir) {
  //           $(gridId).jqGrid('setGridParam', { page: halamanSaatIni + 1 }).trigger('reloadGrid');
  //         }
  //         break;
  //       case 36: // home
  //         if (halamanSaatIni > 1) {
  //           $(gridId).jqGrid('setGridParam', { page: 1 }).trigger('reloadGrid');
  //         }
  //         break;
  //       case 35: // end
  //         if (halamanSaatIni < halamanTerakhir) {
  //           $(gridId).jqGrid('setGridParam', { page: halamanTerakhir }).trigger('reloadGrid');
  //         }
  //         break;
  //       default:
  //         break;
  //     }
  //   });
  // }
  
  // MASTER GRID
  // Inisialisasi jqGrid untuk master
  $('#jqGrid').jqGrid({
    url: "/penjualan/master",
    mtype: "GET",
    styleUI: 'Bootstrap4',
    iconSet: 'fontAwesome',
    datatype: "JSON",
    colModel: [
      {
        label: 'Id Bukti',
        name: 'id',
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
    gridComplete: function (response) {
      const ids = $("#jqGrid").jqGrid('getDataIDs');

      // console.log(response);

      if (selectId) {
        selectRow(selectId);
        // $("#jqGrid").jqGrid('setSelection', selectId);
        // console.log(selectId);
        detailTable(selectId);
        // console.log(selectId)

      } else {
        selectRow(ids[0]);
        detailTable(ids[0]);
        // console.log(ids[0]);

      }


      // Highlight pencarian
      higligthPencarian($(this));
      // Setup navigasi untuk grid master
      initializeGridNavigation(masterGrid);

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
  // const buttonX = `<button id="reset_search" type="button" class="" title="Reset All Toolbar Search ">X</span>
  // </button>`;
  // $('#gsh_jqGrid_rn div').append(buttonX); // master grid
  const masterButton = createResetButtonElement('master');
  $('#gsh_jqGrid_rn div').append(masterButton);
  
  // Event handler menggunakan class (lebih fleksibel)
  $(document).on('click', '.reset-search-btn', function() {
      const buttonId = $(this).attr('id');
      
      if (buttonId === 'reset_search_master') {
          $('#gsearch_JqGrid').val('');
          resetToolbarSearch('#jqGrid'); // Reset master grid
      } 
      else if (buttonId === 'reset_search_detail') {
          $('#gsearch_detailItem').val('');
          resetToolbarSearch('#detailItem'); // Reset detail grid
      }
  });

  // Global Search untuk master
  const masterSearchElem = `
    <div class='ui-jqgrid-titlebar ui-widget-header'>
      Global Search :
      <input type='text' name='gsearch' id='gsearch_JqGrid' class='rounded border-0' placeholder='.....' style='width: 300px; height: 30px; padding: 0 10px;'>
    </div>`;
  $('#gbox_jqGrid .ui-jqgrid-titlebar').after(masterSearchElem);
  // Global Search untuk master
  // const globalSearchInput = createGlobalSearchInput('#jqGrid');
  // $('.ui-jqgrid-titlebar').after(createGlobalSearchInput('#jqGrid'));

  $('#gsearch_JqGrid').on('keyup', function () {
    let text = $(this).val();

    resetToolbarSearch('#jqGrid');

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

  // tombol tambah
  $("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
    caption: 'Tambah',
    buttonicon: 'fa-plus-circle',
    onClickButton: function () {
      // tambahBarang();
      $('#formModal').modal('show');
    },
    position: 'first',
    title: 'Add',
    id: "AddHeader",
    cursor: "pointer",
  });
  
  // $('#jqGrid').on('mouseenter', function() {
  //   setupKeydown("#jqGrid", 'keydown.master');
  //   $(document).off('keydown.detail');
  // });
  // $('#jqGrid').on('mouseleave', function() {
  //   $(document).off('keydown.master');
  // });

  // $('#detailItem').on('mouseenter', function() {
  //   setupKeydown("#detailItem", 'keydown.detail');
  //   $(document).off('keydown.master');
  // });
  // $('#detailItem').on('mouseleave', function() {
  //   $(document).off('keydown.detail');
  // });

  // navigasi 
  // navigasi user (custom)
  // $(document).on("keydown", function(e) {
  // $(document).off('keydown.jqgrid').on('keydown.jqgrid', function(e) { // lebih bagus karna bisa autofocus
  //   // Cek apakah ada input yang sedang fokus
  //   // if ($('input:focus, textarea:focus').length > 0) {
  //   //   return;
  //   // }

  //   if (e.which == 38 || e.which == 40 || e.which == 33 || e.which == 34 || e.which == 35 || e.which == 36) {
  //     e.preventDefault();
  //   }

  //   // ambil id yang dipilih
  //   const barisTerpilih = getSelectedRowId();
  //   const ids = $("#jqGrid").jqGrid('getDataIDs'); // console.log("Total Index: ", ids.length - 1); = 9
  //   const indexSaatIni = ids.indexOf(barisTerpilih);

  //   // Page Saat ini
  //   const halamanSaatIni = $("#jqGrid").jqGrid('getGridParam', 'page');
  //   const halamanTerakhir = $("#jqGrid").jqGrid('getGridParam', 'lastpage');
    
  //   let indexBaru;

  //   switch (e.which) {
  //     case 38: // arrow up
  //       // e.preventDefault();
  //       if (indexSaatIni > 0) {
  //         indexBaru = ids[indexSaatIni - 1];
  //         selectRow(indexBaru);
  //         console.info("NAIK");
  //         console.log("Index sebelum naik: ", indexSaatIni);
  //         console.log("Naik ke index: ", indexSaatIni - 1);
  //       } 
  //       break;
      
  //     case 40: // arrow down
  //       // e.preventDefault();
  //       if (indexSaatIni < ids.length - 1) {
  //         indexBaru = ids[indexSaatIni + 1];
  //         selectRow(indexBaru);
  //         console.info("TURUN");
  //         console.log("Index sebelum turun: ", indexSaatIni);
  //         console.log("Turun ke index: ", indexSaatIni + 1);
  //       }
  //     break;

  //     case 33: // page up
  //       // e.preventDefault();
  //       if (halamanSaatIni > 1) {
  //         console.log("PAGE UP");
  //         console.info("Pindah halaman ke: ", halamanSaatIni - 1);
  //         $("#jqGrid").jqGrid('setGridParam', { page: halamanSaatIni - 1 }).trigger('reloadGrid');
  //       }
  //       break;

  //     case 34: // page down
  //       // e.preventDefault();
  //       if (halamanSaatIni < halamanTerakhir) {
  //         console.log("PAGE DOWN");
  //         console.info("Pindah halaman ke: ", halamanSaatIni + 1);
  //         $("#jqGrid").jqGrid('setGridParam', { page: halamanSaatIni + 1 }).trigger('reloadGrid');
  //       }
  //       break;

  //     case 36: // home
  //       // e.preventDefault();
  //       if (halamanSaatIni > 1) {
  //         console.log("HOME");
  //         console.info("Halaman PERTAMA");
  //         $("#jqGrid").jqGrid('setGridParam', { page: 1 }).trigger('reloadGrid');
  //       }
  //       break;

  //     case 35: // end
  //       // e.preventDefault();
  //       if (halamanSaatIni < halamanTerakhir) {
  //         console.log("END");
  //         console.info("Halaman TERAKHIR");
  //         $("#jqGrid").jqGrid('setGridParam', { page: halamanTerakhir }).trigger('reloadGrid');
  //       }
  //       break;

  //     default:
  //       break;
  //   }


  // });
  </script>
@endpush