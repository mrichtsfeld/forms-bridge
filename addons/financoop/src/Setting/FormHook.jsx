// source
import FormHook from "../../../../src/components/FormHooks/FormHook";
import NewRestFormHook from "./NewFormHook";

const { TextControl } = wp.components;
const { __ } = wp.i18n;

export default function FinanCoopFormHook({ data, update, remove }) {
  return (
    <FormHook
      data={data}
      update={update}
      remove={remove}
      schema={["name", "backend", "form_id", "endpoint"]}
      template={({ add, schema }) => (
        <NewRestFormHook add={add} schema={schema} />
      )}
    >
      {({ data, update }) => (
        <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
          <TextControl
            label={__("Endpoint", "forms-bridge")}
            value={data.endpoint || ""}
            onChange={(endpoint) => update({ ...data, endpoint })}
            __nextHasNoMarginBottom
            __next40pxDefaultSize
          />
        </div>
      )}
    </FormHook>
  );
}
