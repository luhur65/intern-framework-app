// function isValidTanggal(tgl) {
//   // Format: dd-mm-yyyy
//   const regex = /^(\d{2})-(\d{2})-(\d{4})$/;
//   const match = tgl.match(regex);

//   if (!match) return false;

//   const day = parseInt(match[1], 10);
//   const month = parseInt(match[2], 10) - 1; // JS months: 0-11
//   const year = parseInt(match[3], 10);

//   // Batas aman
//   if (year < 1900 || year > 2099) return false;

//   const date = new Date(year, month, day);
//   return (
//     date.getFullYear() === year &&
//     date.getMonth() === month &&
//     date.getDate() === day
//   );
// }

function validasiInputData() {

  let isValid = true

  const noBukti = $("form [name=no_bukti]").val();
  const tglBukti = $("form [name=tgl_bukti]").val();
  const pelanggan = $("form [name=pelanggan]").val();

  const namaBarangs = $("form#data-form .namabarang");
  const qtys = $("form#data-form .qty");
  const hargas = $("form#data-form .harga");
  // const totals = $("")

  // validasi form - penjualan
  if (noBukti == "") {
    isValid = false;
    $('#no_bukti_invalid').show();
    // return false; // ubah kode
  } else {
    $('#no_bukti_invalid').hide();
  }

  if (tglBukti == "" || tglBukti == 0) {
    isValid = false;
    $('#tgl_bukti_invalid').show();
  } else {
    $('#tgl_bukti_invalid').hide();
  }

  if (pelanggan <= 0 || pelanggan == null) {
    isValid = false;
    $('#pelanggan_invalid').show();
  } else {
    $('#pelanggan_invalid').hide();
  }

  // validasi form - detail penjualan
  namaBarangs.each(function(i, input) {
    const errorP = input.parentElement.querySelector('.error-msg-namabarang');
    if (input.value.trim() === '') {
      errorP.textContent = 'Nama barang harus diisi!';
      isValid = false;
    } else {
      errorP.textContent = '';
    }
  });

  qtys.each(function(i, input) {
    const errorP = input.parentElement.querySelector('.error-msg-qty');
    if (input.value.trim() === '' || isNaN(input.value) || input.value == 0) {
      errorP.textContent = 'Qty barang kosong!';
      isValid = false;
    } else {
      errorP.textContent = '';
    }
  });

  hargas.each(function(i, input) {
    const errorP = input.parentElement.querySelector('.error-msg-harga');
    if (input.value.trim() === '' || isNaN(input.value) || input.value == 0) {
      errorP.textContent = 'Harga barang kosong!';
      isValid = false;
    } else {
      errorP.textContent = '';
    }
  });

  return isValid;

}

function validateRange() {
    // Ambil nilai dari input
    let startRange = document.getElementById('start_range').value;
    let endRange = document.getElementById('end_range').value;
    
    // Ambil elemen pesan error
    let startInvalidMessage = document.getElementById('start_invalid');
    let endInvalidMessage = document.getElementById('end_invalid');

    // Asumsi totalRecords sudah ada
    let totalRecords = $("#jqGrid").jqGrid('getGridParam', 'records');

    // 1. Reset kondisi dan bersihkan pesan error sebelum validasi baru
    startInvalidMessage.textContent = "";
    endInvalidMessage.textContent = "";
    let isValid = true;

    // 2. Validasi untuk Start Range
    // Menggunakan else if agar hanya satu pesan yang muncul per field
    if (startRange === "") {
        startInvalidMessage.textContent = "Kolom ini wajib diisi.";
        isValid = false;
    } else if (parseInt(startRange, 10) <= 0) {
        startInvalidMessage.textContent = "Harus dimulai dari angka 1 atau lebih.";
        isValid = false;
    }

    // 3. Validasi untuk End Range
    if (endRange === "") {
        endInvalidMessage.textContent = "Kolom ini wajib diisi.";
        isValid = false;
    } else if (parseInt(endRange, 10) <= 0) {
        endInvalidMessage.textContent = "Harus diisi dengan angka positif.";
        isValid = false;
    }

    // 4. Lakukan validasi perbandingan HANYA JIKA kedua input sudah dianggap valid sejauh ini.
    // Ini mencegah error parseInt(NaN) dan pesan error yang tidak relevan.
    if (isValid) {
        const startNum = parseInt(startRange, 10);
        const endNum = parseInt(endRange, 10);
        const totalNum = parseInt(totalRecords, 10);

        if (startNum > endNum) {
            startInvalidMessage.textContent = "Nilai awal tidak boleh lebih besar dari akhir.";
            isValid = false;
        } 
        // Gunakan else if agar tidak ada pesan error ganda pada field yang sama
        else if (endNum > totalNum) {
            endInvalidMessage.textContent = "Maksimal hanya sampai " + totalNum + " data.";
            document.getElementById('end_range').value = totalNum;
            isValid = false;
        }
    }

    // Kembalikan status validasi akhir
    return isValid;
}

