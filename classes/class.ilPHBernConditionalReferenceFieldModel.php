<?php
require_once("./Modules/DataCollection/classes/Fields/Reference/class.ilDclReferenceFieldModel.php");
require_once("./Modules/DataCollection/classes/Helpers/class.ilDclRecordQueryObject.php");

/**
 * Class ilPHBernConditionalReferenceFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHBernConditionalReferenceFieldModel extends ilDclReferenceFieldModel {
	const PROP_LIMIT_PER_USER = "phbe_creference_limit_per_user";
	const PROP_ONLY_ON_VALUES = "phbe_creference_only_on_ref_values";

	/**
	 * @inheritDoc
	 */
	public function __construct($a_id = 0) {
		parent::__construct($a_id);

		$this->setStorageLocationOverride(2);
	}

	/**
	 * @inheritDoc
	 */
	public function getValidFieldProperties() {
		$props = array_merge(parent::getValidFieldProperties(), array(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME, self::PROP_LIMIT_PER_USER, self::PROP_ONLY_ON_VALUES));
		return $props;
	}


	/**
	 * Check validity
	 * @param      $value
	 * @param null $record_id
	 *
	 * @return bool
	 * @throws ilDclInputException
	 */
	public function checkValidity($value, $record_id = NULL) {
		global $ilUser;

		if($this->hasProperty(self::PROP_LIMIT_PER_USER)) {
			$count = 0;
			$table = ilDclCache::getTableCache($this->getTableId());

			$records = $table->getRecords();
			$last_record = null;
			foreach ($records as $record) {
				if($record->getOwner() == $ilUser->getId() &&
					((!$this->getProperty(self::PROP_ONLY_ON_VALUES) && $record->getRecordFieldValue($this->getId()) == $value) || ($value == $this->getProperty(self::PROP_ONLY_ON_VALUES) && $record->getRecordFieldValue($this->getId()) == $this->getProperty(self::PROP_ONLY_ON_VALUES))) &&
					($record->getId() != $record_id || $record_id == 0)
				) {
					$count ++;
					$last_record = $record;
				}
			}

			if($count >= $this->getProperty(self::PROP_LIMIT_PER_USER)) {
				$record_field = ($last_record !== null)?  $last_record->getRecordFieldHTML($this->getId()) : '';
				throw new ilDclInputException(ilDclInputException::TYPE_EXCEPTION, sprintf(ilPHBernConditionalReferencePlugin::getInstance()->txt('only_certain_number_of_entries_are_possible'), $this->getProperty(self::PROP_LIMIT_PER_USER), $record_field));
			}
		}

		return true;
	}
}