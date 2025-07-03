<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPpnBiayaAdminToTransaction extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transaction', [
            'ppn' => [
                'type'       => 'DOUBLE',
                'null'       => true, 
                'after'      => 'ongkir', 
            ],
            'biaya_admin' => [
                'type'       => 'DOUBLE',
                'null'       => true, 
                'after'      => 'ppn', 
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transaction', ['ppn', 'biaya_admin']);
    }
}