"use strict";

(function () {
  var sidebarStorageKey = "adminHMD.sidebarMini";
  var themeStorageKey = "adminHMD.colorTheme";
  var desktopMedia = "(min-width: 992px)";

  function onReady(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
      return;
    }

    callback();
  }

  function isDesktop() {
    return window.matchMedia(desktopMedia).matches;
  }

  function canUseStorage() {
    try {
      var testKey = sidebarStorageKey + ".test";
      window.localStorage.setItem(testKey, "1");
      window.localStorage.removeItem(testKey);
      return true;
    } catch (error) {
      return false;
    }
  }

  function getSavedMiniState(storageAvailable) {
    if (!storageAvailable) {
      return false;
    }

    return window.localStorage.getItem(sidebarStorageKey) === "true";
  }

  function saveMiniState(storageAvailable, isMini) {
    if (storageAvailable) {
      window.localStorage.setItem(sidebarStorageKey, String(isMini));
    }
  }

  function getPreferredTheme(storageAvailable) {
    var savedTheme = storageAvailable ? window.localStorage.getItem(themeStorageKey) : "";

    if (savedTheme === "dark" || savedTheme === "light") {
      return savedTheme;
    }

    if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
      return "dark";
    }

    return "light";
  }

  onReady(function () {
    var body = document.body;
    var sidebarToggle = document.querySelector("[data-sidebar-toggle]");
    var themeToggles = document.querySelectorAll("[data-theme-toggle]");
    var themeIcons = document.querySelectorAll("[data-theme-icon]");
    var closeButtons = document.querySelectorAll("[data-sidebar-close]");
    var sidebarLinks = document.querySelectorAll(".sidebar-nav .nav-link");
    var mediaQuery = window.matchMedia(desktopMedia);
    var storageAvailable = canUseStorage();

    function initModalTeleport() {
      document.addEventListener("show.bs.modal", function (event) {
        var modal = event.target;
        if (modal && modal.parentElement !== document.body) {
          document.body.appendChild(modal);
        }
      });
    }

    function setupPasswordToggles(container) {
      var inputs = (container || document).querySelectorAll('input[type="password"]');
      Array.prototype.forEach.call(inputs, function (input) {
        if (input.dataset.hasPasswordToggle) return;
        input.dataset.hasPasswordToggle = "true";

        var group = input.closest(".input-group");
        var toggleBtn = group ? group.querySelector(".btn-toggle-password, [data-toggle-password]") : null;

        if (!toggleBtn) {
          if (!group) {
            group = document.createElement("div");
            group.className = "input-group";
            input.parentNode.insertBefore(group, input);
            group.appendChild(input);
          }

          toggleBtn = document.createElement("button");
          toggleBtn.type = "button";
          toggleBtn.className = "btn btn-outline-secondary btn-toggle-password";
          toggleBtn.setAttribute("aria-label", "Afficher/Masquer le mot de passe");
          toggleBtn.setAttribute("title", "Afficher le mot de passe");
          toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
          group.appendChild(toggleBtn);
        }
      });
    }

    function initPasswordToggles() {
      setupPasswordToggles(document);

      document.addEventListener("shown.bs.modal", function (e) {
        setupPasswordToggles(e.target);
      });

      document.addEventListener("click", function (e) {
        var btn = e.target.closest(".btn-toggle-password, [data-toggle-password]");
        if (!btn) return;

        var group = btn.closest(".input-group") || btn.parentNode;
        if (!group) return;

        var input = group.querySelector("input");
        if (!input) return;

        e.preventDefault();

        var isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";

        var icon = btn.querySelector("i");
        if (icon) {
          if (isPassword) {
            icon.className = "bi bi-eye-slash";
            btn.setAttribute("title", "Masquer le mot de passe");
          } else {
            icon.className = "bi bi-eye";
            btn.setAttribute("title", "Afficher le mot de passe");
          }
        }
      });
    }

    function initValidation() {
      var forms = document.querySelectorAll(".needs-validation");

      Array.prototype.forEach.call(forms, function (form) {
        form.addEventListener("submit", function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }

          form.classList.add("was-validated");
        });
      });
    }

    function initTableSearch() {
      var searchInputs = document.querySelectorAll("[data-table-search]");

      Array.prototype.forEach.call(searchInputs, function (input) {
        var tableId = input.getAttribute("data-table-search");
        var table = document.getElementById(tableId);

        if (!table) {
          return;
        }

        input.addEventListener("input", function () {
          var query = input.value.trim().toLowerCase();
          var rows = table.querySelectorAll("tbody tr");

          Array.prototype.forEach.call(rows, function (row) {
            row.hidden = query !== "" && row.textContent.toLowerCase().indexOf(query) === -1;
          });
        });
      });
    }

    function updateThemeControls(theme) {
      var nextTheme = theme === "dark" ? "light" : "dark";
      var label = "Switch to " + nextTheme + " mode";
      var iconClass = theme === "dark" ? "bi bi-sun" : "bi bi-moon-stars";

      Array.prototype.forEach.call(themeToggles, function (button) {
        button.setAttribute("aria-label", label);
        button.setAttribute("title", label);
      });

      Array.prototype.forEach.call(themeIcons, function (icon) {
        icon.className = iconClass;
      });
    }

    function applyTheme(theme) {
      document.documentElement.setAttribute("data-theme", theme);
      document.documentElement.setAttribute("data-bs-theme", theme);

      if (storageAvailable) {
        window.localStorage.setItem(themeStorageKey, theme);
      }

      updateThemeControls(theme);
    }

    function initThemeToggle() {
      applyTheme(getPreferredTheme(storageAvailable));

      Array.prototype.forEach.call(themeToggles, function (button) {
        button.addEventListener("click", function () {
          var currentTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "dark" : "light";
          applyTheme(currentTheme === "dark" ? "light" : "dark");
        });
      });
    }

    initModalTeleport();
    initPasswordToggles();
    initValidation();
    initTableSearch();
    initThemeToggle();

    // Initialize user profile values in UI only if window.adminHMDUser is explicitly set.
    function initUserProfile() {
      if (!window.adminHMDUser) {
        return;
      }
      var user = window.adminHMDUser;

      var sidebarNameEl = document.querySelector(".sidebar-user strong");
      var sidebarWorkspaceEl = document.querySelector(".sidebar-user small");
      var sidebarAvatar = document.querySelector(".sidebar-user .avatar-img");
      var profileNameEls = document.querySelectorAll(".profile-name");
      var profileAvatarEls = document.querySelectorAll(".profile-button .avatar-img, .profile-button img");

      if (sidebarNameEl && user.name) sidebarNameEl.textContent = user.name;
      if (sidebarWorkspaceEl && user.workspace) sidebarWorkspaceEl.textContent = user.workspace;
      if (sidebarAvatar && user.avatar) { sidebarAvatar.src = user.avatar; sidebarAvatar.alt = user.name; }

      if (user.name) {
        Array.prototype.forEach.call(profileNameEls, function (el) { el.textContent = user.name; });
      }
      if (user.avatar) {
        Array.prototype.forEach.call(profileAvatarEls, function (img) { img.src = user.avatar; if (user.name) img.alt = user.name; });
      }
    }

    initUserProfile();

    if (!sidebarToggle) {
      return;
    }

    function setClass(element, className, enabled) {
      if (enabled) {
        element.classList.add(className);
      } else {
        element.classList.remove(className);
      }
    }

    function setToggleExpanded() {
      var expanded = isDesktop()
        ? !body.classList.contains("sidebar-mini")
        : body.classList.contains("sidebar-open");

      sidebarToggle.setAttribute("aria-expanded", String(expanded));
    }

    function closeMobileSidebar() {
      body.classList.remove("sidebar-open");
      setToggleExpanded();
    }

    function toggleSidebar() {
      if (isDesktop()) {
        body.classList.toggle("sidebar-mini");
        saveMiniState(storageAvailable, body.classList.contains("sidebar-mini"));
      } else {
        body.classList.toggle("sidebar-open");
      }

      setToggleExpanded();
    }

    function addCloseHandlers(items) {
      Array.prototype.forEach.call(items, function (item) {
        item.addEventListener("click", function () {
          if (!isDesktop()) {
            closeMobileSidebar();
          }
        });
      });
    }

    if (getSavedMiniState(storageAvailable) && isDesktop()) {
      body.classList.add("sidebar-mini");
    }

    sidebarToggle.addEventListener("click", toggleSidebar);
    addCloseHandlers(closeButtons);
    addCloseHandlers(sidebarLinks);
    setToggleExpanded();

    function handleBreakpointChange() {
      if (isDesktop()) {
        body.classList.remove("sidebar-open");
        setClass(body, "sidebar-mini", getSavedMiniState(storageAvailable));
      } else {
        body.classList.remove("sidebar-mini");
      }

      setToggleExpanded();
    }

    if (mediaQuery.addEventListener) {
      mediaQuery.addEventListener("change", handleBreakpointChange);
    } else if (mediaQuery.addListener) {
      mediaQuery.addListener(handleBreakpointChange);
    }
  });
})();
