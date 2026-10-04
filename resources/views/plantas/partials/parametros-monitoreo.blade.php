<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-2 border-b border-[#ecedea] pb-2">
        <div class="flex items-center gap-3">
            <div class="w-2 h-6 rounded-full bg-[#629f22]"></div>
            <h2 class="text-[#103928] font-bold text-[1.3em]">Parámetros de Monitoreo IoT</h2>
        </div>
        <p class="text-sm text-[#718071]">
            Configura los rangos de referencia por especie. Son opcionales mientras se verifican los valores adecuados.
            Si ingresas un límite, completa también el otro extremo del rango.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="flex flex-col gap-4 rounded-xl border border-[#ecedea] p-5">
            <h3 class="font-semibold text-[#304e42]">Humedad del suelo (%)</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="humedad_suelo_min" class="text-sm font-medium text-[#304e42]">Mínima</label>
                    <input type="number" step="0.01" min="0" max="100" name="humedad_suelo_min" id="humedad_suelo_min"
                           class="rounded-[10px] border-2 @error('humedad_suelo_min') border-red-500 @else border-[#ecedea] @enderror bg-[#f3f5f3] p-3 outline-none focus:border-[#629f22]"
                           value="{{ old('humedad_suelo_min', isset($find) ? $find->humedad_suelo_min : '') }}" placeholder="0–100">
                    @error('humedad_suelo_min') <span class="text-sm font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="humedad_suelo_max" class="text-sm font-medium text-[#304e42]">Máxima</label>
                    <input type="number" step="0.01" min="0" max="100" name="humedad_suelo_max" id="humedad_suelo_max"
                           class="rounded-[10px] border-2 @error('humedad_suelo_max') border-red-500 @else border-[#ecedea] @enderror bg-[#f3f5f3] p-3 outline-none focus:border-[#629f22]"
                           value="{{ old('humedad_suelo_max', isset($find) ? $find->humedad_suelo_max : '') }}" placeholder="0–100">
                    @error('humedad_suelo_max') <span class="text-sm font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-[#ecedea] p-5">
            <h3 class="font-semibold text-[#304e42]">Temperatura ambiental (°C)</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="temperatura_min" class="text-sm font-medium text-[#304e42]">Mínima</label>
                    <input type="number" step="0.01" min="-40" max="80" name="temperatura_min" id="temperatura_min"
                           class="rounded-[10px] border-2 @error('temperatura_min') border-red-500 @else border-[#ecedea] @enderror bg-[#f3f5f3] p-3 outline-none focus:border-[#629f22]"
                           value="{{ old('temperatura_min', isset($find) ? $find->temperatura_min : '') }}" placeholder="-40–80">
                    @error('temperatura_min') <span class="text-sm font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="temperatura_max" class="text-sm font-medium text-[#304e42]">Máxima</label>
                    <input type="number" step="0.01" min="-40" max="80" name="temperatura_max" id="temperatura_max"
                           class="rounded-[10px] border-2 @error('temperatura_max') border-red-500 @else border-[#ecedea] @enderror bg-[#f3f5f3] p-3 outline-none focus:border-[#629f22]"
                           value="{{ old('temperatura_max', isset($find) ? $find->temperatura_max : '') }}" placeholder="-40–80">
                    @error('temperatura_max') <span class="text-sm font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-[#ecedea] p-5">
            <h3 class="font-semibold text-[#304e42]">Humedad ambiental (%)</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="humedad_ambiental_min" class="text-sm font-medium text-[#304e42]">Mínima</label>
                    <input type="number" step="0.01" min="0" max="100" name="humedad_ambiental_min" id="humedad_ambiental_min"
                           class="rounded-[10px] border-2 @error('humedad_ambiental_min') border-red-500 @else border-[#ecedea] @enderror bg-[#f3f5f3] p-3 outline-none focus:border-[#629f22]"
                           value="{{ old('humedad_ambiental_min', isset($find) ? $find->humedad_ambiental_min : '') }}" placeholder="0–100">
                    @error('humedad_ambiental_min') <span class="text-sm font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="humedad_ambiental_max" class="text-sm font-medium text-[#304e42]">Máxima</label>
                    <input type="number" step="0.01" min="0" max="100" name="humedad_ambiental_max" id="humedad_ambiental_max"
                           class="rounded-[10px] border-2 @error('humedad_ambiental_max') border-red-500 @else border-[#ecedea] @enderror bg-[#f3f5f3] p-3 outline-none focus:border-[#629f22]"
                           value="{{ old('humedad_ambiental_max', isset($find) ? $find->humedad_ambiental_max : '') }}" placeholder="0–100">
                    @error('humedad_ambiental_max') <span class="text-sm font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-[#ecedea] p-5">
            <h3 class="font-semibold text-[#304e42]">Iluminación (lux)</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="luz_min" class="text-sm font-medium text-[#304e42]">Mínima</label>
                    <input type="number" step="0.01" min="0" max="65535" name="luz_min" id="luz_min"
                           class="rounded-[10px] border-2 @error('luz_min') border-red-500 @else border-[#ecedea] @enderror bg-[#f3f5f3] p-3 outline-none focus:border-[#629f22]"
                           value="{{ old('luz_min', isset($find) ? $find->luz_min : '') }}" placeholder="0–65535">
                    @error('luz_min') <span class="text-sm font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="luz_max" class="text-sm font-medium text-[#304e42]">Máxima</label>
                    <input type="number" step="0.01" min="0" max="65535" name="luz_max" id="luz_max"
                           class="rounded-[10px] border-2 @error('luz_max') border-red-500 @else border-[#ecedea] @enderror bg-[#f3f5f3] p-3 outline-none focus:border-[#629f22]"
                           value="{{ old('luz_max', isset($find) ? $find->luz_max : '') }}" placeholder="0–65535">
                    @error('luz_max') <span class="text-sm font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>
</div>
