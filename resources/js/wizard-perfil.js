document.addEventListener('DOMContentLoaded', function () {
    const steps = document.querySelectorAll('.wizard-step');
    const prevBtn = document.getElementById('wizard-prev');
    const nextBtn = document.getElementById('wizard-next');
    const progressText = document.getElementById('wizard-progress');
    const currentStepSpan = document.getElementById('wizard-current-step');
    const completionSpan = document.getElementById('wizard-completion');

    let currentStep = 0;

    function showStep(stepIndex) {
        steps.forEach((step, index) => {
            step.classList.toggle('active', index === stepIndex);
        });
        currentStep = stepIndex;
        currentStepSpan.textContent = stepIndex + 1;
        completionSpan.textContent = `${Math.floor((stepIndex + 1) / steps.length * 100)}% completado`;
        prevBtn.disabled = stepIndex === 0;
        nextBtn.textContent = stepIndex === steps.length - 1 ? 'Actualizar perfil' : 'Siguiente';
    }

    function validateStep(stepIndex) {
        const step = steps[stepIndex];
        const selects = step.querySelectorAll('select[required]');
        const checkboxes = step.querySelectorAll('input[type="checkbox"]');

        let valid = true;
        selects.forEach(select => {
            if (!select.value) {
                valid = false;
                select.classList.add('border-red-500');
            } else {
                select.classList.remove('border-red-500');
            }
        });
        // Checkbox no es requerido, pero si estuviera, se validaría aquí.
        return valid;
    }

    function setLoading(isLoading) {
        nextBtn.disabled = isLoading;
        if (isLoading) {
            nextBtn.dataset.originalText = nextBtn.textContent;
            nextBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Guardando...';
        } else if (nextBtn.dataset.originalText) {
            nextBtn.textContent = nextBtn.dataset.originalText;
        }
    }

    async function handleSubmit() {
        setLoading(true);
        // Recopilar todos los datos del formulario
        const formData = {
            tamaño_adulto: document.getElementById('wizard-tamanio').value,
            luz_requerida: document.getElementById('wizard-luz').value,
            frecuencia_riego: document.getElementById('wizard-riego').value,
            tipo_ambiente: document.getElementById('wizard-ambiente').value,
            nivel_cuidado: document.getElementById('wizard-nivel').value,
            estetica: document.getElementById('wizard-estetica').value,
            toxicidad: document.getElementById('wizard-toxicidad').checked ? 1 : 0
        };

        // Validar que todos los campos requeridos estén presentes
        const required = ['tamaño_adulto', 'luz_requerida', 'frecuencia_riego', 'tipo_ambiente', 'nivel_cuidado', 'estetica'];
        const missing = required.filter(field => !formData[field]);

        if (missing.length > 0) {
            alert('Por favor completa todos los campos requeridos');
            setLoading(false);
            return;
        }

        try {
            const response = await fetch(PERFIL_UPDATE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const errorData = await response.json();
                let errorMsg = 'Error al actualizar el perfil';
                if (errorData.message) {
                    errorMsg = errorData.message;
                } else if (errorData.errors) {
                    // Mostrar primeros errores de validación
                    const firstError = Object.values(errorData.errors)[0][0];
                    errorMsg = firstError;
                }
                alert(errorMsg);
                setLoading(false);
                return;
            }

            const result = await response.json();
            if (result.success) {
                // Redirigir a la página principal del cliente
                window.location.href = CLIENT_HOME_URL;
            } else {
                alert(result.message || 'Error desconocido');
                setLoading(false);
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión. Por favor intenta nuevamente.');
            setLoading(false);
        }
    }

    // Event listeners
    prevBtn.addEventListener('click', () => {
        if (currentStep > 0) {
            showStep(currentStep - 1);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentStep < steps.length - 1) {
            // Validar paso actual antes de avanzar
            if (validateStep(currentStep)) {
                showStep(currentStep + 1);
            } else {
                alert('Por favor completa todos los campos de este paso antes de continuar');
            }
        } else {
            // Último paso: enviar formulario
            if (validateStep(currentStep)) {
                handleSubmit();
            } else {
                alert('Por favor completa todos los campos antes de finalizar');
            }
        }
    });

    // Inicializar mostrando el primer paso
    showStep(0);
});