function tambahBarang() 
{
  $('#dialogElem').load('./app/views/penjualan/form-penjualan.php').dialog({
    modal: true,
    height: 500,
    width: 1100,
    position: { my: "center center", at: "center", of: window },
    title: 'Tambah Data',
    buttons: {
      'Simpan Data': function () {
        let sortname = $('#jqGrid').jqGrid('getGridParam', 'sortname');
        let sortorder = $('#jqGrid').jqGrid('getGridParam', 'sortorder');
        let rowNum = parseInt($('#jqGrid').jqGrid('getGridParam', 'rowNum'));
        
        // Unformat AutoNumeric sebelum serialize
        $('#data-form .harga, #data-form .qty').each(function () {
          const an = AutoNumeric.getAutoNumericElement(this);
          if (an) this.value = an.getNumber();
        });

        // validation
        if (!validasiInputData()) return;

        $.ajax({
          url: './app/data/penjualan.php?sortname=' + sortname + '&sortorder=' + sortorder + '&rows=' + rowNum,
          method: 'POST',
          dataType: 'JSON',
          data: $('#data-form').serialize(),
          success: function (data) {
            // alert("Berhasil");
            // Swal.fire({
            //   title: 'Berhasil!',
            //   text: 'Berhasil Ditambahkan',
            //   icon: 'success',
            // });

            // Simpan id ke global
            selectId = data.id;
            page = data.page;

            console.log("ID yang disimpan:", selectId);
            console.log("Page tujuan:", page);

            $('#dialogElem').dialog('close');
            $('#jqGrid').setGridParam({
              page: page
            }).trigger('reloadGrid');
            
          },
          error: function (e) {
            console.log(e.responseJSON.error);
            // alert("Gagal ditambahkan: " + e.responseJSON.error);
            // Swal.fire({
            //   title: 'Error!',
            //   text: 'Gagal Ditambahkan',
            //   icon: 'error',
            // });
          }
        });
      },
      'Cancel': function () {
        $('#dialogElem').dialog('close');
      }
    }
  });

}

