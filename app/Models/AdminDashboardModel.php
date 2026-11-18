<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminDashboardModel extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findPropertyServiceSummary(): array
    {
        $rows = $this->db->table('inmuebles i')
            ->select([
                'i.id AS inmueble_id',
                'i.alias_nombre',
                'i.tipo',
                'i.estado AS estado_inmueble',
                'i.usuario_id',
                'u.nombre AS usuario_nombre',
                'u.apellido AS usuario_apellido',
                'u.activo AS usuario_activo',
                'r.nombre AS usuario_rol',
                'isv.id AS asociacion_id',
                'isv.estado AS estado_asociacion',
                'isv.estado_vinculacion',
                's.nombre AS servicio_nombre',
                's.categoria AS servicio_categoria',
                's.proveedor AS servicio_proveedor',
            ])
            ->join('usuarios u', 'u.id = i.usuario_id', 'left')
            ->join('rol_usuario ru', 'ru.usuario_id = u.id', 'left')
            ->join('roles r', 'r.id = ru.rol_id', 'left')
            ->join('inmueble_servicios isv', 'isv.inmueble_id = i.id', 'left')
            ->join('servicios s', 's.id = isv.servicio_id', 'left')
            ->orderBy('i.id', 'DESC')
            ->orderBy('isv.id', 'ASC')
            ->get()
            ->getResultArray();

        $properties = [];
        foreach ($rows as $row) {
            $propertyId = (int) $row['inmueble_id'];
            if (! isset($properties[$propertyId])) {
                $hasOwner = $row['usuario_id'] !== null;
                $properties[$propertyId] = [
                    'id' => $propertyId,
                    'alias_nombre' => $row['alias_nombre'],
                    'tipo' => $row['tipo'],
                    'estado' => $row['estado_inmueble'],
                    'usuario_nombre' => $hasOwner
                        ? trim((string) $row['usuario_nombre'] . ' ' . (string) $row['usuario_apellido'])
                        : 'Sin usuario asignado',
                    'usuario_rol' => $row['usuario_rol'],
                    'usuario_estado' => ! $hasOwner
                        ? 'Sin asignar'
                        : ((bool) $row['usuario_activo'] ? 'Activa' : 'Baja'),
                    'servicios' => [],
                ];
            }

            if ($row['asociacion_id'] !== null) {
                $properties[$propertyId]['servicios'][] = [
                    'nombre' => $row['servicio_nombre'],
                    'categoria' => $row['servicio_categoria'],
                    'proveedor' => $row['servicio_proveedor'],
                    'estado' => $row['estado_asociacion'],
                    'estado_vinculacion' => $row['estado_vinculacion'],
                ];
            }
        }

        return array_values($properties);
    }
}
