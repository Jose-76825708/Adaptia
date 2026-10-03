function initializeProfileWizard() {
    const stepsContainer = document.getElementById('wizard-steps');
    const steps = Array.from(document.querySelectorAll('.wizard-step'));
    const prevBtn = document.getElementById('wizard-prev');
    const nextBtn = document.getElementById('wizard-next');
    const currentStepSpan = document.getElementById('wizard-current-step');
    const totalStepsSpan = document.getElementById('wizard-total-steps');
    const completionSpan = document.getElementById('wizard-completion');
    const progressBar = document.getElementById('wizard-progress-bar');
    const progressFill = progressBar?.firstElementChild;

    if (
        !stepsContainer ||
        steps.length === 0 ||
        !prevBtn ||
        !nextBtn ||
        !currentStepSpan ||
        !totalStepsSpan ||
        !completionSpan ||
        !progressBar ||
        !progressFill
    ) {
        return;
    }

    const transitionDuration = 360;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let currentStep = Math.max(steps.findIndex(step => step.classList.contains('active')), 0);
    let isTransitioning = false;
    let isSaving = false;

    function updateProgress() {
        const percentage = Math.round(((currentStep + 1) / steps.length) * 100);

        currentStepSpan.textContent = String(currentStep + 1);
        totalStepsSpan.textContent = String(steps.length);
        completionSpan.textContent = `${percentage}% completado`;
        progressBar.setAttribute('aria-valuenow', String(percentage));
        progressFill.style.width = `${percentage}%`;
        prevBtn.disabled = currentStep === 0 || isTransitioning || isSaving;
        nextBtn.disabled = isTransitioning || isSaving;
        nextBtn.textContent = currentStep === steps.length - 1 ? 'Actualizar perfil' : 'Siguiente';
    }

    function setStepAccessibility(activeIndex) {
        steps.forEach((step, index) => {
            const isActive = index === activeIndex;
            step.setAttribute('aria-hidden', String(!isActive));
            step.inert = !isActive;
        });
    }

    function validateStep(stepIndex) {
        const requiredSelects = steps[stepIndex].querySelectorAll('select[required]');
        let valid = true;

        requiredSelects.forEach(select => {
            if (!select.value) {
                valid = false;
                select.classList.add('border-red-500');
            } else {
                select.classList.remove('border-red-500');
            }
        });

        return valid;
    }

    function setLoading(isLoading) {
        isSaving = isLoading;

        if (isLoading) {
            nextBtn.dataset.originalText = nextBtn.textContent;
            nextBtn.disabled = true;
            prevBtn.disabled = true;
            nextBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Guardando...';
        } else {
            nextBtn.textContent = nextBtn.dataset.originalText || 'Actualizar perfil';
            updateProgress();
        }
    }

    async function handleSubmit() {
        setLoading(true);

        const formData = {
            tamaño_adulto: document.getElementById('wizard-tamanio').value,
            luz_requerida: document.getElementById('wizard-luz').value,
            frecuencia_riego: document.getElementById('wizard-riego').value,
            tipo_ambiente: document.getElementById('wizard-ambiente').value,
            nivel_cuidado: document.getElementById('wizard-nivel').value,
            estetica: document.getElementById('wizard-estetica').value,
            toxicidad: document.getElementById('wizard-toxicidad').checked ? 1 : 0
        };

        const requiredFields = [
            'tamaño_adulto',
            'luz_requerida',
            'frecuencia_riego',
            'tipo_ambiente',
            'nivel_cuidado',
            'estetica'
        ];
        const missingFields = requiredFields.filter(field => !formData[field]);

        if (missingFields.length > 0) {
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
                let errorMessage = 'Error al actualizar el perfil';

                if (errorData.message) {
                    errorMessage = errorData.message;
                } else if (errorData.errors) {
                    errorMessage = Object.values(errorData.errors)[0][0];
                }

                alert(errorMessage);
                setLoading(false);
                return;
            }

            const result = await response.json();

            if (result.success) {
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

    function showStep(stepIndex, direction) {
        if (
            isTransitioning ||
            stepIndex < 0 ||
            stepIndex >= steps.length ||
            stepIndex === currentStep
        ) {
            return;
        }

        const previousStep = steps[currentStep];
        const nextStep = steps[stepIndex];
        const exitClass = direction > 0 ? 'is-exiting-left' : 'is-exiting-right';
        const enterClass = direction > 0 ? 'enter-from-right' : 'enter-from-left';

        currentStep = stepIndex;
        setStepAccessibility(currentStep);
        updateProgress();

        if (reducedMotion) {
            previousStep.classList.remove('active');
            nextStep.classList.add('active');
            return;
        }

        isTransitioning = true;
        updateProgress();
        previousStep.classList.remove('active');
        previousStep.classList.add(exitClass);
        nextStep.classList.add(enterClass);
        void nextStep.offsetWidth;
        nextStep.classList.add('active');
        nextStep.classList.remove(enterClass);
        updateProgress();

        window.setTimeout(() => {
            previousStep.classList.remove(exitClass);
            isTransitioning = false;
            updateProgress();
        }, transitionDuration + 20);
    }

    prevBtn.addEventListener('click', () => {
        showStep(currentStep - 1, -1);
    });

    nextBtn.addEventListener('click', () => {
        if (!validateStep(currentStep)) {
            alert('Por favor completa todos los campos de este paso antes de continuar');
            return;
        }

        if (currentStep < steps.length - 1) {
            showStep(currentStep + 1, 1);
        } else {
            handleSubmit();
        }
    });

    setStepAccessibility(currentStep);
    updateProgress();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProfileWizard, { once: true });
} else {
    initializeProfileWizard();
}
