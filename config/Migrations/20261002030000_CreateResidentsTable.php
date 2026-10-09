<?php
declare(strict_types=1);
use Migrations\BaseMigration;
class CreateResidentsTable extends BaseMigration
{
    /** Creates residents associated with homes. */
    public function change(): void
    {
        $this->table('residentes')->addColumn('resident_code','string',['limit'=>30])->addColumn('vivienda_id','integer')->addColumn('document_type','string',['limit'=>20])->addColumn('document_number','string',['limit'=>30])->addColumn('full_name','string',['limit'=>180])->addColumn('relationship_type','string',['limit'=>30])->addColumn('phone','string',['limit'=>30,'null'=>true])->addColumn('email','string',['limit'=>150,'null'=>true])->addColumn('start_date','date',['null'=>true])->addColumn('end_date','date',['null'=>true])->addColumn('active','boolean',['default'=>true])->addColumn('notes','text',['null'=>true])->addTimestamps()->addIndex(['resident_code'],['unique'=>true])->addIndex(['document_type','document_number'],['unique'=>true])->addIndex(['vivienda_id','active'])->addForeignKey('vivienda_id','viviendas','id',['delete'=>'RESTRICT'])->create();
    }
}
