// source
import Bridge from "../../../../src/components/Bridges/Bridge";
import NewZohoBridge from "./NewBridge";

const { TextControl } = wp.components;
const { __ } = wp.i18n;

export default function ZohoBridge({ data, update, remove }) {
  return (
    <Bridge
      data={data}
      update={update}
      remove={remove}
      schema={["name", "backend", "form_id", "endpoint", "scope"]}
      template={({ add, schema }) => (
        <NewZohoBridge add={(data) => add(data)} schema={schema} />
      )}
    >
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
    </Bridge>
  );
}
