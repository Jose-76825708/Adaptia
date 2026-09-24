// Wizard de perfil animado
document.addEventListener('DOMContentLoaded', function() {
    const wizardTrigger = document.getElementById('wizard-trigger');
    if (!wizardTrigger) return;

    // Crear el modal del wizard
    const wizardModal = document.createElement('div');
    wizardModal.innerHTML = `
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" id="profile-wizard-modal">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold text-[#0f3c2b]">Actualizar mi perfil</h2>
                    <button id="wizard-close" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-6" id="wizard-steps">
                    <!-- Paso 1: Información básica -->
                    <div class="wizard-step active">
                        <h3 class="text-lg font-bold text-[#0f3c2b] mb-4">Paso 1: Tu espacio y luz</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tamaño adulto de planta preferido</label>
                                <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                        id="wizard-tamanio">
                                    <option value="">Selecciona una opción</option>
                                    <option value="pequena">Pequeña (Estante/Mesa)</option>
                                    <option value="mediana">Mediana (Habitación)</option>
                                    <option value="grande">Grande (Jardín/Patio)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Luz requerida en tu espacio</label>
                                <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                        id="wizard-luz">
                                    <option value="">Selecciona una opción</option>
                                    <option value="baja">Baja (Sombra)</option>
                                    <option value="media">Media (Luz indirecta)</option>
                                    <option value="alta">Alta (Mucha luz)</option>
                                    <option value="siempre_en_el_sol">Siempre al sol</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 2: Cuidado y ambiente -->
                    <div class="wizard-step">
                        <h3 class="text-lg font-bold text-[#0f3c2b] mb-4">Paso 2: Cuidado y ambiente</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nivel de experiencia en cuidado de plantas</label>
                                <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                        id="wizard-nivel">
                                    <option value="">Selecciona una opción</option>
                                    <option value="principiante">Principiante</option>
                                    <option value="intermedio">Intermedio</option>
                                    <option value="experto">Experto</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de ambiente</label>
                                <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                        id="wizard-ambiente">
                                    <option value="">Selecciona una opción</option>
                                    <option value="interiores">Interiores</option>
                                    <option value="exteriores">Exteriores</option>
                                    <option value="ambos">Ambos</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Preferencias adicionales -->
                    <div class="wizard-step">
                        <h3 class="text-lg font-bold text-[#0f3c2b] mb-4">Paso 3: Preferencias adicionales</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Frecuencia de riego que prefieres</label>
                                <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                        id="wizard-riego">
                                    <option value="">Selecciona una opción</option>
                                    <option value="diario">Diario</option>
                                    <option value="cada_3_dias">Cada 3 días</option>
                                    <option value="semanal">Semanal</option>
                                    <option value="quincenal">Quincenal</option>
                                    <option value="mensualmente">Mensualmente</option>
                                </select>
                            </div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="wizard-toxicidad" type="checkbox" value="" class="w-4 h-4 text-[#7cb22b] bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-[#7cb22b]">
                                </div>
                                <div class="ml-3 text-start">
                                    <label for="wizard-toxicidad" class="text-sm font-medium text-gray-700">
                                        Prefiero evitar plantas tóxicas (por seguridad de mascotas o niños)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <div id="wizard-progress" class="flex justify-between text-sm text-gray-500 mb-3">
                        <span>Paso <span id="wizard-current-step">1</span> de 3</span>
                        <span id="wizard-completion">33% completado</span>
                    </div>
                    <div class="flex w-full space-x-3">
                        <button id="wizard-prev"
                                class="px-4 py-2 bg-gray-200 text-gray-600 rounded-md hover:bg-gray-300 disabled:opacity-50"
                                disabled>
                            Anterior
                        </button>
                        <button id="wizard-next"
                                class="px-6 py-2 bg-[#7cb22b] text-white rounded-md hover:bg-[#6eab26]">
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(wizardModal);

    const modal = document.getElementById('profile-wizard-modal');
    const closeBtn = document.getElementById('wizard-close');
    const prevBtn = document.getElementById('wizard-prev');
    const nextBtn = document.getElementById('wizard-next');
    const steps = document.querySelectorAll('.wizard-step');
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
        nextBtn.textContent = stepIndex === steps.length - 1 ? 'Finalizar' : 'Siguiente';
    }

    function openWizard() {
        modal.classList.remove('hidden');
        // Prellenar con valores actuales si existen
        if (window.userData && window.userData.perfil) {
            const perfil = window.userData.perfil;
            document.getElementById('wizard-tamanio').value = perfil.tamaño_adulto || '';
            document.getElementById('wizard-luz').value = perfil.luz_requerida || '';
            document.getElementById('wizard-riego').value = perfil.frecuencia_riego || '';
            document.getElementById('wizard-nivel').value = perfil.nivel_cuidado || '';
            document.getElementById('wizard-ambiente').value = perfil.tipo_ambiente || '';
            document.getElementById('wizard-toxicidad').checked = perfil.toxicidad;
        }
        showStep(0);
    }

    function closeWizard() {
        modal.classList.add('hidden');
    }

    function handleNext() {
        if (currentStep < steps.length - 1) {
            showStep(currentStep + 1);
        } else {
            // Recopilar datos y guardar
            const formData = {
                tamanho_adulto: document.getElementById('wizard-tamanio').value,
                luz_requerida: document.getElementById('wizard-luz').value,
                frecuencia_riego: document.getElementById('wizard-riego').value,
                nivel_cuidado: document.getElementById('wizard-nivel').value,
                tipo_ambiente: document.getElementById('wizard-ambiente').value,
                toxicidad: document.getElementById('wizard-toxicidad').checked ? 1 : 0
            };

            // Validar que todos los campos requeridos estén llenos
            const requiredFields = ['tamanio_adulto', 'luz_requerida', 'frecuencia_riego', 'nivel_cuidado', 'tipo_ambiente'];
            const missingFields = requiredFields.filter(field => !formData[field]);

            if (missingFields.length > 0) {
                alert('Por favor completa todos los campos requeridos');
                return;
            }

            // Aquí iría la llamada AJAX para guardar el perfil
            // Por ahora simulamos el guardado y recargamos la página
            alert('Perfil actualizado correctamente');
            closeWizard();
            location.reload();
        }
    }

    function handlePrev() {
        if (currentStep > 0) {
            showStep(currentStep - 1);
        }
    }

    // Event listeners
    wizardTrigger.addEventListener('click', function(e) {
        e.preventDefault();
        openWizard();
    });

    closeBtn.addEventListener('click', closeWizard);
    prevBtn.addEventListener('click', handlePrev);
    nextBtn.addEventListener('click', handleNext);

    // Clic fuera del modal para cerrar
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeWizard();
        }
    });

    // Tecla Escape para cerrar
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeWizard();
        }
    });
});