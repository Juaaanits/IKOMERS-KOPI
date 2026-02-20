document.addEventListener("DOMContentLoaded", () => {
  const trigger = document.getElementById("account-trigger");
  const dropdown = document.getElementById("account-dropdown");
  if (!trigger || !dropdown) return;

  const close = () => {
    dropdown.hidden = true;
    trigger.setAttribute("aria-expanded", "false");
  };

  trigger.addEventListener("click", (e) => {
    e.stopPropagation();
    const isOpen = !dropdown.hidden;
    dropdown.hidden = isOpen;
    trigger.setAttribute("aria-expanded", isOpen ? "false" : "true");
  });

  document.addEventListener("click", close);
});
