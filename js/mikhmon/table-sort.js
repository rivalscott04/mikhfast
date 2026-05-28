/* Mikhmon — Sortable tables */
function sortTable(table, colIndex, dir) {
  var tbody = table.tBodies[0];
  var rows = Array.prototype.slice.call(tbody.rows, 0);

  dir = -(+dir || -1);
  rows = rows.sort(function (a, b) {
    return (
      dir *
      a.cells[colIndex].textContent
        .trim()
        .localeCompare(b.cells[colIndex].textContent.trim())
    );
  });

  for (var i = 0; i < rows.length; ++i) tbody.appendChild(rows[i]);
}

function makeSortable(table) {
  var head = table.tHead;
  if (head) head = head.rows[0];
  if (head) head = head.cells;
  if (!head) return;

  for (var i = head.length; --i >= 0; ) {
    (function (colIndex) {
      var dir = 1;
      head[colIndex].addEventListener("click", function () {
        sortTable(table, colIndex, (dir = 1 - dir));
      });
    })(i);
  }
}

function makeAllSortable(root) {
  root = root || document.body;
  var tables = root.getElementsByTagName("table");
  for (var i = tables.length; --i >= 0; ) makeSortable(tables[i]);
}