function UbahBarang(id) 
{

  if (id == null) {
    alert("Pilih data yang mau diedit");
    // Swal.fire({
    //   icon: 'question',
    //   text: 'Pilih data yang mana mau di edit'
    // });

    return false;

  }

  $('#dialogElem').load('./app/views/penjualan/form-penjualan.php?id=' + id).dialog({
    modal: true,
    height: 500,
    width: 1100,
    position: { my: "center center", at: "center", of: window },
    title: "Ubah Data",

    buttons: {
      'Ubah Data': function () {
        let sortname = $('#jqGrid').jqGrid('getGridParam', 'sortname');
        let sortorder = $('#jqGrid').jqGrid('getGridParam', 'sortorder');
        let rowNum = parseInt($('#jqGrid').jqGrid('getGridParam', 'rowNum'));
        let postData = $('#jqGrid').jqGrid('getGridParam', 'postData');
        let filters = postData.filters;
        let globalSearchKey = postData.global_search;
        let url = './app/data/penjualan.php?id=' + id + '&action=ubah';

        // Unformat AutoNumeric sebelum serialize
        $('#data-form .harga, #data-form .qty').each(function () {
          const an = AutoNumeric.getAutoNumericElement(this);
          if (an) this.value = an.getNumber();
        });

        // validation
        if (!validasiInputData()) return;

        let formData = $('#data-form').serializeArray();
        formData.push({ name: 'sortname', value: sortname });
        formData.push({ name: 'sortorder', value: sortorder });
        formData.push({ name: 'rows', value: rowNum });

        if (filters) {
            formData.push({ name: 'filters', value: filters });
            url = './app/data/penjualan.php?id=' + id + '&action=ubah&_search=true'
        }

        if (globalSearchKey) {
            formData.push({ name: 'global_search', value: globalSearchKey });
            url = './app/data/penjualan.php?id=' + id + '&action=ubah&'
        }


        $.ajax({
          url: url ,
          method: 'POST',
          dataType: 'JSON',
          data: formData,
          success: function (data) {

            // Simpan id ke global
            selectId = data.id;
            page = data.page;

            console.log("ID yang disimpan:", selectId);
            console.log("Page tujuan:", page);

            $('#dialogElem').dialog('close');
            $('#jqGrid').setGridParam({
              page: page
            }).trigger('reloadGrid', [{current: true}]);

            // notif saya
            // alert("Berhasil diubah");
            // Swal.fire({
            //   title: 'Berhasil!',
            //   text: 'Berhasil Diubah',
            //   icon: 'success',
            // });
          },
          error: function () {
            // alert("Gagal diubah");
            // Swal.fire({
            //   title: 'Error!',
            //   text: 'Gagal Diubah',
            //   icon: 'error',
            // });
          }
        });
      },
      'Cancel': function () {
        $('#dialogElem').dialog('close');
      }
    }

  });

}

function HapusBarang(id) 
{

  if (id == null) {
    alert("Pilih data yang mana mau di hapus");
    // Swal.fire({
    //   icon: 'warning',
    //   text: 'Pilih data yang mana mau di hapus'
    // });

    return false;

  }

  console.log("Hapus ID:", id);

  $('#dialogElem').load('./app/views/penjualan/form-penjualan.php?id_del=' + id).dialog({
    modal: true,
    height: 500,
    width: 1100,
    position: { my: "center center", at: "center", of: window },
    title: "Hapus Data",

    buttons: {
      'Delete': function () {
        let sortname = $('#jqGrid').jqGrid('getGridParam', 'sortname');
        let sortorder = $('#jqGrid').jqGrid('getGridParam', 'sortorder');
        let rowNum = parseInt($('#jqGrid').jqGrid('getGridParam', 'rowNum'));
        let postData = $('#jqGrid').jqGrid('getGridParam', 'postData');
        let filters = postData.filters;
        let globalSearchKey = postData.global_search;
        let url = './app/data/penjualan.php?id=' + id + '&action=hapus';

        let formData = $('#data-form').serializeArray();
        formData.push({ name: 'sortname', value: sortname });
        formData.push({ name: 'sortorder', value: sortorder });
        formData.push({ name: 'rows', value: rowNum });

        if (filters) {
          formData.push({ name: 'filters', value: filters });
          url = './app/data/penjualan.php?id=' + id + '&action=hapus&_search=true'
        }

        if (globalSearchKey) {
          formData.push({ name: 'global_search', value: globalSearchKey });
          url = './app/data/penjualan.php?id=' + id + '&action=hapus&'
        }

        $.ajax({
          url: url,
          method: 'POST',
          dataType: 'JSON',
          data: formData,
          success: function (data) {
            
            // Simpan id ke global
            selectId = data.id;
            page = data.page;

            console.log("ID yang disimpan:", selectId);
            console.log("Page tujuan:", page);

            $('#dialogElem').dialog('close');

            // select id null
            if (selectId == null) {
              console.log("Data kosong karena filter, reset filter...");
              console.log("Masuk reset filter");
              // Reset filter (search box) dan reload dari page 1
              // Reset postData dan search = false
              // hapus data pencarian
              $('#gsearch').val('');
              resetSearch();
              const postData = $('#jqGrid').getGridParam("postData");
              delete postData.global_search;
              delete postData.filters;
              $('#jqGrid').setGridParam({
                search: false,
                postData: {
                  _search: false,
                }
              }).trigger('reloadGrid', [{ page: 1 }]);

            } else {
              // Jika ada data, reload grid dengan id yang sudah dihapus
              $('#jqGrid').setGridParam({
                page: page
              }).trigger('reloadGrid')
            }

            // notif saya
            // alert("Berhasil Dihapus");
            // Swal.fire({
            //   title: 'Berhasil!',
            //   text: 'Berhasil Dihapus',
            //   icon: 'success',
            // });
          },
          error: function () {
            // alert("Gagal Dihapus");
            // Swal.fire({
            //   title: 'Error!',
            //   text: 'Gagal Dihapus',
            //   icon: 'error',
            // });
          }
        });
      },
      'Cancel': function () {
        $('#dialogElem').dialog('close');
      }
    }

  });

}

