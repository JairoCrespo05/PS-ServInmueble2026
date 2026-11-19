<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class ServiciosSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            [
                'nombre'            => 'Electricidad',
                'unidad_medida_std' => 'kWh',
                'proveedor'         => 'EDERSA',
                'categoria'         => 'Electricidad',
            ],
            [
                'nombre'            => 'Agua corriente',
                'unidad_medida_std' => 'm3',
                'proveedor'         => 'Aguas Rionegrinas S.A.',
                'categoria'         => 'Agua',
            ],
            [
                'nombre'            => 'Gas natural',
                'unidad_medida_std' => 'm3',
                'proveedor'         => 'Camuzzi Gas del Sur',
                'categoria'         => 'Gas',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mes',
                'proveedor'         => 'Río Net',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mes',
                'proveedor'         => 'AgilNet',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mes',
                'proveedor'         => 'Fastnet',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mes',
                'proveedor'         => 'Zielo Internet',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mes',
                'proveedor'         => 'R.N. Online',
                'categoria'         => 'Internet',
            ],
        ];

        $tabla = $this->db->table('servicios');

        foreach ($servicios as $servicio) {
            $existentes = $tabla
                ->where('nombre', $servicio['nombre'])
                ->where('proveedor', $servicio['proveedor'])
                ->where('categoria', $servicio['categoria'])
                ->get()
                ->getResultArray();

            if (count($existentes) > 1) {
                throw new RuntimeException('Hay tipos de servicio duplicados para el proveedor ' . $servicio['proveedor'] . '.');
            }

            if ($existentes === []) {
                if (! $tabla->insert($servicio)) {
                    throw new RuntimeException('No se pudo crear el tipo de servicio ' . $servicio['nombre'] . '.');
                }

                continue;
            }

            if ($existentes[0]['unidad_medida_std'] === $servicio['unidad_medida_std']) {
                continue;
            }

            if (
                $servicio['categoria'] !== 'Internet'
                || $existentes[0]['unidad_medida_std'] !== 'Mbps'
                || $servicio['unidad_medida_std'] !== 'Mes'
            ) {
                throw new RuntimeException('La unidad del tipo de servicio no coincide con la semilla para ' . $servicio['proveedor'] . '.');
            }

            if (! $tabla->where('id', $existentes[0]['id'])->update([
                'unidad_medida_std' => $servicio['unidad_medida_std'],
            ])) {
                throw new RuntimeException('No se pudo corregir la unidad del servicio de Internet ' . $servicio['proveedor'] . '.');
            }
        }
    }
}

