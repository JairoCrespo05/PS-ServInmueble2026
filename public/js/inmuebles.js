const rows = [...document.querySelectorAll("[data-property-row]")];
const searchInput = document.querySelector("#search-property");
const typeFilter = document.querySelector("#filter-type");
const statusFilter = document.querySelector("#filter-status");
const pageSize = document.querySelector("#page-size");
const pagination = document.querySelector("#pagination");
const resultSummary = document.querySelector("#result-summary");
const emptyState = document.querySelector("#empty-state");
let currentPage = 1;

function renderTable() {
  const query = searchInput.value.trim().toLocaleLowerCase("es");
  const type = typeFilter.value;
  const status = statusFilter.value;
  const filtered = rows.filter((row) =>
    row.dataset.search.includes(query)
    && (!type || row.dataset.type === type)
    && (!status || row.dataset.status === status)
  );
  const size = Number(pageSize.value);
  const pageCount = Math.max(1, Math.ceil(filtered.length / size));
  currentPage = Math.min(currentPage, pageCount);
  const start = (currentPage - 1) * size;
  const visible = new Set(filtered.slice(start, start + size));

  rows.forEach((row) => { row.hidden = !visible.has(row); });
  emptyState.style.display = filtered.length ? "none" : "block";
  resultSummary.textContent = filtered.length
    ? `Mostrando ${start + 1} - ${Math.min(start + size, filtered.length)} de ${filtered.length} propiedades`
    : "Mostrando 0 propiedades";

  pagination.replaceChildren();
  const addPageButton = (label, page, disabled = false, current = false, accessibleLabel = "") => {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = label;
    button.disabled = disabled;
    if (accessibleLabel) button.setAttribute("aria-label", accessibleLabel);
    if (current) button.setAttribute("aria-current", "page");
    button.addEventListener("click", () => { currentPage = page; renderTable(); });
    pagination.append(button);
  };
  addPageButton("\u2190", Math.max(1, currentPage - 1), currentPage === 1, false, "Página anterior");
  for (let page = 1; page <= pageCount; page += 1) {
    addPageButton(String(page), page, false, page === currentPage);
  }
  addPageButton("\u2192", Math.min(pageCount, currentPage + 1), currentPage === pageCount, false, "Página siguiente");
}

[searchInput, typeFilter, statusFilter].forEach((control) => control.addEventListener("input", () => {
  currentPage = 1;
  renderTable();
}));
pageSize.addEventListener("change", () => { currentPage = 1; renderTable(); });
document.querySelector("#reset-filters").addEventListener("click", () => {
  searchInput.value = "";
  typeFilter.value = "";
  statusFilter.value = "";
  currentPage = 1;
  renderTable();
});

document.querySelector("#export-button").addEventListener("click", () => {
  const headers = ["Propiedad / Alias", "Direccion y localidad", "Tipo", "Superficie", "Servicios activos", "Estado"];
  const data = rows.map((row) => [...row.cells].slice(0, 6).map((cell) => cell.innerText.trim()));
  const csv = [headers, ...data].map((record) =>
    record.map((value) => `"${value.replaceAll('"', '""')}"`).join(";")
  ).join("\r\n");
  const link = document.createElement("a");
  link.href = URL.createObjectURL(new Blob(["\uFEFF", csv], { type: "text/csv;charset=utf-8" }));
  link.download = "inmuebles.csv";
  link.click();
  URL.revokeObjectURL(link.href);
});