function detailTable(id) 
{

  const formatOpt = {
    prefix: '',
    thousandsSeparator: ',',
    decimalPlaces: 2,
    decimalSeparator: '.',
  }

  // Detail Table
  jQuery("#detailItem").jqGrid({
    height: 100,
    url: './app/data/detail.php?id=' + id,
    datatype: "json",
    colNames: ['Nama Barang', 'Banyak Barang', 'Harga Satuan (Rp)', 'Total (Rp)'],
    colModel: [
      // { name: 'num', index: 'num', width: 55 },
      { name: 'nama_barang', index: 'nama_barang', width: 180 },
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
    width: 600,
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
    }
  }).navGrid('#detailItemPager', { add: false, edit: false, del: false, search: false, refresh: false });

}

function highlightText(cell, keyword) 
{

  if (!keyword) return;
  const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const regex = new RegExp(`(${escapedKeyword})`, "gi");
  const updatedHtml = cell.html().replace(regex, '<span class="highlight">$1</span>');
  cell.html(updatedHtml);

}

function higligthPencarian(grid) 
{
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

function exportExcel()
{

  $('#dialogElem').load('./app/views/export/export.php').dialog({
    modal: true,
    height: 300,
    width: 500,
    position: { my: "center center", at: "center", of: window },
    title: 'Export Data ke Excel',
    buttons: {
      'Export To Excel': function () {
        // let sortname = $('#jqGrid').jqGrid('getGridParam', 'sortname');
        // let sortorder = $('#jqGrid').jqGrid('getGridParam', 'sortorder');
        // let rowNum = parseInt($('#jqGrid').jqGrid('getGridParam', 'rowNum'));
        let postData = $("#jqGrid").jqGrid('getGridParam', 'postData');

        // // parameter export=excel
        // postData.export = 'excel';

        // url semua parameter
        let params = new URLSearchParams(postData).toString();
        let url = './app/data/export.php?' + params;

        // const notifMessage = document.getElementById('alert-export');
        // notifMessage.style.background = "white";
        // notifMessage.textContent = "";

        if (!validateRange()) return;

        let form = document.getElementById('export-form');
        let formDataString = new URLSearchParams(new FormData(form)).toString();

        // MENGGANTI $.ajax DENGAN XMLHttpRequest MURNI
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);

        // Ini adalah pengganti xhrFields: { responseType: 'blob' }
        xhr.responseType = 'blob';
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Fungsi ini akan dipanggil setelah respons diterima dari server
        xhr.onload = function () {
          // Periksa apakah request sukses (status 200-299)
          if (xhr.status >= 200 && xhr.status < 300) {
            // JIKA SUKSES, PROSES BLOB MENJADI DOWNLOAD
            const blob = xhr.response; // Ambil blob dari respons

            let filename = "Laporan_Penjualan.xlsx";
            const disposition = xhr.getResponseHeader('Content-Disposition');
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

            $('#dialogElem').dialog('close');

          } else {
            // JIKA GAGAL (status 4xx atau 5xx), PROSES PESAN ERROR JSON
            const reader = new FileReader();
            reader.onload = function() {
              try {
                const errorData = JSON.parse(reader.result);
                alert("Gagal melakukan export: " + errorData.error);
                console.error("Server-side export error:", errorData.error);
              } catch (e) {
                alert("Terjadi kesalahan. Gagal membaca pesan error dari server. Status: " + xhr.status);
                console.error("Failed to parse error response:", reader.result);
              }
            };
            reader.readAsText(xhr.response); // xhr.response adalah blob yang berisi JSON
          }
        };

        // Fungsi ini untuk menangani error level jaringan (misal: tidak ada koneksi)
        xhr.onerror = function () {
          alert("Terjadi error jaringan. Tidak dapat menghubungi server.");
        };

        // Kirim request dengan data dari form
        xhr.send(formDataString);


      },
    }
  });

}

