document.addEventListener("DOMContentLoaded", () => {
  const trigger = document.getElementById("account-trigger");
  const dropdown = document.getElementById("account-dropdown");
  if (!trigger || !dropdown) return;

  const closeDropdown = () => {
    dropdown.hidden = true;
    trigger.setAttribute("aria-expanded", "false");
  };

  trigger.addEventListener("click", (event) => {
    event.stopPropagation();
    const isOpen = !dropdown.hidden;
    dropdown.hidden = isOpen;
    trigger.setAttribute("aria-expanded", isOpen ? "false" : "true");
  });

  const profileState = {
    name:
      dropdown.querySelector(".account-dropdown__head strong")?.textContent?.trim() ||
      (trigger.querySelector("span")?.textContent || "Admin").replace(/^Hello,\s*/i, "").trim() ||
      "Admin",
    email: "",
    phone: "",
    role: "Admin",
  };

  const profileModal = document.createElement("div");
  profileModal.className = "profile-modal";
  profileModal.hidden = true;
  profileModal.innerHTML = `
    <div class="profile-modal__backdrop" data-close="1"></div>
    <div class="profile-modal__panel" role="dialog" aria-modal="true" aria-labelledby="profile-modal-title">
      <button type="button" class="profile-modal__close" aria-label="Close profile modal">&times;</button>
      <div class="profile-modal__head">
        <div class="profile-modal__avatar" id="profile-avatar-label">A</div>
        <div>
          <h3 id="profile-modal-title"></h3>
          <p id="profile-role"></p>
        </div>
      </div>
      <div class="profile-modal__item">
        <span>Email</span>
        <strong id="profile-email"></strong>
      </div>
      <div class="profile-modal__item">
        <span>Phone</span>
        <strong id="profile-phone"></strong>
      </div>
      <div class="profile-modal__item">
        <span>Role</span>
        <strong id="profile-role-value"></strong>
      </div>
      <div class="profile-modal__actions">
        <button type="button" class="profile-btn profile-btn--ghost" data-close="1">Close</button>
        <button type="button" class="profile-btn profile-btn--primary" data-open-edit="1">Edit Profile</button>
      </div>
    </div>
  `;
  document.body.appendChild(profileModal);

  const editModal = document.createElement("div");
  editModal.className = "profile-edit-modal";
  editModal.hidden = true;
  editModal.innerHTML = `
    <div class="profile-modal__backdrop" data-close-edit="1"></div>
    <div class="profile-modal__panel profile-modal__panel--edit" role="dialog" aria-modal="true" aria-labelledby="profile-edit-title">
      <button type="button" class="profile-modal__close" aria-label="Close edit profile modal">&times;</button>
      <div class="profile-modal__head">
        <div class="profile-modal__avatar" id="profile-edit-avatar">A</div>
        <div>
          <h3 id="profile-edit-title">Edit Profile</h3>
          <p id="profile-edit-role"></p>
        </div>
      </div>
      <form class="profile-edit-form" id="profile-edit-form" novalidate>
        <label class="profile-edit-field">
          <span>Name</span>
          <input type="text" name="name" id="profile-edit-name" required maxlength="120">
        </label>
        <label class="profile-edit-field">
          <span>Email</span>
          <input type="email" name="email" id="profile-edit-email" maxlength="190">
        </label>
        <label class="profile-edit-field">
          <span>Phone</span>
          <input type="text" name="phone" id="profile-edit-phone" maxlength="40">
        </label>
        <label class="profile-edit-field">
          <span>Role</span>
          <input type="text" id="profile-edit-role-value" disabled>
        </label>
        <div class="profile-modal__actions">
          <button type="button" class="profile-btn profile-btn--ghost" data-close-edit="1">Close</button>
          <button type="submit" class="profile-btn profile-btn--primary">Save Changes</button>
        </div>
      </form>
    </div>
  `;
  document.body.appendChild(editModal);

  const profileNameEl = profileModal.querySelector("#profile-modal-title");
  const profileAvatarEl = profileModal.querySelector("#profile-avatar-label");
  const profileRoleEl = profileModal.querySelector("#profile-role");
  const profileRoleValueEl = profileModal.querySelector("#profile-role-value");
  const profileEmailEl = profileModal.querySelector("#profile-email");
  const profilePhoneEl = profileModal.querySelector("#profile-phone");

  const editAvatarEl = editModal.querySelector("#profile-edit-avatar");
  const editRoleEl = editModal.querySelector("#profile-edit-role");
  const editRoleValueEl = editModal.querySelector("#profile-edit-role-value");
  const editNameInput = editModal.querySelector("#profile-edit-name");
  const editEmailInput = editModal.querySelector("#profile-edit-email");
  const editPhoneInput = editModal.querySelector("#profile-edit-phone");
  const editForm = editModal.querySelector("#profile-edit-form");

  const setBodyLock = () => {
    const open = !profileModal.hidden || !editModal.hidden;
    document.body.classList.toggle("profile-modal-open", open);
  };

  const renderProfile = () => {
    const avatar = (profileState.name || "A").trim().charAt(0).toUpperCase() || "A";
    if (profileNameEl) profileNameEl.textContent = profileState.name || "Admin";
    if (profileAvatarEl) profileAvatarEl.textContent = avatar;
    if (profileRoleEl) profileRoleEl.textContent = profileState.role || "Admin";
    if (profileRoleValueEl) profileRoleValueEl.textContent = profileState.role || "Admin";
    if (profileEmailEl) profileEmailEl.textContent = profileState.email || "N/A";
    if (profilePhoneEl) profilePhoneEl.textContent = profileState.phone || "N/A";
    if (editAvatarEl) editAvatarEl.textContent = avatar;
    if (editRoleEl) editRoleEl.textContent = profileState.role || "Admin";
    if (editRoleValueEl) editRoleValueEl.value = profileState.role || "Admin";
    if (editNameInput) editNameInput.value = profileState.name || "";
    if (editEmailInput) editEmailInput.value = profileState.email || "";
    if (editPhoneInput) editPhoneInput.value = profileState.phone || "";

    const greetingEl = trigger.querySelector("span");
    if (greetingEl) greetingEl.textContent = `Hello, ${profileState.name || "Admin"}`;
    const headStrong = dropdown.querySelector(".account-dropdown__head strong");
    if (headStrong) headStrong.textContent = profileState.name || "Admin";
  };

  const loadProfile = async () => {
    try {
      const response = await fetch("profile_get.php", {
        method: "GET",
        cache: "no-store",
      });
      const data = await response.json();
      if (!response.ok || !data?.ok) return;
      profileState.name = data.profile?.name || profileState.name;
      profileState.email = data.profile?.email || "";
      profileState.phone = data.profile?.phone || "";
      profileState.role = data.profile?.role || "Admin";
      renderProfile();
    } catch {
      // keep current UI state if profile API fails
    }
  };

  const openProfileModal = async () => {
    await loadProfile();
    profileModal.hidden = false;
    setBodyLock();
  };

  const closeProfileModal = () => {
    profileModal.hidden = true;
    setBodyLock();
  };

  const openEditModal = () => {
    renderProfile();
    editModal.hidden = false;
    setBodyLock();
  };

  const closeEditModal = () => {
    editModal.hidden = true;
    setBodyLock();
  };

  profileModal.addEventListener("click", (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;

    if (target.closest("[data-close='1'], .profile-modal__close")) {
      closeProfileModal();
      return;
    }

    if (target.closest("[data-open-edit='1']")) {
      openEditModal();
    }
  });

  editModal.addEventListener("click", (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    if (target.closest("[data-close-edit='1'], .profile-modal__close")) {
      closeEditModal();
    }
  });

  editForm?.addEventListener("submit", async (event) => {
    event.preventDefault();
    const payload = new FormData(editForm);

    try {
      const response = await fetch("profile_update.php", {
        method: "POST",
        body: payload,
      });
      const data = await response.json();
      if (!response.ok || !data?.ok) {
        window.showAppNotice?.(data?.message || "Failed to save profile.", "error");
        return;
      }

      profileState.name = data.profile?.name || profileState.name;
      profileState.email = data.profile?.email || "";
      profileState.phone = data.profile?.phone || "";
      profileState.role = data.profile?.role || profileState.role;
      renderProfile();
      closeEditModal();
      window.showAppNotice?.(data.message || "Profile updated successfully.", "success");
    } catch {
      window.showAppNotice?.("Unable to save profile right now.", "error");
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      if (!editModal.hidden) closeEditModal();
      if (!profileModal.hidden) closeProfileModal();
    }
  });

  dropdown.addEventListener("click", (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;
    const clickedLink = target.closest("a.js-my-profile-link");
    if (!clickedLink) return;

    event.preventDefault();
    event.stopPropagation();
    closeDropdown();
    openProfileModal();
  });

  document.addEventListener("click", closeDropdown);
  renderProfile();
  loadProfile();
});
