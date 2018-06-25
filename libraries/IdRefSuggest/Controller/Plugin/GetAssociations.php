<?php
class IdRefSuggest_Controller_Plugin_GetAssociations extends Zend_Controller_Plugin_Abstract {

	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		$db = get_db();

		// Set NULL modules to default. Some routes do not have a default
        // module, which resolves to NULL.
        $module = $request->getModuleName();
        if (is_null($module)) {
            $module = 'default';
        }
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        // Include all routes (route + controller + actions) that render an
        // element form, including actions requested via AJAX.
        $routes = array(
            array(
                'module' => 'default',
                'controller' => 'items',
                'actions' => array('add', 'edit', 'element-form', 'change-type'),
            ),
        );

        foreach ($routes as $route) {
            // Set the autosuggest if the current action matches a defined route.
            if ($route['module'] === $module
                    && $route['controller'] === $controller
                    && in_array($action, $route['actions'])
                ) {
                // Iterate the elements that are assigned to a suggest endpoint.
				$assocs = $db->getTable('IdRefSuggestAssoc')->findAll();
				foreach($assocs as $assoc) {
                    // Add the autosuggest JavaScript to the JS queue.
                    $view = Zend_Registry::get('view');
                    $view->headScript()->captureStart();
?>
jQuery(document).bind('omeka:elementformload', function(event) {
    jQuery('#element-<?php echo $assoc->element_id; ?> textarea').autocomplete({
        minLength: 3,
        source: function(request, response) {
			let source = <?php echo json_encode(WEB_ROOT.'/admin/id-ref-suggest/term/search'); ?>;
			let url = source + "?suggest_type=<?php echo $assoc->suggest_type; ?>&term=" + request.term;
            console.log(url);
			jQuery.get(url, function(data) {
				response(data);
			});
    	}
    });
    jQuery('#element-<?php echo $assoc->element_id; ?> .use-html').addClass("remove-use-html");
    <?php if ($assoc->suggest_type == "persname") { ?>
    let warning = '<p class="refsuggest-warning">(<?php echo __("Suggestions are formatted this way : Lastname Name"); ?>)</p>';
    jQuery('#element-<?php echo $assoc->element_id; ?> .refsuggest-warning').after(warning);
    <?php } ?>
});
<?php
                    $view->headScript()->captureEnd();
                }

                // Once the JavaScript is applied there is no need to continue
                // looping the defined routes.
                break;
            }
        }
	}
}