const modal = document.querySelector("#create-modal");
const form = modal.querySelector("form");
const provinceSelect = form.elements.namedItem("provincia");
const citySelect = form.elements.namedItem("ciudad");
const locationStatus = document.querySelector("#location-status");
const retryLocationCatalog = document.querySelector("#retry-location-catalog");
const provinceByName = new Map();
let localityRequestId = 0;
const closeModal = () => modal.classList.remove("open");
const detailsModal = document.querySelector("#property-details-modal");
const detailsGrid = document.querySelector("#property-details-grid");
const detailsServices = document.querySelector("#property-details-services");
const detailsTitle = document.querySelector("#property-details-title");
const closeDetails = () => {
  detailsModal.classList.remove("open");
  detailsModal.setAttribute("aria-hidden", "true");
};
const appendDetail = (container, label, value) => {
  const item = document.createElement("div");
  item.className = "detail-item";
  const labelElement = document.createElement("span");
  labelElement.className = "detail-item-label";
  labelElement.textContent = label;
  const valueElement = document.createElement("span");
  valueElement.className = "detail-item-value";
  valueElement.textContent = value === null || value === "" ? "No disponible" : String(value);
  item.append(labelElement, valueElement);
  container.append(item);
};
const setLocationStatus = (message, retry = false) => {
  locationStatus.textContent = message;
  retryLocationCatalog.hidden = !retry;
};
const setSelectPlaceholder = (select, label) => {
  select.replaceChildren(new Option(label, ""));
};
const fetchLocationData = async (url) => {
  const response = await fetch(url, {
    headers: { Accept: "application/json" },
    credentials: "same-origin",
  });
  let data;
  try {
    data = await response.json();
  } catch {
    throw new Error("GeoRef respondió con datos inválidos. Intentá nuevamente.");
  }
  if (!response.ok) throw new Error(data.error || "No se pudo cargar el catálogo geográfico.");
  return data;
};
const loadLocalities = async (selectedCity = "") => {
  const province = provinceByName.get(provinceSelect.value);
  const requestId = ++localityRequestId;
  setSelectPlaceholder(citySelect, "Cargando localidades...");
  citySelect.disabled = true;
  setLocationStatus("");

  if (!province) {
    setSelectPlaceholder(citySelect, "Primero seleccioná una provincia");
    return false;
  }

  try {
    const data = await fetchLocationData(
      `${form.dataset.localitiesUrl}/${encodeURIComponent(province.id)}/localidades`
    );
    if (requestId !== localityRequestId) return false;
    if (!Array.isArray(data.localidades)) throw new Error("GeoRef respondió sin localidades.");

    setSelectPlaceholder(citySelect, "Seleccioná una ciudad o localidad");
    data.localidades.forEach((name) => citySelect.add(new Option(name, name)));
    citySelect.disabled = false;

    if (selectedCity && [...citySelect.options].some((option) => option.value === selectedCity)) {
      citySelect.value = selectedCity;
    } else if (selectedCity) {
      setLocationStatus("La localidad guardada ya no figura en el catálogo de GeoRef. Elegí una localidad del listado.");
    }

    return true;
  } catch (error) {
    if (requestId !== localityRequestId) return false;
    setSelectPlaceholder(citySelect, "No se pudieron cargar las localidades");
    setLocationStatus(error.message, true);
    console.error("Error al cargar localidades de GeoRef:", error);
    return false;
  }
};
const loadProvinces = async (selectedProvince = "", selectedCity = "") => {
  provinceSelect.disabled = true;
  citySelect.disabled = true;
  setSelectPlaceholder(provinceSelect, "Cargando provincias...");
  setSelectPlaceholder(citySelect, "Primero seleccioná una provincia");
  setLocationStatus("");

  try {
    const data = await fetchLocationData(form.dataset.provincesUrl);
    if (!Array.isArray(data.provincias)) throw new Error("GeoRef respondió sin provincias.");

    provinceByName.clear();
    setSelectPlaceholder(provinceSelect, "Seleccioná una provincia");
    data.provincias.forEach((province) => {
      provinceByName.set(province.nombre, province);
      provinceSelect.add(new Option(province.nombre, province.nombre));
    });
    provinceSelect.disabled = false;

    if (selectedProvince && provinceByName.has(selectedProvince)) {
      provinceSelect.value = selectedProvince;
      await loadLocalities(selectedCity);
    } else if (selectedProvince) {
      setLocationStatus("La provincia guardada ya no figura en el catálogo de GeoRef. Elegí una provincia del listado.");
    } else if (selectedCity) {
      setLocationStatus("Este inmueble no tiene una provincia registrada. Seleccioná una provincia y luego una localidad.");
    }

    return true;
  } catch (error) {
    setSelectPlaceholder(provinceSelect, "No se pudieron cargar las provincias");
    setSelectPlaceholder(citySelect, "Primero seleccioná una provincia");
    setLocationStatus(error.message, true);
    console.error("Error al cargar provincias de GeoRef:", error);
    return false;
  }
};
let provincesPromise = loadProvinces(form.dataset.initialProvince, form.dataset.initialCity);

provinceSelect.addEventListener("change", () => { loadLocalities(); });
retryLocationCatalog.addEventListener("click", () => {
  if (provinceSelect.value) {
    loadLocalities();
  } else {
    provincesPromise = loadProvinces();
  }
});
form.addEventListener("submit", (event) => {
  if (!provinceSelect.value || !citySelect.value || provinceSelect.disabled || citySelect.disabled) {
    event.preventDefault();
    setLocationStatus("Seleccioná una provincia y una ciudad/localidad antes de guardar.");
    if (!retryLocationCatalog.hidden) {
      retryLocationCatalog.focus();
    } else if (!provinceSelect.disabled) {
      provinceSelect.focus();
    }
  }
});

