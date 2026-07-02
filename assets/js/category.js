$(document).ready(function () {
  "use strict";

  var $categorySection = $("#page-master-sick");
  var $categoryTableBody = $("#categoryTableBody");
  var $categoryModal = $("#addCategoryModal");
  var categoryModal = $categoryModal.length
    ? new bootstrap.Modal($categoryModal[0])
    : null;

  var $categoryForm = $("#categoryForm");
  var $categoryId = $("#categoryId");
  var $categoryName = $("#categoryName");
  var $categorySeverity = $("#categorySeverity");
  var $categoryColorBadge = $("#categoryColorBadge");
  var $saveCategoryBtn = $("#saveCategoryBtn");
  var $categoryModalTitle = $("#categoryModalTitle");

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function setLoadingState() {
    $categoryTableBody.html(
      '<tr><td colspan="5" class="text-center py-4 text-muted">Loading category data...</td></tr>',
    );
  }

  function renderEmptyState(message) {
    $categoryTableBody.html(
      '<tr><td colspan="5" class="text-center py-4 text-muted">' +
        escapeHtml(message) +
        "</td></tr>",
    );
  }

  function renderColorBadge(color) {
    if (!color) {
      return '<span class="text-muted">-</span>';
    }

    return (
      '<span class="badge text-white" style="background:' +
      escapeHtml(color) +
      ';">' +
      escapeHtml(color) +
      "</span>"
    );
  }

  function renderCategories(rows) {
    if (!rows.length) {
      renderEmptyState("No category data found.");
      return;
    }

    var html = "";

    rows.forEach(function (row, index) {
      var rowData = {
        id: Number(row.id || 0),
        name: row.name || "",
        severity: row.severity || "",
        color_badge: row.color_badge || "",
      };

      html +=
        "<tr>" +
        "<td>" +
        (index + 1) +
        "</td>" +
        "<td><strong>" +
        escapeHtml(rowData.name) +
        "</strong></td>" +
        "<td>" +
        escapeHtml(rowData.severity) +
        "</td>" +
        "<td>" +
        renderColorBadge(rowData.color_badge) +
        "</td>" +
        '<td><div class="action-btns d-flex gap-1">' +
        '<button class="btn btn-edit" type="button" data-action="edit-category" data-category="' +
        encodeURIComponent(JSON.stringify(rowData)) +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button class="btn btn-delete" type="button" data-action="delete-category" data-id="' +
        rowData.id +
        '"><i class="bi bi-trash"></i></button>' +
        "</div></td>" +
        "</tr>";
    });

    $categoryTableBody.html(html);
  }

  function loadCategoryData() {
    setLoadingState();

    ajaxRequest({
      url: "service/category.php",
      method: "GET",
      data: {
        action: "list",
      },
    })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error(
            (response && response.message) || "Unable to load category data.",
          );
        }

        renderCategories(response.data || []);
      })
      .catch(function (error) {
        renderEmptyState(error.message || "Failed to load category data.");
      });
  }

  function resetCategoryForm() {
    $categoryForm[0].reset();
    $categoryId.val("");
    $categoryColorBadge.val("#4F8EF7");
    $categoryModalTitle.html(
      '<i class="bi bi-tag text-primary-custom me-2"></i>Add Sick Category',
    );
    $saveCategoryBtn.text("Save Category");
  }

  function openCreateModal() {
    resetCategoryForm();
    if (categoryModal) {
      categoryModal.show();
    }
  }

  function openEditModal(data) {
    $categoryId.val(data.id || "");
    $categoryName.val(data.name || "");
    $categorySeverity.val(data.severity || "");
    $categoryColorBadge.val(data.color_badge || "#4F8EF7");
    $categoryModalTitle.html(
      '<i class="bi bi-tag text-primary-custom me-2"></i>Edit Sick Category',
    );
    $saveCategoryBtn.text("Update Category");

    if (categoryModal) {
      categoryModal.show();
    }
  }

  function submitCategoryForm() {
    var id = $categoryId.val().trim();
    var name = $categoryName.val().trim();
    var severity = $categorySeverity.val().trim();
    var colorBadge = $categoryColorBadge.val().trim();

    if (!name || !severity) {
      alert("Name and severity are required.");
      return;
    }

    var action = id ? "update" : "create";
    var payload = {
      action: action,
      name: name,
      severity: severity,
      color_badge: colorBadge,
    };

    if (id) {
      payload.id = id;
    }

    ajaxRequest({
      url: "service/category.php",
      method: "POST",
      data: payload,
      button: "#saveCategoryBtn",
      loadingText: id ? "Updating..." : "Saving...",
    })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error(
            (response && response.message) || "Failed to save category.",
          );
        }

        if (categoryModal) {
          categoryModal.hide();
        }
        loadCategoryData();
      })
      .catch(function (error) {
        alert(error.message || "Failed to save category.");
      });
  }

  function deleteCategory(id) {
    if (!window.confirm("Delete this category?")) {
      return;
    }

    ajaxRequest({
      url: "service/category.php",
      method: "POST",
      data: {
        action: "delete",
        id: id,
      },
    })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error(
            (response && response.message) || "Failed to delete category.",
          );
        }

        loadCategoryData();
      })
      .catch(function (error) {
        alert(error.message || "Failed to delete category.");
      });
  }

  $(document).on("click", '[data-page="master-sick"]', function () {
    loadCategoryData();
  });

  $(document).on("click", '[data-action="open-category-create"]', function () {
    openCreateModal();
  });

  $(document).on("click", '[data-action="edit-category"]', function () {
    var raw = $(this).attr("data-category") || "";
    try {
      var data = JSON.parse(decodeURIComponent(raw));
      openEditModal(data);
    } catch (e) {
      alert("Invalid category data.");
    }
  });

  $(document).on("click", '[data-action="delete-category"]', function () {
    var id = parseInt($(this).data("id"), 10) || 0;
    if (id > 0) {
      deleteCategory(id);
    }
  });

  $categoryForm.on("submit", function (e) {
    e.preventDefault();
    submitCategoryForm();
  });

  if ($categorySection.hasClass("active")) {
    loadCategoryData();
  }
});
