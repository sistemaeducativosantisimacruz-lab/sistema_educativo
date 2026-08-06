import re

with open('resources/views/admin/estudiantes/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Add editData to x-data
content = content.replace(
    "editCodigoEstudiante: '',",
    "editCodigoEstudiante: '',\n        editData: {},"
)

# Update the Edit button click
old_click = """editSexo = editEstudiante.sexo;
                                                    editCodigoEstudiante = editEstudiante.codigo_estudiante || '';
                                                    editTipoMatricula = '{{ $matricula->tipo_matricula ?? 'Normal' }}';"""

new_click = """editSexo = editEstudiante.sexo;
                                                    editCodigoEstudiante = editEstudiante.codigo_estudiante || '';
                                                    editTipoMatricula = '{{ $matricula->tipo_matricula ?? 'Normal' }}';
                                                    editData = {
                                                        dni: editEstudiante.dni || '',
                                                        nombres: editEstudiante.nombres || '',
                                                        apellido_paterno: editEstudiante.apellido_paterno || '',
                                                        apellido_materno: editEstudiante.apellido_materno || '',
                                                        fecha_nacimiento: editEstudiante.fecha_nacimiento ? editEstudiante.fecha_nacimiento.split('T')[0] : '',
                                                        colegio_inicial: editEstudiante.colegio_inicial || '',
                                                        padre_dni: editPadre ? editPadre.dni : '',
                                                        padre_nombres: editPadre ? (editPadre.apellido_paterno + ' ' + (editPadre.apellido_materno || '') + ', ' + editPadre.nombres) : '',
                                                        padre_telefono: editPadre ? editPadre.telefono : '',
                                                        madre_dni: editMadre ? editMadre.dni : '',
                                                        madre_nombres: editMadre ? (editMadre.apellido_paterno + ' ' + (editMadre.apellido_materno || '') + ', ' + editMadre.nombres) : '',
                                                        madre_telefono: editMadre ? editMadre.telefono : '',
                                                        apoderado_dni: editApoderado ? editApoderado.dni : '',
                                                        apoderado_parentesco: editApoderado ? editApoderado.parentesco : '',
                                                        apoderado_nombres: editApoderado ? editApoderado.nombres : '',
                                                        apoderado_apellido_paterno: editApoderado ? editApoderado.apellido_paterno : '',
                                                        apoderado_apellido_materno: editApoderado ? editApoderado.apellido_materno : '',
                                                        apoderado_telefono: editApoderado ? editApoderado.telefono : '',
                                                        apoderado_direccion: editApoderado ? editApoderado.direccion : ''
                                                    };"""

content = content.replace(old_click, new_click)

# Update inputs in the edit modal to use x-model="editData.*"
replacements = {
    ":value=\"editEstudiante ? editEstudiante.dni : ''\"": 'x-model="editData.dni"',
    ":value=\"editEstudiante ? editEstudiante.nombres : ''\"": 'x-model="editData.nombres"',
    ":value=\"editEstudiante ? editEstudiante.apellido_paterno : ''\"": 'x-model="editData.apellido_paterno"',
    ":value=\"editEstudiante ? editEstudiante.apellido_materno : ''\"": 'x-model="editData.apellido_materno"',
    ":value=\"editEstudiante && editEstudiante.fecha_nacimiento ? editEstudiante.fecha_nacimiento.split('T')[0] : ''\"": 'x-model="editData.fecha_nacimiento"',
    ":value=\"editEstudiante ? editEstudiante.colegio_inicial : ''\"": 'x-model="editData.colegio_inicial"',
    
    ":value=\"editPadre ? editPadre.dni : ''\"": 'x-model="editData.padre_dni"',
    ":value=\"editPadre ? (editPadre.apellido_paterno + ' ' + (editPadre.apellido_materno || '') + ', ' + editPadre.nombres) : ''\"": 'x-model="editData.padre_nombres"',
    ":value=\"editPadre ? editPadre.telefono : ''\"": 'x-model="editData.padre_telefono"',
    
    ":value=\"editMadre ? editMadre.dni : ''\"": 'x-model="editData.madre_dni"',
    ":value=\"editMadre ? (editMadre.apellido_paterno + ' ' + (editMadre.apellido_materno || '') + ', ' + editMadre.nombres) : ''\"": 'x-model="editData.madre_nombres"',
    ":value=\"editMadre ? editMadre.telefono : ''\"": 'x-model="editData.madre_telefono"',
    
    ":value=\"editApoderado ? editApoderado.dni : ''\"": 'x-model="editData.apoderado_dni"',
    "name=\"apoderado_parentesco\" class=\"": 'name="apoderado_parentesco" x-model="editData.apoderado_parentesco" class="',
    ":value=\"editApoderado ? editApoderado.nombres : ''\"": 'x-model="editData.apoderado_nombres"',
    ":value=\"editApoderado ? editApoderado.apellido_paterno : ''\"": 'x-model="editData.apoderado_apellido_paterno"',
    ":value=\"editApoderado ? editApoderado.apellido_materno : ''\"": 'x-model="editData.apoderado_apellido_materno"',
    ":value=\"editApoderado ? editApoderado.telefono : ''\"": 'x-model="editData.apoderado_telefono"',
    ":value=\"editApoderado ? editApoderado.direccion : ''\"": 'x-model="editData.apoderado_direccion"',
}

for old, new in replacements.items():
    content = content.replace(old, new)

with open('resources/views/admin/estudiantes/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
