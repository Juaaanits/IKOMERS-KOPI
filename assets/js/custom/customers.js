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

  const openAdd = () => addModal?.showModal();
  const closeAddModal = () => addModal?.close();
  const openRemove = () => removeModal?.showModal();
  const closeRemoveModal = () => removeModal?.close();

  fab?.addEventListener("click", openAdd);
  closeAdd?.addEventListener("click", closeAddModal);
  cancelAdd?.addEventListener("click", closeAddModal);

  document.querySelectorAll(".icon-btn--delete").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (removeName) removeName.textContent = btn.dataset.name || "Selected customer";
      if (removeEmail) removeEmail.textContent = btn.dataset.email || "";
      openRemove();
    });
  });

  closeRemove?.addEventListener("click", closeRemoveModal);
  cancelRemove?.addEventListener("click", closeRemoveModal);
  confirmRemove?.addEventListener("click", closeRemoveModal);

  // Chart: spending distribution
  const spendingCtx = document.getElementById("spendingChart");
  const legend = document.getElementById("spendingLegend");
  const spendingData = {
    labels: ["$0-$20", "$20-$50", "$50-$100", "No Spending", "Over $100"],
    values: [18.8, 31.2, 18.8, 12.5, 18.8],
    colors: ["#6b35d9", "#e23c7e", "#f0b22f", "#2b90e0", "#28a56b"],
  };

  if (spendingCtx && Chart) {
    new Chart(spendingCtx, {
      type: "doughnut",
      data: {
        labels: spendingData.labels,
        datasets: [
          {
            data: spendingData.values,
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
          <span class="legend-value">${spendingData.values[idx]}%</span>
        </div>`
      )
      .join("");
  }
});
