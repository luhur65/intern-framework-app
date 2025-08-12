// PERBAIKAN 1: Ambil CSRF token yang benar
const csrfToken = $('meta[name="csrf-token"]').attr('content') ||
  $('input[name="_token"]').val() ||
  document.querySelector('meta[name="csrf-token"]')?.content;

if (!csrfToken) {
  console.error('CSRF token tidak ditemukan!');
  showNotificationDialog('Error: CSRF token tidak ditemukan');
  // return;
}

// Dialog JQuery
function showNotificationDialog(message) {
  $('#notificationMessage').text(message);
  $('#notificationDialog').dialog({
    modal: true,
    zIndex: 9999,
    buttons: {
      "Ok": function () {
        $(this).dialog("close");
      }
    }
  });
}

// Fungsi untuk mendapatkan ID baris yang dipilih
function getSelectedRowId() {
  return $("#jqGrid").jqGrid('getGridParam', 'selrow');
}

// Fungsi untuk memilih baris berdasarkan ID
function selectRow(id) {
  $("#jqGrid").jqGrid('setSelection', id);
}

function highlightText(cell, keyword) {
  if (!keyword) return;

  const textOnly = cell.text().trim().toUpperCase();

  // Lewati kalau isinya "TIDAK ADA DATA"
  if (textOnly === 'TIDAK ADA DATA') return;

  const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const regex = new RegExp(`(${escapedKeyword})`, "gi");

  cell.contents().each(function () {
    if (this.nodeType === 3) { // Node teks
      const newHtml = this.nodeValue.replace(regex, '<span class="highlight">$1</span>');
      $(this).replaceWith(newHtml);
    }
  });
}

// function highlightText(cell, keyword) {

//   if (!keyword) return;
//   const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
//   const regex = new RegExp(`(${escapedKeyword})`, "gi");
//   const updatedHtml = cell.html().replace(regex, '<span class="highlight">$1</span>');
//   cell.html(updatedHtml);

// }

function higligthPencarian(grid) {
  const postData = grid.getGridParam("postData");
  const filtersJSON = postData.filters;
  const globalSearch = postData.global_search;
  const gridId = $(grid).getGridParam().id;

  // Bersihkan highlight lama
  $(grid).find("td").each(function () {
    const cleanText = $(this).html().replace(/<span class="highlight">(.*?)<\/span>/gi, "$1");
    $(this).html(cleanText);
  });

  let toolbarHasFilter = false;

  // Highlight dari toolbar filter
  if (filtersJSON && typeof filtersJSON === "string") {
    const filterObj = JSON.parse(filtersJSON);
    if (Array.isArray(filterObj.rules)) {
      const filterRules = filterObj.rules;
      toolbarHasFilter = filterRules.some(rule => rule.data && rule.data.trim().length > 0);

      if (toolbarHasFilter) {
        // Reset global search input
        $('#gsearch').val('');
        delete postData.global_search;

        filterRules.forEach(rule => {
          if (rule.data && rule.data.trim().length > 0) {
            const selector = `tbody tr td[aria-describedby="${gridId}_${rule.field}"]`;
            $(grid).find(selector).each(function () {
              highlightText($(this), rule.data);
            });
          }
        });

        return; // stop di sini jika toolbar aktif
      }
    }
  }

  // Jika tidak ada filter toolbar, cek global search
  if (globalSearch && globalSearch.trim().length > 0) {
    $(grid).find("td").each(function () {
      highlightText($(this), globalSearch);
    });
  }

}

function resetSearch(gridSelector) {

  if (gridSelector == "#detailItem") {
    $('#gs_nama_barang').val('');
    $('#gs_qty').val('');
    $('#gs_harga').val('');
    $('#gs_total').val('');

  } else {
    $('#gs_no_bukti').val('');
    $('#gs_tgl_bukti').val('');
    $('#gs_nama_pelanggan').val('');

  }

}

