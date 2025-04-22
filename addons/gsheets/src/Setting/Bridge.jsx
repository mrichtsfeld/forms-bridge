// source
import Bridge from "../../../../src/components/Bridges/Bridge";
import NewGSsheetsBridge from "./NewBridge";
import { useSpreadsheets } from "../providers/Spreadsheets";

const { TextControl, SelectControl } = wp.components;
const { __ } = wp.i18n;

export default function GSheetsBridge({ data, update, remove }) {
  const spreadsheets = useSpreadsheets();
  const sheetOptions = [{ label: "", value: "" }].concat(
    spreadsheets.map(({ title, id }) => ({
      label: title,
      value: id,
    }))
  );

  return (
    <Bridge
      data={data}
      update={update}
      remove={remove}
      schema={["name", "form_id", "spreadsheet", "tab"]}
      template={({ add, schema }) => (
        <NewGSsheetsBridge add={add} schema={schema} />
      )}
    >
      {({ data, update }) => (
        <>
          <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
            <SelectControl
              label={__("Spreadsheet", "forms-bridge")}
              value={data.spreadsheet || ""}
              onChange={(spreadsheet) => update({ ...data, spreadsheet })}
              options={sheetOptions}
              __nextHasNoMarginBottom
              __next40pxDefaultSize
            />
          </div>
          <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
            <TextControl
              label={__("Tab name", "forms-bridge")}
              value={data.tab || ""}
              onChange={(tab) => update({ ...data, tab })}
              __nextHasNoMarginBottom
              __next40pxDefaultSize
            />
          </div>
        </>
      )}
    </Bridge>
  );
}
