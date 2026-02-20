document.addEventListener("DOMContentLoaded", () => {
  const fab = document.getElementById("menu-fab");
  const modal = document.getElementById("menu-modal");
  const closeModal = document.getElementById("close-menu-modal");
  const cancelBtn = document.getElementById("cancel-menu-item");
  const browseLink = document.getElementById("browse-files");
  const dropzone = document.getElementById("dropzone");
  const fileInput = document.getElementById("image-input");
  const form = document.getElementById("menu-form");
  const fileNameLabel = document.getElementById("selected-image-name");
  const modalTitle = document.getElementById("menu-modal-title");
  const submitBtn = document.getElementById("menu-submit-btn");
  const itemIdInput = document.getElementById("menu-item-id");
  const nameInput = form?.querySelector('input[name="name"]');
  const priceInput = form?.querySelector('input[name="price"]');
  const descInput = form?.querySelector('textarea[name="description"]');

  const parseJsonResponse = async (res) => {
    const raw = await res.text();
    try {
      return JSON.parse(raw);
    } catch {
      throw new Error(raw.slice(0, 180) || "Server returned invalid JSON");
    }
  };

  const resetToAddMode = () => {
    if (itemIdInput) itemIdInput.value = "";
    if (modalTitle) modalTitle.textContent = "Add New Menu Item";
    if (submitBtn) submitBtn.textContent = "Add Item";
    if (fileInput) fileInput.required = true;
  };

fab?.addEventListener("click", () => {
  resetToAddMode();
  openModal();
});

document.addEventListener("click", async (event) => {
  const deleteBtn = event.target.closest(".js-delete-item");
  if (deleteBtn) {
    const id = deleteBtn.dataset.id;
    if (!id) return;
    if (!confirm("Delete this menu item?")) return;

    const fd = new FormData();
    fd.append("id", id);

    const res = await fetch("delete.php", { method: "POST", body: fd });
    const data = await parseJsonResponse(res);

    if (!data.ok) {
      alert(data.message || "Delete failed.");
      return;
    }

    const card = deleteBtn.closest(".menu-card");
    if (card) card.remove();
    return;
  }

  const editBtn = event.target.closest(".js-edit-item");
    if (editBtn) {
      resetToAddMode();
      if (itemIdInput) itemIdInput.value = editBtn.dataset.id || "";
      if (nameInput) nameInput.value = editBtn.dataset.name || "";
      if (priceInput) priceInput.value = editBtn.dataset.price || "";
      if (descInput) descInput.value = editBtn.dataset.description || "";
      if (modalTitle) modalTitle.textContent = "Edit Menu Item";
      if (submitBtn) submitBtn.textContent = "Save Changes";
      if (fileInput) fileInput.required = false;
      openModal();
    }
  });

form?.addEventListener("submit", async (event) => {
  const isEdit = !!itemIdInput?.value;
  if (!isEdit) return; // let your current add flow submit normally

  event.preventDefault();
  const fd = new FormData(form);
  fd.append("id", itemIdInput.value);

  const res = await fetch("update.php", { method: "POST", body: fd });
  const data = await parseJsonResponse(res);

  if (!data.ok) {
    alert(data.message || "Update failed.");
    return;
  }

  // easiest refresh to reflect card changes
  window.location.reload();
});

  const openModal = () => modal?.showModal();
  const closeModalDialog = () => modal?.close();

  fab?.addEventListener("click", openModal);
  closeModal?.addEventListener("click", closeModalDialog);
  cancelBtn?.addEventListener("click", closeModalDialog);

  form?.addEventListener("submit", () => {
    if (modal) {
      modal.setAttribute("aria-busy", "true");
    }
  });

  browseLink?.addEventListener("click", (event) => {
    event.preventDefault();
    fileInput?.click();
  });

  const updateFileName = (files) => {
    if (!fileNameLabel) return;
    fileNameLabel.textContent = files && files.length
      ? `Selected: ${files[0].name}`
      : "No image selected yet";
  };

  fileInput?.addEventListener("change", (event) => {
    const target = event.target;
    updateFileName(target?.files);
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
        updateFileName(files);
      }
    });
  }
});
