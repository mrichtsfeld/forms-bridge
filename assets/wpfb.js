window.wpfb = (() => {
  const _registry = new Map();
  const registry = {
    get: (event) => {
      const listeners = _registry.get(event);
      if (listeners) {
        return listeners;
      }
      return _registry.set(event, new WeakMap()).get(event);
    },
  };

  const events = {
    get: (event, callback) => {
      return registry.get(event).get(callback);
    },
    set: (event, callback, listener) => {
      registry.get(event).set(callback, listener);
    },
    delete: (event, callback) => {
      registry.get(event).delete(callback);
    },
  };

  const el = document.createElement("div");
  el.id = "wpfb";
  el.style.visibility = "hidden";
  el.setAttribute("aria-hidden", "true");

  document.addEventListener("DOMContentLoaded", () => {
    document.body.appendChild(el);
  });

  const ns = (event, prefix) => `${prefix}:${event}`;

  return {
    on: function (event, callback, type = "event") {
      event = ns(event, type);
      events.set(event, callback, ({ detail: data }) =>
        callback.call(null, data)
      );
      el.addEventListener(event, events.get(event, callback));
    },
    off: function (event, callback, type = "event") {
      event = ns(event, type);
      el.removeEventListener(event, events.get(event, callback));
      events.delete(event, callback);
    },
    emit: function (event, data, type = "event") {
      event = ns(event, type);
      el.dispatchEvent(new CustomEvent(event, { detail: data }));
    },
    bus: function (name, data) {
      const bus = { data };
      this.emit(name, bus, "bus");
      return bus.data;
    },
    join: function (name, callback) {
      this.on(name, callback, "bus");
    },
    leave: function (name, callback) {
      this.off(name, callback, "bus");
    },
  };
})();
