document.addEventListener("DOMContentLoaded", () => {
  const fab = document.getElementById("orders-fab");
  const orderModal = document.getElementById("orderModal");
  const closeOrderModal = document.getElementById("closeOrderModal");
  const cancelOrderModal = document.getElementById("cancelOrderModal");

  const selectCustomerModal = document.getElementById("selectCustomerModal");
  const openSelectCustomer = document.getElementById("openSelectCustomer");
  const closeSelectCustomer = document.getElementById("closeSelectCustomer");
  const cancelSelectCustomer = document.getElementById("cancelSelectCustomer");

  const statusModal = document.getElementById("statusModal");
  const closeStatusModal = document.getElementById("closeStatusModal");
  const cancelStatusModal = document.getElementById("cancelStatusModal");
  const saveStatusModal = document.getElementById("saveStatusModal");
  const statusOrderLabel = document.getElementById("statusOrderLabel");
  const statusSelect = document.getElementById("statusSelect");

  const openDialog = (dlg) => dlg?.showModal();
  const closeDialog = (dlg) => dlg?.close();

  fab?.addEventListener("click", () => openDialog(orderModal));
  closeOrderModal?.addEventListener("click", () => closeDialog(orderModal));
  cancelOrderModal?.addEventListener("click", () => closeDialog(orderModal));

  openSelectCustomer?.addEventListener("click", () => openDialog(selectCustomerModal));
  closeSelectCustomer?.addEventListener("click", () => closeDialog(selectCustomerModal));
  cancelSelectCustomer?.addEventListener("click", () => closeDialog(selectCustomerModal));

  document.querySelectorAll(".status-edit-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const orderId = btn.dataset.order || "";
      const currentStatus = btn.dataset.status || "Pending";
      if (statusOrderLabel) statusOrderLabel.textContent = `Order: #${orderId}`;
      if (statusSelect) statusSelect.value = currentStatus;
      openDialog(statusModal);
    });
  });

  closeStatusModal?.addEventListener("click", () => closeDialog(statusModal));
  cancelStatusModal?.addEventListener("click", () => closeDialog(statusModal));
  saveStatusModal?.addEventListener("click", () => closeDialog(statusModal));

  // Order status donut
  const statusCtx = document.getElementById("orderStatusChart");
  const legend = document.getElementById("orderStatusLegend");
  const statusData = {
    labels: ["Pending", "Processing", "Completed", "Cancelled"],
    values: [54, 30, 42, 5],
    colors: ["#6b35d9", "#17b7b2", "#f16521", "#c23dc4"],
  };

  if (statusCtx && Chart) {
    new Chart(statusCtx, {
      type: "doughnut",
      data: {
        labels: statusData.labels,
        datasets: [
          {
            data: statusData.values,
            backgroundColor: statusData.colors,
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
    const total = statusData.values.reduce((a, b) => a + b, 0);
    legend.innerHTML = statusData.labels
      .map((label, idx) => {
        const value = statusData.values[idx];
        const pct = ((value / total) * 100).toFixed(1);
        return `
        <div class="legend-row">
          <span class="legend-label">
            <span class="legend-dot" style="background:${statusData.colors[idx]}"></span>
            ${label}
          </span>
          <span class="legend-value">${pct}%</span>
        </div>`;
      })
      .join("");
  }
});
