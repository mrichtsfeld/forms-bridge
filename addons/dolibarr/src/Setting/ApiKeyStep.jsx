import useDolibarrApi from "../hooks/useDolibarrApi";
import TemplateStep from "../../../../src/components/Templates/Steps/Step";
import Field from "../../../../src/components/Templates/Field";

const { SelectControl } = wp.components;
const { useMemo, useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const fieldsOrder = ["name", "backend", "key"];

export default function ApiKeyStep({ fields, data, setData }) {
  const [{ api_keys }] = useDolibarrApi();
  const apiKeyNames = useMemo(
    () =>
      new Set(
        api_keys.filter(({ backend }) => backend).map(({ name }) => name)
      ),
    [api_keys]
  );

  const [name, setName] = useState(apiKeyNames.has(data.name) ? data.name : "");
  const [newName, setNewName] = useState(data.name || "");

  const keyOptions = [{ label: "", value: "" }].concat(
    Array.from(apiKeyNames).map((name) => ({ label: name, value: name }))
  );

  const apiKey = useMemo(
    () => api_keys.find((key) => key.name === name),
    [api_keys, name]
  );

  useEffect(() => {
    if (apiKey) {
      setData({ ...apiKey });
    } else {
      setData({
        name: null,
        backend: null,
        key: null,
      });
    }
  }, [apiKey]);

  const sortedFields = useMemo(
    () =>
      fields.sort((a, b) => {
        if (!fieldsOrder.includes(a.name)) {
          return 1;
        } else if (!fieldsOrder.includes(b.name)) {
          return -1;
        } else {
          return fieldsOrder.indexOf(a.name) - fieldsOrder.indexOf(b.name);
        }
      }),
    [fields]
  );

  const filteredFields = useMemo(
    () => (name ? [] : sortedFields.filter(({ name }) => name !== "name")),
    [name, sortedFields]
  );

  const nameField = useMemo(
    () => sortedFields.find(({ name }) => name === "name"),
    [sortedFields]
  );

  const nameConflict = useMemo(
    () => (newName && apiKeyNames.has(newName.trim())) || false,
    [apiKeyNames, newName]
  );

  useEffect(() => {
    if (!nameConflict && newName) {
      setData({ name: newName });
    }
  }, [newName]);

  useEffect(() => {
    if (name) {
      setNewName("");
    }
  }, [name]);

  return (
    <TemplateStep
      name={__("API Key", "forms-bridge")}
      description={__(
        "Configure the Dolibarr REST API credentials",
        "forms-bridge"
      )}
    >
      <SelectControl
        label={__("Reuse an API key", "forms-bridge")}
        value={name}
        options={keyOptions}
        onChange={(value) => setName(value)}
        __nextHasNoMarginBottom
      />
      {!name && (
        <Field
          error={
            nameConflict
              ? __("This name is already in use", "forms-bridge")
              : false
          }
          data={{
            ...nameField,
            value: newName || "",
            onChange: setNewName,
          }}
        />
      )}
      {filteredFields.map((field) => (
        <Field
          data={{
            ...field,
            value: data[field.name] || "",
            onChange: (value) => setData({ [field.name]: value }),
          }}
        />
      ))}
    </TemplateStep>
  );
}
