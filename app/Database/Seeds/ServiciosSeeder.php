<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

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
                'unidad_medida_std' => 'Mbps',
                'proveedor'         => 'Río Net',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mbps',
                'proveedor'         => 'AgilNet',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mbps',
                'proveedor'         => 'Fastnet',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mbps',
                'proveedor'         => 'Zielo Internet',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mbps',
                'proveedor'         => 'R.N. Online',
                'categoria'         => 'Internet',
            ],
        ];

        $tabla = $this->db->table('servicios');

        foreach ($servicios as $servicio) {
            $existe = $tabla
                ->where('nombre', $servicio['nombre'])
                ->where('unidad_medida_std', $servicio['unidad_medida_std'])
                ->where('proveedor', $servicio['proveedor'])
                ->where('categoria', $servicio['categoria'])
                ->countAllResults();

            if ($existe === 0) {
                $tabla->insert($servicio);
            }
        }
    }
}

