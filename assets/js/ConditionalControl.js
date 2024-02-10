function ConditionalControl(el, meta) {
	this.el = el;
	this.controlWrap = this.el.parentElement;
	this.conditionalWrap = this.controlWrap.parentElement.parentElement;
	this.type = el.getAttribute("type");
	this.fieldName = el.getAttribute("name");
	this.conditional =
		meta && meta.classList.contains("wpcf7-form-control-conditional");
	this.conditions = {};

	if (this.conditional) {
		this.conditions = meta.dataset.conditions
			.split("&")
			.reduce((acum, chunk) => {
				const [field, val] = chunk.split("=");
				acum[field] = val;
				return acum;
			}, {});
		this.conditionalWrap.classList.add(
			"wpcf7-form-control-conditional-wrap"
		);
	}

	Object.defineProperty(this, "value", {
		get() {
			return this.el.value;
		},
		set(value) {
			this.el.value = value;
		},
	});

	if (this.el.value !== void 0) {
		Object.defineProperty(this, "defaultValue", {
			writable: false,
			configurable: false,
			enumerable: false,
			value: this.el.value,
		});
	}

	Object.defineProperty(this, "visible", {
		get() {
			return this.conditionalWrap.classList.contains("visible");
		},
	});
}

ConditionalControl.prototype.validateConditions = function (state) {
	if (!this.conditional) return true;
	return Object.keys(this.conditions).reduce((acum, field) => {
		const value = Array.isArray(state[field])
			? state[field].join(",")
			: state[field];
		return acum && value == this.conditions[field];
	}, true);
};

ConditionalControl.prototype.updateVisibility = function (
	state,
	initial = false
) {
	// Check visibility based on state
	const isVisible = this.validateConditions(state);
	const hasChanged = this.visible !== isVisible;

	// Update visibility state
	if (isVisible) {
		this.conditionalWrap.classList.add("visible");
		if (!this.controlWrap.contains(this.el)) {
			this.controlWrap.appendChild(this.el);
			this.controlWrap.setAttribute("data-name", this.fieldName);
		}
	} else {
		this.conditionalWrap.classList.remove("visible");
		if (this.controlWrap.contains(this.el)) {
			this.controlWrap.removeChild(this.el);
			this.controlWrap.removeAttribute("data-name");
		}
	}

	if (!hasChanged) return;

	// Emit visibility change
	this.el.dispatchEvent(
		new CustomEvent("show", {
			detail: {
				value: this.visible,
				state: state,
			},
		})
	);
};

ConditionalControl.prototype.on = function (event, callback) {
	this.el.addEventListener(event, callback);
};

ConditionalControl.prototype.off = function (event, callback) {
	this.el.removeEventListener(event, callback);
};

ConditionalControl.prototype.emptyValue = function () {
	this._memValue = this.value;
	switch (this.type) {
		case "text":
			return "wpct-empty";
		case "email":
			return "wpct-empty@mail.com";
		case "tel":
			return "+000000000";
		case "url":
			return "https://wpct-empty.com";
		case "date":
			return "0001-01-01";
		case "number":
			return -1234567890;
		case "checkbox":
		case "select":
		case "radio":
			return ["wpct-empty"];
	}
};

export default ConditionalControl;