// Function yang lebih modular
function initializeGridNavigation(gridSelector) {
  var $grid = $(gridSelector);
  var gridId = $grid.attr('id');

  // Bersihkan event handler sebelumnya untuk grid ini
  $grid.off('keydown.gridNav');

  // Tambahkan tabindex agar bisa menerima focus
  $grid.attr('tabindex', '0');

  // Event handler untuk grid ini saja
  $grid.on('keydown.gridNav', function (e) {
    if (e.which == 38 || e.which == 40 || e.which == 33 || e.which == 34 || e.which == 35 || e.which == 36) {
      e.preventDefault();
      $grid.focus();
    }

    const barisTerpilih = $grid.jqGrid('getGridParam', 'selrow');
    const ids = $grid.jqGrid('getDataIDs');
    const indexSaatIni = ids.indexOf(barisTerpilih);

    const halamanSaatIni = $grid.jqGrid('getGridParam', 'page');
    const halamanTerakhir = $grid.jqGrid('getGridParam', 'lastpage');
    let indexBaru;

    switch (e.which) {
      case 38: // up
        if (indexSaatIni > 0) {
          indexBaru = ids[indexSaatIni - 1];
          $grid.jqGrid('setSelection', indexBaru);
        }
        break;
      case 40: // down
        if (indexSaatIni < ids.length - 1) {
          indexBaru = ids[indexSaatIni + 1];
          $grid.jqGrid('setSelection', indexBaru);
        }
        break;
      case 33: // page up
        if (halamanSaatIni > 1) {
          $grid.jqGrid('setGridParam', { page: halamanSaatIni - 1 }).trigger('reloadGrid');
        }
        break;
      case 34: // page down
        if (halamanSaatIni < halamanTerakhir) {
          $grid.jqGrid('setGridParam', { page: halamanSaatIni + 1 }).trigger('reloadGrid');
        }
        break;
      case 36: // home
        if (halamanSaatIni > 1) {
          $grid.jqGrid('setGridParam', { page: 1 }).trigger('reloadGrid');
        }
        break;
      case 35: // end
        if (halamanSaatIni < halamanTerakhir) {
          $grid.jqGrid('setGridParam', { page: halamanTerakhir }).trigger('reloadGrid');
        }
        break;
      default:
        break;
    }
  });


}

// Buat fungsi untuk membuat tombol dengan ID unik + event handler
function createResetButtonElement(gridId) {
  const uniqueId = `reset_search_${gridId}`;
  return $(`<button id="${uniqueId}" type="button" class="reset-search-btn" data-grid="#${gridId}" title="Reset All Toolbar Search">X</button>`);
}

function resetToolbarSearch(gridSelector) {

  const $grid = $(gridSelector);

  resetSearch(gridSelector);

  // hapus data pencarian
  const postData = $grid.getGridParam("postData");
  delete postData.global_search;
  delete postData.filters;

  // Reset postData dan search = false
  $grid.setGridParam({
    search: false,
    postData: {
      _search: false,
    }
  }).trigger('reloadGrid', [{ page: 1 }]);

  // Bersihkan highlight pada semua cell
  $grid.find('td').each(function () {
    let html = $(this).html();
    html = html.replace(/<span class="highlight">(.*?)<\/span>/gi, "$1");
    $(this).html(html);
  });

}

