$(document).ready(function () {
  "use strict";

  var $visitorForm = $("#visitorForm");
  var $visitorId = $("#visitorId");
  var $visitorStudentId = $("#visitorStudentId");
  var $visitorName = $("#visitorName");
  var $visitorLevel = $("#visitorLevel");
  var $visitorGrade = $("#visitorGrade");
  var $visitorDate = $("#visitorDate");
  var $visitorTime = $("#visitorTime");
  var $visitorSickCategory = $("#visitorSickCategory");
  var $visitorStatus = $("#visitorStatus");
  var $visitorIntervention = $("#visitorIntervention");
  var $visitorAccidental = $("#visitorAccidental");
  var $visitorResult = $("#visitorResult");
  var $visitorItemUsed = $("#visitorItemUsed");
  var $visitorNote = $("#visitorNote");
  var $visitorImage = $("#visitorImage");
  var $visitorSubmitText = $("#visitorSubmitText");

  var $visitorTableBody = $("#visitorTableBody");
  var $visitorSummary = $("#visitorSummary");
  var $visitorSearch = $("#visitorSearch");
  var $visitorDivisionFilter = $("#visitorDivisionFilter");
  var $visitorStartDate = $("#visitorStartDate");
  var $visitorEndDate = $("#visitorEndDate");

  var $studentPickerSearch = $("#studentPickerSearch");
  var $studentPickerTableBody = $("#studentPickerTableBody");
  var $studentPickerPagination = $("#studentPickerPagination");
  var $studentPickerSummary = $("#studentPickerSummary");

  var pickerModalElement = document.getElementById("studentPickerModal");
  var studentPickerModal = pickerModalElement
    ? new bootstrap.Modal(pickerModalElement)
    : null;

  var studentPickerState = {
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 0,
    search: "",
    token: 0,
  };

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function formatStatus(status) {
    var code = Number(status || 0);
    if (code === 1) {
      return '<span class="badge badge-modern badge-status-treatment">Treatment</span>';
    }
    if (code === 2) {
      return '<span class="badge badge-modern badge-status-recovered">Recovered</span>';
    }
    return '<span class="badge badge-modern badge-status-waiting">Waiting</span>';
  }

  function todayIsoDate() {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, "0");
    var date = String(now.getDate()).padStart(2, "0");
    return year + "-" + month + "-" + date;
  }

  function loadCategories(selectedCategoryId) {
    var preservedValue = "";
    if (
      typeof selectedCategoryId !== "undefined" &&
      selectedCategoryId !== null
    ) {
      preservedValue = String(selectedCategoryId).trim();
    } else {
      preservedValue = String($visitorSickCategory.val() || "").trim();
    }

    ajaxRequest({
      url: "service/healthdesk.php",
      method: "GET",
      data: { action: "categories" },
    }).then(function (response) {
      if (!response || !response.success) {
        return;
      }

      var html = '<option value="">Select Category</option>';
      (response.data || []).forEach(function (row) {
        html +=
          '<option value="' +
          Number(row.id || 0) +
          '">' +
          escapeHtml(row.name || "-") +
          "</option>";
      });
      $visitorSickCategory.html(html);

      if (
        preservedValue !== "" &&
        $visitorSickCategory.find('option[value="' + preservedValue + '"]')
          .length
      ) {
        $visitorSickCategory.val(preservedValue);
      }
    });
  }

  function loadDivisionFilters() {
    var preservedValue = String($visitorDivisionFilter.val() || "").trim();

    ajaxRequest({
      url: "service/healthdesk.php",
      method: "GET",
      data: { action: "levels" },
    }).then(function (response) {
      if (!response || !response.success) {
        return;
      }

      var html = '<option value="">All Divisions</option>';
      (response.data || []).forEach(function (row) {
        var levelName = String(row.level_name || "").trim();
        if (levelName === "") {
          return;
        }

        html +=
          '<option value="' +
          escapeHtml(levelName) +
          '">' +
          escapeHtml(levelName) +
          "</option>";
      });

      $visitorDivisionFilter.html(html);

      if (
        preservedValue !== "" &&
        $visitorDivisionFilter.find('option[value="' + preservedValue + '"]')
          .length
      ) {
        $visitorDivisionFilter.val(preservedValue);
      }
    });
  }

  function getVisitorFilters() {
    return {
      search: String($visitorSearch.val() || "").trim(),
      division: String($visitorDivisionFilter.val() || "").trim(),
      start_date: String($visitorStartDate.val() || "").trim(),
      end_date: String($visitorEndDate.val() || "").trim(),
    };
  }

  function resetVisitorForm() {
    $visitorForm[0].reset();
    $visitorId.val("");
    $visitorStudentId.val("");
    $visitorDate.val(todayIsoDate());
    $visitorSubmitText.text("Save");
  }

  function renderVisitors(rows) {
    if (!rows.length) {
      $visitorTableBody.html(
        '<tr><td colspan="7" class="text-center py-4 text-muted">No visitor data found.</td></tr>',
      );
      $visitorSummary.text("Showing 0 entries");
      return;
    }

    var html = "";
    rows.forEach(function (row, index) {
      var packed = encodeURIComponent(
        JSON.stringify({
          id: row.id,
          student_id: row.student_id,
          name: row.name,
          level: row.level,
          grade: row.grade,
          sick_category_id: row.sick_category_id,
          date: row.date,
          time: row.time,
          intervention: row.intervention,
          accidental: row.accidental,
          status: row.status,
          result: row.result,
          item_used: row.item_used,
          note: row.note,
        }),
      );

      html +=
        "<tr>" +
        "<td>" +
        (index + 1) +
        "</td>" +
        "<td><strong>" +
        escapeHtml(row.name || "-") +
        "</strong></td>" +
        "<td>" +
        escapeHtml((row.date || "") + " " + (row.time || "")) +
        "</td>" +
        "<td>" +
        escapeHtml((row.level || "-") + " / " + (row.grade || "-")) +
        "</td>" +
        "<td>" +
        escapeHtml(row.category_name || "-") +
        "</td>" +
        "<td>" +
        escapeHtml(row.accidental || "-") +
        "</td>" +
        '<td><div class="action-btns d-flex gap-1">' +
        '<button class="btn btn-view" type="button" data-action="edit-visitor" data-visitor="' +
        packed +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button class="btn btn-delete" type="button" data-action="delete-visitor" data-id="' +
        Number(row.id || 0) +
        '"><i class="bi bi-trash"></i></button>' +
        "</div></td>" +
        "</tr>";
    });

    $visitorTableBody.html(html);
    $visitorSummary.text("Showing " + rows.length + " entries");
  }

  function loadVisitors() {
    var filters = getVisitorFilters();

    ajaxRequest({
      url: "service/healthdesk.php",
      method: "GET",
      data: {
        action: "visitors",
        search: filters.search,
        division: filters.division,
        start_date: filters.start_date,
        end_date: filters.end_date,
      },
    })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error("Unable to load visitor data.");
        }
        renderVisitors(response.data || []);
      })
      .catch(function (error) {
        $visitorTableBody.html(
          '<tr><td colspan="7" class="text-center py-4 text-muted">' +
            escapeHtml(error.message || "Failed to load visitor data.") +
            "</td></tr>",
        );
      });
  }

  function buildStudentPagination(currentPage, totalPages) {
    if (totalPages <= 1) {
      return "";
    }

    var html = "";
    var start = Math.max(1, currentPage - 1);
    var end = Math.min(totalPages, currentPage + 1);

    html +=
      '<li class="page-item' +
      (currentPage === 1 ? " disabled" : "") +
      '"><a href="#" class="page-link" data-student-picker-page="' +
      Math.max(1, currentPage - 1) +
      '">Previous</a></li>';

    for (var page = start; page <= end; page += 1) {
      html +=
        '<li class="page-item' +
        (page === currentPage ? " active" : "") +
        '"><a href="#" class="page-link" data-student-picker-page="' +
        page +
        '">' +
        page +
        "</a></li>";
    }

    html +=
      '<li class="page-item' +
      (currentPage === totalPages ? " disabled" : "") +
      '"><a href="#" class="page-link" data-student-picker-page="' +
      Math.min(totalPages, currentPage + 1) +
      '">Next</a></li>';

    return html;
  }

  function renderStudentPicker(rows) {
    if (!rows.length) {
      $studentPickerTableBody.html(
        '<tr><td colspan="5" class="text-center py-4 text-muted">No student found.</td></tr>',
      );
      return;
    }

    var html = "";
    rows.forEach(function (row, index) {
      var data = encodeURIComponent(
        JSON.stringify({
          id: row.id,
          student_name: row.student_name,
          level_name: row.level_name || row.level,
          grade: row.grade,
        }),
      );

      html +=
        "<tr>" +
        "<td>" +
        ((studentPickerState.page - 1) * studentPickerState.limit + index + 1) +
        "</td>" +
        "<td>" +
        escapeHtml(row.student_name || "-") +
        "</td>" +
        "<td>" +
        escapeHtml(row.grade || "-") +
        "</td>" +
        "<td>" +
        escapeHtml(row.level_name || row.level || "-") +
        "</td>" +
        '<td><button class="btn btn-modern btn-modern-primary btn-sm" type="button" data-action="pick-student" data-student="' +
        data +
        '">Select</button></td>' +
        "</tr>";
    });

    $studentPickerTableBody.html(html);
  }

  function loadStudentPicker(page, search) {
    studentPickerState.page = page || 1;
    if (typeof search === "string") {
      studentPickerState.search = search;
    }

    var token = ++studentPickerState.token;

    ajaxRequest({
      url: "service/healthdesk.php",
      method: "GET",
      data: {
        action: "students",
        page: studentPickerState.page,
        limit: studentPickerState.limit,
        search: studentPickerState.search,
      },
    })
      .then(function (response) {
        if (token !== studentPickerState.token) {
          return;
        }
        if (!response || !response.success) {
          throw new Error("Unable to load student list.");
        }

        studentPickerState.total = Number(response.total || 0);
        studentPickerState.totalPages = Number(response.totalPages || 0);
        studentPickerState.page = Number(response.page || 1);

        renderStudentPicker(response.data || []);
        $studentPickerPagination.html(
          buildStudentPagination(
            studentPickerState.page,
            studentPickerState.totalPages,
          ),
        );
        $studentPickerSummary.text(
          "Showing " +
            (response.data || []).length +
            " of " +
            studentPickerState.total +
            " students",
        );
      })
      .catch(function (error) {
        $studentPickerTableBody.html(
          '<tr><td colspan="5" class="text-center py-4 text-muted">' +
            escapeHtml(error.message || "Failed to load student list.") +
            "</td></tr>",
        );
      });
  }

  function fillVisitorForm(visitor) {
    $visitorId.val(visitor.id || "");
    $visitorStudentId.val(visitor.student_id || "");
    $visitorName.val(visitor.name || "");
    $visitorLevel.val(visitor.level || "");
    $visitorGrade.val(visitor.grade || "");
    $visitorSickCategory.val(
      visitor.sick_category_id === null ||
        typeof visitor.sick_category_id === "undefined"
        ? ""
        : String(visitor.sick_category_id),
    );
    $visitorDate.val(visitor.date || "");
    $visitorTime.val(visitor.time || "");
    $visitorIntervention.val(visitor.intervention || "");
    $visitorAccidental.val(visitor.accidental || "");
    $visitorStatus.val(String(visitor.status || 0));
    $visitorResult.val(visitor.result || "");
    $visitorItemUsed.val(visitor.item_used || "");
    $visitorNote.val(visitor.note || "");
    $visitorSubmitText.text("Update");
  }

  function submitVisitorForm() {
    function fieldValue($field) {
      if (!$field || !$field.length) {
        return "";
      }

      var value = $field.val();
      return typeof value === "string"
        ? value.trim()
        : String(value || "").trim();
    }

    var isUpdate = $visitorId.val().trim() !== "";
    var payload = new FormData($visitorForm[0]);
    payload.set("action", isUpdate ? "visitor_update" : "visitor_create");
    payload.set("id", fieldValue($visitorId));
    payload.set("student_id", fieldValue($visitorStudentId));
    payload.set("name", fieldValue($visitorName));
    payload.set("level", fieldValue($visitorLevel));
    payload.set("grade", fieldValue($visitorGrade));
    payload.set("sick_category_id", fieldValue($visitorSickCategory));
    payload.set("date", fieldValue($visitorDate));
    payload.set("time", fieldValue($visitorTime));
    payload.set("intervention", fieldValue($visitorIntervention));
    payload.set("accidental", fieldValue($visitorAccidental));
    payload.set("status", "0");
    payload.set("result", fieldValue($visitorResult));
    payload.set("item_used", fieldValue($visitorItemUsed));
    payload.set("note", fieldValue($visitorNote));

    if (!$visitorImage[0].files.length) {
      payload.delete("image");
    }

    ajaxRequest({
      url: "service/healthdesk.php",
      method: "POST",
      data: payload,
      button: "#submitVisitorBtn",
      loadingText: isUpdate ? "Updating..." : "Saving...",
    })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error("Failed to save visitor.");
        }

        resetVisitorForm();
        loadVisitors();
      })
      .catch(function (error) {
        alert(error.message || "Failed to save visitor.");
      });
  }

  function deleteVisitor(id) {
    if (!window.confirm("Delete this visitor record?")) {
      return;
    }

    ajaxRequest({
      url: "service/healthdesk.php",
      method: "POST",
      data: {
        action: "visitor_delete",
        id: id,
      },
    })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error("Failed to delete visitor.");
        }
        loadVisitors();
      })
      .catch(function (error) {
        alert(error.message || "Failed to delete visitor.");
      });
  }

  $(document).on(
    "click",
    '[data-page="input-visitor"], [ata-page="input-visitor"]',
    function () {
      loadCategories();
    },
  );

  $(document).on(
    "click",
    '[data-page="list-visitor"], [ata-page="list-visitor"]',
    function () {
      loadDivisionFilters();
      loadVisitors();
    },
  );

  var visitorSearchTimer = null;
  $visitorSearch.on("input", function () {
    clearTimeout(visitorSearchTimer);
    visitorSearchTimer = setTimeout(function () {
      loadVisitors();
    }, 300);
  });

  $visitorDivisionFilter.on("change", function () {
    loadVisitors();
  });

  $visitorStartDate.on("change", function () {
    loadVisitors();
  });

  $visitorEndDate.on("change", function () {
    loadVisitors();
  });

  $("#openStudentPickerBtn").on("click", function () {
    if (studentPickerModal) {
      studentPickerModal.show();
    }
    loadStudentPicker(1, "");
  });

  var pickerSearchTimer = null;
  $studentPickerSearch.on("input", function () {
    var val = $(this).val().trim();
    clearTimeout(pickerSearchTimer);
    pickerSearchTimer = setTimeout(function () {
      loadStudentPicker(1, val);
    }, 300);
  });

  $(document).on("click", "[data-student-picker-page]", function (e) {
    e.preventDefault();
    var page = parseInt($(this).data("student-picker-page"), 10) || 1;
    if (
      !$(this).parent().hasClass("disabled") &&
      !$(this).parent().hasClass("active")
    ) {
      loadStudentPicker(page);
    }
  });

  $(document).on("click", '[data-action="pick-student"]', function () {
    var encoded = $(this).attr("data-student") || "";
    try {
      var data = JSON.parse(decodeURIComponent(encoded));
      $visitorStudentId.val(data.id || "");
      $visitorName.val(data.student_name || "");
      $visitorLevel.val(data.level_name || "");
      $visitorGrade.val(data.grade || "");

      if (studentPickerModal) {
        studentPickerModal.hide();
      }
    } catch (e) {
      alert("Invalid student data.");
    }
  });

  $(document).on("click", '[data-action="edit-visitor"]', function () {
    var encoded = $(this).attr("data-visitor") || "";
    try {
      var data = JSON.parse(decodeURIComponent(encoded));
      fillVisitorForm(data);
      $("[data-page='input-visitor'], [ata-page='input-visitor']").trigger(
        "click",
      );
      loadCategories(data.sick_category_id);
    } catch (e) {
      alert("Invalid visitor data.");
    }
  });

  $(document).on("click", '[data-action="delete-visitor"]', function () {
    var id = parseInt($(this).data("id"), 10) || 0;
    if (id > 0) {
      deleteVisitor(id);
    }
  });

  $("#resetVisitorFormBtn").on("click", function () {
    resetVisitorForm();
  });

  $visitorForm.on("submit", function (e) {
    e.preventDefault();
    submitVisitorForm();
  });

  resetVisitorForm();
  loadCategories();
  loadDivisionFilters();
  loadVisitors();
});
