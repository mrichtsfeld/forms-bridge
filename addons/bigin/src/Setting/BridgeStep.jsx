import BridgeStep from "../../../../src/components/Templates/Steps/BridgeStep";

const { useMemo, useEffect } = wp.element;

const API_FIELDS = ["Owner.id"];

export default function BiginBridgeStep({ fields, data, setData }) {
  const users = useMemo(() => data._users || [], [data._users]);

  const userOptions = useMemo(
    () =>
      users.map(({ zuid, email }) => ({
        value: zuid,
        label: email,
      })),
    [users]
  );

  const standardFields = useMemo(
    () => fields.filter(({ name }) => !API_FIELDS.includes(name)),
    [fields]
  );

  const apiFields = useMemo(() => {
    return fields
      .filter(({ name }) => API_FIELDS.includes(name))
      .map((field) => {
        if (field.name === "Owner.id") {
          return {
            ...field,
            type: "options",
            options: userOptions,
          };
        }
      });
  }, [fields, userOptions]);

  useEffect(() => {
    const defaults = {};

    if (userOptions.length > 0 && !data["Owner.id"]) {
      defaults["Owner.id"] = userOptions[0].value;
    }

    setData(defaults);
  }, [userOptions]);

  return (
    <BridgeStep
      fields={standardFields.concat(apiFields)}
      data={data}
      setData={setData}
    />
  );
}