function exportPDF() {
    
    $('#dialogElem').load('./app/views/export/export.php').dialog({
    modal: true,
    height: 300,
    width: 500,
    position: { my: "center center", at: "center", of: window },
    title: 'Export Data ke PDF',
    buttons: {
      'Export To PDF': function () {
        let postData = $("#jqGrid").jqGrid('getGridParam', 'postData');

        // url semua parameter
        let params = new URLSearchParams(postData).toString();
        let url = './app/data/stimulsoft.php?' + params; console.log(url);

        if (!validateRange()) return;

        let form = document.getElementById('export-form');
        let formDataString = new URLSearchParams(new FormData(form)).toString();
        window.open('laporan.php?'+ params +'&' + formDataString, '_blank');

        // $.ajax({
        //   url: url,
        //   method: 'POST',
        //   dataType: 'JSON',
        //   data: formDataString,
        //   success: function (data) {

        //     // 1. Buat viewer dan objek report terlebih dahulu
        //     const viewer = new Stimulsoft.Viewer.StiViewer(null, "StiViewer", false);
        //     const report = new Stimulsoft.Report.StiReport();

        //     // 2. Muat file .mrt dan gunakan CALLBACK function-nya
        //     // Kode di dalam 'function () { ... }' ini HANYA akan berjalan SETELAH file .mrt selesai dimuat.
        //     report.loadFile('./app/views/report/report_baru.mrt', function () {
        //         console.log("Template .mrt berhasil dimuat.");

        //         // 3. Daftarkan data JSON ke report (setelah template siap)
        //         var dataSet = new Stimulsoft.System.Data.DataSet("Data");
        //         dataSet.readJson(data);
        //         report.regData("Data", "Data", dataSet);

        //         // 4. Masukkan report yang sudah siap (template + data) ke dalam viewer
        //         viewer.report = report;
        //         console.log("Objek report dimasukkan ke viewer.");

        //         // 5. Terakhir, render viewer untuk menampilkannya di halaman HTML
        //         // Viewer akan secara otomatis me-render report yang sudah ada di dalamnya.
        //         viewer.renderHtml('stimulsoft-viewer');
        //         console.log("Viewer selesai dirender.");
        //     });

            
        //   },
        //   error: function () {
            
        //   }
        // });


      },
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


let selectId = null;
let page = 1;
let isValid = true;

// Inisialisasi JQGrid
$('#jqGrid').jqGrid({
  url: "./app/data/testing.php",
  mtype: "GET",
  datatype: "json",
  colModel: [
    {
      label: 'Id Bukti',
      name: 'id_penjualan',
      hidden: true,
      key: true,
      width: 75
    },
    {
      label: 'No Bukti',
      name: 'no_bukti',
      width: 150
    },
    {
      label: 'Tanggal Bukti',
      name: 'tgl_bukti',
      width: 150,
      // searchoptions: { 
      //   dataInit: function (el) { 
      //     $(el).datepicker({ dateFormat: 'dd-mm-yy' }); 
      //   } 
      // }
      formatter: 'date',
      formatoptions: { srcformat: 'Y-m-d', newformat: 'd-m-Y' } // format tanggal untuk backend yang menggunakan ISO date
    },
    {
      label: 'Nama Pelanggan',
      name: 'nama_pelanggan',
      width: 150
    }
  ],
  cmTemplate: { required: true },
  width: 1000,
  height: 'auto',
  rowNum: 10,
  mtype: "POST", // ini berpengaruh pada cara pengambilan parameter untuk jqGrid
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
    jQuery("#detailItem").jqGrid('setGridParam', { url: "./app/data/detail.php?id=" + id, page: 1 });
    jQuery("#detailItem").trigger('reloadGrid');

  },
  loadComplete: function (response) {
    const ids = $("#jqGrid").jqGrid('getDataIDs');

    // console.log(response);

    if(selectId) {
      selectRow(selectId);
      // $("#jqGrid").jqGrid('setSelection', selectId);
      // console.log(selectId);
      detailTable(selectId);

    } else {
      selectRow(ids[0]);
      detailTable(ids[0]);
    }

    // Highlight pencarian
    higligthPencarian($(this));
  }
});

// setting default untuk seluruh action bawaan jqgrid
$("#jqGrid").jqGrid('navGrid', '#jqGridPager', { edit: false, add: false, del: false, search: false, refresh: false });

// navigasi user
// $('#jqGrid').jqGrid('bindKeys');

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


// tombol tambah
$("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
  caption: 'Tambah',
  buttonicon: 'ui-icon-plus',
  onClickButton: function () {
    tambahBarang();
  },
  position: 'last',
  title: 'Add',
  id: "AddHeader",
  cursor: "pointer",
});

// tombol ubah
$("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
  caption: 'Ubah',
  buttonicon: 'ui-icon-pencil',
  onClickButton: function () {
    const rowId = $('#jqGrid').jqGrid('getGridParam', 'selrow');
    UbahBarang(rowId);
  },
  position: 'last',
  title: 'Edit',
  id: "EditHeader",
  cursor: "pointer",
});

// tombol hapus
$("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
  caption: 'Hapus',
  buttonicon: 'ui-icon-trash',
  onClickButton: function () {
    const rowId = $('#jqGrid').jqGrid('getGridParam', 'selrow');
    HapusBarang(rowId);
  },
  position: 'last',
  title: 'Hapus',
  id: "HapusHeader",
  cursor: "pointer",
});

// tombol export data ke excel
$("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
  caption: 'Export Excel',
  buttonicon: 'ui-icon-print',
  onClickButton: function () {
    exportExcel();
  },
  position: 'last',
  title: 'Export Data',
  id: "ExportHeader",
  cursor: "pointer",
});

// Tombol export PDF langsung
$("#jqGrid").jqGrid('navButtonAdd', '#jqGridPager', {
  caption: 'Export PDF',
  buttonicon: 'ui-icon-print',
  onClickButton: function () {
      exportPDF();
  },
  position: 'last',
  title: 'Export PDF',
  id: "PDFHeader",
  cursor: "pointer",
});

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

function resetSearch() {

  $('#gs_no_bukti').val('');
  $('#gs_tgl_bukti').val('');
  $('#gs_nama_pelanggan').val('');

}

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
