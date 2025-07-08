import useBackends from "../../hooks/useBackends";
import { useForms } from "../../providers/Forms";
import { prependEmptyOption } from "../../lib/utils";
import { useCredentials } from "../../hooks/useAddon";

const { TextControl, SelectControl } = wp.components;
const { useEffect, useMemo } = wp.element;

export const INTERNALS = [
  "enabled",
  "is_valid",
  "workflow",
  "custom_fields",
  "mutations",
];

export default function BridgeFields({
  data,
  setData,
  schema,
  optionals = false,
  setting = {},
  errors = {},
}) {
  const [backends] = useBackends();
  const backendOptions = useMemo(() => {
    return prependEmptyOption(
      backends
        .map(({ name }) => ({
          label: name,
          value: name,
        }))
        .sort((a, b) => {
          return a.label > b.label ? 1 : -1;
        })
    );
  }, [backends]);

  const [forms] = useForms();
  const formOptions = useMemo(() => {
    return prependEmptyOption(
      forms
        .map(({ _id, title }) => ({
          label: title,
          value: _id,
        }))
        .sort((a, b) => {
          return a.label > b.label ? 1 : -1;
        })
    );
  }, [forms]);

  const [credentials] = useCredentials();
  const credentialOptions = useMemo(() => {
    return prependEmptyOption(
      credentials
        .map(({ name }) => ({ label: name, value: name }))
        .sort((a, b) => (a.label > b.label ? 1 : -1))
    );
  }, [credentials]);

  const fields = useMemo(() => {
    if (!schema) return [];
    return Object.keys(schema.properties)
      .filter((name) => !INTERNALS.includes(name))
      .map((name) => ({
        ...schema.properties[name],
        label: schema.properties[name].name || name,
        name,
      }))
      .map((field) => {
        if (field.name === "form_id") {
          return {
            ...field,
            type: "options",
            options: formOptions,
          };
        } else if (field.name === "backend") {
          return {
            ...field,
            type: "options",
            options: backendOptions,
          };
        } else if (field.name === "credential") {
          return {
            ...field,
            type: "options",
            options: credentialOptions,
          };
        } else if (field.enum) {
          return {
            ...field,
            type: "options",
            options: field.enum.map((value) => ({ label: value, value })),
          };
        } else if (field.$ref) {
          const options = setting[field.$ref] || [];
          return { ...field, type: "options", options };
        }

        return field;
      });
  }, [schema, formOptions, backendOptions, credentialOptions]);

  useEffect(() => {
    const defaults = fields.reduce((defaults, field) => {
      if (field.default && !data[field.name]) {
        defaults[field.name] = field.default;
      }
      return defaults;
    }, {});

    if (Object.keys(defaults).length) {
      setData({ ...data, ...defaults });
    }
  }, [data, fields]);

  return fields
    .filter((field) => !field.default)
    .map((field) => {
      switch (field.type) {
        case "string":
          return (
            <StringField
              error={errors[field.name]}
              label={field.label}
              value={data[field.name] || ""}
              setValue={(value) => setData({ ...data, [field.name]: value })}
            />
          );
        case "options":
          return (
            <OptionsField
              error={errors[field.name]}
              label={field.label}
              value={data[field.name] || ""}
              setValue={(value) => setData({ ...data, [field.name]: value })}
              options={field.options}
              optional={optionals}
            />
          );
      }
    });
}

export function StringField({ label, value, setValue, error }) {
  return (
    <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
      <TextControl
        label={label}
        value={value}
        onChange={setValue}
        help={error}
        __nextHasNoMarginBottom
        __next40pxDefaultSize
      />
    </div>
  );
}

export function OptionsField({
  label,
  options,
  value,
  setValue,
  optional,
  error,
}) {
  if (optional) {
    options = prependEmptyOption(options);
  }

  return (
    <div style={{ flex: 1, minWidth: "150px", maxWidth: "250px" }}>
      <SelectControl
        label={label}
        value={value}
        onChange={setValue}
        options={options}
        help={error}
        __nextHasNoMarginBottom
        __next40pxDefaultSize
      />
    </div>
  );
}
