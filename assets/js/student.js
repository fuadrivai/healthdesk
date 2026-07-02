$(document).ready(function () {
  "use strict";

  var studentPageState = {
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 0,
    search: "",
    loading: false,
    requestToken: 0,
  };

  var $studentSection = $("#page-master-student");
  var $studentTableBody = $("#studentTableBody");
  var $studentPagination = $("#studentPagination");
  var $studentSummary = $("#studentSummary");
  var $studentSearch = $("#studentSearch");

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function pickValue(row, keys) {
    for (var i = 0; i < keys.length; i += 1) {
      var key = keys[i];
      var value = row[key];
      if (
        value !== undefined &&
        value !== null &&
        String(value).trim() !== ""
      ) {
        return value;
      }
    }

    return "-";
  }

  function renderStatusBadge(status) {
    var label = String(status ?? "-");
    var normalized = label.toLowerCase();
    var badgeClass = "bg-secondary text-white";

    if (normalized === "active" || normalized === "1" || normalized === "yes") {
      badgeClass = "bg-success text-white";
    } else if (
      normalized === "inactive" ||
      normalized === "0" ||
      normalized === "no"
    ) {
      badgeClass = "bg-warning text-dark";
    }

    return (
      '<span class="badge ' + badgeClass + '">' + escapeHtml(label) + "</span>"
    );
  }

  function setLoadingState() {
    $studentTableBody.html(
      '<tr><td colspan="6" class="text-center py-4 text-muted">Loading student data...</td></tr>',
    );
    $studentPagination.empty();
    $studentSummary.text("Loading students...");
  }

  function renderEmptyState(message) {
    $studentTableBody.html(
      '<tr><td colspan="6" class="text-center py-4 text-muted">' +
        escapeHtml(message) +
        "</td></tr>",
    );
    $studentPagination.empty();
  }

  function buildPaginationItems(currentPage, totalPages) {
    if (totalPages <= 1) {
      return "";
    }

    var items = [];

    function addPageItem(label, pageNumber, disabled, active) {
      items.push(
        '<li class="page-item' +
          (disabled ? " disabled" : "") +
          (active ? " active" : "") +
          '">' +
          '<a class="page-link" href="#" data-student-page="' +
          pageNumber +
          '">' +
          label +
          "</a></li>",
      );
    }

    addPageItem(
      "Previous",
      Math.max(1, currentPage - 1),
      currentPage === 1,
      false,
    );

    var startPage = Math.max(1, currentPage - 1);
    var endPage = Math.min(totalPages, currentPage + 1);

    if (currentPage <= 2) {
      endPage = Math.min(totalPages, 3);
    }

    if (currentPage >= totalPages - 1) {
      startPage = Math.max(1, totalPages - 2);
    }

    if (startPage > 1) {
      addPageItem("1", 1, false, currentPage === 1);
      if (startPage > 2) {
        items.push(
          '<li class="page-item disabled"><span class="page-link">...</span></li>',
        );
      }
    }

    for (var page = startPage; page <= endPage; page += 1) {
      if (page !== 1 && page !== totalPages) {
        addPageItem(String(page), page, false, page === currentPage);
      }
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        items.push(
          '<li class="page-item disabled"><span class="page-link">...</span></li>',
        );
      }
      addPageItem(
        String(totalPages),
        totalPages,
        false,
        currentPage === totalPages,
      );
    }

    addPageItem(
      "Next",
      Math.min(totalPages, currentPage + 1),
      currentPage === totalPages,
      false,
    );

    return items.join("");
  }

  function renderStudents(rows, meta) {
    var html = "";

    if (!rows.length) {
      renderEmptyState("No student data found.");
      $studentSummary.text("Showing 0 students");
      return;
    }

    rows.forEach(function (row, index) {
      var rowNumber = (meta.page - 1) * meta.limit + index + 1;
      var name = pickValue(row, [
        "name",
        "student_name",
        "fullname",
        "full_name",
      ]);
      var grade = pickValue(row, ["grade", "class", "class_name", "kelas"]);
      var level = pickValue(row, [
        "level_name",
        "level",
        "division",
        "division_name",
        "section",
      ]);
      var phone = pickValue(row, ["phone", "phone_number", "mobile", "telp"]);
      var status = pickValue(row, ["status"]);

      html +=
        "<tr>" +
        "<td>" +
        rowNumber +
        "</td>" +
        "<td><strong>" +
        escapeHtml(name) +
        "</strong></td>" +
        "<td>" +
        escapeHtml(grade) +
        "</td>" +
        "<td>" +
        escapeHtml(level) +
        "</td>" +
        "<td>" +
        escapeHtml(phone) +
        "</td>" +
        "<td>" +
        renderStatusBadge(status) +
        "</td>" +
        "</tr>";
    });

    $studentTableBody.html(html);
    $studentPagination.html(buildPaginationItems(meta.page, meta.totalPages));
    $studentSummary.text(
      "Showing " + rows.length + " of " + meta.total + " students",
    );
  }

  function loadStudentData(page, search) {
    if (typeof search === "string") {
      studentPageState.search = search;
    }

    var requestToken = ++studentPageState.requestToken;
    studentPageState.loading = true;
    setLoadingState();

    ajaxRequest({
      url: "service/student.php",
      method: "GET",
      data: {
        action: "list",
        page: page || 1,
        limit: studentPageState.limit,
        search: studentPageState.search,
      },
    })
      .then(function (response) {
        if (requestToken !== studentPageState.requestToken) {
          return;
        }

        if (!response || !response.success) {
          throw new Error(
            (response && response.message) || "Unable to load student data.",
          );
        }

        studentPageState.page = response.page || page || 1;
        studentPageState.limit = response.limit || studentPageState.limit;
        studentPageState.total = response.total || 0;
        studentPageState.totalPages = response.totalPages || 0;
        studentPageState.search =
          response.search || studentPageState.search || "";

        renderStudents(response.data || [], {
          page: studentPageState.page,
          limit: studentPageState.limit,
          total: studentPageState.total,
          totalPages: studentPageState.totalPages,
        });
      })
      .catch(function (error) {
        if (requestToken !== studentPageState.requestToken) {
          return;
        }

        renderEmptyState(error.message || "Failed to load student data.");
        $studentSummary.text("Showing 0 students");
      })
      .finally(function () {
        if (requestToken !== studentPageState.requestToken) {
          return;
        }

        studentPageState.loading = false;
      });
  }

  $(document).on("click", '[data-page="master-student"]', function () {
    loadStudentData(1);
  });

  $(document).on(
    "click",
    "#studentPagination [data-student-page]",
    function (e) {
      e.preventDefault();
      var page = parseInt($(this).data("student-page"), 10) || 1;
      if (
        !$(this).parent().hasClass("disabled") &&
        !$(this).parent().hasClass("active")
      ) {
        loadStudentData(page);
      }
    },
  );

  if ($studentSection.hasClass("active")) {
    loadStudentData(1);
  }

  var studentSearchTimer = null;
  $studentSearch.on("input", function () {
    var val = $(this).val().trim();

    clearTimeout(studentSearchTimer);
    studentSearchTimer = setTimeout(function () {
      loadStudentData(1, val);
    }, 300);
  });
});
