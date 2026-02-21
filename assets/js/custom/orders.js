document.addEventListener("DOMContentLoaded", () => {
  const fab = document.getElementById("orders-fab");
  const orderModal = document.getElementById("orderModal");
  const closeOrderModal = document.getElementById("closeOrderModal");
  const cancelOrderModal = document.getElementById("cancelOrderModal");
  const orderIdInput = document.getElementById("order-id");
  const orderForm = document.querySelector("#orderModal form");
  const orderModalTitle = document.querySelector("#orderModal h3");
  const submitBtn = document.querySelector("#orderModal button[type='submit']");

  const selectCustomerModal = document.getElementById("selectCustomerModal");
  const openSelectCustomer = document.getElementById("openSelectCustomer");
  const closeSelectCustomer = document.getElementById("closeSelectCustomer");
  const cancelSelectCustomer = document.getElementById("cancelSelectCustomer");
  const confirmSelectCustomer = document.getElementById("confirmSelectCustomer");
  const selectCustomerSearchInput = document.getElementById("select-customer-search-input");
  const selectCustomerRows = Array.from(document.querySelectorAll(".select-customer-row"));
  const orderCustomerNameInput = document.getElementById("order-customer-name");
  const orderCustomerIdInput = document.getElementById("order-customer-id");
  const orderItemsHiddenInput = document.querySelector(".order-items-hidden");
  const addOrderItemBtn = document.getElementById("addOrderItemBtn");
  const orderItemsTableBody = document.querySelector(".items-table tbody");
  const orderTotalInput = document.querySelector("#orderModal input[name='total']");
  const menuCatalog = Array.isArray(window.menuItemCatalog) ? window.menuItemCatalog : [];
  const menuCatalogMap = new Map(
    menuCatalog.map((item) => [String(item.name || "").trim().toLowerCase(), item])
  );

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
  const parseJsonResponse = async (res) => {
    const raw = await res.text();
    try {
      return JSON.parse(raw);
    } catch {
      throw new Error(raw.slice(0, 180) || "Invalid JSON response");
    }
  };
  let selectedCustomerRow = null;
  let activeStatusOrderId = 0;

  const formatMoney = (value) => Number(value).toFixed(2);

  const parseOrderItems = (itemsText) => {
    const raw = String(itemsText || "").trim();
    if (!raw) return [];

    return raw
      .split(",")
      .map((part) => part.trim())
      .filter(Boolean)
      .map((part) => {
        const normalized = part.replace(/\s+/g, " ").trim();
        const match = normalized.match(
          /^(\d+)\s*x\s*(.+?)\s*\((?:₱|\$|â‚±)?\s*([\d.]+)\)$/i
        );

        if (!match) {
          const fallbackName = normalized.replace(/^\d+\s*x\s*/i, "").trim();
          return {
            name: fallbackName || normalized,
            qty: 1,
            price: 0,
            id: 0,
          };
        }

        const qty = Math.max(1, Number.parseInt(match[1], 10) || 1);
        const name = match[2].trim();
        const price = Math.max(0, Number.parseFloat(match[3]) || 0);
        const mapped = menuCatalogMap.get(name.toLowerCase());

        return {
          name,
          qty,
          price,
          id: mapped ? Number(mapped.id || 0) : 0,
        };
      });
  };

  const clearOrderItemRows = () => {
    if (!orderItemsTableBody) return;
    orderItemsTableBody.innerHTML = "";
    ensureEmptyStateRow();
  };

  const ensureEmptyStateRow = () => {
    if (!orderItemsTableBody) return;
    const hasDataRows = orderItemsTableBody.querySelectorAll(".order-item-edit-row").length > 0;
    const emptyRow = orderItemsTableBody.querySelector(".items-empty-row");
    if (!hasDataRows && !emptyRow) {
      const tr = document.createElement("tr");
      tr.innerHTML = '<td colspan="6" class="items-empty-row"></td>';
      orderItemsTableBody.appendChild(tr);
      return;
    }
    if (hasDataRows && emptyRow) {
      emptyRow.closest("tr")?.remove();
    }
  };

  const buildItemsPayloadAndTotal = () => {
    if (!orderItemsTableBody) return;
    const rows = Array.from(orderItemsTableBody.querySelectorAll(".order-item-edit-row"));
    const tokens = [];
    let orderTotal = 0;
    let hasPricedLine = false;

    rows.forEach((row) => {
      const itemInput = row.querySelector(".order-item-name-input");
      const qtyInput = row.querySelector(".order-item-qty-input");
      const priceInput = row.querySelector(".order-item-price-input");
      const totalCell = row.querySelector(".order-item-total");

      const itemName = (itemInput?.value || "").trim();
      const qty = Math.max(1, Number.parseInt(qtyInput?.value || "1", 10) || 1);
      const price = Math.max(0, Number.parseFloat(priceInput?.value || "0") || 0);
      const lineTotal = qty * price;
      orderTotal += lineTotal;
      if (lineTotal > 0) hasPricedLine = true;

      if (totalCell) totalCell.textContent = formatMoney(lineTotal);
      if (itemName) {
        tokens.push(`${qty}x ${itemName} (₱${formatMoney(price)})`);
      }
    });

    if (tokens.length > 0) {
      if (orderTotalInput && hasPricedLine) orderTotalInput.value = formatMoney(orderTotal);
      if (orderItemsHiddenInput) orderItemsHiddenInput.value = tokens.join(", ");
    } else if (rows.length === 0 && orderItemsHiddenInput) {
      orderItemsHiddenInput.value = "";
    }
  };

  const applyMenuItemToRow = (row) => {
    const nameInput = row.querySelector(".order-item-name-input");
    const idInput = row.querySelector(".order-item-id-input");
    const priceInput = row.querySelector(".order-item-price-input");
    const key = (nameInput?.value || "").trim().toLowerCase();
    const match = menuCatalogMap.get(key);

    if (match) {
      if (idInput) idInput.value = String(match.id || 0);
      if (priceInput) priceInput.value = formatMoney(match.price || 0);
    }
  };

  const addItemRow = (seed = {}) => {
    if (!orderItemsTableBody) return;
    ensureEmptyStateRow();

    const tr = document.createElement("tr");
    tr.className = "order-item-edit-row";
    tr.innerHTML = `
      <td><input type="text" class="order-item-input order-item-name-input" value="${seed.name || ""}" placeholder="Item name" list="order-item-options" autocomplete="off"></td>
      <td><input type="number" class="order-item-input order-item-id-input" value="${seed.id || 0}" min="0" step="1" readonly></td>
      <td><input type="number" class="order-item-input order-item-qty-input" value="${seed.qty || 1}" min="1" step="1"></td>
      <td><input type="number" class="order-item-input order-item-price-input" value="${seed.price || 0}" min="0" step="0.01" readonly></td>
      <td class="order-item-total">0.00</td>
      <td><button type="button" class="row-remove-btn" aria-label="Remove item">&times;</button></td>
    `;

    tr.querySelectorAll("input").forEach((input) => {
      input.addEventListener("input", () => {
        if (input.classList.contains("order-item-name-input")) {
          applyMenuItemToRow(tr);
        }
        buildItemsPayloadAndTotal();
      });
      input.addEventListener("change", () => {
        if (input.classList.contains("order-item-name-input")) {
          applyMenuItemToRow(tr);
        }
        buildItemsPayloadAndTotal();
      });
    });
    tr.querySelector(".row-remove-btn")?.addEventListener("click", () => {
      tr.remove();
      ensureEmptyStateRow();
      buildItemsPayloadAndTotal();
    });

    orderItemsTableBody.appendChild(tr);
    applyMenuItemToRow(tr);
    ensureEmptyStateRow();
    buildItemsPayloadAndTotal();
  };

  fab?.addEventListener("click", () => {
    orderForm.reset();
    orderIdInput.value = "";
    if (orderCustomerIdInput) orderCustomerIdInput.value = "";
    clearOrderItemRows();
    orderModalTitle.textContent = "Add New Order";
    submitBtn.textContent = "Add Order";
    orderModal.showModal();
  });

  closeOrderModal?.addEventListener("click", () => closeDialog(orderModal));
  cancelOrderModal?.addEventListener("click", () => closeDialog(orderModal));

  openSelectCustomer?.addEventListener("click", () => openDialog(selectCustomerModal));
  closeSelectCustomer?.addEventListener("click", () => closeDialog(selectCustomerModal));
  cancelSelectCustomer?.addEventListener("click", () => closeDialog(selectCustomerModal));

  addOrderItemBtn?.addEventListener("click", () => addItemRow());

  selectCustomerRows.forEach((row) => {
    row.addEventListener("click", () => {
      if (selectedCustomerRow) selectedCustomerRow.classList.remove("is-selected");
      selectedCustomerRow = row;
      selectedCustomerRow.classList.add("is-selected");
    });

    row.addEventListener("dblclick", () => {
      selectedCustomerRow = row;
      const id = row.dataset.id || "";
      const name = row.dataset.name || "";
      if (orderCustomerNameInput) orderCustomerNameInput.value = name;
      if (orderCustomerIdInput) orderCustomerIdInput.value = id ? `ID: ${id}` : "";
      closeDialog(selectCustomerModal);
    });
  });

  selectCustomerSearchInput?.addEventListener("input", () => {
    const keyword = selectCustomerSearchInput.value.trim().toLowerCase();
    selectCustomerRows.forEach((row) => {
      const text = row.textContent?.toLowerCase() || "";
      row.style.display = text.includes(keyword) ? "" : "none";
    });
  });

  confirmSelectCustomer?.addEventListener("click", () => {
    if (!selectedCustomerRow) return;
    const id = selectedCustomerRow.dataset.id || "";
    const name = selectedCustomerRow.dataset.name || "";
    if (orderCustomerNameInput) orderCustomerNameInput.value = name;
    if (orderCustomerIdInput) orderCustomerIdInput.value = id ? `ID: ${id}` : "";
    closeDialog(selectCustomerModal);
  });

  document.querySelectorAll(".js-edit-order").forEach((btn) => {
    btn.addEventListener("click", () => {
      clearOrderItemRows();

      const itemsText = btn.dataset.items || "";
      const parsedItems = parseOrderItems(itemsText);
      if (parsedItems.length > 0) {
        parsedItems.forEach((item) => addItemRow(item));
      }

      orderIdInput.value = btn.dataset.orderId || "";
      document.getElementById("order-customer-name").value = btn.dataset.customer || "";
      orderForm.querySelector("textarea[name='items']").value = itemsText;
      orderForm.querySelector("input[name='total']").value = btn.dataset.total || "";
      orderForm.querySelector("select[name='status']").value = btn.dataset.status || "Pending";

      orderModalTitle.textContent = "Edit Order";
      submitBtn.textContent = "Save Changes";
      orderModal.showModal();
    });
  });

  closeStatusModal?.addEventListener("click", () => closeDialog(statusModal));
  cancelStatusModal?.addEventListener("click", () => closeDialog(statusModal));
  saveStatusModal?.addEventListener("click", async () => {
    if (!activeStatusOrderId || !statusSelect) return;

    try {
      const fd = new FormData();
      fd.append("id", String(activeStatusOrderId));
      fd.append("status", statusSelect.value);

      const res = await fetch("orders_update.php", { method: "POST", body: fd });
      const data = await parseJsonResponse(res);

      if (!data.ok) {
        window.showAppNotice?.(data.message || "Failed to update order", "error");
        return;
      }

      window.location.reload();
    } catch (error) {
      window.showAppNotice?.(String(error.message || error), "error");
    }
  });

  document.querySelectorAll(".js-delete-order").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const orderId = Number.parseInt(btn.dataset.orderId || "0", 10);
      if (!orderId) return;
      const confirmed = window.confirm("Delete this order permanently?");
      if (!confirmed) return;

      try {
        const fd = new FormData();
        fd.append("id", String(orderId));

        const res = await fetch("orders_delete.php", { method: "POST", body: fd });
        const data = await parseJsonResponse(res);

        if (!data.ok) {
          window.showAppNotice?.(data.message || "Failed to delete order", "error");
          return;
        }

        window.location.reload();
      } catch (error) {
        window.showAppNotice?.(String(error.message || error), "error");
      }
    });
  });

  document.querySelectorAll(".js-view-order").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id || "0";
      const date = btn.dataset.date || "-";
      const total = btn.dataset.total || "0.00";
      const itemsRaw = (btn.dataset.items || "").trim();

      if (viewOrderTitle) viewOrderTitle.textContent = `Order #${id}`;
      if (viewOrderDate) viewOrderDate.textContent = date;
      if (viewOrderTotal) viewOrderTotal.textContent = `₱${total}`;

      if (viewOrderItems) {
        const itemRows = itemsRaw
          ? itemsRaw.split(",").map((item) => item.trim()).filter(Boolean)
          : [];
        let subtotal = 0;

        viewOrderItems.innerHTML = itemRows.length
          ? itemRows
              .map((item) => {
                const normalized = item.replace(/\s+/g, " ").trim();
                const match = normalized.match(/^(\d+)\s*x\s*(.+?)\s*\(([₱$])?([\d.]+)\)$/i);

                const qty = match ? Number.parseInt(match[1], 10) : 1;
                const name = match ? match[2] : normalized;
                const price = match ? Number.parseFloat(match[4]) : Number.NaN;

                if (!Number.isNaN(price)) {
                  subtotal += qty * price;
                }

                const priceText = Number.isNaN(price) ? "-" : `₱${price.toFixed(2)}`;
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
          viewOrderSubtotal.textContent = `₱${(itemRows.length ? subtotal : Number.parseFloat(total) || 0).toFixed(2)}`;
        }
      }

      openDialog(viewOrderModal);
    });
  });

  closeViewOrderModal?.addEventListener("click", () => closeDialog(viewOrderModal));
  cancelViewOrderModal?.addEventListener("click", () => closeDialog(viewOrderModal));
  printViewOrderBtn?.addEventListener("click", () => window.print());

  // Keep backend-required items field non-empty when user submits from cloned UI.
  document.querySelector("#orderModal form")?.addEventListener("submit", () => {
    buildItemsPayloadAndTotal();
    if (orderItemsHiddenInput && !orderItemsHiddenInput.value.trim()) {
      orderItemsHiddenInput.value = "1x Custom Order (₱0.01)";
    }
    if (orderTotalInput) {
      const parsedTotal = Number.parseFloat(orderTotalInput.value || "0");
      if (!Number.isFinite(parsedTotal) || parsedTotal <= 0) {
        orderTotalInput.value = "0.01";
      }
    }
  });

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
