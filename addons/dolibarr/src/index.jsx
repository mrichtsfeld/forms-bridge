// source
import Addon from "./Addon";

const { createRoot } = wp.element;
const { __ } = wp.i18n;

function componentsReducer({ name, component }) {
  switch (name) {
    case "WorkflowJobs":
      return () => <h1>Hello, World!</h1>;
    default:
      return component;
  }
}

wpfb.join("addons", ({ data: registry }) => {
  registry["dolibarr"] = __("dolibarr", "forms-bridge");

  const root = document.createElement("div");
  root.style.visibility = "hidden";
  document.body.appendChild(root);

  createRoot(root).render(<Addon />);
});

wpfb.join("component", function ({ data }) {
  data.component = componentsReducer(data) || data.component;
});
