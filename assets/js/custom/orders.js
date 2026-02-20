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
  const viewOrderModal = document.getElementById("viewOrderModal");
  const closeViewOrderModal = document.getElementById("closeViewOrderModal");
  const cancelViewOrderModal = document.getElementById("cancelViewOrderModal");
  const printViewOrderBtn = document.getElementById("printViewOrderBtn");
  const viewOrderTitle = document.getElementById("viewOrderTitle");
  const viewOrderDate = document.getElementById("viewOrderDate");
  const viewOrderItems = document.getElementById("viewOrderItems");
  const viewOrderSubtotal = document.getElementById("viewOrderSubtotal");
  const viewOrderTotal = document.getElementById("viewOrderTotal");

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

  document.querySelectorAll(".js-view-order").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id || "0";
      const date = btn.dataset.date || "-";
      const total = btn.dataset.total || "0.00";
      const itemsRaw = (btn.dataset.items || "").trim();

      if (viewOrderTitle) viewOrderTitle.textContent = `Order #${id}`;
      if (viewOrderDate) viewOrderDate.textContent = date;
      if (viewOrderTotal) viewOrderTotal.textContent = `$${total}`;

      if (viewOrderItems) {
        const itemRows = itemsRaw
          ? itemsRaw.split(",").map((item) => item.trim()).filter(Boolean)
          : [];
        let subtotal = 0;

        viewOrderItems.innerHTML = itemRows.length
          ? itemRows
              .map((item) => {
                const normalized = item.replace(/\s+/g, " ").trim();
                const match = normalized.match(/^(\d+)\s*x\s*(.+?)\s*\(\$?([\d.]+)\)$/i);

                const qty = match ? Number.parseInt(match[1], 10) : 1;
                const name = match ? match[2] : normalized;
                const price = match ? Number.parseFloat(match[3]) : Number.NaN;

                if (!Number.isNaN(price)) {
                  subtotal += qty * price;
                }

                const priceText = Number.isNaN(price) ? "-" : `$${price.toFixed(2)}`;
                return `
                <li class="order-item-row">
                  <div class="order-item-thumb" aria-hidden="true"><span>IMG</span></div>
                  <div class="order-item-meta">
                    <p class="order-item-name">${name}</p>
                    <p class="order-item-sub">Quantity: ${qty} <span>&#9679;</span> ${priceText}</p>
                  </div>
                </li>`;
              })
              .join("")
          : '<li class="order-item-row"><div class="order-item-meta"><p class="order-item-name">No items listed.</p></div></li>';

        if (viewOrderSubtotal) {
          viewOrderSubtotal.textContent = `$${(itemRows.length ? subtotal : Number.parseFloat(total) || 0).toFixed(2)}`;
        }
      }

      openDialog(viewOrderModal);
    });
  });

  closeViewOrderModal?.addEventListener("click", () => closeDialog(viewOrderModal));
  cancelViewOrderModal?.addEventListener("click", () => closeDialog(viewOrderModal));
  printViewOrderBtn?.addEventListener("click", () => window.print());

  // Order status donut
  const statusCtx = document.getElementById("orderStatusChart");
  const legend = document.getElementById("orderStatusLegend");
  const statusData = window.ordersStatusData || {
    labels: ["Pending", "Processing", "Completed", "Cancelled"],
    values: [0, 0, 0, 0],
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
        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : "0.0";
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
