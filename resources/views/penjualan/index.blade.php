@extends('layouts.app')
@section('title', 'Home')

@section('content')
  
  {{-- <h1>Penjualan</h1> --}}

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
            <form id="penjualanForm" method="POST" action="{{ route('penjualan.store') }}">
              {{-- <form id="penjualanForm" method="POST" action="{{ url('penjualan') }}"> --}}
              @csrf
              {{-- <input type="hidden" name="_method" value="PUT"> --}}
              <input type="hidden" name="id" id="formId">
              <div class="form-group row">
                <label for="no_bukti" class="col-sm-2 col-form-label">No Bukti</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" id="no_bukti" name="no_bukti" required autocomplete="off" data-inputmask="'mask': 'AAA99', 'greedy': 'false', 'placeholder': '', 'showMaskOnHover': false, 'showMaskOnFocus': false" inputmode="text">
                </div>
              </div>

              <div class="form-group row">
                <label for="tgl_bukti" class="col-sm-2 col-form-label">Tanggal Bukti</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" id="tgl_bukti" name="tgl_bukti" required data-inputmask="'alias': 'datetime','inputFormat': 'dd-mm-yyyy'" inputmode="numeric">
                </div>
              </div>

              <div class="form-group row">
                <label for="nama_pelanggan" class="col-sm-2 col-form-label">Nama Pelanggan</label>
                <div class="col-sm-10">
                  <select style="width: 100%;padding: 20px" class="ui-widget-content ui-corner-all js-example-placeholder-single js-states js-example-matcher" id="nama_pelanggan" name="nama_pelanggan">
                    <option value="0">PILIH PELANGGAN</option>
                    @foreach ($pelanggans as $pelanggan)
                      <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama_pelanggan }}</option>
                    @endforeach
                  </select>
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
                <table class="table table-bordered" id="tableBarang">
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
                    <tr class="barangRow">
                      <td><input type="text" name="nama_barang[]" class="form-control" required></td>
                      <td><input type="text" name="qty[]" class="form-control qty" min="1" required></td>
                      <td><input type="text" name="harga[]" class="form-control harga" min="0" required></td>
                      <td><input type="text" name="total[]" class="form-control total" readonly></td>
                      <td>
                        <button type="button" class="btn btn-danger btn-sm removeBarangRow">-</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
          <button id="saveBtn" type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
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

    .ui-pg-table #AddHeader,
    .ui-pg-table #EditHeader,
    .ui-pg-table #DeleteHeader { 
      padding: 5px;
      /* background-color: #125824; */
      border-radius: 5px;
      margin: 0px 50px;
    }

  
</style>
@endpush

