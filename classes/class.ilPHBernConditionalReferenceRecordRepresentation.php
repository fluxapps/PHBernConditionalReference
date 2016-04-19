<?php
require_once('./Modules/DataCollection/classes/Fields/Reference/class.ilDclReferenceRecordRepresentation.php');

/**
 * Class ilPHBernConditionalReferenceRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHBernConditionalReferenceRecordRepresentation extends ilDclReferenceRecordRepresentation {

	/**
	 * @inheritDoc
	 */
	public function getConfirmationHTML() {
		if($this->record_field->getValue() == '') {
			return false;
		}
		return parent::getConfirmationHTML();
	}
}