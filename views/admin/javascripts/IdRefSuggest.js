window.onload = function() {
	const add_button = document.getElementById("add");
	const select_field = document.getElementById("field");
	const associations = document.getElementById("associations");
	const suggest_types = [
		{name: "Select ...", value: ""},
		{name: "Person name", value: "persname"},
		{name: "Subject (Rameau)", value: "subjectheading"},
		{name: "Delete suggestion", value: "del"}
	];

	add_button.onclick = function() {
		let field_name = select_field.options[select_field.selectedIndex].innerHTML;
		let field_id = select_field.value;
		let test = document.getElementById("suggest-"+field_id);

		if (test === null) {
			if (field_id !== '') {
				let field_div = document.createElement("div");
				field_div.className = "columns alpha";
				field_label = document.createElement("label");
				field_label.setAttribute("for", "element-"+field_id);
				field_label.innerHTML = field_name;
				field_div.appendChild(field_label);
				associations.appendChild(field_div);

				let field_select = document.createElement("select");
				field_select.setAttribute("name", "element-"+field_id);
				field_select.setAttribute("id", "suggest-"+field_id);
				for (x in suggest_types) {
					let option = document.createElement("option");
					option.setAttribute("value", suggest_types[x].value);
					option.innerHTML = suggest_types[x].name;
					field_select.appendChild(option);
				}
				associations.appendChild(field_select);
			}
		}
	}
}