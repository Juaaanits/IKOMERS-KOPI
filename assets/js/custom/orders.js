const orders = [
  {
    orderID: 1,
    customer: "Maria Sanchez",
    items: "1x Cappuccino ($4.75)",
    total: 4.75,
    status: "completed",
    date: "2025-01-05",
  },
];

document.getElementById("add-order").addEventListener("click", function () {
  const orderDialog = document.getElementById("order-form");
  orderDialog.showModal();
});
