import TemplateStep from "../../../../src/components/Templates/Steps/Step";
import TemplateField from "../../../../src/components/Templates/Field";

const { useMemo } = wp.element;
const { __ } = wp.i18n;

const fieldsOrder = ["name"];

export default function BridgeStep({ fields, data, setData }) {
  const lists = data._lists || [];

  const listOptions = useMemo(
    () =>
      lists.map(({ id, name }) => ({
        value: id,
        label: name,
      })),
    [lists]
  );

  const sortedFields = useMemo(
    () =>
      fields.sort((a, b) => {
        if (!fieldsOrder.includes(a.name)) {
          return 1;
        } else if (!fieldsOrder.includes(b.name)) {
          return -1;
        } else {
          fieldsOrder.indexOf(a.name) - fieldsOrder.indexOf(b.name);
        }
      }),
    [fields]
  );

  const listIdsField = useMemo(
    () => sortedFields.find(({ name }) => name === "lists"),
    [sortedFields]
  );

  const filteredFields = useMemo(
    () => sortedFields.filter(({ name }) => name !== "lists"),
    [sortedFields]
  );

  return (
    <TemplateStep
      name={__("Bridge", "forms-bridge")}
      description={__("Configure the bridge", "forms-bridge")}
    >
      {filteredFields.map((field) => (
        <TemplateField
          data={{
            ...field,
            value: data[field.name] || "",
            onChange: (value) => setData({ [field.name]: value }),
          }}
        />
      ))}
      <TemplateField
        data={{
          ...listIdsField,
          value: data.lists || "",
          type: "options",
          options: listOptions,
          onChange: (lists) => setData({ lists }),
          multiple: true,
          description: !lists.length
            ? __(
                "There is no active mailing list. This can happens due to a wrong backend connexion, or because you have not created any list yet. Please, fix this issue before createing new bridges.",
                "forms-bridge"
              )
            : null,
        }}
      />
    </TemplateStep>
  );
}
