document.addEventListener("DOMContentLoaded", () => {
  const fab = document.getElementById("menu-fab");
  const modal = document.getElementById("menu-modal");
  const closeModal = document.getElementById("close-menu-modal");
  const cancelBtn = document.getElementById("cancel-menu-item");
  const browseLink = document.getElementById("browse-files");
  const dropzone = document.getElementById("dropzone");
  const fileInput = document.getElementById("image-input");
  const form = document.getElementById("menu-form");

  const openModal = () => modal?.showModal();
  const closeModalDialog = () => modal?.close();

  fab?.addEventListener("click", openModal);
  closeModal?.addEventListener("click", closeModalDialog);
  cancelBtn?.addEventListener("click", closeModalDialog);

  // Prevent default submit for now; this is placeholder UI only.
  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    closeModalDialog();
  });

  browseLink?.addEventListener("click", (event) => {
    event.preventDefault();
    fileInput?.click();
  });

  if (dropzone) {
    ["dragenter", "dragover"].forEach((type) => {
      dropzone.addEventListener(type, (event) => {
        event.preventDefault();
        event.stopPropagation();
        dropzone.classList.add("dragover");
      });
    });

    ["dragleave", "drop"].forEach((type) => {
      dropzone.addEventListener(type, (event) => {
        event.preventDefault();
        event.stopPropagation();
        dropzone.classList.remove("dragover");
      });
    });

    dropzone.addEventListener("drop", (event) => {
      if (!fileInput) return;
      const { files } = event.dataTransfer || {};
      if (files && files.length) {
        fileInput.files = files;
      }
    });
  }
});
