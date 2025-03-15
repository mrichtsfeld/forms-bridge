// source
import NewBridge from "../../../../src/components/Bridges/NewBridge";
import ZohoTemplateWizard from "./TemplateWizard";

const { TextControl } = wp.components;
const { __ } = wp.i18n;

export default function NewZohoBridge({ add, schema }) {
  return (
    <NewBridge add={add} schema={schema} Wizard={ZohoTemplateWizard}>
      {({ data, update }) => (
        <>
          <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
            <TextControl
              label={__("Endpoint", "forms-bridge")}
              value={data.endpoint || ""}
              onChange={(endpoint) => update({ ...data, endpoint })}
              __nextHasNoMarginBottom
              __next40pxDefaultSize
            />
          </div>
          <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
            <TextControl
              label={__("Scope", "forms-bridge")}
              value={data.scope || ""}
              onChange={(scope) => update({ ...data, scope })}
              __nextHasNoMarginBottom
              __next40pxDefaultSize
            />
          </div>
        </>
      )}
    </NewBridge>
  );
}
