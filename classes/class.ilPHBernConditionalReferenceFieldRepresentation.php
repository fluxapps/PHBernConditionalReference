<?php
require_once('./Modules/DataCollection/classes/Fields/Reference/class.ilDclReferenceFieldRepresentation.php');
require_once('./Modules/DataCollection/classes/Fields/Reference/class.ilDclReferenceFieldModel.php');

/**
 * Class ilPHBernConditionalReferenceFieldRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHBernConditionalReferenceFieldRepresentation extends ilDclReferenceFieldRepresentation {

	/**
	 * @inheritDoc
	 */
	public function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create') {
		$opt = parent::buildFieldCreationInput($dcl, $mode);

		$input = new ilNumberInputGUI(ilPHBernConditionalReferencePlugin::getInstance()->txt('limit_number_per_user'), $this->getPropertyInputFieldId(ilPHBernConditionalReferenceFieldModel::PROP_LIMIT_PER_USER));
		$opt->addSubItem($input);

		$input = new ilNumberInputGUI(ilPHBernConditionalReferencePlugin::getInstance()->txt('only_on_values'), $this->getPropertyInputFieldId(ilPHBernConditionalReferenceFieldModel::PROP_ONLY_ON_VALUES));
		$opt->addSubItem($input);

		return $opt;
	}
}