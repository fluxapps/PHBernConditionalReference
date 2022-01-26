<#1>
<?php
$result = $ilDB->query('select
	rf.*, sl.*
from
	il_dcl_field f
inner join il_dcl_field_prop fp on
	(fp.field_id = f.id
	AND fp.name = \'plugin_hook_name\')
inner join il_dcl_record_field rf on 
	(rf.field_id = f.id)
inner join il_dcl_stloc2_value sl on
	(sl.record_field_id = rf.id)
where
	f.datatype_id = 12
	AND fp.value = \'PHBernConditionalReference\';');

while ($rec = $ilDB->fetchAssoc($result)) {
    if ($rec['value'] > 0) {
        $result2 = $ilDB->query('SELECT * FROM il_dcl_stloc1_value sl WHERE sl.record_field_id = ' . $rec['record_field_id']);
        $rec2 = $ilDB->fetchAssoc($result2);
        if (!$rec2) {
            $record_field = ilDclCache::getRecordFieldCache(
                ilDclCache::getRecordCache($rec['record_id']),
                ilDclCache::getFieldCache($rec['field_id'])
            );
            $record_field->setValue($rec['value']);
            if ($record_field->getId()) {
                $record_field->doUpdate();
            } else {
                $record_field->doCreate();
            }
        }
    }
}
?>