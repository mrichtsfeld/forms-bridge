import TemplateStep from "./Step";
import { sortByNamesOrder } from "../../../lib/utils";

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

export default function CredentialStep({
  credentials,
  fields,
  data,
  setData,
}) {}
