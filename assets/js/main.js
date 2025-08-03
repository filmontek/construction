// Construction Management System JavaScript

document.addEventListener("DOMContentLoaded", function () {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Initialize popovers
  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Auto-hide alerts after 5 seconds
  setTimeout(function () {
    var alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
    alerts.forEach(function (alert) {
      var bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);

  // Form validation
  var forms = document.querySelectorAll(".needs-validation");
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add("was-validated");
      },
      false
    );
  });

  // Password strength indicator
  var passwordInput = document.getElementById("password");
  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      checkPasswordStrength(this.value);
    });
  }

  // Confirm password validation
  var confirmPasswordInput = document.getElementById("confirm_password");
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener("input", function () {
      validatePasswordMatch();
    });
  }

  // Search functionality
  var searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      filterTable(this.value);
    });
  }

  // Plan form auto-save (draft)
  var planForm = document.getElementById("planForm");
  if (planForm) {
    setInterval(function () {
      autoSaveDraft();
    }, 30000); // Auto-save every 30 seconds
  }
});

// Password strength checker
function checkPasswordStrength(password) {
  var strengthIndicator = document.getElementById("passwordStrength");
  if (!strengthIndicator) return;

  var strength = 0;
  var feedback = [];

  if (password.length >= 8) strength++;
  else feedback.push("At least 8 characters");

  if (/[a-z]/.test(password)) strength++;
  else feedback.push("Lowercase letter");

  if (/[A-Z]/.test(password)) strength++;
  else feedback.push("Uppercase letter");

  if (/[0-9]/.test(password)) strength++;
  else feedback.push("Number");

  if (/[^A-Za-z0-9]/.test(password)) strength++;
  else feedback.push("Special character");

  var strengthText = ["Very Weak", "Weak", "Fair", "Good", "Strong"];
  var strengthColors = ["danger", "warning", "info", "primary", "success"];

  strengthIndicator.className =
    "progress-bar bg-" + strengthColors[strength - 1];
  strengthIndicator.style.width = strength * 20 + "%";
  strengthIndicator.textContent = strengthText[strength - 1] || "";

  var feedbackElement = document.getElementById("passwordFeedback");
  if (feedbackElement) {
    feedbackElement.innerHTML =
      feedback.length > 0
        ? "Missing: " + feedback.join(", ")
        : "Password strength is good!";
  }
}

// Validate password match
function validatePasswordMatch() {
  var password = document.getElementById("password").value;
  var confirmPassword = document.getElementById("confirm_password").value;
  var feedback = document.getElementById("passwordMatchFeedback");

  if (confirmPassword && password !== confirmPassword) {
    feedback.textContent = "Passwords do not match";
    feedback.className = "text-danger small";
    return false;
  } else if (confirmPassword) {
    feedback.textContent = "Passwords match";
    feedback.className = "text-success small";
    return true;
  }
  return true;
}

// Table search/filter
function filterTable(searchTerm) {
  var table = document.querySelector(".table tbody");
  if (!table) return;

  var rows = table.querySelectorAll("tr");
  searchTerm = searchTerm.toLowerCase();

  rows.forEach(function (row) {
    var text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? "" : "none";
  });
}

// Confirm action dialogs
function confirmAction(message, callback) {
  if (confirm(message)) {
    callback();
  }
}

// Show loading spinner
function showLoading(button) {
  var originalText = button.innerHTML;
  button.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
  button.disabled = true;

  return function () {
    button.innerHTML = originalText;
    button.disabled = false;
  };
}

// Auto-save draft functionality
function autoSaveDraft() {
  var form = document.getElementById("planForm");
  if (!form) return;

  var formData = new FormData(form);
  formData.append("action", "auto_save");

  fetch("/construction_management/pages/plans/auto_save.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Draft saved automatically", "success");
      }
    })
    .catch((error) => {
      console.error("Auto-save failed:", error);
    });
}

// Show notification
function showNotification(message, type = "info") {
  var notification = document.createElement("div");
  notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
  notification.style.cssText =
    "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
  notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  document.body.appendChild(notification);

  setTimeout(function () {
    notification.remove();
  }, 5000);
}

// Plan status update
function updatePlanStatus(planId, status, button) {
  var hideLoading = showLoading(button);

  fetch("/construction_management/pages/plans/update_status.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      plan_id: planId,
      status: status,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoading();
      if (data.success) {
        showNotification("Plan status updated successfully", "success");
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification("Failed to update plan status", "danger");
      }
    })
    .catch((error) => {
      hideLoading();
      showNotification("An error occurred", "danger");
      console.error("Error:", error);
    });
}

// User status update
function updateUserStatus(userId, status, button) {
  var hideLoading = showLoading(button);

  fetch("/construction_management/pages/users/update_status.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      user_id: userId,
      status: status,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoading();
      if (data.success) {
        showNotification("User status updated successfully", "success");
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification("Failed to update user status", "danger");
      }
    })
    .catch((error) => {
      hideLoading();
      showNotification("An error occurred", "danger");
      console.error("Error:", error);
    });
}

// Toggle password visibility
function togglePassword(inputId, iconId) {
  var input = document.getElementById(inputId);
  var icon = document.getElementById(iconId);

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

// Print functionality
function printPage() {
  window.print();
}

// Export to CSV
function exportToCSV(tableId, filename) {
  var table = document.getElementById(tableId);
  if (!table) return;

  var csv = [];
  var rows = table.querySelectorAll("tr");

  for (var i = 0; i < rows.length; i++) {
    var row = [],
      cols = rows[i].querySelectorAll("td, th");

    for (var j = 0; j < cols.length; j++) {
      row.push(cols[j].innerText);
    }

    csv.push(row.join(","));
  }

  var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
  var downloadLink = document.createElement("a");
  downloadLink.download = filename;
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
}
