<?php
/**
 * IdRefSuggest
 *
 * An Omeka plugin to link item fields to the IdRef database
 *
 * @package IdRefSuggest
 */

class IdRefSuggestPlugin extends Omeka_Plugin_AbstractPlugin {
    protected $_hooks = array(
    	'admin_head',
    	'initialize',
    	'install',
    	'uninstall',
    	'config_form',
    	'config'
    );

    public function hookConfigForm() {
        require dirname(__FILE__) . '/config_form.php';
    }

    public function hookConfig($args) {
    	foreach ($args['post'] as $name => $suggest_type) {
    		list($prefix, $id) = explode("-", $name);
    		if (!empty($suggest_type) && $prefix == "element") {
                if($suggest_type != "del") {
                    $assoc = new IdRefSuggestAssoc;
                    $assoc->element_id = $id;
                    $assoc->suggest_type = $suggest_type;
                    $assoc->save();
                } else {
                    $assocs = $this->_db->getTable('IdRefSuggestAssoc')->findByElementId($id);
                    foreach ($assocs as $assoc) {
                        $assoc->delete();
                    }
                }
    		}
    	}
    }

    public function hookInstall() {
    	$sql ="
    	CREATE TABLE `{$this->_db->IdRefSuggestAssoc}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `element_id` int(10) unsigned NOT NULL,
            `suggest_type` TINYTEXT NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `element_id` (`element_id`)
        ) ENGINE=INNODB  DEFAULT CHARSET=utf8
        ";

    	$request = $this->_db->prepare($sql);
    	$request->execute();
    }

    public function hookUninstall() {
    	$sql = "DROP TABLE IF EXISTS {$this->_db->IdRefSuggestAssoc}";
    	$request = $this->_db->prepare($sql);
    	$request->execute();
    }

    public function hookInitialize() {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new IdRefSuggest_Controller_Plugin_GetAssociations);
    }

    public function hookAdminHead() {
        queue_css_file('IdRefSuggestStyle');

        $assocs = $this->_db->getTable('IdRefSuggestAssoc')->findAll();
        foreach ($assocs as $assoc) {
            $element = get_db()->getTable('Element')->find($assoc->element_id);
            add_filter(array('ElementForm', 'Item', $element->getElementSet()->name, $element->name), array($this,'addWarning'));
        }
    }

    public function addWarning($components, $args) {
        $warning = __("(This element makes suggestions based on the IdRef database)");
        $components['description'] .= '<p class="refsuggest-warning">'.$warning.'</p>';

        return $components;
    }
}