<?php
declare(strict_types=1);
use Migrations\BaseMigration;
class CreateParkingManagementTables extends BaseMigration
{
    /** Creates parking, assignment, and monthly payment tables. */
    public function change(): void
    {
        $this->table('parqueaderos')->addColumn('code','string',['limit'=>30])->addColumn('vehicle_type','string',['limit'=>10])->addColumn('location','string',['limit'=>120,'null'=>true])->addColumn('covered','boolean',['default'=>false])->addColumn('status','string',['limit'=>20,'default'=>'available'])->addColumn('notes','text',['null'=>true])->addTimestamps()->addIndex(['code'],['unique'=>true])->create();
        $this->table('asignaciones_parqueaderos')->addColumn('parqueadero_id','integer')->addColumn('copropietario_id','integer')->addColumn('vehicle_plate','string',['limit'=>15,'null'=>true])->addColumn('vehicle_description','string',['limit'=>120,'null'=>true])->addColumn('monthly_fee','decimal',['precision'=>12,'scale'=>2,'default'=>0])->addColumn('start_date','date')->addColumn('end_date','date',['null'=>true])->addColumn('active','boolean',['default'=>true])->addColumn('notes','text',['null'=>true])->addTimestamps()->addIndex(['parqueadero_id','active'])->addForeignKey('parqueadero_id','parqueaderos','id',['delete'=>'RESTRICT'])->addForeignKey('copropietario_id','copropietarios','id',['delete'=>'RESTRICT'])->create();
        $this->table('pagos_parqueaderos')->addColumn('asignacion_parqueadero_id','integer')->addColumn('period','string',['limit'=>7])->addColumn('amount_due','decimal',['precision'=>12,'scale'=>2])->addColumn('amount_paid','decimal',['precision'=>12,'scale'=>2,'default'=>0])->addColumn('due_date','date')->addColumn('payment_date','date',['null'=>true])->addColumn('status','string',['limit'=>20,'default'=>'pending'])->addColumn('payment_reference','string',['limit'=>80,'null'=>true])->addColumn('notes','text',['null'=>true])->addTimestamps()->addIndex(['asignacion_parqueadero_id','period'],['unique'=>true])->addForeignKey('asignacion_parqueadero_id','asignaciones_parqueaderos','id',['delete'=>'RESTRICT'])->create();
    }
}
