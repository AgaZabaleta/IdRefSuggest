<?php
$db = get_db();
$sql = "
SELECT es.name AS element_set_name,
    e.id AS element_id,
    e.name AS element_name,
    it.name AS item_type_name
FROM {$db->ElementSet} es
JOIN {$db->Element} e ON es.id = e.element_set_id
LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id
LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id
WHERE es.record_type IS NULL OR es.record_type = 'Item'
ORDER BY es.name, it.name, e.name";
$request = $db->prepare($sql);
$request->execute();

$elements = $request->fetchAll();
$element_types = array('' => __("Please select an item field."));
foreach ($elements as $element) {
    $group = $element['item_type_name']
        ? __('Item Type') . ': ' . __($element['item_type_name'])
        : __($element['element_set_name']);
    $value = __($element['element_name']);
    $element_types[$group][$element['element_id']] = $value;
}

$suggest_index = array(
    '' => __("Select ..."),
    'persname' => __("Person name"),
    'subjectheading' => __("Subject (Rameau)"),
    'del' => __("Delete suggestion"),
);

$assocs = $db->getTable('IdRefSuggestAssoc')->findAll();

queue_js_file("IdRefSuggest");
echo head_js();
?>

<div class="field">
    <div id="add-field">
        <?php echo get_view()->formSelect('field', null, array('id' => 'field'), $element_types); ?>
        <input type="button" id="add" value="<?php echo __("Add a suggestion"); ?>">
    </div>
    <p class="explanation">
        <?php echo __('Select the kind of suggestion you want to apply'); ?>
    </p>
    <div id="associations">
        <?php 
        if($assocs) {
            foreach ($assocs as $assoc) {
                $default = $assoc->suggest_type;
                $element = get_db()->getTable('Element')->find($assoc->element_id);
                ?>
        <div class="columns alpha">
            <?php echo get_view()->formLabel('element-'.$assoc->element_id, $element->name); ?>
        </div>
                <?php
                echo get_view()->formSelect(
                    'element-'.$assoc->element_id, 
                    $default, 
                    array('id' => 'suggest-'.$assoc->element_id), 
                    $suggest_index
                ); 
            }
        }
        ?>
    </div>
</div>