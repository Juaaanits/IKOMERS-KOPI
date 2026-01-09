document.addEventListener("DOMContentLoaded", () => {
  const fab = document.getElementById("users-fab");
  const userModal = document.getElementById("userModal");
  const closeUserModal = document.getElementById("closeUserModal");
  const cancelUserModal = document.getElementById("cancelUserModal");

  const openDialog = (dlg) => dlg?.showModal();
  const closeDialog = (dlg) => dlg?.close();

  fab?.addEventListener("click", () => openDialog(userModal));
  closeUserModal?.addEventListener("click", () => closeDialog(userModal));
  cancelUserModal?.addEventListener("click", () => closeDialog(userModal));

  // Placeholder password toggle could be wired here; currently non-functional per mock.
});
