import ConditionalControl from "./ConditionalControl.js";
import ConditionalCheckbox from "./ConditionalCheckbox.js";

function Form(el) {
	this.el = el;
	this.state = {};
	this.conditions = {};

	this.controls = Array.from(this.el.querySelectorAll(".wpcf7-form-control"))
		.map((el) => Form.getControl(el))
		.filter((control) => control);

	for (const control of this.controls) {
		control.on("show", ({ detail }) => {
			if (detail.value) {
				this.setStateField(control);
			} else {
				delete detail.state[control.fieldName];
			}
		});

		control.updateVisibility({
			[control.fieldName]: control.value,
			...this.getState(),
		});

		if (control.visible) {
			this.setStateField(control);
		}

		control.on("change", () => this.updateVisibility(control));

		Object.keys(control.conditions).forEach(
			(field) => (this.conditions[field] = true)
		);
	}

	this.el.addEventListener("wpcf7beforesubmit", (ev) =>
		this.beforeSubmit(ev)
	);

	this.el.addEventListener("wpcf7reset", (ev) => this.reset(ev));
}

Form.getControl = function (el) {
	const meta = el.parentElement.nextElementSibling;
	let type = el.getAttribute("type");
	if (!type) {
		if (el.classList.contains("wpcf7-checkbox")) {
			type = "checkbox";
		} else if (el.classList.contains("wpcf7-acceptance")) {
			type = "acceptance";
		}
	}

	switch (type) {
		case "submit":
		case "acceptance":
		case "hidden":
			return null;
		case "checkbox":
			return new ConditionalCheckbox(el, meta);
		default:
			return new ConditionalControl(el, meta);
	}
};

Form.prototype.setStateField = function (control) {
	Object.defineProperty(this.state, control.fieldName, {
		enumerable: true,
		configurable: true,
		get() {
			return control.value;
		},
		set(value) {
			if (control.value != value) {
				control.value = value;
			}
		},
	});
};

Form.prototype.updateVisibility = function (control) {
	if (control && !this.conditions[control.fieldName]) return;

	for (const control of this.controls) {
		if (!control.conditional) continue;
		control.updateVisibility(this.state);
	}
};

Form.prototype.getState = function () {
	return this.controls.reduce((acum, control) => {
		const exists = Object.prototype.hasOwnProperty.call(
			this.state,
			control.fieldName
		);

		if (control.visible && exists) {
			acum[control.fieldName] = control.value;
		}

		return acum;
	}, {});
};

Form.prototype.beforeSubmit = function ({ detail }) {
	this.controls.forEach((control) => {
		if (!control.conditional) return;

		if (!detail.formData.has(control.fieldName)) {
			detail.formData.append(control.fieldName, control.emptyValue());
		}
	});
};

Form.prototype.reset = function ({ detail }) {
	this.controls.forEach(
		(control) => (control.value = control.defaultValue || "")
	);
	setTimeout(() => {
		this.updateVisibility();
		window.scrollTo({
			top: Math.max(0, this.el.offsetTop - 20),
			behavior: "smooth",
		});
	}, 1e3);
	console.log(detail);
};

export default Form;
