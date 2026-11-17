<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInvoiceServicePeriodUniqueKey extends Migration
{
    private const KEY_NAME = 'uq_factura_servicio_periodo';

    public function up(): void
    {
        $this->forge->addUniqueKey(
            ['inmueble_servicio_id', 'periodo_facturacion'],
            self::KEY_NAME,
        );
        $this->forge->processIndexes('facturas');
    }

    public function down(): void
    {
        $this->forge->dropKey('facturas', self::KEY_NAME);
    }
}