function createGlobalSearchInput(gridSelector) {
  // Cek apakah input global search sudah ada
  const inputID = `gsearch_${gridSelector}`;
  // const existingInput = $(`input#gsearch_${gridSelector}`);
  // if (existingInput.length > 0) {
  //   return existingInput.closest('.ui-jqgrid-titlebar');
  // }

  // Buat input global search
  const globalSearchInput = $(
    `<div class='ui-jqgrid-titlebar ui-widget-header'>
      Global Search :
      <input type='text' name='gsearch' id='${inputID}' class='rounded border-0' placeholder='.....' style='width: 300px; height: 30px; padding: 0 10px;'>
    </div>`
  );

  const $inputElement = globalSearchInput.find(`input#${inputID}`);

  $inputElement.on('keyup', function (e) {
    resetSearch(gridSelector);
    const searchValue = $(this).val();
    $(gridSelector).jqGrid('setGridParam', {
      search: false,
      page: 1,
      postData: {
        filters: {},
        _search: false,
        global_search: searchValue
      }
    }).trigger('reloadGrid')
    // if (e.key === 'Enter') { // Jika ingin trigger reloadGrid hanya saat Enter ditekan
    // }
  });

  return globalSearchInput;
}

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
    styleUI: 'Bootstrap4',
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
        // sortable: false,
        // search: false,
        formatter: 'currency',
        formatoptions: formatOpt,
      },
    ],
    rowNum: 10,
    rowList: [5, 10, 20],
    pager: '#detailItemPager',
    sortname: 'nama_barang',
    viewrecords: true,
    gridview: true,
    // width: 600,
    height: 'auto',
    sortorder: "desc",
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
      // Setup navigasi untuk grid detail
      initializeGridNavigation(detailGrid);


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


  // Untuk detail grid (pastikan element ini ada di DOM Anda)
  $('#gsh_detailItem_rn div').empty(); // Kosongkan elemen sebelum menambahkan tombol baru
  const detailButton = createResetButtonElement('detail'); // Sesuaikan selector
  $('#gsh_detailItem_rn div').append(detailButton);

  const detailContainer = $('#gbox_detailItem');
  if (detailContainer.find('#gsearch_detailItem').length === 0) {
    // Jika .length adalah 0 (elemen tidak ada), maka kita tambahkan.
    // console.log("Search bar detail belum ada, saatnya menambahkan...");

    // pencarian di detail grid
    const detailSearchElem = `
    <div class='ui-jqgrid-titlebar ui-widget-header'>
      Global Search :
      <input type='text' name='gsearch' id='gsearch_detailItem' data-selectid='${id}' class='rounded border-0' placeholder='.....' style='width: 300px; height: 30px; padding: 0 10px;'>
    </div>`;
    $('#gbox_detailItem .ui-jqgrid-titlebar').after(detailSearchElem);
    // Global Search untuk master
    // const globalSearchInput = createGlobalSearchInput('#jqGrid');
    // $('.ui-jqgrid-titlebar').after(createGlobalSearchInput('#jqGrid'));

    $('#gsearch_detailItem').on('keyup', function () {
      let text = $(this).val();

      $('#gs_nama_barang').val('');
      $('#gs_qty').val('');
      $('#gs_harga').val('');
      $('#gs_total').val('');

      // resetToolbarSearch('#jqGrid');

      //ada banyak parameter grid, salah satunya postData. untuk nngeliat bisa bikin getGridParam
      //untuk nambahin isi dari parameternya bisa dibuat pake setGridParam
      //jadi untuk search, set dulu data baru untuk param postData. lalu di trigger dengan reloadGrid
      //maka setelah itu, isi param postData bisa bertambah sesuai yg diinginkan
      $('#detailItem').jqGrid('setGridParam', {
        search: false,
        page: 1,
        postData: {
          filters: {},
          _search: false,
          global_search: text,
          id_penjualan: $(this).data('selectid') // Menggunakan data-selectid untuk filter
        }
      }).trigger('reloadGrid')

    });

  } else {
    // Jika .length > 0 (elemen sudah ada), kita tidak melakukan apa-apa.
    // console.log("Search bar detail sudah ada, tidak perlu ditambah lagi.");
  }




}
// End of detailTable function