@push('script')

  <script>

  let modalMode = 'add';
  let dataBarang = [];
  let selectId = null;
  let page = 1;
  const detailGrid = "#detailItem";
  const masterGrid = "#jqGrid";
  const tableBarang = $('#tableBarang').length > 0 ? $('#tableBarang') : null;
  const barangRow = tableBarang ? tableBarang.find('.barangRow') : null;
  const barangRowCount = tableBarang ? tableBarang.find('.barangRow').length : 0;
  const setDatePicker = {
    dateFormat: 'dd-mm-yyyy',
    changeMonth: true,
    changeYear: true,
    yearRange: "1900:2099",
    maxDate: new Date(2099, 11, 31), // batas maksimum
    minDate: new Date(1900, 0, 1)    // batas minimum
  };
  const setMoneyNumeric = {
    digitGroupSeparator: ',',
    decimalCharacter: '.',
    decimalPlaces: 2,
    modifyValueOnWheel: false,
    currencySymbolPlacement: 'p',
  };
  const setQtyNumeric = {
    digitGroupSeparator: '', // Tanpa pemisah ribuan
    decimalCharacter: '.',
    decimalPlaces: 0,
    minimumValue: '0',
  };


  // Select2
  $(".js-example-placeholder-single").select2({
    placeholder: "Pilih Pelanggan",
    allowClear: true,
    dropdownParent: $('#formModal'),
  });

  // Datepicker
  $('#tgl_bukti').datepicker(setDatePicker);

  // Validate Tgl Bukti on input change
  // $(document).on('change', '#tgl_bukti', function () {
  //   const tglBukti = $(this).val();
  //   if (!isValidDate(tglBukti)) {
  //     alert('Tanggal Bukti tidak valid. Format yang diharapkan: dd-mm-yyyy');
  //     $(this).val(''); // Kosongkan input jika tidak valid
  //   }
  // });

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

      console.log(selectId);
      if (selectId) {
        selectRow(selectId);
        // $("#jqGrid").jqGrid('setSelection', selectId);
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
    // console.log("text: ", text);

    // ga perlu reset toolbar search, karena sudah di reset di beforeSearch
  
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

  // END Filter Bar Master
  // END MASTER GRID

  // Total, Harga, Qty
  // Fungsi untuk menghitung total harga dan qty
  // function calculateTotal() {
  //   let total = 0;

  //   $('.barangRow').each(function() {
  //     const qty = parseFloat($(this).find("input[name='qty[]']").val()) || 0;
  //     const harga = parseFloat($(this).find("input[name='harga[]']").val()) || 0;
  //     const rowTotal = qty * harga;

  //     $(this).find("input[name='total[]']").val(rowTotal.toFixed(2));
  //     total += rowTotal;
  //   });

  //   // Update total keseluruhan jika ada elemen untuk menampung total
  //   // if ($('#totalKeseluruhan').length) {
  //   //   $('#totalKeseluruhan').text(total.toFixed(2));
  //   // }
  // }

  function calculateTotal() {
    $('.barangRow').each(function () {
      const $row = $(this);
      const $qtyInput = $row.find('.qty');
      const $hargaInput = $row.find('.harga');
      const $totalInput = $row.find('.total');

      // Kalau salah satu input tidak ditemukan, skip baris ini
      // if ($qtyInput.length === 0 || $hargaInput.length === 0 || $totalInput.length === 0) {
      //   console.warn('Input tidak lengkap dalam baris ini, lewati.');
      //   return;
      // }

      // Inisialisasi AutoNumeric jika belum
      let anQty = AutoNumeric.getAutoNumericElement($qtyInput[0]);
      if (!anQty) {
        anQty = new AutoNumeric($qtyInput[0], setQtyNumeric);
      }

      let anHarga = AutoNumeric.getAutoNumericElement($hargaInput[0]);
      if (!anHarga) {
        anHarga = new AutoNumeric($hargaInput[0], setMoneyNumeric);
      }

      let anTotal = AutoNumeric.getAutoNumericElement($totalInput[0]);
      if (!anTotal) {
        anTotal = new AutoNumeric($totalInput[0], setMoneyNumeric);
      }

      // Event input qty dan harga
      $qtyInput.on('input', function () {
        updateTotalRow($row);
        // updateGrandTotal();
      });

      $hargaInput.on('input', function () {
        const value = anHarga.getNumber();
        anHarga.set(value); // reformat langsung
        updateTotalRow($row);
        // updateGrandTotal();
      });

      // Hitung total pertama kali
      updateTotalRow($row);
    });
  }


  // Event untuk menghitung total saat qty atau harga berubah
  $(document).on('input', 'input[name="qty[]"], input[name="harga[]"]', function() {
    calculateTotal();
  });

  // Initialize auto numeric
  $('#tableBarang tbody tr').each(function () {
    initAutoNumericRow($(this));
  });

  // tombol tambah
  $("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
    caption: ' Tambah',
    buttonicon: 'fa-fw fa-plus-circle',
    onClickButton: function () {
      // tambahBarang();
      // $('#formModal').modal('show');
      openModal('add');

    },
    position: 'first',
    title: 'Add',
    id: "AddHeader",
    cursor: "pointer",
  });

  // tombol edit
  $("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
    caption: ' Ubah',
    buttonicon: 'fa-fw fa-pencil-alt',
    onClickButton: function () {
      // $('#formModal').modal('show');
      // openModal('edit', data = {
      //   id: "78",
      //   no_bukti: "BKT001",
      //   tgl_bukti: "2023-10-01",
      //   nama_pelanggan: "4",
      //   barang: [
      //     { nama_barang: "Barang A", qty: 2, harga: 10000, total: 20000 },
      //     { nama_barang: "Barang B", qty: 1, harga: 15000, total: 15000 }
      //   ],
      // });

      const selectedId = $('#jqGrid').jqGrid('getGridParam', 'selrow');
      if (!selectedId) {
        alert('Silakan pilih data yang ingin diedit');
        return;
      }

      $.ajax({
        url: `/penjualan/${selectedId}`,
        method: 'GET',
        success: function (res) {
          openModal('edit', res);
        },
        error: function (err) {
          alert('Gagal mengambil data');
          console.error(err);
        }
      });

    },
    position: 'last',
    title: 'Edit',
    id: "EditHeader",
    cursor: "pointer",
  });

  // Simpan (Tambah/Edit)
  $('#saveBtn').on('click', function(e) {
    e.preventDefault(); // Hindari submit form default
    
    let dataBarang = []; // Reset array setiap klik

    const id = $('#formId').val();

    if (modalMode === 'edit' && !id) {
      alert('ID tidak ditemukan. Pastikan Anda memilih data yang akan diedit.');
      return;
    }

    // Ambil semua nilai array dari inputan
    // const nama_barang = $("input[name='nama_barang[]']").map(function(){ return $(this).val(); }).get();
    // const qty = $("input[name='qty[]']").map(function(){ return $(this).val(); }).get();
    // const harga = $("input[name='harga[]']").map(function(){ return $(this).val(); }).get();
    // const total = $("input[name='total[]']").map(function(){ return $(this).val(); }).get();

    $('.barangRow').each(function() {
      const $row = $(this);
      
      const $nama = $row.find("input[name='nama_barang[]']").val();
      const $qty = $row.find('.qty');
      const $harga = $row.find('.harga');

      // Dapatkan instance AutoNumeric
      const anQty = AutoNumeric.getAutoNumericElement($qty[0]);
      const anHarga = AutoNumeric.getAutoNumericElement($harga[0]);

      // Set nilai input ke angka mentah (raw number)
      const qtyValue = anQty ? anQty.getNumber() : 0;
      const hargaValue = anHarga ? anHarga.getNumber() : 0;

      dataBarang.push({
        nama_barang: $nama,
        qty: qtyValue,
        harga: hargaValue,
        // total: total
      });
    });

    const formData = {
      _token: '{{ csrf_token() }}',
      no_bukti: $('#no_bukti').val(),
      tgl_bukti: $('#tgl_bukti').val(),
      nama_pelanggan: $('#nama_pelanggan').val(),
      barang: dataBarang,
      sortname: $('#jqGrid').jqGrid('getGridParam', 'sortname'),
      sortorder: $('#jqGrid').jqGrid('getGridParam', 'sortorder'),
      rows: parseInt($('#jqGrid').jqGrid('getGridParam', 'rowNum')),
    };

    let url = '/penjualan';
    let type = 'POST';

    if (modalMode === 'edit') {
      url = `/penjualan/${id}`;
      type = 'PUT';
    }

    $.ajax({
      url: url,
      type: type,
      data: formData,
      success: function(data) {
        $('#formModal').modal('hide');
        // $('#jqGrid').trigger('reloadGrid');
        // alert('Data berhasil disimpan');

        // Simpan id ke global
        selectId = data.id;
        page = data.page;

        console.log("ID yang disimpan:", selectId);
        console.log("Page tujuan:", page);

        $('#jqGrid').setGridParam({
          page: page
        }).trigger('reloadGrid');
      }
    });
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

  // Tambahkan baris barang
  $(document).on('click', '#addBarangRow', function() {
    
    // tambahkan baris baru
    tableBarang.find('tbody').append(HTMLBarisBarangBaru());
  });

  // Hapus baris barang
  $(document).on('click', '.removeBarangRow', function() {
    if (tableBarang.find('tbody tr').length > 1) {
      $(this).closest('tr').remove();
    } 
  });

  // Reset form ketika modal ditutup
  $('#formModal').on('hidden.bs.modal', function () {
    modalMode = 'add'; // Set mode ke tambah
    $('#penjualanForm')[0].reset(); // Reset form
    $('#formId').val(''); // Kosongkan ID form
    $('#nama_pelanggan').val('0').trigger('change'); // Reset select2
    tableBarang.find('tbody').html(HTMLBarisBarangBaru()); // Reset tabel barang
  });

  </script>
@endpush