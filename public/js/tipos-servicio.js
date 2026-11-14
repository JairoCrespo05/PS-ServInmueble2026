(() => {
      const modal = document.getElementById("service-modal");
      const form = document.getElementById("service-form");
      const title = document.getElementById("modal-title");
      const nameInput = document.getElementById("service-name");
      const categoryInput = document.getElementById("service-category");
      const unitInput = document.getElementById("service-unit");
      const providerInput = document.getElementById("service-provider");
      const createAction = form.dataset.createAction;

      function openModal(service = null) {
        form.reset();
        form.action = service ? `${createAction}/${service.id}` : createAction;
        title.textContent = service ? "Modificar servicio" : "Nuevo servicio";
        form.querySelector('[type="submit"]').textContent = service ? "Guardar cambios" : "Registrar servicio";
        nameInput.value = service?.name ?? "";
        categoryInput.value = service?.category ?? "";
        unitInput.value = service?.unit ?? "";
        providerInput.value = service?.provider ?? "";
        modal.classList.add("open");
        modal.removeAttribute("aria-hidden");
        nameInput.focus();
      }

      function closeModal() {
        modal.classList.remove("open");
        modal.setAttribute("aria-hidden", "true");
      }

      document.getElementById("open-create").addEventListener("click", () => openModal());
      document.querySelectorAll("[data-edit-service]").forEach((button) => {
        button.addEventListener("click", () => openModal({
          id: button.dataset.id,
          name: button.dataset.name,
          category: button.dataset.category,
          unit: button.dataset.unit,
          provider: button.dataset.provider,
        }));
      });
      document.querySelectorAll("[data-close-modal]").forEach((button) => button.addEventListener("click", closeModal));
      modal.addEventListener("click", (event) => {
        if (event.target === modal) closeModal();
      });
      document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && modal.classList.contains("open")) closeModal();
      });
      document.querySelectorAll("[data-delete-form]").forEach((deleteForm) => {
        deleteForm.addEventListener("submit", (event) => {
          if (!window.confirm("¿Eliminar este servicio? No se puede eliminar si ya tiene asociaciones registradas.")) {
            event.preventDefault();
          }
        });
      });

      const search = document.getElementById("service-search");
      const categoryFilter = document.getElementById("category-filter");
      const rows = [...document.querySelectorAll("[data-service-row]")];
      const categoryCards = [...document.querySelectorAll("[data-category-card]")];
      const empty = document.getElementById("empty-state");
      const count = document.getElementById("results-count");
      const pagination = document.getElementById("pagination");
      const paginationSummary = document.getElementById("pagination-summary");
      const paginationNumbers = document.getElementById("pagination-numbers");
      const previousPage = document.getElementById("previous-page");
      const nextPage = document.getElementById("next-page");
      const pageSize = 8;
      let currentPage = 1;

      function updateCatalog() {
        const query = search.value.trim().toLocaleLowerCase();
        const category = categoryFilter.value;
        const matchingRows = rows.filter((row) =>
          row.dataset.search.includes(query)
          && (category === "" || row.dataset.category === category),
        );
        const pageCount = Math.max(1, Math.ceil(matchingRows.length / pageSize));
        currentPage = Math.min(currentPage, pageCount);
        const firstIndex = (currentPage - 1) * pageSize;
        const lastIndex = Math.min(firstIndex + pageSize, matchingRows.length);

        rows.forEach((row) => {
          row.hidden = true;
        });
        matchingRows.slice(firstIndex, lastIndex).forEach((row) => {
          row.hidden = false;
        });

        empty.hidden = matchingRows.length !== 0;
        pagination.hidden = matchingRows.length === 0;
        count.innerHTML = `Mostrando <strong>${matchingRows.length === 0 ? 0 : firstIndex + 1}-${lastIndex}</strong> de ${matchingRows.length} tipos`;
        paginationSummary.textContent = `Pagina ${currentPage} de ${pageCount}`;
        previousPage.disabled = currentPage === 1;
        nextPage.disabled = currentPage === pageCount;
        paginationNumbers.replaceChildren();

        for (let pageNumber = 1; pageNumber <= pageCount; pageNumber++) {
          const button = document.createElement("button");
          button.type = "button";
          button.textContent = String(pageNumber);
          button.setAttribute("aria-label", `Pagina ${pageNumber}`);
          if (pageNumber === currentPage) {
            button.setAttribute("aria-current", "page");
          }
          button.addEventListener("click", () => {
            currentPage = pageNumber;
            updateCatalog();
          });
          paginationNumbers.append(button);
        }

        categoryCards.forEach((card) => {
          const selected = card.dataset.category === category;
          card.classList.toggle("active", selected);
          card.setAttribute("aria-pressed", String(selected));
        });
      }

      search.addEventListener("input", () => {
        currentPage = 1;
        updateCatalog();
      });
      categoryFilter.addEventListener("change", () => {
        currentPage = 1;
        updateCatalog();
      });
      categoryCards.forEach((card) => {
        card.addEventListener("click", () => {
          categoryFilter.value = categoryFilter.value === card.dataset.category ? "" : card.dataset.category;
          currentPage = 1;
          updateCatalog();
        });
      });
      previousPage.addEventListener("click", () => {
        currentPage--;
        updateCatalog();
      });
      nextPage.addEventListener("click", () => {
        currentPage++;
        updateCatalog();
      });
      updateCatalog();
    })();
