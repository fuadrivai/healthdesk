$(document).ready(function () {
  "use strict";

  var $divisionSection = $("#page-master-division");
  var $divisionTableBody = $("#divisionTable tbody");

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function setLoadingState() {
    $divisionTableBody.html(
      '<tr><td colspan="2" class="text-center py-4 text-muted">Loading division data...</td></tr>',
    );
  }

  function renderEmptyState(message) {
    $divisionTableBody.html(
      '<tr><td colspan="2" class="text-center py-4 text-muted">' +
        escapeHtml(message) +
        "</td></tr>",
    );
  }

  function renderDivisions(rows) {
    if (!rows.length) {
      renderEmptyState("No division data found.");
      return;
    }

    var html = "";
    rows.forEach(function (row, index) {
      html +=
        "<tr>" +
        "<td>" +
        (index + 1) +
        "</td>" +
        "<td>" +
        escapeHtml(row.level_name || "-") +
        "</td>" +
        "</tr>";
    });

    $divisionTableBody.html(html);
  }

  function loadDivisionData() {
    setLoadingState();

    ajaxRequest({
      url: "service/division.php",
      method: "GET",
      data: {
        action: "list",
      },
    })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error(
            (response && response.message) || "Unable to load division data.",
          );
        }

        renderDivisions(response.data || []);
      })
      .catch(function (error) {
        renderEmptyState(error.message || "Failed to load division data.");
      });
  }

  $(document).on("click", '[data-page="master-division"]', function () {
    loadDivisionData();
  });

  if ($divisionSection.hasClass("active")) {
    loadDivisionData();
  }
});
