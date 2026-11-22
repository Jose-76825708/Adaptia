@php
    $filas = count($productosIniciales) > 0
        ? $productosIniciales
        : [['planta_id' => '', 'cantidad_meta' => '', 'descuento_porcentaje' => '']];
@endphp

<div class="mt-4" data-product-form>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm text-[#304e42]">
            <thead class="bg-[#edf8e7] text-[#3b5d2d]">
                <tr>
                    <th class="px-3 py-2">Planta</th>
                    <th class="px-3 py-2">Cantidad meta</th>
                    <th class="px-3 py-2">Descuento (%)</th>
                    <th class="px-3 py-2">Acción</th>
                </tr>
            </thead>
            <tbody data-product-rows data-next-index="{{ count($filas) }}">
                @foreach ($filas as $indice => $producto)
                    <tr class="border-t border-[#e4ebdf]">
                        <td class="px-3 py-2">
                            <label for="producto-{{ $indice }}-planta" class="sr-only">Planta de la fila {{ $indice + 1 }}</label>
                            <select id="producto-{{ $indice }}-planta" name="productos[{{ $indice }}][planta_id]" class="w-full rounded-xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2">
                                <option value="">Selecciona una planta</option>
                                @foreach ($plantas as $planta)
                                    <option value="{{ $planta->id }}" @selected((string) ($producto['planta_id'] ?? '') === (string) $planta->id)>{{ $planta->nombre }} (stock {{ $planta->stock_actual }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <label for="producto-{{ $indice }}-cantidad" class="sr-only">Cantidad meta de la fila {{ $indice + 1 }}</label>
                            <input id="producto-{{ $indice }}-cantidad" name="productos[{{ $indice }}][cantidad_meta]" type="number" min="1" value="{{ $producto['cantidad_meta'] ?? '' }}" class="w-full rounded-xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2">
                        </td>
                        <td class="px-3 py-2">
                            <label for="producto-{{ $indice }}-descuento" class="sr-only">Descuento de la fila {{ $indice + 1 }}</label>
                            <input id="producto-{{ $indice }}-descuento" name="productos[{{ $indice }}][descuento_porcentaje]" type="number" min="0" max="100" step="0.01" value="{{ $producto['descuento_porcentaje'] ?? '' }}" class="w-full rounded-xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2">
                        </td>
                        <td class="px-3 py-2"><button type="button" data-remove-producto class="rounded-full border border-[#dce6d7] px-3 py-1 text-xs font-semibold text-[#3b5b42]">Quitar</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <button type="button" data-add-producto class="mt-3 rounded-full border border-[#dce6d7] px-4 py-2 text-sm font-semibold text-[#3b5b42]">Añadir planta</button>

    <template data-product-row>
        <tr class="border-t border-[#e4ebdf]">
            <td class="px-3 py-2">
                <label for="producto-__INDEX__-planta" class="sr-only">Planta de la nueva fila</label>
                <select id="producto-__INDEX__-planta" name="productos[__INDEX__][planta_id]" class="w-full rounded-xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2">
                    <option value="">Selecciona una planta</option>
                    @foreach ($plantas as $planta)
                        <option value="{{ $planta->id }}">{{ $planta->nombre }} (stock {{ $planta->stock_actual }})</option>
                    @endforeach
                </select>
            </td>
            <td class="px-3 py-2">
                <label for="producto-__INDEX__-cantidad" class="sr-only">Cantidad meta de la nueva fila</label>
                <input id="producto-__INDEX__-cantidad" name="productos[__INDEX__][cantidad_meta]" type="number" min="1" class="w-full rounded-xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2">
            </td>
            <td class="px-3 py-2">
                <label for="producto-__INDEX__-descuento" class="sr-only">Descuento de la nueva fila</label>
                <input id="producto-__INDEX__-descuento" name="productos[__INDEX__][descuento_porcentaje]" type="number" min="0" max="100" step="0.01" class="w-full rounded-xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2">
            </td>
            <td class="px-3 py-2"><button type="button" data-remove-producto class="rounded-full border border-[#dce6d7] px-3 py-1 text-xs font-semibold text-[#3b5b42]">Quitar</button></td>
        </tr>
    </template>
</div>

<script>
    document.querySelectorAll('[data-product-form]').forEach((form) => {
        const rows = form.querySelector('[data-product-rows]');
        const template = form.querySelector('[data-product-row]');
        const addButton = form.querySelector('[data-add-producto]');

        addButton.addEventListener('click', () => {
            const index = Number(rows.dataset.nextIndex);
            rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index)));
            rows.dataset.nextIndex = String(index + 1);
        });

        rows.addEventListener('click', (event) => {
            if (event.target.closest('[data-remove-producto]')) {
                const fila = event.target.closest('tr');
                if (rows.children.length > 1) {
                    fila.remove();
                } else {
                    fila.querySelector('select').value = '';
                    fila.querySelectorAll('input').forEach((input) => input.value = '');
                }
            }
        });
    });
</script>
