// source
import RemoveButton from "../RemoveButton";
import { useCredentials } from "../../hooks/useAddon";
import CredentialFields from "./Fields";

const { __experimentalSpacer: Spacer } = wp.components;
const { useState, useEffect, useMemo, useCallback } = wp.element;
const { __ } = wp.i18n;

export default function Credential({ data, update, remove, schema }) {
  const [state, setState] = useState({ ...data });

  const [credentials] = useCredentials();
  const names = useMemo(() => {
    return new Set(credentials.map((c) => c.name));
  }, [credentials]);

  const nameConflict = useMemo(() => {
    if (!state.name) return false;
    return data.name !== state.name.trim() && names.has(state.name.trim());
  }, [names, state.name]);

  const validate = useCallback(
    (data) => {
      return !!Object.keys(schema.properties).reduce((isValid, prop) => {
        const value = data[prop];

        if (schema.properties[prop].pattern) {
          isValid = new RegExp(schema.properties[prop].pattern).test(value);
        }

        return isValid && value;
      }, true);
    },
    [schema]
  );

  const isValid = useMemo(() => {
    return validate(state) && !nameConflict;
  }, [state, nameConflict]);

  useEffect(() => {
    if (isValid) update(state);
  }, [isValid, state]);

  useEffect(() => {
    setState(data);
  }, [data.name]);

  return (
    <div
      style={{
        padding: "calc(24px) calc(32px)",
        width: "calc(100% - 64px)",
        backgroundColor: "rgb(245, 245, 245)",
      }}
    >
      <div
        style={{
          display: "flex",
          gap: "1em",
          flexWrap: "wrap",
        }}
      >
        <CredentialFields
          data={state}
          setData={setState}
          schema={schema}
          errors={{
            name: nameConflict
              ? __("This name is already in use", "forms-bridge")
              : false,
          }}
        />
      </div>
      <Spacer paddingY="calc(8px)" />
      <div
        style={{
          display: "flex",
          gap: "0.5rem",
        }}
      >
        <RemoveButton onClick={() => remove(data)}>
          {__("Remove", "forms-bridge")}
        </RemoveButton>
      </div>
    </div>
  );
}
