(() => {
  const LAYOUT_SELECTOR = ".dashboard-layout";
  const STORAGE_KEY = "brewbean.sidebarCollapsed";
  const DESKTOP_QUERY = window.matchMedia("(min-width: 1081px)");

  const setCollapsed = (layout, button, collapsed) => {
    layout.classList.toggle("sidebar-collapsed", collapsed);
    button.setAttribute("aria-expanded", String(!collapsed));
    button.setAttribute(
      "aria-label",
      collapsed ? "Expand sidebar" : "Collapse sidebar"
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

    const storedCollapsed = localStorage.getItem(STORAGE_KEY) === "1";
    if (DESKTOP_QUERY.matches && storedCollapsed) {
      setCollapsed(layout, toggleButton, true);
    } else {
      setCollapsed(layout, toggleButton, false);
    }

    toggleButton.addEventListener("click", () => {
      const nextCollapsed = !layout.classList.contains("sidebar-collapsed");
      setCollapsed(layout, toggleButton, nextCollapsed);
      localStorage.setItem(STORAGE_KEY, nextCollapsed ? "1" : "0");
    });

    DESKTOP_QUERY.addEventListener("change", (event) => {
      if (!event.matches) {
        layout.classList.remove("sidebar-collapsed");
      } else if (localStorage.getItem(STORAGE_KEY) === "1") {
        layout.classList.add("sidebar-collapsed");
      }
    });
  });
})();