document.querySelectorAll("[data-view-property]").forEach((button) => {
  button.addEventListener("click", () => {
    const property = JSON.parse(button.dataset.propertyDetails);
    detailsTitle.textContent = property.alias_nombre;
    detailsGrid.replaceChildren();
    detailsServices.replaceChildren();

    [
      ["ID del inmueble", property.id],
      ["Alias o nombre", property.alias_nombre],
      ["Tipo", property.tipo],
      ["Superficie", `${property.superficie} m²`],
      ["Estado", property.estado],
      ["ID de direccion", property.direccion_id],
      ["Calle", property.calle],
      ["Numero", property.numero],
      ["Piso / departamento", property.piso_dpto],
      ["Provincia", property.provincia],
      ["Ciudad / localidad", property.ciudad],
      ["Inmueble creado", property.inmueble_created_at],
      ["Inmueble actualizado", property.inmueble_updated_at],
      ["Direccion creada", property.direccion_created_at],
      ["Direccion actualizada", property.direccion_updated_at],
    ].forEach(([label, value]) => appendDetail(detailsGrid, label, value));

    if (property.servicios.length === 0) {
      const emptyServices = document.createElement("p");
      emptyServices.className = "no-associated-services";
      emptyServices.textContent = "Este inmueble no tiene servicios asociados.";
      detailsServices.append(emptyServices);
    } else {
      property.servicios.forEach((service) => {
        const card = document.createElement("article");
        card.className = "associated-service";
        const heading = document.createElement("h4");
        heading.textContent = service.servicio_nombre;
        card.append(heading);
        [
          ["ID de asociacion", service.asociacion_id],
          ["ID de servicio", service.servicio_id],
          ["Categoria", service.servicio_categoria],
          ["Unidad de medida", service.unidad_medida_std],
          ["Proveedor", service.proveedor],
          ["Titular", service.titular_inmueble],
          ["Estado de la asociacion", service.estado_asociacion],
          ["Numero de medidor", service.nro_medidor],
          ["Cuenta / contrato", service.nro_cuenta_contrato],
          ["Codigo de pago electronico", service.cod_pago_electronico],
          ["Domicilio de facturacion", service.domicilio_facturacion],
          ["Asociacion creada", service.asociacion_created_at],
          ["Asociacion actualizada", service.asociacion_updated_at],
        ].forEach(([label, value]) => appendDetail(card, label, value));
        card.classList.add("details-grid");
        detailsServices.append(card);
      });
    }

    detailsModal.classList.add("open");
    detailsModal.removeAttribute("aria-hidden");
    document.querySelector("#close-property-details").focus();
  });
});
document.querySelector("#open-create").addEventListener("click", () => {
  form.reset();
  form.action = form.dataset.createAction;
  provinceSelect.value = "";
  setSelectPlaceholder(citySelect, "Primero seleccioná una provincia");
  citySelect.disabled = true;
  setLocationStatus("");
  document.querySelector("#modal-title").textContent = "Registrar nuevo inmueble";
  document.querySelector("#save-property").textContent = "Guardar inmueble";
  modal.classList.add("open");
});
document.querySelectorAll("[data-edit-property]").forEach((button) => {
  button.addEventListener("click", async () => {
    form.reset();
    form.action = `${form.dataset.createAction}/${button.dataset.id}`;
    document.querySelector("#modal-title").textContent = "Modificar inmueble";
    document.querySelector("#save-property").textContent = "Guardar cambios";
    for (const [field, value] of Object.entries({
      alias_nombre: button.dataset.alias,
      tipo: button.dataset.tipo,
      superficie: button.dataset.superficie,
      estado: button.dataset.estado,
      calle: button.dataset.calle,
      numero: button.dataset.numero,
      piso_dpto: button.dataset.piso,
    })) {
      form.elements.namedItem(field).value = value;
    }
    const provincesAvailable = await provincesPromise;
    if (!provincesAvailable) {
      modal.classList.add("open");
      return;
    }

    provinceSelect.value = button.dataset.provincia || "";
    if (provinceSelect.value) {
      await loadLocalities(button.dataset.ciudad || "");
    } else {
      setSelectPlaceholder(citySelect, "Primero seleccioná una provincia");
      citySelect.disabled = true;
      setLocationStatus("Este inmueble no tiene una provincia registrada. Seleccioná una provincia y luego una localidad.");
    }
    modal.classList.add("open");
  });
});
document.querySelectorAll("[data-deactivate-form]").forEach((deactivateForm) => {
  deactivateForm.addEventListener("submit", (event) => {
    if (!window.confirm("¿Quieres dar de baja este inmueble? Se conservara el registro y quedara como Inactivo.")) {
      event.preventDefault();
    }
  });
});
document.querySelector("#close-create").addEventListener("click", closeModal);
document.querySelector("#cancel-create").addEventListener("click", closeModal);
modal.addEventListener("click", (event) => { if (event.target === modal) closeModal(); });
document.querySelector("#close-property-details").addEventListener("click", closeDetails);
document.querySelector("#close-property-details-footer").addEventListener("click", closeDetails);
detailsModal.addEventListener("click", (event) => { if (event.target === detailsModal) closeDetails(); });
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeModal();
    closeDetails();
  }
});

renderTable();
