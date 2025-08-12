@extends('layouts.app')
@section('title', 'Home')

@section('content')

    {{-- @dd($generateNoBukti) --}}

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

    <!-- Modal Form Penjualan -->
    <div class="modal fade" id="formModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="formModalLabel" aria-hidden="true">
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
                            {{-- <form id="penjualanForm" method="POST" action="{{ url('penjualan') }}"> --}}
                            @csrf
                            {{-- <input type="hidden" name="_method" value="PUT"> --}}
                            <input type="hidden" name="id" id="formId">
                            <div class="form-group row">
                                <label for="no_bukti" class="col-sm-2 col-form-label">No Bukti</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="no_bukti" name="no_bukti" required
                                        autocomplete="off"
                                        data-inputmask="'mask': 'AAA-AA-9999', 'greedy': 'false', 'placeholder': '', 'showMaskOnHover': false, 'showMaskOnFocus': false"
                                        inputmode="text" readonly>
                                    <span class="text-danger error-text no_bukti_error"></span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="tgl_bukti" class="col-sm-2 col-form-label">Tanggal Bukti</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="tgl_bukti" name="tgl_bukti" required
                                        data-inputmask="'alias': 'datetime','inputFormat': 'dd-mm-yyyy'"
                                        inputmode="numeric">
                                    <span class="text-danger error-text tgl_bukti_error"></span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nama_pelanggan" class="col-sm-2 col-form-label">Nama Pelanggan</label>
                                <div class="col-sm-10">
                                    <select style="width: 100%;padding: 20px"
                                        class="ui-widget-content ui-corner-all js-example-placeholder-single js-states js-example-matcher"
                                        id="nama_pelanggan" name="nama_pelanggan">
                                        <option value="0">PILIH PELANGGAN</option>
                                        @foreach ($pelanggans as $pelanggan)
                                            <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama_pelanggan }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-text nama_pelanggan_error"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="totalSemuaBarang" class="col-sm-2 col-form-label">Harga Total</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="totalSemuaBarang" required readonly>
                                </div>
                            </div>
                            {{-- <input type="hidden" name="id" id="formId">
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
                                                <button type="button" class="btn btn-success btn-sm"
                                                    id="addBarangRow">+</button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="barangRow">
                                            <td>
                                                <input type="text" name="nama_barang[]" class="form-control namabarang"
                                                    required>
                                                <span class="text-danger error-text nama_barang_error"></span>
                                            </td>
                                            <td>
                                                <input type="text" name="qty[]" class="form-control qty" min="1"
                                                    required>
                                                <span class="text-danger error-text qty_error"></span>
                                            </td>
                                            <td>
                                                <input type="text" name="harga[]" class="form-control harga"
                                                    min="1" required>
                                                <span class="text-danger error-text harga_error"></span>
                                            </td>
                                            <td>
                                                <input type="text" name="total[]" class="form-control total" readonly>
                                            </td>
                                            <td>
                                                <button type="button"
                                                    class="btn btn-danger btn-sm removeBarangRow">-</button>
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
                    <button type="button" class="btn btn-danger d-none" id="deleteBtn">Hapus</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Export -->
    <div class="modal fade" id="exportForm" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exportFormLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportFormLabel">
                        Export Excel
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-body">
                        <form id="export-form">
                            @csrf

                            <div class="form-group row">
                                <label for="start_range" class="col-sm-4 col-form-label">Data Ke </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="start_range" name="start_range"
                                        required autocomplete="off"
                                        data-inputmask="'alias': 'integer', 'placeholder': '', 'rightAlign': false, 'showMaskOnHover': false, 'showMaskOnFocus': false"
                                        inputmode="number">
                                    <span class="text-danger error-text start_range_error"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="end_range" class="col-sm-4 col-form-label">S.D Data Ke</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="end_range" name="end_range" required
                                        autocomplete="off"
                                        data-inputmask="'alias': 'integer', 'placeholder': '', 'rightAlign': false, 'showMaskOnHover': false, 'showMaskOnFocus': false"
                                        inputmode="number">
                                    <span class="text-danger error-text end_range_error"></span>
                                </div>
                            </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                    <button id="confirmExportBtn" type="submit" class="btn btn-primary">Simpan</button>
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

        #gsh_jqGrid_rn>div {
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
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            yearRange: "1900:2099",
            maxDate: new Date(2099, 11, 31), // batas maksimum
            minDate: new Date(1900, 0, 1) // batas minimum
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
            decimalPlaces: 2,
            minimumValue: '0',
        };


        // Select2
        $(".js-example-placeholder-single").select2({
            placeholder: "PILIH PELANGGAN",
            allowClear: true,
            dropdownParent: $('#formModal'),
        });

        // Datepicker
        $('#tgl_bukti').datepicker(setDatePicker);

        // Total
        const totalHarga = document.querySelector('#totalSemuaBarang');
        const NumericTotal = new AutoNumeric(totalHarga, setMoneyNumeric);

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
            colModel: [{
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
            cmTemplate: {
                required: true
            },
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
            onSelectRow: function(id) {
                jQuery("#detailItem").jqGrid('setGridParam', {
                    url: "penjualan/" + id + "/detail",
                    page: 1
                });
                jQuery("#detailItem").trigger('reloadGrid');

            },
            gridComplete: function(response) {
                const ids = $("#jqGrid").jqGrid('getDataIDs');

                // console.log(response);

                // console.log(selectId);
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
        $("#jqGrid").jqGrid('navGrid', '#jqGridPager', {
            edit: false,
            add: false,
            del: false,
            search: false,
            refresh: false
        });

        // filter bar master 
        // Filter Bar => Untuk mencari data
        $('#jqGrid').jqGrid('filterToolbar', {
            autosearch: true,
            stringResult: true,
            searchOnEnter: false,
            defaultSearch: "cn",
            multipleSearch: true,
            beforeSearch: function() {
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
            } else if (buttonId === 'reset_search_detail') {
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

        $('#gsearch_JqGrid').on('keyup', function() {
            let text = $(this).val();

            $('#gs_no_bukti').val('');
            $('#gs_tgl_bukti').val('');
            $('#gs_nama_pelanggan').val('');
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
            $('.barangRow').each(function() {
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
                $qtyInput.on('input', function() {
                    updateTotalRow($row);
                    updateGrandTotal();
                });

                $hargaInput.on('input', function() {
                    const value = anHarga.getNumber();
                    anHarga.set(value); // reformat langsung
                    updateTotalRow($row);
                    updateGrandTotal();
                });

                // Hitung total pertama kali
                updateTotalRow($row);

            });
        }


        // Event untuk menghitung total saat qty atau harga berubah
        $(document).on('input', 'input[name="qty[]"], input[name="harga[]"]', function() {
            calculateTotal();
            updateGrandTotal();
        });

        // Initialize auto numeric
        $('#tableBarang tbody tr').each(function() {
            initAutoNumericRow($(this));
        });

        // tombol tambah
        $("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
            caption: ' Tambah',
            buttonicon: 'fa-fw fa-plus-circle',
            onClickButton: function() {
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
            onClickButton: function() {
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
                  showNotificationDialog(`Pilih data yg mau diedit!`);
                  return;
                }

                $.ajax({
                    url: `/penjualan/${selectedId}`,
                    method: 'GET',
                    success: function(res) {
                        openModal('edit', res);

                    },
                    error: function(err) {
                      showNotificationDialog(`Gagal mengambil data!`);
                      console.error(err);
                    }
                });

            },
            position: 'last',
            title: 'Edit',
            id: "EditHeader",
            cursor: "pointer",
        });

        // tombol hapus
        $("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
            caption: ' Hapus',
            buttonicon: 'fa-fw fa-trash-alt',
            onClickButton: function() {

                const selectedId = $('#jqGrid').jqGrid('getGridParam', 'selrow');
                if (!selectedId) {
                  showNotificationDialog(`Pilih data yg mau dihapus!`);
                  return;
                }

                $.ajax({
                    url: `/penjualan/${selectedId}`,
                    method: 'GET',
                    success: function(res) {
                        openModal('delete', res);
                    },
                    error: function(err) {
                        showNotificationDialog(`Tidak dapat mengambil data!`);
                        console.error(err);
                    }
                });

            },
            position: 'last',
            title: 'Delete',
            id: "DeleteHeader",
            cursor: "pointer",
        });

        // Simpan (Tambah/Edit)
        $('#saveBtn').on('click', function(e) {
            e.preventDefault(); // Hindari submit form default

            let dataBarang = []; // Reset array setiap klik

            const id = $('#formId').val();
            let postData = $('#jqGrid').jqGrid('getGridParam', 'postData');
            let filters = postData.filters;
            let globalSearchKey = postData.global_search;

            if (modalMode === 'edit' && !id) {
                showNotificationDialog(`Pilih data yg mau diedit dulu!`);
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

            if (filters) {
                formData.filters = filters;
                url += '?_search=true'
            }

            if (globalSearchKey) {
                formData.global_search = globalSearchKey;
            }

            $.ajax({
                url: url,
                type: type,
                data: formData,
                beforeSend: function() {
                    // Hapus SEMUA status error dari percobaan sebelumnya.
                    // Ini akan memastikan field yang sudah valid tidak lagi berwarna merah.
                    $('.error-text').text('');
                    $('.form-control').removeClass('is-invalid');
                },
                success: function(data) {
                    $('#formModal').modal('hide');
                    // $('#jqGrid').trigger('reloadGrid');
                    // alert('Data berhasil disimpan');

                    showNotificationDialog(`Berhasil Disimpan!`);

                    // Simpan id ke global
                    selectId = data.id;
                    page = data.page;

                    console.log("ID yang disimpan:", selectId);
                    console.log("Page tujuan:", page);

                    $('#jqGrid').setGridParam({
                        page: page
                    }).trigger('reloadGrid');
                },
                error: function(jqXHR, textStatus, errorThrown) {

                    if (jqXHR.status === 422) {
                        // Ambil objek 'errors' dari respons JSON
                        const errors = jqXHR.responseJSON.errors;

                        // Tampilkan pesan error di tempat yang tepat
                        $.each(errors, function(key, value) {
                            const errorMessage = value[0];

                            // Cek apakah ini error untuk array 'barang'
                            if (key.startsWith('barang.')) {
                                // Debug
                                // console.group("Debugging Error untuk key: " + key);

                                // Pecah kuncinya: "barang.0.qty" -> ["barang", "0", "qty"]
                                const parts = key.split('.');
                                const index = parts[1]; // Indeks baris, contoh: 0
                                const fieldName = parts[2]; // Nama field, contoh: "qty"
                                // console.log("Mencari input ke-" + index + " dengan nama '" + fieldName + "[]'");

                                // cari semua input dengan nama yang cocok
                                const allInputs = $('input[name="' + fieldName + '[]"]');
                                // console.log("Ditemukan total " + allInputs.length + " input dengan nama '" + fieldName + "[]'");

                                // Ambil input yang spesifik menggunakan indeksnya
                                const inputField = allInputs.eq(index);
                                // console.log("Input spesifik ditemukan:", inputField.length > 0 ? "Ya" : "Tidak", inputField);

                                if (inputField.length > 0) {
                                    // Tampilkan error di span yang ada di sel yang sama
                                    const errorSpan = inputField.closest('td').find(
                                        '.error-text');
                                    // console.log("Span Error ditemukan:", errorSpan.length > 0 ? "Ya" : "Tidak", errorSpan);
                                    errorSpan.text(errorMessage);
                                    inputField.addClass('is-invalid');
                                }
                                // console.groupEnd();


                            } else {
                                // Logika lama untuk field non-array (no_bukti, dll)
                                const errorClass = key.replace(/\./g, '_');
                                $('.' + errorClass + '_error').text(errorMessage);
                                $('#' + key).addClass('is-invalid');
                                // console.log("Error untuk field " + key + ": " + errorMessage);
                            }
                        });

                    } else {
                        // Untuk error lain (500, 404, dll)
                        showNotificationDialog('Terjadi kesalahan tidak terduga');
                        console.error('Terjadi kesalahan server: ' + (jqXHR.responseJSON.error || errorThrown));
                    }
                }
            });
        });

        // Hapus (misal tombol hapus di modal)
        $('#deleteBtn').on('click', function() {
            const id = $('#formId').val();

            let postData = $('#jqGrid').jqGrid('getGridParam', 'postData');
            let filters = postData.filters;
            let globalSearchKey = postData.global_search;

            const formData = {
                _token: '{{ csrf_token() }}',
                sortname: $('#jqGrid').jqGrid('getGridParam', 'sortname'),
                sortorder: $('#jqGrid').jqGrid('getGridParam', 'sortorder'),
                rows: parseInt($('#jqGrid').jqGrid('getGridParam', 'rowNum')),
                sortname: $('#jqGrid').jqGrid('getGridParam', 'sortname'),
                sortorder: $('#jqGrid').jqGrid('getGridParam', 'sortorder'),
                rows: parseInt($('#jqGrid').jqGrid('getGridParam', 'rowNum')),
            }

            let url = `/penjualan/${id}`;

            if (filters) {
                formData.filters = filters;
                url += '?_search=true'
            }

            if (globalSearchKey) {
                formData.global_search = globalSearchKey;
            }

            $.ajax({
                url: url,
                type: 'DELETE',
                data: formData,
                success: function(data) {
                    $('#formModal').modal('hide');

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

        // tombol export data ke excel
        $("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
            caption: 'Excel',
            buttonicon: 'fa-fw fa-file-excel',
            onClickButton: function() {
                exportModal('excel');
            },
            position: 'last',
            title: 'Export Data',
            id: "ExportHeader",
            cursor: "pointer",
        });

        // Tombol export PDF langsung
        $("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
            caption: 'PDF',
            buttonicon: 'fa-fw fa-print',
            onClickButton: function() {
                exportModal('pdf');
            },
            position: 'last',
            title: 'Export PDF',
            id: "PDFHeader",
            cursor: "pointer",
        });

        $('#confirmExportBtn').on('click', function(e) {
            e.preventDefault();

            $('.error-text').text('');
            $('#start_range_input, #end_range_input').removeClass('is-invalid');

            const mode = $(this).data('mode');
            if (!mode) {
                showNotificationDialog(`Mode ${mode} tidak ada!`);
                return;
            }

            // 1. Ambil semua parameter filter dan sorting dari grid
            const exportData = $('#jqGrid').jqGrid('getGridParam', 'postData');
            let totalRecords = $("#jqGrid").jqGrid('getGridParam', 'records');

            // Ambil sord, sidx detail grid
            const detailSidx = $('#detailItem').jqGrid('getGridParam', 'sortname');
            const detailSord = $('#detailItem').jqGrid('getGridParam', 'sortorder');

            exportData.sord_detail = detailSord;
            exportData.sidx_detail = detailSidx;

            // Kirim data start_range dan end_range
            exportData.start_range = $('#start_range').val();
            exportData.end_range = $('#end_range').val();
            // exportData._token = '{{ csrf_token() }}';
            exportData.record = totalRecords;

            // 2. PERBAIKAN: Buat endpoint khusus validasi atau gunakan parameter validasi
            // Untuk PDF, kita perlu validasi dulu sebelum buka tab baru
            if (mode === 'pdf') {
                // Opsi 1: Gunakan endpoint validasi terpisah
                validateAndExportPDF(exportData);
            } else {
                // Opsi 2: Untuk Excel, lanjutkan dengan logic yang sudah ada
                exportExcel(exportData);
            }

            // // 2. Gunakan fetch API untuk permintaan yang lebih canggih
            // fetch(`/penjualan/export/${mode}`, {
            //     method: 'POST',
            //     headers: {
            //         // 'Content-Type' tidak perlu diatur untuk FormData
            //         'Accept': 'application/json, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //         'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //     },
            //     body: JSON.stringify(exportData)
            // })
            // .then(response => {
            //     // 3. Cek status respons SEBELUM membaca body
            //     if (response.ok) {
            //         // Jika status OK (2xx), berarti ini adalah file.
            //         // Ambil header dan kembalikan blob-nya.
            //         const disposition = response.headers.get('Content-Disposition');
            //         return response.blob().then(blob => ({ blob, disposition }));
            //     } else {
            //         // Jika status tidak OK (4xx, 5xx), berarti ini adalah error JSON.
            //         // Baca sebagai JSON dan lemparkan sebagai error untuk ditangkap oleh .catch().
            //         return response.json().then(errorData => {
            //             throw errorData;
            //         });
            //     }
            // })
            // .then(({ blob, disposition }) => {

            //     // jika mode pdf
            //     if (mode === 'pdf') {
            //       // Ini akan mengubah { _search: false, sidx: 'id', ... } menjadi "_search=false&sidx=id&..."
            //       const queryString = $.param(exportData);
            //       const exportUrl = `/penjualan/report/view${mode}?${queryString}`;

            //       // 3. Buka URL ekspor di tab baru dengan query string yang sudah dibuat
            //       window.open(exportUrl, '_blank');

            //       // 4. Tutup modal setelah proses dimulai
            //       $('#exportForm').modal('hide');
            //       return
            //     }

            //     // 4. JIKA SUKSES, proses blob menjadi unduhan
            //     let filename = "laporan.xlsx";
            //     if (disposition && disposition.indexOf('attachment') !== -1) {
            //         const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            //         const matches = filenameRegex.exec(disposition);
            //         if (matches != null && matches[1]) {
            //             filename = matches[1].replace(/['"]/g, '');
            //         }
            //     }

            //     const downloadUrl = window.URL.createObjectURL(blob);
            //     const a = document.createElement('a');
            //     a.style.display = 'none';
            //     a.href = downloadUrl;
            //     a.download = filename;
            //     document.body.appendChild(a);
            //     a.click();
            //     window.URL.revokeObjectURL(downloadUrl);
            //     document.body.removeChild(a);

            //     $('#exportForm').modal('hide');
            // })
            // .catch(errorData => {
            //     // 5. JIKA GAGAL, tangkap error yang dilempar dan tampilkan
            //     if (errorData && errorData.errors) {
            //         $.each(errorData.errors, function(key, value) {
            //             $('.' + key + '_error').text(value[0]);
            //             $('#' + key + '_input').addClass('is-invalid');
            //         });
            //     } else {
            //         console.error('Terjadi kesalahan tidak terduga:', errorData.message);                    
            //     }
            // });

            // Kirim satu permintaan AJAX yang bisa menangani blob atau JSON
            // $.ajax({
            //     url: `/penjualan/export/${mode}`,
            //     type: 'POST',
            //     data: exportData,
            //     xhrFields: {
            //         responseType: 'blob' // default akan dianggap file
            //     },
            //     beforeSend: function(xhr) {
            //       xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            //       $('.error-text').text('');
            //       $('#start_range, #end_range').removeClass('is-invalid');
            //     },
            //     success: function(blob, status, xhr) {
            //       const disposition = xhr.getResponseHeader('Content-Disposition');
            //       let filename = "laporan.xlsx";

            //       if (disposition && disposition.indexOf('attachment') !== -1) {
            //           const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            //           const matches = filenameRegex.exec(disposition);
            //           if (matches != null && matches[1]) {
            //               filename = matches[1].replace(/['"]/g, '');
            //           }

            //           const downloadUrl = window.URL.createObjectURL(blob);
            //           const a = document.createElement('a');
            //           a.href = downloadUrl;
            //           a.download = filename;
            //           document.body.appendChild(a);
            //           a.click();
            //           window.URL.revokeObjectURL(downloadUrl);
            //           document.body.removeChild(a);

            //           $('#exportForm').modal('hide');
            //       } else {
            //           // Kalau ternyata sukses, tapi bukan file (bisa error dalam blob)
            //           const reader = new FileReader();
            //           reader.onload = function() {
            //               try {
            //                   const errorData = JSON.parse(reader.result);
            //                   if (errorData.errors) {
            //                       $.each(errorData.errors, function(key, value) {
            //                           $(`.${key}_error`).text(value[0]);
            //                           $(`#${key}`).addClass('is-invalid');
            //                       });
            //                   } else {
            //                       alert(
            //                           "Gagal memproses file, server mengembalikan pesan tak dikenal.");
            //                   }
            //               } catch (e) {
            //                   alert("Tidak dapat membaca respon dari server.");
            //               }
            //           };
            //           reader.readAsText(blob);
            //       }
            //     },
            //     error: function(xhr, status, error, blob) {

            //       // const reader = new FileReader();
            //       // reader.onload = function() {
            //       //     try {
            //       //         const errorData = JSON.parse(reader.result);
            //       //         console.log(errorData);
            //       //         if (errorData.errors) {
            //       //             $.each(errorData.errors, function(key, value) {
            //       //                 $(`.${key}_error`).text(value[0]);
            //       //                 $(`#${key}`).addClass('is-invalid');
            //       //             });
            //       //         } else {
            //       //             alert(
            //       //                 "Gagal memproses file, server mengembalikan pesan tak dikenal.");
            //       //         }
            //       //     } catch (e) {
            //       //         alert("Tidak dapat membaca respon dari server.");
            //       //     }
            //       // };

            //       // // Kita bisa coba baca langsung dari responseText tanpa FileReader.
            //       // if (jqXHR.status === 422 && jqXHR.responseText) {
            //       //     try {
            //       //         const errorData = JSON.parse(jqXHR.responseText);
            //       //         if (errorData.errors) {
            //       //             $.each(errorData.errors, function(key, value) {
            //       //                 // Tampilkan error di span yang sesuai
            //       //                 // Asumsi nama class error adalah "namafield_error"
            //       //                 // dan ID input adalah "namafield_input"
            //       //                 $('.' + key + '_error').text(value[0]);
            //       //                 $('#' + key + '_input').addClass('is-invalid');
            //       //             });
            //       //         }
            //       //     } catch (e) {
            //       //         // Jika gagal parsing, tampilkan error generik
            //       //         alert('Terjadi kesalahan tidak terduga saat memproses error.');
            //       //         console.error("Gagal mem-parsing JSON dari respons error:", jqXHR.responseText);
            //       //     }
            //       // } else {
            //       //     // Untuk error lain (500, dll) atau jika responseText kosong
            //       //     alert('Terjadi kesalahan server. Status: ' + jqXHR.status);
            //       // }
            //     }
            // });


            // 2. Kirim permintaan validasi via AJAX
            // $.ajax({
            //     url: 'route("penjualan.export.validate")',
            //     type: 'POST',
            //     data: exportData,
            //     dataType: 'json',
            //     beforeSend: function() {
            //         // Bersihkan error lama sebelum validasi baru
            //         $('.error-text').text('');
            //         $('#start_range, #end_range').removeClass('is-invalid');
            //     },
            //     success: function(response) {
            //         // 3. JIKA VALIDASI SUKSES, LANJUTKAN UNDUH
            //         console.log(response.message);
            //         triggerDownload(mode, exportData);
            //         $('#exportForm').modal('hide');
            //     },
            //     error: function(jqXHR) {
            //         // 4. JIKA VALIDASI GAGAL, TAMPILKAN ERROR
            //         if (jqXHR.status === 422) {
            //             const errors = jqXHR.responseJSON.errors;
            //             $.each(errors, function(key, value) {
            //                 // Tampilkan error di span yang sesuai
            //                 $('.' + key + '_error').text(value[0]);
            //                 $('#' + key + '_input').addClass('is-invalid');
            //             });
            //         } else {
            //             alert('Terjadi kesalahan server.');
            //         }
            //     }
            // });

            // Fungsi helper untuk memicu unduhan menggunakan form dinamis
            // function triggerDownload(mode, data) {
            //     const form = $('<form>', {
            //         'action': `/penjualan/export/${mode}`,
            //         'method': 'POST',
            //         'target': '_blank'
            //     });

            //     $.each(data, function(key, value) {
            //         form.append($('<input>', {
            //             'type': 'hidden',
            //             'name': key,
            //             'value': value
            //         }));
            //     });

            //     form.appendTo('body').submit().remove();
            // }

            // Ini akan mengubah { _search: false, sidx: 'id', ... } menjadi "_search=false&sidx=id&..."
            // const queryString = $.param(exportData);
            // const exportUrl = `/penjualan/export/${mode}?${queryString}`;

            // 3. Buka URL ekspor di tab baru dengan query string yang sudah dibuat
            // window.open(exportUrl, '_blank');

            // 4. Tutup modal setelah proses dimulai
            // $('#exportForm').modal('hide');
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
                updateGrandTotal();
            }
        });

        // Reset form ketika modal ditutup
        $('#formModal').on('hidden.bs.modal', function() {
            modalMode = 'add'; // Set mode ke tambah
            tableBarang.find('tbody').html(HTMLBarisBarangBaru()); // Reset tabel barang
            resetFormAndValidation(); // Bersihkan validasi
        });

        // 
        // $('#formModal').on('shown.bs.modal', function (e) {
        //   updateGrandTotal()
        //   // Fokus ke input misalnya
        //   $('#no_bukti').focus();
        // });
    </script>
@endpush
