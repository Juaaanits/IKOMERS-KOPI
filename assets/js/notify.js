(() => {
  const ensureRoot = () => {
    let root = document.getElementById("app-notice-root");
    if (!root) {
      root = document.createElement("div");
      root.id = "app-notice-root";
      root.className = "app-notice-root";
      document.body.appendChild(root);
    }
    return root;
  };

  const showAppNotice = (message, type = "error", title = null) => {
    const root = ensureRoot();
    const card = document.createElement("div");
    card.className = `app-notice app-notice--${type}`;

    const heading =
      title ||
      (type === "success" ? "Success" : type === "info" ? "Info" : "Error");

    card.innerHTML = `
      <div class="app-notice__icon">!</div>
      <div class="app-notice__content">
        <h4>${heading}</h4>
        <p>${message || "Something went wrong."}</p>
        <button type="button" class="app-notice__btn">Close</button>
      </div>
    `;

    const close = () => {
      card.classList.add("is-closing");
      window.setTimeout(() => card.remove(), 220);
    };

    card.querySelector(".app-notice__btn")?.addEventListener("click", close);
    root.appendChild(card);

    window.setTimeout(close, 4500);
  };

  const showAppConfirm = (message, title = "Confirm") =>
    new Promise((resolve) => {
      const root = ensureRoot();
      const card = document.createElement("div");
      card.className = "app-notice app-notice--error app-notice--confirm";
      card.innerHTML = `
        <div class="app-notice__icon">!</div>
        <div class="app-notice__content">
          <h4>${title}</h4>
          <p>${message || "Are you sure?"}</p>
          <div class="app-notice__actions">
            <button type="button" class="app-notice__btn app-notice__btn--cancel">Cancel</button>
            <button type="button" class="app-notice__btn app-notice__btn--ok">Confirm</button>
          </div>
        </div>
      `;

      const close = (result) => {
        card.classList.add("is-closing");
        window.setTimeout(() => {
          card.remove();
          resolve(result);
        }, 220);
      };

      card.querySelector(".app-notice__btn--cancel")?.addEventListener("click", () => close(false));
      card.querySelector(".app-notice__btn--ok")?.addEventListener("click", () => close(true));

      root.appendChild(card);
    });

  window.showAppNotice = showAppNotice;
  window.showAppConfirm = showAppConfirm;
})();
