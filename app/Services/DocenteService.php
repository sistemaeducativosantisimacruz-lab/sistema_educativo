<?php

namespace App\Services;

use App\Models\User;
use App\Models\Docente;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DocenteService
{
    public function crearDocente(array $data)
    {
        return DB::transaction(function () use ($data) {
            $nombres = strtoupper(trim($data['nombres']));
            $apPaterno = strtoupper(trim($data['apellido_paterno']));
            $apMaterno = strtoupper(trim($data['apellido_materno']));

            $user = User::create([
                'name'                 => "{$nombres} {$apPaterno}",
                'email'                => $data['email'] ?? null,
                'dni'                  => $data['dni'],
                'password'             => Hash::make($data['dni']),
                'role_id'              => Role::where('nombre', 'docente')->value('id'),
                'must_change_password' => true,
            ]);

            $docente = Docente::create([
                'user_id'          => $user->id,
                'curso_id'         => null,
                'nivel'            => $data['nivel'] ?? null,
                'tipo'             => $data['tipo'] ?? null,
                'dni'              => $data['dni'],
                'celular'          => $data['celular'] ?? null,
                'nombres'          => $nombres,
                'apellido_paterno' => $apPaterno,
                'apellido_materno' => $apMaterno,
            ]);

            if (($data['tipo'] ?? '') === 'especialista' && isset($data['curso_ids'])) {
                $docente->cursos()->sync($data['curso_ids']);
            }

            return $docente;
        });
    }

    public function actualizarDocente(Docente $docente, array $data)
    {
        return DB::transaction(function () use ($docente, $data) {
            $nombres = strtoupper(trim($data['nombres']));
            $apPaterno = strtoupper(trim($data['apellido_paterno']));
            $apMaterno = strtoupper(trim($data['apellido_materno']));

            $docente->update([
                'dni'              => $data['dni'],
                'nombres'          => $nombres,
                'apellido_paterno' => $apPaterno,
                'apellido_materno' => $apMaterno,
                'celular'          => $data['celular'] ?? null,
                'nivel'            => $data['nivel'] ?? null,
                'tipo'             => $data['tipo'] ?? null,
            ]);

            $nombreCompleto = "{$apPaterno} {$apMaterno} {$nombres}";
            
            $docente->user->update([
                'name'  => $nombreCompleto,
                'email' => $data['email'] ?? null,
                'dni'   => $data['dni'],
                'password' => $docente->user->must_change_password ? Hash::make($data['dni']) : $docente->user->password,
            ]);

            if (($data['tipo'] ?? '') === 'especialista') {
                $docente->cursos()->sync($data['curso_ids'] ?? []);
            } else {
                $docente->cursos()->detach();
            }

            return $docente;
        });
    }
}
