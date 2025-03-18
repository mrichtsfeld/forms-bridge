// source
import NewBridge from "../../../../src/components/Bridges/NewBridge";
import useDolibarrApi from "../hooks/useDolibarrApi";
import DolibarrTemplateWizard from "./TemplateWizard";

const { TextControl, SelectControl } = wp.components;
const { __ } = wp.i18n;

export default function NewDolibarrBridge({ add, schema }) {
  const [{ api_keys }] = useDolibarrApi();
  const keyOptions = [{ label: "", value: "" }].concat(
    api_keys.map(({ name }) => ({
      value: name,
      label: name,
    }))
  );

  return (
    <NewBridge add={add} schema={schema} Wizard={DolibarrTemplateWizard}>
      {({ data, update }) => (
        <>
          <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
            <SelectControl
              label={__("API Key", "forms-bridge")}
              help={
                api_keys.length === 0
                  ? __("Configure, at least, one API Key on the panel below")
                  : ""
              }
              value={data.api_key || ""}
              onChange={(api_key) => update({ ...data, api_key })}
              options={keyOptions}
              __nextHasNoMarginBottom
              __next40pxDefaultSize
            />
          </div>
          <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
            <TextControl
              label={__("Endpoint", "forms-bridge")}
              value={data.endpoint || ""}
              onChange={(endpoint) => update({ ...data, endpoint })}
              __nextHasNoMarginBottom
              __next40pxDefaultSize
            />
          </div>
        </>
      )}
    </NewBridge>
  );
}
