<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServiInmuebleSchema extends Migration
{
    public function up(): void
    {
        $this->forge->addField('id');
        $this->forge->addField([
            'calle'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'numero'     => ['type' => 'VARCHAR', 'constraint' => 20],
            'piso_dpto'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'ciudad'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->createTable('direcciones', true);

        $this->forge->addField('id');
        $this->forge->addField([
            'alias_nombre' => ['type' => 'VARCHAR', 'constraint' => 150],
            'tipo'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'superficie'   => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'estado'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'direccion_id' => ['type' => 'INT'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addUniqueKey('direccion_id');
        $this->forge->addForeignKey('direccion_id', 'direcciones', 'id', '', 'RESTRICT');
        $this->forge->createTable('inmuebles', true);

        $this->forge->addField('id');
        $this->forge->addField([
            'nombre'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'unidad_medida_std' => ['type' => 'VARCHAR', 'constraint' => 30],
            'proveedor'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'categoria'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->createTable('servicios', true);

        $this->forge->addField('id');
        $this->forge->addField([
            'inmueble_id'          => ['type' => 'INT'],
            'servicio_id'          => ['type' => 'INT'],
            'nro_medidor'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'titular_inmueble'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'estado'               => ['type' => 'VARCHAR', 'constraint' => 30],
            'nro_cuenta_contrato'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'cod_pago_electronico' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'domicilio_facturacion' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('inmueble_id');
        $this->forge->addKey('servicio_id');
        $this->forge->addForeignKey('inmueble_id', 'inmuebles', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('servicio_id', 'servicios', 'id', '', 'RESTRICT');
        $this->forge->createTable('inmueble_servicios', true);

        $this->forge->addField('id');
        $this->forge->addField([
            'inmueble_servicio_id'       => ['type' => 'INT'],
            'periodo_facturacion'        => ['type' => 'DATE'],
            'fecha_emision'              => ['type' => 'DATE'],
            'fecha_primer_vencimiento'   => ['type' => 'DATE'],
            'fecha_segundo_vencimiento'  => ['type' => 'DATE', 'null' => true],
            'monto_total'                => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'consumo_periodo'            => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'lectura_anterior'           => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'lectura_actual'             => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'estado_factura'             => ['type' => 'VARCHAR', 'constraint' => 30],
            'fecha_pago_realizado'       => ['type' => 'DATE', 'null' => true],
            'numero_comprobante'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'medio_pago'                 => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'monto_abonado'              => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'mora'                       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'created_at'                 => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                 => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('inmueble_servicio_id');
        $this->forge->addForeignKey('inmueble_servicio_id', 'inmueble_servicios', 'id', '', 'RESTRICT');
        $this->forge->createTable('facturas', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('facturas');
        $this->forge->dropTable('inmueble_servicios');
        $this->forge->dropTable('servicios');
        $this->forge->dropTable('inmuebles');
        $this->forge->dropTable('direcciones');
    }
}
