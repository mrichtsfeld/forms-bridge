import { StringField, OptionsField } from "../Bridge/Fields";
const { useEffect, useMemo } = wp.element;

export default function CredentialFields({
  data,
  setData,
  schema,
  optionals = false,
  errors,
}) {
  const fields = useMemo(() => {
    if (!schema) return [];

    return Object.keys(schema.properties)
      .map((name) => ({
        ...schema.properties[name],
        label: schema.properties[name].name || name,
        name,
      }))
      .map((field) => {
        if (field.enum) {
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
  }, [schema]);

  useEffect(() => {
    const defaults = fields.reduce((defaults, field) => {
      if (field.default && !data[field.name]) {
        defaults[field.name] = field.defaut;
      }
      return defaults;
    }, {});

    if (Object.keys(defaults).length) {
      setData({ ...data, ...defaults });
    }
  }, [fields]);

  return fields
    .filter((field) => !field.default)
    .map((field) => {
      switch (field.type) {
        case "string":
          return (
            <StringField
              label={field.label}
              value={data[field.name]}
              setValue={(value) => setData({ ...data, [field.name]: value })}
              error={errors[field.name]}
            />
          );
        case "options":
          return (
            <OptionsField
              label={field.label}
              value={data[field.name]}
              setValue={(value) => setData({ ...data, [field.name]: value })}
              options={field.options}
              optional={optionals}
              error={errors[field.name]}
            />
          );
      }
    });
}
