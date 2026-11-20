(() => {
  const sidebar = document.querySelector("#sidebar");
  const collapseButton = document.querySelector("#btnToggleSidebar");
  const expandButton = document.querySelector("#btnMiniExpand");

  if (!sidebar || !collapseButton || !expandButton) return;

  const mobileQuery = window.matchMedia("(max-width: 760px)");
  const updateExpandedState = () => {
    const expanded = mobileQuery.matches
      ? sidebar.classList.contains("mobile-open")
      : !sidebar.classList.contains("collapsed");

    collapseButton.setAttribute("aria-expanded", String(expanded));
    expandButton.setAttribute("aria-expanded", String(expanded));
  };
  const toggleSidebar = () => {
    if (mobileQuery.matches) {
      sidebar.classList.toggle("mobile-open");
    } else {
      sidebar.classList.toggle("collapsed");
    }

    updateExpandedState();
  };

  collapseButton.addEventListener("click", toggleSidebar);
  expandButton.addEventListener("click", toggleSidebar);
  mobileQuery.addEventListener("change", () => {
    sidebar.classList.remove("mobile-open");
    updateExpandedState();
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && mobileQuery.matches) {
      sidebar.classList.remove("mobile-open");
      updateExpandedState();
    }
  });
  updateExpandedState();
})();
