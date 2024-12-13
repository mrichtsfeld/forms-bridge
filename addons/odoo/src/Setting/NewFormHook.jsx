// vendor
import React from "react";
import { TextControl, SelectControl } from "@wordpress/components";

// source
import NewFormHook from "../../../../src/components/FormHooks/NewFormHook";
import useOdooApi from "../hooks/useOdooSetting";

export default function NewOdooFormHook({ add }) {
  const __ = wp.i18n.__;

  const [{ databases }] = useOdooApi();
  const dbOptions = [{ label: "", value: "" }].concat(
    databases.map(({ name }) => ({
      label: name,
      value: name,
    }))
  );

  return (
    <NewFormHook add={add}>
      {({ data, update }) => (
        <>
          <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
            <TextControl
              label={__("Model", "forms-bridge")}
              value={data.model || ""}
              onChange={(model) => update({ ...data, model })}
              __nextHasNoMarginBottom
            />
          </div>
          <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
            <SelectControl
              label={__("Database", "forms-bridge")}
              value={data.database}
              onChange={(name) => {
                const db = databases.find((db) => db.name === name);
                update({ ...data, database: name, backend: db.backend });
              }}
              options={dbOptions}
              __nextHasNoMarginBottom
            />
          </div>
        </>
      )}
    </NewFormHook>
  );
}
