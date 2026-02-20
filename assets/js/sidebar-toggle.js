(() => {
  const LAYOUT_SELECTOR = ".dashboard-layout";
  const DESKTOP_STORAGE_KEY = "brewbean.sidebarCollapsedDesktop";
  const MOBILE_STORAGE_KEY = "brewbean.sidebarCollapsedMobile";
  const DESKTOP_QUERY = window.matchMedia("(min-width: 1081px)");

  const setDesktopCollapsed = (layout, button, collapsed) => {
    layout.classList.toggle("sidebar-collapsed", collapsed);
    button.setAttribute("aria-expanded", String(!collapsed));
    button.setAttribute(
      "aria-label",
      collapsed ? "Expand sidebar" : "Collapse sidebar"
    );
  };

  const setMobileCollapsed = (layout, button, collapsed) => {
    layout.classList.toggle("sidebar-collapsed", collapsed);
    layout.classList.toggle("sidebar-mobile-collapsed", collapsed);
    button.setAttribute("aria-expanded", String(!collapsed));
    button.setAttribute(
      "aria-label",
      collapsed ? "Open navigation menu" : "Close navigation menu"
    );
  };

  const buildToggleButton = () => {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "sidebar-toggle";

    const lines = document.createElement("span");
    lines.className = "sidebar-toggle__lines";
    lines.setAttribute("aria-hidden", "true");

    for (let i = 0; i < 3; i += 1) {
      lines.appendChild(document.createElement("span"));
    }

    button.appendChild(lines);
    return button;
  };

  document.addEventListener("DOMContentLoaded", () => {
    const layout = document.querySelector(LAYOUT_SELECTOR);
    if (!layout) return;

    const brand = layout.querySelector(".sidebar .brand");
    if (!brand || brand.querySelector(".sidebar-toggle")) return;

    const toggleButton = buildToggleButton();
    brand.appendChild(toggleButton);

    const storedDesktopCollapsed =
      localStorage.getItem(DESKTOP_STORAGE_KEY) === "1";
    const mobileStorageValue = localStorage.getItem(MOBILE_STORAGE_KEY);
    const storedMobileCollapsed =
      mobileStorageValue === null ? true : mobileStorageValue === "1";

    if (DESKTOP_QUERY.matches) {
      setDesktopCollapsed(layout, toggleButton, storedDesktopCollapsed);
      layout.classList.remove("sidebar-mobile-collapsed");
    } else {
      layout.classList.remove("sidebar-collapsed");
      setMobileCollapsed(layout, toggleButton, storedMobileCollapsed);
    }

    toggleButton.addEventListener("click", () => {
      if (DESKTOP_QUERY.matches) {
        const nextCollapsed = !layout.classList.contains("sidebar-collapsed");
        setDesktopCollapsed(layout, toggleButton, nextCollapsed);
        localStorage.setItem(DESKTOP_STORAGE_KEY, nextCollapsed ? "1" : "0");
      } else {
        const nextCollapsed = !layout.classList.contains(
          "sidebar-mobile-collapsed"
        );
        setMobileCollapsed(layout, toggleButton, nextCollapsed);
        localStorage.setItem(MOBILE_STORAGE_KEY, nextCollapsed ? "1" : "0");
      }
    });

    const navLinks = layout.querySelectorAll(".sidebar-nav a[href]");
    navLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        if (DESKTOP_QUERY.matches) return;

        const targetHref = link.getAttribute("href");
        if (!targetHref || targetHref === "#") return;

        event.preventDefault();
        setMobileCollapsed(layout, toggleButton, true);
        localStorage.setItem(MOBILE_STORAGE_KEY, "1");

        window.requestAnimationFrame(() => {
          window.location.assign(targetHref);
        });
      });
    });

    DESKTOP_QUERY.addEventListener("change", (event) => {
      if (event.matches) {
        layout.classList.remove("sidebar-mobile-collapsed");
        const desktopCollapsed =
          localStorage.getItem(DESKTOP_STORAGE_KEY) === "1";
        setDesktopCollapsed(layout, toggleButton, desktopCollapsed);
      } else {
        layout.classList.remove("sidebar-collapsed");
        const mobileStorage = localStorage.getItem(MOBILE_STORAGE_KEY);
        const mobileCollapsed =
          mobileStorage === null ? true : mobileStorage === "1";
        setMobileCollapsed(layout, toggleButton, mobileCollapsed);
      }
    });
  });
})();
