@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow" x-data="importWizard()">
    <h2 class="text-2xl font-bold mb-4">Importar datos de estudiantes</h2>
    <form method="POST" action="{{ route('admin.importaciones.confirmar') }}" enctype="multipart/form-data" @submit.prevent="submitImport">
        @csrf
        <!-- Step 1: Nivel educativo -->
        <section x-show="step === 1" class="space-y-4">
            <label class="block font-medium">Selecciona el nivel educativo</label>
            <select name="nivel_id" x-model="form.nivel_id" class="w-full border rounded p-2" required>
                <option value="" disabled>-- Selecciona nivel --</option>
                @foreach($niveles as $nivel)
                    <option value="{{ $nivel }}">{{ $nivel }}</option>
                @endforeach
            </select>
        </section>

        <!-- Step 2: Grado y sección -->
        <section x-show="step === 2" class="space-y-4">
            <label class="block font-medium">Selecciona el grado</label>
            <select name="grado_id" x-model="form.grado_id" class="w-full border rounded p-2" required>
                <option value="" disabled>-- Selecciona grado --</option>
                <template x-for="grado in filteredGrados" :key="grado.id">
                    <option :value="grado.id" x-text="grado.nombre"></option>
                </template>
            </select>
            <label class="block font-medium mt-4">Selecciona la sección</label>
            <select name="seccion_id" x-model="form.seccion_id" class="w-full border rounded p-2" required>
                <option value="" disabled>-- Selecciona sección --</option>
                <template x-for="seccion in filteredSecciones" :key="seccion.id">
                    <option :value="seccion.id" x-text="seccion.nombre"></option>
                </template>
            </select>
        </section>

        <!-- Step 3: Cargar archivo Excel -->
        <section x-show="step === 3" class="space-y-4">
            <label class="block font-medium">Archivo Excel a importar</label>
            <input type="file" name="file" @change="handleFile" accept=".xlsx,.xls" class="w-full" required />
        </section>

        <!-- Step 4: Tipo de importación -->
        <section x-show="step === 4" class="space-y-4">
            <label class="block font-medium">Tipo de importación</label>
            <select name="tipo" x-model="form.tipo" class="w-full border rounded p-2" required>
                <option value="" disabled>-- Selecciona tipo --</option>
                <option value="estudiantes">Estudiantes</option>
                <option value="padres">Padres</option>
                <option value="directorio">Directorio</option>
                <option value="siagie">Siagie</option>
                <option value="notas">Notas</option>
            </select>
        </section>

        <!-- Step 5: Vista previa y mapeo de columnas -->
        <section x-show="step === 5" class="space-y-4">
            <h3 class="text-xl font-semibold mb-2">Previsualizar datos y mapear columnas</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border">
                    <thead>
                        <tr class="bg-gray-100">
                            <template x-for="(col, idx) in preview.columns" :key="idx">
                                <th class="border px-2 py-1">
                                    <select x-model="mapping[col]" class="w-full">
                                        <option value="">Ignorar</option>
                                        <option value="dni">DNI</option>
                                        <option value="nombre">Nombre</option>
                                        <option value="apellido">Apellido</option>
                                        <option value="grado_id">Grado</option>
                                        <option value="seccion_id">Sección</option>
                                    </select>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in preview.rows" :key="row.id">
                            <tr>
                                <template x-for="col in preview.columns" :key="col">
                                    <td class="border px-2 py-1" x-text="row[col]"></td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Navigation -->
        <div class="flex justify-between mt-6">
            <button type="button" @click="prevStep" :disabled="step===1" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Anterior</button>
            <template x-if="step < 5">
                <button type="button" @click="nextStep" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Siguiente</button>
            </template>
            <template x-if="step === 5">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Importar</button>
            </template>
        </div>
    </form>
</div>

<script>
function importWizard() {
    return {
        step: 1,
        form: {
            nivel_id: null,
            grado_id: null,
            seccion_id: null,
            tipo: null,
        },
        file: null,
        preview: { columns: [], rows: [] },
        mapping: {},
        gradoSecciones: @json($gradoSecciones),
        get niveles() {
            // Extract distinct niveles from gradoSecciones
            const map = {};
            this.gradoSecciones.forEach(gs => {
                const nivel = gs.grado.nivel;
                const nivelId = gs.grado.nivel_id || `${nivel}-${gs.grado.id}`; // fallback
                if (!map[nivelId]) {
                    map[nivelId] = { id: nivelId, nombre: nivel };
                }
            });
            return Object.values(map);
        },
        get filteredGrados() {
            if (!this.form.nivel_id) return [];
            const seen = {};
            return this.gradoSecciones
                .filter(gs => gs.grado.nivel == this.form.nivel_id)
                .map(gs => ({ id: gs.grado.id, nombre: gs.grado.nombre }))
                .filter(g => !seen[g.id] && (seen[g.id] = true));
        },
        get filteredSecciones() {
            if (!this.form.grado_id) return [];
            const seen = {};
            return this.gradoSecciones
                .filter(gs => gs.grado.id == this.form.grado_id)
                .map(gs => ({ id: gs.seccion.id, nombre: gs.seccion.nombre }))
                .filter(s => !seen[s.id] && (seen[s.id] = true));
        },
        nextStep() {
            if (this.step === 1 && !this.form.nivel_id) return;
            if (this.step === 2 && (!this.form.grado_id || !this.form.seccion_id)) return;
            if (this.step === 3 && !this.file) return;
            if (this.step === 4 && !this.form.tipo) return;
            this.step++;
        },
        prevStep() {
            if (this.step > 1) this.step--;
        },
        handleFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.file = file;
            const formData = new FormData();
            formData.append('file', file);
            fetch('{{ route('admin.importaciones.preview') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                this.preview.columns = data.columns;
                this.preview.rows = data.rows;
                this.mapping = {};
                data.columns.forEach(col => this.mapping[col] = '');
                this.step = 5;
            })
            .catch(err => console.error(err));
        },
        submitImport() {
            const payload = new FormData();
            payload.append('nivel_id', this.form.nivel_id);
            payload.append('grado_id', this.form.grado_id);
            payload.append('seccion_id', this.form.seccion_id);
            payload.append('tipo', this.form.tipo);
            payload.append('file', this.file);
            payload.append('mapping', JSON.stringify(this.mapping));
            // Include original file name from preview response
            if (this.original_name) {
                payload.append('original_name', this.original_name);
            }
            // Include rows to import for processing on server side
            if (this.preview.rows && this.preview.rows.length) {
                payload.append('rows_to_import', JSON.stringify(this.preview.rows));
            }
            fetch('{{ route('admin.importaciones.confirmar') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: payload,
            })
            .then(() => {
                alert('Importación completada');
                window.location.reload();
            })
            .catch(err => console.error(err));
        }
    };
}
</script>
@endsection
