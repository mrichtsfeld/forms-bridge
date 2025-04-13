import TemplateStep from "../../../../src/components/Templates/Steps/Step";
import TemplateField from "../../../../src/components/Templates/Field";

const { useMemo } = wp.element;
const { __ } = wp.i18n;

const fieldsOrder = ["name"];

const API_FIELDS = [
  "listIds",
  "includeListIds",
  "product",
  "products",
  "pipeline",
];

export default function BridgeStep({ fields, data, setData }) {
  const lists = data._lists || [];
  const pipelines = data._pipelines || [];
  const products = data._products || [];

  const listOptions = useMemo(
    () =>
      lists.map(({ id, name }) => ({
        value: id,
        label: name,
      })),
    [lists]
  );

  const productOptions = useMemo(
    () =>
      products.map(({ id, name }) => ({
        value: id,
        label: name,
      })),
    [products]
  );

  const pipelineOptions = useMemo(
    () =>
      pipelines.map(({ pipeline, pipeline_name }) => ({
        value: pipeline,
        label: pipeline_name,
      })),
    [pipelines]
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
          if (field.name === "listIds" || field.name === "includeListIds") {
            return {
              ...field,
              type: "options",
              options: listOptions,
              multiple: true,
            };
          } else if (field.name === "product") {
            return {
              ...field,
              type: "options",
              options: productOptions,
            };
          } else if (field.name === "pipeline") {
            return {
              ...field,
              type: "options",
              options: pipelineOptions,
            };
          }
        }),
    [sortedFields]
  );

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
          }}
        />
      ))}
    </TemplateStep>
  );
}
