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
  const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const regex = new RegExp(`(${escapedKeyword})`, "gi");
  const updatedHtml = cell.html().replace(regex, '<span class="highlight">$1</span>');
  cell.html(updatedHtml);

}

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

function resetSearch() {

  $('#gs_no_bukti').val('');
  $('#gs_tgl_bukti').val('');
  $('#gs_nama_pelanggan').val('');

}














