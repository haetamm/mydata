function logError(message, extra = "") {
  const data = {
    time: new Date().toISOString(),
    page: location.href,
    userAgent: navigator.userAgent,
    error: message,
    extra: extra,
  };

  const blob = new Blob([JSON.stringify(data) + "\n"], {
    type: "application/json",
  });

  if (navigator.sendBeacon) {
    navigator.sendBeacon("api/log-error.php", blob);
  } else {
    fetch("api/log-error.php", { method: "POST", body: blob, keepalive: true });
  }
}

function renderValidationErrors(err, options = {}) {
  const { itemsContainer = "#error-items", fieldPrefix = "error-" } = options;

  if (!err || !err.errors || typeof err.errors !== "object") return;

  // clear semua error dulu
  $(".text-danger.small").text("");
  $(itemsContainer).html("");

  Object.keys(err.errors).forEach((field) => {
    const errorValue = err.errors[field];

    // =========================
    // 1. ERROR FIELD BIASA
    // =========================
    if (typeof errorValue === "string") {
      const $fieldError = $(`#${fieldPrefix}${field}`);

      if ($fieldError.length) {
        $fieldError.text(errorValue);
      } else {
        // fallback
        $(itemsContainer).append(`<div>• ${errorValue}</div>`);
      }
      return;
    }

    // =========================
    // 2. ERROR NESTED (ARRAY / OBJECT)
    // =========================
    if (typeof errorValue === "object") {
      Object.keys(errorValue).forEach((index) => {
        const nestedErrors = errorValue[index];

        if (typeof nestedErrors === "object") {
          Object.keys(nestedErrors).forEach((nestedField) => {
            $(itemsContainer).append(
              `<div>• ${nestedErrors[nestedField]}</div>`,
            );
          });
        }
      });
    }
  });
}

document.querySelectorAll(".filter-input").forEach((input) => {
  // cek saat halaman pertama kali load
  if (input.value.trim() !== "") {
    input.classList.add("filled");
  }

  // tetap cek kalau user mengubah isi input
  input.addEventListener("input", () => {
    if (input.value.trim() !== "") {
      input.classList.add("filled");
    } else {
      input.classList.remove("filled");
    }
  });
});
