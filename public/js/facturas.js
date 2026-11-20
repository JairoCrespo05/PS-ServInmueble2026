(function () {
  const form = document.querySelector('.invoice-card form');
  if (!form) return;

  const secondDueDate = form.elements.fecha_segundo_vencimiento;
  const firstDueDate = form.elements.fecha_primer_vencimiento;
  const previousReading = form.elements.lectura_anterior;
  const currentReading = form.elements.lectura_actual;
  const serviceSelect = form.elements.inmueble_servicio_id;
  const consumptionUnit = document.getElementById('consumo-unidad');
  const errorSummary = document.querySelector('.invoice-error-summary');

  function validateRelatedFields() {
    secondDueDate.setCustomValidity(
      secondDueDate.value && firstDueDate.value && secondDueDate.value < firstDueDate.value
        ? 'El segundo vencimiento no puede ser anterior al primero.'
        : '',
    );

    const readingsIncomplete = Boolean(previousReading.value) !== Boolean(currentReading.value);
    const readingsOutOfOrder =
      previousReading.value &&
      currentReading.value &&
      currentReading.valueAsNumber < previousReading.valueAsNumber;

    previousReading.setCustomValidity(
      readingsIncomplete
        ? 'Completá ambas lecturas o dejá las dos vacías.'
        : '',
    );
    currentReading.setCustomValidity(
      readingsIncomplete
        ? 'Completá ambas lecturas o dejá las dos vacías.'
        : readingsOutOfOrder
          ? 'La lectura actual no puede ser menor que la anterior.'
          : '',
    );
  }

  function updateConsumptionUnit() {
    const option = serviceSelect.selectedOptions[0];
    const selectedService = option && option.value ? option.textContent : '';
    const unit = selectedService.match(/·\s*([^·]+)$/);
    consumptionUnit.textContent = unit ? unit[1].trim() : 'la unidad del servicio';
  }

  for (const field of [secondDueDate, firstDueDate, previousReading, currentReading]) {
    field.addEventListener('input', validateRelatedFields);
    field.addEventListener('change', validateRelatedFields);
  }

  serviceSelect.addEventListener('change', updateConsumptionUnit);
  updateConsumptionUnit();
  validateRelatedFields();

  form.addEventListener('submit', (event) => {
    validateRelatedFields();
    if (!form.checkValidity()) {
      event.preventDefault();
      form.reportValidity();
    }
  });

  if (errorSummary) {
    errorSummary.tabIndex = -1;
    errorSummary.focus();
  }
})();