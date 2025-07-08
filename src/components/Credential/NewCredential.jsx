// source
import { useCredentials } from "../../hooks/useAddon";
import CredentialFields from "./Fields";

const { Button, __experimentalSpacer: Spacer } = wp.components;
const { useState, useMemo, useCallback } = wp.element;
const { __ } = wp.i18n;

export default function NewCredential({ add, schema }) {
  const [data, setData] = useState({});

  const [credentials] = useCredentials();
  const names = useMemo(() => {
    return new Set(credentials.map((c) => c.name));
  }, [credentials]);

  const nameConflict = useMemo(() => {
    if (!data.name) return false;
    return names.has(data.name.trim());
  }, [names, data]);

  const create = () => {
    add({ ...data, name: data.name.trim() });
    setData({});
  };

  const validate = useCallback(
    (data) => {
      return Object.keys(schema.properties).reduce((isValid, prop) => {
        const value = data[prop];
        if (schema.properties[prop].pattern) {
          isValid = new RegExp(schema.properties[prop].pattern).test(value);
        }

        return isValid && value;
      });
    },
    [schema]
  );

  const isValid = useMemo(() => {
    return validate(data);
  }, [data]);

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
          data={data}
          setData={setData}
          schema={schema}
          optionals={true}
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
        <Button
          variant="primary"
          onClick={create}
          style={{ width: "150px", justifyContent: "center" }}
          disabled={!isValid || nameConflict}
          __next40pxDefaultSize
        >
          {__("Add", "forms-bridge")}
        </Button>
      </div>
    </div>
  );
}
