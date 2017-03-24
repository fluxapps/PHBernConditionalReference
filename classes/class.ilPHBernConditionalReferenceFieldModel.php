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
	const PROP_HIDE_ON_FIELD = "phbe_creference_hide_on_field";
	const PROP_HIDE_ON_FIELD_VALUE = "phbe_creference_hide_on_field_value";
	const PROP_ID_SORTING = "phbe_creference_sort_by_id";
	const PROP_ROLE_RESTRICTIONS = "phbe_creference_role_restrictions";

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
		$props = array_merge(parent::getValidFieldProperties(), array(ilDclBaseFieldModel::PROP_PLUGIN_HOOK_NAME, self::PROP_LIMIT_PER_USER, self::PROP_ONLY_ON_VALUES, self::PROP_HIDE_ON_FIELD, self::PROP_HIDE_ON_FIELD_VALUE, self::PROP_ID_SORTING, self::PROP_ROLE_RESTRICTIONS));
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
		global $ilUser, $rbacreview;

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

				if($this->getProperty(self::PROP_LIMIT_PER_USER) == 1) {
					$message = sprintf(ilPHBernConditionalReferencePlugin::getInstance()->txt('only_certain_number_of_entry_is_possible'), $record_field);
				} else {
					$message = sprintf(ilPHBernConditionalReferencePlugin::getInstance()->txt('only_certain_number_of_entries_are_possible'), $this->getProperty(self::PROP_LIMIT_PER_USER), $record_field);
				}
				throw new ilDclInputException(ilDclInputException::CUSTOM_MESSAGE, $message);
			}
		}

		if($restrictions = $this->getProperty(ilPHBernConditionalReferenceFieldModel::PROP_ROLE_RESTRICTIONS)) {
			$assigned_roles = $rbacreview->assignedRoles($ilUser->getId());
			foreach ($restrictions as $restriction) {
				if (in_array($value, explode(',', $restriction['values'])) && empty(array_intersect($assigned_roles, explode(',', $restriction['roles'])))) {
					throw new ilDclInputException(ilDclInputException::CUSTOM_MESSAGE, 'Value Restricted to certain roles');
				}
			}
		}

		return true;
	}
}