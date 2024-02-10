import ConditionalControl from "./ConditionalControl.js";

function ConditionalCheckbox(el, meta) {
	ConditionalControl.call(this, el, meta);
	this.options = Array.from(el.querySelectorAll("input[type='checkbox']"));
	for (const option of this.options) {
		option.addEventListener("change", (ev) => {
			setTimeout(() => {
				el.value = this.options
					.filter((opt) => opt.checked)
					.map((opt) => opt.value);

				el.dispatchEvent(new Event("change"));
			}, 0);
		});
	}

	this.fieldName = this.options[0].getAttribute("name");
	this.type = "checkbox";
	this.el.value = this.options
		.filter((opt) => opt.checked)
		.map((opt) => opt.value);

	Object.defineProperty(this, "defaultValue", {
		writable: false,
		configurable: false,
		enumerable: false,
		value: this.el.value,
	});
}

ConditionalCheckbox.prototype = Object.create(ConditionalControl.prototype);

export default ConditionalCheckbox;
