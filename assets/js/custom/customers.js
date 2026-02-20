document.addEventListener("DOMContentLoaded", () => {
  const fab = document.getElementById("customers-fab");
  const addModal = document.getElementById("addCustomerModal");
  const removeModal = document.getElementById("removeCustomerModal");
  const closeAdd = document.getElementById("closeAddCustomer");
  const cancelAdd = document.getElementById("cancelAddCustomer");
  const closeRemove = document.getElementById("closeRemoveCustomer");
  const cancelRemove = document.getElementById("cancelRemoveCustomer");
  const confirmRemove = document.getElementById("confirmRemoveCustomer");
  const removeName = document.getElementById("removeCustomerName");
  const removeEmail = document.getElementById("removeCustomerEmail");
  const form = document.getElementById("customer-form");
  const formTitle = document.getElementById("customer-form-title");
  const submitBtn = document.getElementById("customer-submit-btn");
  const customerIdInput = document.getElementById("customer-id");
  const customerNameInput = document.getElementById("customer-name");
  const customerEmailInput = document.getElementById("customer-email");
  const customerPhoneInput = document.getElementById("customer-phone");
  const customerAddressInput = document.getElementById("customer-address");
  const customerOrdersInput = document.getElementById("customer-orders");

  let pendingDeleteId = "";
  let pendingDeleteRow = null;

  const openAdd = () => addModal?.showModal();
  const closeAddModal = () => addModal?.close();
  const openRemove = () => removeModal?.showModal();
  const closeRemoveModal = () => removeModal?.close();

  const parseJsonResponse = async (res) => {
    const raw = await res.text();
    try {
      return JSON.parse(raw);
    } catch {
      throw new Error(raw.slice(0, 180) || "Invalid JSON response");
    }
  };

  const resetToAddMode = () => {
    if (formTitle) formTitle.textContent = "Add New Customer";
    if (submitBtn) submitBtn.textContent = "Add Customer";
    if (customerIdInput) customerIdInput.value = "";
    if (form) form.reset();
    if (customerOrdersInput) customerOrdersInput.value = "0";
  };

  fab?.addEventListener("click", () => {
    resetToAddMode();
    openAdd();
  });
  closeAdd?.addEventListener("click", closeAddModal);
  cancelAdd?.addEventListener("click", closeAddModal);

  document.addEventListener("click", (event) => {
    const editBtn = event.target.closest(".js-edit-customer");
    if (editBtn) {
      if (formTitle) formTitle.textContent = "Edit Customer";
      if (submitBtn) submitBtn.textContent = "Save Changes";
      if (customerIdInput) customerIdInput.value = editBtn.dataset.id || "";
      if (customerNameInput) customerNameInput.value = editBtn.dataset.name || "";
      if (customerEmailInput) customerEmailInput.value = editBtn.dataset.email || "";
      if (customerPhoneInput) customerPhoneInput.value = editBtn.dataset.phone || "";
      if (customerAddressInput) customerAddressInput.value = editBtn.dataset.address || "";
      if (customerOrdersInput) customerOrdersInput.value = editBtn.dataset.orders || "0";
      openAdd();
      return;
    }

    const deleteBtn = event.target.closest(".js-delete-customer");
    if (deleteBtn) {
      pendingDeleteId = deleteBtn.dataset.id || "";
      pendingDeleteRow = deleteBtn.closest("tr");
      if (removeName) removeName.textContent = deleteBtn.dataset.name || "Selected customer";
      if (removeEmail) removeEmail.textContent = deleteBtn.dataset.email || "";
      openRemove();
    }
  });

  closeRemove?.addEventListener("click", closeRemoveModal);
  cancelRemove?.addEventListener("click", closeRemoveModal);
  confirmRemove?.addEventListener("click", async () => {
    if (!pendingDeleteId) {
      closeRemoveModal();
      return;
    }

    const fd = new FormData();
    fd.append("id", pendingDeleteId);

    const res = await fetch("customers_delete.php", { method: "POST", body: fd });
    const data = await parseJsonResponse(res);

    if (!data.ok) {
      window.showAppNotice?.(data.message || "Delete failed", "error");
      return;
    }

    if (pendingDeleteRow) {
      pendingDeleteRow.remove();
    } else {
      window.location.reload();
    }

    pendingDeleteId = "";
    pendingDeleteRow = null;
    closeRemoveModal();
  });

  form?.addEventListener("submit", async (event) => {
    const isEditMode = !!(customerIdInput?.value || "").trim();
    if (!isEditMode) {
      // Keep current non-API add flow unchanged.
      return;
    }

    event.preventDefault();
    const fd = new FormData(form);
    const res = await fetch("customers_update.php", { method: "POST", body: fd });
    const data = await parseJsonResponse(res);

    if (!data.ok) {
      window.showAppNotice?.(data.message || "Update failed", "error");
      return;
    }

    window.location.reload();
  });

  // Chart: spending distribution
  const spendingCtx = document.getElementById("spendingChart");
  const legend = document.getElementById("spendingLegend");
  const spendingData = window.customerSpendingData || {
    labels: ["$0-$20", "$20-$50", "$50-$100", "No Spending", "Over $100"],
    counts: [0, 0, 0, 0, 0],
    colors: ["#6b35d9", "#e23c7e", "#f0b22f", "#2b90e0", "#28a56b"],
  };
  const totalCustomersFromBuckets = spendingData.counts.reduce((sum, value) => sum + Number(value || 0), 0);
  const spendingPercents = spendingData.counts.map((value) =>
    totalCustomersFromBuckets > 0 ? (Number(value || 0) / totalCustomersFromBuckets) * 100 : 0
  );

  const syncInsightsHeight = () => {
    const leftCard = document.querySelector(".customers-insights .insight-card.chart-card");
    const rightStack = document.querySelector(".customers-insights .insight-metrics");
    if (!leftCard || !rightStack) return;

    if (window.innerWidth <= 980) {
      rightStack.style.height = "";
      return;
    }

    rightStack.style.height = `${leftCard.offsetHeight}px`;
  };

  if (spendingCtx && Chart) {
    new Chart(spendingCtx, {
      type: "doughnut",
      data: {
        labels: spendingData.labels,
        datasets: [
          {
            data: spendingPercents,
            backgroundColor: spendingData.colors,
            borderWidth: 0,
          },
        ],
      },
      options: {
        cutout: "68%",
        plugins: { legend: { display: false } },
      },
    });
  }

  if (legend) {
    legend.innerHTML = spendingData.labels
      .map(
        (label, idx) => `
        <div class="legend-row">
          <span class="legend-label">
            <span class="legend-dot" style="background:${spendingData.colors[idx]}"></span>
            ${label}
          </span>
          <span class="legend-value">${spendingPercents[idx].toFixed(1)}%</span>
        </div>`
      )
      .join("");
  }

  // Keep right metrics column exactly equal to left chart card on desktop.
  syncInsightsHeight();
  window.addEventListener("resize", syncInsightsHeight);
  window.addEventListener("load", syncInsightsHeight);
  setTimeout(syncInsightsHeight, 0);
});
