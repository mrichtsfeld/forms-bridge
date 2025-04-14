import TemplateStep from "../../../../src/components/Templates/Steps/Step";
import TemplateField from "../../../../src/components/Templates/Field";

const { useMemo, useEffect } = wp.element;
const { __ } = wp.i18n;

const fieldsOrder = ["name"];

const API_FIELDS = ["user_id", "product_id", "tag_ids", "team_id", "list_ids"];

export default function BridgeStep({ fields, data, setData }) {
  const users = data._users || [];
  const products = data._products || [];
  const tags = data._tags || [];
  const teams = data._teams || [];
  const lists = data._lists || [];

  const userOptions = useMemo(
    () =>
      users.map(({ id, email }) => ({
        value: id,
        label: email,
      })),
    [users]
  );

  const productOptions = useMemo(
    () =>
      products.map(({ id, name }) => ({
        value: id,
        label: name,
      })),
    [products]
  );

  const tagOptions = useMemo(
    () =>
      tags.map(({ id, name }) => ({
        value: id,
        label: name,
      })),
    [tags]
  );

  const teamOptions = useMemo(
    () =>
      teams.map(({ id, name }) => ({
        value: id,
        label: name,
      })),
    [teams]
  );

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

  const standardFields = useMemo(
    () => sortedFields.filter(({ name }) => !API_FIELDS.includes(name)),
    [sortedFields]
  );

  const apiFields = useMemo(
    () =>
      sortedFields
        .filter(({ name }) => API_FIELDS.includes(name))
        .map((field) => {
          if (field.name === "user_id") {
            return {
              ...field,
              type: "options",
              options: userOptions,
            };
          } else if (field.name === "product_id") {
            return {
              ...field,
              type: "options",
              options: productOptions,
            };
          } else if (field.name === "tag_ids") {
            return {
              ...field,
              type: "options",
              options: tagOptions,
              multiple: true,
            };
          } else if (field.name === "team_id") {
            return {
              ...field,
              type: "options",
              options: teamOptions,
            };
          } else if (field.name === "list_ids") {
            return {
              ...field,
              type: "options",
              options: listOptions,
              multiple: true,
            };
          }
        }),
    [sortedFields]
  );

  useEffect(() => {
    const defaults = {};

    if (productOptions.length === 1) {
      defaults.product_id = productOptions[0].value;
    }

    if (userOptions.length === 1) {
      defaults.user_id = userOptions[0].value;
    }

    if (teamOptions.length === 1) {
      defaults.team_id = teamOptions[0].value;
    }

    setData(defaults);
  }, [productOptions, userOptions]);

  return (
    <TemplateStep
      name={__("Bridge", "forms-bridge")}
      description={__("Configure the bridge", "forms-bridge")}
    >
      {standardFields.map((field) => (
        <TemplateField
          data={{
            ...field,
            value: data[field.name] || "",
            onChange: (value) => setData({ [field.name]: value }),
          }}
        />
      ))}
      {apiFields.map((field) => (
        <TemplateField
          data={{
            ...field,
            value: data[field.name] || "",
            onChange: (value) => setData({ [field.name]: value }),
            description:
              field.options.length === 0
                ? __(
                    "It seems there is no values for this field. Please, check your backend connection",
                    "forms-bridge"
                  )
                : null,
          }}
        />
      ))}
    </TemplateStep>
  );
}
