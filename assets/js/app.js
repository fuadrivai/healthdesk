$(document).ready(function () {
  "use strict";

  // ============================================
  // TOOLTIPS & POPOVERS
  // ============================================
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  tooltipTriggerList.map(function (el) {
    return new bootstrap.Tooltip(el);
  });

  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]'),
  );
  popoverTriggerList.map(function (el) {
    return new bootstrap.Popover(el);
  });

  // ============================================
  // SIDEBAR TOGGLE
  // ============================================
  var $sidebar = $("#sidebar");
  var $overlay = $("#sidebarOverlay");
  var $mainWrapper = $("#mainWrapper");

  $("#hamburgerBtn").on("click", function () {
    if (window.innerWidth <= 991.98) {
      $sidebar.toggleClass("show");
      $overlay.toggleClass("show");
      $("body").css("overflow", $sidebar.hasClass("show") ? "hidden" : "");
    } else {
      $sidebar.toggleClass("collapsed");
      if ($sidebar.hasClass("collapsed")) {
        $sidebar.css("transform", "translateX(-100%)");
        $mainWrapper.css("margin-left", "0");
      } else {
        $sidebar.css("transform", "translateX(0)");
        $mainWrapper.css("margin-left", "260px");
      }
    }
  });

  $overlay.on("click", function () {
    $sidebar.removeClass("show");
    $overlay.removeClass("show");
    $("body").css("overflow", "");
  });

  // Handle resize
  $(window).on("resize", function () {
    if (window.innerWidth > 991.98) {
      $sidebar.removeClass("show");
      $overlay.removeClass("show");
      $("body").css("overflow", "");
      if (!$sidebar.hasClass("collapsed")) {
        $sidebar.css("transform", "translateX(0)");
        $mainWrapper.css("margin-left", "260px");
      }
    } else {
      $sidebar.css("transform", "");
      $mainWrapper.css("margin-left", "0");
    }
  });

  // ============================================
  // ACTIVE SIDEBAR MENU & PAGE NAVIGATION
  // ============================================
  var $navLinks = $(".sidebar-menu .nav-link");
  var $pages = $(".page-section");

  function navigateTo(pageId) {
    // Hide all pages
    $pages.removeClass("active");

    // Show target page
    var $target = $("#page-" + pageId);
    if ($target.length) {
      $target.addClass("active");
    }

    // Update nav active state
    $navLinks.removeClass("active");
    $navLinks
      .filter('[data-page="' + pageId + '"]')
      .add('[ata-page="' + pageId + '"]')
      .addClass("active");

    // Close sidebar on mobile
    if (window.innerWidth <= 991.98) {
      $sidebar.removeClass("show");
      $overlay.removeClass("show");
      $("body").css("overflow", "");
    }

    // Scroll to top
    $("html, body").animate(
      {
        scrollTop: 0,
      },
      300,
    );
  }

  $navLinks.on("click", function (e) {
    e.preventDefault();
    var page = $(this).data("page") || $(this).attr("ata-page");
    if (page) {
      navigateTo(page);
    }
  });

  // Handle clicks on breadcrumb links and other nav triggers
  $(document).on("click", "[data-page], [ata-page]", function (e) {
    e.preventDefault();
    var page = $(this).data("page") || $(this).attr("ata-page");
    if (page) {
      navigateTo(page);
    }
  });

  // Support opening specific pages directly via URL or server-provided value.
  var initialPage =
    $("body").data("initial-page") ||
    new URLSearchParams(window.location.search).get("page");
  if (initialPage) {
    navigateTo(initialPage);
  }

  // ============================================
  // NOTIFICATION BELL (Toast)
  // ============================================
  $("#notifBtn").on("click", function () {
    showToast("You have 3 new notifications", "bi-bell-fill");
  });

  // ============================================
  // TOAST SYSTEM
  // ============================================
  function showToast(message, icon) {
    var $container = $("#toastContainer");
    var $toast = $(
      '<div class="toast-modern">' +
        '<span class="toast-icon"><i class="bi ' +
        (icon || "bi-info-circle") +
        '"></i></span>' +
        '<span class="toast-body">' +
        message +
        "</span>" +
        '<button class="toast-close"><i class="bi bi-x"></i></button>' +
        "</div>",
    );

    $container.append($toast);

    setTimeout(function () {
      $toast.addClass("hide");
      setTimeout(function () {
        $toast.remove();
      }, 300);
    }, 4000);

    $toast.find(".toast-close").on("click", function () {
      $toast.addClass("hide");
      setTimeout(function () {
        $toast.remove();
      }, 300);
    });
  }

  // ============================================
  // SHOW TOAST BUTTON (Quick Action)
  // ============================================
  $("#showToastBtn").on("click", function () {
    showToast("Notification sent to all staff!", "bi-bell-fill");
  });

  // ============================================
  // SAVE VISITOR BUTTON - LOADING ANIMATION
  // ============================================
  $("#saveVisitorBtn").on("click", function (e) {
    e.preventDefault();
    var $btn = $(this);
    if ($btn.hasClass("btn-loading")) return;

    $btn.addClass("btn-loading");
    $btn.find(".btn-text").text("Saving...");

    setTimeout(function () {
      $btn.removeClass("btn-loading");
      $btn.find(".btn-text").text("Saved!");
      showToast("Visitor record saved successfully!", "bi-check2-circle");

      setTimeout(function () {
        $btn.find(".btn-text").text("Save");
      }, 2000);
    }, 1800);
  });

  // ============================================
  // DASHBOARD - REAL DATA
  // ============================================
  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function formatDashboardDate(dateValue) {
    var dateObj = new Date(dateValue);
    if (Number.isNaN(dateObj.getTime())) {
      return "Today";
    }

    return dateObj.toLocaleDateString("en-US", {
      weekday: "short",
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  }

  function formatVisitTime(dateValue, timeValue) {
    var value = String(timeValue || "").trim();
    if (value === "") {
      return "-";
    }

    var parsed = new Date(String(dateValue || "") + "T" + value);
    if (Number.isNaN(parsed.getTime())) {
      return value;
    }

    return parsed.toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    });
  }

  function renderStatusBadge(status) {
    var code = Number(status || 0);
    if (code === 1) {
      return '<span class="badge badge-modern badge-status-treatment">Treatment</span>';
    }
    if (code === 2) {
      return '<span class="badge badge-modern badge-status-recovered">Recovered</span>';
    }
    return '<span class="badge badge-modern badge-status-waiting">Waiting</span>';
  }

  function renderIllnessCategories(items) {
    var $container = $("#dashboardIllnessList");
    if (!$container.length) {
      return;
    }

    var rows = Array.isArray(items) ? items : [];
    if (!rows.length) {
      $container.html(
        '<div class="item"><span class="label">No data</span><div class="bar-track"><div class="bar-fill" style="width:0%;"></div></div><span class="value">0%</span></div>',
      );
      return;
    }

    var maxCount = 0;
    rows.forEach(function (row) {
      maxCount = Math.max(maxCount, Number(row.total || 0));
    });

    if (maxCount <= 0) {
      maxCount = 1;
    }

    var html = "";
    rows.forEach(function (row) {
      var total = Number(row.total || 0);
      var percent = Math.round((total / maxCount) * 100);
      html +=
        '<div class="item">' +
        '<span class="label">' +
        escapeHtml(row.name || "Unknown") +
        "</span>" +
        '<div class="bar-track"><div class="bar-fill" style="width:' +
        percent +
        '%;"></div></div>' +
        '<span class="value">' +
        total +
        "</span>" +
        "</div>";
    });

    $container.html(html);
  }

  function renderRecentVisitors(rows) {
    var $tbody = $("#dashboardRecentVisitorsBody");
    if (!$tbody.length) {
      return;
    }

    var visitors = Array.isArray(rows) ? rows : [];
    if (!visitors.length) {
      $tbody.html(
        '<tr><td colspan="6" class="text-center py-4 text-muted">No visitor data found.</td></tr>',
      );
      return;
    }

    var html = "";
    visitors.forEach(function (row) {
      var level = String(row.level || "-").trim();
      var grade = String(row.grade || "-").trim();

      html +=
        "<tr>" +
        '<td><span class="small">' +
        escapeHtml(formatVisitTime(row.date, row.time)) +
        "</span></td>" +
        "<td><strong>" +
        escapeHtml(row.name || "-") +
        "</strong></td>" +
        "<td>" +
        escapeHtml(level + " / " + grade) +
        "</td>" +
        "<td>" +
        escapeHtml(row.category_name || "-") +
        "</td>" +
        "<td>" +
        renderStatusBadge(row.status) +
        "</td>" +
        '<td><a href="javascript:void(0)" class="btn btn-view" data-page="list-visitor"><i class="bi bi-eye"></i></a></td>' +
        "</tr>";
    });

    $tbody.html(html);
  }

  var visitorChartInstance = null;

  function renderVisitorTrendChart(trendRows) {
    var ctx1 = document.getElementById("visitorChart");
    if (!ctx1) {
      return;
    }

    var rows = Array.isArray(trendRows) ? trendRows : [];
    var labels = rows.map(function (row) {
      return row.label || "-";
    });
    var visitorsData = rows.map(function (row) {
      return Number(row.visitors || 0);
    });
    var sickData = rows.map(function (row) {
      return Number(row.sick || 0);
    });

    if (visitorChartInstance) {
      visitorChartInstance.data.labels = labels;
      visitorChartInstance.data.datasets[0].data = visitorsData;
      visitorChartInstance.data.datasets[1].data = sickData;
      visitorChartInstance.update();
      return;
    }

    visitorChartInstance = new Chart(ctx1, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Visitors",
            data: visitorsData,
            backgroundColor: "rgba(79, 142, 247, 0.25)",
            borderColor: "#4F8EF7",
            borderWidth: 2,
            borderRadius: 6,
            tension: 0.4,
          },
          {
            label: "Sick Students",
            data: sickData,
            backgroundColor: "rgba(229, 115, 115, 0.20)",
            borderColor: "#E57373",
            borderWidth: 2,
            borderRadius: 6,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              font: {
                family: "Poppins",
                size: 11,
              },
              boxWidth: 12,
              padding: 12,
              usePointStyle: true,
              pointStyle: "circle",
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: "rgba(79, 142, 247, 0.04)",
            },
            ticks: {
              precision: 0,
              font: {
                family: "Poppins",
                size: 10,
              },
            },
          },
          x: {
            grid: {
              display: false,
            },
            ticks: {
              font: {
                family: "Poppins",
                size: 10,
              },
            },
          },
        },
      },
    });
  }

  function loadDashboardData() {
    $.ajax({
      url: "service/healthdesk.php",
      type: "GET",
      dataType: "json",
      data: {
        action: "dashboard",
      },
    })
      .done(function (response) {
        if (!response || !response.success || !response.data) {
          return;
        }

        var data = response.data;
        var summary = data.summary || {};

        $("#dashboardTodayVisitors").text(Number(summary.todayVisitors || 0));
        $("#dashboardSickToday").text(Number(summary.todaySick || 0));
        $("#dashboardWaitingToday").text(Number(summary.waiting || 0));
        $("#dashboardRecoveredToday").text(Number(summary.recovered || 0));
        $("#dashboardTodayBadge").html(
          '<i class="bi bi-calendar3 me-1"></i> ' +
            escapeHtml(formatDashboardDate(data.generatedAt)),
        );

        renderVisitorTrendChart(data.trend || []);
        renderIllnessCategories(data.categories || []);
        renderRecentVisitors(data.recent || []);
      })
      .fail(function () {
        $("#dashboardTodayVisitors").text("0");
        $("#dashboardSickToday").text("0");
        $("#dashboardWaitingToday").text("0");
        $("#dashboardRecoveredToday").text("0");
      });
  }

  $(document).on(
    "click",
    '[data-page="dashboard"], [ata-page="dashboard"]',
    function () {
      loadDashboardData();
    },
  );

  loadDashboardData();

  // ============================================
  // CHART.JS - Visitor Chart
  // ============================================
  var ctx1 = document.getElementById("visitorChart");
  if (ctx1 && !visitorChartInstance) {
    renderVisitorTrendChart([]);
  }

  // ============================================
  // CHART.JS - Report Chart
  // ============================================
  var ctx2 = document.getElementById("reportChart");
  if (ctx2) {
    new Chart(ctx2, {
      type: "line",
      data: {
        labels: ["Week 1", "Week 2", "Week 3", "Week 4"],
        datasets: [
          {
            label: "Visitors",
            data: [120, 145, 132, 168],
            borderColor: "#4F8EF7",
            backgroundColor: "rgba(79, 142, 247, 0.06)",
            fill: true,
            tension: 0.4,
            pointBackgroundColor: "#4F8EF7",
            pointRadius: 4,
          },
          {
            label: "Sick",
            data: [45, 58, 42, 67],
            borderColor: "#E57373",
            backgroundColor: "rgba(229, 115, 115, 0.06)",
            fill: true,
            tension: 0.4,
            pointBackgroundColor: "#E57373",
            pointRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              font: {
                family: "Poppins",
                size: 11,
              },
              boxWidth: 12,
              padding: 12,
              usePointStyle: true,
              pointStyle: "circle",
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: "rgba(79, 142, 247, 0.04)",
            },
            ticks: {
              font: {
                family: "Poppins",
                size: 10,
              },
            },
          },
          x: {
            grid: {
              display: false,
            },
            ticks: {
              font: {
                family: "Poppins",
                size: 10,
              },
            },
          },
        },
      },
    });
  }

  // ============================================
  // DEMO: Bootstrap Modal triggers (already via data-bs-toggle)
  // ============================================

  // ============================================
  // DEMO: Toast on page load (welcome)
  // ============================================
  setTimeout(function () {
    showToast("Welcome back, Sarah! 👋", "bi-emoji-smile");
  }, 600);

  // ============================================
  // SIDEBAR STATE ON LOAD (desktop default)
  // ============================================
  if (window.innerWidth > 991.98) {
    $sidebar.css("transform", "translateX(0)");
    $mainWrapper.css("margin-left", "260px");
  }

  // ============================================
  // NAVBAR DROPDOWN (Bootstrap handles it)
  // ============================================
}); // end document ready
