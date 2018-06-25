<?php
class Table_IdRefSuggestAssoc extends Omeka_Db_Table
{
    public function findByElementId($elementId)
    {
        $select = $this->getSelect()->where('element_id = ?', $elementId);
        return $this->fetchObjects($select);
    }
}