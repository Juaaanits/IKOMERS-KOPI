document.addEventListener("DOMContentLoaded", () => {
  const fab = document.getElementById("users-fab");
  const userModal = document.getElementById("userModal");
  const closeUserModal = document.getElementById("closeUserModal");
  const cancelUserModal = document.getElementById("cancelUserModal");
  const userForm = document.getElementById("user-form");
  const userIdInput = document.getElementById("user-id");
  const userNameInput = document.getElementById("user-fullname");
  const userEmailInput = document.getElementById("user-email");
  const userPasswordInput = document.getElementById("user-password");
  const userPhoneInput = document.getElementById("user-phone");
  const userRoleInput = document.getElementById("user-role");
  const userFormTitle = document.getElementById("user-form-title");
  const userSubmitBtn = document.getElementById("user-submit-btn");

  const openDialog = (dlg) => dlg?.showModal();
  const closeDialog = (dlg) => dlg?.close();
  const parseJsonResponse = async (res) => {
    const raw = await res.text();
    try {
      return JSON.parse(raw);
    } catch {
      throw new Error(raw.slice(0, 180) || "Invalid JSON response");
    }
  };
  const setAddMode = () => {
    if (userFormTitle) userFormTitle.textContent = "Add New User";
    if (userSubmitBtn) userSubmitBtn.textContent = "Add User";
    if (userIdInput) userIdInput.value = "";
    if (userForm) userForm.reset();
    if (userPasswordInput) {
      userPasswordInput.required = true;
      userPasswordInput.placeholder = "Password";
      userPasswordInput.value = "";
    }
  };

  fab?.addEventListener("click", () => {
    setAddMode();
    openDialog(userModal);
  });
  closeUserModal?.addEventListener("click", () => closeDialog(userModal));
  cancelUserModal?.addEventListener("click", () => closeDialog(userModal));

  document.addEventListener("click", (event) => {
    const toggleBtn = event.target.closest(".js-toggle-user-password");
    if (toggleBtn) {
      // Passwords are hashed in DB and cannot be revealed safely.
      window.showAppNotice?.("Password is stored securely and cannot be shown.", "info");
      return;
    }

    const editBtn = event.target.closest(".js-edit-user");
    if (editBtn) {
      if (userFormTitle) userFormTitle.textContent = "Edit User";
      if (userSubmitBtn) userSubmitBtn.textContent = "Save Changes";
      if (userIdInput) userIdInput.value = editBtn.dataset.id || "";
      if (userNameInput) userNameInput.value = editBtn.dataset.name || "";
      if (userEmailInput) userEmailInput.value = editBtn.dataset.email || "";
      if (userPasswordInput) {
        userPasswordInput.value = "";
        userPasswordInput.required = false;
        userPasswordInput.placeholder = "Leave blank to keep password";
      }
      if (userPhoneInput) userPhoneInput.value = editBtn.dataset.phone || "";
      if (userRoleInput) userRoleInput.value = editBtn.dataset.role || "User";
      openDialog(userModal);
      return;
    }

    const deleteBtn = event.target.closest(".js-delete-user");
    if (deleteBtn) {
      const userId = Number.parseInt(deleteBtn.dataset.id || "0", 10);
      if (!userId) return;
      const confirmed = window.confirm("Delete this user?");
      if (!confirmed) return;

      const fd = new FormData();
      fd.append("id", String(userId));

      fetch("users_delete.php", { method: "POST", body: fd })
        .then(parseJsonResponse)
        .then((data) => {
          if (!data.ok) {
            window.showAppNotice?.(data.message || "Delete failed", "error");
            return;
          }
          window.location.reload();
        })
        .catch((error) => {
          window.showAppNotice?.(String(error.message || error), "error");
        });
    }
  });

  userForm?.addEventListener("submit", async (event) => {
    const isEditMode = !!(userIdInput?.value || "").trim();
    if (!isEditMode) {
      return;
    }

    event.preventDefault();
    const fd = new FormData(userForm);
    const res = await fetch("users_update.php", { method: "POST", body: fd });
    const data = await parseJsonResponse(res);
    if (!data.ok) {
      window.showAppNotice?.(data.message || "Update failed", "error");
      return;
    }
    window.location.reload();
  });
});
