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
                'proveedor'         => 'Energia del Centro',
                'categoria'         => 'Electricidad',
            ],
            [
                'nombre'            => 'Agua corriente',
                'unidad_medida_std' => 'm3',
                'proveedor'         => 'Aguas de Cordoba',
                'categoria'         => 'Agua',
            ],
            [
                'nombre'            => 'Gas natural',
                'unidad_medida_std' => 'm3',
                'proveedor'         => 'Gas Centro',
                'categoria'         => 'Gas',
            ],
            [
                'nombre'            => 'Internet hogar',
                'unidad_medida_std' => 'Mbps',
                'proveedor'         => 'Conecta Net',
                'categoria'         => 'Internet',
            ],
            [
                'nombre'            => 'Expensas',
                'unidad_medida_std' => 'ARS',
                'proveedor'         => 'Administracion del Consorcio',
                'categoria'         => 'Expensas',
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