// Validate Tgl Bukti
function isValidDate(dateString) {
  // Format yang diharapkan: dd-mm-yyyy
  const regex = /^(\d{2})-(\d{2})-(\d{4})$/;
  const match = dateString.match(regex);
  if (!match) return false;

  const day = parseInt(match[1], 10);
  const month = parseInt(match[2], 10);
  const year = parseInt(match[3], 10);

  // Cek range bulan
  if (month < 1 || month > 12) return false;

  // Cek hari sesuai bulan
  const monthLengths = [31, (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0) ? 29 : 28,
    31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

  return day >= 1 && day <= monthLengths[month - 1];
}


function openModal(mode, data = {}) {
  modalMode = mode;
  $('#formModalLabel').text(mode === 'add' ? 'Tambah Data Penjualan' : mode === 'edit' ? 'Edit Data Penjualan' : 'Hapus Data Penjualan');
  $('#saveBtn').toggleClass('d-none', mode === 'delete');
  $('#deleteBtn').toggleClass('d-none', mode !== 'delete');
  $('#penjualanForm')[0].reset();

  // if (mode === 'add') {
  //   $('#no_bukti').prop('readonly', true).val('Memuat nomor...');

  //   // Panggil AJAX untuk mendapatkan nomor bukti baru
  //   $.ajax({
  //     url: '/generate/nobukti',
  //     type: 'GET',
  //     success: function (response) {
  //       // Jika berhasil, isi input dengan nomor baru dari backend
  //       $('#no_bukti').val(response.no_bukti);
  //     },
  //     error: function () {
  //       // Jika gagal, tampilkan pesan error
  //       // $('#no_bukti').val('Gagal memuat!');
  //       showNotificationDialog('Gagal mendapatkan nomor bukti dari server.');
  //     }
  //   });
  // }

  if (mode === 'edit' || mode === 'delete') {
    $('#formId').val(data.id);
    $('#no_bukti').val(data.no_bukti);
    $('#tgl_bukti').val(data.tgl_bukti);
    $('#nama_pelanggan').val(String(data.nama_pelanggan)).trigger('change');
    $('#no_bukti').prop('disabled', mode !== 'add'); // disable saat edit/hapus
    $('#tgl_bukti, #nama_pelanggan').prop('disabled', mode === 'delete');

    $('#tableBarang tbody').empty();
    if (data.barang && data.barang.length > 0) {
      // Tambahkan baris barang ke tabel
      data.barang.forEach(item => {
        const $row = $(HTMLBarisBarangBaru());
        $row.find('input[name="nama_barang[]"]').val(item.nama_barang);
        $row.find('input[name="qty[]"]').val(item.qty);
        $row.find('input[name="harga[]"]').val(item.harga);
        initAutoNumericRow($row);
        $('#tableBarang tbody').append($row);

        // disabled input
        $row.find('input[name="nama_barang[]"]').prop('disabled', mode === 'delete')
        $row.find('input[name="qty[]"]').prop('disabled', mode === 'delete')
        $row.find('input[name="harga[]"]').prop('disabled', mode === 'delete')
      });
      
    }

  } else {
    $('#no_bukti, #tgl_bukti, #nama_pelanggan').prop('disabled', false);
  }
  
  $('#formModal').on('shown.bs.modal', function (e) {
    updateGrandTotal();
    // Fokus ke input misalnya
    $('#no_bukti').focus();
  });

  $('#formModal').modal('show');
}

function HTMLBarisBarangBaru() {
  // Template untuk baris barang baru
  return `
  <tr class="barangRow">
    <td>
      <input type="text" name="nama_barang[]" class="form-control namabarang" required>
      <span class="text-danger error-text nama_barang_error"></span>
    </td>
    <td>
      <input type="text" name="qty[]" class="form-control qty" min="1" required>
      <span class="text-danger error-text qty_error"></span>
    </td>
    <td>
      <input type="text" name="harga[]" class="form-control harga" min="1" required>
      <span class="text-danger error-text harga_error"></span>
    </td>
    <td>
      <input type="text" name="total[]" class="form-control total" readonly>
    </td>
    <td>
      <button type="button" class="btn btn-danger btn-sm removeBarangRow">-</button>
    </td>
  </tr>
  `;
}

function initAutoNumericRow($row) {
  const $qty = $row.find('.qty');
  const $harga = $row.find('.harga');
  const $total = $row.find('.total');

  const anQty = new AutoNumeric($qty[0], setQtyNumeric);
  const anHarga = new AutoNumeric($harga[0], setMoneyNumeric);
  const anTotal = new AutoNumeric($total[0], setMoneyNumeric);

  const value = anHarga.getNumber();
  anHarga.set(value);

  updateTotalRow($row);
}

function updateTotalRow($row) {
  const $qtyInput = $row.find('.qty');
  const $hargaInput = $row.find('.harga');
  const $totalInput = $row.find('.total');

  const anQty = AutoNumeric.getAutoNumericElement($qtyInput[0]);
  const anHarga = AutoNumeric.getAutoNumericElement($hargaInput[0]);
  const anTotal = AutoNumeric.getAutoNumericElement($totalInput[0]);

  const qty = anQty ? anQty.getNumber() : 0;
  const harga = anHarga ? anHarga.getNumber() : 0;

  anTotal.set(qty * harga);
}

function updateGrandTotal() {
  let sum = 0;

  $('#tableBarang .total').each(function () {
    const anT = AutoNumeric.getAutoNumericElement(this);
    sum += anT ? anT.getNumber() : 0;
  });

  NumericTotal.set(sum);
}


function resetFormAndValidation() {

  $('#penjualanForm')[0].reset(); // Reset form
  $('#formId').val(''); // Kosongkan ID form
  $('#nama_pelanggan').val('0').trigger('change'); // Reset select2

  // Hapus semua baris barang kecuali yang pertama (template)
  $('#tabelBarang tbody tr.barangRow:not(:first)').remove();
  // Kosongkan juga input di baris pertama
  $('#tabelBarang tbody tr.barangRow:first').find('input').val('');

  // Hapus semua pesan error dan kelas 'is-invalid'
  $('.error-text').text('');
  $('.form-control').removeClass('is-invalid');

}

function exportModal(mode) {

  // bersihkan 
  $('.error-text').text('');
  $('#start_range_input, #end_range_input').removeClass('is-invalid');

  $('#confirmExportBtn').data('mode', mode); // simpan data mode;
  $('#exportFormLabel').text(mode === 'excel' ? 'Export Excel' : 'Export PDF');

  // Atur nilai default untuk input rentang
  const totalRecords = $('#jqGrid').jqGrid('getGridParam', 'records');
  $('#start_range').val(parseInt(1));
  $('#end_range').val(parseInt(totalRecords));

  $('#exportForm').modal('show');

}

// Fungsi helper untuk handle error validasi
function handleValidationErrors(errorData) {
  if (errorData && errorData.errors) {
    $.each(errorData.errors, function (key, value) {
      $('.' + key + '_error').text(value[0]);
      $('#' + key + '_input').addClass('is-invalid');
    });
  } else {
    console.error('Terjadi kesalahan tidak terduga:', errorData.message || errorData);
    showNotificationDialog('Terjadi kesalahan saat memproses permintaan.');
  }
}

// Fungsi validasi khusus untuk PDF
function validateAndExportPDF(exportData) {
  // Tambahkan parameter untuk validasi saja
  const validationData = { ...exportData, validate_only: true };

  $.ajax({
    url: '/penjualan/export/pdf',
    type: 'POST',
    data: JSON.stringify(validationData),
    contentType: 'application/json',
    dataType: 'json',
    headers: {
      'X-CSRF-TOKEN': csrfToken
    },
    success: function (res) {
      if (res.message === "Ok") {
        const queryString = $.param(exportData);
        const exportUrl = `/penjualan/report/viewpdf?${queryString}`;
        window.open(exportUrl, '_blank');
        $('#exportForm').modal('hide');
      }
    },
    error: function (xhr, status, error) {
      console.error('AJAX Error:', xhr.responseJSON || xhr.responseText);
      handleValidationErrors(xhr.responseJSON || { message: error });
    }
  });
}

// Fungsi export untuk Excel (logic yang sudah ada)
function exportExcel(exportData) {
  fetch(`/penjualan/export/excel`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      // 'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
      // 'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify(exportData)
  })
    .then(response => {
      if (response.ok) {
        const disposition = response.headers.get('Content-Disposition');
        return response.blob().then(blob => ({ blob, disposition }));
      } else {
        return response.json().then(errorData => {
          throw errorData;
        });
      }
    })
    .then(({ blob, disposition }) => {
      // Proses download Excel
      let filename = "laporan.xlsx";
      if (disposition && disposition.indexOf('attachment') !== -1) {
        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
        const matches = filenameRegex.exec(disposition);
        if (matches != null && matches[1]) {
          filename = matches[1].replace(/['"]/g, '');
        }
      }

      const downloadUrl = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none';
      a.href = downloadUrl;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(downloadUrl);
      document.body.removeChild(a);

      $('#exportForm').modal('hide');
    })
    .catch(errorData => {
      handleValidationErrors(errorData);
    });
}