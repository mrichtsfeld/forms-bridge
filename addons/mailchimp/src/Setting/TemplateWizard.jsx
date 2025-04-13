import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BridgeStep from "./BridgeStep";
import { useTemplateConfig } from "../../../../src/providers/Templates";

const apiFetch = wp.apiFetch;
const { useState, useEffect, useMemo, useRef } = wp.element;

const MAILCHIMP_HEADERS = ["api-key", "datacenter"];

const STEPS = [
  {
    name: "bridge",
    step: ({ fields, data, setData }) => (
      <BridgeStep fields={fields} data={data} setData={setData} />
    ),
    order: 20,
  },
];

function debounce(fn, ms = 500) {
  let timeout;

  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), ms);
  };
}

function validateBackendData(data) {
  return MAILCHIMP_HEADERS.reduce((isValid, field) => {
    return isValid && data.headers[field];
  }, true);
}
export default function MailchimpTemplateWizard({ integration, onDone }) {
  const [{ backends }] = useGeneral();

  const config = useTemplateConfig();
  const configFields = useMemo(() => config?.fields || [], [config]);
  const apiFields = configFields
    .filter((field) => field.ref === "#bridge/custom_fields[]")
    .map((field) => field.name);

  const [data, setData] = useState({});

  const [lists, setLists] = useState([]);

  const backendData = useMemo(() => {
    if (!data.backend?.name) return;
    const backend = backends.find(({ name }) => name === data.backend.name);

    if (backend && validateBackendData(backend)) {
      return backend;
    }

    const backendData = {
      name: data.backend.name,
      base_url: data.backend.base_url,
      headers: MAILCHIMP_HEADERS.reduce(
        (headers, name) => ({
          ...headers,
          [name]: data.backend[name],
        }),
        {}
      ),
    };

    if (validateBackendData(backendData)) {
      return backendData;
    }
  }, [data.backend, backends]);

  const fetchLists = useRef(
    debounce((data) => {
      const backend = {
        name: data.name,
        base_url: data.base_url,
        headers: MAILCHIMP_HEADERS.map((header) => ({
          name: header,
          value: data.headers[header],
        })),
      };

      apiFetch({
        path: "forms-bridge/v1/mailchimp/lists",
        method: "POST",
        data: backend,
      })
        .then(setLists)
        .catch(() => setLists([]));
    }, 500)
  ).current;

  useEffect(() => {
    if (!backendData) return;

    apiFields.includes("list_id") && fetchLists(backendData);
  }, [backendData, config]);

  useEffect(
    () =>
      setData({
        ...data,
        bridge: {
          ...(data.bridge || {}),
          _lists: lists,
        },
      }),
    [lists]
  );

  return (
    <TemplateWizard
      integration={integration}
      data={data}
      setData={setData}
      onDone={onDone}
      steps={STEPS}
    />
  );
}
