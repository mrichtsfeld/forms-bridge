import { useTemplateConfig } from "../../../providers/Templates";
import TemplateStep from "./Step";
import Field from "../Field";
import { prependEmptyOption, sortByNamesOrder } from "../../../lib/utils";

const { useMemo, useState, useEffect } = wp.element;
const { SelectControl } = wp.components;
const { __ } = wp.i18n;

const FIELDS_ORDER = ["name"];

function validateCredential(credential, schema, fields) {
  const isValid = fields.reduce((isValid, { name }) => {
    return isValid && !!credential[name];
  }, true);

  if (!isValid) return isValid;

  return Object.keys(schema).reduce((isValid, name) => {
    return isValid && !!credential[name];
  }, isValid);
}

export default function CredentialStep({ credentials, fields, data, setData }) {
  const [name, setName] = useState("");

  const names = useMemo(
    () => new Set(credentials.map(({ name }) => name)),
    [credentials]
  );

  const { credential: schema = {} } = useTemplateConfig();

  const validCredentials = useMemo(() => {
    return credentials.filter((credential) =>
      validateCredential(credential, schema, fields)
    );
  }, [credentials]);

  const credentialOptions = useMemo(() => {
    return prependEmptyOption(
      validCredentials.map(({ name }) => ({ label: name, value: name }))
    );
  }, [validCredentials]);

  const [reuse, setReuse] = useState("");
  const [previousReuse, setPreviousReuse] = useState("");

  if (reuse !== previousReuse) {
    setPreviousReuse(reuse);
    setData();
  }

  const credential = useMemo(
    () => validCredentials.find(({ name }) => name === reuse),
    [reuse]
  );

  useEffect(() => {
    if (!credential) return;
    setData({ ...credential });
  }, [credential]);

  const sortedFields = useMemo(
    () => sortByNamesOrder(fields, FIELDS_ORDER),
    [fields]
  );

  const filteredFields = useMemo(() => {
    if (credential) return [];
    return sortedFields.slice(1);
  }, [sortedFields, credential]);

  const nameField = useMemo(() => sortedFields[0], [sortedFields]);

  const nameConflict = useMemo(() => names.has(name.trim()), [names, name]);

  useEffect(() => {
    if (!nameConflict && name) setData({ name });
  }, [name, nameConflict]);

  useEffect(() => {
    if (!data.name) return;

    if (data.name && names.has(data.name.trim())) {
      setReuse(data.name);
    } else if (data.name !== name) {
      setName(data.name);
    }
  }, [data.name]);

  return (
    <TemplateStep
      name={__("Credential", "forms-bridge")}
      description={__("Configure the backend credentials", "forms-bridge")}
    >
      {credentialOptions.length > 0 && (
        <SelectControl
          label={__("Reuse an existing credential", "forms-bridge")}
          value={reuse}
          options={credentialOptions}
          onChange={setReuse}
          __nextHasNoMarginBottom
        />
      )}
      {!reuse && (
        <Field
          data={{
            ...nameField,
            value: name,
            onChange: setName,
          }}
          error={
            nameConflict
              ? __("This name is already in use", "forms-bridge")
              : false
          }
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
