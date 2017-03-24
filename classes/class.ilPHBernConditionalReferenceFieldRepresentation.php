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
	protected $pl;


	/**
	 * ilPHBernConditionalReferenceFieldRepresentation constructor.
	 *
	 * @param ilDclBaseFieldModel $field
	 */
	public function __construct(ilDclBaseFieldModel $field) {
		$this->pl = ilPHBernConditionalReferencePlugin::getInstance();

		parent::__construct($field);
	}


	/**
	 * @param ilPropertyFormGUI $form
	 * @param int               $record_id
	 *
	 * @return ilMultiSelectInputGUI|ilSelectInputGUI|null
	 */
	public function getInputField(ilPropertyFormGUI $form, $record_id = 0) {
		global $DIC;
		$tpl = $DIC['tpl'];
		$rbacreview = $DIC['rbacreview'];
		$ilUser = $DIC['ilUser'];

		$input = parent::getInputField($form, $record_id);

		$options = $input->getOptions();
		if($this->field->getProperty(ilPHBernConditionalReferenceFieldModel::PROP_ID_SORTING)) {
			unset($options['']);
			ksort($options);
			$input->setOptions($options);
		}

		if($restrictions = $this->field->getProperty(ilPHBernConditionalReferenceFieldModel::PROP_ROLE_RESTRICTIONS)) {
			$assigned_roles = $rbacreview->assignedRoles($ilUser->getId());
			foreach ($restrictions as $restriction) {
				if (empty(array_intersect($assigned_roles, explode(',', $restriction['roles'])))) {
					foreach (explode(',', $restriction['values']) as $value) {
						unset ($options[$value]);
					}
					$input->setOptions($options);
				}
			}
		}

		if($this->field->hasProperty(ilPHBernConditionalReferenceFieldModel::PROP_HIDE_ON_FIELD)) {
			$field_id = $this->field->getProperty(ilPHBernConditionalReferenceFieldModel::PROP_HIDE_ON_FIELD);
			$field_value = $this->field->getProperty(ilPHBernConditionalReferenceFieldModel::PROP_HIDE_ON_FIELD_VALUE);

			$script = '$("#field_'.$field_id.'")
			.change(function () {
				if($("#field_'.$field_id.'").val() == "'.$field_value.'") {
					$("#field_'.$this->field->getId().'").val("");
					$("#il_prop_cont_field_'.$this->field->getId().'").hide();
				} else {
					$("#il_prop_cont_field_'.$this->field->getId().'").show();
				}
			})
			.change();';
			$tpl->addOnLoadCode($script);

			if(isset($_POST['field_'.$field_id]) && $_POST['field_'.$field_id] == $field_value) {
				$input->setRequired(false);
			}
		}



		return $input;
	}


	/**
	 * @inheritDoc
	 */
	public function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create') {
		$opt = parent::buildFieldCreationInput($dcl, $mode);
		
		$input = new ilNumberInputGUI($this->pl->txt('limit_number_per_user'), $this->getPropertyInputFieldId(ilPHBernConditionalReferenceFieldModel::PROP_LIMIT_PER_USER));
		$opt->addSubItem($input);

		$input = new ilNumberInputGUI($this->pl->txt('only_on_values'), $this->getPropertyInputFieldId(ilPHBernConditionalReferenceFieldModel::PROP_ONLY_ON_VALUES));
		$opt->addSubItem($input);

		$input = new ilSelectInputGUI($this->pl->txt('hide_on_field'), $this->getPropertyInputFieldId(ilPHBernConditionalReferenceFieldModel::PROP_HIDE_ON_FIELD));

		$fields = ilDclCache::getTableCache($this->field->getTableId())->getFields();
		$options = array(''=>'');
		foreach($fields as $field) {
			$options[$field->getId()] = $field->getTitle();
		}
		$input->setOptions($options);
		$opt->addSubItem($input);

		$input = new ilTextInputGUI(ilPHBernConditionalReferencePlugin::getInstance()->txt('hide_on_field_value'), $this->getPropertyInputFieldId(ilPHBernConditionalReferenceFieldModel::PROP_HIDE_ON_FIELD_VALUE));
		$opt->addSubItem($input);

		$input = new ilCheckboxInputGUI($this->pl->txt('sort_by_id'), $this->getPropertyInputFieldId(ilPHBernConditionalReferenceFieldModel::PROP_ID_SORTING));
		$opt->addSubItem($input);

		$multiinput = new srDclContentImporterMultiLineInputGUI($this->pl->txt('role_restrictions'),$this->getPropertyInputFieldId(ilPHBernConditionalReferenceFieldModel::PROP_ROLE_RESTRICTIONS));
		$multiinput->setInfo("1) Werte, 2) Rollen");
		$multiinput->setTemplateDir(ilDclContentImporterPlugin::getInstance()->getDirectory());

		$values_input = new ilTextInputGUI('', 'values');
		$multiinput->addInput($values_input);

		$roles_input = new ilTextInputGUI('', 'roles');
		$multiinput->addInput($roles_input);

		$opt->addSubItem($multiinput);


		return $opt;
	}
}