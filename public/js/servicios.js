const cards = [...document.querySelectorAll("[data-service-card]")];
const searchInput = document.querySelector("#search-service");
const categoryFilter = document.querySelector("#filter-category");
const emptyState = document.querySelector("#empty-state");

function filterServices() {
  const query = searchInput.value.trim().toLocaleLowerCase("es");
  const category = categoryFilter.value;
  let visibleCount = 0;

  cards.forEach((card) => {
    const visible = card.dataset.search.includes(query)
      && (!category || card.dataset.category === category);
    card.hidden = !visible;
    if (visible) visibleCount += 1;
  });

  emptyState.classList.toggle("visible", visibleCount === 0);
}

searchInput.addEventListener("input", filterServices);
categoryFilter.addEventListener("change", filterServices);

document.querySelectorAll("[data-toggle-associations]").forEach((button) => {
  button.addEventListener("click", () => {
    const panel = document.getElementById(button.getAttribute("aria-controls"));
    const expanded = button.getAttribute("aria-expanded") === "true";

    panel.hidden = expanded;
    button.setAttribute("aria-expanded", String(!expanded));
    button.setAttribute(
      "aria-label",
      `${expanded ? "Ver" : "Ocultar"} inmuebles asociados a ${button.closest("[data-service-card]").querySelector(".service-name").textContent.trim()}`,
    );
    button.innerHTML = `<span class="icon" aria-hidden="true">${expanded ? "expand_more" : "expand_less"}</span>${expanded ? "Ver" : "Ocultar"} inmuebles`;
  });
});

const associateModal = document.querySelector("#associate-modal");
const associateForm = document.querySelector("#associate-form");
const serviceSelect = document.querySelector("#associate-service");
const serviceIdInput = document.querySelector("#associate-service-id");
const serviceLockedInput = document.querySelector("#service-locked");
const serviceLockNote = document.querySelector("#service-lock-note");
const propertySelect = document.querySelector("#associate-property");
const initialPropertyOptions = [...propertySelect.options].slice(1);
const serviceField = document.querySelector("#service-field");
const propertyField = document.querySelector("#property-field");
const associateTitle = document.querySelector("#associate-title");
const associateDescription = document.querySelector("#associate-description");
const saveAssociation = document.querySelector("#save-association");
const closeAssociateModal = () => associateModal.classList.remove("open");

function updateAvailableProperties() {
  const selectedService = serviceSelect.selectedOptions[0];
  const associatedIds = selectedService
    ? JSON.parse(selectedService.dataset.associatedIds || "[]").map(String)
    : [];
  const selectedProperty = propertySelect.value;

  initialPropertyOptions.forEach((option) => {
    option.disabled = associatedIds.includes(option.value);
  });

  if (associatedIds.includes(selectedProperty)) {
    propertySelect.value = "";
  }
}

function setService(serviceId, locked) {
  serviceSelect.value = serviceId;
  serviceSelect.disabled = locked;
  serviceIdInput.value = serviceId;
  serviceLockedInput.value = locked ? "1" : "0";
  serviceLockNote.hidden = !locked;
  updateAvailableProperties();
}

function setAssociationMode(editing) {
  serviceField.hidden = editing;
  propertyField.hidden = editing;
  associateTitle.textContent = editing ? "Modificar asociacion" : "Asociar servicio a inmueble";
  associateDescription.textContent = editing
    ? "Actualiza el estado y los datos registrados para este servicio."
    : "Registra el vinculo y los datos de identificacion del servicio.";
  saveAssociation.textContent = editing ? "Guardar cambios" : "Guardar asociacion";
}

document.querySelector("#open-associate")?.addEventListener("click", () => {
  associateForm.reset();
  associateForm.action = associateForm.dataset.associateAction;
  setAssociationMode(false);
  setService("", false);
  updateAvailableProperties();
  associateModal.classList.add("open");
});
document.querySelectorAll("[data-associate-service]").forEach((button) => {
  button.addEventListener("click", () => {
    associateForm.reset();
    associateForm.action = associateForm.dataset.associateAction;
    setAssociationMode(false);
    setService(button.dataset.serviceId, true);
    associateModal.classList.add("open");
  });
});
document.querySelectorAll("[data-edit-association]").forEach((button) => {
  button.addEventListener("click", () => {
    associateForm.reset();
    associateForm.action = `${associateForm.dataset.associateAction.replace(/\/asociar$/, "")}/asociaciones/${button.dataset.id}`;
    setAssociationMode(true);
    setService(button.dataset.serviceId, true);
    propertySelect.value = button.dataset.propertyId;
    associateForm.elements.namedItem("titular_inmueble").value = button.dataset.holder;
    associateForm.elements.namedItem("estado").value = button.dataset.status;
    associateForm.elements.namedItem("nro_medidor").value = button.dataset.meter;
    associateForm.elements.namedItem("nro_cuenta_contrato").value = button.dataset.account;
    associateForm.elements.namedItem("cod_pago_electronico").value = button.dataset.payment;
    associateForm.elements.namedItem("domicilio_facturacion").value = button.dataset.billing;
    associateModal.classList.add("open");
  });
});
document.querySelectorAll("[data-deactivate-association]").forEach((form) => {
  form.addEventListener("submit", (event) => {
    if (!window.confirm("¿Queres dar de baja este servicio? La asociacion y su historial de facturas se conservaran.")) {
      event.preventDefault();
    }
  });
});
serviceSelect.addEventListener("change", () => {
  serviceIdInput.value = serviceSelect.value;
  updateAvailableProperties();
});
document.querySelector("#close-associate").addEventListener("click", closeAssociateModal);
document.querySelector("#cancel-associate").addEventListener("click", closeAssociateModal);
associateModal.addEventListener("click", (event) => {
  if (event.target === associateModal) closeAssociateModal();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") closeAssociateModal();
});
if (serviceLockedInput.value === "1") {
  setService(serviceIdInput.value, true);
} else {
  serviceIdInput.value = serviceSelect.value;
  updateAvailableProperties();
}
setAssociationMode(associateModal.dataset.editing === "true");
