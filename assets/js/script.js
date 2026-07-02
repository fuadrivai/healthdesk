$(document).ajaxStop($.unblockUI);
$(document).ready(function () {});

function ajaxRequest(options) {
  return new Promise((resolve, reject) => {
    let settings = {
      url: "",
      method: "POST",
      data: {},
      button: null,
      loadingText: "Processing...",
      buttonText: "Submit",
      contentType: null,
    };

    settings = $.extend({}, settings, options);

    const $button = settings.button ? $(settings.button) : null;
    let originalButtonText = "";

    if ($button) {
      originalButtonText = $button.html();

      $button.prop("disabled", true).html(`
                <span class="spinner-border spinner-border-sm"></span>
                ${settings.loadingText}
            `);
    }

    $.ajax({
      url: settings.url,
      type: settings.method,
      data: settings.data,

      processData:
        settings.data instanceof FormData
          ? false
          : settings.contentType === "application/json"
            ? false
            : true,

      contentType:
        settings.data instanceof FormData
          ? false
          : (settings.contentType ??
            "application/x-www-form-urlencoded; charset=UTF-8"),

      cache: false,

      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        Accept: "application/json",
      },

      success: function (response) {
        resolve(response);
      },

      error: function (xhr) {
        let message = "Something went wrong";

        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          message = Object.values(xhr.responseJSON.errors)
            .map((e) => e[0])
            .join("<br>");
        } else if (xhr.responseJSON?.message) {
          message = xhr.responseJSON.message;
        }

        reject({
          xhr,
          message,
        });
      },

      complete: function () {
        if ($button) {
          $button.prop("disabled", false).html(originalButtonText);

          if (typeof lucide !== "undefined") {
            lucide.createIcons();
          }
        }
      },
    });
  });
}

function blockUI() {
  $.blockUI({
    message:
      '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
    css: {
      border: "none",
      backgroundColor: "transparent",
    },
  });
}

function unblockUI() {
  $.unblockUI();
}
